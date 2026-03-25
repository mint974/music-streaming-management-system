@extends('layouts.main')

@section('title', $song->title . ' - Blue Wave Music')

@section('content')
@php
    $artistName = $song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ';
    $coverImage = $song->getCoverUrl();
    $artistAvatar = $song->artist?->getAvatarUrl() ?? asset('images/default-avatar.png');
@endphp

<div class="songs-page">
<div class="song-detail-page container py-4">
    {{-- Error Alert --}}
    @if(!$fileExists)
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        <strong>Bài hát đang được cập nhật</strong>
        <p class="mb-0 mt-1">Bài hát này chưa khả dụng. Vui lòng quay lại sau.</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            {{-- CARD 1: SONG COVER + QUICK INFO --}}
            <div class="card song-detail-card mb-4">
                <div class="card-body song-detail-hero-body">
                    <div class="detail-cover-wrapper mb-3" style="width: 100%;">
                        <img src="{{ $coverImage }}" alt="{{ $song->title }}" class="song-cover-large w-100 rounded shadow" style="height: 33vh; object-fit: cover; object-position: center;">
                    </div>

                    <div class="song-meta-top mb-2">Bài hát</div>

                    <h1 class="detail-title mb-2">
                        {{ $song->title }}
                        @if($song->is_vip)
                            <span class="song-premium-pill ms-2"><i class="fa-solid fa-crown me-1"></i>Premium</span>
                        @endif
                    </h1>

                    <div class="detail-meta mb-3">
                        <a href="{{ $song->artist?->id ? route('search.artist.show', $song->artist->id) : '#' }}" class="artist-link">
                            {{ $artistName }}
                        </a>
                        @if($song->artist?->artist_verified_at)
                            <i class="fa-solid fa-circle-check verify-icon ms-1"></i>
                        @endif
                    </div>

                    <div class="detail-chip-row mb-4">
                        <span class="detail-chip"><i class="fa-solid fa-headphones me-1"></i>{{ number_format((int) $song->listens) }} lượt nghe</span>
                        <span class="detail-chip"><i class="fa-regular fa-clock me-1"></i>{{ $song->durationFormatted() }}</span>
                        <span class="detail-chip">{{ $song->genre?->name ?? 'Khác' }}</span>
                        @if($song->released_date)
                            <span class="detail-chip"><i class="fa-regular fa-calendar me-1"></i>{{ $song->released_date->format('d/m/Y') }}</span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="detail-actions">
                        <button
                            type="button"
                            class="btn btn-song-play js-play-song"
                            data-song-id="{{ $song->id }}"
                            data-song-title="{{ e($song->title) }}"
                            data-song-artist="{{ e($artistName) }}"
                            data-song-cover="{{ $song->getCoverUrl() }}"
                            data-song-premium="{{ $song->is_vip ? '1' : '0' }}"
                            data-song-favorited="{{ $isFavorited ? '1' : '0' }}"
                            data-stream-url="{{ route('songs.stream', $song->id) }}"
                            {{ !$fileExists ? 'disabled' : '' }}>
                            <i class="fa-solid fa-play me-1"></i>{{ !$fileExists ? 'Không khả dụng' : 'Phát bài hát' }}
                        </button>

                        @if($song->album)
                            <a href="{{ route('albums.show', $song->album->id) }}" class="btn btn-song-detail px-3">
                                <i class="fa-solid fa-compact-disc me-1"></i>Xem album
                            </a>
                        @endif

                        @auth
                        <div class="d-flex ms-auto gap-2">
                            <form method="POST" action="{{ route('listener.song.toggleFavorite', $song->id) }}" class="m-0">
                                @csrf
                                <button class="btn {{ $isFavorited ? 'btn-song-liked px-3 py-2' : 'btn-song-like px-3 py-2' }}">
                                    <i class="fa-solid fa-heart me-1"></i>{{ $isFavorited ? 'Đã yêu thích' : 'Yêu thích' }}
                                </button>
                            </form>
                            
                            <button class="btn btn-outline-secondary text-white px-3 py-2 d-flex align-items-center" style="border-radius: 999px; border-color: rgba(255,255,255,0.2);" onclick="openAddToPlaylistModal({{ $song->id }})" title="Lưu Playlist">
                                <i class="fa-solid fa-list-ul me-1"></i>Playlist
                            </button>
                        </div>
                        @endauth
                    </div>
                </div>
            </div>

            {{-- CARD 2: SONG DETAILS --}}
            @if($song->album || true)
            <div class="card song-detail-card song-info-card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Thông tin bài hát</h6>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="info-label">Thể loại</span>
                        <span class="info-value">{{ $song->genre?->name ?? 'Chưa phân loại' }}</span>
                    </div>
                    @if($song->released_date)
                    <div class="info-row">
                        <span class="info-label">Ngày phát hành</span>
                        <span class="info-value">{{ $song->released_date->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Lượt nghe</span>
                        <span class="info-value">{{ number_format((int) $song->listens) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Thời lượng</span>
                        <span class="info-value">{{ $song->durationFormatted() }}</span>
                    </div>

                    @if($song->album)
                    <div class="info-row border-top pt-3 mt-3">
                        <div class="w-100">
                            <span class="info-label d-block mb-2">Album</span>
                            <strong class="info-value d-block mb-3">{{ $song->album->title }}</strong>
                            @auth
                            <form method="POST" action="{{ route('listener.album.toggleSave', $song->album->id) }}">
                                @csrf
                                <button class="btn btn-sm {{ $isAlbumSaved ? 'btn-album-saved' : 'btn-album-save' }}">
                                    <i class="fa-solid fa-bookmark me-1"></i>{{ $isAlbumSaved ? 'Đã lưu album' : 'Lưu album' }}
                                </button>
                            </form>
                            @endauth
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- CARD 3: LYRICS --}}
            @if($song->lyrics)
            <div class="card song-detail-card lyrics-card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="mb-0">Lời bài hát</h6>
                    <small class="text-muted">Nguồn lời: hệ thống nghệ sĩ</small>
                </div>
                <div class="card-body">
                    <div class="lyrics-box lyrics-preview rounded p-3 cursor-pointer" id="lyricsBox" style="background-color: var(--black-soft); color: var(--text-primary); cursor: pointer; transition: all 0.3s ease;" title="Nhấn để xem toàn bộ/thu gọn lời bài hát">
                        {!! nl2br(e($song->lyrics)) !!}
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100 mt-3 lyrics-toggle-btn" id="lyricsToggleBtn">
                        <i class="fa-solid fa-chevron-down me-1"></i>Xem thêm lời bài hát
                    </button>
                </div>
            </div>
            @endif

            {{-- CARD 4: RELATED SONGS BY ARTIST --}}
            @if($artistSongs->count() > 0)
            <div class="card song-detail-card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="mb-0">Các bài hát khác của {{ $artistName }}</h6>
                    <a href="{{ route('songs.index', ['q' => $artistName]) }}" class="btn btn-sm btn-song-detail">Xem tất cả</a>
                </div>
                <div class="card-body">
                    <div class="songs-card-grid">
                        @foreach($artistSongs as $relatedSong)
                            @include('pages.songs.partials.song-card', ['song' => $relatedSong, 'favoriteSongIds' => $favoriteSongIds])
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- CARD 5: ARTIST ALBUMS --}}
            @if($artistAlbums->count() > 0)
            <div class="card song-detail-card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="mb-0">Album của {{ $artistName }}</h6>
                    <a href="{{ route('albums.index', ['q' => $artistName]) }}" class="btn btn-sm btn-song-detail">Xem thêm</a>
                </div>
                <div class="card-body">
                    <div class="albums-card-grid">
                        @foreach($artistAlbums as $artistAlbum)
                            @include('pages.albums.partials.album-card', ['album' => $artistAlbum, 'savedAlbumIds' => $savedAlbumIds])
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- SIDEBAR: ARTIST INFO & RELATED TRACKS --}}
        <div class="col-lg-4">
            {{-- ARTIST INFO CARD --}}
            <div class="card song-detail-card artist-sidebar-card mb-4">
                <div class="card-body">
                    <div class="artist-side-title mb-3">Nghệ sĩ</div>
                    <div class="artist-side-head">
                        <img src="{{ $artistAvatar }}" alt="{{ $artistName }}" class="artist-side-avatar me-3">
                        <div class="flex-grow-1">
                            <div class="artist-side-name">
                                <a href="{{ $song->artist?->id ? route('search.artist.show', $song->artist->id) : '#' }}" class="artist-link">
                                    {{ $artistName }}
                                </a>
                                @if($song->artist?->artist_verified_at)
                                    <i class="fa-solid fa-circle-check ms-1"></i>
                                @endif
                            </div>
                            <div class="artist-side-sub">{{ number_format((int) ($song->artist?->followers()->count() ?? 0)) }} người theo dõi</div>
                        </div>
                    </div>
                    @if($song->artist?->bio)
                        <div class="artist-side-bio mt-3">{{ \Illuminate\Support\Str::limit($song->artist->bio, 140) }}</div>
                    @endif
                </div>
            </div>

            {{-- RELATED TRACKS (LISTENING QUEUE) --}}
            <div class="card song-detail-card tracklist-card">
                <div class="card-header">
                    <h6 class="mb-0">Nghe tiếp</h6>
                </div>
                <div class="card-body p-0">
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

                    @foreach($artistSongs->take(5) as $item)
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
                                class="btn btn-sm btn-song-play js-play-song"
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
                </div>
            </div>
        </div>
    </div>
</div>
{{-- /.song-detail-page --}}
</div>
{{-- /.songs-page --}}

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const lyricsBox = document.getElementById('lyricsBox');
        const toggleBtn = document.getElementById('lyricsToggleBtn');

        function toggleLyrics() {
            if (!lyricsBox) return;
            const isExpanded = lyricsBox.classList.contains('lyrics-expanded');

            if (isExpanded) {
                // Collapse
                lyricsBox.classList.remove('lyrics-expanded');
                lyricsBox.classList.add('lyrics-preview');
                if(toggleBtn) toggleBtn.innerHTML = '<i class="fa-solid fa-chevron-down me-1"></i>Xem thêm lời bài hát';
            } else {
                // Expand
                lyricsBox.classList.remove('lyrics-preview');
                lyricsBox.classList.add('lyrics-expanded');
                if(toggleBtn) toggleBtn.innerHTML = '<i class="fa-solid fa-chevron-up me-1"></i>Ẩn lời bài hát';
            }
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', toggleLyrics);
        }

        // Feature: Click in lyrics box to expand/collapse
        if (lyricsBox) {
            lyricsBox.addEventListener('click', toggleLyrics);
            // Also ensure preview mask logic aligns with UI
            lyricsBox.style.overflow = 'hidden';
            if (lyricsBox.classList.contains('lyrics-preview')) {
                 lyricsBox.style.maxHeight = '200px'; 
                 lyricsBox.style.maskImage = 'linear-gradient(to bottom, black 50%, transparent 100%)';
                 lyricsBox.style.webkitMaskImage = 'linear-gradient(to bottom, black 50%, transparent 100%)';
            }
            
            // Overriding classes with inline styles just to be safe if css is not fully defined
            const orgToggle = toggleLyrics;
            toggleLyrics = function() {
                orgToggle();
                if (lyricsBox.classList.contains('lyrics-expanded')) {
                    lyricsBox.style.maxHeight = 'none';
                    lyricsBox.style.maskImage = 'none';
                    lyricsBox.style.webkitMaskImage = 'none';
                } else {
                    lyricsBox.style.maxHeight = '200px';
                    lyricsBox.style.maskImage = 'linear-gradient(to bottom, black 50%, transparent 100%)';
                    lyricsBox.style.webkitMaskImage = 'linear-gradient(to bottom, black 50%, transparent 100%)';
                }
            }
        }
    });
</script>
@endpush
