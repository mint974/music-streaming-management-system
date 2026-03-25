@php
    $artistName = $song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ';
    $coverUrl = $song->cover_image ? $song->getCoverUrl() : asset('images/disk.png');
    $coverClass = $song->cover_image ? 'has-cover' : 'is-fallback';
    $isFavorited = in_array((int) $song->id, $favoriteSongIds ?? [], true);
@endphp

<article class="song-media-card js-song-row" data-song-row-id="{{ $song->id }}">
    <div class="song-cover-wrap {{ $coverClass }}">
        <img src="{{ $coverUrl }}" alt="{{ $song->title }}" class="song-cover-image">
        @if($song->is_vip)
            <span class="song-premium-pill"><i class="fa-solid fa-crown me-1"></i>Premium</span>
        @endif
    </div>

    <div class="card-body song-card-body p-0">
        <div class="song-title">{{ $song->title }}</div>
        <div class="song-subtitle">{{ $artistName }}</div>
        <div class="song-meta-row">
            <span><i class="fa-regular fa-clock me-1"></i>{{ $song->durationFormatted() }}</span>
            <span class="opacity-50">•</span>
            <span><i class="fa-solid fa-headphones me-1"></i>{{ number_format((int) $song->listens) }}</span>
            @if($song->genre)
                <span class="opacity-50">•</span>
                <span style="max-width:60px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $song->genre->name }}</span>
            @endif
        </div>
    </div>

    <div class="song-card-actions">
        <button
            type="button"
            class="btn btn-song-play js-play-song flex-grow-1 rounded-pill d-flex align-items-center justify-content-center"
            data-song-id="{{ $song->id }}"
            data-song-title="{{ e($song->title) }}"
            data-song-artist="{{ e($artistName) }}"
            data-song-cover="{{ $song->getCoverUrl() }}"
            data-song-premium="{{ $song->is_vip ? '1' : '0' }}"
            data-song-favorited="{{ $isFavorited ? '1' : '0' }}"
            data-stream-url="{{ route('songs.stream', $song->id) }}"
            {{ !$song->hasAudioFile() ? 'disabled title="Bài hát đang được cập nhật"' : '' }}
            style="width:auto;height:auto;padding:.45rem 1rem;border-radius:999px;">
            <i class="fa-solid fa-play me-2"></i> Phát nhạc
        </button>

        <a href="{{ route('songs.show', $song->id) }}" class="btn btn-song-detail d-flex align-items-center justify-content-center flex-shrink-0" title="Chi tiết">
            <i class="fa-solid fa-circle-info"></i>
        </a>

        @auth
        <form method="POST" action="{{ route('listener.song.toggleFavorite', $song->id) }}" class="m-0 p-0 d-flex">
            @csrf
            <button class="btn btn-song-like d-flex align-items-center justify-content-center flex-shrink-0 {{ $isFavorited ? 'btn-song-liked' : '' }}" title="Yêu thích">
                <i class="fa-solid fa-heart"></i>
            </button>
        </form>

        <button class="btn btn-song-detail d-flex align-items-center justify-content-center flex-shrink-0" onclick="openAddToPlaylistModal({{ $song->id }})" title="Lưu Playlist">
            <i class="fa-solid fa-list-ul"></i>
        </button>
        @endauth
    </div>
</article>
