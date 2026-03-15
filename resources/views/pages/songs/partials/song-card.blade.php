@php
    $artistName = $song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ';
    $coverUrl = $song->cover_image ? $song->getCoverUrl() : asset('images/disk.png');
    $discClass = $song->cover_image ? 'has-cover' : 'is-fallback';
    $isFavorited = in_array((int) $song->id, $favoriteSongIds ?? [], true);
@endphp

<article class="song-media-card js-song-row" data-song-row-id="{{ $song->id }}">
    <div class="song-disc-wrap {{ $discClass }}">
        <img src="{{ $coverUrl }}" alt="{{ $song->title }}" class="song-disc-image">
    </div>

    <div class="song-card-body">
        <div class="song-title-line">
            <h6 class="song-title">{{ $song->title }}</h6>
            @if($song->is_vip)
                <span class="song-premium-pill"><i class="fa-solid fa-crown me-1"></i>Premium</span>
            @endif
        </div>

        <div class="song-subtitle">{{ $artistName }}</div>

        <div class="song-meta-row">
            <span><i class="fa-regular fa-clock me-1"></i>{{ $song->durationFormatted() }}</span>
            <span>•</span>
            <span><i class="fa-solid fa-headphones me-1"></i>{{ number_format((int) $song->listens) }}</span>
            @if($song->genre)
                <span>•</span>
                <span>{{ $song->genre->name }}</span>
            @endif
        </div>
    </div>

    <div class="song-card-actions">
        <button
            type="button"
            class="btn btn-sm btn-song-play js-play-song"
            data-song-id="{{ $song->id }}"
            data-song-title="{{ e($song->title) }}"
            data-song-artist="{{ e($artistName) }}"
            data-song-cover="{{ $song->getCoverUrl() }}"
            data-song-premium="{{ $song->is_vip ? '1' : '0' }}"
            data-song-favorited="{{ $isFavorited ? '1' : '0' }}"
            data-stream-url="{{ route('songs.stream', $song->id) }}">
            <i class="fa-solid fa-play"></i>
        </button>

        <a href="{{ route('songs.show', $song->id) }}" class="btn btn-sm btn-song-detail" title="Chi tiết">
            <i class="fa-solid fa-circle-info"></i>
        </a>

        @auth
        <form method="POST" action="{{ route('listener.song.toggleFavorite', $song->id) }}">
            @csrf
            <button class="btn btn-sm {{ $isFavorited ? 'btn-song-liked' : 'btn-song-like' }}" title="Yêu thích">
                <i class="fa-solid fa-heart"></i>
            </button>
        </form>
        @endauth
    </div>
</article>
