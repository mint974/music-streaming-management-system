@extends('layouts.main')

@section('title', $q ? "Kết quả cho \"{$q}\" – Blue Wave Music" : 'Tìm kiếm – Blue Wave Music')

@section('content')
@php
    $hasQuery = $q !== '';
    $activeCount = $counts[$tab] ?? 0;
@endphp

<div class="search-page px-1">
    <x-sparkles :count="10" />

    @if(!$hasQuery)
    <div class="search-hero">
        <div class="search-hero-icon">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <h2>Tìm kiếm nghệ sĩ, bài hát và album</h2>
        <p>Khám phá hồ sơ nghệ sĩ công khai và toàn bộ kho nhạc đã xuất bản trên Blue Wave Music.</p>
    </div>
    @else
    <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
        <div>
            <h3 class="text-white fw-bold mb-1" style="font-size:1.25rem">
                Kết quả cho <span style="color:#c084fc">"{{ $q }}"</span>
            </h3>
            <p class="text-muted mb-0" style="font-size:.82rem">
                @if($totalResults > 0)
                    Tìm thấy {{ $totalResults }} kết quả công khai
                @else
                    Không tìm thấy kết quả nào
                @endif
            </p>
        </div>
        <span class="result-count ms-auto">{{ $activeCount }} đang hiển thị</span>
    </div>

    <div class="search-tab-wrap mb-4">
        <a href="{{ route('search', ['q' => $q, 'tab' => 'artists']) }}"
           class="search-tab-pill js-tab-switch {{ $tab === 'artists' ? 'active artists-tab' : '' }}">
            <i class="fa-solid fa-microphone-lines"></i>Nghệ sĩ
            <span class="badge rounded-pill bg-secondary" style="font-size:.68rem">{{ $counts['artists'] ?? 0 }}</span>
        </a>
        <a href="{{ route('search', ['q' => $q, 'tab' => 'songs']) }}"
           class="search-tab-pill js-tab-switch {{ $tab === 'songs' ? 'active songs-tab' : '' }}">
            <i class="fa-solid fa-music"></i>Nhạc
            <span class="badge rounded-pill bg-secondary" style="font-size:.68rem">{{ $counts['songs'] ?? 0 }}</span>
        </a>
        <a href="{{ route('search', ['q' => $q, 'tab' => 'albums']) }}"
           class="search-tab-pill js-tab-switch {{ $tab === 'albums' ? 'active albums-tab' : '' }}">
            <i class="fa-solid fa-compact-disc"></i>Album
            <span class="badge rounded-pill bg-secondary" style="font-size:.68rem">{{ $counts['albums'] ?? 0 }}</span>
        </a>
    </div>
    @endif

    <div id="searchTabContent" class="tab-content-body">

    @if(!$hasQuery)
    <div class="mb-5" id="historySection">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="search-section-title mb-0">
                <i class="fa-solid fa-clock-rotate-left"></i>Lịch sử tìm kiếm
            </div>
            @if(count($history) > 0)
                @auth
                <button class="btn btn-link p-0 text-muted" style="font-size:.75rem;text-decoration:none" id="clearHistoryBtn">
                    <i class="fa-solid fa-trash-can me-1"></i>Xóa tất cả
                </button>
                @endauth
            @endif
        </div>

        @if(count($history) > 0)
        <div class="history-list" id="historyList">
            @foreach($history as $hq)
            <div class="history-item" data-query="{{ $hq }}">
                <i class="fa-solid fa-clock-rotate-left history-icon"></i>
                <a href="{{ route('search', ['q' => $hq]) }}" class="history-label text-decoration-none" style="color:inherit">{{ $hq }}</a>
                @auth
                <button class="history-remove history-remove-btn" data-query="{{ $hq }}" title="Xóa">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                @endauth
            </div>
            @endforeach
        </div>
        @else
        <p class="text-muted" style="font-size:.83rem">
            @auth
                Chưa có lịch sử tìm kiếm nào.
            @else
                <i class="fa-solid fa-circle-info me-1"></i>Đăng nhập để lưu lịch sử tìm kiếm.
            @endauth
        </p>
        @endif
    </div>
    @endif

    @if($hasQuery && $tab === 'artists')
    <div class="mb-5">
        <div class="search-section-title">
            <i class="fa-solid fa-microphone-lines" style="color:#a855f7"></i>
            Nghệ sĩ công khai
        </div>

        @if($artists->isEmpty())
            <p class="text-muted" style="font-size:.85rem">Không tìm thấy nghệ sĩ nào khớp với "{{ $q }}".</p>
        @else
            <div class="artist-grid">
                @foreach($artists as $artist)
                @php
                    $aName = $artist->artist_name ?: $artist->name;
                    $aAvatar = $artist->getAvatarUrl();
                @endphp
                <a href="{{ route('search.artist.show', ['artistId' => $artist->id]) }}" class="artist-card-search">
                    <img src="{{ $aAvatar }}" alt="{{ $aName }}" class="acs-avatar">
                    <div class="acs-name">
                        {{ $aName }}
                        @if($artist->artist_verified_at)
                            <i class="fa-solid fa-circle-check acs-verified" title="Đã xác minh"></i>
                        @endif
                    </div>
                    <div class="acs-role">{{ \Illuminate\Support\Str::limit($artist->bio ?: 'Nghệ sĩ trên Blue Wave Music', 56) }}</div>
                    <span class="artist-card-link">Xem hồ sơ <i class="fa-solid fa-arrow-right ms-1"></i></span>
                </a>
                @endforeach
            </div>
        @endif
    </div>
    @endif

    @if($hasQuery && $tab === 'songs')
    <div class="mb-5">
        <div class="search-section-title">
            <i class="fa-solid fa-music" style="color:#c084fc"></i>
            Bài hát đã công khai
        </div>

        @if($songs->isEmpty())
            <p class="text-muted" style="font-size:.85rem">Không có bài hát công khai nào khớp với "{{ $q }}".</p>
        @else
            <div class="search-song-list">
                @foreach($songs as $song)
                @php
                    $artistName = $song->artist?->artist_name ?: $song->artist?->name ?: 'Nghệ sĩ';
                @endphp
                <div class="search-song-item">
                    <img src="{{ $song->getCoverUrl() }}" alt="{{ $song->title }}" class="ssi-cover">
                    <div class="ssi-main">
                        <div class="ssi-title">{{ $song->title }}</div>
                        <div class="ssi-meta">
                            <a href="{{ route('search.artist.show', ['artistId' => $song->artist?->id]) }}">{{ $artistName }}</a>
                            @if($song->album)
                                <span>• {{ $song->album->title }}</span>
                            @endif
                            <span>• {{ number_format($song->listens) }} lượt nghe</span>
                        </div>
                    </div>
                    <div class="ssi-actions">
                        <span class="ssi-duration">{{ $song->durationFormatted() }}</span>
                        <button
                            type="button"
                            class="ssi-play js-play-song"
                            data-song-id="{{ $song->id }}"
                            data-song-title="{{ e($song->title) }}"
                            data-song-artist="{{ e($artistName) }}"
                            data-song-cover="{{ $song->getCoverUrl() }}"
                            data-stream-url="{{ route('songs.stream', $song->id) }}">
                            <i class="fa-solid fa-play"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @if($songs && method_exists($songs, 'links'))
            <div class="d-flex justify-content-center mt-4">
                {{ $songs->appends(['q' => $q, 'tab' => 'songs'])->links('pagination::bootstrap-5') }}
            </div>
            @endif
        @endif
    </div>
    @endif

    @if($hasQuery && $tab === 'albums')
    <div class="mb-5">
        <div class="search-section-title">
            <i class="fa-solid fa-compact-disc" style="color:#60a5fa"></i>
            Album đã công khai
        </div>

        @if($albums->isEmpty())
            <p class="text-muted" style="font-size:.85rem">Không có album công khai nào khớp với "{{ $q }}".</p>
        @else
            <div class="search-album-grid">
                @foreach($albums as $album)
                @php
                    $artistName = $album->artist?->artist_name ?: $album->artist?->name ?: 'Nghệ sĩ';
                @endphp
                <div class="search-album-card">
                    <img src="{{ $album->getCoverUrl() }}" alt="{{ $album->title }}" class="sac-cover">
                    <div class="sac-body">
                        <div class="sac-title">{{ $album->title }}</div>
                        <a href="{{ route('search.artist.show', ['artistId' => $album->artist?->id]) }}" class="sac-artist">{{ $artistName }}</a>
                        <div class="sac-meta">
                            <span>{{ $album->released_date?->format('d/m/Y') ?? 'Chưa cập nhật' }}</span>
                            <span>• {{ $album->published_songs_count }} bài</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @if($albums && method_exists($albums, 'links'))
            <div class="d-flex justify-content-center mt-4">
                {{ $albums->appends(['q' => $q, 'tab' => 'albums'])->links('pagination::bootstrap-5') }}
            </div>
            @endif
        @endif
    </div>
    @endif

    @if($hasQuery && $totalResults === 0)
    <div class="search-no-results mt-5">
        <div class="snr-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
        <h4>Không tìm thấy kết quả</h4>
        <p>Thử tìm với từ khóa khác hoặc kiểm tra chính tả.</p>
    </div>
    @endif

    </div>

    <div id="searchTabSkeleton" class="search-tab-skeleton d-none" aria-hidden="true">
        <div class="sts-row"></div>
        <div class="sts-row"></div>
        <div class="sts-row short"></div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const tabContent = document.getElementById('searchTabContent');
    const tabSkeleton = document.getElementById('searchTabSkeleton');

    const showLoading = () => {
        if (!tabContent || !tabSkeleton) return;
        tabContent.classList.add('d-none');
        tabSkeleton.classList.remove('d-none');
    };

    const clearBtn = document.getElementById('clearHistoryBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', async function () {
            if (!confirm('Xóa toàn bộ lịch sử tìm kiếm?')) return;
            try {
                const res = await fetch('{{ route("search.history.clear") }}', {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                });
                if (res.ok) {
                    document.getElementById('historyList')?.remove();
                    clearBtn.closest('div')?.remove();
                }
            } catch (e) {
                console.error(e);
            }
        });
    }

    document.querySelectorAll('.history-remove-btn').forEach(btn => {
        btn.addEventListener('click', async function (e) {
            e.preventDefault();
            e.stopPropagation();
            const query = this.dataset.query;
            try {
                const res = await fetch('{{ route("search.history.remove") }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ query }),
                });
                if (res.ok) {
                    this.closest('.history-item')?.remove();
                }
            } catch (e) {
                console.error(e);
            }
        });
    });

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
