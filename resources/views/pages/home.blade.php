@extends('layouts.main')

@section('title', 'Home – Blue Wave Music')

@section('content')
<div class="home-page-modern">

    {{-- HOMEPAGE BANNERS / HERO SECTION --}}
    @if(isset($banners) && $banners->count() > 0)
    <section class="home-banners">
        <div class="container pt-4">
            <div id="homeBannerCarousel" class="carousel slide shadow-lg" data-bs-ride="carousel" data-bs-touch="true" data-bs-interval="4000" style="border-radius: 16px; overflow: hidden; position: relative;">
                <div class="carousel-indicators" style="bottom: 10px;">
                    @foreach($banners as $index => $banner)
                        <button type="button" data-bs-target="#homeBannerCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index == 0 ? 'active' : '' }}" aria-label="Slide {{ $index+1 }}" style="width: 8px; height: 8px; border-radius: 50%; margin: 0 4px; border: none;"></button>
                    @endforeach
                </div>
                <div class="carousel-inner">
                    @foreach($banners as $index => $banner)
                        <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                            <div style="position: relative; width: 100%; border-radius: 16px; overflow: hidden; aspect-ratio: 2.6/1; max-height: 480px; background: #111;">
                                <!-- Nền mờ (Backdrop Blur) lấp 100% không gian -->
                                <div style="position: absolute; inset: -20px; background: url('{{ asset($banner->image_path) }}') center/cover no-repeat; filter: blur(20px); opacity: 0.5; z-index: 0;"></div>
                                
                                <!-- Ảnh gốc (Contain - không méo, không cắt) đè lên -->
                                @if($banner->target_url)
                                    <a href="{{ $banner->target_url }}" class="d-block w-100 h-100 position-relative z-1" style="display: block; height: 100%;">
                                        <img src="{{ asset($banner->image_path) }}" alt="{{ $banner->title }}" style="width: 100%; height: 100%; object-fit: contain;">
                                    </a>
                                @else
                                    <div class="d-block w-100 h-100 position-relative z-1" style="height: 100%;">
                                        <img src="{{ asset($banner->image_path) }}" alt="{{ $banner->title }}" style="width: 100%; height: 100%; object-fit: contain;">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($banners->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="prev" style="width: 5%; opacity: 0; transition: opacity 0.3s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
                    <span class="carousel-control-prev-icon shadow" aria-hidden="true" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="next" style="width: 5%; opacity: 0; transition: opacity 0.3s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
                    <span class="carousel-control-next-icon shadow" aria-hidden="true" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                @endif
            </div>
        </div>
    </section>
    @elseif($featuredAlbum && $featuredAlbum->songs && $featuredAlbum->songs->count() > 0)
    <section class="hero-banner">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-visual">
                    @php
                        $heroCover = $featuredAlbum->cover_image
                            ? \Illuminate\Support\Facades\Storage::url($featuredAlbum->cover_image)
                            : asset('images/disk.png');
                        $artistName = $featuredAlbum->artist?->getDisplayArtistName() ?? 'Nghệ sĩ';
                    @endphp
                    <img src="{{ $heroCover }}" alt="{{ $featuredAlbum->title }}" class="hero-img" onerror="this.src='{{ asset('images/disk.png') }}'">
                    <div class="hero-gradient"></div>
                </div>

                <div class="hero-info">
                    <span class="hero-badge">Album Nổi Bật</span>
                    <h1 class="hero-heading">{{ $featuredAlbum->title }}</h1>
                    <p class="hero-artist">
                        {{ $artistName }}
                        @if($featuredAlbum->artist?->artist_verified_at)
                            <i class="fa-solid fa-circle-check verify-icon"></i>
                        @endif
                    </p>

                    <div class="hero-meta">
                        <span class="meta-tag">
                            <i class="fa-solid fa-music"></i>
                            {{ $featuredAlbum->songs->count() }} bài
                        </span>
                        <span class="meta-tag">
                            <i class="fa-regular fa-clock"></i>
                            @php
                                $totalSec = $featuredAlbum->songs->sum('duration');
                                $h = intdiv($totalSec, 3600);
                                $m = intdiv($totalSec % 3600, 60);
                            @endphp
                            {{ $h > 0 ? "{$h}h " : '' }}{{ $m }}m
                        </span>
                    </div>

                    <div class="hero-cta">
                        <button class="btn-hero-play" id="heroPlayBtn">
                            <i class="fa-solid fa-play"></i>
                            <span>Phát nhạc</span>
                        </button>
                        <a href="{{ route('albums.show', $featuredAlbum->id) }}" class="btn-hero-secondary">
                            <span>Xem album</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <div class="container py-4">

        {{-- RECENTLY PLAYED --}}
        @auth
        @if($recentlyPlayed->count() > 0)
        <section class="section-block mb-5">
            <div class="section-head">
                <h2 class="section-heading">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i>
                    Nghe gần đây
                </h2>
            </div>
            <div class="horizontal-scroll">
                <div class="scroll-container">
                    @foreach($recentlyPlayed as $song)
                        @include('pages.songs.partials.song-card', ['song' => $song, 'favoriteSongIds' => []])
                    @endforeach
                </div>
            </div>
        </section>
        @endif
        @endauth

        {{-- TRENDING & NEW RELEASES TABS --}}
        @if($trendingSongs->count() > 0 || $newReleases->count() > 0)
        <section class="section-block mb-5">
            <ul class="nav nav-tabs custom-tabs mb-4 border-0" id="exploreTabs" role="tablist" style="border-bottom: 2px solid var(--black-hover) !important;">
                @if($trendingSongs->count() > 0)
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold text-uppercase" id="trending-tab" data-bs-toggle="tab" data-bs-target="#trending" type="button" role="tab" aria-controls="trending" aria-selected="true" style="color: var(--text-primary); background: transparent; border: none; border-bottom: 2px solid transparent;">
                        <i class="fa-solid fa-fire me-2"></i>Đang thịnh hành
                    </button>
                </li>
                @endif
                @if($newReleases->count() > 0)
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-uppercase {{ $trendingSongs->count() == 0 ? 'active' : '' }}" id="new-releases-tab" data-bs-toggle="tab" data-bs-target="#new-releases" type="button" role="tab" aria-controls="new-releases" aria-selected="{{ $trendingSongs->count() == 0 ? 'true' : 'false' }}" style="color: var(--text-primary); background: transparent; border: none; border-bottom: 2px solid transparent;">
                        <i class="fa-solid fa-sparkles me-2"></i>Mới phát hành
                    </button>
                </li>
                @endif
            </ul>

            <style>
                .custom-tabs .nav-link.active {
                    border-bottom: 2px solid var(--primary-blue) !important;
                    color: var(--primary-blue) !important;
                }
                .custom-tabs .nav-link:hover {
                    color: var(--primary-blue-light) !important;
                }
            </style>

            <div class="tab-content" id="exploreTabsContent">
                @if($trendingSongs->count() > 0)
                <div class="tab-pane fade show active" id="trending" role="tabpanel" aria-labelledby="trending-tab">
                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('songs.index', ['sort' => 'popular']) }}" class="section-link text-decoration-none" style="color: var(--text-muted); font-size: 0.9rem;">
                            Xem tất cả <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="horizontal-scroll">
                        <div class="scroll-container pb-2">
                            @foreach($trendingSongs as $song)
                                @include('pages.songs.partials.song-card', ['song' => $song, 'favoriteSongIds' => []])
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                @if($newReleases->count() > 0)
                <div class="tab-pane fade {{ $trendingSongs->count() == 0 ? 'show active' : '' }}" id="new-releases" role="tabpanel" aria-labelledby="new-releases-tab">
                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('songs.index', ['sort' => 'newest']) }}" class="section-link text-decoration-none" style="color: var(--text-muted); font-size: 0.9rem;">
                            Xem tất cả <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="horizontal-scroll">
                        <div class="scroll-container pb-2">
                            @foreach($newReleases as $song)
                                @include('pages.songs.partials.song-card', ['song' => $song, 'favoriteSongIds' => []])
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </section>
        @endif

        {{-- TOP CHARTS --}}
        @if($topCharts->count() > 0)
        <section class="section-block mb-5">
            <div class="section-head">
                <h2 class="section-heading">
                    <i class="fa-solid fa-chart-line me-2"></i>
                    BXH Top 10
                </h2>
            </div>
            <div class="chart-list">
                @foreach($topCharts as $index => $song)
                    <div class="chart-item">
                        <div class="chart-rank rank-{{ $index + 1 }}">{{ $index + 1 }}</div>
                        <div class="chart-cover">
                            <img src="{{ $song->getCoverUrl() }}" alt="{{ $song->title }}">
                        </div>
                        <div class="chart-info">
                            <a href="{{ route('songs.show', $song->id) }}" class="chart-title">{{ $song->title }}</a>
                            <p class="chart-artist">{{ $song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }}</p>
                        </div>
                        <div class="chart-stats">
                            <span class="chart-listens">
                                <i class="fa-solid fa-headphones me-1"></i>
                                {{ number_format($song->listens) }}
                            </span>
                        </div>
                        <button
                            class="btn-chart-play js-play-song"
                            data-song-id="{{ $song->id }}"
                            data-song-title="{{ e($song->title) }}"
                            data-song-artist="{{ e($song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ') }}"
                            data-song-cover="{{ $song->getCoverUrl() }}"
                            data-song-premium="{{ $song->is_vip ? '1' : '0' }}"
                            data-song-favorited="0"
                            data-stream-url="{{ route('songs.stream', $song->id) }}">
                            <i class="fa-solid fa-play"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- BROWSE GENRES --}}
        @if($genres->count() > 0)
        <section class="section-block mb-5">
            <div class="section-head">
                <h2 class="section-heading">
                    <i class="fa-solid fa-grip me-2"></i>
                    Khám phá thể loại
                </h2>
            </div>
            <div class="genres-grid">
                @foreach($genres as $genre)
                    <a href="{{ route('songs.index', ['genre_id' => $genre->id]) }}" class="genre-card"
                       style="background: linear-gradient(135deg, {{ $genre->color ?? '#667eea' }}, {{ $genre->color ?? '#764ba2' }});">
                        @if($genre->icon)
                            <i class="{{ $genre->icon }} genre-icon"></i>
                        @else
                            <i class="fa-solid fa-music genre-icon"></i>
                        @endif
                        <span class="genre-name">{{ $genre->name }}</span>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

        {{-- FEATURED ARTISTS --}}
        @if($featuredArtists->count() > 0)
        <section class="section-block mb-5">
            <div class="section-head">
                <h2 class="section-heading">
                    <i class="fa-solid fa-star me-2"></i>
                    Nghệ sĩ nổi bật
                </h2>
            </div>
            <div class="artists-grid">
                @foreach($featuredArtists as $artist)
                    <a href="{{ route('search.artist.show', $artist->id) }}" class="artist-card-item">
                        <div class="artist-avatar-wrap">
                            <img src="{{ $artist->getAvatarUrl() }}" alt="{{ $artist->getDisplayArtistName() }}" class="artist-avatar">
                        </div>
                        <div class="artist-card-name">
                            {{ $artist->getDisplayArtistName() }}
                            @if($artist->artist_verified_at)
                                <i class="fa-solid fa-circle-check verify-check"></i>
                            @endif
                        </div>
                        <p class="artist-card-stats">{{ $artist->published_songs_count }} bài hát</p>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

    </div>

</div>
@endsection
