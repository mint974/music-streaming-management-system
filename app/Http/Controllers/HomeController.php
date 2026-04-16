<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Banner;
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
        $bannerQuery = Banner::query()
            ->where('status', 'active')
            ->where(function ($query) {
                $now = now();
                $query->where(function ($scheduleQuery) use ($now) {
                    $scheduleQuery->whereNull('start_time')->orWhere('start_time', '<=', $now);
                })->where(function ($scheduleQuery) use ($now) {
                    $scheduleQuery->whereNull('end_time')->orWhere('end_time', '>=', $now);
                });
            });

        $heroBanners = (clone $bannerQuery)
            ->orderBy('order_index')
            ->get();

        // Featured album: newest released album
        $featuredAlbum = Album::published()
            ->with(['artistProfile:id,user_id,artist_package_id,stage_name,bio,avatar,cover_image,verified_at,revoked_at', 'artistProfile.user:id,name,avatar', 'songs:id,album_id,duration'])
            ->orderByDesc('released_date')
            ->first();

        // Trending songs: most listened in last 7 days (top 12)
        $trendingSongs = Song::published()
            ->with(['artistProfile:id,user_id,artist_package_id,stage_name,bio,avatar,cover_image,verified_at,revoked_at', 'artistProfile.user:id,name,avatar', 'genre:id,name'])
            ->orderByDesc('listens')
            ->limit(12)
            ->get();

        // New releases: latest published songs (top 12)
        $newReleases = Song::published()
            ->with(['artistProfile:id,user_id,artist_package_id,stage_name,bio,avatar,cover_image,verified_at,revoked_at', 'artistProfile.user:id,name,avatar', 'genre:id,name'])
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $topSongsWeek = \App\Models\Song::published()
            ->with(['artistProfile:id,user_id,artist_package_id,stage_name,bio,avatar,cover_image,verified_at,revoked_at', 'artistProfile.user:id,name,avatar', 'genre:id,name'])
            ->withSum(['dailyStats as listens_count' => function ($query) {
                $query->whereBetween('stat_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
            }], 'play_count')
            ->orderByDesc('listens_count')
            ->orderByDesc('listens')
            ->take(10)
            ->get();

        $topSongsMonth = \App\Models\Song::published()
            ->with(['artistProfile:id,user_id,artist_package_id,stage_name,bio,avatar,cover_image,verified_at,revoked_at', 'artistProfile.user:id,name,avatar', 'genre:id,name'])
            ->withSum(['dailyStats as listens_count' => function ($query) {
                $query->whereMonth('stat_date', now()->month)
                      ->whereYear('stat_date', now()->year);
            }], 'play_count')
            ->orderByDesc('listens_count')
            ->orderByDesc('listens')
            ->take(10)
            ->get();

        $topSongsQuarter = \App\Models\Song::published()
            ->with(['artistProfile:id,user_id,artist_package_id,stage_name,bio,avatar,cover_image,verified_at,revoked_at', 'artistProfile.user:id,name,avatar', 'genre:id,name'])
            ->withSum(['dailyStats as listens_count' => function ($query) {
                $query->whereBetween('stat_date', [now()->startOfQuarter()->toDateString(), now()->endOfQuarter()->toDateString()]);
            }], 'play_count')
            ->orderByDesc('listens_count')
            ->orderByDesc('listens')
            ->take(10)
            ->get();

        // Fallbacks if dailyStats is empty
        if ($topSongsWeek->every(fn ($s) => (int) $s->listens_count === 0)) {
            $fallback = Song::published()->with(['artistProfile:id,user_id,artist_package_id,stage_name,bio,avatar,cover_image,verified_at,revoked_at', 'artistProfile.user:id,name,avatar'])->orderByDesc('listens')->take(10)->get();
            $topSongsWeek = $fallback;
            $topSongsMonth = $fallback;
            $topSongsQuarter = $fallback;
        }

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
                    ->with(['artistProfile:id,user_id,artist_package_id,stage_name,bio,avatar,cover_image,verified_at,revoked_at', 'artistProfile.user:id,name,avatar', 'genre:id,name'])
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
        $featuredArtists = User::whereHas('roles', fn ($query) => $query->where('slug', 'artist'))
            ->where('users.deleted', false)
            ->withCount(['songs as published_songs_count' => function ($query) {
                $query->where('songs.status', 'published')->where('songs.deleted', false);
            }])
            ->having('published_songs_count', '>', 0)
            ->orderByDesc('published_songs_count')
            ->limit(6)
            ->get();

        return view('pages.home', compact(
            'heroBanners',
            'featuredAlbum',
            'trendingSongs',
            'newReleases',
            'topSongsWeek',
            'topSongsMonth',
            'topSongsQuarter',
            'recentlyPlayed',
            'genres',
            'featuredArtists'
        ));
    }

    public function trackBannerClick(\App\Models\Banner $banner)
    {
        $banner->increment('clicks');
        
        if ($banner->target_url) {
            return redirect()->away($banner->target_url);
        }
        
        return back();
    }
}
