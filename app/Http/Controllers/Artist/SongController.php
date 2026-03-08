<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Genre;
use App\Models\Song;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SongController extends Controller
{
    // ─── Accepted audio MIME types ────────────────────────────────────────────
    private const AUDIO_MIMES = ['audio/mpeg', 'audio/mp3', 'audio/flac', 'audio/x-flac', 'audio/wav', 'audio/x-wav'];
    private const AUDIO_EXTS  = 'mp3,flac,wav';
    private const MAX_AUDIO_MB = 100;
    private const MAX_IMG_MB   = 5;

    // ─── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $user = Auth::user();

        $query = Song::forArtist($user->id)
            ->with(['genre', 'album'])
            ->orderByDesc('created_at');

        // Filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($genre = $request->input('genre_id')) {
            $query->where('genre_id', $genre);
        }
        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $songs  = $query->paginate(15)->withQueryString();
        $genres = Genre::active()->ordered()->get();

        return view('artist.songs.index', compact('songs', 'genres'));
    }

    // ─── Create ────────────────────────────────────────────────────────────────

    public function create(): View
    {
        $user   = Auth::user();
        $genres = Genre::active()->ordered()->get();
        $albums = Album::forArtist($user->id)->where('status', 'published')->get();

        return view('artist.songs.create', compact('genres', 'albums'));
    }

    // ─── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'author'       => ['nullable', 'string', 'max:255'],
            'genre_id'     => ['nullable', 'exists:genres,id'],
            'album_id'     => ['nullable', 'exists:albums,id'],
            'released_date'=> ['nullable', 'date'],
            'is_vip'       => ['boolean'],
            'status'       => ['required', 'in:draft,pending'],
            'lyrics'       => ['nullable', 'string'],
            'lyrics_type'  => ['required', 'in:plain,lrc'],
            'audio_file'   => ['required', 'file', 'mimes:' . self::AUDIO_EXTS, 'max:' . self::MAX_AUDIO_MB * 1024],
            'cover_image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . self::MAX_IMG_MB * 1024],
            'tags.mood'    => ['nullable', 'array'],
            'tags.activity'=> ['nullable', 'array'],
            'tags.topic'   => ['nullable', 'array'],
            'tags.mood.*'     => ['string'],
            'tags.activity.*' => ['string'],
            'tags.topic.*'    => ['string'],
        ]);

        $user = Auth::user();

        // Verify album belongs to this artist
        if (!empty($validated['album_id'])) {
            $album = Album::find($validated['album_id']);
            if (!$album || $album->user_id !== $user->id) {
                return back()->withErrors(['album_id' => 'Album không hợp lệ.'])->withInput();
            }
        }

        // Store audio file
        $audioFile  = $request->file('audio_file');
        $audioPath  = $audioFile->store('songs', 'public');
        $audioMime  = $audioFile->getMimeType();
        $audioSize  = $audioFile->getSize();

        // Try to get duration via ffprobe (optional) — 0 if unavailable
        $duration = $this->getAudioDuration($audioFile->getRealPath());

        // Store cover image
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('covers/songs', 'public');
        }

        Song::create([
            'user_id'       => $user->id,
            'genre_id'      => $validated['genre_id'] ?? null,
            'album_id'      => $validated['album_id'] ?? null,
            'title'         => $validated['title'],
            'author'        => $validated['author'] ?? null,
            'duration'      => $duration,
            'file_path'     => $audioPath,
            'file_mime'     => $audioMime,
            'file_size'     => $audioSize,
            'cover_image'   => $coverPath,
            'lyrics'        => $validated['lyrics'] ?? null,
            'lyrics_type'   => $validated['lyrics_type'],
            'released_date' => $validated['released_date'] ?? null,
            'is_vip'        => $request->boolean('is_vip'),
            'tags'          => [
                'mood'     => $validated['tags']['mood'] ?? [],
                'activity' => $validated['tags']['activity'] ?? [],
                'topic'    => $validated['tags']['topic'] ?? [],
            ],
            'status'  => $validated['status'],
            'listens' => 0,
            'deleted' => false,
        ]);

        return redirect()->route('artist.songs.index')
            ->with('success', 'Bài hát đã được tải lên thành công!');
    }

    // ─── Edit ──────────────────────────────────────────────────────────────────

    public function edit(Song $song): View
    {
        $this->authorizeOwner($song);

        $user   = Auth::user();
        $genres = Genre::active()->ordered()->get();
        $albums = Album::forArtist($user->id)->where('status', 'published')->get();

        return view('artist.songs.edit', compact('song', 'genres', 'albums'));
    }

    // ─── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, Song $song): RedirectResponse
    {
        $this->authorizeOwner($song);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'author'       => ['nullable', 'string', 'max:255'],
            'genre_id'     => ['nullable', 'exists:genres,id'],
            'album_id'     => ['nullable', 'exists:albums,id'],
            'released_date'=> ['nullable', 'date'],
            'is_vip'       => ['boolean'],
            'status'       => ['required', 'in:draft,pending,published'],
            'lyrics'       => ['nullable', 'string'],
            'lyrics_type'  => ['required', 'in:plain,lrc'],
            'audio_file'   => ['nullable', 'file', 'mimes:' . self::AUDIO_EXTS, 'max:' . self::MAX_AUDIO_MB * 1024],
            'cover_image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . self::MAX_IMG_MB * 1024],
            'remove_cover' => ['nullable', 'boolean'],
            'tags.mood'    => ['nullable', 'array'],
            'tags.activity'=> ['nullable', 'array'],
            'tags.topic'   => ['nullable', 'array'],
        ]);

        $user = Auth::user();

        // Verify album belongs to this artist
        if (!empty($validated['album_id'])) {
            $album = Album::find($validated['album_id']);
            if (!$album || $album->user_id !== $user->id) {
                return back()->withErrors(['album_id' => 'Album không hợp lệ.'])->withInput();
            }
        }

        // Replace audio file
        if ($request->hasFile('audio_file')) {
            if ($song->file_path) {
                Storage::disk('public')->delete($song->file_path);
            }
            $audioFile = $request->file('audio_file');
            $song->file_path = $audioFile->store('songs', 'public');
            $song->file_mime = $audioFile->getMimeType();
            $song->file_size = $audioFile->getSize();
            $song->duration  = $this->getAudioDuration($audioFile->getRealPath());
        }

        // Cover image
        if ($request->boolean('remove_cover') && $song->cover_image) {
            Storage::disk('public')->delete($song->cover_image);
            $song->cover_image = null;
        }
        if ($request->hasFile('cover_image')) {
            if ($song->cover_image) {
                Storage::disk('public')->delete($song->cover_image);
            }
            $song->cover_image = $request->file('cover_image')->store('covers/songs', 'public');
        }

        $song->fill([
            'genre_id'      => $validated['genre_id'] ?? null,
            'album_id'      => $validated['album_id'] ?? null,
            'title'         => $validated['title'],
            'author'        => $validated['author'] ?? null,
            'lyrics'        => $validated['lyrics'] ?? null,
            'lyrics_type'   => $validated['lyrics_type'],
            'released_date' => $validated['released_date'] ?? null,
            'is_vip'        => $request->boolean('is_vip'),
            'tags'          => [
                'mood'     => $validated['tags']['mood'] ?? [],
                'activity' => $validated['tags']['activity'] ?? [],
                'topic'    => $validated['tags']['topic'] ?? [],
            ],
            'status' => $validated['status'],
        ]);

        $song->save();

        return redirect()->route('artist.songs.index')
            ->with('success', 'Bài hát đã được cập nhật.');
    }

    // ─── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(Song $song): RedirectResponse
    {
        $this->authorizeOwner($song);

        // Soft delete — keep files
        $song->update(['deleted' => true]);

        return redirect()->route('artist.songs.index')
            ->with('success', 'Bài hát đã được xóa.');
    }

    // ─── Private helpers ───────────────────────────────────────────────────────

    private function authorizeOwner(Song $song): void
    {
        if ($song->user_id !== Auth::id()) {
            abort(403);
        }
    }

    /**
     * Try to extract audio duration in seconds using ffprobe.
     * Returns 0 if ffprobe is not available.
     */
    private function getAudioDuration(string $filePath): int
    {
        if (!function_exists('shell_exec')) {
            return 0;
        }

        $duration = @shell_exec(
            "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " .
            escapeshellarg($filePath) . " 2>/dev/null"
        );

        return $duration ? (int) round((float) trim($duration)) : 0;
    }
}
