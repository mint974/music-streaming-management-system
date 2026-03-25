<?php
namespace App\Http\Controllers\Listener;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlaylistController extends Controller
{
    public function index(Request $request)
    {
        $playlists = $request->user()->playlists()->withCount('songs')->latest()->get();
        return view('pages.listener.playlists.index', compact('playlists'));
    }

    public function store(Request $request)
    {
        if (!in_array($request->user()->role, ['premium', 'artist', 'admin'])) {
            return redirect()->route('subscription.index')->with('error', 'Chức năng tạo playlist cá nhân chỉ dành cho tài khoản nâng cấp. Vui lòng nâng cấp tài khoản.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048'
        ]);

        $data = $request->only('name', 'description');
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('playlists', 'public');
        }

        $request->user()->playlists()->create($data);
        return back()->with('success', 'Đã tạo playlist thành công');
    }

    public function show(Playlist $playlist)
    {
        if ($playlist->user_id !== auth()->id() && !$playlist->is_public) abort(403);
        $playlist->load('songs');
        return view('pages.listener.playlists.show', compact('playlist'));
    }

    public function update(Request $request, Playlist $playlist)
    {
        if ($playlist->user_id !== auth()->id()) abort(403);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048'
        ]);

        $data = $request->only('name', 'description');
        if ($request->hasFile('cover_image')) {
            if ($playlist->cover_image && Storage::disk('public')->exists($playlist->cover_image)) {
                Storage::disk('public')->delete($playlist->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('playlists', 'public');
        }

        $playlist->update($data);
        return back()->with('success', 'Cập nhật playlist thành công');
    }

    public function destroy(Playlist $playlist)
    {
        if ($playlist->user_id !== auth()->id()) abort(403);
        $playlist->delete();
        return redirect()->route('listener.playlists.index')->with('success', 'Đã xóa playlist!');
    }

    public function addSong(Request $request, Playlist $playlist)
    {
        if ($playlist->user_id !== auth()->id()) return response()->json(['success' => false], 403);
        $request->validate(['song_id' => 'required|exists:songs,id']);
        
        if (!$playlist->songs()->where('song_id', $request->song_id)->exists()) {
            $order = $playlist->songs()->max('sort_order') + 1;
            $playlist->songs()->attach($request->song_id, ['sort_order' => $order]);
            return response()->json(['success' => true, 'message' => 'Đã thêm bài hát vào playlist']);
        }
        return response()->json(['success' => false, 'message' => 'Bài hát đã có sẵn trong playlist'], 400);
    }

    public function removeSong(Request $request, Playlist $playlist)
    {
        if ($playlist->user_id !== auth()->id()) abort(403);
        $request->validate(['song_id' => 'required|exists:songs,id']);
        $playlist->songs()->detach($request->song_id);
        return back()->with('success', 'Đã xóa bài hát khỏi playlist');
    }

    public function reorder(Request $request, Playlist $playlist)
    {
        if ($playlist->user_id !== auth()->id()) return response()->json(['success'=>false], 403);
        $order = $request->input('order'); 
        if (is_array($order)) {
            foreach ($order as $songId => $sortOrder) {
                $playlist->songs()->updateExistingPivot($songId, ['sort_order' => $sortOrder]);
            }
        }
        return response()->json(['success' => true]);
    }

    public function searchSongsForPlaylist(Request $request, Playlist $playlist)
    {
        if ($playlist->user_id !== auth()->id()) return response()->json([], 403);
        
        $q = trim($request->input('q', ''));
        if (mb_strlen($q) < 2) return response()->json([]);

        $songs = \App\Models\Song::published()
            ->with('artist:id,name,artist_name,avatar,artist_verified_at')
            ->where(function($query) use ($q) {
                $query->where('title', 'LIKE', "%{$q}%")
                      ->orWhere('author', 'LIKE', "%{$q}%")
                      ->orWhereHas('artist', function($qArtist) use ($q) {
                          $qArtist->where('artist_name', 'LIKE', "%{$q}%")
                                  ->orWhere('name', 'LIKE', "%{$q}%");
                      });
            })
            ->limit(15)
            ->get();

        $existingSongIds = tap($playlist->songs()->pluck('songs.id')->toArray(), function(){});

        $results = $songs->map(function($song) use ($existingSongIds) {
            return [
                'id' => $song->id,
                'title' => $song->title,
                'artist' => $song->artist?->getDisplayArtistName() ?: 'Unknown',
                'cover' => $song->getCoverUrl(),
                'duration' => $song->durationFormatted(),
                'is_vip' => (bool)$song->is_vip,
                'is_added' => in_array($song->id, $existingSongIds)
            ];
        });

        return response()->json($results);
    }
}
