<?php
namespace App\Http\Controllers\Listener;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlaylistController extends Controller
{
    public function index(Request $request)
    {
        $playlists = $request->user()->playlists()->withCount('songs')->latest()->get();
        return view('pages.listener.playlists.index', compact('playlists'));
    }

    public function store(Request $request)
    {
        if (! $request->user()->canAccessPremium()) {
            return redirect()->route('subscription.index')->with('error', 'Chức năng tạo playlist cá nhân chỉ dành cho tài khoản nâng cấp. Vui lòng nâng cấp tài khoản.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048',
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
        $playlist->load([
            'user:id,name',
            'songs.artistProfile:id,user_id,artist_package_id,stage_name,bio,avatar,cover_image,verified_at,revoked_at',
            'songs.artistProfile.user:id,name,avatar',
        ]);

        return view('pages.listener.playlists.show', compact('playlist'));
    }

    public function update(Request $request, Playlist $playlist)
    {
        if ($playlist->user_id !== Auth::id()) abort(403);
        if (! $request->user()->canAccessPremium()) {
            return redirect()->route('subscription.index')->with('error', 'Chức năng quản lý playlist cá nhân chỉ dành cho tài khoản nâng cấp.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048',
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

    public function destroy(Request $request, Playlist $playlist)
    {
        if ($playlist->user_id !== Auth::id()) abort(403);
        if (! $request->user()->canAccessPremium()) {
            return redirect()->route('subscription.index')->with('error', 'Chức năng quản lý playlist cá nhân chỉ dành cho tài khoản nâng cấp.');
        }

        $playlist->delete();
        return redirect()->route('listener.playlists.index')->with('success', 'Đã xóa playlist!');
    }

    public function downloadAudio(Request $request, Playlist $playlist)
    {
        if ($playlist->user_id !== Auth::id()) abort(403);
        if (! $request->user()->canAccessPremium()) {
            return redirect()->route('subscription.index')->with('error', 'Chức năng tải playlist về máy chỉ dành cho tài khoản nâng cấp.');
        }

        $songs = $playlist->songs()
            ->where('songs.is_vip', false)
            ->get();

        if ($songs->isEmpty()) {
            return back()->with('error', 'Playlist này không có bài hát nào không phải Premium để tải về máy.');
        }

        $zipName = $this->sanitizeDownloadName($playlist->name) . '-audio.zip';
        $zipPath = storage_path('app/' . uniqid('playlist_audio_', true) . '.zip');
        $zipClass = 'ZipArchive';

        if (! class_exists($zipClass)) {
            abort(500, 'Máy chủ chưa hỗ trợ tạo file ZIP.');
        }

        $zip = new $zipClass();

        if ($zip->open($zipPath, 1 | 8) !== true) {
            abort(500, 'Không thể tạo file tải xuống.');
        }

        $addedCount = 0;

        foreach ($songs as $song) {
            if (empty($song->file_path)) {
                continue;
            }

            $filePath = storage_path('app/public/' . $song->file_path);
            if (! File::exists($filePath)) {
                continue;
            }

            $extension = (string) Str::of($filePath)->afterLast('.');
            $extension = $extension !== '' ? $extension : 'mp3';
            $entryName = sprintf(
                '%02d_%s.%s',
                (int) $song->id,
                $this->sanitizeDownloadName($song->title),
                $extension
            );

            if ($zip->addFile($filePath, $entryName)) {
                $addedCount++;
            }
        }

        $zip->close();

        if ($addedCount === 0) {
            File::delete($zipPath);
            return back()->with('error', 'Không tìm thấy file audio hợp lệ để tải xuống.');
        }

        return response()->download($zipPath, $zipName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    public function addSong(Request $request, Playlist $playlist)
    {
        if (! $request->user()->canAccessPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Chức năng chỉnh sửa playlist chỉ dành cho tài khoản Premium.',
            ], 403);
        }

        if ($playlist->user_id !== Auth::id()) return response()->json(['success' => false], 403);
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
        if (! $request->user()->canAccessPremium()) {
            return redirect()->route('subscription.index')->with('error', 'Chức năng chỉnh sửa playlist chỉ dành cho tài khoản Premium.');
        }

        if ($playlist->user_id !== Auth::id()) abort(403);
        $request->validate(['song_id' => 'required|exists:songs,id']);
        $playlist->songs()->detach($request->song_id);
        return back()->with('success', 'Đã xóa bài hát khỏi playlist');
    }

    public function reorder(Request $request, Playlist $playlist)
    {
        if (! $request->user()->canAccessPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Chức năng sắp xếp playlist chỉ dành cho tài khoản Premium.',
            ], 403);
        }

        if ($playlist->user_id !== Auth::id()) return response()->json(['success'=>false], 403);

        $payload = $request->input('order');
        if (! is_array($payload)) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu sắp xếp không hợp lệ.'], 422);
        }

        $existingSongIds = $playlist->songs()->pluck('songs.id')->map(fn ($id) => (int) $id)->all();
        $existingLookup = array_fill_keys($existingSongIds, true);

        // Support both formats:
        // 1) [12, 31, 5]               (preferred ordered song IDs)
        // 2) {"12":0,"31":1,"5":2} (legacy map songId => sortOrder)
        if (array_is_list($payload)) {
            foreach ($payload as $index => $songId) {
                $songId = (int) $songId;
                if (! isset($existingLookup[$songId])) {
                    continue;
                }
                $playlist->songs()->updateExistingPivot($songId, ['sort_order' => $index + 1]);
            }
        } else {
            foreach ($payload as $songId => $sortOrder) {
                $songId = (int) $songId;
                if (! isset($existingLookup[$songId])) {
                    continue;
                }
                $playlist->songs()->updateExistingPivot($songId, ['sort_order' => ((int) $sortOrder) + 1]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function searchSongsForPlaylist(Request $request, Playlist $playlist)
    {
        if (! $request->user()->canAccessPremium()) {
            return response()->json([], 403);
        }

        if ($playlist->user_id !== Auth::id()) return response()->json([], 403);
        
        $q = trim($request->input('q', ''));
        if (mb_strlen($q) < 2) return response()->json([]);

        $songs = \App\Models\Song::published()
            ->with(['artistProfile:id,user_id,artist_package_id,stage_name,bio,avatar,cover_image,verified_at,revoked_at', 'artistProfile.user:id,name,avatar'])
            ->where(function($query) use ($q) {
                $query->where('title', 'LIKE', "%{$q}%")
                      ->orWhere('author', 'LIKE', "%{$q}%")
                      ->orWhereHas('artistProfile', function($profileQuery) use ($q) {
                          $profileQuery->where('stage_name', 'LIKE', "%{$q}%")
                              ->orWhere('bio', 'LIKE', "%{$q}%")
                              ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'LIKE', "%{$q}%"));
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

    private function sanitizeDownloadName(string $name): string
    {
        $name = Str::slug($name, '_');

        return $name !== '' ? $name : 'playlist';
    }
}
