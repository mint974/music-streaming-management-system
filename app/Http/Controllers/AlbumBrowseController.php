<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\SavedAlbum;
use App\Models\Song;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
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

    public function download(Album $album)
    {
        if ($album->status !== 'published' || $album->deleted) {
            abort(404);
        }

        $tracks = Song::query()
            ->published()
            ->where('album_id', $album->id)
            ->where('is_vip', false)
            ->orderByDesc('released_date')
            ->orderByDesc('id')
            ->get();

        if ($tracks->isEmpty()) {
            return back()->with('error', 'Album này không có bài hát nào không phải Premium để tải về máy.');
        }

        // Fast path: if only one non-premium track, download directly (no zip build needed).
        if ($tracks->count() === 1) {
            $track = $tracks->first();

            if (empty($track?->file_path)) {
                return back()->with('error', 'Không tìm thấy file audio hợp lệ để tải xuống.');
            }

            $singleFilePath = storage_path('app/public/' . $track->file_path);
            if (! File::exists($singleFilePath)) {
                return back()->with('error', 'Không tìm thấy file audio hợp lệ để tải xuống.');
            }

            $singleExtension = (string) Str::of($singleFilePath)->afterLast('.');
            $singleExtension = $singleExtension !== '' ? $singleExtension : 'mp3';
            $singleName = Str::slug($track->title, '_') ?: 'song';

            return response()->download($singleFilePath, $singleName . '.' . $singleExtension, [
                'Content-Type' => $track->file_mime ?: 'application/octet-stream',
            ]);
        }

        $zipName = (Str::slug($album->title, '_') ?: 'album') . '-audio.zip';
        $zipPath = storage_path('app/' . uniqid('album_audio_', true) . '.zip');

        if (! class_exists(ZipArchive::class)) {
            abort(500, 'Máy chủ chưa hỗ trợ tạo file ZIP.');
        }

        $zip = new ZipArchive();

        if ($zip->open($zipPath, 1 | 8) !== true) {
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

        if ($addedCount === 0) {
            File::delete($zipPath);
            return back()->with('error', 'Không tìm thấy file audio hợp lệ để tải xuống.');
        }

        return response()->download($zipPath, $zipName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }
}
