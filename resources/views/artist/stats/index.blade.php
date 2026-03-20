@extends('layouts.artist')
@section('title', 'Thống kê & Báo cáo')

@push('styles')
<style>
.stat-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 1rem;
    padding: 1.5rem;
    transition: transform 0.2s, box-shadow 0.2s;
    height: 100%;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    border-color: rgba(168, 85, 247, 0.3);
}
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.stat-value {
    font-size: 2rem;
    font-weight: 800;
    margin: 0.5rem 0 0.2rem;
    color: #fff;
}
.stat-label {
    color: #94a3b8;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
}
.trend-up { color: #10b981; font-size: 0.8rem; font-weight: 600; }
.chart-container {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 1rem;
    padding: 1.5rem;
}
.top-song-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 12px;
    margin-bottom: 12px;
    transition: background 0.2s;
}
.top-song-item:hover {
    background: rgba(255, 255, 255, 0.05);
}
.top-song-cover {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    object-fit: cover;
    margin-right: 16px;
}
.top-song-idx {
    width: 24px;
    font-weight: 800;
    color: #64748b;
    font-size: 1.2rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-white mb-1"><i class="fa-solid fa-chart-line text-primary me-2"></i>Tổng quan hiệu suất</h2>
            <p class="text-muted small">Cập nhật lúc: {{ now()->format('H:i d/m/Y') }}</p>
        </div>
        <button class="btn btn-sm btn-outline-secondary rounded-pill" onclick="window.location.reload()"><i class="fa-solid fa-rotate-right me-1"></i>Làm mới</button>
    </div>

    {{-- Thẻ số liệu --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Tổng lượt nghe</div>
                        <div class="stat-value">{{ number_format($totalListens) }}</div>
                        <div class="trend-up"><i class="fa-solid fa-arrow-trend-up me-1"></i>+{{ number_format($todayListens) }} hôm nay</div>
                    </div>
                    <div class="stat-icon" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                        <i class="fa-solid fa-headphones"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Lượt nghe tuần này</div>
                        <div class="stat-value">{{ number_format($weekListens) }}</div>
                        <div class="text-muted small"><i class="fa-regular fa-calendar-days me-1"></i>7 ngày qua</div>
                    </div>
                    <div class="stat-icon" style="background: rgba(168, 85, 247, 0.15); color: #a855f7;">
                        <i class="fa-solid fa-play"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Lượt nghe tháng này</div>
                        <div class="stat-value">{{ number_format($monthListens) }}</div>
                        <div class="text-muted small"><i class="fa-regular fa-calendar me-1"></i>Trong tháng {{ now()->month }}</div>
                    </div>
                    <div class="stat-icon" style="background: rgba(236, 72, 153, 0.15); color: #ec4899;">
                        <i class="fa-solid fa-compact-disc"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Người theo dõi</div>
                        <div class="stat-value">{{ number_format($totalFollowers) }}</div>
                        @if($recentFollowers > 0)
                        <div class="trend-up"><i class="fa-solid fa-arrow-trend-up me-1"></i>+{{ number_format($recentFollowers) }} tuần này</div>
                        @else
                        <div class="text-muted small">Chưa có theo dõi mới</div>
                        @endif
                    </div>
                    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Growth Chart --}}
        <div class="col-lg-8">
            <div class="chart-container h-100">
                <h5 class="text-white fw-bold mb-4"><i class="fa-solid fa-chart-area me-2 text-muted"></i>Biểu đồ Lượt nghe (30 ngày)</h5>
                <div style="height: 300px;">
                    <canvas id="growthChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Top bài hát --}}
        <div class="col-lg-4">
            <div class="chart-container h-100">
                <h5 class="text-white fw-bold mb-4"><i class="fa-solid fa-trophy me-2 text-warning"></i>Top 5 Bài hát Phổ biến</h5>
                @forelse($topSongs as $index => $tsr)
                    <div class="top-song-item">
                        <div class="top-song-idx">#{{ $index + 1 }}</div>
                        <img src="{{ $tsr->cover_image ? asset($tsr->cover_image) : asset('storage/disk.png') }}" class="top-song-cover">
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="text-white fw-bold text-truncate">{{ $tsr->title }}</div>
                            <div class="text-muted small">{{ number_format($tsr->listens) }} lượt nghe</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="fa-solid fa-music fa-2x mb-2 opacity-50"></i>
                        <br>Chưa có dữ liệu lượt nghe
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Gender distribution --}}
        <div class="col-md-4">
            <div class="chart-container">
                <h6 class="text-white fw-bold text-center mb-3">Phân bố Giới tính</h6>
                <div style="height: 220px; position: relative;">
                    <canvas id="genderChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Age distribution --}}
        <div class="col-md-4">
            <div class="chart-container">
                <h6 class="text-white fw-bold text-center mb-3">Độ tuổi Thính giả</h6>
                <div style="height: 220px; position: relative;">
                    <canvas id="ageChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Source distribution --}}
        <div class="col-md-4">
            <div class="chart-container">
                <h6 class="text-white fw-bold text-center mb-3">Nguồn Phát</h6>
                <div style="height: 220px; position: relative;">
                    <canvas id="sourceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = "'Inter', sans-serif";

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } }
        }
    };

    // 1. Growth Area Chart
    const growthLabels = {!! $growthChart->pluck('day')->toJson() !!};
    const growthData = {!! $growthChart->pluck('listens')->toJson() !!};
    
    new Chart(document.getElementById('growthChart'), {
        type: 'line',
        data: {
            labels: growthLabels,
            datasets: [{
                label: 'Lượt nghe',
                data: growthData,
                borderColor: '#a855f7',
                backgroundColor: 'rgba(168, 85, 247, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#a855f7',
                pointRadius: 3,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { grid: { color: 'rgba(255, 255, 255, 0.05)' }, beginAtZero: true },
                x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } }
            }
        }
    });

    // 2. Gender Doughnut Chart
    const genderDist = {!! json_encode($genderDist) !!};
    new Chart(document.getElementById('genderChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(genderDist),
            datasets: [{
                data: Object.values(genderDist),
                backgroundColor: ['#3b82f6', '#ec4899', '#64748b'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: commonOptions
    });

    // 3. Age Bar Chart
    const ageDist = {!! json_encode($ageDist) !!};
    new Chart(document.getElementById('ageChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(ageDist),
            datasets: [{
                label: 'Số lượng',
                data: Object.values(ageDist),
                backgroundColor: 'rgba(52, 211, 153, 0.8)',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { display: false, beginAtZero: true }
            }
        }
    });

    // 4. Source Pie Chart
    const sourceDist = {!! json_encode($sourceDist) !!};
    let sLabels = Object.keys(sourceDist).map(l => l === 'stream' ? 'Nghe trực tuyến' : (l === 'download' ? 'Tải offline' : l));
    
    new Chart(document.getElementById('sourceChart'), {
        type: 'pie',
        data: {
            labels: sLabels,
            datasets: [{
                data: Object.values(sourceDist),
                backgroundColor: ['#f59e0b', '#10b981', '#6366f1', '#8b5cf6'],
                borderWidth: 0
            }]
        },
        options: commonOptions
    });
</script>
@endpush
