<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\SavedAlbum;
use App\Models\Song;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            ->with(['artist:id,name,artist_name,avatar,artist_verified_at'])
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
                        ->orWhereHas('artist', function ($artistQuery) use ($q) {
                            $artistQuery->where('artist_name', 'LIKE', "%{$q}%")
                                ->orWhere('name', 'LIKE', "%{$q}%");
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
            ->with(['artist:id,name,artist_name'])
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
            'artist:id,name,artist_name,avatar,artist_verified_at,bio',
        ]);

        $tracks = Song::query()
            ->published()
            ->with(['artist:id,name,artist_name', 'genre:id,name'])
            ->where('album_id', $album->id)
            ->orderByDesc('released_date')
            ->orderByDesc('id')
            ->get();

        $artistOtherAlbums = Album::query()
            ->published()
            ->with(['artist:id,name,artist_name,avatar,artist_verified_at'])
            ->withCount([
                'songs as published_songs_count' => fn ($query) => $query->published(),
            ])
            ->withSum([
                'songs as published_songs_duration' => fn ($query) => $query->published(),
            ], 'duration')
            ->where('user_id', $album->user_id)
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
}
