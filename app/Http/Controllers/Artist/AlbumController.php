<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\Album;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AlbumController extends Controller
{
    private const MAX_IMG_MB = 5;

    // ─── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $user = Auth::user();

        $query = Album::forArtist($user->id)
            ->withCount(['songs'])
            ->orderByDesc('created_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $albums = $query->paginate(12)->withQueryString();

        return view('artist.albums.index', compact('albums'));
    }

    // ─── Create ────────────────────────────────────────────────────────────────

    public function create(): View
    {
        return view('artist.albums.create');
    }

    // ─── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'released_date'=> ['nullable', 'date'],
            'status'       => ['required', 'in:draft,published'],
            'cover_image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . self::MAX_IMG_MB * 1024],
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('covers/albums', 'public');
        }

        Album::create([
            'user_id'       => Auth::id(),
            'title'         => $validated['title'],
            'description'   => $validated['description'] ?? null,
            'released_date' => $validated['released_date'] ?? null,
            'status'        => $validated['status'],
            'cover_image'   => $coverPath,
            'deleted'       => false,
        ]);

        return redirect()->route('artist.albums.index')
            ->with('success', 'Album đã được tạo thành công!');
    }

    // ─── Edit ──────────────────────────────────────────────────────────────────

    public function edit(Album $album): View
    {
        $this->authorizeOwner($album);

        return view('artist.albums.edit', compact('album'));
    }

    // ─── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, Album $album): RedirectResponse
    {
        $this->authorizeOwner($album);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'released_date'=> ['nullable', 'date'],
            'status'       => ['required', 'in:draft,published'],
            'cover_image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . self::MAX_IMG_MB * 1024],
            'remove_cover' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_cover') && $album->cover_image) {
            Storage::disk('public')->delete($album->cover_image);
            $album->cover_image = null;
        }
        if ($request->hasFile('cover_image')) {
            if ($album->cover_image) {
                Storage::disk('public')->delete($album->cover_image);
            }
            $album->cover_image = $request->file('cover_image')->store('covers/albums', 'public');
        }

        $album->fill([
            'title'         => $validated['title'],
            'description'   => $validated['description'] ?? null,
            'released_date' => $validated['released_date'] ?? null,
            'status'        => $validated['status'],
        ]);

        $album->save();

        return redirect()->route('artist.albums.index')
            ->with('success', 'Album đã được cập nhật.');
    }

    // ─── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(Album $album): RedirectResponse
    {
        $this->authorizeOwner($album);

        $album->update(['deleted' => true]);

        return redirect()->route('artist.albums.index')
            ->with('success', 'Album đã được xóa.');
    }

    // ─── Private helpers ───────────────────────────────────────────────────────

    private function authorizeOwner(Album $album): void
    {
        if ($album->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
