<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Song;
use App\Models\User;
use App\Models\Genre;
use App\Models\ListeningHistory;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Display the home page with featured content from database.
     */
    public function index()
    {
        // Featured album: newest released album
        $featuredAlbum = Album::published()
            ->with(['artist:id,name,artist_name,artist_verified_at', 'songs:id,album_id,duration'])
            ->orderByDesc('released_date')
            ->first();

        // Trending songs: most listened in last 7 days (top 12)
        $trendingSongs = Song::published()
            ->with(['artist:id,name,artist_name', 'genre:id,name'])
            ->orderByDesc('listens')
            ->limit(12)
            ->get();

        // New releases: latest published songs (top 12)
        $newReleases = Song::published()
            ->with(['artist:id,name,artist_name', 'genre:id,name'])
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        // Top charts: most popular songs (top 10)
        $topCharts = Song::published()
            ->with(['artist:id,name,artist_name', 'genre:id,name'])
            ->orderByDesc('listens')
            ->limit(10)
            ->get();

        // Recently played (if authenticated)
        $recentlyPlayed = collect();
        if (Auth::check()) {
            $recentSongIds = ListeningHistory::where('user_id', Auth::id())
                ->orderByDesc('listened_at')
                ->limit(8)
                ->pluck('song_id')
                ->unique();

            if ($recentSongIds->isNotEmpty()) {
                $recentlyPlayed = Song::published()
                    ->with(['artist:id,name,artist_name', 'genre:id,name'])
                    ->whereIn('id', $recentSongIds)
                    ->get()
                    ->sortBy(function ($song) use ($recentSongIds) {
                        return $recentSongIds->search($song->id);
                    });
            }
        }

        // Browse genres
        $genres = Genre::where('is_active', true)
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        // Featured artists: top 6 artists with most songs
        $featuredArtists = User::where('role', 'artist')
            ->where('deleted', false)
            ->withCount(['songs as published_songs_count' => function ($query) {
                $query->where('status', 'published')->where('deleted', false);
            }])
            ->having('published_songs_count', '>', 0)
            ->orderByDesc('published_songs_count')
            ->limit(6)
            ->get();

        return view('pages.home', compact(
            'featuredAlbum',
            'trendingSongs',
            'newReleases',
            'topCharts',
            'recentlyPlayed',
            'genres',
            'featuredArtists'
        ));
    }
}
