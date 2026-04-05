<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SongDailyStat;
use App\Models\User;
use App\Models\Subscription;

class ReportController extends Controller
{
    /**
     * Giao diện thống kê.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'listens'); // listens, users, revenue
        $period = $request->get('period', '30days'); // 7days, 30days, year, custom
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $dateEnd = now()->endOfDay();
        if ($period === 'custom' && $startDate && $endDate) {
            $dateSub = \Carbon\Carbon::parse($startDate)->startOfDay();
            $dateEnd = \Carbon\Carbon::parse($endDate)->endOfDay();
        } else {
            switch ($period) {
                case '7days': $dateSub = now()->subDays(7)->startOfDay(); break;
                case 'year':  $dateSub = now()->subYears(1)->startOfDay(); break;
                default:      $dateSub = now()->subDays(30)->startOfDay(); break;
            }
        }

        $data = [
            'tab' => $tab,
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate
        ];

        if ($tab === 'users') {
            // Thống kê người dùng
            $data['userTrend'] = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('deleted', false)
                ->whereBetween('created_at', [$dateSub, $dateEnd])
                ->groupBy('date')->orderBy('date')->get();

            $today = now()->toDateString();
            $activePremiumConstraint = function ($query) use ($today) {
                $query->where('status', 'active')
                    ->where('end_date', '>=', $today);
            };

            $data['roles'] = [
                'Free'    => User::where('deleted', false)
                    ->whereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', 'free'))
                    ->whereBetween('created_at', [$dateSub, $dateEnd])
                    ->whereDoesntHave('subscriptions', $activePremiumConstraint)
                    ->count(),
                'Premium' => User::where('deleted', false)
                    ->whereBetween('created_at', [$dateSub, $dateEnd])
                    ->where(function ($query) use ($activePremiumConstraint) {
                        $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', 'premium'))
                            ->orWhereHas('subscriptions', $activePremiumConstraint);
                    })
                    ->count(),
                'Artist'  => User::where('deleted', false)
                    ->whereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', 'artist'))
                    ->whereBetween('created_at', [$dateSub, $dateEnd])
                    ->count(),
            ];

            $totalUsers = array_sum($data['roles']);
            $data['conversionRate'] = $totalUsers > 0 ? round(($data['roles']['Premium'] / $totalUsers) * 100, 2) : 0;

            // Phân bổ độ tuổi
            $data['ageDist'] = DB::table('users')
                ->where('deleted', false)
                ->whereBetween('created_at', [$dateSub, $dateEnd])
                ->selectRaw('
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) < 18 THEN 1 ELSE 0 END) as `young`,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) BETWEEN 18 AND 24 THEN 1 ELSE 0 END) as `adult`,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) BETWEEN 25 AND 34 THEN 1 ELSE 0 END) as `middle`,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) >= 35 THEN 1 ELSE 0 END) as `senior`
                ')->first();

        } elseif ($tab === 'revenue') {
            // Thống kê doanh thu
            $format = $period === 'year' ? '%Y-%m' : '%Y-%m-%d';
            $data['revenueTrend'] = Subscription::selectRaw("DATE_FORMAT(created_at, '{$format}') as date, SUM(amount_paid) as total")
                ->whereBetween('created_at', [$dateSub, $dateEnd])
                ->where('status', 'active')
                ->groupBy('date')->orderBy('date')->get();

            $data['totalPremiumRevenue'] = Subscription::where('status', 'active')->whereBetween('created_at', [$dateSub, $dateEnd])->sum('amount_paid');
            // Mock theo clicks banner để có dữ liệu ảo
            $data['totalAdRevenue'] = \App\Models\Banner::whereBetween('created_at', [$dateSub, $dateEnd])->sum('clicks') * 500; 

        } else {
            // Thống kê lượt nghe mặc định
            $data['listenTrend'] = SongDailyStat::selectRaw('stat_date as date, SUM(play_count) as total')
                ->whereBetween('stat_date', [$dateSub->format('Y-m-d'), $dateEnd->format('Y-m-d')])
                ->groupBy('date')->orderBy('date')->get();

            $data['topGenres'] = DB::table('song_daily_stats')
                ->join('songs', 'song_daily_stats.song_id', '=', 'songs.id')
                ->join('genres', 'songs.genre_id', '=', 'genres.id')
                ->selectRaw('genres.name, SUM(song_daily_stats.play_count) as total')
                ->whereBetween('stat_date', [$dateSub->format('Y-m-d'), $dateEnd->format('Y-m-d')])
                ->groupBy('genres.name')->orderByDesc('total')->take(5)->get();

            // Tổng lượt nghe hiện tại
            $data['totalListens'] = SongDailyStat::whereBetween('stat_date', [$dateSub->format('Y-m-d'), $dateEnd->format('Y-m-d')])->sum('play_count');
            
            // Avg listen time logic ảo: 1 lượt nghe tương đương 3.2 phút
            $data['avgListenTimeMins'] = ($data['totalListens'] * 3.2);
        }

        return view('admin.reports.index', $data);
    }

    /**
     * Xuất Excel/CSV đơn giản
     */
    public function export(Request $request)
    {
        $tab = $request->get('tab', 'revenue');
        
        $fileName = "bao_cao_{$tab}_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($tab) {
            $file = fopen('php://output', 'w');
            // Thêm BOM UTF-8 để mở bằng Excel ko lỗi font tiếng Việt
            fputs($file, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));

            if ($tab === 'revenue') {
                fputcsv($file, ['Tháng/Ngày', 'Doanh thu (VNĐ)', 'Loại doanh thu']);
                $revenues = Subscription::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as date, SUM(amount_paid) as total")
                    ->where('status', 'active')
                    ->groupBy('date')->orderByDesc('date')->get();
                foreach($revenues as $rev) {
                    fputcsv($file, [$rev->date, $rev->total, 'Gói Premium']);
                }
            } elseif ($tab === 'users') {
                fputcsv($file, ['Ngày', 'Lượng người dùng mới']);
                $users = User::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as date, COUNT(*) as count")
                    ->groupBy('date')->orderByDesc('date')->get();
                foreach($users as $u) {
                    fputcsv($file, [$u->date, $u->count]);
                }
            } else {
                fputcsv($file, ['Ngày', 'Tổng Lượt Nghe']);
                $stats = SongDailyStat::selectRaw('stat_date as date, SUM(play_count) as total')
                    ->groupBy('date')->orderByDesc('date')->get();
                foreach($stats as $s) {
                    fputcsv($file, [$s->date, $s->total]);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
