<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SongDailyStat;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Payment;
use Rap2hpoutre\FastExcel\FastExcel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Giao diện thống kê.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'listens');
        $period = $request->get('period', '30days');
        $range = $this->getDatesByPeriod($period, $request);

        $data = $this->getReportData($request, $tab, $range);
        $data['period'] = $period;

        return view('admin.reports.index', $data);
    }

    /**
     * Xuất Excel (Sử dụng FastExcel)
     */
    public function export(Request $request)
    {
        $tab = $request->get('tab', 'revenue');
        $period = $request->get('period', '30days');
        $range = $this->getDatesByPeriod($period, $request);
        
        $data = $this->getReportData($request, $tab, $range);
        $filename = "bao_cao_{$tab}_" . now()->format('Ymd_His');

        if ($tab === 'content') {
            // Xuất đa bảng (Multi-sheets) cho nội dung để bao quát hết màn hình
            $summary = collect([
                ['Chỉ số' => 'Tổng lượt nghe', 'Giá trị' => number_format($data['totalPlaysContent'] ?? 0)],
                ['Chỉ số' => 'Bài hát hoạt động', 'Giá trị' => number_format($data['activeSongsCount'] ?? 0)],
                ['Chỉ số' => 'Nghệ sĩ hoạt động', 'Giá trị' => number_format($data['activeArtistsCount'] ?? 0)],
                ['Chỉ số' => 'Lượt yêu thích mới', 'Giá trị' => number_format($data['totalFavoritesContent'] ?? 0)],
                ['Chỉ số' => 'Thời gian báo cáo', 'Giá trị' => $data['startDate'] . ' đến ' . $data['endDate']]
            ]);

            $trending = $data['topTrendingSongs']->map(fn($s, $i) => [
                'Hạng' => $i + 1,
                'Bài hát' => $s->name,
                'Nghệ sĩ' => $s->artist,
                'Album' => $s->album ?? '-',
                'Lượt nghe' => $s->total
            ]);

            $genres = $data['topGenresContent']->map(fn($g) => [
                'Thể loại' => $g->name,
                'Số bài hát' => $g->song_count,
                'Lượt nghe' => $g->total
            ]);

            $searches = $data['topSearchQueries']->map(fn($q) => [
                'Từ khóa' => $q->query,
                'Số lượt tìm' => $q->search_count
            ]);

            $sheets = new \Rap2hpoutre\FastExcel\SheetCollection([
                'Tổng quan' => $summary,
                'Top Trending' => $trending,
                'Phân tích Thể loại' => $genres,
                'Thống kê Tìm kiếm' => $searches
            ]);

            return (new FastExcel($sheets))->download("{$filename}.xlsx");

        } elseif ($tab === 'revenue') {
            // Nếu là Doanh thu, ta cũng có thể làm đa bảng: Tổng quan + Chi tiết
            $summary = collect([
                ['Chỉ số' => 'Tổng doanh thu VIP', 'Giá trị' => number_format($data['totalPremiumRevenue']) . ' VNĐ'],
                ['Chỉ số' => 'Tổng doanh thu Nghệ sĩ', 'Giá trị' => number_format($data['totalArtistRevenue']) . ' VNĐ'],
                ['Chỉ số' => 'Dự báo 30 ngày tới', 'Giá trị' => number_format($data['forecast30']) . ' VNĐ']
            ]);

            $details = Payment::where('status', 'paid')
                ->whereBetween('paid_at', [$range['start'], $range['end']])
                ->orderByDesc('paid_at')
                ->get()
                ->map(fn($p) => [
                    'Mã GD' => $p->id,
                    'Ngày' => $p->paid_at->format('d/m/Y H:i'),
                    'Người dùng' => $p->user->name ?? 'N/A',
                    'Loại' => str_contains($p->payable_type, 'Subscription') ? 'VIP' : 'Artist',
                    'Số tiền' => $p->amount
                ]);

            $sheets = new \Rap2hpoutre\FastExcel\SheetCollection([
                'Tổng quan' => $summary,
                'Chi tiết giao dịch' => $details
            ]);

            return (new FastExcel($sheets))->download("{$filename}.xlsx");

        } elseif ($tab === 'users') {
            $summary = collect([
                ['Chỉ số' => 'Tổng Users hệ thống', 'Giá trị' => number_format(array_sum($data['roles']))],
                ['Chỉ số' => 'Người dùng Miễn phí', 'Giá trị' => number_format($data['roles']['Free'])],
                ['Chỉ số' => 'Người dùng Premium', 'Giá trị' => number_format($data['roles']['Premium'])],
                ['Chỉ số' => 'Nghệ sĩ', 'Giá trị' => number_format($data['roles']['Artist'])],
                ['Chỉ số' => 'Thời gian báo cáo', 'Giá trị' => $data['startDate'] . ' đến ' . $data['endDate']]
            ]);

            $usersList = User::where('deleted', false)
                ->whereBetween('created_at', [$range['start'], $range['end']])
                ->orderByDesc('created_at')
                ->get()
                ->map(fn($u) => [
                    'ID' => $u->id,
                    'Tên người dùng' => $u->name,
                    'Email' => $u->email,
                    'Ngày tham gia' => $u->created_at->format('d/m/Y'),
                    'Giới tính' => $u->gender == 1 ? 'Nam' : ($u->gender == 2 ? 'Nữ' : 'Khác'),
                    'Nhóm tuổi' => Carbon::parse($u->birthday)->age < 18 ? 'Dưới 18' : (Carbon::parse($u->birthday)->age < 25 ? '18-24' : (Carbon::parse($u->birthday)->age < 35 ? '25-34' : 'Trên 35'))
                ]);

            $sheets = new \Rap2hpoutre\FastExcel\SheetCollection([
                'Tổng quan' => $summary,
                'Danh sách người dùng' => $usersList
            ]);

            return (new FastExcel($sheets))->download("{$filename}.xlsx");
        }

        // Mặc định (Lượt nghe)
        $summary = collect([
            ['Chỉ số' => 'Tổng lượt nghe', 'Giá trị' => number_format($data['totalListens'] ?? 0)],
            ['Chỉ số' => 'Người nghe (Unique)', 'Giá trị' => number_format($data['uniqueListeners'] ?? 0)],
            ['Chỉ số' => 'Thời gian nghe TB (Phút)', 'Giá trị' => $data['avgListenTimeMins'] ?? 0],
            ['Chỉ số' => 'Tỷ lệ Yêu thích / Lưu', 'Giá trị' => ($data['favoriteRate'] ?? 0) . '%'],
            ['Chỉ số' => 'Tỷ lệ Thêm vào Playlist', 'Giá trị' => ($data['playlistAddRate'] ?? 0) . '%'],
            ['Chỉ số' => 'Tỷ lệ Theo dõi Nghệ sĩ', 'Giá trị' => ($data['followRate'] ?? 0) . '%'],
        ]);

        $listens = $data['listenTrend']->map(fn($t) => [
            'Ngày' => $t->date,
            'Tổng lượt nghe' => $t->total
        ]);
        
        $peakHours = $data['peakHours']->map(fn($h) => [
            'Khung giờ' => $h->hour . 'h',
            'Số lượt nghe' => $h->count
        ]);
        
        $top5 = $data['top5Content']->map(fn($s, $i) => [
            'Hạng' => $i + 1,
            'Bài hát' => $s->title,
            'Nghệ sĩ' => $s->artist,
            'Lượt nghe' => $s->total
        ]);

        $sheets = new \Rap2hpoutre\FastExcel\SheetCollection([
            'Tổng quan KPI' => $summary,
            'Lượt nghe theo ngày' => $listens,
            'Giờ nghe cao điểm' => $peakHours,
            'Top 5 bài hát' => $top5
        ]);

        return (new FastExcel($sheets))->download("{$filename}.xlsx");
    }

    /**
     * Xuất PDF (Sử dụng DomPDF)
     */
    public function exportPdf(Request $request)
    {
        $tab = $request->get('tab', 'revenue');
        $period = $request->get('period', '30days');
        $range = $this->getDatesByPeriod($period, $request);
        
        $viewData = $this->getReportData($request, $tab, $range);

        $pdf = Pdf::loadView('admin.reports.pdf', $viewData)
            ->setPaper('a4', 'landscape');

        return $pdf->download("bao_cao_{$tab}_" . now()->format('Ymd') . ".pdf");
    }

    /**
     * Helper to get common data for Index and PDF
     */
    private function getReportData($request, $tab, $range)
    {
        $dateSub = $range['start'];
        $dateEnd = $range['end'];
        
        $data = [
            'tab' => $tab,
            'startDate' => $dateSub->toDateString(),
            'endDate' => $dateEnd->toDateString(),
        ];

        if ($tab === 'users') {
            $data['userTrend'] = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('deleted', false)
                ->whereBetween('created_at', [$dateSub, $dateEnd])
                ->groupBy('date')->orderBy('date')->get();

            $today = now()->toDateString();
            $activePremiumConstraint = function ($query) use ($today) {
                $query->where('status', 'active')->where('end_date', '>=', $today);
            };

            $data['roles'] = [
                'Free'    => User::where('deleted', false)->whereHas('roles', fn ($q) => $q->where('slug', 'free'))->whereBetween('created_at', [$dateSub, $dateEnd])->whereDoesntHave('subscriptions', $activePremiumConstraint)->count(),
                'Premium' => User::where('deleted', false)->whereBetween('created_at', [$dateSub, $dateEnd])->where(function ($q) use ($activePremiumConstraint) {
                    $q->whereHas('roles', fn ($rq) => $rq->where('slug', 'premium'))->orWhereHas('subscriptions', $activePremiumConstraint);
                })->count(),
                'Artist'  => User::where('deleted', false)->whereHas('roles', fn ($q) => $q->where('slug', 'artist'))->whereBetween('created_at', [$dateSub, $dateEnd])->count(),
            ];

            $totalUsers = array_sum($data['roles']);
            $data['conversionRate'] = $totalUsers > 0 ? round(($data['roles']['Premium'] / $totalUsers) * 100, 2) : 0;

            $data['ageDist'] = DB::table('users')->where('deleted', false)->whereBetween('created_at', [$dateSub, $dateEnd])
                ->selectRaw('SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) < 18 THEN 1 ELSE 0 END) as young, SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) BETWEEN 18 AND 24 THEN 1 ELSE 0 END) as adult, SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) BETWEEN 25 AND 34 THEN 1 ELSE 0 END) as middle, SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) >= 35 THEN 1 ELSE 0 END) as senior')
                ->first();

        } elseif ($tab === 'revenue') {
            $vipPeriod = $request->get('vip_period', 'month');
            $vipDates = $this->getDatesBySection($request, 'vip', $vipPeriod);
            $data['vipPeriod'] = $vipPeriod;
            $data['vStartDate'] = $vipDates['start']->toDateString();
            $data['vEndDate'] = $vipDates['end']->toDateString();

            $artistPeriod = $request->get('artist_period', 'month');
            $artistDates = $this->getDatesBySection($request, 'artist', $artistPeriod);
            $data['artistPeriod'] = $artistPeriod;
            $data['aStartDate'] = $artistDates['start']->toDateString();
            $data['aEndDate'] = $artistDates['end']->toDateString();

            $vipFormat = $vipPeriod === 'year' ? '%Y-%m' : '%Y-%m-%d';
            $data['vipRevenueTrend'] = DB::table('payments')->where('status', 'paid')->where('payable_type', 'App\Models\Subscription')->whereBetween('paid_at', [$vipDates['start'], $vipDates['end']])->selectRaw("DATE_FORMAT(paid_at, '{$vipFormat}') as chart_date, SUM(amount) as total")->groupBy('chart_date')->orderBy('chart_date')->get();
            $data['totalPremiumRevenue'] = $data['vipRevenueTrend']->sum('total');

            $artFormat = $artistPeriod === 'year' ? '%Y-%m' : '%Y-%m-%d';
            $data['artistRevenueTrend'] = DB::table('payments')->where('status', 'paid')->where('payable_type', 'App\Models\ArtistRegistration')->whereBetween('paid_at', [$artistDates['start'], $artistDates['end']])->selectRaw("DATE_FORMAT(paid_at, '{$artFormat}') as chart_date, SUM(amount) as total")->groupBy('chart_date')->orderBy('chart_date')->get();
            $data['totalArtistRevenue'] = $data['artistRevenueTrend']->sum('total');

            $data['topVipSpenders'] = DB::table('users')->join('payments', 'users.id', '=', 'payments.user_id')->where('payments.status', 'paid')->where('payments.payable_type', 'App\Models\Subscription')->whereBetween('payments.paid_at', [$vipDates['start'], $vipDates['end']])->select('users.id', 'users.name', 'users.avatar', 'users.email', DB::raw('SUM(payments.amount) as total_spent'))->groupBy('users.id', 'users.name', 'users.avatar', 'users.email')->orderByDesc('total_spent')->limit(5)->get();
            $data['topArtistSpenders'] = DB::table('users')->join('payments', 'users.id', '=', 'payments.user_id')->where('payments.status', 'paid')->where('payments.payable_type', 'App\Models\ArtistRegistration')->whereBetween('payments.paid_at', [$artistDates['start'], $artistDates['end']])->select('users.id', 'users.name', 'users.avatar', 'users.email', DB::raw('SUM(payments.amount) as total_spent'))->groupBy('users.id', 'users.name', 'users.avatar', 'users.email')->orderByDesc('total_spent')->limit(5)->get();

            $data['totalSystemRevenue'] = DB::table('payments')->where('status', 'paid')->whereBetween('paid_at', [$dateSub, $dateEnd])->sum('amount');
            $data['totalRevenue'] = $data['totalSystemRevenue']; // For PDF
            $data['refundAmount'] = Payment::whereNotNull('refund_amount')->whereBetween('refunded_at', [$dateSub, $dateEnd])->sum('refund_amount') ?? 0;
            
            $daysDiff = max(1, $dateSub->diffInDays($dateEnd));
            $data['forecast30'] = (($data['totalPremiumRevenue'] + $data['totalArtistRevenue']) / $daysDiff) * 30;

        } elseif ($tab === 'content') {
            $trendingPeriod = $request->get('trending_period', 'week');
            $tRange = $this->getDatesBySection($request, 'trending', $trendingPeriod);
            $data['trendingPeriod'] = $trendingPeriod;
            $data['tStartDate'] = $tRange['start']->toDateString();
            $data['tEndDate'] = $tRange['end']->toDateString();

            $contentPeriod = $request->get('content_period', '30days');
            $cRange = $this->getDatesBySection($request, 'content', $contentPeriod);
            $data['contentPeriod'] = $contentPeriod;
            $data['cStartDate'] = $cRange['start']->toDateString();
            $data['cEndDate'] = $cRange['end']->toDateString();

            $searchPeriod = $request->get('search_period', '30days');
            $sRange = $this->getDatesBySection($request, 'search', $searchPeriod);
            $data['searchPeriod'] = $searchPeriod;
            $data['sStartDate'] = $sRange['start']->toDateString();
            $data['sEndDate'] = $sRange['end']->toDateString();

            $data['totalPlaysContent'] = SongDailyStat::whereBetween('stat_date', [$dateSub->format('Y-m-d'), $dateEnd->format('Y-m-d')])->sum('play_count');
            $data['activeSongsCount'] = DB::table('song_daily_stats')->whereBetween('stat_date', [$dateSub->format('Y-m-d'), $dateEnd->format('Y-m-d')])->distinct('song_id')->count('song_id');
            $data['activeArtistsCount'] = DB::table('song_daily_stats')->join('songs', 'song_daily_stats.song_id', '=', 'songs.id')->whereBetween('song_daily_stats.stat_date', [$dateSub->format('Y-m-d'), $dateEnd->format('Y-m-d')])->distinct('songs.artist_profile_id')->count('songs.artist_profile_id');
            $data['totalFavoritesContent'] = DB::table('song_favorites')->whereBetween('created_at', [$dateSub, $dateEnd])->count();

            $data['topTrendingSongs'] = DB::table('song_daily_stats')->join('songs', 'song_daily_stats.song_id', '=', 'songs.id')->join('artist_profiles', 'songs.artist_profile_id', '=', 'artist_profiles.id')->leftJoin('albums', 'songs.album_id', '=', 'albums.id')->selectRaw('songs.title as name, artist_profiles.stage_name as artist, albums.title as album, SUM(song_daily_stats.play_count) as total')->whereBetween('song_daily_stats.stat_date', [$tRange['start']->format('Y-m-d'), $tRange['end']->format('Y-m-d')])->groupBy('songs.id', 'songs.title', 'artist_profiles.stage_name', 'albums.title')->orderByDesc('total')->take(10)->get();
            $data['topGenresContent'] = DB::table('song_daily_stats')->join('songs', 'song_daily_stats.song_id', '=', 'songs.id')->join('genres', 'songs.genre_id', '=', 'genres.id')->selectRaw('genres.name, genres.color, SUM(song_daily_stats.play_count) as total, COUNT(DISTINCT song_daily_stats.song_id) as song_count')->whereBetween('song_daily_stats.stat_date', [$cRange['start']->format('Y-m-d'), $cRange['end']->format('Y-m-d')])->groupBy('genres.id', 'genres.name', 'genres.color')->orderByDesc('total')->take(8)->get();
            $data['topArtists'] = DB::table('song_daily_stats')->join('songs', 'song_daily_stats.song_id', '=', 'songs.id')->join('artist_profiles', 'songs.artist_profile_id', '=', 'artist_profiles.id')->selectRaw('artist_profiles.stage_name as name, SUM(song_daily_stats.play_count) as total')->whereBetween('song_daily_stats.stat_date', [$cRange['start']->format('Y-m-d'), $cRange['end']->format('Y-m-d')])->groupBy('artist_profiles.id', 'artist_profiles.stage_name')->orderByDesc('total')->take(10)->get();
            
            $data['topSearchQueries'] = DB::table('search_histories as sh')->selectRaw('sh.query, COUNT(*) as search_count')->whereBetween('sh.created_at', [$sRange['start'], $sRange['end']])->where(fn($q) => $q->whereExists(fn($sq) => $sq->select(DB::raw(1))->from('songs')->whereRaw('songs.title LIKE CONCAT(\'%\', sh.query, \'%\')')->where('songs.status', 'published')->where('songs.deleted', false))->orWhereExists(fn($sq) => $sq->select(DB::raw(1))->from('artist_profiles')->whereRaw('artist_profiles.stage_name LIKE CONCAT(\'%\', sh.query, \'%\')')))->groupBy('sh.query')->orderByDesc('search_count')->take(20)->get();
            $data['noResultQueries'] = DB::table('search_histories as sh')->selectRaw('sh.query, COUNT(*) as search_count')->whereBetween('sh.created_at', [$sRange['start'], $sRange['end']])->whereNotExists(fn($q) => $q->select(DB::raw(1))->from('songs')->whereRaw('songs.title LIKE CONCAT(\'%\', sh.query, \'%\')')->where('songs.status', 'published')->where('songs.deleted', false))->whereNotExists(fn($q) => $q->select(DB::raw(1))->from('artist_profiles')->whereRaw('artist_profiles.stage_name LIKE CONCAT(\'%\', sh.query, \'%\')'))->groupBy('sh.query')->orderByDesc('search_count')->take(15)->get();
            $data['totalSearches'] = DB::table('search_histories')->whereBetween('created_at', [$sRange['start'], $sRange['end']])->count();
            $data['totalRevenue'] = Payment::where('status', 'paid')->whereBetween('paid_at', [$dateSub, $dateEnd])->sum('amount');

        } else {
            // -- Tab Lượt nghe (Listens) Redesign Data --
            
            // 1. Key Metrics & Growth calculation
            $daysDiff = $dateSub->diffInDays($dateEnd) + 1;
            $prevStart = (clone $dateSub)->subDays($daysDiff);
            $prevEnd = (clone $dateSub)->subSecond();

            // Total Listens & Growth
            $data['totalListens'] = SongDailyStat::whereBetween('stat_date', [$dateSub->format('Y-m-d'), $dateEnd->format('Y-m-d')])->sum('play_count');
            $prevListens = SongDailyStat::whereBetween('stat_date', [$prevStart->format('Y-m-d'), $prevEnd->format('Y-m-d')])->sum('play_count');
            $data['listensGrowth'] = $prevListens > 0 ? round((($data['totalListens'] - $prevListens) / $prevListens) * 100, 1) : 0;

            // Unique Listeners & Growth
            $lhQuery = DB::table('listening_histories')->whereBetween('listened_at', [$dateSub, $dateEnd]);
            $data['uniqueListeners'] = (clone $lhQuery)->distinct('user_id')->count('user_id');
            $prevUnique = DB::table('listening_histories')->whereBetween('listened_at', [$prevStart, $prevEnd])->distinct('user_id')->count('user_id');
            $data['uniqueGrowth'] = $prevUnique > 0 ? round((($data['uniqueListeners'] - $prevUnique) / $prevUnique) * 100, 1) : 0;

            // Avg Session Time & Growth
            $totalLh = $lhQuery->count();
            $data['avgListenTimeMins'] = $totalLh > 0 ? round((clone $lhQuery)->avg('played_seconds') / 60, 2) : 0;
            $prevAvg = DB::table('listening_histories')->whereBetween('listened_at', [$prevStart, $prevEnd])->avg('played_seconds');
            $prevAvgMins = $prevAvg ? round($prevAvg / 60, 2) : 0;
            $data['sessionGrowth'] = $prevAvgMins > 0 ? round((($data['avgListenTimeMins'] - $prevAvgMins) / $prevAvgMins) * 100, 1) : 0;

            // New Subscribers & Growth
            $data['newSubscribers'] = Payment::where('status', 'paid')->where('payable_type', 'App\Models\Subscription')->whereBetween('paid_at', [$dateSub, $dateEnd])->count();
            $prevSubs = Payment::where('status', 'paid')->where('payable_type', 'App\Models\Subscription')->whereBetween('paid_at', [$prevStart, $prevEnd])->count();
            $data['subsGrowth'] = $prevSubs > 0 ? round((($data['newSubscribers'] - $prevSubs) / $prevSubs) * 100, 1) : 0;

            // 2. Main Trend: Line Chart Data
            $data['listenTrend'] = SongDailyStat::selectRaw('stat_date as date, SUM(play_count) as total')
                ->whereBetween('stat_date', [$dateSub->format('Y-m-d'), $dateEnd->format('Y-m-d')])
                ->groupBy('date')->orderBy('date')->get();

            // 3. Peak Listening Hours (Heatmap/Bar chart)
            $data['peakHours'] = DB::table('listening_histories')
                ->selectRaw('HOUR(listened_at) as hour, COUNT(*) as count')
                ->whereBetween('listened_at', [$dateSub, $dateEnd])
                ->groupBy('hour')->orderBy('hour')->get();

            // 4. Top 5 Performing Content
            $data['top5Content'] = DB::table('song_daily_stats')
                ->join('songs', 'song_daily_stats.song_id', '=', 'songs.id')
                ->join('artist_profiles', 'songs.artist_profile_id', '=', 'artist_profiles.id')
                ->selectRaw('songs.title, artist_profiles.stage_name as artist, SUM(song_daily_stats.play_count) as total')
                ->whereBetween('song_daily_stats.stat_date', [$dateSub->format('Y-m-d'), $dateEnd->format('Y-m-d')])
                ->groupBy('songs.id', 'songs.title', 'artist_profiles.stage_name')
                ->orderByDesc('total')->limit(5)->get();

            // 5. Engagement Metrics
            $data['favoriteRate'] = $totalLh > 0 ? round((DB::table('song_favorites')->whereBetween('created_at', [$dateSub, $dateEnd])->count() / $totalLh) * 100, 1) : 0;
            $data['playlistAddRate'] = $totalLh > 0 ? round((DB::table('playlist_song')->whereBetween('created_at', [$dateSub, $dateEnd])->count() / $totalLh) * 100, 1) : 0;
            $data['followRate'] = $totalLh > 0 ? round((DB::table('artist_follows')->whereBetween('created_at', [$dateSub, $dateEnd])->count() / $totalLh) * 100, 1) : 0;
            $data['skipRate'] = $totalLh > 0 ? round(((clone $lhQuery)->where('played_seconds', '<', 30)->count() / $totalLh) * 100, 1) : 0;
            
            $data['totalRevenue'] = Payment::where('status', 'paid')->whereBetween('paid_at', [$dateSub, $dateEnd])->sum('amount');
        }

        return $data;
    }

    /**
     * Helper to get date range for specific sections
     */
    private function getDatesBySection($request, $section, $period)
    {
        $startKey = $section ? $section . '_start_date' : 'start_date';
        $endKey = $section ? $section . '_end_date' : 'end_date';
        
        $startDate = $request->get($startKey);
        $endDate = $request->get($endKey);
        $end = now()->endOfDay();
        
        $start = match ($period) {
            'today', 'day'  => now()->startOfDay(),
            'yesterday'     => now()->subDays(1)->startOfDay(),
            '7days', 'week' => now()->subDays(7)->startOfDay(),
            'month'         => now()->subDays(30)->startOfDay(),
            'year'          => now()->subYear()->startOfDay(),
            'custom'        => ($startDate && $endDate) ? Carbon::parse($startDate)->startOfDay() : now()->subDays(30)->startOfDay(),
            default         => now()->subDays(30)->startOfDay(),
        };

        if ($period === 'custom' && $startDate && $endDate) {
            $end = Carbon::parse($endDate)->endOfDay();
            // Bắt lỗi: nếu ngày bắt đầu > ngày kết thúc thì quăng lỗi
            if ($start->gt($end)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    $startKey => 'Ngày bắt đầu không được lớn hơn ngày kết thúc.'
                ]);
            }
        } elseif ($period === 'yesterday') {
            $end = now()->subDays(1)->endOfDay();
        }

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Helper to get date range from period string (Global)
     */
    private function getDatesByPeriod($period, $request)
    {
        return $this->getDatesBySection($request, '', $period);
    }
}
