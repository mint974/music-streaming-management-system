@extends('layouts.main')

@section('title', 'Home – Blue Wave Music')

@section('content')
<div class="home-page-modern">

    @if(($heroBanners ?? collect())->isNotEmpty())
    <section class="section-block mb-5">
        <div class="section-head">
            <h2 class="section-heading">
                <i class="fa-solid fa-panorama me-2"></i>
                Banner nổi bật
            </h2>
        </div>

        @if($heroBanners->count() === 1)
            @php $banner = $heroBanners->first(); @endphp
            <a href="{{ route('banners.click', $banner) }}" class="text-decoration-none d-block">
                <div class="rounded-4 overflow-hidden position-relative" style="min-height:220px;background:#0f172a;border:1px solid rgba(255,255,255,.08)">
                    <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-100 h-100" style="object-fit:cover;min-height:220px;max-height:360px;">
                    <div class="position-absolute bottom-0 start-0 w-100 p-4" style="background:linear-gradient(180deg,transparent,rgba(2,6,23,.88));">
                        <div class="badge rounded-pill mb-2" style="background:rgba(255,255,255,.14);backdrop-filter:blur(8px)">Banner</div>
                        <h3 class="text-white fw-bold mb-1">{{ $banner->title }}</h3>
                        @if($banner->target_url)
                            <div class="text-white-50 small text-truncate">{{ $banner->target_url }}</div>
                        @endif
                    </div>
                </div>
            </a>
        @else
            <div id="homeBannerCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    @foreach($heroBanners as $index => $banner)
                        <button type="button" data-bs-target="#homeBannerCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}" aria-label="Banner {{ $index + 1 }}"></button>
                    @endforeach
                </div>
                <div class="carousel-inner rounded-4 overflow-hidden" style="border:1px solid rgba(255,255,255,.08)">
                    @foreach($heroBanners as $index => $banner)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            @if($banner->target_url)
                                <a href="{{ route('banners.click', $banner) }}" class="text-decoration-none d-block">
                            @endif
                            <div class="position-relative" style="min-height:220px;background:#0f172a;">
                                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-100" style="object-fit:cover;min-height:220px;max-height:420px;">
                                <div class="position-absolute bottom-0 start-0 w-100 p-4" style="background:linear-gradient(180deg,transparent,rgba(2,6,23,.88));">
                                    <div class="badge rounded-pill mb-2" style="background:rgba(255,255,255,.14);backdrop-filter:blur(8px)">Banner</div>
                                    <h3 class="text-white fw-bold mb-1">{{ $banner->title }}</h3>
                                    @if($banner->target_url)
                                        <div class="text-white-50 small text-truncate">{{ $banner->target_url }}</div>
                                    @endif
                                </div>
                            </div>
                            @if($banner->target_url)
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if($heroBanners->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Trước</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Sau</span>
                </button>
                @endif
            </div>
        @endif
    </section>
    @endif

    @if(($adBanners ?? collect())->isNotEmpty())
    <section class="section-block mb-5">
        <div class="section-head d-flex align-items-center justify-content-between">
            <h2 class="section-heading">
                <i class="fa-solid fa-bullhorn me-2"></i>
                Quảng cáo audio dành cho tài khoản miễn phí
            </h2>
            <span class="badge rounded-pill" style="background:rgba(251,191,36,.12);color:#fbbf24;border:1px solid rgba(251,191,36,.25)">Audio Ads</span>
        </div>

        <div class="row g-3">
            @foreach($adBanners as $banner)
            <div class="col-12 col-lg-6">
                <div class="rounded-4 p-3 h-100" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)">
                    <div class="d-flex gap-3">
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" style="width:110px;height:72px;object-fit:cover;border-radius:12px;border:1px solid rgba(255,255,255,.08);flex-shrink:0">
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                <div>
                                    <div class="text-white fw-semibold text-truncate">{{ $banner->title }}</div>
                                    <div class="text-muted small">Quảng cáo audio</div>
                                </div>
                                @if($banner->target_url)
                                <a href="{{ route('banners.click', $banner) }}" class="btn btn-sm btn-outline-light flex-shrink-0">Mở link</a>
                                @endif
                            </div>
                            @if($banner->hasAudioFile())
                                <audio controls preload="none" class="w-100 mt-2">
                                    <source src="{{ $banner->audio_url }}">
                                </audio>
                            @else
                                <div class="text-warning small mt-2">Banner này chưa có tệp audio hợp lệ.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- HERO SECTION - FEATURED ALBUM --}}
    @if($featuredAlbum && $featuredAlbum->songs && $featuredAlbum->songs->count() > 0)
    <section class="hero-banner">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-visual">
                    @php
                        $heroCover = $featuredAlbum->getCoverUrl();
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
        @if(isset($topSongsWeek) && $topSongsWeek->count() > 0)
            <section class="section-block animate-on-scroll mb-5">
                <div class="home-chart-container">
                    <div class="chart-inner-wrap">
                        <div class="chart-header-title">BẢNG XẾP HẠNG BLUE WAVE MUSIC</div>
                        <div class="chart-header-subtitle">TOP 10</div>

                        <ul class="nav nav-pills music-x-tabs" id="chartTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="music-x-btn active" id="week-tab" data-bs-toggle="tab"
                                    data-bs-target="#week-chart" type="button" role="tab">Tuần</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="music-x-btn" id="month-tab" data-bs-toggle="tab"
                                    data-bs-target="#month-chart" type="button" role="tab">Tháng</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="music-x-btn" id="quarter-tab" data-bs-toggle="tab"
                                    data-bs-target="#quarter-chart" type="button" role="tab">Quý</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="chartTabsContent">
                            @foreach (['week' => $topSongsWeek, 'month' => $topSongsMonth, 'quarter' => $topSongsQuarter] as $period => $songs)
                                @php
                                    $allSongs = $songs->all();
                                    $top3 = array_slice($allSongs, 0, 3);
                                    $subs = array_slice($allSongs, 3, 7);
                                @endphp
                                <div class="tab-pane fade {{ $period === 'week' ? 'show active' : '' }}"
                                    id="{{ $period }}-chart" role="tabpanel">

                                    <!-- TOP 3 HORIZONTAL ROW -->
                                    <div class="top3-row">
                                        @foreach ($top3 as $index => $song)
                                            <div class="top3-item">
                                                <div class="top3-rank">{{ $index + 1 }}</div>
                                                <div class="top3-card">
                                                    <div class="top3-card-header">
                                                        <div class="top3-cover">
                                                            <img src="{{ $song->getCoverUrl() }}"
                                                                alt="{{ $song->title }}">
                                                        </div>
                                                        <div class="top3-info">
                                                            <a href="{{ route('songs.show', $song->id) }}"
                                                                class="top3-title">{{ $song->title }}</a>
                                                            <div class="top3-artist">
                                                                {{ $song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="top3-card-footer">
                                                        <button class="top3-play-btn js-play-song"
                                                            data-song-id="{{ $song->id }}"
                                                            data-song-title="{{ e($song->title) }}"
                                                            data-song-artist="{{ e($song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ') }}"
                                                            data-song-cover="{{ $song->getCoverUrl() }}"
                                                            data-stream-url="{{ route('songs.stream', $song->id) }}">
                                                            <i class="fa-solid fa-play ms-1"></i>
                                                        </button>
                                                        <div class="top3-stats">
                                                            <div class="soundwave-icon">
                                                                <div class="soundwave-bar"></div>
                                                                <div class="soundwave-bar"></div>
                                                                <div class="soundwave-bar"></div>
                                                                <div class="soundwave-bar"></div>
                                                                <div class="soundwave-bar"></div>
                                                            </div>
                                                            <span
                                                                class="top3-listens">{{ number_format((int) ($song->listens_count ?? $song->listens)) }}
                                                                lượt nghe</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- RANKS 4-10 GRID -->
                                    <div class="sub-chart-grid">
                                        @foreach ($subs as $index => $song)
                                            <div class="sub-chart-item js-play-song"
                                                data-song-id="{{ $song->id }}"
                                                data-song-title="{{ e($song->title) }}"
                                                data-song-artist="{{ e($song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ') }}"
                                                data-song-cover="{{ $song->getCoverUrl() }}"
                                                data-stream-url="{{ route('songs.stream', $song->id) }}">
                                                <div class="sub-rank">{{ $index + 4 }}</div>
                                                <div class="sub-cover">
                                                    <img src="{{ $song->getCoverUrl() }}" alt="{{ $song->title }}">
                                                    <div class="overlay"><i class="fa-solid fa-play"></i></div>
                                                </div>
                                                <div class="sub-info">
                                                    <a href="{{ route('songs.show', $song->id) }}" class="sub-title"
                                                        onclick="event.stopPropagation()">{{ $song->title }}</a>
                                                    <div class="sub-artist">
                                                        {{ $song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }}</div>
                                                </div>
                                                <form method="POST" action="{{ route('listener.song.toggleFavorite', $song->id) }}" onclick="event.stopPropagation()">
                                                    @csrf
                                                    <button type="submit" class="sub-action btn-favorite-song" style="background:none; border:none; color:inherit;">
                                                        <i class="fa-regular fa-heart"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="chart-footer">
                                        <a href="{{ route('songs.index') }}" class="btn-view-all">XEM TẤT CẢ TẠI KHO NHẠC</a>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>
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
