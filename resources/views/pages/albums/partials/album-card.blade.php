@php
    $artistName = $album->artist?->getDisplayArtistName() ?? 'Nghệ sĩ';
    $isSaved = in_array((int) $album->id, $savedAlbumIds ?? [], true);
    $totalDuration = (int) ($album->published_songs_duration ?? 0);
    $durationText = sprintf('%d:%02d', intdiv($totalDuration, 60), $totalDuration % 60);
@endphp

<article class="album-media-card">
    <a href="{{ route('albums.show', $album->id) }}" class="album-cover-wrap" aria-label="{{ $album->title }}">
        <img src="{{ $album->getCoverUrl() }}" alt="{{ $album->title }}" class="album-cover-image">
    </a>

    <div class="album-card-body">
        <div class="album-title-line">
            <a href="{{ route('albums.show', $album->id) }}" class="album-title">{{ $album->title }}</a>
        </div>

        <div class="album-subtitle">{{ $artistName }}</div>

        <div class="album-meta-row">
            <span><i class="fa-regular fa-calendar me-1"></i>{{ $album->released_date?->format('d/m/Y') ?? 'Chưa cập nhật' }}</span>
            <span>•</span>
            <span><i class="fa-solid fa-music me-1"></i>{{ (int) ($album->published_songs_count ?? 0) }} bài</span>
            <span>•</span>
            <span><i class="fa-regular fa-clock me-1"></i>{{ $durationText }}</span>
        </div>
    </div>

    <div class="album-card-actions">
        <a href="{{ route('albums.show', $album->id) }}" class="btn btn-sm btn-song-detail" title="Chi tiết album">
            <i class="fa-solid fa-circle-info"></i>
        </a>

        @auth
        <form method="POST" action="{{ route('listener.album.toggleSave', $album->id) }}">
            @csrf
            <button class="btn btn-sm {{ $isSaved ? 'btn-album-saved' : 'btn-album-save' }}" title="Lưu album">
                <i class="fa-solid fa-bookmark"></i>
            </button>
        </form>
        @endauth
    </div>
</article>
