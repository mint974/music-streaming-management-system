@extends('layouts.artist')

@section('title', 'Tổng quan – Artist Studio')
@section('page-title', 'Tổng quan')
@section('page-subtitle', 'Artist Studio – Blue Wave Music')

@push('styles')
<style>
.dash-kpi {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 16px; padding: 1.25rem 1.4rem;
    transition: border-color .2s, transform .2s;
}
.dash-kpi:hover { border-color: rgba(168,85,247,.28); transform: translateY(-2px); }
.dash-kpi-val { font-size: 1.9rem; font-weight: 800; color: #fff; line-height: 1; margin: .3rem 0 .1rem; }
.dash-kpi-label { font-size: .68rem; font-weight: 700; letter-spacing:.07em; text-transform:uppercase; color: #475569; }
.dash-kpi-sub { font-size: .72rem; color: #475569; margin-top: .1rem; }
.kpi-icon { width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0; }

.quick-btn {
    display:flex; align-items:center; gap:12px; padding:14px 16px;
    border-radius:12px; text-decoration:none; transition:all .18s;
    border: 1px solid transparent;
}
.quick-btn:hover { transform: translateX(3px); }

.song-row-mini {
    display:flex; align-items:center; gap:10px; padding:9px 0;
    border-bottom: 1px solid rgba(255,255,255,.04);
}
.song-row-mini:last-child { border-bottom:none; padding-bottom:0; }
.song-thumb { width:38px;height:38px;border-radius:7px;object-fit:cover;
    border:1px solid rgba(255,255,255,.08);flex-shrink:0; }

.chart-wrap {
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.06);
    border-radius:16px; padding:1.35rem 1.5rem;
}

.status-pip {
    display:inline-block; width:7px; height:7px; border-radius:50%;
}

/* Trend indicator */
.trend-up   { color:#34d399; }
.trend-down { color:#f87171; }
</style>
@endpush

@section('content')
@php
    $user       = auth()->user();
    $artistName = $user->artist_name ?: $user->name;
    $isVerified = $user->artist_verified_at;
@endphp

{{-- ── Welcome banner ─────────────────────────────────────────────────────── --}}
<div class="mb-4 p-4 rounded-3"
     style="background:linear-gradient(135deg,rgba(168,85,247,.1) 0%,rgba(236,72,153,.05) 100%);border:1px solid rgba(168,85,247,.18);">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div style="width:50px;height:50px;border-radius:13px;background:rgba(168,85,247,.18);border:1px solid rgba(168,85,247,.3);display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:#c084fc;flex-shrink:0">
            <i class="fa-solid fa-microphone-lines"></i>
        </div>
        <div class="flex-grow-1">
            <h5 class="text-white fw-bold mb-1" style="font-size:1rem">
                Xin chào, {{ $artistName }}!
                @if($isVerified)
                    <i class="fa-solid fa-circle-check ms-1" style="color:#60a5fa;font-size:.88rem" title="Đã xác minh"></i>
                @endif
            </h5>
            <p class="text-muted mb-0" style="font-size:.82rem">
                @if($isVerified)
                    Tài khoản đã xác minh &nbsp;·&nbsp; {{ now()->format('d/m/Y') }}
                @else
                    Tài khoản đang hoạt động &nbsp;·&nbsp; Tick xanh chờ admin duyệt
                @endif
            </p>
        </div>
        @if($pendingSongs > 0)
        <a href="{{ route('artist.songs.index', ['status'=>'pending']) }}"
           class="btn btn-sm"
           style="background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.3);color:#fbbf24;font-size:.78rem;white-space:nowrap">
            <i class="fa-solid fa-clock me-1"></i>{{ $pendingSongs }} bài chờ duyệt
        </a>
        @endif
    </div>
</div>

{{-- ── KPI cards ────────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="dash-kpi">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="dash-kpi-label">Tổng lượt nghe</div>
                    <div class="dash-kpi-val">{{ number_format($totalListens) }}</div>
                    <div class="dash-kpi-sub">
                        @if($todayListens > 0)
                            <span class="trend-up"><i class="fa-solid fa-arrow-up" style="font-size:.6rem"></i> +{{ number_format($todayListens) }}</span> hôm nay
                        @else
                            Tổng tích lũy
                        @endif
                    </div>
                </div>
                <div class="kpi-icon" style="background:rgba(59,130,246,.14);color:#60a5fa;">
                    <i class="fa-solid fa-headphones"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="dash-kpi">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="dash-kpi-label">Bài hát</div>
                    <div class="dash-kpi-val">{{ $totalSongs }}</div>
                    <div class="dash-kpi-sub">
                        <i class="fa-solid fa-circle-check me-1" style="color:#34d399;font-size:.6rem"></i>{{ $publishedSongs }} xuất bản
                    </div>
                </div>
                <div class="kpi-icon" style="background:rgba(168,85,247,.14);color:#c084fc;">
                    <i class="fa-solid fa-music"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="dash-kpi">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="dash-kpi-label">Người theo dõi</div>
                    <div class="dash-kpi-val">{{ number_format($totalFollowers) }}</div>
                    <div class="dash-kpi-sub">
                        @if($newFollowers > 0)
                            <span class="trend-up"><i class="fa-solid fa-arrow-trend-up" style="font-size:.6rem"></i> +{{ $newFollowers }}</span> tuần này
                        @else
                            Tổng followers
                        @endif
                    </div>
                </div>
                <div class="kpi-icon" style="background:rgba(16,185,129,.14);color:#34d399;">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="dash-kpi">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="dash-kpi-label">Album</div>
                    <div class="dash-kpi-val">{{ $totalAlbums }}</div>
                    <div class="dash-kpi-sub">
                        Tuần này: <span class="text-white fw-semibold">+{{ number_format($weekTotal) }}</span> lượt nghe
                    </div>
                </div>
                <div class="kpi-icon" style="background:rgba(245,158,11,.14);color:#fbbf24;">
                    <i class="fa-solid fa-compact-disc"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Main content ─────────────────────────────────────────────────────────── --}}
<div class="row g-4 mb-4">

    {{-- Chart 7 ngày --}}
    <div class="col-xl-8">
        <div class="chart-wrap h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div style="font-size:.75rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#64748b;">
                    <i class="fa-solid fa-chart-area me-2" style="color:#7c3aed"></i>Lượt nghe 7 ngày qua
                </div>
                <a href="{{ route('artist.stats.index') }}"
                   style="font-size:.75rem;color:#a855f7;text-decoration:none;">
                    Xem đầy đủ <i class="fa-solid fa-arrow-right ms-1" style="font-size:.65rem"></i>
                </a>
            </div>
            <div style="height:220px;position:relative;">
                <canvas id="weekChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="col-xl-4">
        <div class="chart-wrap h-100">
            <div style="font-size:.75rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#64748b;margin-bottom:1rem;">
                <i class="fa-solid fa-bolt me-2" style="color:#fbbf24"></i>Thao tác nhanh
            </div>
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('artist.songs.create') }}" class="quick-btn"
                   style="background:rgba(168,85,247,.08);border-color:rgba(168,85,247,.18);">
                    <div class="kpi-icon" style="background:rgba(168,85,247,.18);color:#c084fc;">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <div>
                        <div class="text-white fw-semibold" style="font-size:.85rem;">Tải lên bài hát</div>
                        <div class="text-muted" style="font-size:.72rem;">MP3, FLAC, WAV</div>
                    </div>
                </a>
                <a href="{{ route('artist.albums.create') }}" class="quick-btn"
                   style="background:rgba(59,130,246,.08);border-color:rgba(59,130,246,.18);">
                    <div class="kpi-icon" style="background:rgba(59,130,246,.18);color:#60a5fa;">
                        <i class="fa-solid fa-folder-plus"></i>
                    </div>
                    <div>
                        <div class="text-white fw-semibold" style="font-size:.85rem;">Tạo Album mới</div>
                        <div class="text-muted" style="font-size:.72rem;">Quản lý bộ sưu tập</div>
                    </div>
                </a>
                <a href="{{ route('artist.profile.edit') }}" class="quick-btn"
                   style="background:rgba(16,185,129,.08);border-color:rgba(16,185,129,.18);">
                    <div class="kpi-icon" style="background:rgba(16,185,129,.18);color:#34d399;">
                        <i class="fa-solid fa-user-pen"></i>
                    </div>
                    <div>
                        <div class="text-white fw-semibold" style="font-size:.85rem;">Hồ sơ nghệ sĩ</div>
                        <div class="text-muted" style="font-size:.72rem;">Tiểu sử, ảnh bìa</div>
                    </div>
                </a>
                <a href="{{ route('artist.stats.index') }}" class="quick-btn"
                   style="background:rgba(245,158,11,.08);border-color:rgba(245,158,11,.18);">
                    <div class="kpi-icon" style="background:rgba(245,158,11,.18);color:#fbbf24;">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div>
                        <div class="text-white fw-semibold" style="font-size:.85rem;">Xem thống kê</div>
                        <div class="text-muted" style="font-size:.72rem;">Phân tích đầy đủ</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── Row 3: Recent songs + Top3 week ────────────────────────────────────── --}}
<div class="row g-4">

    {{-- Bài hát gần đây --}}
    <div class="col-lg-7">
        <div class="chart-wrap h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div style="font-size:.75rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#64748b;">
                    <i class="fa-solid fa-music me-2" style="color:#a855f7"></i>Bài hát mới nhất
                </div>
                <a href="{{ route('artist.songs.index') }}"
                   style="font-size:.75rem;color:#a855f7;text-decoration:none;">
                    Xem tất cả <i class="fa-solid fa-arrow-right ms-1" style="font-size:.65rem"></i>
                </a>
            </div>
            @forelse($recentSongs as $song)
            @php
                $statusStyles = [
                    'published' => ['#34d399', 'Xuất bản'],
                    'pending'   => ['#fbbf24', 'Chờ duyệt'],
                    'draft'     => ['#64748b', 'Nháp'],
                    'hidden'    => ['#f87171', 'Ẩn'],
                    'scheduled' => ['#60a5fa', 'Hẹn giờ'],
                ];
                [$sBadge, $sLabel] = $statusStyles[$song->status] ?? ['#64748b', $song->status];
            @endphp
            <div class="song-row-mini">
                <img src="{{ $song->getCoverUrl() }}" alt="{{ $song->title }}" class="song-thumb">
                <div class="flex-grow-1 min-w-0">
                    <div class="text-white fw-semibold text-truncate" style="font-size:.85rem;">{{ $song->title }}</div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="status-pip" style="background:{{ $sBadge }}"></span>
                        <span style="font-size:.7rem;color:{{ $sBadge }};">{{ $sLabel }}</span>
                        @if($song->genre)
                            <span class="text-muted" style="font-size:.7rem;">· {{ $song->genre->name }}</span>
                        @endif
                    </div>
                </div>
                <div class="text-end flex-shrink-0">
                    <div class="text-white fw-semibold" style="font-size:.82rem;">{{ number_format($song->listens) }}</div>
                    <div class="text-muted" style="font-size:.68rem;">lượt nghe</div>
                </div>
                <a href="{{ route('artist.songs.show', $song) }}"
                   class="ms-2 flex-shrink-0" style="color:#475569;font-size:.75rem;text-decoration:none;">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>
            @empty
            <div class="text-center text-muted py-4">
                <i class="fa-solid fa-music fa-2x mb-2 d-block opacity-20"></i>
                <p style="font-size:.85rem;">Chưa có bài hát nào. <a href="{{ route('artist.songs.create') }}" style="color:#a855f7">Tải lên ngay</a></p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Top 3 tuần + Album nổi bật --}}
    <div class="col-lg-5">
        <div class="chart-wrap mb-3">
            <div style="font-size:.75rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#64748b;margin-bottom:1rem;">
                <i class="fa-solid fa-fire me-2" style="color:#f97316"></i>Top trending tuần này
            </div>
            @forelse($top3Week as $i => $item)
            @php
                $song = $item->song ?? null;
                $tListens = $item->week_listens ?? 0;
                $rankColors = ['#fbbf24','#94a3b8','#b45309'];
            @endphp
            @if($song)
            <div class="song-row-mini">
                <div style="width:22px;font-size:.9rem;font-weight:800;color:{{ $rankColors[$i] ?? '#334155' }};text-align:center;flex-shrink:0;">{{ $i+1 }}</div>
                <img src="{{ $song->getCoverUrl() }}" alt="{{ $song->title }}" class="song-thumb">
                <div class="flex-grow-1 min-w-0">
                    <div class="text-white fw-semibold text-truncate" style="font-size:.82rem;">{{ $song->title }}</div>
                    <div class="text-muted" style="font-size:.7rem;">{{ number_format((int)$tListens) }} lượt tuần này</div>
                </div>
            </div>
            @endif
            @empty
            <div class="text-muted text-center py-3" style="font-size:.8rem;">Không có dữ liệu tuần này</div>
            @endforelse
        </div>

        @if($recentAlbum)
        <div class="chart-wrap">
            <div style="font-size:.75rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#64748b;margin-bottom:.85rem;">
                <i class="fa-solid fa-compact-disc me-2" style="color:#60a5fa"></i>Album gần nhất
            </div>
            <div class="d-flex align-items-center gap-3">
                <img src="{{ $recentAlbum->getCoverUrl() }}" alt="{{ $recentAlbum->title }}"
                     style="width:56px;height:56px;border-radius:10px;object-fit:cover;border:1px solid rgba(255,255,255,.1);flex-shrink:0;">
                <div class="flex-grow-1 min-w-0">
                    <div class="text-white fw-bold text-truncate" style="font-size:.9rem;">{{ $recentAlbum->title }}</div>
                    <div class="text-muted" style="font-size:.75rem;">
                        {{ $recentAlbum->songs_count }} bài hát
                        @if($recentAlbum->released_date)
                            · {{ $recentAlbum->released_date->format('d/m/Y') }}
                        @endif
                    </div>
                    <div class="mt-1">
                        @php $aStatus = $recentAlbum->status === 'published' ? ['#34d399','Đã xuất bản'] : ['#64748b','Bản nháp']; @endphp
                        <span class="status-pip" style="background:{{ $aStatus[0] }}"></span>
                        <span style="font-size:.7rem;color:{{ $aStatus[0] }};">{{ $aStatus[1] }}</span>
                    </div>
                </div>
                <a href="{{ route('artist.albums.show', $recentAlbum) }}"
                   class="btn btn-sm"
                   style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#94a3b8;font-size:.75rem;white-space:nowrap;flex-shrink:0">
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const days   = @json($weekDays);
    const vals   = @json($weekListens);

    const ctx = document.getElementById('weekChart').getContext('2d');
    const grad = ctx.createLinearGradient(0, 0, 0, 220);
    grad.addColorStop(0, 'rgba(168,85,247,.25)');
    grad.addColorStop(1, 'rgba(168,85,247,.01)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: days,
            datasets: [{
                label: 'Lượt nghe',
                data: vals,
                borderColor: '#a855f7',
                backgroundColor: grad,
                borderWidth: 2, tension: 0.4, fill: true,
                pointBackgroundColor: '#a855f7',
                pointRadius: 3, pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15,23,42,.95)',
                    borderColor: 'rgba(168,85,247,.3)', borderWidth: 1,
                    callbacks: { label: ctx => ' ' + ctx.parsed.y.toLocaleString('vi-VN') + ' lượt nghe' }
                }
            },
            scales: {
                x: { grid: { color: 'rgba(255,255,255,.04)' }, ticks: { color: '#475569', font: { size: 11 } } },
                y: { grid: { color: 'rgba(255,255,255,.04)' }, beginAtZero: true,
                     ticks: { color: '#475569', font: { size: 11 },
                              callback: v => v >= 1e6 ? (v/1e6).toFixed(1)+'M' : v >= 1e3 ? (v/1e3).toFixed(0)+'K' : v
                     }
                }
            }
        }
    });
});
</script>
@endpush
