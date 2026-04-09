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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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

        if (! in_array($sort, ['newest', 'popular', 'az', 'premium'], true)) {
            $sort = 'newest';
        }

        if (! in_array($topPeriod, ['week', 'month', 'quarter'], true)) {
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
        } elseif ($sort === 'premium') {
            $songsQuery->orderByDesc('is_vip')->orderByDesc('listens')->orderByDesc('id');
        } else {
            $songsQuery->orderByDesc('released_date')->orderByDesc('id');
        }

        $songs = $songsQuery
            ->paginate($cardsLimit)
            ->withQueryString();

        $topSongsQuery = Song::query()
            ->published()
            ->with(['artist:id,name,artist_name', 'genre:id,name'])
            ->when($topGenreId > 0, fn (Builder $query) => $query->where('genre_id', $topGenreId));

        $topSongsQuery->withSum(['dailyStats as period_listens' => function ($query) use ($topPeriod) {
            if ($topPeriod === 'week') {
                $query->whereBetween('stat_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
            } elseif ($topPeriod === 'month') {
                $query->whereMonth('stat_date', now()->month)
                      ->whereYear('stat_date', now()->year);
            } elseif ($topPeriod === 'quarter') {
                $query->whereBetween('stat_date', [now()->startOfQuarter()->toDateString(), now()->endOfQuarter()->toDateString()]);
            }
        }], 'play_count');

        $topSongs = $topSongsQuery->orderByDesc('period_listens')
            ->orderByDesc('listens')
            ->take(10)
            ->get();

        // Fallback when there is no listening history in selected period.
        if ($topSongs->every(fn (Song $song) => (int) ($song->period_listens ?? 0) === 0)) {
            $topSongs = Song::query()
                ->published()
                ->with(['artist:id,name,artist_name', 'genre:id,name'])
                ->when($topGenreId > 0, fn (Builder $query) => $query->where('genre_id', $topGenreId))
                ->orderByDesc('listens')
                ->take(10)
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

        $hasLyricVisibilityColumn = Schema::hasColumn('song_lyrics', 'is_visible');

        $song->load([
            'artist:id,name,artist_name,avatar,artist_verified_at,bio',
            'album:id,title',
            'genre:id,name',
            'lyrics' => function ($query) use ($hasLyricVisibilityColumn) {
                $query->where('status', 'verified');

                if ($hasLyricVisibilityColumn) {
                    $query->where('is_visible', true);
                }

                $query->with('lines')
                    ->orderByDesc('is_default')
                    ->orderByDesc('id');
            },
        ]);

        $visibleLyrics = $song->getRelation('lyrics');
        $defaultVisibleLyric = $visibleLyrics->firstWhere('id', $song->default_lyric_id)
            ?? $visibleLyrics->firstWhere('is_default', true)
            ?? $visibleLyrics->first();

        // Check if audio file exists
        $fileExists = false;
        if (!empty($song->file_path)) {
            $filePath = storage_path('app/public/' . $song->file_path);
            $fileExists = File::exists($filePath);
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
            'visibleLyrics' => $visibleLyrics,
            'defaultVisibleLyric' => $defaultVisibleLyric,
            'artistSongs' => $artistSongs,
            'artistAlbums' => $artistAlbums,
            'isFavorited' => $isFavorited,
            'isAlbumSaved' => $isAlbumSaved,
            'favoriteSongIds' => $favoriteSongIds,
            'savedAlbumIds' => $savedAlbumIds,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function download(Song $song)
    {
        if ($song->status !== 'published' || $song->deleted) {
            abort(404);
        }

        if ($song->is_vip) {
            return back()->with('error', 'Bài hát Premium không hỗ trợ tải về máy.');
        }

        if (empty($song->file_path)) {
            abort(404, 'Bài hát này không có file âm thanh.');
        }

        $filePath = storage_path('app/public/' . $song->file_path);

        if (! File::exists($filePath)) {
            abort(404, 'Bài hát đang được cập nhật. Vui lòng quay lại sau.');
        }

        $extension = (string) Str::of($filePath)->afterLast('.');
        $extension = $extension !== '' ? $extension : 'mp3';
        $downloadName = Str::slug($song->title, '_') ?: 'song';

        return response()->download($filePath, $downloadName . '.' . $extension, [
            'Content-Type' => $song->file_mime ?: 'application/octet-stream',
        ]);
    }

    /**
     * API to fetch song lyrics for the player.
     */
    public function lyrics(Song $song)
    {
        if ($song->status !== 'published' || $song->deleted) {
            return response()->json(['error' => 'Song not found'], 404);
        }

        $hasLyricVisibilityColumn = Schema::hasColumn('song_lyrics', 'is_visible');

        $lyricsQuery = $song->lyrics()
            ->where('status', 'verified');

        if ($hasLyricVisibilityColumn) {
            $lyricsQuery->where('is_visible', true);
        }

        $lyrics = $lyricsQuery
            ->with('lines')
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        if ($lyrics->isEmpty()) {
            return response()->json([
                'id' => $song->id,
                'versions' => [],
                'lyrics' => null,
            ]);
        }

        $lyric = $lyrics->firstWhere('id', $song->default_lyric_id)
            ?? $lyrics->firstWhere('is_default', true)
            ?? $lyrics->first();

        $versions = $lyrics->map(function ($item) {
            $lines = [];
            if ($item->type === 'synced') {
                $lines = $item->lines->map(fn ($line) => [
                    'time' => round($line->start_time_ms / 1000, 3),
                    'text' => $line->content,
                ])->toArray();
            }

            return [
                'id' => (int) $item->id,
                'name' => $item->name ?: ('Phiên bản #' . $item->id),
                'type' => $item->type,
                'is_default' => (bool) $item->is_default,
                'raw_text' => $item->raw_text,
                'lines' => $lines,
            ];
        })->values();

        $lines = [];
        if ($lyric->type === 'synced') {
            $lines = $lyric->lines->map(fn($line) => [
                'time' => round($line->start_time_ms / 1000, 3), // return in seconds, float format
                'text' => $line->content,
            ])->toArray();
        }

        return response()->json([
            'id' => $song->id,
            'default_lyric_id' => (int) $lyric->id,
            'versions' => $versions,
            'lyrics_type' => $lyric->type,
            'raw_text' => $lyric->type === 'plain' ? $lyric->raw_text : null,
            'lines' => $lines,
        ]);
    }
}
