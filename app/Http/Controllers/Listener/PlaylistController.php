<?php
namespace App\Http\Controllers\Listener;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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
        $traceId = (string) Str::uuid();
        Log::info('[Download:Playlist] Request received', [
            'trace_id' => $traceId,
            'playlist_id' => (int) $playlist->id,
            'owner_id' => (int) $playlist->user_id,
            'auth_id' => (int) (Auth::id() ?? 0),
            'uid_query' => (int) $request->query('uid', 0),
            'has_valid_signature' => $request->hasValidSignature(),
            'url' => $request->fullUrl(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        if ($playlist->user_id !== Auth::id()) abort(403);

        if ((int) $request->query('uid', 0) !== (int) Auth::id()) {
            Log::warning('[Download:Playlist] Rejected invalid uid/signature context', [
                'trace_id' => $traceId,
                'playlist_id' => (int) $playlist->id,
                'uid_query' => (int) $request->query('uid', 0),
                'auth_id' => (int) (Auth::id() ?? 0),
            ]);
            abort(403, 'Liên kết tải xuống không hợp lệ.');
        }

        if (! $request->user()->canAccessPremium()) {
            Log::warning('[Download:Playlist] Rejected non-premium user', [
                'trace_id' => $traceId,
                'playlist_id' => (int) $playlist->id,
                'user_id' => (int) (Auth::id() ?? 0),
            ]);
            return redirect()->route('subscription.index')->with('error', 'Chức năng tải playlist về máy chỉ dành cho tài khoản nâng cấp.');
        }

        $songs = $playlist->songs()
            ->where('songs.is_vip', false)
            ->get();

        Log::info('[Download:Playlist] Downloadable tracks resolved', [
            'trace_id' => $traceId,
            'playlist_id' => (int) $playlist->id,
            'tracks_count' => (int) $songs->count(),
        ]);

        if ($songs->isEmpty()) {
            Log::warning('[Download:Playlist] No non-premium tracks available', [
                'trace_id' => $traceId,
                'playlist_id' => (int) $playlist->id,
            ]);
            return back()->with('error', 'Playlist này không có bài hát nào không phải Premium để tải về máy.');
        }

        // Fast path: if only one downloadable track, download directly.
        if ($songs->count() === 1) {
            $track = $songs->first();

            if (empty($track?->file_path)) {
                Log::error('[Download:Playlist] Single-track download missing file_path', [
                    'trace_id' => $traceId,
                    'playlist_id' => (int) $playlist->id,
                    'song_id' => (int) ($track?->id ?? 0),
                ]);
                return back()->with('error', 'Không tìm thấy file audio hợp lệ để tải xuống.');
            }

            $singleFilePath = storage_path('app/public/' . $track->file_path);
            if (! File::exists($singleFilePath)) {
                Log::error('[Download:Playlist] Single-track file not found', [
                    'trace_id' => $traceId,
                    'playlist_id' => (int) $playlist->id,
                    'song_id' => (int) $track->id,
                    'file_path' => $singleFilePath,
                ]);
                return back()->with('error', 'Không tìm thấy file audio hợp lệ để tải xuống.');
            }

            if (! $this->isAllowedAudioFile($singleFilePath, (string) ($track->file_mime ?? ''))) {
                Log::error('[Download:Playlist] Single-track invalid audio format/mime', [
                    'trace_id' => $traceId,
                    'playlist_id' => (int) $playlist->id,
                    'song_id' => (int) $track->id,
                    'file_path' => $singleFilePath,
                    'file_mime' => (string) ($track->file_mime ?? ''),
                ]);
                return back()->with('error', 'Không tìm thấy file audio hợp lệ để tải xuống.');
            }

            $singleExtension = (string) Str::of($singleFilePath)->afterLast('.');
            $singleExtension = $singleExtension !== '' ? $singleExtension : 'mp3';
            $singleName = Str::slug($track->title, '_') ?: 'song';
            $downloadFileName = $singleName . '.' . $singleExtension;

            Log::info('[Download:Playlist] Sending single-track download response', [
                'trace_id' => $traceId,
                'playlist_id' => (int) $playlist->id,
                'song_id' => (int) $track->id,
                'file_path' => $singleFilePath,
                'download_file_name' => $downloadFileName,
            ]);

            if (ob_get_level()) {
                ob_end_clean();
            }

            return response()->download(
                $singleFilePath,
                $downloadFileName,
                $this->buildDownloadHeaders($track->file_mime ?: 'application/octet-stream')
            );
        }

        $zipName = $this->sanitizeDownloadName($playlist->name) . '-audio.zip';
        $zipPath = storage_path('app/' . uniqid('playlist_audio_', true) . '.zip');
        $zipClass = 'ZipArchive';

        if (! class_exists($zipClass)) {
            Log::error('[Download:Playlist] ZipArchive extension missing', [
                'trace_id' => $traceId,
                'playlist_id' => (int) $playlist->id,
            ]);
            abort(500, 'Máy chủ chưa hỗ trợ tạo file ZIP.');
        }

        $zip = new $zipClass();

        if ($zip->open($zipPath, 1 | 8) !== true) {
            Log::error('[Download:Playlist] Failed to create zip file', [
                'trace_id' => $traceId,
                'playlist_id' => (int) $playlist->id,
                'zip_path' => $zipPath,
            ]);
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

            if (! $this->isAllowedAudioFile($filePath, (string) ($song->file_mime ?? ''))) {
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
                // Store mode avoids expensive CPU compression spikes on large playlists.
                $zip->setCompressionName($entryName, \ZipArchive::CM_STORE);
                $addedCount++;
            }
        }

        $zip->close();

        Log::info('[Download:Playlist] Zip build finished', [
            'trace_id' => $traceId,
            'playlist_id' => (int) $playlist->id,
            'zip_path' => $zipPath,
            'added_count' => (int) $addedCount,
        ]);

        if ($addedCount === 0) {
            File::delete($zipPath);
            Log::warning('[Download:Playlist] Zip contains zero valid tracks', [
                'trace_id' => $traceId,
                'playlist_id' => (int) $playlist->id,
            ]);
            return back()->with('error', 'Không tìm thấy file audio hợp lệ để tải xuống.');
        }

        Log::info('[Download:Playlist] Sending playlist zip download response', [
            'trace_id' => $traceId,
            'playlist_id' => (int) $playlist->id,
            'zip_name' => $zipName,
            'zip_path' => $zipPath,
        ]);

        if (ob_get_level()) {
            ob_end_clean();
        }

        return response()
            ->download($zipPath, $zipName, $this->buildDownloadHeaders('application/zip'))
            ->deleteFileAfterSend(true);
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

    private function isAllowedAudioFile(string $filePath, string $mimeType = ''): bool
    {
        $allowedExtensions = ['mp3', 'flac', 'wav', 'ogg', 'm4a', 'aac'];
        $extension = strtolower((string) Str::of($filePath)->afterLast('.'));

        if (! in_array($extension, $allowedExtensions, true)) {
            return false;
        }

        return $mimeType === '' || str_starts_with(strtolower($mimeType), 'audio/');
    }

    private function buildDownloadHeaders(string $mimeType): array
    {
        return [
            'Content-Type' => 'application/octet-stream',
            'X-Download-Content-Type' => $mimeType,
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
        ];
    }
}
