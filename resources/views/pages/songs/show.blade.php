@extends('layouts.main')

@section('title', $song->title . ' - Blue Wave Music')

@section('content')
@php
    $artistName = $song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ';
    $coverImage = $song->getCoverUrl();
    $artistAvatar = $song->artist?->getAvatarUrl() ?? asset('images/default-avatar.png');
    $isFavorited = in_array((int) $song->id, $favoriteSongIds ?? [], true);
    $isAlbumSaved = $song->album ? in_array((int) $song->album->id, $savedAlbumIds ?? [], true) : false;
@endphp

<div class="song-detail-page-modern">
    {{-- Error Alert --}}
    @if(!$fileExists)
    <div class="container mt-4">
        <div class="alert alert-warning alert-dismissible fade show mb-0" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <strong>Bài hát đang được cập nhật</strong>
            <p class="mb-0 mt-1">Bài hát này chưa khả dụng. Vui lòng quay lại sau.</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    @endif

    {{-- HERO SECTION (1/3 Màn Hình ~ 38vh) --}}
    <div class="song-detail-hero position-relative overflow-hidden" style="height: 38vh; min-height: 300px; background-color: var(--black-main);">
        <!-- Background Blur -->
        <div class="position-absolute w-100 h-100 start-0 top-0" style="background-image: url('{{ $coverImage }}'); background-size: cover; background-position: center; filter: blur(60px) brightness(0.4); z-index: 1;"></div>
        <!-- Gradient overlay -->
        <div class="position-absolute w-100 h-100 start-0 top-0" style="background: linear-gradient(to bottom, transparent, var(--black-main)); z-index: 2;"></div>
        
        <div class="container h-100 position-relative" style="z-index: 3;">
             <div class="d-flex align-items-end h-100 pb-4">
                 <img src="{{ $coverImage }}" alt="{{ $song->title }}" class="rounded shadow-lg d-none d-md-block me-4" style="height: 220px; width: 220px; object-fit: cover; border: 1px solid rgba(255,255,255,0.1);">
                 <div class="flex-grow-1 text-white">
                     <span class="text-uppercase fw-bold mb-2 d-inline-block" style="font-size: 0.8rem; color: var(--text-muted); letter-spacing: 1px;"><i class="fa-solid fa-music me-2"></i>Bài hát</span>
                     <h1 class="fw-bolder mb-3" style="font-size: clamp(2.5rem, 5vw, 4.5rem); letter-spacing: -1px; line-height: 1.1;">
                         {{ $song->title }}
                         @if($song->is_vip)
                             <span class="badge align-middle ms-2 fs-6 position-relative" style="background-color: var(--purple-main); top: -8px;"><i class="fa-solid fa-crown me-1"></i>Premium</span>
                         @endif
                     </h1>
                     <div class="d-flex align-items-center flex-wrap" style="font-size: 1rem;">
                         <img src="{{ $artistAvatar }}" class="rounded-circle me-2 shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
                         <a href="{{ $song->artist?->id ? route('search.artist.show', $song->artist->id) : '#' }}" class="text-decoration-none text-white fw-bold me-1 hover-primary">{{ $artistName }}</a>
                         @if($song->artist?->artist_verified_at)
                             <i class="fa-solid fa-circle-check text-primary me-2 ms-0"></i>
                         @endif
                         <span class="opacity-50 mx-2">•</span> 
                         <span>{{ number_format((int) $song->listens) }} lượt nghe</span>
                         <span class="opacity-50 mx-2">•</span> 
                         <span>{{ $song->durationFormatted() }}</span>
                         @if($song->released_date)
                             <span class="opacity-50 mx-2">•</span> 
                             <span class="opacity-75">{{ $song->released_date->format('Y') }}</span>
                         @endif
                     </div>
                 </div>
             </div>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="container py-4 pb-5">
        {{-- ACTION BAR --}}
        <div class="action-bar d-flex align-items-center mb-5 gap-3">
            <button
                type="button"
                class="btn btn-hero-play js-play-song d-flex align-items-center justify-content-center shadow"
                data-song-id="{{ $song->id }}"
                data-song-title="{{ e($song->title) }}"
                data-song-artist="{{ e($artistName) }}"
                data-song-cover="{{ $song->getCoverUrl() }}"
                data-song-premium="{{ $song->is_vip ? '1' : '0' }}"
                data-song-favorited="{{ $isFavorited ? '1' : '0' }}"
                data-stream-url="{{ route('songs.stream', $song->id) }}"
                {{ !$fileExists ? 'disabled' : '' }}
                style="width: 64px; height: 64px; border-radius: 50%; font-size: 1.5rem; background-color: var(--primary-blue); color: white; border: none; transition: transform 0.2s;">
                <i class="fa-solid fa-play ps-1"></i>
            </button>

            @auth
            <form method="POST" action="{{ route('listener.song.toggleFavorite', $song->id) }}" class="m-0 p-0">
                @csrf
                <button class="btn btn-dark rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 {{ $isFavorited ? 'text-danger' : 'text-white' }}" title="{{ $isFavorited ? 'Bỏ yêu thích' : 'Yêu thích' }}" style="width: 48px; height: 48px; background: transparent; border: 1px solid rgba(255,255,255,0.2); font-size: 1.25rem; transition: border 0.3s;" onmouseover="this.style.borderColor='white'" onmouseout="this.style.borderColor='rgba(255,255,255,0.2)'">
                    <i class="{{ $isFavorited ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                </button>
            </form>
            @endauth

            @if($song->album)
            <a href="{{ route('albums.show', $song->album->id) }}" class="btn btn-dark rounded-pill d-flex align-items-center px-4 shadow-sm" style="height: 48px; background: transparent; border: 1px solid rgba(255,255,255,0.2); color: white; font-weight: bold; transition: border 0.3s;" onmouseover="this.style.borderColor='white'" onmouseout="this.style.borderColor='rgba(255,255,255,0.2)'">
                <i class="fa-solid fa-compact-disc me-2"></i> Xem Album
            </a>
            @endif
        </div>

        <div class="row gx-5">
            {{-- LEFT COLUMN --}}
            <div class="col-lg-8">
                {{-- LYRICS --}}
                @if($song->lyrics)
                <div class="mb-5">
                    <h5 class="fw-bold mb-3 text-white">Lời bài hát</h5>
                    <div class="lyrics-box lyrics-preview rounded p-4 shadow-sm" id="lyricsBox" 
                         title="Nhấn để xem toàn bộ/thu gọn lời bài hát" 
                         style="background-color: var(--black-soft); color: var(--text-primary); cursor: pointer; transition: all 0.3s ease; font-size: 1.1rem; line-height: 1.8; overflow: hidden; max-height: 250px; mask-image: linear-gradient(to bottom, black 50%, transparent 100%); -webkit-mask-image: linear-gradient(to bottom, black 50%, transparent 100%); position: relative;">
                        {!! nl2br(e($song->lyrics)) !!}
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-light w-100 mt-3 fw-bold" id="lyricsToggleBtn" style="border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; font-size: 0.95rem; padding: 0.5rem;">
                        <i class="fa-solid fa-chevron-down me-2"></i>Xem toàn bộ lời bài hát
                    </button>
                </div>
                @endif

                {{-- ARTIST ABOUT --}}
                <div class="mb-5">
                    <h5 class="fw-bold mb-3 text-white">Nghệ sĩ</h5>
                    <a href="{{ $song->artist?->id ? route('search.artist.show', $song->artist->id) : '#' }}" class="text-decoration-none">
                        <div class="card bg-transparent border-0 position-relative overflow-hidden shadow-sm" style="border-radius: 16px; background-color: var(--black-soft) !important;">
                            <div class="position-absolute w-100 h-100" style="background-image: url('{{ $artistAvatar }}'); background-size: cover; background-position: center; filter: blur(30px) opacity(0.3);"></div>
                            <div class="card-body p-4 position-relative d-flex align-items-center" style="z-index: 2;">
                                <img src="{{ $artistAvatar }}" class="rounded-circle me-4 shadow" style="width: 80px; height: 80px; object-fit: cover; border: 2px solid white;">
                                <div>
                                    <div class="text-white text-uppercase fw-bold opacity-75" style="letter-spacing: 1px; font-size: 0.8rem;">Profile</div>
                                    <h5 class="fw-bold text-white mb-1 hover-primary">{{ $artistName }}</h5>
                                    <div style="color: rgba(255,255,255,0.8); font-size: 0.9rem;">{{ number_format((int) ($song->artist?->followers()->count() ?? 0)) }} người theo dõi</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- OTHER SONGS BY ARTIST --}}
                @if(isset($artistSongs) && $artistSongs->count() > 0)
                <div class="mb-5">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-white m-0">Bài hát khác của {{ $artistName }}</h5>
                        <a href="{{ route('songs.index', ['q' => $artistName]) }}" class="text-decoration-none fw-bold" style="color: var(--text-muted); font-size: 0.9rem;">Xem tất cả</a>
                    </div>
                    <div class="songs-card-grid row row-cols-2 row-cols-md-3 g-3">
                        @foreach($artistSongs->take(6) as $relatedSong)
                            <div class="col">
                            @include('pages.songs.partials.song-card', ['song' => $relatedSong, 'favoriteSongIds' => $favoriteSongIds ?? []])
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                {{-- ALBUMS BY ARTIST --}}
                @if(isset($artistAlbums) && $artistAlbums->count() > 0)
                <div class="mb-5">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-white m-0">Album của {{ $artistName }}</h5>
                        <a href="{{ route('albums.index', ['q' => $artistName]) }}" class="text-decoration-none fw-bold" style="color: var(--text-muted); font-size: 0.9rem;">Xem tất cả</a>
                    </div>
                    <div class="albums-card-grid row row-cols-2 row-cols-md-3 g-3">
                        @foreach($artistAlbums->take(3) as $artistAlbum)
                            <div class="col">
                            @include('pages.albums.partials.album-card', ['album' => $artistAlbum, 'savedAlbumIds' => $savedAlbumIds ?? []])
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- RIGHT COLUMN (SIDEBAR) --}}
            <div class="col-lg-4">
                {{-- UP NEXT LIST --}}
                @if(isset($artistSongs) && $artistSongs->count() > 0)
                <div class="card p-0 overflow-hidden mb-4 shadow" style="background-color: var(--black-soft); border: 1px solid var(--black-hover); border-radius: 16px;">
                    <div class="card-header border-0 bg-transparent p-4 pb-2">
                        <h6 class="text-white fw-bold m-0"><i class="fa-solid fa-list me-2"></i>Nghe tiếp</h6>
                    </div>
                    <div class="list-group list-group-flush pb-2 px-2">
                        @foreach($artistSongs->take(5) as $idx => $item)
                        @php $itemFavorited = in_array((int) $item->id, $favoriteSongIds ?? [], true); @endphp
                        <div class="list-group-item bg-transparent border-0 d-flex align-items-center rounded px-3 py-2 my-1 position-relative track-item" style="transition: background 0.2s;" onmouseover="this.style.backgroundColor='var(--black-hover)'; this.querySelector('.play-overlay').style.opacity='1'" onmouseout="this.style.backgroundColor='transparent'; this.querySelector('.play-overlay').style.opacity='0'">
                            <div class="position-relative me-3 flex-shrink-0" style="width: 48px; height: 48px; border-radius: 6px; overflow: hidden;">
                                <img src="{{ $item->getCoverUrl() }}" class="w-100 h-100 object-fit-cover shadow-sm">
                                <button type="button" 
                                    class="js-play-song d-flex align-items-center justify-content-center play-overlay w-100 h-100 position-absolute top-0 start-0 border-0"
                                    data-song-id="{{ $item->id }}" data-song-title="{{ e($item->title) }}" data-song-artist="{{ e($item->artist?->getDisplayArtistName() ?? 'Nghệ sĩ') }}" data-song-cover="{{ $item->getCoverUrl() }}" data-song-premium="{{ $item->is_vip ? '1' : '0' }}" data-song-favorited="{{ $itemFavorited ? '1' : '0' }}" data-stream-url="{{ route('songs.stream', $item->id) }}"
                                    style="background: rgba(0,0,0,0.6); color: white; opacity: 0; transition: opacity 0.2s;">
                                    <i class="fa-solid fa-play"></i>
                                </button>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <a href="{{ route('songs.show', $item->id) }}" class="text-decoration-none text-white fw-bold text-truncate d-block mb-1" style="font-size: 0.95rem;">{{ $item->title }}</a>
                                <div class="text-truncate" style="color: var(--text-muted); font-size: 0.8rem;">
                                    {{ $item->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }}
                                    @if($item->is_vip) <i class="fa-solid fa-crown ms-1 text-warning"></i> @endif
                                </div>
                            </div>
                            <div class="ms-2 text-end" style="color: var(--text-muted); font-size: 0.8rem;">
                                {{ $item->durationFormatted() }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- SONG INFO BOX --}}
                <div class="card p-4 shadow mb-4" style="background-color: var(--black-soft); border: 1px solid var(--black-hover); border-radius: 16px;">
                    <h6 class="text-white fw-bold mb-4">Thông tin bài hát</h6>
                    <ul class="list-unstyled m-0" style="color: var(--text-muted); font-size: 0.95rem;">
                        <li class="d-flex justify-content-between mb-3 border-bottom pb-2" style="border-color: rgba(255,255,255,0.05) !important;">
                            <span>Nghệ sĩ</span>
                            <span class="text-white text-end">{{ $artistName }}</span>
                        </li>
                        <li class="d-flex justify-content-between mb-3 border-bottom pb-2" style="border-color: rgba(255,255,255,0.05) !important;">
                            <span>Thể loại</span>
                            <span class="text-white text-end">{{ $song->genre?->name ?? 'Chưa phân loại' }}</span>
                        </li>
                        @if($song->released_date)
                        <li class="d-flex justify-content-between mb-3 border-bottom pb-2" style="border-color: rgba(255,255,255,0.05) !important;">
                            <span>Phát hành</span>
                            <span class="text-white text-end">{{ $song->released_date->format('d/m/Y') }}</span>
                        </li>
                        @endif
                        <li class="d-flex justify-content-between">
                            <span>Bản quyền</span>
                            <span class="text-white text-end">&copy; {{ $song->released_date ? $song->released_date->format('Y') : date('Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const lyricsBox = document.getElementById('lyricsBox');
        const toggleBtn = document.getElementById('lyricsToggleBtn');

        function toggleUI() {
            if (!lyricsBox) return;
            const isExpanded = lyricsBox.classList.contains('lyrics-expanded');

            if (isExpanded) {
                // Collapse
                lyricsBox.classList.remove('lyrics-expanded');
                lyricsBox.classList.add('lyrics-preview');
                lyricsBox.style.maxHeight = '250px';
                lyricsBox.style.maskImage = 'linear-gradient(to bottom, black 50%, transparent 100%)';
                lyricsBox.style.webkitMaskImage = 'linear-gradient(to bottom, black 50%, transparent 100%)';
                if(toggleBtn) toggleBtn.innerHTML = '<i class="fa-solid fa-chevron-down me-2"></i>Xem toàn bộ lời bài hát';
            } else {
                // Expand
                lyricsBox.classList.remove('lyrics-preview');
                lyricsBox.classList.add('lyrics-expanded');
                lyricsBox.style.maxHeight = 'none';
                lyricsBox.style.maskImage = 'none';
                lyricsBox.style.webkitMaskImage = 'none';
                if(toggleBtn) toggleBtn.innerHTML = '<i class="fa-solid fa-chevron-up me-2"></i>Thu gọn lời bài hát';
            }
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', toggleUI);
        }

        if (lyricsBox) {
            // Initiate preview class state
            lyricsBox.classList.add('lyrics-preview');
            // Click on lyrics to expand if currently compact
            lyricsBox.addEventListener('click', function(e) {
                if (!lyricsBox.classList.contains('lyrics-expanded')) {
                    toggleUI();
                }
            });
        }
    });
</script>
@endpush
