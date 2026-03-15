@extends('layouts.main')

@section('title', 'Albums - Blue Wave Music')

@section('content')
<div class="albums-page">
    @php
        $albumsTotal = is_object($albums) && method_exists($albums, 'total') ? $albums->total() : count($albums);
    @endphp

    <section class="songs-hero albums-hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="songs-eyebrow">Latest Albums</div>
            <h1 class="songs-title">Khám phá album theo vibe riêng</h1>
            <p class="songs-subtitle">Xem album mới, mở chi tiết và phát từng bài ngay trong player footer hiện tại của hệ thống.</p>

            <form method="GET" action="{{ route('albums.index') }}" class="songs-search-inline">
                <input type="text" name="q" value="{{ $q }}" placeholder="Tìm album hoặc nghệ sĩ...">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="limit" value="{{ $cardsLimit }}">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
        </div>

        <div class="songs-hero-stat">
            <div class="label">Hiển thị</div>
            <div class="value">{{ $albumsTotal }}</div>
            <div class="sub">album</div>
        </div>
    </section>

    <form method="GET" action="{{ route('albums.index') }}" class="songs-toolbar">
        <input type="hidden" name="q" value="{{ $q }}">

        <div class="toolbar-item">
            <label>Sắp xếp</label>
            <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Phổ biến</option>
                <option value="az" {{ $sort === 'az' ? 'selected' : '' }}>A-Z</option>
            </select>
        </div>

        <div class="toolbar-item">
            <label>Giới hạn card</label>
            <select name="limit" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach([6, 8, 10, 12, 16, 20] as $limit)
                    <option value="{{ $limit }}" {{ $cardsLimit === $limit ? 'selected' : '' }}>{{ $limit }} album</option>
                @endforeach
            </select>
        </div>

        <a href="{{ route('albums.index') }}" class="btn btn-sm btn-outline-light ms-auto">Reset</a>
    </form>

    <div class="songs-content-grid">
        <section>
            <div class="albums-card-grid">
                @forelse($albums as $album)
                    @include('pages.albums.partials.album-card', ['album' => $album, 'savedAlbumIds' => $savedAlbumIds])
                @empty
                    <div class="songs-empty">
                        <i class="fa-solid fa-compact-disc"></i>
                        <p class="mb-0">Không có album phù hợp bộ lọc hiện tại.</p>
                    </div>
                @endforelse
            </div>

            @if(is_object($albums) && method_exists($albums, 'hasPages') && $albums->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $albums->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </section>

        <aside class="songs-top-panel">
            <div class="top-panel-head">
                <h5 class="mb-1">Top Album</h5>
                <small class="text-muted">Theo tổng lượt nghe bài trong album</small>
            </div>

            <div class="top-list mt-3">
                @forelse($topAlbums as $index => $album)
                    <a href="{{ route('albums.show', $album->id) }}" class="top-song-item text-decoration-none">
                        <span class="rank">#{{ $index + 1 }}</span>
                        <span class="title-wrap">
                            <span class="title">{{ $album->title }}</span>
                            <span class="meta">
                                {{ $album->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }} • {{ number_format((int) ($album->published_songs_listens ?? 0)) }} lượt nghe
                            </span>
                        </span>
                        <span class="play-icon"><i class="fa-solid fa-circle-info"></i></span>
                    </a>
                @empty
                    <div class="text-muted small">Chưa đủ dữ liệu album.</div>
                @endforelse
            </div>
        </aside>
    </div>
</div>
@endsection
