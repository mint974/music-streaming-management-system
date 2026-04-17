<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Genre;
use App\Models\Song;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
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
    private const CREATE_STATUSES = ['draft', 'scheduled'];
    private const UPDATE_STATUSES = ['draft', 'pending', 'scheduled', 'published', 'hidden'];

    // ─── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $user = $this->currentUser();
        $artistProfileId = (int) ($user->artistProfile?->id ?? 0);

        if ($artistProfileId <= 0) {
            abort(403);
        }

        $query = Song::forArtist($artistProfileId)
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

    public function create(): View|RedirectResponse
    {
        if ($redirect = $this->denyIfCannotManage()) return $redirect;

        $user   = $this->currentUser();
        $artistProfileId = (int) ($user->artistProfile?->id ?? 0);

        if ($artistProfileId <= 0) {
            return redirect()->route('artist-register.index')->with('error', 'Không tìm thấy hồ sơ nghệ sĩ.');
        }
        
        $check = $user->canCreateMoreSongs();
        if (!$check['ok']) {
            return redirect()->route('artist.songs.index')->with('error', $check['message']);
        }

        $genres = Genre::active()->ordered()->get();
        $albums = Album::forArtist($artistProfileId)->where('status', 'published')->get();

        return view('artist.songs.create', compact('genres', 'albums'));
    }

    // ─── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        if ($redirect = $this->denyIfCannotManage()) return $redirect;

        $user = $this->currentUser();
        $check = $user->canCreateMoreSongs();
        if (!$check['ok']) {
            return redirect()->route('artist.songs.index')->with('error', $check['message']);
        }
        $currentYear = now()->year;
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'genre_id'     => ['required', 'exists:genres,id'],
            'album_id'     => ['nullable', 'exists:albums,id'],
            'released_date'=> ['nullable', 'date'],
            'released_year'=> ['required', 'integer', 'min:1901', 'max:' . $currentYear],
            'is_vip'       => ['boolean'],
            'status'       => ['required', 'in:' . implode(',', self::CREATE_STATUSES)],
            'publish_at'   => ['nullable', 'date'],
            'lyrics'       => ['nullable', 'string'],
            'lyrics_type'  => ['nullable', 'in:plain,lrc'],
            'lyrics_name'  => ['nullable', 'string', 'max:100'],
            'is_lyrics_visible' => ['boolean'],
            'audio_file'   => ['required', 'file', 'mimes:' . self::AUDIO_EXTS, 'max:' . self::MAX_AUDIO_MB * 1024],
            'cover_image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . self::MAX_IMG_MB * 1024],
            'tags.mood'    => ['nullable', 'array'],
            'tags.activity'=> ['nullable', 'array'],
            'tags.topic'   => ['nullable', 'array'],
            'tags.mood.*'     => ['string'],
            'tags.activity.*' => ['string'],
            'tags.topic.*'    => ['string'],
        ], [
            'released_year.required' => 'Vui lòng nhập năm phát hành.',
            'released_year.integer'  => 'Năm phát hành phải là số nguyên.',
            'released_year.min'      => 'Năm phát hành phải lớn hơn 1900.',
            'released_year.max'      => 'Năm phát hành không được vượt quá năm hiện tại (' . $currentYear . ').',
            'title.required'         => 'Vui lòng nhập tên bài hát.',
            'genre_id.required'      => 'Vui lòng chọn thể loại.',
            'genre_id.exists'        => 'Thể loại không hợp lệ.',
            'audio_file.required'    => 'Vui lòng tải lên file âm nhạc.',
            'audio_file.mimes'       => 'File âm nhạc phải có định dạng MP3, FLAC hoặc WAV.',
            'audio_file.max'         => 'File âm nhạc không được vượt quá ' . self::MAX_AUDIO_MB . 'MB.',
            'cover_image.image'      => 'Ảnh bìa không hợp lệ.',
            'cover_image.mimes'      => 'Ảnh bìa phải có định dạng JPG, PNG hoặc WEBP.',
            'cover_image.max'        => 'Ảnh bìa không được vượt quá ' . self::MAX_IMG_MB . 'MB.',
        ]);

        // Business rules for scheduling
        if (($validated['status'] ?? null) === 'scheduled' && empty($validated['publish_at'])) {
            return back()->withErrors(['publish_at' => 'Vui lòng chọn thời điểm xuất bản cho trạng thái hẹn giờ.'])->withInput();
        }

        if ($this->parsePublishAt($validated)?->isPast() && ($validated['status'] ?? null) === 'scheduled') {
            return back()->withErrors(['publish_at' => 'Thời điểm hẹn giờ phải lớn hơn thời điểm hiện tại.'])->withInput();
        }

        // Kiểm tra ít nhất 1 tag được chọn
        $allTags = array_merge(
            $validated['tags']['mood']     ?? [],
            $validated['tags']['activity'] ?? [],
            $validated['tags']['topic']    ?? [],
        );
        if (empty($allTags)) {
            return back()->withErrors(['tags' => 'Vui lòng chọn ít nhất 1 tag (Tâm trạng, Hoạt động hoặc Chủ đề) cho bài hát.'])->withInput();
        }

        $user = $this->currentUser();

        // Verify album belongs to this artist
        $artistProfileId = (int) ($user->artistProfile?->id ?? 0);
        if ($artistProfileId <= 0) {
            return back()->withErrors(['album_id' => 'Không tìm thấy hồ sơ nghệ sĩ.'])->withInput();
        }

        if (!empty($validated['album_id'])) {
            $album = Album::find($validated['album_id']);
            if (!$album || (int) $album->artist_profile_id !== $artistProfileId) {
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

        $song = Song::create([
            'artist_profile_id' => $user->artistProfile?->id,
            'genre_id'      => $validated['genre_id'] ?? null,
            'album_id'      => $validated['album_id'] ?? null,
            'title'         => $validated['title'],
            'duration'      => $duration,
            'file_path'     => $audioPath,
            'file_mime'     => $audioMime,
            'file_size'     => $audioSize,
            'cover_image'   => $coverPath,
            'released_date' => $this->resolveReleasedDate($validated),
            'is_vip'        => $request->boolean('is_vip'),
            'status'        => $this->resolveStatus($validated),
            'publish_at'    => $this->resolvePublishAt($validated),
            'listens' => 0,
            'deleted' => false,
        ]);

        $this->syncSongTags($song, $validated);

        if (!empty($validated['lyrics'])) {
            $this->syncSongLyrics(
                $song,
                $validated['lyrics'],
                $validated['lyrics_type'] ?? 'plain',
                $validated['lyrics_name'] ?? null,
                $validated['is_lyrics_visible'] ?? true
            );
        }

        if ($song->status === 'published') {
            \App\Services\ReleaseNotificationService::notifyFollowers($user, $song);
        }

        return redirect()->route('artist.songs.index')
            ->with('success', 'Bài hát đã được tải lên thành công!');
    }

    // ─── Show ──────────────────────────────────────────────────────────────────

    public function show(Song $song): View|RedirectResponse
    {
        $this->authorizeOwner($song);

        // Fallback cho dữ liệu cũ chưa có duration: tự tính lại từ file audio.
        if ((int) $song->duration <= 0 && !empty($song->file_path) && Storage::disk('public')->exists($song->file_path)) {
            $calculatedDuration = $this->getAudioDuration(storage_path('app/public/' . $song->file_path));
            if ($calculatedDuration > 0) {
                $song->duration = $calculatedDuration;
                $song->save();
            }
        }

        $song->load([
            'genre',
            'album',
            'tags',
            'favorites',
            'defaultLyric.lines',
            'lyrics' => function ($q) {
                $q->with('lines')
                    ->orderByDesc('is_default')
                    ->orderByDesc('id');
            },
        ]);

        // Ưu tiên bản lyric được đánh dấu mặc định; fallback về bản ghi mới nhất nếu dữ liệu cũ chưa set.
        $defaultLyric = $song->defaultLyric;
        if (!$defaultLyric) {
            $lyricRelations = $song->getRelation('lyrics');
            $defaultLyric = $lyricRelations->firstWhere('is_default', true) ?? $lyricRelations->first();
        }

        // Daily stats: last 30 days
        $now     = \Carbon\Carbon::now();
        $rawStats = \App\Models\SongDailyStat::where('song_id', $song->id)
            ->where('stat_date', '>=', $now->copy()->subDays(29)->toDateString())
            ->select('stat_date', 'play_count')
            ->orderBy('stat_date')
            ->pluck('play_count', 'stat_date');

        $dailyDays = [];
        $dailyVals = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->toDateString();
            $dailyDays[] = \Carbon\Carbon::parse($d)->format('d/m');
            $dailyVals[] = (int)($rawStats->get($d, 0));
        }

        $favoritesCount = $song->favorites->count();

        return view('artist.songs.show', compact(
            'song', 'defaultLyric', 'dailyDays', 'dailyVals', 'favoritesCount'
        ));
    }

    // ─── Edit ──────────────────────────────────────────────────────────────────

    public function edit(Song $song): View|RedirectResponse
    {
        if ($redirect = $this->denyIfCannotManage()) return $redirect;
        $this->authorizeOwner($song);

        $user   = $this->currentUser();
        $artistProfileId = (int) ($user->artistProfile?->id ?? 0);
        if ($artistProfileId <= 0) {
            abort(403);
        }
        $genres = Genre::active()->ordered()->get();
        $albums = Album::forArtist($artistProfileId)->where('status', 'published')->get();

        $song->load('tags', 'defaultLyric');

        return view('artist.songs.edit', compact('song', 'genres', 'albums'));
    }

    // ─── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, Song $song): RedirectResponse
    {
        if ($redirect = $this->denyIfCannotManage()) return $redirect;
        $this->authorizeOwner($song);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'genre_id'     => ['nullable', 'exists:genres,id'],
            'album_id'     => ['nullable', 'exists:albums,id'],
            'released_date'=> ['nullable', 'date'],
            'released_year'=> ['nullable', 'integer', 'min:1900', 'max:' . (now()->year + 1)],
            'is_vip'       => ['boolean'],
            'status'       => ['required', 'in:' . implode(',', self::UPDATE_STATUSES)],
            'publish_at'   => ['nullable', 'date'],
            'lyrics'       => ['nullable', 'string'],
            'lyrics_type'  => ['nullable', 'in:plain,lrc'],
            'lyrics_name'  => ['nullable', 'string', 'max:100'],
            'is_lyrics_visible' => ['boolean'],
            'audio_file'   => ['nullable', 'file', 'mimes:' . self::AUDIO_EXTS, 'max:' . self::MAX_AUDIO_MB * 1024],
            'cover_image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . self::MAX_IMG_MB * 1024],
            'remove_cover' => ['nullable', 'boolean'],
            'tags.mood'    => ['nullable', 'array'],
            'tags.activity'=> ['nullable', 'array'],
            'tags.topic'   => ['nullable', 'array'],
            'tags.mood.*'     => ['string'],
            'tags.activity.*' => ['string'],
            'tags.topic.*'    => ['string'],
        ]);

        if (($validated['status'] ?? null) === 'scheduled' && empty($validated['publish_at'])) {
            return back()->withErrors(['publish_at' => 'Vui lòng chọn thời điểm xuất bản cho trạng thái hẹn giờ.'])->withInput();
        }

        if ($this->parsePublishAt($validated)?->isPast() && ($validated['status'] ?? null) === 'scheduled') {
            return back()->withErrors(['publish_at' => 'Thời điểm hẹn giờ phải lớn hơn thời điểm hiện tại.'])->withInput();
        }

        $user = $this->currentUser();

        // Verify album belongs to this artist
        if (!empty($validated['album_id'])) {
            $album = Album::find($validated['album_id']);
            $artistProfileId = (int) ($user->artistProfile?->id ?? 0);
            if (!$album || $artistProfileId <= 0 || (int) $album->artist_profile_id !== $artistProfileId) {
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
            'released_date' => $this->resolveReleasedDate($validated),
            'is_vip'        => $request->boolean('is_vip'),
            'status'        => $this->resolveStatus($validated),
            'publish_at'    => $this->resolvePublishAt($validated),
        ]);

        $song->save();

        $this->syncSongTags($song, $validated);

        if (isset($validated['lyrics'])) {
            $this->syncSongLyrics(
                $song,
                $validated['lyrics'],
                $validated['lyrics_type'] ?? 'plain',
                $validated['lyrics_name'] ?? null,
                $validated['is_lyrics_visible'] ?? true
            );
        }

        if ($song->wasChanged('status') && $song->status === 'published') {
            \App\Services\ReleaseNotificationService::notifyFollowers($user, $song);
        }

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

    /**
     * Kiểm tra quyền tạo/chỉnh sửa nội dung.
     * Trả về RedirectResponse nếu bị chặn, null nếu được phép.
     */
    private function denyIfCannotManage(): ?RedirectResponse
    {
        $user = $this->currentUser();
        if ($user->canManageMusic()) return null;

        return redirect()->route('artist-register.index')
            ->with('warning', 'Gói Nghệ sĩ của bạn đã hết hạn. Vui lòng đăng ký gói mới để tiếp tục tạo và chỉnh sửa nội dung.');
    }

    private function currentUser(): User
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            abort(403);
        }

        return $user;
    }

    private function authorizeOwner(Song $song): void
    {
        $artistProfileId = (int) (Auth::user()?->artistProfile?->id ?? 0);
        if ($artistProfileId <= 0 || (int) $song->artist_profile_id !== $artistProfileId) {
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

    private function resolveReleasedDate(array $validated): ?string
    {
        if (!empty($validated['released_year'])) {
            return Carbon::createFromDate((int) $validated['released_year'], 1, 1)->toDateString();
        }

        if (!empty($validated['released_date'])) {
            return Carbon::parse($validated['released_date'])->toDateString();
        }

        return null;
    }

    private function parsePublishAt(array $validated): ?Carbon
    {
        if (empty($validated['publish_at'])) {
            return null;
        }

        // datetime-local input sends "YYYY-MM-DDTHH:mm" with no timezone offset.
        // Explicitly parse in Asia/Ho_Chi_Minh so the value is always correct
        // regardless of the server's system timezone.
        return Carbon::createFromFormat('Y-m-d\TH:i', $validated['publish_at'], config('app.timezone'))
            ?? Carbon::parse($validated['publish_at'], config('app.timezone'));
    }

    private function resolveStatus(array $validated): string
    {
        $status    = $validated['status'] ?? 'draft';
        $publishAt = $this->parsePublishAt($validated);

        if ($status === 'published' && $publishAt && $publishAt->isFuture()) {
            return 'scheduled';
        }

        if ($status === 'scheduled') {
            return 'scheduled';
        }

        return $status;
    }

    private function resolvePublishAt(array $validated): ?string
    {
        $status    = $validated['status'] ?? 'draft';
        $publishAt = $this->parsePublishAt($validated);

        if ($status === 'scheduled') {
            // Store as local-time string; DB session is +07:00 so MySQL converts correctly.
            return $publishAt?->format('Y-m-d H:i:s');
        }

        if ($status === 'published' && $publishAt && $publishAt->isFuture()) {
            return $publishAt->format('Y-m-d H:i:s');
        }

        return null;
    }

    private function syncSongTags(Song $song, array $validated): void
    {
        $inputTags = $validated['tags'] ?? [];

        $canonicalByType = [
            'mood' => Song::$MOOD_TAGS,
            'activity' => Song::$ACTIVITY_TAGS,
            'topic' => Song::$TOPIC_TAGS,
        ];

        $requested = [];

        foreach ($canonicalByType as $type => $definitions) {
            $slugs = array_values(array_unique($inputTags[$type] ?? []));

            foreach ($slugs as $slug) {
                if (!isset($definitions[$slug])) {
                    continue;
                }

                $label = $definitions[$slug];

                Tag::updateOrCreate(
                    ['type' => $type, 'slug' => $slug],
                    ['label' => $label]
                );

                $requested[] = ['type' => $type, 'slug' => $slug];
            }
        }

        if (empty($requested)) {
            $song->tags()->sync([]);
            return;
        }

        $tagIds = Tag::query()
            ->where(function ($query) use ($requested) {
                foreach ($requested as $pair) {
                    $query->orWhere(function ($subQuery) use ($pair) {
                        $subQuery->where('type', $pair['type'])
                            ->where('slug', $pair['slug']);
                    });
                }
            })
            ->pluck('id')
            ->all();

        $song->tags()->sync($tagIds);
    }

    private function syncSongLyrics(Song $song, ?string $lyricsText, string $lyricsType, ?string $lyricsName = null, bool $isVisible = true): void
    {
        if (empty($lyricsText)) {
            return;
        }

        $type = $lyricsType === 'lrc' ? 'synced' : 'plain';

        $songLyric = \App\Models\SongLyric::create([
            'song_id' => $song->id,
            'name' => $lyricsName ?: $this->generateAutoLyricName($song, $type),
            'language_code' => 'vi',
            'source' => 'artist',
            'is_default' => true,
            'is_visible' => $isVisible,
        ]);

        if ($type === 'synced') {
            $this->insertSyncedLyricLines($songLyric, $lyricsText);
        } else {
            $this->insertPlainLyricLines($songLyric, $lyricsText);
        }

        \App\Models\SongLyric::where('song_id', $song->id)
            ->where('id', '!=', $songLyric->id)
            ->update(['is_default' => false]);
    }

    private function insertSyncedLyricLines(\App\Models\SongLyric $songLyric, string $lyricsText): void
    {
        $lines = explode("\n", $lyricsText);
        $lineOrder = 1;
        $linesToInsert = [];

        foreach ($lines as $line) {
            if (\preg_match('/\[(\d{2,}):(\d{2})(?:\.(\d{1,3}))?\](.*)/', $line, $matches)) {
                $min = (int) $matches[1];
                $sec = (int) $matches[2];
                $msStr = isset($matches[3]) && $matches[3] !== '' ? $matches[3] : '0';
                $msParts = (int) $msStr;
                if (strlen($msStr) === 1) {
                    $msParts *= 100;
                } elseif (strlen($msStr) === 2) {
                    $msParts *= 10;
                }

                $timeMs = ($min * 60 * 1000) + ($sec * 1000) + $msParts;
                $text = trim($matches[4]);

                if (!empty($text)) {
                    $linesToInsert[] = [
                        'song_lyric_id' => $songLyric->id,
                        'line_order' => $lineOrder++,
                        'start_time_ms' => $timeMs,
                        'end_time_ms' => null,
                        'content' => $text,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (!empty($linesToInsert)) {
            \App\Models\SongLyricLine::insert($linesToInsert);
        }
    }

    private function insertPlainLyricLines(\App\Models\SongLyric $songLyric, string $lyricsText): void
    {
        $rows = \preg_split('/\r\n|\r|\n/', $lyricsText) ?: [];
        $lineOrder = 1;
        $linesToInsert = [];

        foreach ($rows as $row) {
            $text = trim((string) $row);
            if ($text === '') {
                continue;
            }

            $linesToInsert[] = [
                'song_lyric_id' => $songLyric->id,
                'line_order' => $lineOrder++,
                'start_time_ms' => null,
                'end_time_ms' => null,
                'content' => $text,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($linesToInsert)) {
            \App\Models\SongLyricLine::insert($linesToInsert);
        }
    }

    private function generateAutoLyricName(Song $song, string $type): string
    {
        $prefix = $type === 'synced' ? 'Lời đồng bộ' : 'Lời thường';
        $count = \App\Models\SongLyric::where('song_id', $song->id)->count() + 1;

        return $prefix . ' #' . $count;
    }
}
