<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Song;
use App\Models\ArtistFollow;
use App\Models\ListeningHistory;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $artistId = Auth::id();

        // 1. Tổng lượt nghe
        $baseQuery = SongDailyStat::whereHas('song', function ($q) use ($artistId) {
            $q->where('user_id', $artistId);
        });

        $totalListens = (clone $baseQuery)->sum('play_count');
        $todayListens = (clone $baseQuery)->whereDate('stat_date', Carbon::today())->sum('play_count');
        $weekListens = (clone $baseQuery)->whereBetween('stat_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('play_count');
        $monthListens = (clone $baseQuery)->whereMonth('stat_date', Carbon::now()->month)->whereYear('stat_date', Carbon::now()->year)->sum('play_count');

        // 2. Số người theo dõi và Xu hướng
        $totalFollowers = ArtistFollow::where('artist_id', $artistId)->count();
        $recentFollowers = ArtistFollow::where('artist_id', $artistId)
            ->whereBetween('created_at', [Carbon::now()->subDays(7), Carbon::now()])->count();

        // 3. Phân bố thính giả (dựa trên nhóm người đã nghe nhạc)
        $listenerUsers = DB::table('listening_histories')
            ->join('songs', 'listening_histories.song_id', '=', 'songs.id')
            ->join('users', 'listening_histories.user_id', '=', 'users.id')
            ->where('songs.user_id', $artistId)
            ->select('users.id', 'users.gender', 'users.birthday', 'listening_histories.source')
            ->get();

        $uniqueListeners = $listenerUsers->unique('id');

        $genderDist = [
            'Nam' => $uniqueListeners->where('gender', 'Nam')->count(),
            'Nữ' => $uniqueListeners->where('gender', 'Nữ')->count(),
            'Khác' => $uniqueListeners->whereIn('gender', ['Khác', null])->count(),
        ];

        $ageDist = ['Dưới 18' => 0, '18-24' => 0, '25-34' => 0, 'Trên 35' => 0, 'Chưa rõ' => 0];
        foreach ($uniqueListeners as $u) {
            if ($u->birthday) {
                $age = Carbon::parse($u->birthday)->age;
                if ($age < 18) $ageDist['Dưới 18']++;
                elseif ($age <= 24) $ageDist['18-24']++;
                elseif ($age <= 34) $ageDist['25-34']++;
                else $ageDist['Trên 35']++;
            } else {
                $ageDist['Chưa rõ']++;
            }
        }

        // Tần suất nguồn nghe (Stream / Playlists / Download...)
        $sourceDist = $listenerUsers->groupBy('source')->map(fn($group) => $group->count());

        // 4. Bài hát phổ biến nhất (dựa trên bảng daily_stats tổng lại)
        $topSongs = DB::table('song_daily_stats')
            ->join('songs', 'song_daily_stats.song_id', '=', 'songs.id')
            ->where('songs.user_id', $artistId)
            ->groupBy('songs.id', 'songs.title', 'songs.cover_image')
            ->select('songs.id', 'songs.title', 'songs.cover_image', DB::raw('SUM(song_daily_stats.play_count) as listens'))
            ->orderByDesc('listens')
            ->take(5)
            ->get();

        // 5. Biểu đồ tăng trưởng 30 ngày
        $last30Days = collect();
        for ($i = 29; $i >= 0; $i--) {
            $last30Days->push(Carbon::now()->subDays($i)->format('Y-m-d'));
        }

        $dailyListens = DB::table('song_daily_stats')
            ->join('songs', 'song_daily_stats.song_id', '=', 'songs.id')
            ->where('songs.user_id', $artistId)
            ->where('song_daily_stats.stat_date', '>=', Carbon::now()->subDays(30)->startOfDay())
            ->select(DB::raw('DATE(song_daily_stats.stat_date) as date'), DB::raw('SUM(song_daily_stats.play_count) as count'))
            ->groupBy('date')
            ->pluck('count', 'date');

        $growthChart = $last30Days->map(function ($date) use ($dailyListens) {
            return [
                'day' => collect(explode('-', $date))->last() . '/' . collect(explode('-', $date))->get(1),
                'listens' => $dailyListens->get($date, 0)
            ];
        });

        return view('artist.stats.index', compact(
            'totalListens', 'todayListens', 'weekListens', 'monthListens',
            'totalFollowers', 'recentFollowers',
            'genderDist', 'ageDist', 'sourceDist',
            'topSongs', 'growthChart'
        ));
    }
}
