@extends('layouts.artist')
@section('title', 'Thống kê & Báo cáo – Artist Studio')
@section('page-title', 'Thống kê')
@section('page-subtitle', 'Phân tích hiệu suất & thính giả kênh nhạc của bạn')

@push('styles')
<style>
/* ─── Scoped stats styles ─────────────────────────────────────────────────── */
.st-card {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 16px;
    padding: 1.35rem 1.5rem;
    transition: border-color .2s, transform .2s, box-shadow .2s;
}
.st-card:hover {
    border-color: rgba(168,85,247,.28);
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(0,0,0,.25);
}
.st-icon {
    width: 46px; height: 46px; border-radius: 12px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 1.15rem;
}
.st-val {
    font-size: 1.85rem; font-weight: 800; color: #fff; line-height: 1;
    margin: .3rem 0 .15rem;
}
.st-label {
    font-size: .7rem; font-weight: 700; letter-spacing: .07em;
    text-transform: uppercase; color: #64748b;
}
.st-sub { font-size: .72rem; color: #475569; margin-top: .15rem; }
.st-up   { color: #34d399; font-weight: 600; }
.st-down { color: #f87171; font-weight: 600; }

/* Period tabs */
.period-tab {
    padding: 4px 12px; font-size: .75rem; font-weight: 600;
    border-radius: 8px; border: 1px solid transparent; cursor: pointer;
    color: #64748b; background: transparent; transition: all .15s;
}
.period-tab.active, .period-tab:hover {
    background: rgba(168,85,247,.18);
    border-color: rgba(168,85,247,.35);
    color: #c084fc;
}

/* Chart container */
.st-chart-card {
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 16px; padding: 1.5rem;
}
.st-chart-title {
    font-size: .82rem; font-weight: 700; color: #94a3b8;
    text-transform: uppercase; letter-spacing: .06em; margin-bottom: 1rem;
}

/* Top song item */
.ts-item {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,.05);
}
.ts-item:last-child { border-bottom: none; padding-bottom: 0; }
.ts-rank { width: 24px; font-size: .85rem; font-weight: 800; color: #334155; text-align: center; }
.ts-rank.gold   { color: #fbbf24; }
.ts-rank.silver { color: #94a3b8; }
.ts-rank.bronze { color: #b45309; }
.ts-cover { width: 42px; height: 42px; border-radius: 8px; object-fit: cover;
    border: 1px solid rgba(255,255,255,.08); flex-shrink: 0; }
.ts-bar-wrap { flex: 1; min-width: 0; }
.ts-bar-bg { height: 4px; background: rgba(255,255,255,.06); border-radius: 2px; margin-top: 5px; }
.ts-bar { height: 4px; border-radius: 2px;
    background: linear-gradient(90deg, #7c3aed, #a855f7); transition: width .6s ease; }

/* Audience donut legend */
.aud-legend { display: flex; flex-direction: column; gap: 6px; }
.aud-legend-item { display:flex; align-items: center; gap: 8px; font-size: .78rem; }
.aud-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

/* Hourly heatrow */
.hr-cell {
    flex: 1; height: 32px; border-radius: 4px;
    min-width: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: .58rem; color: rgba(255,255,255,.5);
    transition: transform .15s;
}
.hr-cell:hover { transform: scaleY(1.2); }

/* ─── Filter Bar ─── */
.sf-filter-bar {
    background: rgba(255,255,255,.025);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 14px;
    padding: .9rem 1.25rem;
    display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
}
.sf-filter-bar .form-select, .sf-filter-bar .form-control {
    background: rgba(30,41,59,.8) !important;
    border: 1px solid rgba(255,255,255,.12) !important;
    color: #e2e8f0 !important;
    border-radius: 9px; font-size: .8rem; padding: .45rem .85rem;
}
.sf-filter-bar .form-select:focus, .sf-filter-bar .form-control:focus {
    border-color: rgba(168,85,247,.5) !important;
    box-shadow: 0 0 0 3px rgba(168,85,247,.15) !important;
}
.sf-period-active { color: #a855f7; font-weight: 700; font-size: .78rem; margin-left: auto; }

/* ─── Export buttons ─── */
.btn-export {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: .78rem; font-weight: 600; padding: .45rem .95rem;
    border-radius: 9px; border: 1px solid; cursor: pointer; text-decoration: none;
    transition: all .18s;
}
.btn-export-excel {
    background: rgba(34,197,94,.1); border-color: rgba(34,197,94,.3); color: #4ade80;
}
.btn-export-excel:hover {
    background: rgba(34,197,94,.2); color: #86efac;
}
.btn-export-pdf {
    background: rgba(239,68,68,.1); border-color: rgba(239,68,68,.3); color: #f87171;
}
.btn-export-pdf:hover {
    background: rgba(239,68,68,.2); color: #fca5a5;
}

/* ─── Compare Panel ─── */
.sf-compare-panel {
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(168,85,247,.2);
    border-radius: 16px; padding: 1.4rem 1.5rem;
}
.sf-compare-select {
    background: rgba(30,41,59,.8) !important;
    border: 1px solid rgba(255,255,255,.12) !important;
    color: #e2e8f0 !important;
    border-radius: 9px; font-size: .8rem;
}
.compare-legend-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block;}

/* ─── Forecast Badge ─── */
.forecast-badge {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(251,191,36,.1); border: 1px solid rgba(251,191,36,.25);
    color: #fbbf24; font-size: .72rem; font-weight: 600;
    padding: 3px 10px; border-radius: 20px;
}
</style>
@endpush

@section('content')
@php
    $now = now();
    $maxListens = $topSongs->max('listens') ?: 1;
@endphp

{{-- ── Header bar: Filter + Export ───────────────────────────────────────── --}}
<div class="mb-4">
    <form method="GET" action="{{ route('artist.stats.index') }}" class="sf-filter-bar" id="statsFilterForm" hx-boost="false">
        <span class="text-muted" style="font-size:.78rem;font-weight:600;white-space:nowrap">
            <i class="fa-solid fa-filter me-1" style="color:#a855f7"></i>Khoảng thời gian:
        </span>

        {{-- Quick period pills --}}
        @foreach(['7d'=>'7 ngày','30d'=>'30 ngày','this_month'=>'Tháng này','last_month'=>'Tháng trước','this_quarter'=>'Quý này','custom'=>'Tùy chọn'] as $val=>$label)
        <button type="submit" name="period" value="{{ $val }}"
                class="period-tab {{ $period === $val ? 'active' : '' }}">
            {{ $label }}
        </button>
        @endforeach

        {{-- Custom date range (shown only when period=custom) --}}
        <div id="customRangeWrap" class="d-flex align-items-center gap-2 {{ $period !== 'custom' ? 'd-none' : '' }}" style="flex-wrap:wrap">
            <input type="date" name="date_from" class="form-control" style="width:140px"
                   value="{{ $period === 'custom' ? request('date_from', $dateFrom->toDateString()) : '' }}"
                   max="{{ now()->toDateString() }}">
            <span class="text-muted" style="font-size:.75rem">đến</span>
            <input type="date" name="date_to" class="form-control" style="width:140px"
                   value="{{ $period === 'custom' ? request('date_to', $dateTo->toDateString()) : '' }}"
                   max="{{ now()->toDateString() }}">
            <button type="submit" class="btn btn-sm" style="background:rgba(168,85,247,.2);color:#c084fc;border:1px solid rgba(168,85,247,.3);font-size:.78rem;border-radius:8px">
                <i class="fa-solid fa-check me-1"></i>Áp dụng
            </button>
        </div>

        <span class="sf-period-active ms-auto d-none d-md-inline">
            <i class="fa-solid fa-calendar-days me-1"></i>
            {{ $dateFrom->format('d/m/Y') }} – {{ $dateTo->format('d/m/Y') }}
        </span>

        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="window.location.reload()" title="Làm mới">
            <i class="fa-solid fa-rotate-right"></i>
        </button>
    </form>

    {{-- Export buttons --}}
    <div class="d-flex align-items-center gap-2 mt-3 flex-wrap">
        <span class="text-muted" style="font-size:.75rem"><i class="fa-solid fa-download me-1"></i>Xuất báo cáo:</span>
        <a href="{{ route('artist.stats.export.excel', ['period'=>$period,'date_from'=>request('date_from'),'date_to'=>request('date_to')]) }}"
           class="btn-export btn-export-excel" hx-boost="false">
            <i class="fa-solid fa-file-excel"></i> Excel
        </a>
        <a href="{{ route('artist.stats.export.pdf', ['period'=>$period,'date_from'=>request('date_from'),'date_to'=>request('date_to')]) }}"
           class="btn-export btn-export-pdf" hx-boost="false">
            <i class="fa-solid fa-file-pdf"></i> PDF
        </a>
        <span class="text-muted" style="font-size:.7rem">· Báo cáo khoảng đang xem</span>
    </div>
</div>

{{-- ── ROW 1: KPI cards ───────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Tổng lượt nghe --}}
    <div class="col-6 col-lg-3">
        <div class="st-card h-100">
            <div class="d-flex align-items-start justify-content-between gap-2">
                <div class="min-w-0">
                    <div class="st-label">Tổng lượt nghe</div>
                    <div class="st-val">{{ number_format($totalListens) }}</div>
                    <div class="st-sub">
                        <span class="st-up"><i class="fa-solid fa-arrow-up me-1" style="font-size:.65rem"></i>{{ number_format($todayListens) }}</span>
                        hôm nay
                    </div>
                </div>
                <div class="st-icon" style="background:rgba(59,130,246,.15);color:#60a5fa;">
                    <i class="fa-solid fa-headphones"></i>
                </div>
            </div>
            <div class="mt-2 d-flex gap-2 flex-wrap">
                <span class="st-sub"><span class="text-white fw-semibold">{{ number_format($weekListens) }}</span> tuần</span>
                <span class="text-muted" style="font-size:.7rem">·</span>
                <span class="st-sub"><span class="text-white fw-semibold">{{ number_format($monthListens) }}</span> tháng</span>
            </div>
        </div>
    </div>

    {{-- Bài hát --}}
    <div class="col-6 col-lg-3">
        <div class="st-card h-100">
            <div class="d-flex align-items-start justify-content-between gap-2">
                <div>
                    <div class="st-label">Bài hát</div>
                    <div class="st-val">{{ $totalSongs }}</div>
                    <div class="st-sub">
                        <i class="fa-solid fa-circle-check me-1" style="color:#34d399;font-size:.65rem"></i>
                        {{ $publishedSongs }} đã xuất bản
                    </div>
                </div>
                <div class="st-icon" style="background:rgba(168,85,247,.15);color:#c084fc;">
                    <i class="fa-solid fa-music"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="st-sub"><span class="text-white fw-semibold">{{ $totalAlbums }}</span> album</span>
            </div>
        </div>
    </div>

    {{-- Người theo dõi --}}
    <div class="col-6 col-lg-3">
        <div class="st-card h-100">
            <div class="d-flex align-items-start justify-content-between gap-2">
                <div>
                    <div class="st-label">Người theo dõi</div>
                    <div class="st-val">{{ number_format($totalFollowers) }}</div>
                    <div class="st-sub">
                        @if($weekFollowers > 0)
                            <span class="st-up"><i class="fa-solid fa-arrow-trend-up me-1" style="font-size:.65rem"></i>+{{ $weekFollowers }}</span> tuần này
                        @else
                            <span class="text-muted">Chưa có follow mới</span>
                        @endif
                    </div>
                </div>
                <div class="st-icon" style="background:rgba(16,185,129,.15);color:#34d399;">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="st-sub"><span class="text-white fw-semibold">+{{ $monthFollowers }}</span> tháng này</span>
            </div>
        </div>
    </div>

    {{-- Thính giả độc lập --}}
    <div class="col-6 col-lg-3">
        <div class="st-card h-100">
            <div class="d-flex align-items-start justify-content-between gap-2">
                <div>
                    <div class="st-label">Thính giả</div>
                    <div class="st-val">{{ number_format($totalListeners) }}</div>
                    <div class="st-sub">Người nghe độc lập</div>
                </div>
                <div class="st-icon" style="background:rgba(245,158,11,.15);color:#fbbf24;">
                    <i class="fa-solid fa-ear-listen"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="st-sub">Từ lịch sử nghe nhạc</span>
            </div>
        </div>
    </div>
</div>

{{-- ── ROW 2a: Growth chart (filter-aware) + Dự báo ───────────────────────── --}}
<div class="row g-3 mb-3">
    <div class="col-xl-8">
        <div class="st-chart-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div class="st-chart-title mb-0">
                    <i class="fa-solid fa-chart-area me-2" style="color:#7c3aed"></i>
                    Lượt nghe ({{ $dateFrom->format('d/m') }} – {{ $dateTo->format('d/m/Y') }})
                </div>
                <span class="forecast-badge" title="Dự báo tuyến tính 7 ngày tới">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Dự báo 7 ngày
                </span>
            </div>
            <div style="height:260px;position:relative;">
                <canvas id="growthChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="st-chart-card h-100">
            <div class="st-chart-title">
                <i class="fa-solid fa-users me-2" style="color:#34d399"></i>Tăng trưởng follows
            </div>
            <div style="height:260px;position:relative;">
                <canvas id="followChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ── ROW 2b: So sánh 2 bài hát ──────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="sf-compare-panel">
            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                <div class="st-chart-title mb-0">
                    <i class="fa-solid fa-code-compare me-2" style="color:#a855f7"></i>So sánh 2 bài hát
                </div>
                <span class="text-muted" style="font-size:.72rem">· Cùng 1 biểu đồ để thấy rõ hiệu suất</span>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-sm-4">
                    <label class="text-muted mb-1" style="font-size:.72rem;font-weight:600">BÀI HÁT 1
                        <span class="compare-legend-dot ms-1" style="background:#818cf8"></span>
                    </label>
                    <select id="compareSong1" class="form-select sf-compare-select">
                        <option value="">-- Chọn bài hát --</option>
                        @foreach($allSongsForCompare as $s)
                        <option value="{{ $s->id }}">{{ \Illuminate\Support\Str::limit($s->title, 40) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4">
                    <label class="text-muted mb-1" style="font-size:.72rem;font-weight:600">BÀI HÁT 2
                        <span class="compare-legend-dot ms-1" style="background:#f472b6"></span>
                    </label>
                    <select id="compareSong2" class="form-select sf-compare-select">
                        <option value="">-- Chọn bài hát --</option>
                        @foreach($allSongsForCompare as $s)
                        <option value="{{ $s->id }}">{{ \Illuminate\Support\Str::limit($s->title, 40) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-2">
                    <label class="text-muted mb-1" style="font-size:.72rem;font-weight:600">SỐ NGÀY</label>
                    <select id="compareDays" class="form-select sf-compare-select">
                        <option value="7">7 ngày</option>
                        <option value="14">14 ngày</option>
                        <option value="30" selected>30 ngày</option>
                        <option value="60">60 ngày</option>
                        <option value="90">90 ngày</option>
                    </select>
                </div>
                <div class="col-sm-2 d-flex align-items-end">
                    <button id="compareBtn" class="btn w-100" onclick="loadCompareChart()"
                        style="background:rgba(168,85,247,.2);border:1px solid rgba(168,85,247,.3);color:#c084fc;font-size:.8rem;border-radius:9px">
                        <i class="fa-solid fa-chart-line me-1"></i>So sánh
                    </button>
                </div>
            </div>

            <div id="compareChartWrap" style="height:240px;position:relative;display:none">
                <canvas id="compareChart"></canvas>
            </div>
            <div id="compareEmpty" class="text-center text-muted py-4" style="font-size:.85rem">
                <i class="fa-solid fa-code-compare fa-2x d-block mb-2 opacity-20"></i>
                Chọn 2 bài hát và nhấn "So sánh" để xem biểu đồ
            </div>
        </div>
    </div>
</div>

{{-- ── ROW 3: Top songs + Audience ─────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Top 5 bài hát --}}
    <div class="col-lg-5">
        <div class="st-chart-card h-100">
            <div class="st-chart-title">
                <i class="fa-solid fa-trophy me-2" style="color:#fbbf24"></i>Top 5 bài hát phổ biến nhất
            </div>
            @forelse($topSongs as $i => $song)
            @php
                $rankClass = match($i) { 0 => 'gold', 1 => 'silver', 2 => 'bronze', default => '' };
                $pct = $maxListens > 0 ? round($song->listens / $maxListens * 100) : 0;
            @endphp
            <div class="ts-item">
                <div class="ts-rank {{ $rankClass }}">{{ $i + 1 }}</div>
                <img src="{{ $song->getCoverUrl() }}" alt="{{ $song->title }}" class="ts-cover">
                <div class="ts-bar-wrap">
                    <div class="text-white fw-semibold text-truncate" style="font-size:.85rem;">{{ $song->title }}</div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted" style="font-size:.72rem;">{{ number_format((int)$song->listens) }} lượt</span>
                        @if($song->genre)
                            <span style="font-size:.65rem;color:#a855f7">{{ $song->genre->name }}</span>
                        @endif
                    </div>
                    <div class="ts-bar-bg">
                        <div class="ts-bar" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                <a href="{{ route('artist.songs.show', $song) }}"
                   style="color:#334155;font-size:.75rem;text-decoration:none;flex-shrink:0"
                   title="Xem chi tiết">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
            </div>
            @empty
            <div class="text-center text-muted py-4">
                <i class="fa-solid fa-music fa-2x d-block mb-2 opacity-20"></i>
                Chưa có bài hát nào
            </div>
            @endforelse
        </div>
    </div>

    {{-- Top 10 bar chart --}}
    <div class="col-lg-7">
        <div class="st-chart-card h-100">
            <div class="st-chart-title">
                <i class="fa-solid fa-chart-bar me-2" style="color:#a855f7"></i>Lượt nghe theo bài hát (top 10)
            </div>
            <div style="height:280px;position:relative;">
                <canvas id="top10Chart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ── ROW 4: Audience charts ──────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Giới tính --}}
    <div class="col-md-4">
        <div class="st-chart-card h-100">
            <div class="st-chart-title">
                <i class="fa-solid fa-venus-mars me-2" style="color:#ec4899"></i>Giới tính thính giả
            </div>
            <div style="height:180px;position:relative;">
                <canvas id="genderChart"></canvas>
            </div>
            <div class="aud-legend mt-3">
                @php
                    $gColors = ['Nam'=>'#60a5fa','Nữ'=>'#f472b6','Khác'=>'#94a3b8'];
                @endphp
                @foreach($genderDist as $label => $val)
                <div class="aud-legend-item">
                    <div class="aud-dot" style="background:{{ $gColors[$label] ?? '#64748b' }}"></div>
                    <span class="text-muted">{{ $label }}</span>
                    <span class="ms-auto text-white fw-semibold">{{ $val }}</span>
                    <span class="text-muted" style="font-size:.7rem;min-width:36px;text-align:right">
                        @if($totalListeners > 0){{ round($val/$totalListeners*100) }}%@endif
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Độ tuổi --}}
    <div class="col-md-4">
        <div class="st-chart-card h-100">
            <div class="st-chart-title">
                <i class="fa-solid fa-cake-candles me-2" style="color:#fbbf24"></i>Độ tuổi thính giả
            </div>
            <div style="height:180px;position:relative;">
                <canvas id="ageChart"></canvas>
            </div>
            <div class="aud-legend mt-3">
                @php
                    $ageColors = ['<18'=>'#7dd3fc','18-24'=>'#a78bfa','25-34'=>'#34d399','35-44'=>'#fbbf24','>44'=>'#fb923c','N/A'=>'#475569'];
                @endphp
                @foreach($ageDist as $label => $val)
                @if($val > 0)
                <div class="aud-legend-item">
                    <div class="aud-dot" style="background:{{ $ageColors[$label] ?? '#64748b' }}"></div>
                    <span class="text-muted">{{ $label }}</span>
                    <span class="ms-auto text-white fw-semibold">{{ $val }}</span>
                    <span class="text-muted" style="font-size:.7rem;min-width:36px;text-align:right">
                        @if($totalListeners > 0){{ round($val/$totalListeners*100) }}%@endif
                    </span>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- Nguồn phát --}}
    <div class="col-md-4">
        <div class="st-chart-card h-100">
            <div class="st-chart-title">
                <i class="fa-solid fa-tower-broadcast me-2" style="color:#34d399"></i>Nguồn phát nhạc
            </div>
            <div style="height:180px;position:relative;">
                <canvas id="sourceChart"></canvas>
            </div>
            <div class="aud-legend mt-3">
                @php
                    $srcLabels = ['stream'=>'Nghe trực tuyến','download'=>'Tải offline','playlist'=>'Playlist','radio'=>'Radio'];
                    $srcColors = ['stream'=>'#7c3aed','download'=>'#0ea5e9','playlist'=>'#f59e0b','radio'=>'#10b981'];
                @endphp
                @foreach($sourceDist as $src => $cnt)
                <div class="aud-legend-item">
                    <div class="aud-dot" style="background:{{ $srcColors[$src] ?? '#64748b' }}"></div>
                    <span class="text-muted">{{ $srcLabels[$src] ?? ucfirst($src) }}</span>
                    <span class="ms-auto text-white fw-semibold">{{ number_format($cnt) }}</span>
                </div>
                @endforeach
                @if($sourceDist->isEmpty())
                    <div class="text-muted text-center" style="font-size:.78rem">Chưa có dữ liệu</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── ROW 5: Hourly heatmap + Status dist ────────────────────────────────── --}}
<div class="row g-3">

    {{-- Giờ nghe nhiều nhất --}}
    <div class="col-lg-7">
        <div class="st-chart-card">
            <div class="st-chart-title">
                <i class="fa-regular fa-clock me-2" style="color:#60a5fa"></i>Phân bố lượt nghe theo giờ trong ngày
            </div>
            @php
                $maxHour = max($hourlyDist) ?: 1;
            @endphp
            <div class="d-flex align-items-end gap-1" style="height:80px;">
                @foreach($hourlyDist as $h => $cnt)
                @php
                    $pct = round($cnt / $maxHour * 100);
                    $hh = str_pad($h, 2, '0', STR_PAD_LEFT) . 'h';
                    // Color by intensity
                    $alpha = max(0.08, $pct / 100);
                @endphp
                <div class="flex-grow-1 d-flex flex-column align-items-center" title="{{ $hh }}: {{ number_format($cnt) }} lượt">
                    <div style="width:100%;height:{{ max(4, $pct) }}%;min-height:4px;max-height:68px;background:rgba(168,85,247,{{ $alpha }});border-radius:3px 3px 0 0;transition:height .3s;"></div>
                </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-between mt-1">
                <span class="text-muted" style="font-size:.65rem">0h</span>
                <span class="text-muted" style="font-size:.65rem">6h</span>
                <span class="text-muted" style="font-size:.65rem">12h</span>
                <span class="text-muted" style="font-size:.65rem">18h</span>
                <span class="text-muted" style="font-size:.65rem">23h</span>
            </div>
        </div>
    </div>

    {{-- Phân bố trạng thái bài hát --}}
    <div class="col-lg-5">
        <div class="st-chart-card h-100">
            <div class="st-chart-title">
                <i class="fa-solid fa-layer-group me-2" style="color:#a855f7"></i>Trạng thái bài hát
            </div>
            @php
                $stLabels = [
                    'published' => ['Đã xuất bản', '#34d399', 'rgba(52,211,153,.12)'],
                    'pending'   => ['Chờ duyệt',   '#fbbf24', 'rgba(251,191,36,.12)'],
                    'draft'     => ['Bản nháp',     '#94a3b8', 'rgba(148,163,184,.1)'],
                    'hidden'    => ['Đã ẩn',        '#f87171', 'rgba(248,113,113,.1)'],
                    'scheduled' => ['Hẹn giờ',      '#7dd3fc', 'rgba(125,211,252,.1)'],
                ];
            @endphp
            <div class="d-flex flex-column gap-2">
                @forelse($statusDist as $row)
                @php [$lbl,$color,$bg] = $stLabels[$row->status] ?? [ucfirst($row->status),'#94a3b8','rgba(148,163,184,.1)']; @endphp
                <div class="d-flex align-items-center gap-3 p-2 rounded-3" style="background:{{ $bg }};border:1px solid {{ $color }}22;">
                    <div class="fw-bold" style="color:{{ $color }};font-size:1.2rem;min-width:28px;text-align:center;">{{ $row->count }}</div>
                    <div>
                        <div style="color:{{ $color }};font-size:.78rem;font-weight:600;">{{ $lbl }}</div>
                        <div class="text-muted" style="font-size:.7rem;">{{ number_format((int)$row->total_listens) }} lượt nghe</div>
                    </div>
                </div>
                @empty
                <div class="text-muted text-center py-3">Chưa có bài hát</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Shared defaults ───────────────────────────────────────────────────── //
    Chart.defaults.color = '#475569';
    Chart.defaults.font.family = "'Quicksand', sans-serif";
    Chart.defaults.font.size = 11;

    const gridColor   = 'rgba(255,255,255,.05)';
    const purpleMain  = '#a855f7';
    const purpleDark  = '#7c3aed';
    const greenMain   = '#34d399';

    // ── 1. Growth chart (lượt nghe 30 ngày) ──────────────────────────────── //
    const allDays   = @json($growthDays);
    const allVals   = @json($growthValues);
    const followVals = @json($followValues);

    function buildGrowthData(days, vals) {
        return {
            labels: days,
            datasets: [{
                label: 'Lượt nghe',
                data: vals,
                borderColor: purpleMain,
                backgroundColor: 'rgba(168,85,247,.08)',
                borderWidth: 2, tension: 0.4, fill: true,
                pointRadius: 2, pointHoverRadius: 5,
                pointBackgroundColor: purpleMain,
            }]
        };
    }

    // Forecast data from server
    const forecastDays   = @json($forecastDays);
    const forecastValues = @json($forecastValues);

    const growthCtx = document.getElementById('growthChart').getContext('2d');
    const growthChart = new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: [...allDays, ...forecastDays],
            datasets: [
                {
                    label: 'Lượt nghe thực tế',
                    data: allVals.map((v, i) => ({x: i, y: v})),
                    // fill with real indices
                    borderColor: purpleMain,
                    backgroundColor: function(ctx) {
                        const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 260);
                        g.addColorStop(0, 'rgba(168,85,247,.35)');
                        g.addColorStop(1, 'rgba(168,85,247,0)');
                        return g;
                    },
                    fill: true, tension: 0.35, pointRadius: 2, borderWidth: 2,
                    // Only show actual data points (null for forecast slots)
                    data: [...allVals.map(v => v), ...forecastValues.map(() => null)],
                },
                {
                    label: 'Dự báo 7 ngày tới',
                    data: [...allVals.map(() => null), ...forecastValues],
                    borderColor: '#fbbf24',
                    backgroundColor: 'rgba(251,191,36,.08)',
                    fill: true, tension: 0.35, pointRadius: 3, borderWidth: 2,
                    borderDash: [5, 4],
                    pointStyle: 'triangle',
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true, position: 'top', align: 'end',
                    labels: { color: '#94a3b8', font: { size: 11 }, boxWidth: 12, pointStyle: 'circle' }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            if (ctx.parsed.y === null) return null;
                            return ' ' + ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString('vi-VN') + ' lượt';
                        }
                    }
                }
            },
            scales: {
                x: { grid: { color: gridColor }, ticks: { maxTicksLimit: 10 } },
                y: { grid: { color: gridColor }, beginAtZero: true,
                     ticks: { callback: v => v >= 1e6 ? (v/1e6).toFixed(1)+'M' : v >= 1e3 ? (v/1e3).toFixed(0)+'K' : v }
                }
            }
        }
    });


    // ── 2. Follow chart ───────────────────────────────────────────────────── //
    new Chart(document.getElementById('followChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: allDays,
            datasets: [{
                label: 'Follows mới',
                data: followVals,
                backgroundColor: 'rgba(52,211,153,.6)',
                borderColor: greenMain, borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ' +' + ctx.parsed.y + ' follows' } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } },
                y: { grid: { color: gridColor }, beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // ── 3. Top 10 bar chart ───────────────────────────────────────────────── //
    const top10 = @json($top10);
    new Chart(document.getElementById('top10Chart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: top10.map(d => d.title),
            datasets: [{
                label: 'Lượt nghe',
                data: top10.map(d => d.listens),
                backgroundColor: function(ctx) {
                    const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                    g.addColorStop(0, 'rgba(168,85,247,.85)');
                    g.addColorStop(1, 'rgba(124,58,237,.3)');
                    return g;
                },
                borderColor: purpleMain, borderWidth: 1, borderRadius: 6,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.parsed.x.toLocaleString('vi-VN') + ' lượt'
                    }
                }
            },
            scales: {
                x: { grid: { color: gridColor },
                     ticks: { callback: v => v >= 1e6 ? (v/1e6).toFixed(1)+'M' : v >= 1e3 ? (v/1e3).toFixed(0)+'K' : v }
                },
                y: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });

    // ── 4. Gender doughnut ────────────────────────────────────────────────── //
    const genderDist = @json($genderDist);
    new Chart(document.getElementById('genderChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(genderDist),
            datasets: [{
                data: Object.values(genderDist),
                backgroundColor: ['#60a5fa', '#f472b6', '#94a3b8'],
                borderWidth: 0, hoverOffset: 6,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed } }
            }
        }
    });

    // ── 5. Age bar chart ──────────────────────────────────────────────────── //
    const ageDist  = @json($ageDist);
    const ageColors = { '<18':'#7dd3fc','18-24':'#a78bfa','25-34':'#34d399','35-44':'#fbbf24','>44':'#fb923c','N/A':'#475569' };
    new Chart(document.getElementById('ageChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: Object.keys(ageDist),
            datasets: [{
                data: Object.values(ageDist),
                backgroundColor: Object.keys(ageDist).map(k => ageColors[k] || '#64748b'),
                borderWidth: 0, borderRadius: 5,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { color: gridColor }, beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // ── 6. Source pie ─────────────────────────────────────────────────────── //
    const sourceDist   = @json($sourceDist);
    const srcLabelMap  = { stream:'Nghe trực tuyến', download:'Tải offline', playlist:'Playlist', radio:'Radio' };
    const srcColorList = ['#7c3aed','#0ea5e9','#f59e0b','#10b981','#f43f5e','#6366f1'];
    const srcKeys      = Object.keys(sourceDist);
    new Chart(document.getElementById('sourceChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: srcKeys.map(k => srcLabelMap[k] || k),
            datasets: [{
                data: Object.values(sourceDist),
                backgroundColor: srcColorList.slice(0, srcKeys.length),
                borderWidth: 0, hoverOffset: 6,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '60%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed.toLocaleString() } }
            }
        }
    });


    // ── Custom date range toggle ─────────────────────────────────────────── //
    document.querySelectorAll('button[name="period"][value="custom"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const wrap = document.getElementById('customRangeWrap');
            if (wrap && wrap.classList.contains('d-none')) {
                e.preventDefault();
                wrap.classList.remove('d-none');
            }
        });
    });

});

// ── Compare chart (async) ─────────────────────────────────────────────────── //
let compareChartInstance = null;

async function loadCompareChart() {
    const s1   = document.getElementById('compareSong1').value;
    const s2   = document.getElementById('compareSong2').value;
    const days = document.getElementById('compareDays').value;
    const btn  = document.getElementById('compareBtn');

    if (!s1 || !s2) {
        alert('Vui lòng chọn đủ 2 bài hát!');
        return;
    }
    if (s1 === s2) {
        alert('Vui lòng chọn 2 bài hát khác nhau!');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Đang tải...';

    try {
        const url = `{{ route('artist.stats.compare') }}?song1=${s1}&song2=${s2}&days=${days}`;
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) throw new Error('Lỗi ' + res.status);
        const data = await res.json();

        document.getElementById('compareChartWrap').style.display = 'block';
        document.getElementById('compareEmpty').style.display     = 'none';

        if (compareChartInstance) compareChartInstance.destroy();

        const ctx = document.getElementById('compareChart').getContext('2d');
        compareChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: data.song1.title,
                        data: data.song1.values,
                        borderColor: '#818cf8',
                        backgroundColor: 'rgba(129,140,248,.1)',
                        fill: true, tension: 0.35, pointRadius: 2, borderWidth: 2,
                    },
                    {
                        label: data.song2.title,
                        data: data.song2.values,
                        borderColor: '#f472b6',
                        backgroundColor: 'rgba(244,114,182,.08)',
                        fill: true, tension: 0.35, pointRadius: 2, borderWidth: 2,
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true, position: 'top',
                        labels: { color: '#94a3b8', font: { size: 11 }, boxWidth: 12 }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ' + ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString('vi-VN') + ' lượt'
                        }
                    }
                },
                scales: {
                    x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { maxTicksLimit: 10 } },
                    y: { grid: { color: 'rgba(255,255,255,.05)' }, beginAtZero: true,
                         ticks: { callback: v => v >= 1e3 ? (v/1e3).toFixed(0)+'K' : v }
                    }
                }
            }
        });
    } catch(err) {
        alert('Không thể tải dữ liệu so sánh: ' + err.message);
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-chart-line me-1"></i>So sánh';
}
</script>
@endpush
