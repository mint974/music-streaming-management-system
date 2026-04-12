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
use Barryvdh\DomPDF\Facade\Pdf;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Collection;

class StatsController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Helpers: parse khoảng thời gian từ request
    // ─────────────────────────────────────────────────────────────────────────
    private function resolveDateRange(Request $request): array
    {
        $period = $request->input('period', '30d');
        $now    = Carbon::now();

        return match ($period) {
            'this_month'  => [$now->copy()->startOfMonth(),           $now->copy()->endOfMonth()],
            'last_month'  => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'this_quarter'=> [$now->copy()->startOfQuarter(),          $now->copy()->endOfQuarter()],
            '7d'          => [$now->copy()->subDays(6)->startOfDay(),   $now->copy()->endOfDay()],
            'custom'      => [
                Carbon::parse($request->input('date_from', $now->copy()->subDays(29)->toDateString()))->startOfDay(),
                Carbon::parse($request->input('date_to',   $now->toDateString()))->endOfDay(),
            ],
            default       => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()], // 30d
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Main view
    // ─────────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $artistId = Auth::id();

        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $period   = $request->input('period', '30d');
        $dateFromStr = $dateFrom->toDateString();
        $dateToStr   = $dateTo->toDateString();

        $now = Carbon::now();

        // ── Songs / Albums overview ───────────────────────────────────────────
        $songsBase = Song::where('user_id', $artistId)->where('deleted', false);

        $totalSongs     = (clone $songsBase)->count();
        $publishedSongs = (clone $songsBase)->where('status', 'published')->count();
        $totalAlbums    = Album::where('user_id', $artistId)->where('deleted', false)->count();
        $totalListens   = (clone $songsBase)->sum('listens');
        $songIds        = (clone $songsBase)->pluck('id');

        // ── Followers ─────────────────────────────────────────────────────────
        $totalFollowers  = ArtistFollow::where('artist_id', $artistId)->count();
        $weekFollowers   = ArtistFollow::where('artist_id', $artistId)
            ->where('created_at', '>=', $now->copy()->subDays(7))->count();
        $monthFollowers  = ArtistFollow::where('artist_id', $artistId)
            ->where('created_at', '>=', $now->copy()->startOfMonth())->count();

        // ── Daily stats trong khoảng được chọn ───────────────────────────────
        $dailyRaw = SongDailyStat::whereIn('song_id', $songIds)
            ->whereBetween('stat_date', [$dateFromStr, $dateToStr])
            ->select('stat_date', DB::raw('SUM(play_count) as total'))
            ->groupBy('stat_date')
            ->orderBy('stat_date')
            ->pluck('total', 'stat_date');

        // Build array theo từng ngày trong khoảng
        $growthDays   = [];
        $growthValues = [];
        $cursor = $dateFrom->copy();
        while ($cursor->lte($dateTo)) {
            $d              = $cursor->toDateString();
            $growthDays[]   = $cursor->format('d/m');
            $growthValues[] = (int)($dailyRaw->get($d, 0));
            $cursor->addDay();
        }

        // ── Tổng kỳ ──────────────────────────────────────────────────────────
        $todayListens = (int) SongDailyStat::whereIn('song_id', $songIds)
            ->whereDate('stat_date', $now->toDateString())->sum('play_count');
        $weekListens  = (int) SongDailyStat::whereIn('song_id', $songIds)
            ->whereBetween('stat_date', [$now->copy()->subDays(6)->toDateString(), $now->toDateString()])
            ->sum('play_count');
        $monthListens = (int) SongDailyStat::whereIn('song_id', $songIds)
            ->where('stat_date', '>=', $now->copy()->startOfMonth()->toDateString())
            ->sum('play_count');

        // ── Follow chart (cùng khoảng thời gian) ─────────────────────────────
        $followRaw = ArtistFollow::where('artist_id', $artistId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('day')->pluck('cnt', 'day');

        $followValues = [];
        $cursor = $dateFrom->copy();
        while ($cursor->lte($dateTo)) {
            $followValues[] = (int)($followRaw->get($cursor->toDateString(), 0));
            $cursor->addDay();
        }

        // ── Dự báo 7 ngày tới (linear regression đơn giản) ───────────────────
        $forecastDays   = [];
        $forecastValues = [];
        if (count($growthValues) >= 7) {
            $last7 = array_slice($growthValues, -7);
            $avg   = array_sum($last7) / 7;
            // Tính slope từ 7 ngày gần nhất
            $n     = count($last7);
            $xMean = ($n - 1) / 2;
            $num = $den = 0;
            foreach ($last7 as $i => $val) {
                $num += ($i - $xMean) * ($val - $avg);
                $den += ($i - $xMean) ** 2;
            }
            $slope = $den > 0 ? $num / $den : 0;
            $base  = end($last7);

            for ($i = 1; $i <= 7; $i++) {
                $forecastDays[]   = $now->copy()->addDays($i)->format('d/m');
                $forecastValues[] = (int)max(0, round($base + $slope * $i));
            }
        }

        // ── Top 5 bài hát / Top 10 chart ─────────────────────────────────────
        $topSongs = (clone $songsBase)
            ->with('genre')->orderByDesc('listens')->take(5)
            ->get(['id', 'title', 'author', 'cover_image', 'listens', 'genre_id', 'duration']);

        $top10 = (clone $songsBase)->orderByDesc('listens')->take(10)
            ->get(['title', 'listens'])
            ->map(fn($s) => [
                'title'   => \Illuminate\Support\Str::limit($s->title, 18),
                'listens' => (int)$s->listens,
            ]);

        // ── Phân bố thính giả ─────────────────────────────────────────────────
        $listenerRows = DB::table('listening_histories')
            ->join('songs', 'listening_histories.song_id', '=', 'songs.id')
            ->join('users', 'listening_histories.user_id', '=', 'users.id')
            ->where('songs.user_id', $artistId)->where('songs.deleted', false)
            ->select('users.id as uid', 'users.gender', 'users.birthday', 'listening_histories.listened_at')
            ->get();

        $unique         = $listenerRows->unique('uid');
        $totalListeners = $unique->count();

        $genderDist = [
            'Nam'  => $unique->where('gender', 'Nam')->count(),
            'Nữ'   => $unique->where('gender', 'Nữ')->count(),
            'Khác' => $unique->filter(fn($u) => !in_array($u->gender, ['Nam', 'Nữ']))->count(),
        ];

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

        $hourlyRaw = DB::table('listening_histories')
            ->join('songs', 'listening_histories.song_id', '=', 'songs.id')
            ->where('songs.user_id', $artistId)->where('songs.deleted', false)
            ->select(DB::raw('HOUR(listening_histories.listened_at) as hr'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('hr')->pluck('cnt', 'hr');
        $hourlyDist = [];
        for ($h = 0; $h < 24; $h++) $hourlyDist[] = (int)($hourlyRaw->get($h, 0));

        // ── Trạng thái bài hát ────────────────────────────────────────────────
        $statusDist = (clone $songsBase)
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('COALESCE(SUM(listens),0) as total_listens'))
            ->groupBy('status')->get();

        // ── Danh sách bài hát cho bộ so sánh ─────────────────────────────────
        $allSongsForCompare = (clone $songsBase)
            ->where('status', 'published')
            ->orderByDesc('listens')
            ->get(['id', 'title']);

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
            'genderDist', 'ageDist', 'hourlyDist',
            // Status
            'statusDist',
            // Forecast
            'forecastDays', 'forecastValues',
            // Filter state
            'period', 'dateFrom', 'dateTo',
            // Compare
            'allSongsForCompare',
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // API: So sánh 2 bài hát (JSON)
    // ─────────────────────────────────────────────────────────────────────────
    public function compare(Request $request)
    {
        $artistId = Auth::id();
        $song1Id  = (int)$request->input('song1');
        $song2Id  = (int)$request->input('song2');
        $days     = min((int)$request->input('days', 30), 90);

        if (!$song1Id || !$song2Id) {
            return response()->json(['error' => 'Cần chọn 2 bài hát'], 422);
        }

        // Xác minh bài hát thuộc artist
        $ownedIds = Song::where('user_id', $artistId)
            ->whereIn('id', [$song1Id, $song2Id])
            ->pluck('title', 'id');

        if ($ownedIds->count() < 2) {
            return response()->json(['error' => 'Bài hát không hợp lệ'], 403);
        }

        $now     = Carbon::now();
        $fromStr = $now->copy()->subDays($days - 1)->toDateString();
        $toStr   = $now->toDateString();

        $buildSeries = function (int $songId) use ($fromStr, $toStr, $now, $days): array {
            $raw = SongDailyStat::where('song_id', $songId)
                ->whereBetween('stat_date', [$fromStr, $toStr])
                ->pluck('play_count', 'stat_date');
            $vals = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $d      = $now->copy()->subDays($i)->toDateString();
                $vals[] = (int)($raw->get($d, 0));
            }
            return $vals;
        };

        $days_labels = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $days_labels[] = $now->copy()->subDays($i)->format('d/m');
        }

        return response()->json([
            'labels' => $days_labels,
            'song1'  => [
                'id'     => $song1Id,
                'title'  => $ownedIds->get($song1Id),
                'values' => $buildSeries($song1Id),
            ],
            'song2'  => [
                'id'     => $song2Id,
                'title'  => $ownedIds->get($song2Id),
                'values' => $buildSeries($song2Id),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Export Excel
    // ─────────────────────────────────────────────────────────────────────────
    public function exportExcel(Request $request)
    {
        $artistId = Auth::id();
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $period = $request->input('period', '30d');

        $artist   = \App\Models\User::find($artistId);
        $songIds  = Song::where('user_id', $artistId)->where('deleted', false)->pluck('id');

        // Daily totals
        $dailyRaw = SongDailyStat::whereIn('song_id', $songIds)
            ->whereBetween('stat_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->select('stat_date', DB::raw('SUM(play_count) as total'))
            ->groupBy('stat_date')->orderBy('stat_date')
            ->pluck('total', 'stat_date');

        // Top songs
        $topSongs = Song::where('user_id', $artistId)->where('deleted', false)
            ->where('status', 'published')->orderByDesc('listens')->take(10)
            ->get(['title', 'listens', 'status']);

        $filename = 'bao-cao-' . ($artist->artist_name ?: $artist->name) . '-' . now()->format('Ymd') . '.xlsx';

        $rows = collect();
        // Header info
        $rows->push(['Báo cáo thống kê nghệ sĩ', '', '']);
        $rows->push(['Nghệ sĩ:', $artist->artist_name ?: $artist->name, '']);
        $rows->push(['Khoảng:', $dateFrom->format('d/m/Y') . ' – ' . $dateTo->format('d/m/Y'), '']);
        $rows->push(['Xuất lúc:', now()->format('H:i d/m/Y'), '']);
        $rows->push(['', '', '']);

        // Section 1: Daily listens
        $rows->push(['LƯỢT NGHE THEO NGÀY', '', '']);
        $rows->push(['Ngày', 'Lượt nghe', '']);
        foreach ($dailyRaw as $date => $total) {
            $rows->push([Carbon::parse($date)->format('d/m/Y'), (int)$total, '']);
        }
        $rows->push(['', '', '']);

        // Section 2: Top songs
        $rows->push(['TOP BÀI HÁT PHỔ BIẾN', '', '']);
        $rows->push(['Tên bài hát', 'Tổng lượt nghe', 'Trạng thái']);
        foreach ($topSongs as $s) {
            $rows->push([$s->title, (int)$s->listens, $s->status]);
        }

        return (new FastExcel($rows))->withoutHeaders()->download($filename);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Export PDF
    // ─────────────────────────────────────────────────────────────────────────
    public function exportPdf(Request $request)
    {
        $artistId = Auth::id();
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $artist  = \App\Models\User::find($artistId);
        $songIds = Song::where('user_id', $artistId)->where('deleted', false)->pluck('id');

        $dailyRaw = SongDailyStat::whereIn('song_id', $songIds)
            ->whereBetween('stat_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->select('stat_date', DB::raw('SUM(play_count) as total'))
            ->groupBy('stat_date')->orderBy('stat_date')->get();

        $topSongs = Song::where('user_id', $artistId)->where('deleted', false)
            ->where('status', 'published')->orderByDesc('listens')->take(10)
            ->get(['title', 'listens', 'status']);

        $totalInPeriod = $dailyRaw->sum('total');
        $totalSongs    = Song::where('user_id', $artistId)->where('deleted', false)->count();

        $pdf = Pdf::loadView('artist.stats.pdf', compact(
            'artist', 'dateFrom', 'dateTo',
            'dailyRaw', 'topSongs', 'totalInPeriod', 'totalSongs',
        ))->setPaper('a4', 'portrait');

        $filename = 'bao-cao-' . ($artist->artist_name ?: $artist->name) . '-' . now()->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }
}
