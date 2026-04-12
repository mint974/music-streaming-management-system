@extends('layouts.main')

@section('title', 'Bài hát yêu thích')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h4 class="text-white mb-1">Bài hát yêu thích</h4>
            <p class="text-muted mb-0">Xem chi tiết các bài đã thích, sắp xếp và lọc như lịch sử nghe.</p>
        </div>
        <a href="{{ route('listener.index') }}" class="btn mm-btn mm-btn-outline">
            <i class="fa-solid fa-grid-2"></i>
            Quay lại listener
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm listener-panel">
                <div class="card-body">
                    <div class="text-muted small mb-1">Tổng yêu thích</div>
                    <div class="text-white fw-semibold fs-4">{{ number_format($favorites->total()) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm listener-panel">
                <div class="card-body">
                    <div class="text-muted small mb-1">Đang hiển thị</div>
                    <div class="text-white fw-semibold fs-4">{{ number_format($favorites->count()) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm listener-panel">
                <div class="card-body">
                    <div class="text-muted small mb-1">Bộ lọc</div>
                    <div class="text-white fw-semibold fs-4">{{ $filters['q'] !== '' ? 'Có' : 'Không' }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm listener-panel">
                <div class="card-body">
                    <div class="text-muted small mb-1">Sắp xếp</div>
                    <div class="text-white fw-semibold fs-4 text-capitalize">{{ $filters['sort'] ?? 'recent' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-bar mb-4">
        <form method="GET" action="{{ route('listener.favorites') }}" class="filter-bar-inner">
                <div class="filter-field flex-grow-1" style="min-width: 240px;">
                    <label class="filter-label">Tìm bài hát</label>
                    <div class="filter-search-wrap">
                        <i class="fa-solid fa-magnifying-glass filter-search-icon"></i>
                        <input type="text" name="q" class="filter-input" value="{{ $filters['q'] ?? '' }}" placeholder="Tên bài hát, nghệ sĩ, album">
                    </div>
                </div>

                <div class="filter-field" style="min-width: 160px;">
                    <label class="filter-label">Sắp xếp</label>
                    <select class="filter-select" name="sort">
                        <option value="recent" @selected(($filters['sort'] ?? 'recent') === 'recent')>Mới thích</option>
                        <option value="oldest" @selected(($filters['sort'] ?? 'recent') === 'oldest')>Cũ nhất</option>
                        <option value="title" @selected(($filters['sort'] ?? 'recent') === 'title')>Theo tên</option>
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

                <div class="filter-actions">
                    <button type="submit" class="btn mm-btn mm-btn-primary">
                        <i class="fa-solid fa-filter"></i>
                        Lọc
                    </button>
                    <a href="{{ route('listener.favorites') }}" class="btn mm-btn mm-btn-ghost">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
        </form>
    </div>

    <div class="d-grid gap-3">
        @forelse($favorites as $item)
            @php $song = $item->song; @endphp
            @if($song)
                <div class="card border-0 shadow-sm listener-entity-card" style="overflow:hidden;">
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <a href="{{ route('songs.show', $song->id) }}">
                                    <img src="{{ $song->getCoverUrl() }}" alt="{{ $song->title }}" class="listener-entity-cover">
                                </a>
                            </div>
                            <div class="col">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <h5 class="text-white mb-0 fs-6 fw-semibold">
                                        <a href="{{ route('songs.show', $song->id) }}" class="listener-entity-title">{{ $song->title }}</a>
                                    </h5>
                                    @if($song->is_vip)
                                        <span class="badge rounded-pill text-bg-warning text-dark">Premium</span>
                                    @endif
                                </div>
                                <div class="listener-entity-meta mb-2">
                                    {{ $song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }}
                                    @if($song->album)
                                        <span class="mx-1">•</span>{{ $song->album->title }}
                                    @endif
                                    <span class="mx-1">•</span>{{ $item->created_at?->format('d/m/Y H:i') }}
                                </div>
                                <div class="listener-entity-actions">
                                    <button
                                        type="button"
                                        class="btn mm-btn mm-btn-primary btn-sm js-play-song"
                                        data-song-id="{{ $song->id }}"
                                        data-song-title="{{ e($song->title) }}"
                                        data-song-artist="{{ e($song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ') }}"
                                        data-song-cover="{{ $song->getCoverUrl() }}"
                                        data-song-premium="{{ $song->is_vip ? '1' : '0' }}"
                                        data-stream-url="{{ route('songs.stream', $song->id) }}">
                                        Phát
                                    </button>
                                    <a href="{{ route('songs.show', $song->id) }}" class="btn mm-btn mm-btn-outline btn-sm">Chi tiết</a>
                                    <form method="POST" action="{{ route('listener.song.toggleFavorite', $song->id) }}" class="m-0" data-confirm-message="Bỏ thích bài hát {{ $song->title }}?" data-confirm-title="Bỏ thích bài hát">
                                        @csrf
                                        <button type="submit" class="btn mm-btn mm-btn-danger btn-sm">Bỏ thích</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="card border-0 shadow-sm listener-panel">
                <div class="card-body text-center text-muted py-5">
                    <i class="fa-solid fa-heart fa-2x mb-3 opacity-25 d-block"></i>
                    <div>Bạn chưa yêu thích bài hát nào hoặc bộ lọc chưa khớp.</div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $favorites->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
