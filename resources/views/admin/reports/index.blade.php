@extends('layouts.admin')

@section('title', 'Thống kê & Báo cáo')
@section('page-title', 'Phân tích dữ liệu')
@section('page-subtitle', 'Theo dõi hiệu suất và tăng trưởng của hệ thống')

@push('styles')
<style>
.stat-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    padding: 1.5rem;
    transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}
.stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    margin-bottom: 1rem;
}
.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #fff;
    line-height: 1;
    margin-bottom: 0.5rem;
}
.stat-label {
    color: #94a3b8;
    font-size: 0.85rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.nav-pills.custom-tabs {
    gap: 0.5rem;
}
.nav-pills.custom-tabs .nav-link {
    color: #94a3b8;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    padding: 0.6rem 1.2rem;
    font-weight: 600;
    transition: all 0.2s;
}
.nav-pills.custom-tabs .nav-link:hover {
    color: #e2e8f0;
    background: rgba(255, 255, 255, 0.08);
}
.nav-pills.custom-tabs .nav-link.active {
    color: #fff;
    background: #6366f1;
    border-color: #6366f1;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}
.chart-container {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 16px;
    padding: 1.5rem;
    height: 100%;
}
.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.chart-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #e2e8f0;
    margin: 0;
}
</style>
@endpush

@section('content')

{{-- Controls: Tab, Period, Export --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    
    <ul class="nav nav-pills custom-tabs mb-0">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'listens' ? 'active' : '' }}" href="?tab=listens&period={{ $period }}">
                <i class="fa-solid fa-headphones me-2"></i>Lượt nghe
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'users' ? 'active' : '' }}" href="?tab=users&period={{ $period }}">
                <i class="fa-solid fa-users me-2"></i>Người dùng
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'revenue' ? 'active' : '' }}" href="?tab=revenue&period={{ $period }}">
                <i class="fa-solid fa-sack-dollar me-2"></i>Doanh thu
            </a>
        </li>
    </ul>

    <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 text-end">
        <form action="{{ route('admin.reports.index') }}" method="GET" class="d-flex flex-wrap align-items-center justify-content-end gap-2" id="filterForm">
            <input type="hidden" name="tab" value="{{ $tab }}">
            
            <div id="customDateWrap" class="d-flex align-items-center gap-2" style="display: {{ $period === 'custom' ? 'flex' : 'none !important' }};">
                <input type="date" name="start_date" class="form-control form-control-sm bg-dark text-white border-secondary shadow-none" value="{{ request('start_date') }}" title="Ngày bắt đầu" style="border-radius: 8px;">
                <span class="text-muted"><i class="fa-solid fa-arrow-right-long"></i></span>
                <input type="date" name="end_date" class="form-control form-control-sm bg-dark text-white border-secondary shadow-none" value="{{ request('end_date') }}" title="Ngày kết thúc" style="border-radius: 8px;">
                <button type="submit" class="btn btn-sm btn-primary" style="border-radius: 8px; white-space: nowrap;"><i class="fa-solid fa-filter"></i> Lọc</button>
            </div>

            <select name="period" class="form-select form-select-sm bg-dark text-white border-secondary shadow-none" 
                    onchange="handlePeriodChange(this)" style="width: 150px; border-radius: 8px;">
                <option value="7days" {{ $period === '7days' ? 'selected' : '' }}>7 ngày qua</option>
                <option value="30days" {{ $period === '30days' ? 'selected' : '' }}>30 ngày qua</option>
                <option value="year" {{ $period === 'year' ? 'selected' : '' }}>1 năm qua</option>
                <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Tùy chọn khoảng</option>
            </select>
        </form>

        <a href="{{ route('admin.reports.export', ['tab' => $tab, 'period' => $period]) }}" class="btn btn-sm btn-outline-success" style="border-radius: 8px; font-weight: 600;">
            <i class="fa-solid fa-file-excel me-2"></i>Xuất Excel
        </a>
    </div>

</div>

{{-- Dynamic Content Based On Tab --}}
@if($tab === 'users')

    {{-- Users Tab --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card border-top border-3" style="border-top-color: #3b82f6 !important;">
                <div class="stat-icon" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa;"><i class="fa-solid fa-users"></i></div>
                <div class="stat-value">{{ number_format(array_sum($roles)) }}</div>
                <div class="stat-label">Tổng Users Hệ thống</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card border-top border-3" style="border-top-color: #10b981 !important;">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;"><i class="fa-solid fa-user-check"></i></div>
                <div class="stat-value">{{ number_format($roles['Free']) }}</div>
                <div class="stat-label">Người dùng Miễn phí</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card border-top border-3" style="border-top-color: #f59e0b !important;">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;"><i class="fa-solid fa-crown"></i></div>
                <div class="stat-value">{{ number_format($roles['Premium']) }}</div>
                <div class="stat-label">Người dùng Premium</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card border-top border-3" style="border-top-color: #8b5cf6 !important;">
                <div class="stat-icon" style="background: rgba(139, 92, 246, 0.15); color: #a855f7;"><i class="fa-solid fa-percent"></i></div>
                <div class="stat-value">{{ $conversionRate }}%</div>
                <div class="stat-label">Tỷ lệ chuyển đổi Vip</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title"><i class="fa-solid fa-chart-area me-2 text-info"></i>Tăng trưởng Users ({{ $period == 'year' ? 'Năm' : 'Ngày' }})</h5>
                </div>
                <div style="height: 320px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title"><i class="fa-solid fa-chart-pie me-2 text-warning"></i>Phân bổ độ tuổi</h5>
                </div>
                <div style="height: 320px; display: flex; align-items: center; justify-content: center;">
                    <canvas id="doughnutChart"></canvas>
                </div>
            </div>
        </div>
    </div>

@elseif($tab === 'revenue')

    {{-- Revenue Tab --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="stat-card border-top border-3" style="border-top-color: #fbbf24 !important;">
                <div class="stat-icon" style="background: rgba(251, 191, 36, 0.15); color: #fbbf24;"><i class="fa-solid fa-crown"></i></div>
                <div class="stat-value">{{ number_format($totalPremiumRevenue) }} <span style="font-size:1rem;color:#94a3b8">₫</span></div>
                <div class="stat-label">Doanh thu gói Premium</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="stat-card border-top border-3" style="border-top-color: #a855f7 !important;">
                <div class="stat-icon" style="background: rgba(168, 85, 247, 0.15); color: #c084fc;"><i class="fa-solid fa-bullhorn"></i></div>
                <div class="stat-value">{{ number_format($totalAdRevenue) }} <span style="font-size:1rem;color:#94a3b8">₫</span></div>
                <div class="stat-label">Doanh thu Quảng cáo (Thuần Ads)</div>
            </div>
        </div>
        <div class="col-sm-12 col-xl-4">
            <div class="stat-card border-top border-3" style="border-top-color: #10b981 !important;">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;"><i class="fa-solid fa-sack-dollar"></i></div>
                <div class="stat-value">{{ number_format($totalPremiumRevenue + $totalAdRevenue) }} <span style="font-size:1rem;color:#94a3b8">₫</span></div>
                <div class="stat-label">Tổng doanh thu nền tảng</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title"><i class="fa-solid fa-money-bill-trend-up me-2 text-success"></i>Biểu đồ doanh thu Premium ({{ $period == 'year' ? 'Tháng' : 'Ngày' }})</h5>
                </div>
                <div style="height: 380px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

@else

    {{-- Listens Tab --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-6">
            <div class="stat-card border-start border-4" style="border-color: #3b82f6 !important;">
                <div class="stat-icon" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa;"><i class="fa-solid fa-headphones"></i></div>
                <div class="stat-value">{{ number_format($totalListens ?? 0) }}</div>
                <div class="stat-label">Tổng lượt nghe {{ $period == 'year' ? '1 năm' : ($period == '7days' ? '7 ngày' : '30 ngày') }}</div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="stat-card border-start border-4" style="border-color: #ec4899 !important;">
                <div class="stat-icon" style="background: rgba(236, 72, 153, 0.15); color: #f472b6;"><i class="fa-solid fa-play"></i></div>
                <div class="stat-value">{{ number_format($avgListenTimeMins ?? 0) }} <span style="font-size:1.2rem;color:#94a3b8">phút</span></div>
                <div class="stat-label">Tổng Thời lượng Nghe Trung bình</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title"><i class="fa-solid fa-wave-square me-2 text-primary"></i>Biểu đồ Mật độ Lượt Nghe</h5>
                </div>
                <div style="height: 320px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title"><i class="fa-solid fa-compact-disc me-2 text-danger"></i>Top Thể loại Nổi bật</h5>
                </div>
                <div style="height: 320px; display: flex; align-items: center; justify-content: center;">
                    <canvas id="doughnutChart"></canvas>
                </div>
            </div>
        </div>
    </div>

@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Cấu hình Chart.js Mặc định cho Giao diện Tối
Chart.defaults.color = '#94a3b8';
Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
Chart.defaults.scale.grid.color = 'rgba(255, 255, 255, 0.05)';
Chart.defaults.scale.grid.borderColor = 'rgba(255, 255, 255, 0.1)';

const ctxTrend = document.getElementById('trendChart');
const ctxDoughnut = document.getElementById('doughnutChart');

@if($tab === 'users')
    // Data Users Trend
    const trendLabels = {!! json_encode($userTrend->pluck('date')) !!};
    const trendData = {!! json_encode($userTrend->pluck('count')) !!};

    if(ctxTrend) {
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Người dùng Đăng ký Mới',
                    data: trendData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#3b82f6',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // Age distribution
    const ageData = {!! json_encode(array_values((array)$ageDist)) !!};
    const ageLabels = ['Dưới 18', '18 - 24', '25 - 34', 'Trên 35'];
    if(ctxDoughnut) {
        new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: ageLabels,
                datasets: [{
                    data: ageData,
                    backgroundColor: ['#6366f1', '#ec4899', '#f59e0b', '#10b981'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } }
                },
                cutout: '70%'
            }
        });
    }

@elseif($tab === 'revenue')

    // Revenue Trend
    const revLabels = {!! json_encode($revenueTrend->pluck('date')) !!};
    const revData = {!! json_encode($revenueTrend->pluck('total')) !!};
    if(ctxTrend) {
        new Chart(ctxTrend, {
            type: 'bar',
            data: {
                labels: revLabels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: revData,
                    backgroundColor: '#10b981',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

@else

    // Listen Trend
    const listenLabels = {!! json_encode($listenTrend->pluck('date')) !!};
    const listenData = {!! json_encode($listenTrend->pluck('total')) !!};
    if(ctxTrend) {
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: listenLabels,
                datasets: [{
                    label: 'Lượt nghe',
                    data: listenData,
                    borderColor: '#ec4899',
                    backgroundColor: 'rgba(236, 72, 153, 0.2)',
                    borderWidth: 3,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { beginAtZero: true },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Genres Pie
    const genreLabels = {!! json_encode($topGenres->pluck('name')) !!};
    const genreData = {!! json_encode($topGenres->pluck('total')) !!};
    if(ctxDoughnut) {
        new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: genreLabels,
                datasets: [{
                    data: genreData,
                    backgroundColor: ['#6366f1', '#ec4899', '#f59e0b', '#10b981', '#3b82f6'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } }
                },
                cutout: '65%'
            }
        });
    }

@endif
</script>
<script>
function handlePeriodChange(select) {
    if (select.value === 'custom') {
        document.getElementById('customDateWrap').style.setProperty('display', 'flex', 'important');
    } else {
        document.getElementById('customDateWrap').style.setProperty('display', 'none', 'important');
        document.getElementById('filterForm').submit();
    }
}
</script>
@endpush
