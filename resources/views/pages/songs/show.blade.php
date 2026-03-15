@extends('layouts.main')

@section('title', $song->title . ' - Blue Wave Music')

@section('content')
@php
    $artistName = $song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ';
    $coverImage = $song->getCoverUrl();
    $artistAvatar = $song->artist?->getAvatarUrl() ?? asset('images/default-avatar.png');
@endphp

<div class="song-detail-page">
    <section class="song-show-shell">
        <div class="show-cover-col">
            <div class="detail-cover-shell">
                <img src="{{ $coverImage }}" alt="{{ $song->title }}" class="detail-cover-image">
            </div>
        </div>

        <div class="show-main-col">
            <div class="song-meta-top">Bài hát</div>

            <h1 class="detail-title mt-2">
                {{ $song->title }}
                @if($song->is_vip)
                    <span class="song-premium-pill ms-2"><i class="fa-solid fa-crown me-1"></i>Premium</span>
                @endif
            </h1>

            <div class="detail-meta mt-1">{{ $artistName }}</div>

            <div class="detail-chip-row mt-2">
                <span class="detail-chip"><i class="fa-solid fa-headphones me-1"></i>{{ number_format((int) $song->listens) }} lượt nghe</span>
                <span class="detail-chip"><i class="fa-regular fa-clock me-1"></i>{{ $song->durationFormatted() }}</span>
                <span class="detail-chip">{{ $song->genre?->name ?? 'Khác' }}</span>
                @if($song->released_date)
                    <span class="detail-chip"><i class="fa-regular fa-calendar me-1"></i>{{ $song->released_date->format('d/m/Y') }}</span>
                @endif
            </div>

            <div class="detail-actions mt-4">
                <button
                    type="button"
                    class="btn btn-detail-play js-play-song"
                    data-song-id="{{ $song->id }}"
                    data-song-title="{{ e($song->title) }}"
                    data-song-artist="{{ e($artistName) }}"
                    data-song-cover="{{ $song->getCoverUrl() }}"
                    data-song-premium="{{ $song->is_vip ? '1' : '0' }}"
                    data-song-favorited="{{ $isFavorited ? '1' : '0' }}"
                    data-stream-url="{{ route('songs.stream', $song->id) }}">
                    <i class="fa-solid fa-play me-1"></i>Phát bài hát
                </button>

                @if($song->album)
                    <a href="{{ route('albums.show', $song->album->id) }}" class="btn btn-song-detail px-3">
                        <i class="fa-solid fa-compact-disc me-1"></i>Xem album
                    </a>
                @endif

                @auth
                <form method="POST" action="{{ route('listener.song.toggleFavorite', $song->id) }}">
                    @csrf
                    <button class="btn {{ $isFavorited ? 'btn-song-liked px-3 py-2' : 'btn-song-like px-3 py-2' }}">
                        <i class="fa-solid fa-heart me-1"></i>{{ $isFavorited ? 'Đã yêu thích' : 'Yêu thích' }}
                    </button>
                </form>
                @endauth
            </div>

            @if($song->album)
            <div class="song-detail-info mt-4">
                <h6>Album</h6>
                <div class="row-item"><span>Tên album</span><strong>{{ $song->album->title }}</strong></div>
                @auth
                <form method="POST" action="{{ route('listener.album.toggleSave', $song->album->id) }}" class="mt-2">
                    @csrf
                    <button class="btn btn-sm {{ $isAlbumSaved ? 'btn-album-saved' : 'btn-album-save' }}">
                        <i class="fa-solid fa-bookmark me-1"></i>{{ $isAlbumSaved ? 'Đã lưu album' : 'Lưu album' }}
                    </button>
                </form>
                @endauth
            </div>
            @endif

            @if($song->lyrics)
            <div class="song-lyrics-panel mt-4">
                <div class="section-title d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Lời bài hát</h5>
                    <small class="text-muted">Nguồn lời: hệ thống nghệ sĩ</small>
                </div>
                <div class="lyrics-box mt-3">{!! nl2br(e($song->lyrics)) !!}</div>
            </div>
            @endif
        </div>

        <aside class="show-side-col">
            <div class="artist-side-card">
                <div class="artist-side-title">Nghệ sĩ</div>
                <div class="artist-side-head">
                    <img src="{{ $artistAvatar }}" alt="{{ $artistName }}" class="artist-side-avatar">
                    <div>
                        <div class="artist-side-name">
                            {{ $artistName }}
                            @if($song->artist?->artist_verified_at)
                                <i class="fa-solid fa-circle-check ms-1"></i>
                            @endif
                        </div>
                        <div class="artist-side-sub">{{ number_format((int) ($song->artist?->followers()->count() ?? 0)) }} người theo dõi</div>
                    </div>
                </div>
                @if($song->artist?->bio)
                    <div class="artist-side-bio">{{ \Illuminate\Support\Str::limit($song->artist->bio, 140) }}</div>
                @endif
            </div>

            <div class="right-pane mt-3">
                <div class="tracklist-head">
                    <span>Nghe tiếp</span>
                    <span>Thời lượng</span>
                </div>

                <div class="tracklist-item is-active">
                    <div class="track-main">
                        <img src="{{ $song->getCoverUrl() }}" alt="{{ $song->title }}">
                        <div>
                            <div class="name">{{ $song->title }}</div>
                            <div class="artist">{{ $artistName }}</div>
                        </div>
                    </div>
                    <div class="duration">{{ $song->durationFormatted() }}</div>
                </div>

                @foreach($artistSongs->take(5) as $item)
                @php $itemFavorited = in_array((int) $item->id, $favoriteSongIds, true); @endphp
                <div class="tracklist-item">
                    <div class="track-main">
                        <img src="{{ $item->getCoverUrl() }}" alt="{{ $item->title }}">
                        <div>
                            <a class="name" href="{{ route('songs.show', $item->id) }}">{{ $item->title }}</a>
                            <div class="artist">
                                {{ $item->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }}
                                @if($item->is_vip)
                                    <i class="fa-solid fa-crown premium-crown ms-1"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button
                            type="button"
                            class="btn btn-sm btn-song-play js-play-song"
                            data-song-id="{{ $item->id }}"
                            data-song-title="{{ e($item->title) }}"
                            data-song-artist="{{ e($item->artist?->getDisplayArtistName() ?? 'Nghệ sĩ') }}"
                            data-song-cover="{{ $item->getCoverUrl() }}"
                            data-song-premium="{{ $item->is_vip ? '1' : '0' }}"
                            data-song-favorited="{{ $itemFavorited ? '1' : '0' }}"
                            data-stream-url="{{ route('songs.stream', $item->id) }}">
                            <i class="fa-solid fa-play"></i>
                        </button>
                        <div class="duration">{{ $item->durationFormatted() }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </aside>
    </section>

    <section class="artist-more-songs mt-4">
        <div class="section-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Các bài hát khác của {{ $artistName }}</h5>
            <a href="{{ route('songs.index', ['q' => $artistName]) }}" class="btn btn-sm btn-song-detail">Xem tất cả</a>
        </div>

        <div class="songs-card-grid mt-3">
            @forelse($artistSongs as $relatedSong)
                @include('pages.songs.partials.song-card', ['song' => $relatedSong, 'favoriteSongIds' => $favoriteSongIds])
            @empty
                <div class="songs-empty"><p class="mb-0 text-muted">Chưa có bài hát liên quan.</p></div>
            @endforelse
        </div>
    </section>

    <section class="artist-more-songs mt-4">
        <div class="section-title d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Album của {{ $artistName }}</h5>
            <a href="{{ route('albums.index', ['q' => $artistName]) }}" class="btn btn-sm btn-song-detail">Xem thêm album</a>
        </div>

        <div class="albums-card-grid mt-3">
            @forelse($artistAlbums as $artistAlbum)
                @include('pages.albums.partials.album-card', ['album' => $artistAlbum, 'savedAlbumIds' => $savedAlbumIds])
            @empty
                <div class="songs-empty"><p class="mb-0 text-muted">Nghệ sĩ chưa có album công khai.</p></div>
            @endforelse
        </div>
    </section>
</div>
@endsection
