@extends('layouts.main')

@section('title', $album->title . ' - Album - Blue Wave Music')

@section('content')
@php
    $artistName = $album->artist?->getDisplayArtistName() ?? 'Nghệ sĩ';
    $durationText = sprintf('%d:%02d', intdiv((int) $albumDuration, 60), ((int) $albumDuration) % 60);
@endphp

<div class="song-detail-page album-detail-page">
    <section class="song-detail-hero">
        <div class="left-pane">
            <div class="detail-cover-shell">
                <img src="{{ $album->getCoverUrl() }}" alt="{{ $album->title }}" class="detail-cover-image">
            </div>

            <h1 class="detail-title mt-3">{{ $album->title }}</h1>
            <div class="detail-meta">{{ $artistName }}</div>

            <div class="detail-chip-row mt-3">
                <span class="detail-chip"><i class="fa-solid fa-music me-1"></i>{{ $tracks->count() }} bài hát</span>
                <span class="detail-chip"><i class="fa-regular fa-clock me-1"></i>{{ $durationText }}</span>
                <span class="detail-chip"><i class="fa-regular fa-calendar me-1"></i>{{ $album->released_date?->format('d/m/Y') ?? 'Chưa cập nhật' }}</span>
            </div>

            @auth
            <div class="detail-actions mt-3">
                <form method="POST" action="{{ route('listener.album.toggleSave', $album->id) }}">
                    @csrf
                    <button class="btn {{ $isSaved ? 'btn-album-saved px-3' : 'btn-album-save px-3' }}">
                        <i class="fa-solid fa-bookmark me-1"></i>{{ $isSaved ? 'Đã lưu album' : 'Lưu album' }}
                    </button>
                </form>
            </div>
            @endauth

            @if($album->description)
            <div class="song-detail-info mt-3">
                <h6>Mô tả album</h6>
                <div class="text-muted small" style="line-height:1.65">{{ $album->description }}</div>
            </div>
            @endif
        </div>

        <div class="right-pane">
            <div class="tracklist-head">
                <span>Danh sách bài hát</span>
                <span>Thời lượng</span>
            </div>

            @forelse($tracks as $track)
            <div class="tracklist-item">
                <div class="track-main">
                    <img src="{{ $track->getCoverUrl() }}" alt="{{ $track->title }}">
                    <div>
                        <a class="name" href="{{ route('songs.show', $track->id) }}">{{ $track->title }}</a>
                        <div class="artist">
                            {{ $track->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }}
                            @if($track->is_vip)
                                <i class="fa-solid fa-crown premium-crown ms-1"></i>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button
                        type="button"
                        class="btn btn-sm btn-song-play js-play-song"
                        data-song-id="{{ $track->id }}"
                        data-song-title="{{ e($track->title) }}"
                        data-song-artist="{{ e($track->artist?->getDisplayArtistName() ?? 'Nghệ sĩ') }}"
                        data-song-cover="{{ $track->getCoverUrl() }}"
                        data-song-premium="{{ $track->is_vip ? '1' : '0' }}"
                        data-stream-url="{{ route('songs.stream', $track->id) }}">
                        <i class="fa-solid fa-play"></i>
                    </button>
                    <div class="duration">{{ $track->durationFormatted() }}</div>
                </div>
            </div>
            @empty
            <div class="songs-empty mt-2">
                <p class="mb-0">Album này chưa có bài hát công khai.</p>
            </div>
            @endforelse
        </div>
    </section>

    <section class="artist-more-songs mt-4">
        <div class="section-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Album khác của {{ $artistName }}</h5>
            <a href="{{ route('albums.index', ['q' => $artistName]) }}" class="btn btn-sm btn-song-detail">Xem tất cả</a>
        </div>

        <div class="albums-card-grid mt-3">
            @forelse($artistOtherAlbums as $otherAlbum)
                @include('pages.albums.partials.album-card', ['album' => $otherAlbum, 'savedAlbumIds' => $savedAlbumIds])
            @empty
                <div class="songs-empty"><p class="mb-0 text-muted">Chưa có album liên quan.</p></div>
            @endforelse
        </div>
    </section>
</div>
@endsection
