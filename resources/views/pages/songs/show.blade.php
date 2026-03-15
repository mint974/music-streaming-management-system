@extends('layouts.main')

@section('title', $song->title . ' - Blue Wave Music')

@section('content')
@php
    $artistName = $song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ';
    $discCover = $song->cover_image ? $song->getCoverUrl() : asset('images/disk.png');
@endphp

<div class="song-detail-page">
    <section class="song-detail-hero">
        <div class="left-pane">
            <div class="detail-disc {{ $song->cover_image ? 'has-cover' : 'is-fallback' }}">
                <img src="{{ $discCover }}" alt="{{ $song->title }}">
            </div>

            <h1 class="detail-title mt-3">
                {{ $song->title }}
                @if($song->is_vip)
                    <i class="fa-solid fa-crown premium-crown ms-1" title="Bài hát Premium"></i>
                @endif
            </h1>

            <div class="detail-meta">{{ $artistName }} • {{ number_format((int) $song->listens) }} lượt nghe • {{ $song->durationFormatted() }}</div>

            <div class="detail-actions mt-3">
                <button
                    type="button"
                    class="btn btn-primary js-play-song"
                    data-song-id="{{ $song->id }}"
                    data-song-title="{{ e($song->title) }}"
                    data-song-artist="{{ e($artistName) }}"
                    data-song-cover="{{ $song->getCoverUrl() }}"
                    data-song-premium="{{ $song->is_vip ? '1' : '0' }}"
                    data-song-favorited="{{ $isFavorited ? '1' : '0' }}"
                    data-stream-url="{{ route('songs.stream', $song->id) }}">
                    <i class="fa-solid fa-play me-1"></i>Phát bài hát
                </button>

                @auth
                <form method="POST" action="{{ route('listener.song.toggleFavorite', $song->id) }}">
                    @csrf
                    <button class="btn {{ $isFavorited ? 'btn-danger' : 'btn-outline-light' }}">
                        <i class="fa-solid fa-heart me-1"></i>{{ $isFavorited ? 'Đã yêu thích' : 'Yêu thích' }}
                    </button>
                </form>
                @endauth
            </div>
        </div>

        <div class="right-pane">
            <div class="tracklist-head">
                <span>Bài hát</span>
                <span>Thời gian</span>
            </div>

            <div class="tracklist-item is-active">
                <div class="track-main">
                    <img src="{{ $song->getCoverUrl() }}" alt="{{ $song->title }}">
                    <div>
                        <div class="name">{{ $song->title }}</div>
                        <div class="artist">{{ $artistName }}</div>
                    </div>
                </div>
                <div class="duration">{{ $song->durationFormatted() }}</div>
            </div>

            @foreach($artistSongs->take(4) as $item)
            @php $itemFavorited = in_array((int) $item->id, $favoriteSongIds, true); @endphp
            <div class="tracklist-item">
                <div class="track-main">
                    <img src="{{ $item->getCoverUrl() }}" alt="{{ $item->title }}">
                    <div>
                        <a class="name" href="{{ route('songs.show', $item->id) }}">{{ $item->title }}</a>
                        <div class="artist">
                            {{ $item->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }}
                            @if($item->is_vip)
                                <i class="fa-solid fa-crown premium-crown ms-1"></i>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-info js-play-song"
                        data-song-id="{{ $item->id }}"
                        data-song-title="{{ e($item->title) }}"
                        data-song-artist="{{ e($item->artist?->getDisplayArtistName() ?? 'Nghệ sĩ') }}"
                        data-song-cover="{{ $item->getCoverUrl() }}"
                        data-song-premium="{{ $item->is_vip ? '1' : '0' }}"
                        data-song-favorited="{{ $itemFavorited ? '1' : '0' }}"
                        data-stream-url="{{ route('songs.stream', $item->id) }}">
                        <i class="fa-solid fa-play"></i>
                    </button>
                    <div class="duration">{{ $item->durationFormatted() }}</div>
                </div>
            </div>
            @endforeach

            <div class="song-detail-info">
                <h6>Thông tin</h6>
                <div class="row-item"><span>Genre</span><strong>{{ $song->genre?->name ?? 'Khác' }}</strong></div>
                <div class="row-item"><span>Album</span><strong>{{ $song->album?->title ?? 'Single' }}</strong></div>
                <div class="row-item"><span>Phát hành</span><strong>{{ $song->released_date?->format('Y') ?? 'N/A' }}</strong></div>
                <div class="row-item"><span>Cung cấp bởi</span><strong>{{ $artistName }}</strong></div>
            </div>
        </div>
    </section>

    <section class="artist-more-songs mt-4">
        <div class="section-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Các bài hát khác của {{ $artistName }}</h5>
            <a href="{{ route('songs.index', ['q' => $artistName]) }}" class="btn btn-sm btn-outline-light">Xem tất cả</a>
        </div>

        <div class="songs-card-grid mt-3">
            @forelse($artistSongs as $song)
                @include('pages.songs.partials.song-card', ['song' => $song, 'favoriteSongIds' => $favoriteSongIds])
            @empty
                <div class="songs-empty"><p class="mb-0 text-muted">Chưa có bài hát liên quan.</p></div>
            @endforelse
        </div>
    </section>
</div>
@endsection
