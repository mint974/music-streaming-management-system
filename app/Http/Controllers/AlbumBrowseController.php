<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\SavedAlbum;
use App\Models\Song;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

class AlbumBrowseController extends Controller
{
    /**
     * Public album browse page.
     */
    public function index(Request $request): View
    {
        $q = trim((string) $request->input('q', ''));
        $sort = (string) $request->input('sort', 'newest');
        $cardsLimit = (int) $request->input('limit', 12);

        if (! in_array($cardsLimit, [6, 8, 10, 12, 16, 20], true)) {
            $cardsLimit = 12;
        }

        if (! in_array($sort, ['newest', 'popular', 'az'], true)) {
            $sort = 'newest';
        }

        $albumsQuery = Album::query()
            ->published()
            ->with([
                'artistProfile:id,user_id,artist_package_id,stage_name,bio,avatar,cover_image,verified_at,revoked_at',
                'artistProfile.user:id,name,avatar',
            ])
            ->withCount([
                'songs as published_songs_count' => fn ($query) => $query->published(),
            ])
            ->withSum([
                'songs as published_songs_duration' => fn ($query) => $query->published(),
            ], 'duration')
            ->withSum([
                'songs as published_songs_listens' => fn ($query) => $query->published(),
            ], 'listens')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('title', 'LIKE', "%{$q}%")
                        ->orWhere('description', 'LIKE', "%{$q}%")
                        ->orWhereHas('artistProfile', function ($profileQuery) use ($q) {
                            $profileQuery->where(function ($nestedProfileQuery) use ($q) {
                                $nestedProfileQuery->where('stage_name', 'LIKE', "%{$q}%")
                                    ->orWhere('bio', 'LIKE', "%{$q}%")
                                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'LIKE', "%{$q}%"));
                            });
                        });
                });
            });

        if ($sort === 'popular') {
            $albumsQuery->orderByDesc('published_songs_listens')->orderByDesc('published_songs_count')->orderByDesc('id');
        } elseif ($sort === 'az') {
            $albumsQuery->orderBy('title')->orderByDesc('id');
        } else {
            $albumsQuery->orderByDesc('released_date')->orderByDesc('id');
        }

        $albums = $albumsQuery->paginate($cardsLimit)->withQueryString();

        $topAlbums = Album::query()
            ->published()
            ->with([
                'artistProfile:id,user_id,artist_package_id,stage_name,bio,avatar,cover_image,verified_at,revoked_at',
                'artistProfile.user:id,name,avatar',
            ])
            ->withCount([
                'songs as published_songs_count' => fn ($query) => $query->published(),
            ])
            ->withSum([
                'songs as published_songs_listens' => fn ($query) => $query->published(),
            ], 'listens')
            ->orderByDesc('published_songs_listens')
            ->orderByDesc('published_songs_count')
            ->take(5)
            ->get();

        $savedAlbumIds = [];
        if (Auth::check()) {
            $savedAlbumIds = SavedAlbum::query()
                ->where('user_id', (int) Auth::id())
                ->whereIn('album_id', collect($albums->items())->pluck('id')->merge($topAlbums->pluck('id'))->unique()->values()->all())
                ->pluck('album_id')
                ->map(static fn ($id) => (int) $id)
                ->all();
        }

        $breadcrumbs = [
            ['label' => 'Albums', 'url' => route('albums.index')],
        ];

        return view('pages.albums.index', [
            'albums' => $albums,
            'topAlbums' => $topAlbums,
            'q' => $q,
            'sort' => $sort,
            'cardsLimit' => $cardsLimit,
            'savedAlbumIds' => $savedAlbumIds,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Public album detail page.
     */
    public function show(Album $album): View
    {
        if ($album->status !== 'published' || $album->deleted) {
            abort(404);
        }

        $album->load([
            'artistProfile:id,user_id,artist_package_id,stage_name,bio,avatar,cover_image,verified_at,revoked_at',
            'artistProfile.user:id,name,avatar',
        ]);

        $tracks = Song::query()
            ->published()
            ->with([
                'artistProfile:id,user_id,artist_package_id,stage_name,bio,avatar,cover_image,verified_at,revoked_at',
                'artistProfile.user:id,name,avatar',
                'genre:id,name',
            ])
            ->where('album_id', $album->id)
            ->orderByDesc('released_date')
            ->orderByDesc('id')
            ->get();

        $artistOtherAlbums = Album::query()
            ->published()
            ->with([
                'artistProfile:id,user_id,artist_package_id,stage_name,bio,avatar,cover_image,verified_at,revoked_at',
                'artistProfile.user:id,name,avatar',
            ])
            ->withCount([
                'songs as published_songs_count' => fn ($query) => $query->published(),
            ])
            ->withSum([
                'songs as published_songs_duration' => fn ($query) => $query->published(),
            ], 'duration')
            ->where('artist_profile_id', $album->artist_profile_id)
            ->where('id', '!=', $album->id)
            ->orderByDesc('released_date')
            ->take(8)
            ->get();

        $isSaved = false;
        $savedAlbumIds = [];
        if (Auth::check()) {
            $isSaved = SavedAlbum::query()
                ->where('user_id', (int) Auth::id())
                ->where('album_id', $album->id)
                ->exists();

            $savedAlbumIds = SavedAlbum::query()
                ->where('user_id', (int) Auth::id())
                ->whereIn('album_id', $artistOtherAlbums->pluck('id')->all())
                ->pluck('album_id')
                ->map(static fn ($id) => (int) $id)
                ->all();
        }

        $albumDuration = (int) ($tracks->sum('duration') ?? 0);

        $breadcrumbs = [
            ['label' => 'Albums', 'url' => route('albums.index')],
            ['label' => $album->title, 'url' => route('albums.show', $album->id)],
        ];

        return view('pages.albums.show', [
            'album' => $album,
            'tracks' => $tracks,
            'artistOtherAlbums' => $artistOtherAlbums,
            'isSaved' => $isSaved,
            'savedAlbumIds' => $savedAlbumIds,
            'albumDuration' => $albumDuration,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function download(Request $request, Album $album)
    {
        $traceId = (string) Str::uuid();
        Log::info('[Download:Album] Request received', [
            'trace_id' => $traceId,
            'album_id' => (int) $album->id,
            'user_id' => (int) (Auth::id() ?? 0),
            'status' => (string) $album->status,
            'deleted' => (bool) $album->deleted,
            'uid_query' => (int) $request->query('uid', 0),
            'has_valid_signature' => $request->hasValidSignature(),
            'url' => $request->fullUrl(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        if ($album->status !== 'published' || $album->deleted) {
            Log::warning('[Download:Album] Rejected unpublished/deleted album', [
                'trace_id' => $traceId,
                'album_id' => (int) $album->id,
            ]);
            abort(404);
        }

        if ((int) $request->query('uid', 0) !== (int) Auth::id()) {
            Log::warning('[Download:Album] Rejected invalid uid/signature context', [
                'trace_id' => $traceId,
                'album_id' => (int) $album->id,
                'uid_query' => (int) $request->query('uid', 0),
                'auth_id' => (int) (Auth::id() ?? 0),
            ]);
            abort(403, 'Liên kết tải xuống không hợp lệ.');
        }

        /** @var User|null $user */
        $user = Auth::user();

        if (! $user?->canAccessPremium()) {
            Log::warning('[Download:Album] Rejected non-premium user', [
                'trace_id' => $traceId,
                'album_id' => (int) $album->id,
                'user_id' => (int) ($user?->id ?? 0),
            ]);
            return redirect()->route('subscription.index')
                ->with('error', 'Tính năng tải nhạc về máy chỉ dành cho tài khoản Premium.');
        }

        $tracks = Song::query()
            ->published()
            ->where('album_id', $album->id)
            ->where('is_vip', false)
            ->orderByDesc('released_date')
            ->orderByDesc('id')
            ->get();

        Log::info('[Download:Album] Downloadable tracks resolved', [
            'trace_id' => $traceId,
            'album_id' => (int) $album->id,
            'tracks_count' => (int) $tracks->count(),
        ]);

        if ($tracks->isEmpty()) {
            Log::warning('[Download:Album] No non-premium tracks available', [
                'trace_id' => $traceId,
                'album_id' => (int) $album->id,
            ]);
            return back()->with('error', 'Album này không có bài hát nào không phải Premium để tải về máy.');
        }

        // Fast path: if only one downloadable track, download directly.
        if ($tracks->count() === 1) {
            $track = $tracks->first();

            if (empty($track?->file_path)) {
                Log::error('[Download:Album] Single-track download missing file_path', [
                    'trace_id' => $traceId,
                    'album_id' => (int) $album->id,
                    'song_id' => (int) ($track?->id ?? 0),
                ]);
                return back()->with('error', 'Không tìm thấy file audio hợp lệ để tải xuống.');
            }

            $singleFilePath = storage_path('app/public/' . $track->file_path);
            if (! File::exists($singleFilePath)) {
                Log::error('[Download:Album] Single-track file not found', [
                    'trace_id' => $traceId,
                    'album_id' => (int) $album->id,
                    'song_id' => (int) $track->id,
                    'file_path' => $singleFilePath,
                ]);
                return back()->with('error', 'Không tìm thấy file audio hợp lệ để tải xuống.');
            }

            if (! $this->isAllowedAudioFile($singleFilePath, (string) ($track->file_mime ?? ''))) {
                Log::error('[Download:Album] Single-track invalid audio format/mime', [
                    'trace_id' => $traceId,
                    'album_id' => (int) $album->id,
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

            Log::info('[Download:Album] Sending single-track download response', [
                'trace_id' => $traceId,
                'album_id' => (int) $album->id,
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

        $zipName = (Str::slug($album->title, '_') ?: 'album') . '-audio.zip';
        $zipPath = storage_path('app/' . uniqid('album_audio_', true) . '.zip');

        if (! class_exists(ZipArchive::class)) {
            Log::error('[Download:Album] ZipArchive extension missing', [
                'trace_id' => $traceId,
                'album_id' => (int) $album->id,
            ]);
            abort(500, 'Máy chủ chưa hỗ trợ tạo file ZIP.');
        }

        $zip = new ZipArchive();

        if ($zip->open($zipPath, 1 | 8) !== true) {
            Log::error('[Download:Album] Failed to create zip file', [
                'trace_id' => $traceId,
                'album_id' => (int) $album->id,
                'zip_path' => $zipPath,
            ]);
            abort(500, 'Không thể tạo file tải xuống.');
        }

        $addedCount = 0;

        foreach ($tracks as $track) {
            if (empty($track->file_path)) {
                continue;
            }

            $filePath = storage_path('app/public/' . $track->file_path);
            if (! File::exists($filePath)) {
                continue;
            }

            if (! $this->isAllowedAudioFile($filePath, (string) ($track->file_mime ?? ''))) {
                continue;
            }

            $extension = (string) Str::of($filePath)->afterLast('.');
            $extension = $extension !== '' ? $extension : 'mp3';
            $entryName = sprintf(
                '%02d_%s.%s',
                (int) $track->id,
                Str::slug($track->title, '_') ?: 'track',
                $extension
            );

            if ($zip->addFile($filePath, $entryName)) {
                // Store mode avoids expensive CPU compression spikes on large albums.
                $zip->setCompressionName($entryName, ZipArchive::CM_STORE);
                $addedCount++;
            }
        }

        $zip->close();

        Log::info('[Download:Album] Zip build finished', [
            'trace_id' => $traceId,
            'album_id' => (int) $album->id,
            'zip_path' => $zipPath,
            'added_count' => (int) $addedCount,
        ]);

        if ($addedCount === 0) {
            File::delete($zipPath);
            Log::warning('[Download:Album] Zip contains zero valid tracks', [
                'trace_id' => $traceId,
                'album_id' => (int) $album->id,
            ]);
            return back()->with('error', 'Không tìm thấy file audio hợp lệ để tải xuống.');
        }

        Log::info('[Download:Album] Sending album zip download response', [
            'trace_id' => $traceId,
            'album_id' => (int) $album->id,
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

    private function isAllowedAudioFile(string $filePath, string $mimeType = ''): bool
    {
        $allowedExtensions = ['mp3', 'flac', 'wav', 'ogg', 'm4a', 'aac'];
        $extension = strtolower((string) Str::of($filePath)->afterLast('.'));

        if (! in_array($extension, $allowedExtensions, true)) {
            return false;
        }

        return $mimeType === '' || str_starts_with(strtolower($mimeType), 'audio/');
    }
}
