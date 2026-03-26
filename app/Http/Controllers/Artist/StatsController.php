<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\ArtistFollow;
use App\Models\Album;
use App\Models\Song;
use App\Models\SongDailyStat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $artistId = Auth::id();

        // ── Tổng quan Songs / Albums ──────────────────────────────────────────
        $songsBase = Song::where('user_id', $artistId)->where('deleted', false);

        $totalSongs     = (clone $songsBase)->count();
        $publishedSongs = (clone $songsBase)->where('status', 'published')->count();
        $totalAlbums    = Album::where('user_id', $artistId)->where('deleted', false)->count();
        $totalListens   = (clone $songsBase)->sum('listens');

        // ── Followers ─────────────────────────────────────────────────────────
        $now = Carbon::now();

        $totalFollowers  = ArtistFollow::where('artist_id', $artistId)->count();
        $weekFollowers   = ArtistFollow::where('artist_id', $artistId)
            ->where('created_at', '>=', $now->copy()->subDays(7))->count();
        $monthFollowers  = ArtistFollow::where('artist_id', $artistId)
            ->where('created_at', '>=', $now->copy()->startOfMonth())->count();

        // ── Daily-stats lượt nghe 30 ngày ────────────────────────────────────
        // song_daily_stats có: song_id, stat_date, play_count
        $songIds = (clone $songsBase)->pluck('id');

        $dailyRaw = SongDailyStat::whereIn('song_id', $songIds)
            ->where('stat_date', '>=', $now->copy()->subDays(29)->toDateString())
            ->select('stat_date', DB::raw('SUM(play_count) as total'))
            ->groupBy('stat_date')
            ->orderBy('stat_date')
            ->pluck('total', 'stat_date');

        // Build 30-day array (fill 0 for missing days)
        $growthDays    = [];
        $growthValues  = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->toDateString();
            $growthDays[]   = Carbon::parse($d)->format('d/m');
            $growthValues[] = (int) ($dailyRaw->get($d, 0));
        }

        // Tổng theo kỳ (từ daily stats)
        $todayListens = (int) SongDailyStat::whereIn('song_id', $songIds)
            ->whereDate('stat_date', $now->toDateString())->sum('play_count');
        $weekListens  = (int) SongDailyStat::whereIn('song_id', $songIds)
            ->where('stat_date', '>=', $now->copy()->subDays(6)->toDateString())->sum('play_count');
        $monthListens = (int) SongDailyStat::whereIn('song_id', $songIds)
            ->where('stat_date', '>=', $now->copy()->startOfMonth()->toDateString())->sum('play_count');

        // ── Follow growth chart (30 ngày) ────────────────────────────────────
        $followRaw = ArtistFollow::where('artist_id', $artistId)
            ->where('created_at', '>=', $now->copy()->subDays(29)->startOfDay())
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('day')
            ->pluck('cnt', 'day');

        $followValues = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->toDateString();
            $followValues[] = (int) ($followRaw->get($d, 0));
        }

        // ── Top 5 bài hát phổ biến nhất ──────────────────────────────────────
        $topSongs = (clone $songsBase)
            ->with('genre')
            ->orderByDesc('listens')
            ->take(5)
            ->get(['id', 'title', 'author', 'cover_image', 'listens', 'genre_id', 'duration']);

        // ── Phân bố thính giả từ listening_histories ─────────────────────────
        $listenerRows = DB::table('listening_histories')
            ->join('songs', 'listening_histories.song_id', '=', 'songs.id')
            ->join('users', 'listening_histories.user_id', '=', 'users.id')
            ->where('songs.user_id', $artistId)
            ->where('songs.deleted', false)
            ->select(
                'users.id as uid',
                'users.gender',
                'users.birthday',
                'listening_histories.source',
                'listening_histories.listened_at'
            )
            ->get();

        $unique = $listenerRows->unique('uid');
        $totalListeners = $unique->count();

        // Giới tính
        $genderDist = [
            'Nam'  => $unique->where('gender', 'Nam')->count(),
            'Nữ'   => $unique->where('gender', 'Nữ')->count(),
            'Khác' => $unique->filter(fn($u) => !in_array($u->gender, ['Nam', 'Nữ']))->count(),
        ];

        // Độ tuổi
        $ageDist = ['<18' => 0, '18-24' => 0, '25-34' => 0, '35-44' => 0, '>44' => 0, 'N/A' => 0];
        foreach ($unique as $u) {
            if ($u->birthday) {
                $age = Carbon::parse($u->birthday)->age;
                if      ($age < 18) $ageDist['<18']++;
                elseif  ($age < 25) $ageDist['18-24']++;
                elseif  ($age < 35) $ageDist['25-34']++;
                elseif  ($age < 45) $ageDist['35-44']++;
                else                $ageDist['>44']++;
            } else {
                $ageDist['N/A']++;
            }
        }

        // Nguồn phát
        $sourceDist = $listenerRows
            ->groupBy('source')
            ->map(fn($g) => $g->count())
            ->sortDesc();

        // Lượt nghe theo giờ trong ngày (từ listening_histories)
        $hourlyRaw = DB::table('listening_histories')
            ->join('songs', 'listening_histories.song_id', '=', 'songs.id')
            ->where('songs.user_id', $artistId)
            ->where('songs.deleted', false)
            ->select(DB::raw('HOUR(listening_histories.listened_at) as hr'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('hr')
            ->pluck('cnt', 'hr');
        $hourlyDist = [];
        for ($h = 0; $h < 24; $h++) {
            $hourlyDist[] = (int)($hourlyRaw->get($h, 0));
        }

        // ── Bar chart "Top 10 bài hát" ────────────────────────────────────────
        $top10 = (clone $songsBase)
            ->orderByDesc('listens')
            ->take(10)
            ->get(['title', 'listens'])
            ->map(fn($s) => [
                'title'   => \Illuminate\Support\Str::limit($s->title, 18),
                'listens' => (int)$s->listens,
            ]);

        // ── Trạng thái bài hát ────────────────────────────────────────────────
        $statusDist = (clone $songsBase)
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('COALESCE(SUM(listens),0) as total_listens'))
            ->groupBy('status')
            ->get();

        return view('artist.stats.index', compact(
            // Overview
            'totalSongs', 'publishedSongs', 'totalAlbums', 'totalListens',
            'totalListeners',
            // Followers
            'totalFollowers', 'weekFollowers', 'monthFollowers',
            // Period listens
            'todayListens', 'weekListens', 'monthListens',
            // Charts
            'growthDays', 'growthValues',
            'followValues',
            'top10',
            // Top songs
            'topSongs',
            // Audience
            'genderDist', 'ageDist', 'sourceDist', 'hourlyDist',
            // Status
            'statusDist'
        ));
    }
}
