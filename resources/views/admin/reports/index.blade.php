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
.stat-card.glass {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
}
.stat-card:hover {
    border-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-5px);
}
.trend-indicator {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.trend-up { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.trend-down { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

.sparkline-container { width: 80px; height: 35px; }

.engagement-bar {
    height: 8px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    overflow: hidden;
    margin-top: 5px;
}
.engagement-fill { height: 100%; border-radius: 10px; transition: width 1s ease-in-out; }

.content-table tr { border-bottom: 1px solid rgba(255, 255, 255, 0.03); }
.content-table tr:last-child { border-bottom: none; }

.peak-hour-bar {
    flex: 1;
    background: rgba(99, 102, 241, 0.1);
    border-radius: 3px 3px 0 0;
    margin: 0 1px;
    position: relative;
    cursor: pointer;
}
.peak-hour-bar:hover { background: #6366f1; }
.peak-hour-label { font-size: 0.6rem; color: #64748b; text-align: center; margin-top: 5px; }
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
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'content' ? 'active' : '' }}" href="?tab=content&period={{ $period }}">
                <i class="fa-solid fa-music me-2"></i>Nội dung
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

        <div class="btn-group" role="group">
            <a href="{{ route('admin.reports.export', ['tab' => $tab, 'period' => $period]) }}" class="btn btn-sm btn-outline-success" style="border-radius: 8px 0 0 8px; font-weight: 600;">
                <i class="fa-solid fa-file-excel me-2"></i>Excel
            </a>
            <a href="{{ route('admin.reports.exportPdf', ['tab' => $tab, 'period' => $period]) }}" class="btn btn-sm btn-outline-danger" style="border-radius: 0 8px 8px 0; font-weight: 600;">
                <i class="fa-solid fa-file-pdf me-2"></i>PDF
            </a>
        </div>
    </div>

</div>

{{-- Dynamic Content Based On Tab --}}
@if($tab === 'users')

    {{-- Users Tab --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-4 col-xl-4">
            <div class="stat-card border-top border-3" style="border-top-color: #3b82f6 !important;">
                <div class="stat-icon" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa;"><i class="fa-solid fa-users"></i></div>
                <div class="stat-value">{{ number_format(array_sum($roles)) }}</div>
                <div class="stat-label">Tổng Users Hệ thống</div>
            </div>
        </div>
        <div class="col-sm-4 col-xl-4">
            <div class="stat-card border-top border-3" style="border-top-color: #10b981 !important;">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;"><i class="fa-solid fa-user-check"></i></div>
                <div class="stat-value">{{ number_format($roles['Free']) }}</div>
                <div class="stat-label">Người dùng Miễn phí</div>
            </div>
        </div>
        <div class="col-sm-4 col-xl-4">
            <div class="stat-card border-top border-3" style="border-top-color: #f59e0b !important;">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;"><i class="fa-solid fa-crown"></i></div>
                <div class="stat-value">{{ number_format($roles['Premium']) }}</div>
                <div class="stat-label">Người dùng Premium</div>
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

    {{-- 1. KPI Stats --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="stat-card border-top border-3" style="border-top-color: #fbbf24 !important;">
                <div class="stat-icon" style="background: rgba(251, 191, 36, 0.15); color: #fbbf24;"><i class="fa-solid fa-crown"></i></div>
                <div class="stat-value" style="font-size:1.6rem">{{ number_format($totalPremiumRevenue) }} <span style="font-size:0.9rem;color:#94a3b8">₫</span></div>
                <div class="stat-label">Doanh thu Gói VIP (Lọc)</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="stat-card border-top border-3" style="border-top-color: #a855f7 !important;">
                <div class="stat-icon" style="background: rgba(168, 85, 247, 0.15); color: #c084fc;"><i class="fa-solid fa-microphone-lines"></i></div>
                <div class="stat-value" style="font-size:1.6rem">{{ number_format($totalArtistRevenue) }} <span style="font-size:0.9rem;color:#94a3b8">₫</span></div>
                <div class="stat-label">Doanh thu Gói Nghệ Sĩ (Lọc)</div>
            </div>
        </div>
        <div class="col-sm-12 col-xl-4">
            <div class="stat-card border-top border-3" style="border-top-color: #10b981 !important;">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;"><i class="fa-solid fa-sack-dollar"></i></div>
                <div class="stat-value" style="font-size:1.6rem">{{ number_format($totalSystemRevenue ?? 0) }} <span style="font-size:0.9rem;color:#94a3b8">₫</span></div>
                <div class="stat-label">Tổng doanh thu hệ thống (Cả kỳ)</div>
            </div>
        </div>
    </div>

    {{-- 2. Phân tích doanh thu VIP --}}
    <div class="card bg-dark border-secondary shadow-sm mb-4" style="border-radius:12px; overflow:hidden;">
        <div class="card-header bg-dark border-secondary p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h5 class="mb-0 text-warning"><i class="fa-solid fa-crown me-2"></i>Phân tích gói VIP (Premium)</h5>
                
                <div class="filter-bar p-1 bg-dark rounded-pill border border-secondary d-inline-flex">
                    <form action="" method="GET" class="d-flex m-0 align-items-center">
                        <input type="hidden" name="tab" value="revenue">
                        <select name="vip_period" class="form-select form-select-sm bg-transparent text-white border-0 shadow-none py-0 px-3" style="width: auto; cursor: pointer; font-size: 0.75rem;" onchange="this.form.submit()">
                            @foreach(['month' => 'Tháng này', '7days' => '7 ngày qua', 'year' => 'Năm nay', 'custom' => 'Tùy chọn lọc ngày'] as $val => $label)
                                <option value="{{ $val }}" class="bg-dark" {{ ($vipPeriod ?? 'month') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            @if(($vipPeriod ?? 'month') === 'custom')
            <div class="mt-3 p-3 bg-black bg-opacity-25 rounded-3 border border-secondary border-opacity-50">
                <form action="" method="GET" class="row g-3 align-items-end m-0">
                    <input type="hidden" name="tab" value="revenue">
                    <input type="hidden" name="vip_period" value="custom">
                    <div class="col-auto">
                        <label class="text-muted small fw-bold mb-1 d-block">Từ ngày</label>
                        <input type="date" name="vip_start_date" id="vip_start_date" class="form-control form-control-sm bg-dark text-white border-secondary" value="{{ $vStartDate ?? '' }}" style="width:160px;">
                    </div>
                    <div class="col-auto">
                        <label class="text-muted small fw-bold mb-1 d-block">Đến ngày</label>
                        <input type="date" name="vip_end_date" id="vip_end_date" class="form-control form-control-sm bg-dark text-white border-secondary" value="{{ $vEndDate ?? '' }}" style="width:160px;">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-warning px-4 fw-bold" style="height:31px; border-radius:6px; color:#000;">Áp dụng</button>
                    </div>
                </form>
            </div>
            @endif
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-xl-8">
                    <h6 class="text-muted small mb-3">Biểu đồ doanh thu hàng ngày (VNĐ)</h6>
                    <div style="height: 350px;">
                        <canvas id="vipRevenueChart"></canvas>
                    </div>
                </div>
                <div class="col-xl-4 border-start border-secondary border-opacity-25">
                    <h6 class="text-muted small mb-3">Top 5 Người dùng chi nhiều nhất (VIP)</h6>
                    <div class="top-list">
                        @forelse($topVipSpenders as $i => $u)
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-2 bg-black bg-opacity-25 border border-secondary border-opacity-10 position-relative hover-link-wrapper">
                            <div class="position-relative">
                                <a href="{{ route('admin.users.show', $u->id) }}">
                                    <img src="{{ $u->avatar ? asset('storage/'.$u->avatar) : asset('storage/avt.jpg') }}" class="rounded-pill" width="40" height="40" style="object-fit:cover;">
                                </a>
                                <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-warning text-black" style="font-size:0.6rem; transform:translate(30%, 30%)">#{{ $i+1 }}</span>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <a href="{{ route('admin.users.show', $u->id) }}" class="text-white fw-bold text-truncate small d-block" style="text-decoration:none;">{{ $u->name }}</a>
                                <div class="text-muted" style="font-size:0.7rem">{{ $u->email }}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-warning small">{{ number_format($u->total_spent) }}</div>
                                <div class="text-muted" style="font-size:0.6rem">VNĐ</div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-4 small">Chưa có dữ liệu chi tiêu</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Phân tích doanh thu Nghệ sĩ --}}
    <div class="card bg-dark border-secondary shadow-sm mb-4" style="border-radius:12px; overflow:hidden;">
        <div class="card-header bg-dark border-secondary p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h5 class="mb-0 text-info" style="color:#a855f7 !important;"><i class="fa-solid fa-microphone-lines me-2"></i>Phân tích gói Nghệ sĩ</h5>
                
                <div class="filter-bar p-1 bg-dark rounded-pill border border-secondary d-inline-flex">
                    <form action="" method="GET" class="d-flex m-0 align-items-center">
                        <input type="hidden" name="tab" value="revenue">
                        <select name="artist_period" class="form-select form-select-sm bg-transparent text-white border-0 shadow-none py-0 px-3" style="width: auto; cursor: pointer; font-size: 0.75rem;" onchange="this.form.submit()">
                            @foreach(['month' => 'Tháng này', '7days' => '7 ngày qua', 'year' => 'Năm nay', 'custom' => 'Tùy chọn lọc ngày'] as $val => $label)
                                <option value="{{ $val }}" class="bg-dark" {{ ($artistPeriod ?? 'month') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            @if(($artistPeriod ?? 'month') === 'custom')
            <div class="mt-3 p-3 bg-black bg-opacity-25 rounded-3 border border-secondary border-opacity-50">
                <form action="" method="GET" class="row g-3 align-items-end m-0">
                    <input type="hidden" name="tab" value="revenue">
                    <input type="hidden" name="artist_period" value="custom">
                    <div class="col-auto">
                        <label class="text-muted small fw-bold mb-1 d-block">Từ ngày</label>
                        <input type="date" name="artist_start_date" id="artist_start_date" class="form-control form-control-sm bg-dark text-white border-secondary" value="{{ $aStartDate ?? '' }}" style="width:160px;">
                    </div>
                    <div class="col-auto">
                        <label class="text-muted small fw-bold mb-1 d-block">Đến ngày</label>
                        <input type="date" name="artist_end_date" id="artist_end_date" class="form-control form-control-sm bg-dark text-white border-secondary" value="{{ $aEndDate ?? '' }}" style="width:160px;">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary px-4 fw-bold" style="height:31px; border-radius:6px; background:#a855f7; border:none;">Áp dụng</button>
                    </div>
                </form>
            </div>
            @endif
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-xl-8">
                    <h6 class="text-muted small mb-3">Biểu đồ doanh thu hàng ngày (VNĐ)</h6>
                    <div style="height: 350px;">
                        <canvas id="artistRevenueChart"></canvas>
                    </div>
                </div>
                <div class="col-xl-4 border-start border-secondary border-opacity-25">
                    <h6 class="text-muted small mb-3">Top 5 Người dùng chi nhiều nhất (Nghệ sĩ)</h6>
                    <div class="top-list">
                        @forelse($topArtistSpenders as $i => $u)
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-2 bg-black bg-opacity-25 border border-secondary border-opacity-10 position-relative hover-link-wrapper">
                            <div class="position-relative">
                                <a href="{{ route('admin.artists.show', $u->id) }}">
                                    <img src="{{ $u->avatar ? asset('storage/'.$u->avatar) : asset('storage/avt.jpg') }}" class="rounded-pill" width="40" height="40" style="object-fit:cover;">
                                </a>
                                <span class="position-absolute bottom-0 end-0 badge rounded-pill" style="background:#a855f7; color:#fff; font-size:0.6rem; transform:translate(30%, 30%)">#{{ $i+1 }}</span>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <a href="{{ route('admin.artists.show', $u->id) }}" class="text-white fw-bold text-truncate small d-block" style="text-decoration:none;">{{ $u->name }}</a>
                                <div class="text-muted" style="font-size:0.7rem">{{ $u->email }}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold small" style="color:#c084fc;">{{ number_format($u->total_spent) }}</div>
                                <div class="text-muted" style="font-size:0.6rem">VNĐ</div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-4 small">Chưa có dữ liệu chi tiêu</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

@elseif($tab === 'content')

    {{-- 1. KPI Stats --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card border-top border-3" style="border-top-color: #6366f1 !important;">
                <div class="stat-icon" style="background: rgba(99,102,241,0.15); color: #818cf8;"><i class="fa-solid fa-headphones"></i></div>
                <div class="stat-value" style="font-size:1.6rem">{{ number_format($totalPlaysContent ?? 0) }}</div>
                <div class="stat-label">Tổng Lượt Nghe</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card border-top border-3" style="border-top-color: #3b82f6 !important;">
                <div class="stat-icon" style="background: rgba(59,130,246,0.15); color: #60a5fa;"><i class="fa-solid fa-music"></i></div>
                <div class="stat-value" style="font-size:1.6rem">{{ number_format($activeSongsCount ?? 0) }}</div>
                <div class="stat-label">Bài Hát Hoạt Động</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card border-top border-3" style="border-top-color: #10b981 !important;">
                <div class="stat-icon" style="background: rgba(16,185,129,0.15); color: #34d399;"><i class="fa-solid fa-user-tie"></i></div>
                <div class="stat-value" style="font-size:1.6rem">{{ number_format($activeArtistsCount ?? 0) }}</div>
                <div class="stat-label">Nghệ Sĩ Hoạt Động</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card border-top border-3" style="border-top-color: #ec4899 !important;">
                <div class="stat-icon" style="background: rgba(236,72,153,0.15); color: #f472b6;"><i class="fa-solid fa-heart"></i></div>
                <div class="stat-value" style="font-size:1.6rem">{{ number_format($totalFavoritesContent ?? 0) }}</div>
                <div class="stat-label">Lượt Yêu Thích Mới</div>
            </div>
        </div>
    </div>

    {{-- 2. Top Trending Table --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="chart-container">
                <div class="chart-header flex-column align-items-start mb-3">
                    <div class="d-flex justify-content-between w-100 mb-3 align-items-center">
                        <h5 class="chart-title"><i class="fa-solid fa-fire-flame-curved me-2 text-danger"></i>🔥 Top Trending Bài Hát</h5>
                        <div class="filter-bar p-1 bg-dark rounded-pill border border-secondary d-inline-flex">
                            @foreach(['day' => '24h', 'week' => '7 ngày', 'month' => '30 ngày', 'year' => '1 năm', 'custom' => 'Tùy chọn'] as $val => $label)
                                <a href="?tab=content&period={{ $period }}&trending_period={{ $val }}{{ $period === 'custom' ? '&start_date='.$startDate.'&end_date='.$endDate : '' }}"
                                   class="btn btn-sm px-3 rounded-pill {{ ($trendingPeriod ?? 'week') === $val ? 'btn-primary shadow-sm' : 'btn-outline-secondary border-0' }}"
                                   style="font-size:0.75rem;">{{ $label }}</a>
                            @endforeach
                        </div>
                    </div>
                    
                    @if(($trendingPeriod ?? 'week') === 'custom')
                    <div class="filter-bar w-100 p-3 bg-dark rounded-3 border border-secondary shadow-sm mb-2" style="background: rgba(15,23,42,0.6) !important;">
                        <form action="" method="GET" class="row g-3 align-items-end m-0">
                            <input type="hidden" name="tab" value="content">
                            <input type="hidden" name="trending_period" value="custom">
                            <div class="col-auto">
                                <label class="text-muted small fw-bold mb-1 d-block">Từ ngày</label>
                                <input type="date" name="trending_start_date" id="trending_start_date" class="form-control form-control-sm bg-dark text-white border-secondary" value="{{ $tStartDate ?? '' }}" style="width:160px;">
                            </div>
                            <div class="col-auto">
                                <label class="text-muted small fw-bold mb-1 d-block">Đến ngày</label>
                                <input type="date" name="trending_end_date" id="trending_end_date" class="form-control form-control-sm bg-dark text-white border-secondary" value="{{ $tEndDate ?? '' }}" style="width:160px;">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-primary px-4" style="height:31px; border-radius:6px;"><i class="fa-solid fa-filter me-1"></i> Áp dụng</button>
                                <a href="?tab=content&trending_period=week" class="btn btn-sm btn-outline-secondary ms-1" style="height:31px; border-radius:6px;"><i class="fa-solid fa-rotate-left"></i></a>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0" style="font-size:0.88rem;">
                        <thead style="color:#94a3b8; background:rgba(255,255,255,0.02);">
                            <tr>
                                <th width="40" class="ps-3">#</th>
                                <th>Bài hát</th>
                                <th>Nghệ sĩ</th>
                                <th>Album</th>
                                <th class="text-end pe-3">Lượt nghe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topTrendingSongs ?? [] as $i => $song)
                            <tr>
                                <td class="ps-3">
                                    @if($i < 3)
                                        <span style="color: {{ ['#ffd700','#f1f5f9','#f59e0b'][$i] }}; font-weight:700;">{{ $i+1 }}</span>
                                    @else
                                        <span class="text-muted">{{ $i+1 }}</span>
                                    @endif
                                </td>
                                <td><span class="fw-bold">{{ $song->name }}</span></td>
                                <td class="text-muted">{{ $song->artist }}</td>
                                <td class="text-muted small">{{ $song->album ?? '—' }}</td>
                                <td class="text-end pe-3">
                                    <span class="badge rounded-pill" style="background: rgba(99,102,241,0.2); color:#818cf8; border:1px solid rgba(99,102,241,0.3)">
                                        {{ number_format($song->total) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Không có dữ liệu trong khoảng thời gian này</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Genre & Artist Analysis --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="chart-container">
                <div class="chart-header flex-column align-items-start mb-3">
                    <div class="d-flex justify-content-between w-100 mb-3 align-items-center">
                        <h5 class="chart-title"><i class="fa-solid fa-layer-group me-2 text-warning"></i>Xu hướng & Nghệ sĩ</h5>
                        <div class="filter-bar p-1 bg-dark rounded-pill border border-secondary d-inline-flex">
                            <form action="" method="GET" class="d-flex m-0 align-items-center">
                                <input type="hidden" name="tab" value="content">
                                <select name="content_period" class="form-select form-select-sm bg-transparent text-white border-0 shadow-none py-0 px-3" style="width: auto; cursor: pointer; font-size: 0.75rem;" onchange="this.form.submit()">
                                    @foreach(['today' => 'Hôm nay', 'yesterday' => 'Hôm qua', 'week' => '7 ngày qua', 'month' => '30 ngày qua', 'year' => '1 năm qua', 'custom' => 'Tùy chọn lọc ngày'] as $val => $label)
                                        <option value="{{ $val }}" class="bg-dark" {{ ($contentPeriod ?? 'week') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>

                    @if(($contentPeriod ?? 'week') === 'custom')
                    <div class="filter-bar w-100 p-3 bg-dark rounded-3 border border-secondary shadow-sm mb-2" style="background: rgba(15,23,42,0.6) !important;">
                        <form action="" method="GET" class="row g-3 align-items-end m-0">
                            <input type="hidden" name="tab" value="content">
                            <input type="hidden" name="content_period" value="custom">
                            <div class="col-auto">
                                <label class="text-muted small fw-bold mb-1 d-block">Từ ngày</label>
                                <input type="date" name="content_start_date" id="content_start_date" class="form-control form-control-sm bg-dark text-white border-secondary" value="{{ $cStartDate ?? '' }}" style="width:160px;">
                            </div>
                            <div class="col-auto">
                                <label class="text-muted small fw-bold mb-1 d-block">Đến ngày</label>
                                <input type="date" name="content_end_date" id="content_end_date" class="form-control form-control-sm bg-dark text-white border-secondary" value="{{ $cEndDate ?? '' }}" style="width:160px;">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-primary px-4" style="height:31px; border-radius:6px;"><i class="fa-solid fa-filter me-1"></i> Áp dụng</button>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-xl-5">
                        <div style="height: 280px;">
                            <canvas id="genreChart"></canvas>
                        </div>
                        <div class="mt-4" style="max-height: 200px; overflow-y: auto;">
                            @foreach($topGenresContent ?? [] as $g)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary border-opacity-25">
                                <span class="small"><span class="me-2" style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $g->color ?? '#6366f1' }}"></span>{{ $g->name }}</span>
                                <span class="text-muted" style="font-size:0.75rem;">{{ number_format($g->total) }} lượt · {{ $g->song_count }} bài</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-xl-7 border-start border-secondary border-opacity-25">
                        <div class="chart-header d-flex justify-content-between pt-0 px-0">
                            <h6 class="text-muted small mb-3"><i class="fa-solid fa-user-tie me-2"></i>Top Nghệ sĩ mang lại Traffic</h6>
                            <span class="badge bg-secondary opacity-50" style="font-size:0.65rem">{{ count($topArtists) }} nghệ sĩ</span>
                        </div>
                        <div style="height: 380px;">
                            <canvas id="topArtistsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Search Analytics --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="chart-container">
                <div class="chart-header flex-column align-items-start mb-3">
                    <div class="d-flex justify-content-between w-100 mb-3 align-items-center">
                        <h5 class="chart-title"><i class="fa-solid fa-magnifying-glass me-2 text-cyan" style="color:#06b6d4"></i>Phân tích tìm kiếm</h5>
                        <div class="filter-bar p-1 bg-dark rounded-pill border border-secondary d-inline-flex">
                            <form action="" method="GET" class="d-flex m-0 align-items-center">
                                <input type="hidden" name="tab" value="content">
                                <select name="search_period" class="form-select form-select-sm bg-transparent text-white border-0 shadow-none py-0 px-3" style="width: auto; cursor: pointer; font-size: 0.75rem;" onchange="this.form.submit()">
                                    @foreach(['today' => 'Hôm nay', 'yesterday' => 'Hôm qua', 'week' => '7 ngày qua', 'month' => '30 ngày qua', 'year' => '1 năm qua', 'custom' => 'Tùy chọn lọc ngày'] as $val => $label)
                                        <option value="{{ $val }}" class="bg-dark" {{ ($searchPeriod ?? 'week') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>

                    @if(($searchPeriod ?? 'week') === 'custom')
                    <div class="filter-bar w-100 p-3 bg-dark rounded-3 border border-secondary shadow-sm mb-2" style="background: rgba(15,23,42,0.6) !important;">
                        <form action="" method="GET" class="row g-3 align-items-end m-0">
                            <input type="hidden" name="tab" value="content">
                            <input type="hidden" name="search_period" value="custom">
                            <div class="col-auto">
                                <label class="text-muted small fw-bold mb-1 d-block">Từ ngày</label>
                                <input type="date" name="search_start_date" id="search_start_date" class="form-control form-control-sm bg-dark text-white border-secondary" value="{{ $sStartDate ?? '' }}" style="width:160px;">
                            </div>
                            <div class="col-auto">
                                <label class="text-muted small fw-bold mb-1 d-block">Đến ngày</label>
                                <input type="date" name="search_end_date" id="search_end_date" class="form-control form-control-sm bg-dark text-white border-secondary" value="{{ $sEndDate ?? '' }}" style="width:160px;">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-primary px-4" style="height:31px; border-radius:6px;"><i class="fa-solid fa-filter me-1"></i> Áp dụng</button>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>

                <div class="row g-4">
                    <div class="col-xl-6">
                        <div class="card bg-dark text-white border-secondary shadow-sm">
                            <div class="card-header border-secondary d-flex justify-content-between align-items-center py-3 bg-transparent">
                                <h6 class="mb-0 small"><i class="fa-solid fa-chart-line me-2 text-cyan" style="color:#22d3ee"></i>Từ khóa xu hướng (Có kết quả)</h6>
                                <small class="text-muted" style="font-size:0.7rem">{{ number_format($totalSearches ?? 0) }} lượt tìm</small>
                            </div>
                            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-dark table-hover mb-0" style="font-size:0.82rem;">
                                    <thead style="color:#94a3b8; position:sticky; top:0; background:#1e293b; z-index:10;">
                                        <tr><th class="ps-3" width="40">#</th><th>Từ khóa</th><th class="text-end pe-4">Lượt tìm</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse($topSearchQueries ?? [] as $i => $sq)
                                        <tr>
                                            <td class="ps-3 text-muted">{{ $i+1 }}</td>
                                            <td><code class="px-2 py-1 rounded-1" style="color:#22d3ee; background:rgba(34,211,238,0.1)">{{ $sq->query }}</code></td>
                                            <td class="text-end pe-4 fw-bold">{{ number_format($sq->search_count) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center text-muted py-4">Chưa có dữ liệu</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card bg-dark text-white border-danger shadow-sm" style="border-color: rgba(239,68,68,0.2) !important;">
                            <div class="card-header d-flex justify-content-between align-items-center py-3" style="background:rgba(239,68,68,0.05); border-bottom:1px solid rgba(239,68,68,0.1)">
                                <h6 class="mb-0 text-danger small"><i class="fa-solid fa-circle-exclamation me-2"></i>Tìm kiếm KHÔNG kết quả</h6>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" style="font-size:0.65rem">{{ count($noResultQueries) }} từ khóa</span>
                            </div>
                            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-dark table-hover mb-0" style="font-size:0.82rem;">
                                    <thead style="color:#94a3b8; position:sticky; top:0; background:#1e293b; z-index:10;">
                                        <tr><th class="ps-3" width="40">#</th><th>Từ khóa</th><th class="text-end pe-4">Lượt tìm</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse($noResultQueries ?? [] as $i => $nq)
                                        <tr>
                                            <td class="ps-3 text-muted">{{ $i+1 }}</td>
                                            <td><span class="text-danger opacity-75 fw-medium">{{ $nq->query }}</span></td>
                                            <td class="text-end pe-4">
                                                <span class="badge rounded-pill bg-danger shadow-sm" style="font-size: 0.75rem; background: #ef4444 !important; min-width: 32px; padding: .35em .65em;">
                                                    {{ number_format($nq->search_count) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center text-muted py-4">Hệ thống đáp ứng tốt 🎉</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@else
    {{-- Tier 1: Key Metrics Cards (3 columns) --}}
    <div class="row g-4 mb-4 text-white">
        <div class="col-sm-4">
            <div class="stat-card glass border-top border-3" style="border-top-color: #8b5cf6 !important;">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon m-0" style="background: rgba(139, 92, 246, 0.15); color: #c4b5fd;"><i class="fa-solid fa-play"></i></div>
                    <div class="trend-indicator {{ $listensGrowth >= 0 ? 'trend-up' : 'trend-down' }}">
                        <i class="fa-solid fa-caret-{{ $listensGrowth >= 0 ? 'up' : 'down' }}"></i> {{ abs($listensGrowth) }}%
                    </div>
                </div>
                <div class="stat-value">{{ number_format($totalListens ?? 0) }}</div>
                <div class="d-flex justify-content-between align-items-end">
                    <div class="stat-label">Tổng lượt nghe</div>
                    <div class="sparkline-container"><canvas id="sparkline_listens"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card glass border-top border-3" style="border-top-color: #3b82f6 !important;">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon m-0" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa;"><i class="fa-solid fa-users"></i></div>
                    <div class="trend-indicator {{ $uniqueGrowth >= 0 ? 'trend-up' : 'trend-down' }}">
                        <i class="fa-solid fa-caret-{{ $uniqueGrowth >= 0 ? 'up' : 'down' }}"></i> {{ abs($uniqueGrowth) }}%
                    </div>
                </div>
                <div class="stat-value">{{ number_format($uniqueListeners ?? 0) }}</div>
                <div class="d-flex justify-content-between align-items-end">
                    <div class="stat-label">Người nghe (Unique)</div>
                    <div class="sparkline-container"><canvas id="sparkline_unique"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card glass border-top border-3" style="border-top-color: #ec4899 !important;">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon m-0" style="background: rgba(236, 72, 153, 0.15); color: #f472b6;"><i class="fa-solid fa-clock"></i></div>
                    <div class="trend-indicator {{ $sessionGrowth >= 0 ? 'trend-up' : 'trend-down' }}">
                        <i class="fa-solid fa-caret-{{ $sessionGrowth >= 0 ? 'up' : 'down' }}"></i> {{ abs($sessionGrowth) }}%
                    </div>
                </div>
                <div class="stat-value">{{ number_format($avgListenTimeMins ?? 0, 1) }} <span style="font-size:0.9rem; color:#94a3b8;">phút</span></div>
                <div class="d-flex justify-content-between align-items-end">
                    <div class="stat-label">Thời gian nghe TB</div>
                    <div class="sparkline-container"><canvas id="sparkline_session"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tier 2: Main Trend --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="chart-container glass p-4 text-white">
                <div class="chart-header">
                    <h5 class="chart-title"><i class="fa-solid fa-wave-square me-2" style="color:#8b5cf6;"></i>Biểu đồ mật độ lượt nghe</h5>
                    <div class="text-muted small">Biến động hoạt động nghe nhạc hằng ngày trong kỳ báo cáo</div>
                </div>
                <div style="height: 420px;">
                    <canvas id="mainDensityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Tier 3: Insights --}}
    <div class="row g-4">
        <div class="col-md-6">
            {{-- Engagement Overview --}}
            <div class="chart-container glass text-white">
                <div class="chart-header">
                    <h5 class="chart-title"><i class="fa-solid fa-microchip me-2 text-pink" style="color:#ec4899;"></i>Tổng quan tương tác</h5>
                </div>
                
                @php
                    $engagements = [
                        ['label' => 'Tỷ lệ Yêu thích / Lưu', 'val' => $favoriteRate, 'color' => '#f43f5e'],
                        ['label' => 'Thêm vào Playlist', 'val' => $playlistAddRate, 'color' => '#3b82f6'],
                        ['label' => 'Theo dõi Nghệ sĩ', 'val' => $followRate, 'color' => '#8b5cf6']
                    ];
                @endphp

                <div class="py-2">
                    @foreach($engagements as $eng)
                    <div class="mb-4">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">{{ $eng['label'] }}</span>
                            <span class="fw-bold">{{ $eng['val'] }}%</span>
                        </div>
                        <div class="engagement-bar" style="height: 12px;">
                            <div class="engagement-fill" style="width: {{ $eng['val'] }}%; background: {{ $eng['color'] }}; box-shadow: 0 0 15px {{ $eng['color'] }}55;"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-6">
            {{-- Peak Hours Widget --}}
            <div class="chart-container glass p-4 text-white">
                <div class="chart-header mb-4">
                    <h5 class="chart-title"><i class="fa-solid fa-clock-rotate-left me-2 text-info"></i>Giờ nghe cao điểm trong ngày</h5>
                    <div class="text-muted small">Khung giờ thu hút lượng nghe lớn nhất</div>
                </div>
                <div class="d-flex align-items-end mb-3" style="height: 150px;">
                    @php
                        $hours = collect($peakHours)->keyBy('hour');
                        $maxCount = count($peakHours) > 0 ? $hours->max('count') : 1;
                    @endphp
                    @for($h=0; $h<24; $h++)
                        @php $val = isset($hours[$h]) ? $hours[$h]->count : 0; @endphp
                        <div class="peak-hour-bar" style="height: {{ max(5, ($val/$maxCount)*100) }}%;" title="{{ $h }}h: {{ number_format($val) }} lượt nghe"></div>
                    @endfor
                </div>
                <div class="d-flex justify-content-between px-1 text-muted fw-bold" style="font-size:0.7rem;">
                    <span>00h</span><span>04h</span><span>08h</span><span>12h</span><span>16h</span><span>20h</span><span>23h</span>
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

    // VIP Revenue Chart
    const vipRevCtx = document.getElementById('vipRevenueChart');
    if(vipRevCtx) {
        new Chart(vipRevCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($vipRevenueTrend->pluck('chart_date')) !!},
                datasets: [{
                    label: 'Doanh thu VIP (VNĐ)',
                    data: {!! json_encode($vipRevenueTrend->pluck('total')) !!},
                    backgroundColor: '#fbbf24',
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

    // Artist Revenue Chart
    const artRevCtx = document.getElementById('artistRevenueChart');
    if(artRevCtx) {
        new Chart(artRevCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($artistRevenueTrend->pluck('chart_date')) !!},
                datasets: [{
                    label: 'Doanh thu Nghệ Sĩ (VNĐ)',
                    data: {!! json_encode($artistRevenueTrend->pluck('total')) !!},
                    backgroundColor: '#c084fc',
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

@elseif($tab === 'content')
    const topSongsCtx = document.getElementById('topSongsChart');
    const topArtistsCtx = document.getElementById('topArtistsChart');
    const genreCtx = document.getElementById('genreChart');

    if(topArtistsCtx) {
        new Chart(topArtistsCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($topArtists->pluck('name')) !!},
                datasets: [{
                    label: 'Lượt nghe',
                    data: {!! json_encode($topArtists->pluck('total')) !!},
                    backgroundColor: '#06b6d4',
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true }, y: { grid: { display: false } } }
            }
        });
    }

    if(genreCtx) {
        const genreColors = {!! json_encode($topGenresContent->pluck('color')) !!};
        new Chart(genreCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($topGenresContent->pluck('name')) !!},
                datasets: [{
                    data: {!! json_encode($topGenresContent->pluck('total')) !!},
                    backgroundColor: genreColors.length ? genreColors : ['#6366f1','#ec4899','#f59e0b','#10b981','#3b82f6','#8b5cf6','#06b6d4','#f97316'],
                    borderWidth: 0, hoverOffset: 8
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                cutout: '60%'
            }
        });
    }

@else
    // Redesigned Listens Tab Charts
    const listenLabels = {!! json_encode($listenTrend->pluck('date')) !!};
    const listenData = {!! json_encode($listenTrend->pluck('total')) !!};
    
    // Main Density Chart (Tier 2)
    const ctxDensity = document.getElementById('mainDensityChart');
    if(ctxDensity) {
        const gradient = ctxDensity.getContext('2d').createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(139, 92, 246, 0.3)');
        gradient.addColorStop(1, 'rgba(139, 92, 246, 0)');

        new Chart(ctxDensity, {
            type: 'line',
            data: {
                labels: listenLabels,
                datasets: [{
                    label: 'Streaming Density',
                    data: listenData,
                    borderColor: '#8b5cf6',
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#8b5cf6',
                    pointHoverRadius: 6,
                    fill: true,
                    backgroundColor: gradient,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.03)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Mini Sparklines (Tier 1)
    const commonSparkOptions = {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        scales: { x: { display: false }, y: { display: false } },
        elements: { point: { radius: 0 }, line: { borderWidth: 2, tension: 0.4 } }
    };

    const sparkData = listenData.slice(-7); // Last 7 points for sparkline
    
    ['listens', 'unique', 'session', 'subs'].forEach((id, idx) => {
        const ctx = document.getElementById('sparkline_' + id);
        if(ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [1,2,3,4,5,6,7],
                    datasets: [{
                        data: sparkData.map(v => v * (1 + (Math.random() * 0.2 - 0.1))), // Mock unique variations
                        borderColor: ['#8b5cf6', '#3b82f6', '#ec4899', '#10b981'][idx],
                        fill: false
                    }]
                },
                options: commonSparkOptions
            });
        }
    });
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

// Bắt lỗi ngày bắt đầu < ngày kết thúc và áp dụng min/max giống Artist Studio
function initSectionFilters() {
    const filters = [
        { start: 'trending_start_date', end: 'trending_end_date' },
        { start: 'content_start_date', end: 'content_end_date' },
        { start: 'search_start_date', end: 'search_end_date' },
        { start: 'vip_start_date', end: 'vip_end_date' },
        { start: 'artist_start_date', end: 'artist_end_date' }
    ];

    const today = new Date().toISOString().split('T')[0];

    filters.forEach(f => {
        const sd = document.getElementById(f.start);
        const ed = document.getElementById(f.end);

        if (sd && ed) {
            sd.max = today;
            ed.max = today;

            sd.addEventListener('change', function() {
                ed.min = this.value || '';
                if (ed.value && ed.value < this.value) {
                    ed.value = this.value;
                }
            });

            ed.addEventListener('change', function() {
                if (sd.value && this.value < sd.value) {
                    this.value = sd.value;
                }
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', initSectionFilters);

// Thông báo alert nếu vi phạm (dự phòng cho form submit)
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const sd = this.querySelector('input[type="date"][name$="_start_date"]');
        const ed = this.querySelector('input[type="date"][name$="_end_date"]');
        
        if (sd && ed && sd.value && ed.value) {
            if (new Date(sd.value) > new Date(ed.value)) {
                e.preventDefault();
                alert('⚠️ Lỗi: Ngày bắt đầu không được lớn hơn ngày kết thúc!');
            }
        }
    });
});
</script>
@endpush
