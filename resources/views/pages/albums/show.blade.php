@extends('layouts.main')

@section('title', $album->title . ' - Album - Blue Wave Music')

@section('content')
@php
    $artistName     = $album->artist?->getDisplayArtistName() ?? 'Nghệ sĩ';
    $artistAvatar   = $album->artist?->getAvatarUrl() ?? asset('images/default-avatar.png');
    $totalSeconds   = (int) $albumDuration;
    $durationText   = $totalSeconds >= 3600
        ? sprintf('%d:%02d:%02d', intdiv($totalSeconds, 3600), intdiv($totalSeconds % 3600, 60), $totalSeconds % 60)
        : sprintf('%d:%02d', intdiv($totalSeconds, 60), $totalSeconds % 60);
    $totalListens   = $tracks->sum('listens');
    $canUseOffline  = auth()->check() && auth()->user()->canAccessPremium();
    $offlineUrls    = $tracks->map(fn ($track) => route('songs.stream', $track->id))->values();
@endphp

<div class="albums-page">
<div class="song-detail-page container py-4">
    <div class="row">
        {{-- ===== MAIN COLUMN ===== --}}
        <div class="col-lg-8">

            {{-- CARD 1: ALBUM COVER + INFO --}}
            <div class="card song-detail-card mb-4">
                <div class="card-body song-detail-hero-body">
                    {{-- Cover --}}
                    <div class="detail-cover-wrapper mb-3" style="width: 100%;">
                        <img src="{{ $album->getCoverUrl() }}"
                             alt="{{ $album->title }}"
                             class="song-cover-large w-100 rounded shadow"
                             style="height: 33vh; object-fit: cover; object-position: center;">
                    </div>

                    <div class="song-meta-top mb-2">Album</div>

                    <h1 class="detail-title mb-2">{{ $album->title }}</h1>

                    <div class="detail-meta mb-3">
                        <a href="{{ $album->artist?->id ? route('search.artist.show', $album->artist->id) : '#' }}"
                           class="artist-link">
                            {{ $artistName }}
                        </a>
                        @if($album->artist?->artist_verified_at)
                            <i class="fa-solid fa-circle-check verify-icon ms-1"></i>
                        @endif
                    </div>

                    <div class="detail-chip-row mb-4">
                        <span class="detail-chip">
                            <i class="fa-solid fa-music me-1"></i>{{ $tracks->count() }} bài hát
                        </span>
                        <span class="detail-chip">
                            <i class="fa-regular fa-clock me-1"></i>{{ $durationText }}
                        </span>
                        @if($album->released_date)
                            <span class="detail-chip">
                                <i class="fa-regular fa-calendar me-1"></i>{{ $album->released_date->format('d/m/Y') }}
                            </span>
                        @endif
                        <span class="detail-chip">
                            <i class="fa-solid fa-headphones me-1"></i>{{ number_format($totalListens) }} lượt nghe
                        </span>
                    </div>

                    {{-- Actions --}}
                    <div class="detail-actions">
                        @if($tracks->isNotEmpty())
                            @php $firstTrack = $tracks->first(); @endphp
                            <button
                                type="button"
                                class="btn btn-song-play js-play-song"
                                data-song-id="{{ $firstTrack->id }}"
                                data-song-title="{{ e($firstTrack->title) }}"
                                data-song-artist="{{ e($firstTrack->artist?->getDisplayArtistName() ?? 'Nghệ sĩ') }}"
                                data-song-cover="{{ $firstTrack->getCoverUrl() }}"
                                data-song-premium="{{ $firstTrack->is_vip ? '1' : '0' }}"
                                data-stream-url="{{ route('songs.stream', $firstTrack->id) }}">
                                <i class="fa-solid fa-play me-1"></i>Phát album
                            </button>
                        @endif

                        @auth
                        <form method="POST" action="{{ route('listener.album.toggleSave', $album->id) }}" class="m-0">
                            @csrf
                            <button class="btn {{ $isSaved ? 'btn-album-saved px-3' : 'btn-album-save px-3' }}">
                                <i class="fa-solid fa-bookmark me-1"></i>{{ $isSaved ? 'Đã lưu' : 'Lưu album' }}
                            </button>
                        </form>
                        @endauth
                        @if($canUseOffline)
                            <button type="button" id="btnAlbumOffline" class="btn btn-outline-success px-3" onclick="syncAlbumOffline()">
                                <i class="fa-solid fa-download me-1"></i>Tải album offline
                            </button>
                            <div id="albumOfflineStatus" class="small text-muted ms-auto"></div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- CARD 2: ALBUM INFO --}}
            <div class="card song-detail-card song-info-card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Thông tin album</h6>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="info-label">Nghệ sĩ</span>
                        <span class="info-value">{{ $artistName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Số bài hát</span>
                        <span class="info-value">{{ $tracks->count() }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tổng thời lượng</span>
                        <span class="info-value">{{ $durationText }}</span>
                    </div>
                    @if($album->released_date)
                    <div class="info-row">
                        <span class="info-label">Ngày phát hành</span>
                        <span class="info-value">{{ $album->released_date->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Tổng lượt nghe</span>
                        <span class="info-value">{{ number_format($totalListens) }}</span>
                    </div>

                    @if($album->description)
                    <div class="info-row" style="flex-direction: column; align-items: flex-start; gap: .4rem;">
                        <span class="info-label">Mô tả</span>
                        <span class="info-value text-start" style="text-align:left!important; color: rgba(255,255,255,.78); font-size:.84rem; line-height:1.65;">
                            {{ $album->description }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- CARD 3: TRACKLIST --}}
            <div class="card song-detail-card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Danh sách bài hát</h6>
                </div>
                <div class="card-body p-0">
                    @forelse($tracks as $index => $track)
                    <div class="tracklist-item {{ $loop->first ? 'is-active' : '' }}">
                        <div class="track-main">
                            <img src="{{ $track->getCoverUrl() }}" alt="{{ $track->title }}">
                            <div style="min-width:0;">
                                <a class="name" href="{{ route('songs.show', $track->id) }}">{{ $track->title }}</a>
                                <div class="artist">
                                    {{ $track->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }}
                                    @if($track->is_vip)
                                        <i class="fa-solid fa-crown premium-crown ms-1"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button
                                type="button"
                                class="btn btn-sm btn-song-play js-play-song"
                                data-song-id="{{ $track->id }}"
                                data-song-title="{{ e($track->title) }}"
                                data-song-artist="{{ e($track->artist?->getDisplayArtistName() ?? 'Nghệ sĩ') }}"
                                data-song-cover="{{ $track->getCoverUrl() }}"
                                data-song-premium="{{ $track->is_vip ? '1' : '0' }}"
                                data-stream-url="{{ route('songs.stream', $track->id) }}">
                                <i class="fa-solid fa-play"></i>
                            </button>
                            <div class="duration">{{ $track->durationFormatted() }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 text-center text-muted">
                        <i class="fa-solid fa-compact-disc mb-2 d-block" style="font-size:2rem; opacity:.4;"></i>
                        Album này chưa có bài hát công khai.
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- CARD 4: OTHER ALBUMS BY ARTIST --}}
            @if($artistOtherAlbums->count() > 0)
            <div class="card song-detail-card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="mb-0">Album khác của {{ $artistName }}</h6>
                    <a href="{{ route('albums.index', ['q' => $artistName]) }}" class="btn btn-sm btn-song-detail">Xem tất cả</a>
                </div>
                <div class="card-body">
                    <div class="albums-card-grid">
                        @foreach($artistOtherAlbums as $otherAlbum)
                            @include('pages.albums.partials.album-card', ['album' => $otherAlbum, 'savedAlbumIds' => $savedAlbumIds])
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- ===== SIDEBAR ===== --}}
        <div class="col-lg-4">
            {{-- ARTIST INFO CARD --}}
            <div class="card song-detail-card artist-sidebar-card mb-4">
                <div class="card-body">
                    <div class="artist-side-title mb-3">Nghệ sĩ</div>
                    <div class="artist-side-head">
                        <img src="{{ $artistAvatar }}" alt="{{ $artistName }}" class="artist-side-avatar me-3">
                        <div class="flex-grow-1">
                            <div class="artist-side-name">
                                <a href="{{ $album->artist?->id ? route('search.artist.show', $album->artist->id) : '#' }}"
                                   class="artist-link">{{ $artistName }}</a>
                                @if($album->artist?->artist_verified_at)
                                    <i class="fa-solid fa-circle-check ms-1"></i>
                                @endif
                            </div>
                            <div class="artist-side-sub">
                                {{ number_format((int) ($album->artist?->followers()->count() ?? 0)) }} người theo dõi
                            </div>
                        </div>
                    </div>
                    @if($album->artist?->bio)
                        <div class="artist-side-bio mt-3">{{ \Illuminate\Support\Str::limit($album->artist->bio, 140) }}</div>
                    @endif
                </div>
            </div>

            {{-- TRACKLIST SIDEBAR --}}
            <div class="card song-detail-card tracklist-card">
                <div class="card-header">
                    <h6 class="mb-0">Nghe tiếp</h6>
                </div>
                <div class="card-body p-0">
                    @foreach($tracks->take(8) as $item)
                    <div class="tracklist-item {{ $loop->first ? 'is-active' : '' }}">
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
                                data-stream-url="{{ route('songs.stream', $item->id) }}">
                                <i class="fa-solid fa-play"></i>
                            </button>
                            <div class="duration">{{ $item->durationFormatted() }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
{{-- /.song-detail-page --}}
</div>
{{-- /.albums-page --}}
@endsection

@push('scripts')
<script>
    const albumOfflineUrls = @json($offlineUrls);

    async function syncAlbumOffline() {
        const btn = document.getElementById('btnAlbumOffline');
        if (!btn) return;

        if (!window.BWMOffline || !window.BWMOffline.isSupported()) {
            alert('Trình duyệt không hỗ trợ Cache Storage API tải offline!');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Đang tải...';

        try {
            await window.BWMOffline.syncUrls(albumOfflineUrls, ({ done, total }) => {
                btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin me-1"></i>Đang tải (${done}/${total})`;
            });

            btn.classList.remove('btn-outline-success');
            btn.classList.add('btn-success');
            btn.innerHTML = '<i class="fa-solid fa-check me-1"></i>Đã lưu offline';
            showToast('Đã lưu toàn bộ bài hát trong album để nghe offline.', 'success');
            await window.BWMOffline.renderUsageStatus('albumOfflineStatus', {
                usageLabel: 'Dung lượng cache',
                clearLabel: 'Xóa dữ liệu Offline',
            });
        } catch (e) {
            console.error(e);
            btn.classList.remove('btn-outline-success');
            btn.classList.add('btn-outline-danger');
            btn.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i>Lỗi tải';
            showToast('Không thể tải offline album lúc này.', 'danger');
        } finally {
            btn.disabled = false;
        }
    }

    window.clearCacheApp = window.clearCacheApp || (async () => {
        if (confirm('Bạn có chắc muốn xóa toàn bộ dữ liệu offline đã lưu?')) {
            await window.BWMOffline.clearCache();
            location.reload();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('albumOfflineStatus') && window.BWMOffline) {
            window.BWMOffline.renderUsageStatus('albumOfflineStatus', {
                usageLabel: 'Dung lượng cache',
                clearLabel: 'Xóa dữ liệu Offline',
            });
        }
    });
</script>
@endpush
