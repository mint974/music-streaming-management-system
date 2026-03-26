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
</style>
@endpush

@section('content')
@php
    $now = now();
    $maxListens = $topSongs->max('listens') ?: 1;
@endphp

{{-- ── Header bar ─────────────────────────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between mb-4 gap-3 flex-wrap">
    <div>
        <p class="text-muted small mb-0">
            <i class="fa-regular fa-clock me-1 opacity-50"></i>Cập nhật: {{ $now->format('H:i, d/m/Y') }}
        </p>
    </div>
    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="window.location.reload()">
        <i class="fa-solid fa-rotate-right me-1"></i>Làm mới
    </button>
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

{{-- ── ROW 2: Growth chart + Follow chart ─────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="st-chart-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div class="st-chart-title mb-0">
                    <i class="fa-solid fa-chart-area me-2" style="color:#7c3aed"></i>Lượt nghe 30 ngày gần nhất
                </div>
                <div class="d-flex gap-1">
                    <button class="period-tab active" data-chart="growth" data-period="30">30N</button>
                    <button class="period-tab" data-chart="growth" data-period="7">7N</button>
                </div>
            </div>
            <div style="height:260px;position:relative;">
                <canvas id="growthChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="st-chart-card h-100">
            <div class="st-chart-title">
                <i class="fa-solid fa-users me-2" style="color:#34d399"></i>Tăng trưởng follows (30 ngày)
            </div>
            <div style="height:260px;position:relative;">
                <canvas id="followChart"></canvas>
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

    const growthCtx = document.getElementById('growthChart').getContext('2d');
    const growthChart = new Chart(growthCtx, {
        type: 'line',
        data: buildGrowthData(allDays, allVals),
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.parsed.y.toLocaleString('vi-VN') + ' lượt nghe'
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

    // Period tabs
    document.querySelectorAll('.period-tab[data-chart="growth"]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.period-tab[data-chart="growth"]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const n = parseInt(this.dataset.period);
            growthChart.data = buildGrowthData(allDays.slice(-n), allVals.slice(-n));
            growthChart.update();
        });
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

});
</script>
@endpush
