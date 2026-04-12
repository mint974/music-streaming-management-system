@extends('layouts.main')

@section('title', 'Lịch sử nghe')

@section('content')
<div class="container py-4">
    @php
        $summarySeconds = (int) ($summary->total_seconds ?? 0);
        $summaryHours = floor($summarySeconds / 3600);
        $summaryMinutes = floor(($summarySeconds % 3600) / 60);
        $summaryDurationLabel = $summaryHours > 0
            ? $summaryHours . ' giờ ' . $summaryMinutes . ' phút'
            : $summaryMinutes . ' phút';
    @endphp

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h4 class="text-white mb-0">Lịch sử nghe</h4>
        <form method="POST" action="{{ route('listener.history.clear') }}" data-confirm-message="Xóa toàn bộ lịch sử nghe?" data-confirm-title="Xóa lịch sử nghe">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn mm-btn mm-btn-danger">
                <i class="fa-solid fa-trash-can"></i>
                Xóa toàn bộ
            </button>
        </form>
    </div>

    <div class="filter-bar">
        <form method="GET" action="{{ route('listener.history') }}" class="filter-bar-inner">
            <div class="filter-field flex-grow-1" style="min-width: 220px;">
                <label class="filter-label">Tìm kiếm</label>
                <div class="filter-search-wrap">
                    <i class="fa-solid fa-magnifying-glass filter-search-icon"></i>
                    <input
                        type="text"
                        name="q"
                        class="filter-input"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="Tên bài hát hoặc nghệ sĩ">
                </div>
            </div>

            <div class="filter-field" style="min-width: 180px;">
                <label class="filter-label">Sắp xếp</label>
                <select class="filter-select" name="sort">
                    <option value="recent" @selected($filters['sort'] === 'recent')>Nghe gần nhất</option>
                    <option value="oldest" @selected($filters['sort'] === 'oldest')>Nghe xa nhất</option>
                    <option value="most_listened" @selected($filters['sort'] === 'most_listened')>Bài nghe nhiều nhất</option>
                </select>
            </div>

            <div class="filter-field" style="min-width: 170px;">
                <label class="filter-label">Trạng thái</label>
                <select class="filter-select" name="status">
                    <option value="all" @selected($filters['status'] === 'all')>Tất cả</option>
                    <option value="unfinished" @selected($filters['status'] === 'unfinished')>Nghe dang dở</option>
                </select>
            </div>

            <div class="filter-field" style="min-width: 170px;">
                <label class="filter-label">Thể loại</label>
                <select class="filter-select" name="genre_id">
                    <option value="">Tất cả thể loại</option>
                    @foreach($genres as $genre)
                        <option value="{{ $genre->id }}" @selected((string) ($filters['genre_id'] ?? '') === (string) $genre->id)>{{ $genre->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field" style="min-width: 180px;">
                <label class="filter-label">Nghệ sĩ</label>
                <select class="filter-select" name="artist_id">
                    <option value="">Tất cả nghệ sĩ</option>
                    @foreach($artists as $artist)
                        <option value="{{ $artist->id }}" @selected((string) ($filters['artist_id'] ?? '') === (string) $artist->id)>{{ $artist->getDisplayArtistName() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field" style="min-width: 160px;">
                <label class="filter-label">Từ ngày</label>
                <input type="date" class="filter-input" name="from_date" value="{{ $filters['from_date'] ?? '' }}">
            </div>

            <div class="filter-field" style="min-width: 160px;">
                <label class="filter-label">Đến ngày</label>
                <input type="date" class="filter-input" name="to_date" value="{{ $filters['to_date'] ?? '' }}">
            </div>

            <div class="filter-field" style="min-width: 140px;">
                <label class="filter-label">Từ giờ</label>
                <input type="time" class="filter-input" name="from_time" value="{{ $filters['from_time'] ?? '' }}">
            </div>

            <div class="filter-field" style="min-width: 140px;">
                <label class="filter-label">Đến giờ</label>
                <input type="time" class="filter-input" name="to_time" value="{{ $filters['to_time'] ?? '' }}">
            </div>

            <div class="filter-field" style="min-width: 150px;">
                <label class="filter-label">Biểu đồ theo</label>
                <select class="filter-select" name="chart_group">
                    <option value="day" @selected($filters['chart_group'] === 'day')>Ngày</option>
                    <option value="hour" @selected($filters['chart_group'] === 'hour')>Giờ</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn mm-btn mm-btn-primary">
                    <i class="fa-solid fa-filter"></i>
                    Áp dụng
                </button>
                <a href="{{ route('listener.history') }}" class="btn mm-btn mm-btn-ghost">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-4">
            <div class="card h-100" style="background:#0b1220;border:1px solid #1f2937">
                <div class="card-body">
                    <div class="text-muted small">Lượt nghe đã lọc</div>
                    <div class="text-white fw-semibold fs-4">{{ number_format((int) ($summary->total_listens ?? 0)) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100" style="background:#0b1220;border:1px solid #1f2937">
                <div class="card-body">
                    <div class="text-muted small">Số bài hát khác nhau</div>
                    <div class="text-white fw-semibold fs-4">{{ number_format((int) ($summary->unique_songs ?? 0)) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100" style="background:#0b1220;border:1px solid #1f2937">
                <div class="card-body">
                    <div class="text-muted small">Tổng thời gian nghe</div>
                    <div class="text-white fw-semibold fs-4">{{ $summaryDurationLabel }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="card h-100" style="background:#111827;border:1px solid #1f2937">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-white mb-0">Biểu đồ tổng thời gian nghe</h6>
                        <small class="text-muted">Theo bộ lọc hiện tại</small>
                    </div>
                    <div style="height:320px">
                        <canvas id="historyDurationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100" style="background:#111827;border:1px solid #1f2937">
                <div class="card-body">
                    <h6 class="text-white mb-3">Top bài nghe lại</h6>
                    @forelse($topSongs as $topItem)
                        @php $topSong = $topItem->song; @endphp
                        @if($topSong)
                            <div class="d-flex justify-content-between align-items-start py-2 border-bottom border-secondary border-opacity-25">
                                <div>
                                    <div class="text-white small fw-semibold">{{ $topSong->title }}</div>
                                    <div class="text-muted" style="font-size:.75rem">{{ $topSong->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }}</div>
                                </div>
                                <span class="badge bg-primary-subtle text-primary-emphasis">{{ (int) $topItem->replay_count }} lần</span>
                            </div>
                        @endif
                    @empty
                        <p class="text-muted mb-0">Chưa có dữ liệu.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="d-grid gap-3">
        @forelse($histories as $item)
            @php $song = $item->song; @endphp
            @if($song)
            <div class="card border-0 shadow-sm" style="background:#111827;border:1px solid #1f2937;overflow:hidden;">
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto">
                            <a href="{{ route('songs.show', $song->id) }}" class="d-inline-block" aria-label="{{ $song->title }}">
                                <img
                                    src="{{ $song->getCoverUrl() }}"
                                    alt="{{ $song->title }}"
                                    class="rounded-3"
                                    style="width:88px;height:88px;object-fit:cover;border:1px solid rgba(255,255,255,.08)">
                            </a>
                        </div>
                        <div class="col">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <h5 class="text-white mb-0 fs-6 fw-semibold">
                                    <a href="{{ route('songs.show', $song->id) }}" class="text-decoration-none text-white">
                                        {{ $song->title }}
                                    </a>
                                </h5>
                                @if($song->is_vip)
                                    <span class="badge rounded-pill text-bg-warning text-dark">Premium</span>
                                @endif
                            </div>

                            <div class="text-muted small mb-2">
                                {{ $song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }}
                                @if($song->genre)
                                    <span class="mx-1">•</span>{{ $song->genre->name }}
                                @endif
                                <span class="mx-1">•</span>{{ $item->listened_at?->format('d/m/Y H:i') }}
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                @if(!is_null($item->played_percent))
                                    <span class="badge rounded-pill text-bg-secondary">{{ number_format((float) $item->played_percent, 0) }}%</span>
                                @endif
                                @if($item->is_completed)
                                    <span class="badge rounded-pill text-bg-success">Hoàn tất</span>
                                @else
                                    <span class="badge rounded-pill text-bg-warning text-dark">Dang dở</span>
                                @endif
                                @if(!is_null($item->played_seconds))
                                    <span class="badge rounded-pill text-bg-dark">{{ gmdate('i:s', (int) $item->played_seconds) }}</span>
                                @endif
                            </div>

                        </div>
                        <div class="col-auto">
                            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                <button
                                    type="button"
                                    class="btn mm-btn mm-btn-primary js-play-song"
                                    data-song-id="{{ $song->id }}"
                                    data-song-title="{{ e($song->title) }}"
                                    data-song-artist="{{ e($song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ') }}"
                                    data-song-cover="{{ $song->getCoverUrl() }}"
                                    data-song-premium="{{ $song->is_vip ? '1' : '0' }}"
                                    data-stream-url="{{ route('songs.stream', $song->id) }}">
                                    <i class="fa-solid fa-play"></i>
                                    Phát
                                </button>
                                <form method="POST" action="{{ route('listener.history.remove', $item->id) }}" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn mm-btn mm-btn-danger">
                                        <i class="fa-solid fa-trash-can"></i>
                                        Xóa
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @empty
            <div class="card" style="background:#111827;border:1px solid #1f2937">
                <div class="card-body text-muted">Chưa có lịch sử nghe.</div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">{{ $histories->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
        (() => {
            const chartData = @json($chartData);
            const canvas = document.getElementById('historyDurationChart');
            if (!canvas || typeof Chart === 'undefined') {
                return;
            }

            new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Phút nghe',
                        data: chartData.minutes,
                        borderColor: 'rgba(59, 130, 246, 0.95)',
                        backgroundColor: 'rgba(59, 130, 246, 0.18)',
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        borderWidth: 2,
                        tension: 0.35,
                        fill: true,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: {
                                color: '#9ca3af',
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 12,
                            },
                            grid: { color: 'rgba(148, 163, 184, 0.12)' },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#9ca3af' },
                            grid: { color: 'rgba(148, 163, 184, 0.12)' },
                        },
                    },
                    plugins: {
                        legend: {
                            labels: { color: '#e5e7eb' },
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        },
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                },
            });
        })();
    </script>
@endpush
