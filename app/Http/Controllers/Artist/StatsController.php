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
        $period = $request->input('period', 'this_month');
        $now    = Carbon::now();

        if ($period === 'custom') {
            $from = Carbon::parse($request->input('date_from', $now->copy()->startOfMonth()->toDateString()))->startOfDay();
            $to   = Carbon::parse($request->input('date_to',   $now->toDateString()))->endOfDay();

            // Chặn date_to > hôm nay
            if ($to->isAfter($now->copy()->endOfDay())) {
                $to = $now->copy()->endOfDay();
            }
            // Nếu date_to < date_from → swap (bảo vệ server-side)
            if ($to->lt($from)) {
                [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
            }

            return [$from, $to];
        }

        return match ($period) {
            '7d'          => [$now->copy()->subDays(6)->startOfDay(),    $now->copy()->endOfDay()],
            'last_month'  => [$now->copy()->subMonth()->startOfMonth(),  $now->copy()->subMonth()->endOfMonth()],
            'this_quarter'=> [$now->copy()->startOfQuarter(),            $now->copy()->endOfQuarter()],
            default       => [$now->copy()->startOfMonth(),              $now->copy()->endOfDay()], // this_month
        };
    }


    // ─────────────────────────────────────────────────────────────────────────
    // Main view
    // ─────────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $artist = \App\Models\User::query()
            ->with('artistProfile:id,user_id')
            ->findOrFail((int) Auth::id());
        $artistProfileId = (int) ($artist?->artistProfile?->id ?? 0);

        if ($artistProfileId <= 0) {
            abort(403, 'Artist profile not found.');
        }

        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $period      = $request->input('period', 'this_month');
        $dateFromStr = $dateFrom->toDateString();
        $dateToStr   = $dateTo->toDateString();
        $now         = Carbon::now();

        // ── Songs / Albums overview (tổng không lọc ngày — context chung) ─────
        $songsBase      = Song::where('artist_profile_id', $artistProfileId)->where('deleted', false);
        $totalSongs     = (clone $songsBase)->count();
        $publishedSongs = (clone $songsBase)->where('status', 'published')->count();
        $totalAlbums    = Album::where('artist_profile_id', $artistProfileId)->where('deleted', false)->count();
        $songIds        = (clone $songsBase)->pluck('id');

        // ── Lượt nghe trong kỳ (từ SongDailyStat) ────────────────────────────
        $totalListens = (int) SongDailyStat::whereIn('song_id', $songIds)
            ->whereBetween('stat_date', [$dateFromStr, $dateToStr])
            ->sum('play_count');

        // ── Daily listen chart trong khoảng được chọn ────────────────────────
        $dailyRaw = SongDailyStat::whereIn('song_id', $songIds)
            ->whereBetween('stat_date', [$dateFromStr, $dateToStr])
            ->select('stat_date', DB::raw('SUM(play_count) as total'))
            ->groupBy('stat_date')
            ->orderBy('stat_date')
            ->pluck('total', 'stat_date');

        $growthDays   = [];
        $growthValues = [];
        $cursor = $dateFrom->copy();
        while ($cursor->lte($dateTo)) {
            $d              = $cursor->toDateString();
            $growthDays[]   = $cursor->format('d/m');
            $growthValues[] = (int)($dailyRaw->get($d, 0));
            $cursor->addDay();
        }

        // ── Followers được gain trong kỳ ─────────────────────────────────────
        $totalFollowers = ArtistFollow::where('followed_artist_profile_id', $artistProfileId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        // Các nhãn phụ (so với kỳ trước)
        $prevFrom = $dateFrom->copy()->subDays($dateFrom->diffInDays($dateTo) + 1);
        $prevTo   = $dateFrom->copy()->subSecond();
        $prevFollowers = ArtistFollow::where('followed_artist_profile_id', $artistProfileId)
            ->whereBetween('created_at', [$prevFrom, $prevTo])
            ->count();
        $weekFollowers  = ArtistFollow::where('followed_artist_profile_id', $artistProfileId)
            ->where('created_at', '>=', $now->copy()->subDays(7))
            ->count();
        $monthFollowers = ArtistFollow::where('followed_artist_profile_id', $artistProfileId)
            ->where('created_at', '>=', $now->copy()->startOfMonth())
            ->count();

        // ── Sub-labels lượt nghe (hôm nay / 7 ngày / tháng này) ─────────────
        $todayListens = (int) SongDailyStat::whereIn('song_id', $songIds)
            ->whereDate('stat_date', $now->toDateString())->sum('play_count');
        $weekListens  = (int) SongDailyStat::whereIn('song_id', $songIds)
            ->whereBetween('stat_date', [$now->copy()->subDays(6)->toDateString(), $now->toDateString()])
            ->sum('play_count');
        $monthListens = (int) SongDailyStat::whereIn('song_id', $songIds)
            ->where('stat_date', '>=', $now->copy()->startOfMonth()->toDateString())
            ->sum('play_count');

        // ── Follow chart (cùng khoảng thời gian) ─────────────────────────────
        $followRaw = ArtistFollow::where('followed_artist_profile_id', $artistProfileId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('day')->pluck('cnt', 'day');

        $followValues = [];
        $cursor = $dateFrom->copy();
        while ($cursor->lte($dateTo)) {
            $followValues[] = (int)($followRaw->get($cursor->toDateString(), 0));
            $cursor->addDay();
        }


        // ── Top 5 / Top 10 theo lượt nghe trong kỳ (SongDailyStat) ──────────
        $periodPlaysBySong = SongDailyStat::whereIn('song_id', $songIds)
            ->whereBetween('stat_date', [$dateFromStr, $dateToStr])
            ->select('song_id', DB::raw('SUM(play_count) as period_plays'))
            ->groupBy('song_id')
            ->orderByDesc('period_plays')
            ->pluck('period_plays', 'song_id');

        $topSongIds = $periodPlaysBySong->keys()->take(5);
        $top10SongIds = $periodPlaysBySong->keys()->take(10);

        // Lấy thông tin bài hát và gắn period_plays
        $topSongsCollection = (clone $songsBase)
            ->with('genre')
            ->whereIn('id', $topSongIds)
            ->get(['id', 'title', 'cover_image', 'listens', 'genre_id', 'duration'])
            ->map(function ($s) use ($periodPlaysBySong) {
                $s->period_plays = (int)($periodPlaysBySong->get($s->id, 0));
                return $s;
            })
            ->sortByDesc('period_plays')
            ->values();

        // Fallback: nếu không có dữ liệu trong kỳ → dùng listens tổng
        $topSongs = $topSongsCollection->isNotEmpty()
            ? $topSongsCollection
            : (clone $songsBase)->with('genre')->orderByDesc('listens')->take(5)
                ->get(['id', 'title', 'cover_image', 'listens', 'genre_id', 'duration'])
                ->map(function ($s) { $s->period_plays = (int)$s->listens; return $s; });

        $top10SongsCollection = (clone $songsBase)
            ->whereIn('id', $top10SongIds)
            ->get(['id', 'title', 'listens'])
            ->map(function ($s) use ($periodPlaysBySong) {
                $s->period_plays = (int)($periodPlaysBySong->get($s->id, 0));
                return $s;
            })
            ->sortByDesc('period_plays')
            ->values();

        $top10 = ($top10SongsCollection->isNotEmpty() ? $top10SongsCollection
            : (clone $songsBase)->orderByDesc('listens')->take(10)->get(['id', 'title', 'listens'])
                ->map(function ($s) { $s->period_plays = (int)$s->listens; return $s; }))
            ->map(fn($s) => [
                'title'   => \Illuminate\Support\Str::limit($s->title, 18),
                'listens' => (int)$s->period_plays,
            ]);

        // ── Thính giả trong kỳ (lọc theo listening_histories.listened_at) ────
        $listenerRows = DB::table('listening_histories')
            ->join('songs', 'listening_histories.song_id', '=', 'songs.id')
            ->join('users', 'listening_histories.user_id', '=', 'users.id')
            ->where('songs.artist_profile_id', $artistProfileId)
            ->where('songs.deleted', false)
            ->whereBetween('listening_histories.listened_at', [$dateFrom, $dateTo])
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

        // ── Phân bố giờ nghe trong kỳ ────────────────────────────────────────
        $hourlyRaw = DB::table('listening_histories')
            ->join('songs', 'listening_histories.song_id', '=', 'songs.id')
            ->where('songs.artist_profile_id', $artistProfileId)
            ->where('songs.deleted', false)
            ->whereBetween('listening_histories.listened_at', [$dateFrom, $dateTo])
            ->select(DB::raw('HOUR(listening_histories.listened_at) as hr'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('hr')->pluck('cnt', 'hr');
        $hourlyDist = [];
        for ($h = 0; $h < 24; $h++) $hourlyDist[] = (int)($hourlyRaw->get($h, 0));

        // ── Trạng thái bài hát (tổng — không lọc ngày, là context tĩnh) ──────
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
        $artist = \App\Models\User::query()
            ->with('artistProfile:id,user_id')
            ->findOrFail((int) Auth::id());
        $artistProfileId = (int) ($artist?->artistProfile?->id ?? 0);
        $song1Id  = (int)$request->input('song1');
        $song2Id  = (int)$request->input('song2');
        $days     = min((int)$request->input('days', 30), 90);

        if ($artistProfileId <= 0) {
            return response()->json(['error' => 'Artist profile not found'], 403);
        }

        if (!$song1Id || !$song2Id) {
            return response()->json(['error' => 'Cần chọn 2 bài hát'], 422);
        }

        // Xác minh bài hát thuộc artist
        $ownedIds = Song::where('artist_profile_id', $artistProfileId)
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
        $artist = \App\Models\User::query()
            ->with('artistProfile:id,user_id')
            ->findOrFail((int) Auth::id());
        $artistProfileId = (int) ($artist?->artistProfile?->id ?? 0);
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $period = $request->input('period', '30d');

        if ($artistProfileId <= 0) {
            abort(403, 'Artist profile not found.');
        }

        $songIds  = Song::where('artist_profile_id', $artistProfileId)->where('deleted', false)->pluck('id');

        // Daily totals
        $dailyRaw = SongDailyStat::whereIn('song_id', $songIds)
            ->whereBetween('stat_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->select('stat_date', DB::raw('SUM(play_count) as total'))
            ->groupBy('stat_date')->orderBy('stat_date')
            ->pluck('total', 'stat_date');

        // Top songs
        $topSongs = Song::where('artist_profile_id', $artistProfileId)->where('deleted', false)
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
        $artist = \App\Models\User::query()
            ->with('artistProfile:id,user_id')
            ->findOrFail((int) Auth::id());
        $artistProfileId = (int) ($artist?->artistProfile?->id ?? 0);
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        if ($artistProfileId <= 0) {
            abort(403, 'Artist profile not found.');
        }

        $songIds = Song::where('artist_profile_id', $artistProfileId)->where('deleted', false)->pluck('id');

        $dailyRaw = SongDailyStat::whereIn('song_id', $songIds)
            ->whereBetween('stat_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->select('stat_date', DB::raw('SUM(play_count) as total'))
            ->groupBy('stat_date')->orderBy('stat_date')->get();

        $topSongs = Song::where('artist_profile_id', $artistProfileId)->where('deleted', false)
            ->where('status', 'published')->orderByDesc('listens')->take(10)
            ->get(['title', 'listens', 'status']);

        $totalInPeriod = $dailyRaw->sum('total');
        $totalSongs    = Song::where('artist_profile_id', $artistProfileId)->where('deleted', false)->count();

        $pdf = Pdf::loadView('artist.stats.pdf', compact(
            'artist', 'dateFrom', 'dateTo',
            'dailyRaw', 'topSongs', 'totalInPeriod', 'totalSongs',
        ))->setPaper('a4', 'portrait');

        $filename = 'bao-cao-' . ($artist->artist_name ?: $artist->name) . '-' . now()->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }
}
