<section class="songs-top-panel modern-chart-panel">
    <div class="top-panel-head">
        <h3 class="chart-title">BẢNG XẾP HẠNG <i class="fa-solid fa-fire text-danger" style="margin-left: 5px; font-size: 1.2rem; filter: drop-shadow(0 0 5px rgba(239,68,68,0.8));"></i></h3>
    </div>

    <div class="top-panel-filter">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="{{ route('songs.index', ['q' => $q, 'genre_id' => $genreId, 'sort' => $sort, 'limit' => $cardsLimit, 'top_genre_id' => 0, 'top_period' => $topPeriod]) }}" class="btn btn-sm rounded-pill d-flex align-items-center gap-1 {{ $topGenreId === 0 ? 'text-dark fw-bold border-0' : 'border border-secondary text-white' }}" style="font-size: 0.73rem; background: {{ $topGenreId === 0 ? '#38bdf8' : 'rgba(255,255,255,0.03)' }}; transition: all 0.3s;">
                <i class="fa-solid fa-layer-group"></i> Tất cả
            </a>
            @foreach($genres as $genre)
                <a href="{{ route('songs.index', ['q' => $q, 'genre_id' => $genreId, 'sort' => $sort, 'limit' => $cardsLimit, 'top_genre_id' => $genre->id, 'top_period' => $topPeriod]) }}" 
                   class="btn btn-sm rounded-pill d-flex align-items-center gap-1 {{ $topGenreId === (int) $genre->id ? 'text-white fw-bold border-0 shadow-sm' : 'border border-secondary text-white' }}"
                   style="font-size: 0.73rem; background: {{ $topGenreId === (int) $genre->id ? ($genre->color ?: '#0d6efd') : 'rgba(255,255,255,0.03)' }}; transition: all 0.3s;">
                    <i class="{{ $genre->icon ?: 'fa-solid fa-music' }}" style="{{ $topGenreId !== (int) $genre->id ? 'color: ' . ($genre->color ?: '#fff') . ';' : '' }}"></i>
                    {{ $genre->name }}
                </a>
            @endforeach
        </div>

        <div class="top-period-tabs">
            <a href="{{ route('songs.index', ['q' => $q, 'genre_id' => $genreId, 'sort' => $sort, 'limit' => $cardsLimit, 'top_genre_id' => $topGenreId, 'top_period' => 'week']) }}" class="period-tab {{ $topPeriod === 'week' ? 'is-active' : '' }}">Tuần</a>
            <a href="{{ route('songs.index', ['q' => $q, 'genre_id' => $genreId, 'sort' => $sort, 'limit' => $cardsLimit, 'top_genre_id' => $topGenreId, 'top_period' => 'month']) }}" class="period-tab {{ $topPeriod === 'month' ? 'is-active' : '' }}">Tháng</a>
            <a href="{{ route('songs.index', ['q' => $q, 'genre_id' => $genreId, 'sort' => $sort, 'limit' => $cardsLimit, 'top_genre_id' => $topGenreId, 'top_period' => 'quarter']) }}" class="period-tab {{ $topPeriod === 'quarter' ? 'is-active' : '' }}">Quý</a>
        </div>
    </div>

    <div class="top-list custom-scroll-y">
        @forelse($topSongs as $index => $top)
        @php
            $topArtist = $top->artist?->getDisplayArtistName() ?? 'Nghệ sĩ';
            $topFavorited = in_array((int) $top->id, $favoriteSongIds ?? [], true);
            $rankClass = 'rank-other';
            if ($index === 0) $rankClass = 'rank-1';
            elseif ($index === 1) $rankClass = 'rank-2';
            elseif ($index === 2) $rankClass = 'rank-3';
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
            
            <div class="rank">{{ $index + 1 }}</div>
            <div class="title-wrap">
                <div class="title">{{ $top->title }} @if($top->is_vip)<i class="fa-solid fa-crown text-warning ms-1" style="font-size: 0.7em"></i>@endif</div>
                <div class="meta">{{ $topArtist }} • {{ number_format((int) $top->listens) }} lượt nghe</div>
            </div>
            <div class="play-icon">
                <i class="fa-solid fa-play"></i>
            </div>
        </button>
        @empty
        <div class="text-center text-muted p-4">
            <i class="fa-brands fa-itunes-note fa-3x mb-2 opacity-50"></i>
            <div class="small">Chưa có dữ liệu bảng xếp hạng này.</div>
        </div>
        @endforelse
    </div>
</section>
