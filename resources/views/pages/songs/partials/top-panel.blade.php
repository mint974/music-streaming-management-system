<section class="songs-top-panel">
    <div class="top-panel-head">
        <h5 class="mb-0">Top 5 bài hát nghe nhiều</h5>
        <div class="small text-muted">Lọc theo thể loại và mốc thời gian</div>
    </div>

    <div class="top-panel-filter">
        <div class="top-genre-chips">
            <a href="{{ route('songs.index', ['q' => $q, 'genre_id' => $genreId, 'sort' => $sort, 'limit' => $cardsLimit, 'top_genre_id' => 0, 'top_period' => $topPeriod]) }}" class="top-chip {{ $topGenreId === 0 ? 'is-active' : '' }}">Tất cả</a>
            @foreach($genres as $genre)
                <a href="{{ route('songs.index', ['q' => $q, 'genre_id' => $genreId, 'sort' => $sort, 'limit' => $cardsLimit, 'top_genre_id' => $genre->id, 'top_period' => $topPeriod]) }}" class="top-chip {{ $topGenreId === (int) $genre->id ? 'is-active' : '' }}">{{ $genre->name }}</a>
            @endforeach
        </div>

        <div class="top-period-tabs">
            <a href="{{ route('songs.index', ['q' => $q, 'genre_id' => $genreId, 'sort' => $sort, 'limit' => $cardsLimit, 'top_genre_id' => $topGenreId, 'top_period' => 'week']) }}" class="period-tab {{ $topPeriod === 'week' ? 'is-active' : '' }}">Tuần</a>
            <a href="{{ route('songs.index', ['q' => $q, 'genre_id' => $genreId, 'sort' => $sort, 'limit' => $cardsLimit, 'top_genre_id' => $topGenreId, 'top_period' => 'month']) }}" class="period-tab {{ $topPeriod === 'month' ? 'is-active' : '' }}">Tháng</a>
            <a href="{{ route('songs.index', ['q' => $q, 'genre_id' => $genreId, 'sort' => $sort, 'limit' => $cardsLimit, 'top_genre_id' => $topGenreId, 'top_period' => 'year']) }}" class="period-tab {{ $topPeriod === 'year' ? 'is-active' : '' }}">Năm</a>
        </div>
    </div>

    <div class="top-list">
        @forelse($topSongs as $index => $top)
        @php
            $topArtist = $top->artist?->getDisplayArtistName() ?? 'Nghệ sĩ';
            $topFavorited = in_array((int) $top->id, $favoriteSongIds ?? [], true);
        @endphp
        <button
            type="button"
            class="top-song-item js-play-song"
            data-song-id="{{ $top->id }}"
            data-song-title="{{ e($top->title) }}"
            data-song-artist="{{ e($topArtist) }}"
            data-song-cover="{{ $top->getCoverUrl() }}"
            data-song-premium="{{ $top->is_vip ? '1' : '0' }}"
            data-song-favorited="{{ $topFavorited ? '1' : '0' }}"
            data-stream-url="{{ route('songs.stream', $top->id) }}">
            <span class="rank">{{ $index + 1 }}</span>
            <span class="title-wrap">
                <span class="title">{{ $top->title }} @if($top->is_vip)<i class="fa-solid fa-crown premium-crown ms-1"></i>@endif</span>
                <span class="meta">{{ $topArtist }} • {{ number_format((int) $top->listens) }} lượt nghe</span>
            </span>
            <span class="play-icon"><i class="fa-solid fa-play"></i></span>
        </button>
        @empty
        <div class="text-muted small">Chưa có dữ liệu top phù hợp.</div>
        @endforelse
    </div>
</section>
