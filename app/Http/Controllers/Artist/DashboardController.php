<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\ArtistFollow;
use App\Models\Album;
use App\Models\Song;
use App\Models\SongDailyStat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $artistId = Auth::id();
        $now      = Carbon::now();

        // ── KPI cards ─────────────────────────────────────────────────────────
        $songsBase = Song::where('user_id', $artistId)->where('deleted', false);

        $totalSongs     = (clone $songsBase)->count();
        $publishedSongs = (clone $songsBase)->where('status', 'published')->count();
        $pendingSongs   = (clone $songsBase)->where('status', 'pending')->count();
        $totalAlbums    = Album::where('user_id', $artistId)->where('deleted', false)->count();
        $totalListens   = (clone $songsBase)->sum('listens');
        $totalFollowers = ArtistFollow::where('artist_id', $artistId)->count();

        // ── Lượt nghe 7 ngày (daily stats) ───────────────────────────────────
        $songIds = (clone $songsBase)->pluck('id');

        $last7Raw = SongDailyStat::whereIn('song_id', $songIds)
            ->where('stat_date', '>=', $now->copy()->subDays(6)->toDateString())
            ->select('stat_date', DB::raw('SUM(play_count) as total'))
            ->groupBy('stat_date')
            ->pluck('total', 'stat_date');

        $weekDays    = [];
        $weekListens = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->toDateString();
            $weekDays[]    = Carbon::parse($d)->format('d/m');
            $weekListens[] = (int)($last7Raw->get($d, 0));
        }

        $todayListens = (int) SongDailyStat::whereIn('song_id', $songIds)
            ->whereDate('stat_date', $now->toDateString())->sum('play_count');

        $weekTotal = array_sum($weekListens);

        // ── Top 3 bài hát tuần này──────────────────────────────────────────────
        $top3Week = SongDailyStat::whereIn('song_id', $songIds)
            ->where('stat_date', '>=', $now->copy()->subDays(6)->toDateString())
            ->select('song_id', DB::raw('SUM(play_count) as week_listens'))
            ->groupBy('song_id')
            ->orderByDesc('week_listens')
            ->take(3)
            ->with('song:id,title,cover_image,listens')
            ->get();

        // Fallback: nếu không có daily stats thì dùng listens column
        if ($top3Week->isEmpty()) {
            $top3Week = (clone $songsBase)
                ->orderByDesc('listens')
                ->take(3)
                ->get(['id', 'title', 'cover_image', 'listens'])
                ->map(fn($s) => (object)[
                    'song'         => $s,
                    'week_listens' => $s->listens,
                ]);
        }

        // ── Bài hát gần đây nhất ──────────────────────────────────────────────
        $recentSongs = (clone $songsBase)
            ->with('genre')
            ->orderByDesc('created_at')
            ->take(5)
            ->get(['id', 'title', 'author', 'cover_image', 'status', 'listens', 'created_at', 'genre_id']);

        // ── Album gần đây ──────────────────────────────────────────────────────
        $recentAlbum = Album::where('user_id', $artistId)
            ->where('deleted', false)
            ->withCount('songs')
            ->orderByDesc('created_at')
            ->first();

        // ── Followers tuần này ────────────────────────────────────────────────
        $newFollowers = ArtistFollow::where('artist_id', $artistId)
            ->where('created_at', '>=', $now->copy()->subDays(6)->startOfDay())
            ->count();

        return view('artist.dashboard', compact(
            'totalSongs', 'publishedSongs', 'pendingSongs',
            'totalAlbums', 'totalListens', 'totalFollowers',
            'weekDays', 'weekListens', 'todayListens', 'weekTotal',
            'top3Week', 'recentSongs', 'recentAlbum', 'newFollowers'
        ));
    }
}
