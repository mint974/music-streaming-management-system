@extends('layouts.main')

@section('title', 'Songs - Blue Wave Music')

@section('content')
<div class="songs-page">
    @php
        $songsTotal = is_object($songs) && method_exists($songs, 'total') ? $songs->total() : count($songs);
    @endphp

    <section class="songs-hero">
        <div class="hero-overlay"></div>
        

        <div class="songs-hero-stat">
            <div class="label">Hiển thị</div>
            <div class="value">{{ $songsTotal }}</div>
            <div class="sub">bài hát</div>
        </div>
    </section>

    @include('pages.songs.partials.genre-strip', ['genres' => $genres, 'genreId' => $genreId])

    <form method="GET" action="{{ route('songs.index') }}" class="songs-toolbar">
        <input type="hidden" name="q" value="{{ $q }}">
        <input type="hidden" name="genre_id" value="{{ $genreId }}">
        <input type="hidden" name="top_genre_id" value="{{ $topGenreId }}">
        <input type="hidden" name="top_period" value="{{ $topPeriod }}">

        <div class="toolbar-item">
            <label>Sắp xếp</label>
            <select name="sort" class="form-select form-select-sm" onchange="this.form.requestSubmit()">
                <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Nghe nhiều nhất</option>
                <option value="az" {{ $sort === 'az' ? 'selected' : '' }}>A-Z</option>
                <option value="premium" {{ $sort === 'premium' ? 'selected' : '' }}>Premium</option>
            </select>
        </div>

        <div class="toolbar-item">
            <label>Giới hạn card</label>
            <select name="limit" class="form-select form-select-sm" onchange="this.form.requestSubmit()">
                @foreach([6, 8, 10, 12, 16, 20] as $limit)
                    <option value="{{ $limit }}" {{ $cardsLimit === $limit ? 'selected' : '' }}>{{ $limit }} bài</option>
                @endforeach
            </select>
        </div>

        <a href="{{ route('songs.index') }}" class="btn btn-sm btn-outline-light ms-auto">Reset</a>
    </form>

    <div class="songs-content-grid">
        <section class="animate-on-scroll">
            <div class="songs-card-grid">
                @forelse($songs as $song)
                    @include('pages.songs.partials.song-card', ['song' => $song, 'favoriteSongIds' => $favoriteSongIds])
                @empty
                    <div class="songs-empty">
                        <i class="fa-solid fa-music"></i>
                        <p class="mb-0">Không có bài hát phù hợp bộ lọc hiện tại.</p>
                    </div>
                @endforelse
            </div>

            @if(is_object($songs) && method_exists($songs, 'hasPages') && $songs->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $songs->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </section>

        @include('pages.songs.partials.top-panel', [
            'topSongs' => $topSongs,
            'genres' => $genres,
            'q' => $q,
            'genreId' => $genreId,
            'sort' => $sort,
            'cardsLimit' => $cardsLimit,
            'topGenreId' => $topGenreId,
            'topPeriod' => $topPeriod,
            'favoriteSongIds' => $favoriteSongIds,
        ])
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = document.querySelectorAll('.js-song-row');

    const setSelected = (songId) => {
        rows.forEach((row) => {
            row.classList.toggle('is-selected', row.dataset.songRowId === String(songId));
        });
        window.sessionStorage.setItem('bwm_selected_song_id', String(songId));
    };

    const rememberedSongId = window.sessionStorage.getItem('bwm_selected_song_id');
    if (rememberedSongId) {
        setSelected(rememberedSongId);
    }

    rows.forEach((row) => {
        const playButton = row.querySelector('.js-play-song');
        if (!playButton) return;

        row.addEventListener('click', function (event) {
            const blocked = event.target.closest('button, a, input, select, textarea, form, label');
            if (blocked) return;

            playButton.click();
        });

        playButton.addEventListener('click', function () {
            setSelected(playButton.dataset.songId);
        });
    });
});
</script>
@endpush
