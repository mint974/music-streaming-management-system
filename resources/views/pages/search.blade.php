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



    <div class="modal fade" id="microListenModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 micro-listen-modal">
                <div class="modal-header border-0 border-bottom">
                    <h5 class="modal-title text-white fw-bold" id="microListenTitle">Đang nghe...</h5>
                    <button type="button" class="btn-close btn-close-white" id="microListenCloseBtn" aria-label="Đóng"></button>
                </div>
                <div class="modal-body text-center py-5">
                    <div class="micro-listen-icon-wrap mx-auto mb-4">
                        <i class="fa-solid fa-microphone"></i>
                    </div>
                    <div class="micro-listen-subtitle" id="microListenSubtitle">Hãy nói tên bài hát hoặc ca sĩ</div>
                </div>
                <div class="modal-footer border-0 justify-content-center gap-2">
                    <button type="button" class="btn btn-danger rounded-pill px-4" id="microListenStopBtn">
                        <i class="fa-solid fa-stop me-1"></i>Dừng
                    </button>
                    <button type="button" class="btn btn-success rounded-pill px-4" id="microListenFinishBtn" style="display:none;">
                        <i class="fa-solid fa-check me-1"></i>Kết thúc & Tìm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="searchTabContent" class="tab-content-body">

    @if(!$hasQuery)
    <div class="mb-5" id="historySection">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="search-section-title mb-0">
                <i class="fa-solid fa-clock-rotate-left"></i>Lịch sử tìm kiếm
            </div>
            @if(count($history) > 0)
                @auth
                <button class="btn btn-link p-0 text-muted" style="font-size:.75rem;text-decoration:none" id="clearHistoryBtn" data-confirm-message="Xóa toàn bộ lịch sử tìm kiếm?" data-confirm-title="Xóa lịch sử tìm kiếm">
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
                <div class="search-song-item js-search-song-card" data-song-url="{{ route('songs.show', $song->id) }}" role="link" tabindex="0">
                    <img src="{{ $song->getCoverUrl() }}" alt="{{ $song->title }}" class="ssi-cover">
                    <div class="ssi-main">
                        <div class="ssi-title">
                            <a href="{{ route('songs.show', $song->id) }}" style="color:inherit;text-decoration:none">{{ $song->title }}</a>
                            @if($song->is_vip)
                                <i class="fa-solid fa-crown text-warning ms-1" style="font-size: 0.8rem;" title="Premium"></i>
                            @endif
                        </div>
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
                            data-song-premium="{{ $song->is_vip ? '1' : '0' }}"
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

@push('styles')
<style>
.micro-listen-modal {
    background:
        radial-gradient(circle at top center, rgba(46, 104, 255, 0.18), transparent 58%),
        linear-gradient(145deg, rgba(7, 17, 44, 0.98), rgba(8, 12, 30, 0.98));
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 28px 60px rgba(0, 0, 0, 0.45);
    border-radius: 18px;
}

.micro-listen-modal .modal-header {
    border-bottom-color: rgba(255, 255, 255, 0.12) !important;
}

.micro-listen-modal .modal-footer {
    border-top: none !important;
    padding: 1.5rem 1rem !important;
    background: transparent;
    display: flex !important;
    justify-content: center !important;
    gap: 0.5rem !important;
}

.micro-listen-icon-wrap {
    width: 72px;
    height: 72px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(145deg, #ff5f5f, #f43f5e);
    color: #fff;
    font-size: 1.6rem;
    box-shadow: 0 12px 22px rgba(244, 63, 94, 0.36);
    animation: micPulse 1.3s ease-in-out infinite;
}

.micro-listen-subtitle {
    color: rgba(255, 255, 255, 0.75);
    font-size: 0.84rem;
    text-align: center;
    min-height: 18px;
}

@keyframes micPulse {
    0%,
    100% { transform: scale(1); box-shadow: 0 12px 22px rgba(244, 63, 94, 0.36); }
    50% { transform: scale(1.08); box-shadow: 0 15px 30px rgba(244, 63, 94, 0.5); }
}

.js-search-song-card {
    cursor: pointer;
}
</style>
@endpush

@push('scripts')
<script>
function initSearchPageSearch() {
    const pageRoot = document.querySelector('.search-page');
    if (!pageRoot) return;

    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const tabContent = document.getElementById('searchTabContent');
    const tabSkeleton = document.getElementById('searchTabSkeleton');
    
    // Voice search modal elements
    const microListenModalEl = document.getElementById('microListenModal');
    const microListenTitle = document.getElementById('microListenTitle');
    const microListenSubtitle = document.getElementById('microListenSubtitle');
    const microListenStopBtn = document.getElementById('microListenStopBtn');
    const microListenFinishBtn = document.getElementById('microListenFinishBtn');
    const microListenCloseBtn = document.getElementById('microListenCloseBtn');
    
    const microListenModal = microListenModalEl && window.bootstrap ? bootstrap.Modal.getOrCreateInstance(microListenModalEl) : null;

    let voiceRecognition = null;
    let voiceTranscript = '';

    const showLoading = () => {
        if (!tabContent || !tabSkeleton) return;
        tabContent.classList.add('d-none');
        tabSkeleton.classList.remove('d-none');
    };

    const showMicOverlay = (subtitle = '') => {
        if (microListenTitle) {
            microListenTitle.textContent = 'Đang nghe...';
        }
        if (microListenSubtitle) {
            microListenSubtitle.textContent = subtitle || 'Hãy nói tên bài hát hoặc ca sĩ';
        }

        if (microListenModal) {
            microListenModal.show();
        }
    };

    const hideMicOverlay = () => {
        if (microListenModal) {
            microListenModal.hide();
        }
    };

    const getSpeechRecognitionCtor = () => {
        return window.SpeechRecognition || window.webkitSpeechRecognition || null;
    };

    const stopVoiceRecognition = () => {
        if (voiceRecognition) {
            try { voiceRecognition.stop(); } catch (e) {}
        }
    };

    const setVoiceTranscript = (text) => {
        voiceTranscript = text || '';
        if (microListenSubtitle) {
            microListenSubtitle.textContent = voiceTranscript || 'Chưa nhận được giọng nói nào.';
        }
        if (microListenFinishBtn) {
            microListenFinishBtn.style.display = voiceTranscript ? 'block' : 'none';
        }
    };

    const runVoiceSearch = async () => {
        const query = (voiceTranscript || '').trim();
        if (!query) {
            alert('Chưa có nội dung giọng nói để tìm kiếm.');
            return;
        }

        try {
            hideMicOverlay();
            const response = await fetch('{{ route('search.voice') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ transcript: query }),
            });

            const data = await response.json();
            if (!response.ok || !data.ok || !data.redirect_url) {
                throw new Error(data.message || 'Không thể xử lý tìm kiếm giọng nói.');
            }

            window.location.href = data.redirect_url;
        } catch (error) {
            console.error(error);
            alert(error.message || 'Lỗi tìm kiếm giọng nói.');
        }
    };

    const bindSearchSongCardNavigation = () => {
        document.querySelectorAll('.js-search-song-card').forEach((card) => {
            if (card.dataset.navBound === '1') {
                return;
            }
            card.dataset.navBound = '1';

            const navigateToSongDetail = () => {
                const targetUrl = card.dataset.songUrl;
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            };

            card.addEventListener('click', (event) => {
                const target = event.target;
                if (target.closest('a, button, .js-play-song, form')) {
                    return;
                }
                navigateToSongDetail();
            });

            card.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    navigateToSongDetail();
                }
            });
        });
    };

    bindSearchSongCardNavigation();

    // Voice search modal button handlers
    if (microListenStopBtn && microListenStopBtn.dataset.bound !== '1') {
        microListenStopBtn.dataset.bound = '1';
        microListenStopBtn.addEventListener('click', () => {
            stopVoiceRecognition();
        });
    }

    if (microListenFinishBtn && microListenFinishBtn.dataset.bound !== '1') {
        microListenFinishBtn.dataset.bound = '1';
        microListenFinishBtn.addEventListener('click', () => {
            stopVoiceRecognition();
            runVoiceSearch();
        });
    }

    if (microListenCloseBtn && microListenCloseBtn.dataset.bound !== '1') {
        microListenCloseBtn.dataset.bound = '1';
        microListenCloseBtn.addEventListener('click', () => {
            stopVoiceRecognition();
            hideMicOverlay();
        });
    }

    // Expose voice search trigger for header button
    window.startVoiceSearchFromHeader = function() {
        const SpeechRecognition = getSpeechRecognitionCtor();
        if (!SpeechRecognition) {
            alert('Trình duyệt không hỗ trợ nhận diện giọng nói.');
            return;
        }

        stopVoiceRecognition();
        voiceRecognition = new SpeechRecognition();
        voiceRecognition.lang = 'vi-VN';
        voiceRecognition.interimResults = true;
        voiceRecognition.continuous = false;

        voiceRecognition.onstart = () => {
            setVoiceTranscript('');
            showMicOverlay();
        };

        voiceRecognition.onresult = (event) => {
            const transcript = Array.from(event.results)
                .map((result) => result[0]?.transcript || '')
                .join(' ')
                .trim();
            if (transcript) {
                setVoiceTranscript(transcript);
            }
        };

        voiceRecognition.onerror = (event) => {
            setVoiceTranscript('');
            alert(`Không nhận diện được giọng nói: ${event.error}`);
            hideMicOverlay();
        };

        voiceRecognition.onend = () => {
            hideMicOverlay();
        };

        try {
            voiceRecognition.start();
        } catch (error) {
            alert('Không thể bắt đầu nhận diện giọng nói.');
            hideMicOverlay();
        }
    };

    const clearBtn = document.getElementById('clearHistoryBtn');
    if (clearBtn && clearBtn.dataset.bound !== '1') {
        clearBtn.dataset.bound = '1';
        clearBtn.addEventListener('click', async function () {
            if (typeof window.showConfirmModal === 'function') {
                const accepted = await window.showConfirmModal('Xóa toàn bộ lịch sử tìm kiếm?', {
                    title: 'Xóa lịch sử tìm kiếm',
                });
                if (!accepted) return;
            }
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
        if (btn.dataset.bound === '1') return;
        btn.dataset.bound = '1';
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
        if (tabLink.dataset.bound === '1') return;
        tabLink.dataset.bound = '1';
        tabLink.addEventListener('click', function () {
            showLoading();
        });
    });

    document.querySelectorAll('.pagination a').forEach((pageLink) => {
        if (pageLink.dataset.bound === '1') return;
        pageLink.dataset.bound = '1';
        pageLink.addEventListener('click', function () {
            showLoading();
        });
    });
}

document.addEventListener('DOMContentLoaded', initSearchPageSearch);

if (window.htmx) {
    document.body.addEventListener('htmx:load', initSearchPageSearch);
    document.body.addEventListener('htmx:afterSwap', initSearchPageSearch);
}
</script>
@endpush
