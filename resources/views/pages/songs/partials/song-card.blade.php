@php
    $artistName = $song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ';
    $coverUrl = $song->cover_image ? $song->getCoverUrl() : asset('images/disk.png');
    $coverClass = $song->cover_image ? 'has-cover' : 'is-fallback';
    $isFavorited = in_array((int) $song->id, $favoriteSongIds ?? [], true);
@endphp

<article class="card song-media-card js-song-row h-100 d-flex flex-column shadow-sm transition-all" data-song-row-id="{{ $song->id }}" style="background-color: var(--black-soft); border: 1px solid var(--black-hover); border-radius: 12px; overflow: hidden; min-width: 200px;">
    <div class="song-cover-wrap {{ $coverClass }} position-relative" style="aspect-ratio: 1/1;">
        <img src="{{ $coverUrl }}" alt="{{ $song->title }}" class="card-img-top song-cover-image w-100 h-100 object-fit-cover">
        @if($song->is_vip)
            <span class="song-premium-pill position-absolute top-0 end-0 m-2 badge" style="background-color: var(--purple-main);"><i class="fa-solid fa-crown me-1"></i>Premium</span>
        @endif
    </div>

    <div class="card-body song-card-body d-flex flex-column flex-grow-1 text-white p-3">
        <h6 class="card-title song-title text-truncate fw-bold mb-1" style="color: var(--white-main); font-size: 1rem;">{{ $song->title }}</h6>
        <div class="song-subtitle text-truncate mb-2" style="color: var(--text-muted); font-size: 0.85rem;">{{ $artistName }}</div>
        
        <div class="song-meta-row mt-auto pt-2 d-flex align-items-center flex-wrap" style="color: var(--text-muted); font-size: 0.75rem; gap: 4px;">
            <span><i class="fa-regular fa-clock me-1"></i>{{ $song->durationFormatted() }}</span>
            <span class="opacity-50">•</span>
            <span><i class="fa-solid fa-headphones me-1"></i>{{ number_format((int) $song->listens) }}</span>
            @if($song->genre)
                <span class="opacity-50">•</span>
                <span class="text-truncate" style="max-width: 60px;">{{ $song->genre->name }}</span>
            @endif
        </div>
    </div>

    <div class="card-footer song-card-actions bg-transparent border-0 pt-0 pb-3 px-3 d-flex align-items-center gap-2 mt-auto">
        <button
            type="button"
            class="btn btn-hero-play js-play-song flex-grow-1 rounded-pill d-flex align-items-center justify-content-center shadow-sm"
            data-song-id="{{ $song->id }}"
            data-song-title="{{ e($song->title) }}"
            data-song-artist="{{ e($artistName) }}"
            data-song-cover="{{ $song->getCoverUrl() }}"
            data-song-premium="{{ $song->is_vip ? '1' : '0' }}"
            data-song-favorited="{{ $isFavorited ? '1' : '0' }}"
            data-stream-url="{{ route('songs.stream', $song->id) }}"
            {{ !$song->hasAudioFile() ? 'disabled title="Bài hát đang được cập nhật"' : '' }}
            style="background: var(--primary-blue); color: var(--white-main); border: none; padding: 0.5rem 1rem; font-weight: 600; font-size: 0.85rem; transition: opacity 0.2s;">
            <i class="fa-solid fa-play me-2"></i> Phát nhạc
        </button>

        <a href="{{ route('songs.show', $song->id) }}" class="btn rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" title="Chi tiết" style="width: 38px; height: 38px; background-color: var(--black-hover); color: var(--text-primary); border: none; transition: background 0.2s;">
            <i class="fa-solid fa-circle-info"></i>
        </a>

        @auth
        <form method="POST" action="{{ route('listener.song.toggleFavorite', $song->id) }}" class="m-0 p-0 d-flex">
            @csrf
            <button class="btn rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 {{ $isFavorited ? 'text-danger' : '' }}" title="Yêu thích" style="width: 38px; height: 38px; background-color: var(--black-hover); border: none; color: {{ $isFavorited ? 'var(--red-main)' : 'var(--text-primary)' }}; transition: color 0.2s, background 0.2s;">
                <i class="fa-solid fa-heart"></i>
            </button>
        </form>
        @endauth
    </div>
</article>
