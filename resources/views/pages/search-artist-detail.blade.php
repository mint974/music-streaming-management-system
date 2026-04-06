@extends('layouts.main')

@section('title', ($artist->artist_name ?: $artist->name) . ' – Nghệ sĩ Blue Wave Music')

@section('content')
@php
    $artistName = $artist->artist_name ?: $artist->name;
    $social = $artist->getSocialLinksFiltered();
@endphp

<div class="artist-public-page px-1">
    <x-sparkles :count="8" />

    <div class="artist-public-hero mb-4">
        <div class="aph-cover" style="background-image:url('{{ $artist->cover_image ? asset($artist->cover_image) : asset('images/default-song.png') }}')"></div>
        <div class="aph-overlay"></div>

        <div class="aph-content">
            <img src="{{ $artist->getAvatarUrl() }}" alt="{{ $artistName }}" class="aph-avatar">
            <div class="aph-meta">
                <div class="aph-role">Nghệ sĩ</div>
                <h1>
                    {{ $artistName }}
                    @if($artist->artist_verified_at)
                        <i class="fa-solid fa-circle-check" title="Đã xác minh"></i>
                    @endif
                </h1>
                <p>{{ \Illuminate\Support\Str::limit($artist->bio ?: 'Nghệ sĩ trên Blue Wave Music.', 180) }}</p>
                <div class="aph-stats">
                    <span><i class="fa-solid fa-music"></i>{{ $songsCount }} bài hát</span>
                    <span><i class="fa-solid fa-compact-disc"></i>{{ $albumsCount }} album</span>
                    <span><i class="fa-solid fa-calendar-days"></i>Tham gia {{ $artist->created_at?->format('m/Y') }}</span>
                </div>

                @auth
                    @if(auth()->id() !== $artist->id)
                    <form method="POST" action="{{ route('listener.artist.toggleFollow', $artist->id) }}" class="mt-3">
                        @csrf
                        <button class="btn btn-sm {{ $isFollowingArtist ? 'btn-danger' : 'btn-primary' }}">
                            <i class="fa-solid {{ $isFollowingArtist ? 'fa-user-minus' : 'fa-user-plus' }} me-1"></i>
                            {{ $isFollowingArtist ? 'Hủy theo dõi' : 'Theo dõi nghệ sĩ' }}
                        </button>
                    </form>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <div class="artist-public-grid">
        <section class="artist-public-section">
            <div class="search-section-title mb-3">
                <i class="fa-solid fa-user"></i>Thông tin công khai
            </div>

            <div class="public-info-card">
                <div class="pic-title">Giới thiệu</div>
                <div class="pic-text">
                    {{ $artist->bio ?: 'Nghệ sĩ chưa cập nhật phần giới thiệu công khai.' }}
                </div>

                <div class="pic-list mt-4">
                    <div class="pic-item">
                        <span class="label">Tên hiển thị</span>
                        <span class="value">{{ $artistName }}</span>
                    </div>
                    <div class="pic-item">
                        <span class="label">Tài khoản tạo từ</span>
                        <span class="value">{{ $artist->created_at?->format('d/m/Y') }}</span>
                    </div>
                    <div class="pic-item">
                        <span class="label">Xác minh</span>
                        <span class="value">{{ $artist->artist_verified_at ? 'Đã xác minh' : 'Chưa xác minh' }}</span>
                    </div>
                    @if($latestRegistration?->package)
                    <div class="pic-item">
                        <span class="label">Gói nghệ sĩ gần nhất</span>
                        <span class="value">{{ $latestRegistration->package->name }}</span>
                    </div>
                    @endif
                </div>

                @if(count($social) > 0)
                <div class="pic-social mt-3">
                    @foreach([
                        'facebook'  => ['fab fa-facebook', '#1877f2'],
                        'instagram' => ['fab fa-instagram', '#e1306c'],
                        'youtube'   => ['fab fa-youtube', '#ff0000'],
                        'tiktok'    => ['fab fa-tiktok', '#ffffff'],
                        'spotify'   => ['fab fa-spotify', '#1ed760'],
                        'website'   => ['fas fa-globe', '#94a3b8'],
                    ] as $key => [$icon, $color])
                        @if(!empty($social[$key]))
                        <a href="{{ $social[$key] }}" target="_blank" rel="noopener" style="color:{{ $color }}">
                            <i class="{{ $icon }}"></i>
                        </a>
                        @endif
                    @endforeach
                </div>
                @endif
            </div>
        </section>

        <section class="artist-public-section">
            <div class="search-section-title mb-3">
                <i class="fa-solid fa-list-music"></i>Nội dung đã công khai
            </div>

            <div class="search-tab-wrap mb-4">
                <a href="{{ route('search.artist.show', ['artistId' => $artist->id, 'tab' => 'songs']) }}"
                   class="search-tab-pill js-tab-switch {{ $tab === 'songs' ? 'active songs-tab' : '' }}">
                    <i class="fa-solid fa-music"></i>Nhạc
                    <span class="badge rounded-pill bg-secondary" style="font-size:.68rem">{{ $songsCount }}</span>
                </a>
                <a href="{{ route('search.artist.show', ['artistId' => $artist->id, 'tab' => 'albums']) }}"
                   class="search-tab-pill js-tab-switch {{ $tab === 'albums' ? 'active albums-tab' : '' }}">
                    <i class="fa-solid fa-compact-disc"></i>Album
                    <span class="badge rounded-pill bg-secondary" style="font-size:.68rem">{{ $albumsCount }}</span>
                </a>
            </div>

            <div id="artistTabContent" class="tab-content-body">

            @if($tab === 'songs')
                @if(count($songs) === 0)
                <div class="search-empty-state">
                    <i class="fa-solid fa-music"></i>
                    <p>Nghệ sĩ chưa có bài hát công khai.</p>
                </div>
                @else
                <div class="search-song-list">
                    @foreach($songs as $song)
                    <div class="search-song-item" style="position:relative;">
                        {{-- Hidden GET form → trang chi tiết bài hát --}}
                        <form method="GET"
                              action="{{ route('songs.redirect') }}"
                              id="songDetailForm{{ $song->id }}"
                              style="display:none">
                            <input type="hidden" name="song_id" value="{{ $song->id }}">
                        </form>

                        {{-- Ảnh bìa: click → chi tiết --}}
                        <img src="{{ $song->getCoverUrl() }}"
                             alt="{{ $song->title }}"
                             class="ssi-cover"
                             style="cursor:pointer"
                             onclick="document.getElementById('songDetailForm{{ $song->id }}').submit()"
                             title="Xem chi tiết bài hát">

                        {{-- Thông tin bài hát: click → chi tiết --}}
                        <div class="ssi-main"
                             style="cursor:pointer"
                             onclick="document.getElementById('songDetailForm{{ $song->id }}').submit()">
                            <div class="ssi-title">
                                {{ $song->title }}
                                @if($song->is_vip)
                                    <i class="fa-solid fa-crown text-warning ms-1" style="font-size: 0.8rem;" title="Premium"></i>
                                @endif
                            </div>
                            <div class="ssi-meta">
                                @if($song->album)
                                    <span>{{ $song->album->title }}</span>
                                    <span>•</span>
                                @endif
                                <span>{{ number_format($song->listens) }} lượt nghe</span>
                            </div>
                        </div>

                        {{-- Actions: thời lượng + nút phát (KHÔNG navigate) --}}
                        <div class="ssi-actions">
                            <span class="ssi-duration">{{ $song->durationFormatted() }}</span>
                            <button
                                type="button"
                                class="ssi-play js-play-song"
                                data-song-id="{{ $song->id }}"
                                data-song-title="{{ e($song->title) }}"
                                data-song-artist="{{ e($artistName) }}"
                                data-song-cover="{{ $song->getCoverUrl() }}"
                                data-song-premium="{{ $song->is_vip ? '1' : '0' }}"
                                data-stream-url="{{ route('songs.stream', $song->id) }}">
                                <i class="fa-solid fa-play"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if(is_object($songs) && method_exists($songs, 'appends'))
                <div class="d-flex justify-content-center mt-4">
                    {{ $songs->appends(['tab' => 'songs'])->links('pagination::bootstrap-5') }}
                </div>
                @endif
                @endif
            @endif

            @if($tab === 'albums')
                @if(count($albums) === 0)
                <div class="search-empty-state">
                    <i class="fa-solid fa-compact-disc"></i>
                    <p>Nghệ sĩ chưa có album công khai.</p>
                </div>
                @else
                <div class="search-album-grid">
                    @foreach($albums as $album)
                    <div class="search-album-card">
                        <img src="{{ $album->getCoverUrl() }}" alt="{{ $album->title }}" class="sac-cover">
                        <div class="sac-body">
                            <div class="sac-title">{{ $album->title }}</div>
                            <div class="sac-meta">
                                <span>{{ $album->released_date?->format('d/m/Y') ?? 'Chưa cập nhật' }}</span>
                                <span>• {{ $album->published_songs_count }} bài</span>
                            </div>
                            @if($album->description)
                            <div class="sac-desc">{{ \Illuminate\Support\Str::limit($album->description, 88) }}</div>
                            @endif
                            @auth
                            <form method="POST" action="{{ route('listener.album.toggleSave', $album->id) }}" class="mt-3">
                                @csrf
                                <button class="btn btn-sm {{ in_array((int) $album->id, $savedAlbumIds, true) ? 'btn-warning' : 'btn-outline-light' }}">
                                    <i class="fa-solid {{ in_array((int) $album->id, $savedAlbumIds, true) ? 'fa-bookmark' : 'fa-bookmark' }} me-1"></i>
                                    {{ in_array((int) $album->id, $savedAlbumIds, true) ? 'Đã lưu album' : 'Lưu album' }}
                                </button>
                            </form>
                            @endauth
                        </div>
                    </div>
                    @endforeach
                </div>
                @if(is_object($albums) && method_exists($albums, 'appends'))
                <div class="d-flex justify-content-center mt-4">
                    {{ $albums->appends(['tab' => 'albums'])->links('pagination::bootstrap-5') }}
                </div>
                @endif
                @endif
            @endif

            </div>

            <div id="artistTabSkeleton" class="search-tab-skeleton d-none" aria-hidden="true">
                <div class="sts-row"></div>
                <div class="sts-row"></div>
                <div class="sts-row short"></div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabContent = document.getElementById('artistTabContent');
    const tabSkeleton = document.getElementById('artistTabSkeleton');

    const showLoading = () => {
        if (!tabContent || !tabSkeleton) return;
        tabContent.classList.add('d-none');
        tabSkeleton.classList.remove('d-none');
    };

    document.querySelectorAll('.js-tab-switch').forEach((tabLink) => {
        tabLink.addEventListener('click', function () {
            showLoading();
        });
    });

    document.querySelectorAll('.pagination a').forEach((pageLink) => {
        pageLink.addEventListener('click', function () {
            showLoading();
        });
    });
});
</script>
@endpush
