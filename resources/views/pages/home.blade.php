@extends('layouts.main')

@section('title', 'Home – Blue Wave Music')

@section('content')
<div class="home-page">

    <section class="hero-section">
        <div class="hero-wrapper">
            <div class="hero-image">
                <img
                    src="{{ asset('images/album-midnight-reverie.jpg') }}"
                    alt="Midnight Reverie"
                    loading="eager"
                    decoding="async"
                    onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27232%27 height=%27232%27%3E%3Crect width=%27232%27 height=%27232%27 fill=%27%231e293b%27/%3E%3Ctext x=%2750%25%27 y=%2750%25%27 dominant-baseline=%27middle%27 text-anchor=%27middle%27 font-family=%27Arial%27 font-size=%2716%27 fill=%27%23e11d48%27 font-weight=%27bold%27%3EAlbum%3C/text%3E%3C/svg%3E'"
                >
            </div>

            <div class="hero-content">
                <div class="hero-type">Albums</div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h1 class="hero-title m-0">Midnight Reverie</h1>
                    <i class="fa-solid fa-circle-check hero-verified" title="Verified"></i>
                </div>

                <div class="hero-subtitle">By Dave</div>

                <div class="hero-meta">
                    <span class="meta-chip"><i class="fa-solid fa-music"></i> 10 songs</span>
                    <span class="meta-chip"><i class="fa-regular fa-clock"></i> 1 hr 30 mins</span>
                </div>

                <div class="hero-actions">
                    <button class="btn mm-btn mm-btn-primary" id="mainPlayBtn">
                        <i class="fa-solid fa-play"></i>
                        <span>Play</span>
                    </button>

                    <button class="btn mm-btn mm-btn-outline" id="shuffleBtn">
                        <i class="fa-solid fa-shuffle"></i>
                        <span>Shuffle</span>
                    </button>

                    <button class="btn mm-icon-btn" id="favoriteBtn" title="Like">
                        <i class="fa-regular fa-heart"></i>
                    </button>

                    <button class="btn mm-icon-btn" title="More">
                        <i class="fa-solid fa-ellipsis"></i>
                    </button>
                </div>
            </div>

            <button class="hero-fab" type="button" aria-label="Play">
                <i class="fa-solid fa-play"></i>
            </button>
        </div>
    </section>

    @include('pages.partials.song-list')

</div>
@endsection
