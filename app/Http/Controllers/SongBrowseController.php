<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Genre;
use App\Models\SavedAlbum;
use App\Models\SongFavorite;
use App\Models\Song;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SongBrowseController extends Controller
{
    /**
     * Public song page for listeners.
     */
    public function index(Request $request): View
    {
        $q = trim((string) $request->input('q', ''));
        $genreId = (int) $request->input('genre_id', 0);
        $sort = (string) $request->input('sort', 'newest');
        $cardsLimit = (int) $request->input('limit', 12);
        $topGenreId = (int) $request->input('top_genre_id', 0);
        $topPeriod = (string) $request->input('top_period', 'week');

        if (! in_array($cardsLimit, [6, 8, 10, 12, 16, 20], true)) {
            $cardsLimit = 12;
        }

        if (! in_array($sort, ['newest', 'popular', 'az'], true)) {
            $sort = 'newest';
        }

        if (! in_array($topPeriod, ['week', 'month', 'year'], true)) {
            $topPeriod = 'week';
        }

        $genres = Genre::query()
            ->active()
            ->ordered()
            ->get(['id', 'name']);

        $songsQuery = Song::query()
            ->published()
            ->with([
                'artist:id,name,artist_name,avatar,artist_verified_at',
                'album:id,title',
                'genre:id,name',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('title', 'LIKE', "%{$q}%")
                        ->orWhere('author', 'LIKE', "%{$q}%")
                        ->orWhereHas('artist', function ($artistQuery) use ($q) {
                            $artistQuery->where('artist_name', 'LIKE', "%{$q}%")
                                ->orWhere('name', 'LIKE', "%{$q}%");
                        });
                });
            })
            ->when($genreId > 0, fn ($query) => $query->where('genre_id', $genreId));

        if ($sort === 'popular') {
            $songsQuery->orderByDesc('listens')->orderByDesc('id');
        } elseif ($sort === 'az') {
            $songsQuery->orderBy('title')->orderByDesc('id');
        } else {
            $songsQuery->orderByDesc('released_date')->orderByDesc('id');
        }

        $songs = $songsQuery
            ->paginate($cardsLimit)
            ->withQueryString();

        $periodFrom = match ($topPeriod) {
            'month' => Carbon::now('Asia/Ho_Chi_Minh')->subMonth(),
            'year'  => Carbon::now('Asia/Ho_Chi_Minh')->subYear(),
            default => Carbon::now('Asia/Ho_Chi_Minh')->subWeek(),
        };

        $topSongs = Song::query()
            ->published()
            ->with(['artist:id,name,artist_name', 'genre:id,name'])
            ->when($topGenreId > 0, fn (Builder $query) => $query->where('genre_id', $topGenreId))
            ->withCount([
                'listeningHistories as period_listens' => function ($query) use ($periodFrom) {
                    $query->where('listened_at', '>=', $periodFrom);
                },
            ])
            ->orderByDesc('period_listens')
            ->orderByDesc('listens')
            ->take(5)
            ->get();

        // Fallback when there is no listening history in selected period.
        if ($topSongs->every(fn (Song $song) => (int) ($song->period_listens ?? 0) === 0)) {
            $topSongs = Song::query()
                ->published()
                ->with(['artist:id,name,artist_name', 'genre:id,name'])
                ->when($topGenreId > 0, fn (Builder $query) => $query->where('genre_id', $topGenreId))
                ->orderByDesc('listens')
                ->take(5)
                ->get();
        }

            $favoriteSongIds = [];
            if (Auth::check()) {
                $pageSongIds = collect($songs->items())->pluck('id');
                $topSongIds = $topSongs->pluck('id');
                $favoriteSongIds = SongFavorite::query()
                ->where('user_id', (int) Auth::id())
                ->whereIn('song_id', $pageSongIds->merge($topSongIds)->unique()->values()->all())
                ->pluck('song_id')
                ->map(static fn ($id) => (int) $id)
                ->all();
            }

        $breadcrumbs = [
            ['label' => 'Songs', 'url' => route('songs.index')],
        ];

        return view('pages.songs.index', [
            'songs' => $songs,
            'topSongs' => $topSongs,
            'genres' => $genres,
            'q' => $q,
            'genreId' => $genreId,
            'sort' => $sort,
            'cardsLimit' => $cardsLimit,
            'topGenreId' => $topGenreId,
            'topPeriod' => $topPeriod,
            'favoriteSongIds' => $favoriteSongIds,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function show(Song $song): View
    {
        if ($song->status !== 'published' || $song->deleted) {
            abort(404);
        }

        $song->load([
            'artist:id,name,artist_name,avatar,artist_verified_at,bio',
            'album:id,title',
            'genre:id,name',
        ]);

        // Check if audio file exists
        $fileExists = false;
        if (!empty($song->file_path)) {
            $filePath = storage_path('app/public/' . $song->file_path);
            $fileExists = file_exists($filePath);
        }

        $artistSongs = Song::query()
            ->published()
            ->with(['artist:id,name,artist_name', 'genre:id,name'])
            ->where('user_id', $song->user_id)
            ->where('id', '!=', $song->id)
            ->orderByDesc('listens')
            ->take(8)
            ->get();

        $artistAlbums = Album::query()
            ->published()
            ->with(['artist:id,name,artist_name,avatar,artist_verified_at'])
            ->withCount([
                'songs as published_songs_count' => fn ($query) => $query->published(),
            ])
            ->withSum([
                'songs as published_songs_duration' => fn ($query) => $query->published(),
            ], 'duration')
            ->where('user_id', $song->user_id)
            ->orderByDesc('released_date')
            ->take(6)
            ->get();

        $isFavorited = false;
        $isAlbumSaved = false;
        if (Auth::check()) {
            $isFavorited = SongFavorite::query()
            ->where('user_id', (int) Auth::id())
                ->where('song_id', $song->id)
                ->exists();

            if ($song->album_id) {
                $isAlbumSaved = SavedAlbum::query()
                    ->where('user_id', (int) Auth::id())
                    ->where('album_id', (int) $song->album_id)
                    ->exists();
            }
        }

        $favoriteSongIds = [];
        if (Auth::check() && $artistSongs->isNotEmpty()) {
            $favoriteSongIds = SongFavorite::query()
            ->where('user_id', (int) Auth::id())
                ->whereIn('song_id', $artistSongs->pluck('id')->all())
                ->pluck('song_id')
                ->map(static fn ($id) => (int) $id)
                ->all();
        }

        $savedAlbumIds = [];
        if (Auth::check() && $artistAlbums->isNotEmpty()) {
            $savedAlbumIds = SavedAlbum::query()
                ->where('user_id', (int) Auth::id())
                ->whereIn('album_id', $artistAlbums->pluck('id')->all())
                ->pluck('album_id')
                ->map(static fn ($id) => (int) $id)
                ->all();
        }

        $breadcrumbs = [
            ['label' => 'Songs', 'url' => route('songs.index')],
            ['label' => $song->title, 'url' => route('songs.show', $song->id)],
        ];

        return view('pages.songs.show', [
            'song' => $song,
            'fileExists' => $fileExists,
            'artistSongs' => $artistSongs,
            'artistAlbums' => $artistAlbums,
            'isFavorited' => $isFavorited,
            'isAlbumSaved' => $isAlbumSaved,
            'favoriteSongIds' => $favoriteSongIds,
            'savedAlbumIds' => $savedAlbumIds,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
