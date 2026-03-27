@extends('layouts.admin')

@section('title', 'Bảng điều khiển')
@section('page-title', 'Bảng điều khiển')
@section('page-subtitle', 'Tổng quan chi tiết hệ thống và tăng trưởng doanh thu')

@section('content')

{{-- ─── Hàng 1: Người dùng & Nghệ sĩ & Doanh thu ─── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="rounded-3 p-3 bg-dark border border-secondary border-opacity-25 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between mb-3">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: .7rem; letter-spacing: .05em;">Tổng người dùng</div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;background:rgba(99,102,241,.15);">
                    <i class="fa-solid fa-users" style="color:#818cf8;font-size:.8rem"></i>
                </div>
            </div>
            <div>
                <div class="fw-bold text-white fs-3 lh-1 mb-2">{{ number_format($totalUsers) }}</div>
                <div class="d-flex gap-2 text-muted" style="font-size:.7rem">
                    <span><i class="fa-solid fa-crown text-warning me-1"></i>{{ number_format($totalPremiumUsers) }} VIP</span>
                    <span><i class="fa-solid fa-user me-1"></i>{{ number_format($totalFreeUsers) }} Free</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="rounded-3 p-3 bg-dark border border-secondary border-opacity-25 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between mb-3">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: .7rem; letter-spacing: .05em;">Nghệ sĩ</div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;background:rgba(168,85,247,.15);">
                    <i class="fa-solid fa-microphone-lines" style="color:#c084fc;font-size:.8rem"></i>
                </div>
            </div>
            <div>
                <div class="fw-bold text-white fs-3 lh-1">{{ number_format($totalArtists) }}</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="rounded-3 p-3 bg-dark border border-secondary border-opacity-25 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between mb-3">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: .7rem; letter-spacing: .05em;">Doanh thu VIP (Tháng này)</div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;background:rgba(34,197,94,.15);">
                    <i class="fa-solid fa-sack-dollar" style="color:#4ade80;font-size:.8rem"></i>
                </div>
            </div>
            <div>
                <div class="fw-bold text-white fs-3 lh-1" style="color:#4ade80 !important">{{ number_format($revenueMonth) }} đ</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="rounded-3 p-3 bg-dark border border-secondary border-opacity-25 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between mb-3">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: .7rem; letter-spacing: .05em;">Tổng Doanh thu VIP</div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;background:rgba(234,179,8,.15);">
                    <i class="fa-solid fa-vault" style="color:#facc15;font-size:.8rem"></i>
                </div>
            </div>
            <div>
                <div class="fw-bold text-white fs-3 lh-1">{{ number_format($revenueTotal) }} đ</div>
            </div>
        </div>
    </div>
</div>

{{-- ─── Hàng 2: Content (Nội dung) ─── --}}
<div class="row g-3 mb-4">
    <div class="col-4 col-md-4">
        <div class="rounded-3 p-3 text-center bg-dark border border-secondary border-opacity-25 h-100">
            <i class="fa-solid fa-music fa-lg mb-2" style="color:#38bdf8"></i>
            <div class="fw-bold fs-4 text-white lh-1">{{ number_format($totalSongs) }}</div>
            <div class="text-muted small mt-1">Bài hát</div>
        </div>
    </div>
    <div class="col-4 col-md-4">
        <div class="rounded-3 p-3 text-center bg-dark border border-secondary border-opacity-25 h-100">
            <i class="fa-solid fa-record-vinyl fa-lg mb-2" style="color:#f472b6"></i>
            <div class="fw-bold fs-4 text-white lh-1">{{ number_format($totalAlbums) }}</div>
            <div class="text-muted small mt-1">Album</div>
        </div>
    </div>
    <div class="col-4 col-md-4">
        <div class="rounded-3 p-3 text-center bg-dark border border-secondary border-opacity-25 h-100">
            <i class="fa-solid fa-list fa-lg mb-2" style="color:#a78bfa"></i>
            <div class="fw-bold fs-4 text-white lh-1">{{ number_format($totalPlaylists) }}</div>
            <div class="text-muted small mt-1">Playlist</div>
        </div>
    </div>
</div>

{{-- ─── Hàng 3: Biểu đồ & Lượt nghe ─── --}}
<div class="row g-4 mb-4">
    {{-- Lượt nghe --}}
    <div class="col-12 col-xl-4 d-flex flex-column gap-3">
        <div class="rounded-3 p-4 bg-dark border border-secondary border-opacity-25 flex-grow-1 text-center d-flex flex-column justify-content-center">
            <div class="text-muted small fw-semibold text-uppercase mb-2" style="font-size: .75rem; letter-spacing: .05em;">Lượt nghe hôm nay</div>
            <div class="fw-bold text-white mb-4 shadow-sm" style="font-size: 3rem; lh-1; color:#fbbf24 !important; text-shadow: 0 0 20px rgba(251,191,36,.2)">
                {{ number_format($listensToday) }}
            </div>
            
            <div class="row g-0 pt-3 border-top border-secondary border-opacity-25">
                <div class="col-6 border-end border-secondary border-opacity-25">
                    <div class="text-muted small mb-1">Tuần này</div>
                    <div class="text-white fw-bold fs-5">{{ number_format($listensWeek) }}</div>
                </div>
                <div class="col-6">
                    <div class="text-muted small mb-1">Tháng này</div>
                    <div class="text-white fw-bold fs-5">{{ number_format($listensMonth) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Biểu đồ tăng trưởng --}}
    <div class="col-12 col-xl-8">
        <div class="card bg-dark border-secondary border-opacity-25 h-100">
            <div class="card-header bg-transparent border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                <span class="fw-semibold text-white small"><i class="fa-solid fa-chart-line me-2 text-muted"></i>Biểu đồ Lượt nghe & Doanh thu 7 ngày qua</span>
            </div>
            <div class="card-body position-relative pb-2" style="min-height: 260px;">
                <canvas id="growthChart"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('growthChart').getContext('2d');
    
    // JS Data from backend
    const labels = {!! json_encode(array_reverse($chartData['labels'])) !!};
    const playsData = {!! json_encode(array_reverse($chartData['plays'])) !!};
    const revenueData = {!! json_encode(array_reverse($chartData['revenue'])) !!};
    
    // Dark mode chart setup
    Chart.defaults.color = '#9ca3af';
    Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: ' Lượt nghe',
                    data: playsData,
                    borderColor: '#a855f7', // Purple
                    backgroundColor: 'rgba(168,85,247,0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                },
                {
                    label: ' Doanh thu (đ)',
                    data: revenueData,
                    borderColor: '#4ade80', // Green
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.4,
                    pointBackgroundColor: '#22c55e',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(17,24,39,0.9)',
                    titleColor: '#f3f4f6',
                    bodyColor: '#d1d5db',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    padding: 12,
                    boxPadding: 6,
                    usePointStyle: true
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(255,255,255,0.05)',
                        drawBorder: false
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: {
                        color: 'rgba(255,255,255,0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        precision: 0
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false, 
                    },
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000) return (value/1000000) + 'M';
                            if (value >= 1000) return (value/1000) + 'K';
                            return value;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
