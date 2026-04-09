@extends('layouts.main')

@section('title', $q ? "Kết quả cho \"{$q}\" – Blue Wave Music" : 'Tìm kiếm – Blue Wave Music')

@section('content')
@php
    $hasQuery = $q !== '';
    $activeCount = $counts[$tab] ?? 0;
    $canUseHumming = auth()->check() && auth()->user()?->isPremium();
@endphp

<div class="search-page px-1" data-can-use-humming="{{ $canUseHumming ? '1' : '0' }}">
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

    <div class="humming-search-card mb-4">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
            <div>
                <div class="search-section-title mb-1">
                    <i class="fa-solid fa-microphone-lines"></i>Tìm bài hát bằng âm thanh
                </div>
                <p class="text-muted mb-0" style="font-size:.86rem;max-width:780px">
                    Bấm nút micro để chọn giữa tìm bằng giọng nói hoặc tìm bằng ngân nga. Với ngân nga, bạn có thể ghi âm trực tiếp hoặc up file audio.
                </p>
                @if(!$canUseHumming)
                    <p class="text-warning mb-0 mt-2" style="font-size:.82rem">
                        <i class="fa-solid fa-crown me-1"></i>Chỉ tài khoản Premium mới dùng được tính năng tìm kiếm ngân nga.
                    </p>
                @endif
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge rounded-pill text-bg-dark">Voice Search</span>
                <span class="badge rounded-pill text-bg-dark">Humming Search</span>
            </div>
        </div>

        <div class="mt-3 position-relative">
            <button type="button" class="btn btn-primary rounded-pill" id="microModeBtn" data-bs-toggle="modal" data-bs-target="#microModeModal">
                <i class="fa-solid fa-microphone me-1"></i>Micro
            </button>
        </div>

        <div id="voiceSearchPanel" class="micro-search-panel d-none mt-3">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-2">
                <div>
                    <div class="panel-title"><i class="fa-solid fa-comment-dots me-2"></i>Tìm kiếm giọng nói</div>
                    <div class="text-muted" style="font-size:.84rem">Nói tên bài hát, ca sĩ hoặc từ khóa rồi hệ thống sẽ chuyển sang tìm kiếm thường.</div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-info btn-sm" id="startVoiceSearchBtn"><i class="fa-solid fa-circle-dot me-1"></i>Bắt đầu nói</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="stopVoiceSearchBtn" disabled><i class="fa-solid fa-stop me-1"></i>Dừng</button>
                </div>
            </div>
            <div class="micro-search-feedback" id="voiceSearchText">Chưa nhận được giọng nói nào.</div>
            <div class="mt-3 d-flex gap-2">
                <button type="button" class="btn btn-primary btn-sm" id="submitVoiceSearchBtn" disabled>Tìm ngay</button>
                <button type="button" class="btn btn-link text-muted btn-sm" id="clearVoiceSearchBtn">Xóa</button>
            </div>
        </div>

        <form id="hummingSearchForm" class="micro-search-panel d-none mt-3">
            <input type="file" id="hummingAudioInput" accept=".mp3,.wav,.webm,.ogg,.m4a,audio/*" hidden>
            <input type="hidden" id="hummingTopK" value="5">

            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-2">
                <div>
                    <div class="panel-title"><i class="fa-solid fa-wave-square me-2"></i>Tìm kiếm ngân nga</div>
                    <div class="text-muted" style="font-size:.84rem">Ghi âm trực tiếp hoặc up file audio để tìm bài hát tương tự.</div>
                </div>
                <span class="badge rounded-pill text-bg-dark">4-6 khung / bài</span>
            </div>

            <div class="d-flex flex-wrap gap-2 align-items-center humming-controls">
                <button type="button" class="btn btn-outline-light" id="chooseHummingFileBtn" {{ $canUseHumming ? '' : 'disabled' }}>
                    <i class="fa-solid fa-folder-open me-1"></i>Up file
                </button>
                <button type="button" class="btn btn-outline-info" id="startHummingRecordBtn" {{ $canUseHumming ? '' : 'disabled' }}>
                    <i class="fa-solid fa-circle-dot me-1"></i>Ghi âm
                </button>
                <button type="button" class="btn btn-outline-warning" id="stopHummingRecordBtn" disabled>
                    <i class="fa-solid fa-stop me-1"></i>Dừng ghi
                </button>
                <button type="submit" class="btn btn-primary" id="searchHummingSubmitBtn" {{ $canUseHumming ? '' : 'disabled' }}>
                    <i class="fa-solid fa-magnifying-glass me-1"></i>Tìm ngay
                </button>
                <button type="button" class="btn btn-link text-muted px-0" id="clearHummingBtn">Xóa</button>
            </div>

            <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
                <span class="text-muted" id="hummingFileLabel">Chưa có file âm thanh được chọn.</span>
                <span class="text-muted" id="hummingStatus"></span>
            </div>

            <audio id="hummingPreview" controls class="w-100 mt-3 d-none humming-preview"></audio>
        </form>

        <div id="hummingResults" class="mt-4"></div>
    </div>

    <div class="modal fade" id="microModeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 micro-mode-modal">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-white fw-bold">Chọn chế độ micro</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <button type="button" class="micro-mode-option w-100 mb-2" data-mode="voice" data-bs-dismiss="modal">
                        <i class="fa-solid fa-comment-dots"></i>
                        <span>
                            <strong>Tìm kiếm giọng nói</strong>
                            <small>Nói từ khóa để tìm bài hát</small>
                        </span>
                    </button>
                    <button type="button" class="micro-mode-option w-100 {{ $canUseHumming ? '' : 'disabled locked' }}" data-mode="humming" {{ $canUseHumming ? '' : 'disabled' }} data-bs-dismiss="modal">
                        <i class="fa-solid fa-wave-square"></i>
                        <span>
                            <strong>Tìm kiếm ngân nga</strong>
                            <small>{{ $canUseHumming ? 'Ghi âm hoặc upload file ngân nga' : 'Yêu cầu tài khoản Premium' }}</small>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="microListenModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 micro-listen-modal">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-white fw-bold" id="microListenTitle">Đang nghe...</h5>
                    <button type="button" class="btn-close btn-close-white" id="microListenCloseBtn" aria-label="Đóng"></button>
                </div>
                <div class="modal-body text-center pt-1 pb-4">
                    <div class="micro-listen-icon-wrap mx-auto mb-3">
                        <i class="fa-solid fa-microphone"></i>
                    </div>
                    <div class="micro-listen-subtitle" id="microListenSubtitle">Hãy nói tên bài hát hoặc ca sĩ</div>
                    <button type="button" class="btn btn-danger rounded-pill mt-3 px-4" id="microListenStopBtn">
                        <i class="fa-solid fa-stop me-1"></i>Dừng
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="hummingBlockingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 micro-blocking-modal text-center">
                <div class="modal-body py-4">
                    <div class="spinner-border text-light" role="status" style="width:3rem;height:3rem">
                        <span class="visually-hidden">Đang xử lý...</span>
                    </div>
                    <div class="mt-3 text-white fw-semibold">AI đang phân tích ngân nga...</div>
                    <div class="text-white-50" style="font-size:.86rem">Vui lòng chờ trong giây lát</div>
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
.humming-search-card {
    padding: 1.1rem 1.15rem;
    border-radius: 18px;
    background:
        linear-gradient(145deg, rgba(10, 12, 20, 0.96), rgba(22, 24, 38, 0.92)),
        radial-gradient(circle at top right, rgba(168, 85, 247, 0.22), transparent 36%),
        radial-gradient(circle at bottom left, rgba(96, 165, 250, 0.18), transparent 34%);
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 20px 45px rgba(0, 0, 0, 0.22);
}

.micro-mode-modal,
.micro-listen-modal,
.micro-blocking-modal {
    background:
        radial-gradient(circle at top center, rgba(46, 104, 255, 0.18), transparent 58%),
        linear-gradient(145deg, rgba(7, 17, 44, 0.98), rgba(8, 12, 30, 0.98));
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 28px 60px rgba(0, 0, 0, 0.45);
    border-radius: 18px;
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

.micro-mode-option {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.85rem;
    border: 0;
    border-radius: 14px;
    background: transparent;
    color: #fff;
    text-align: left;
    transition: background 0.16s ease, transform 0.16s ease;
}

.micro-mode-option:hover {
    background: rgba(255, 255, 255, 0.06);
    transform: translateY(-1px);
}

.micro-mode-option.disabled,
.micro-mode-option:disabled,
.micro-mode-option.locked {
    opacity: 0.58;
    cursor: not-allowed;
    pointer-events: none;
}

.micro-mode-option i {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(192, 132, 252, 0.16);
    color: #d8b4fe;
    flex-shrink: 0;
}

.micro-mode-option span {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.micro-mode-option small {
    color: rgba(255, 255, 255, 0.62);
}

.micro-search-panel {
    padding: 1rem;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.panel-title {
    color: #fff;
    font-weight: 700;
    font-size: 0.96rem;
}

.micro-search-feedback {
    min-height: 48px;
    padding: 0.8rem 0.9rem;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px dashed rgba(255, 255, 255, 0.14);
    color: rgba(255, 255, 255, 0.82);
}

.humming-controls .btn {
    border-radius: 999px;
}

.humming-preview {
    border-radius: 12px;
}

.humming-result-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 0.85rem;
}

.humming-result-card {
    display: flex;
    gap: 0.85rem;
    padding: 0.85rem;
    border-radius: 16px;
    text-decoration: none;
    color: inherit;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease;
}

.humming-result-card:hover {
    transform: translateY(-2px);
    border-color: rgba(168, 85, 247, 0.32);
    background: rgba(255, 255, 255, 0.05);
}

.humming-result-cover {
    width: 70px;
    height: 70px;
    border-radius: 14px;
    object-fit: cover;
    flex-shrink: 0;
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.25);
}

.humming-result-title {
    font-weight: 700;
    color: #fff;
    line-height: 1.25;
    margin-bottom: 0.25rem;
}

.humming-result-meta,
.humming-result-stats {
    font-size: 0.78rem;
    color: rgba(255, 255, 255, 0.68);
}

.humming-result-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem 0.6rem;
    margin-top: 0.4rem;
}

.js-search-song-card {
    cursor: pointer;
}

.humming-empty-state {
    padding: 1rem 1.1rem;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px dashed rgba(255, 255, 255, 0.12);
    color: rgba(255, 255, 255, 0.75);
}

.humming-confidence-badge {
    margin-top: 0.45rem;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.55rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #f59e0b, #f97316);
    color: #fff;
    font-weight: 700;
    font-size: 0.75rem;
    letter-spacing: 0.1px;
}

</style>
@endpush

@push('scripts')
<script>
function initSearchPageAudioSearch() {
    const pageRoot = document.querySelector('.search-page');
    if (!pageRoot) return;
    const canUseHumming = pageRoot.dataset.canUseHumming === '1';

    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const tabContent = document.getElementById('searchTabContent');
    const tabSkeleton = document.getElementById('searchTabSkeleton');
    const microModeBtn = document.getElementById('microModeBtn');
    const voiceSearchPanel = document.getElementById('voiceSearchPanel');
    const hummingForm = document.getElementById('hummingSearchForm');
    const hummingInput = document.getElementById('hummingAudioInput');
    const chooseHummingFileBtn = document.getElementById('chooseHummingFileBtn');
    const startHummingRecordBtn = document.getElementById('startHummingRecordBtn');
    const stopHummingRecordBtn = document.getElementById('stopHummingRecordBtn');
    const clearHummingBtn = document.getElementById('clearHummingBtn');
    const searchHummingSubmitBtn = document.getElementById('searchHummingSubmitBtn');
    const hummingStatus = document.getElementById('hummingStatus');
    const hummingFileLabel = document.getElementById('hummingFileLabel');
    const hummingPreview = document.getElementById('hummingPreview');
    const hummingResults = document.getElementById('hummingResults');
    const hummingTopK = document.getElementById('hummingTopK');
    const startVoiceSearchBtn = document.getElementById('startVoiceSearchBtn');
    const stopVoiceSearchBtn = document.getElementById('stopVoiceSearchBtn');
    const submitVoiceSearchBtn = document.getElementById('submitVoiceSearchBtn');
    const clearVoiceSearchBtn = document.getElementById('clearVoiceSearchBtn');
    const voiceSearchText = document.getElementById('voiceSearchText');
    const microListenTitle = document.getElementById('microListenTitle');
    const microListenSubtitle = document.getElementById('microListenSubtitle');
    const microListenStopBtn = document.getElementById('microListenStopBtn');
    const microListenCloseBtn = document.getElementById('microListenCloseBtn');
    const microModeModalEl = document.getElementById('microModeModal');
    const microListenModalEl = document.getElementById('microListenModal');
    const hummingBlockingModalEl = document.getElementById('hummingBlockingModal');

    const microModeModal = microModeModalEl && window.bootstrap ? bootstrap.Modal.getOrCreateInstance(microModeModalEl) : null;
    const microListenModal = microListenModalEl && window.bootstrap ? bootstrap.Modal.getOrCreateInstance(microListenModalEl) : null;
    const hummingBlockingModal = hummingBlockingModalEl && window.bootstrap ? bootstrap.Modal.getOrCreateInstance(hummingBlockingModalEl) : null;

    // Re-bind when HTMX swaps panel/form nodes. If unchanged, skip to avoid duplicate listeners.
    if (pageRoot.__audioSearchBoundForm === hummingForm) {
        return;
    }
    pageRoot.__audioSearchBoundForm = hummingForm;

    let recorder = null;
    let recorderStream = null;
    let recorderChunks = [];
    let recordedBlob = null;
    let recordedObjectUrl = null;
    let voiceRecognition = null;
    let voiceTranscript = '';
    let activeMicMode = null;

    const showLoading = () => {
        if (!tabContent || !tabSkeleton) return;
        tabContent.classList.add('d-none');
        tabSkeleton.classList.remove('d-none');
    };

    const hideAllMicroPanels = () => {
        if (voiceSearchPanel) voiceSearchPanel.classList.add('d-none');
        if (hummingForm) hummingForm.classList.add('d-none');
    };

    const showMicOverlay = (mode, subtitle = '') => {
        activeMicMode = mode;
        if (microListenTitle) {
            microListenTitle.textContent = mode === 'humming' ? 'Đang ghi âm...' : 'Đang nghe...';
        }
        if (microListenSubtitle) {
            microListenSubtitle.textContent = subtitle || (mode === 'humming'
                ? 'Hãy ngân nga đoạn nhạc bạn muốn tìm'
                : 'Hãy nói tên bài hát hoặc ca sĩ');
        }

        if (microListenModal) {
            microListenModal.show();
        }
    };

    const hideMicOverlay = () => {
        activeMicMode = null;
        if (microListenModal) {
            microListenModal.hide();
        }
    };

    const setHummingStatus = (message, kind = 'muted') => {
        if (!hummingStatus) return;
        const color = kind === 'success'
            ? 'text-success'
            : kind === 'danger'
                ? 'text-danger'
                : kind === 'warning'
                    ? 'text-warning'
                    : kind === 'info'
                        ? 'text-info'
                        : 'text-muted';
        hummingStatus.className = color;
        hummingStatus.textContent = message;
    };

    const setHummingBlocking = (isBlocking) => {
        if (!hummingBlockingModal) return;
        if (isBlocking) {
            hummingBlockingModal.show();
        } else {
            hummingBlockingModal.hide();
        }
    };

    const waitForPaint = () => new Promise((resolve) => {
        requestAnimationFrame(() => requestAnimationFrame(resolve));
    });

    const ensureHummingResultsContainer = () => {
        let container = document.getElementById('hummingResults');
        if (container) return container;
        if (!hummingForm) return null;

        container = document.createElement('div');
        container.id = 'hummingResults';
        container.className = 'mt-4';
        hummingForm.insertAdjacentElement('afterend', container);
        return container;
    };

    const escapeHtml = (value) => {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
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

    const resetRecordedPreview = () => {
        if (recordedObjectUrl) {
            URL.revokeObjectURL(recordedObjectUrl);
            recordedObjectUrl = null;
        }

        recordedBlob = null;
        if (hummingPreview) {
            hummingPreview.src = '';
            hummingPreview.classList.add('d-none');
        }
    };

    const clearHummingState = () => {
        if (hummingInput) hummingInput.value = '';
        resetRecordedPreview();
        if (hummingFileLabel) hummingFileLabel.textContent = 'Chưa có file âm thanh được chọn.';
        setHummingStatus('');
        if (hummingResults) hummingResults.innerHTML = '';
        if (startHummingRecordBtn) startHummingRecordBtn.disabled = false;
        if (stopHummingRecordBtn) stopHummingRecordBtn.disabled = true;
        if (searchHummingSubmitBtn) searchHummingSubmitBtn.disabled = false;
    };

    const getSpeechRecognitionCtor = () => {
        return window.SpeechRecognition || window.webkitSpeechRecognition || null;
    };

    const stopVoiceRecognition = () => {
        if (voiceRecognition) {
            try { voiceRecognition.stop(); } catch (e) {}
        }
    };

    const setVoiceTranscript = (text, status = '') => {
        voiceTranscript = text || '';
        if (voiceSearchText) {
            voiceSearchText.textContent = voiceTranscript || 'Chưa nhận được giọng nói nào.';
        }
        if (submitVoiceSearchBtn) {
            submitVoiceSearchBtn.disabled = !voiceTranscript;
        }
        if (status) {
            setHummingStatus(status, 'success');
        }
    };

    const runVoiceSearch = async () => {
        const query = (voiceTranscript || '').trim();
        if (!query) {
            setHummingStatus('Chưa có nội dung giọng nói để tìm kiếm.', 'warning');
            return;
        }

        try {
            setHummingStatus('Đang xử lý giọng nói...', 'warning');
            if (submitVoiceSearchBtn) submitVoiceSearchBtn.disabled = true;

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

            setHummingStatus(`Đã nhận: "${query}". Đang chuyển trang...`, 'success');
            window.location.href = data.redirect_url;
        } catch (error) {
            console.error(error);
            setHummingStatus(error.message || 'Lỗi tìm kiếm giọng nói.', 'danger');
        } finally {
            if (submitVoiceSearchBtn) submitVoiceSearchBtn.disabled = !voiceTranscript;
        }
    };

    const renderHummingResults = (matches) => {
        const container = ensureHummingResultsContainer();
        if (!container) return;

        if (!matches || matches.length === 0) {
            container.innerHTML = `
                <div class="humming-empty-state">
                    <i class="fa-solid fa-circle-info me-2"></i>Không tìm thấy bài hát phù hợp.
                </div>
            `;
            return;
        }

        container.innerHTML = `
            <div class="search-song-list">
                ${matches.map((item) => `
                    <div class="search-song-item js-search-song-card" data-song-url="${escapeHtml(item.song_url || '#')}" role="link" tabindex="0">
                        <img src="${escapeHtml(item.cover_url || '')}" alt="${escapeHtml(item.title || item.file_name || 'Song')}" class="ssi-cover">
                        <div class="ssi-main">
                            <div class="ssi-title">
                                <a href="${escapeHtml(item.song_url || '#')}" style="color:inherit;text-decoration:none">
                                    ${escapeHtml(item.title || item.file_name || 'Không rõ tên')}
                                </a>
                                ${item.is_vip ? '<i class="fa-solid fa-crown text-warning ms-1" style="font-size:0.8rem" title="Premium"></i>' : ''}
                            </div>
                            <div class="ssi-meta">
                                <span>${escapeHtml(item.artist_name || 'Nghệ sĩ')}</span>
                                ${item.album_title ? `<span>• ${escapeHtml(item.album_title)}</span>` : ''}
                                <span>• ${Number(item.listens ?? 0).toLocaleString('vi-VN')} lượt nghe</span>
                            </div>
                            <div class="ssi-meta">
                                <span>Score: ${Number(item.score ?? 0).toFixed(4)}</span>
                            </div>
                        </div>
                        <div class="ssi-actions text-end" style="min-width:130px">
                            <span class="ssi-duration">${escapeHtml(item.duration_formatted || '--:--')}</span>
                            <div class="humming-confidence-badge">
                                <i class="fa-solid fa-bullseye"></i>
                                <span>Confidence ${Number(item.confidence ?? 0).toFixed(1)}%</span>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        bindSearchSongCardNavigation();

        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    const pickRecorderMimeType = () => {
        const options = [
            'audio/webm;codecs=opus',
            'audio/webm',
            'audio/ogg;codecs=opus',
            'audio/mp4',
        ];

        for (const option of options) {
            if (window.MediaRecorder && MediaRecorder.isTypeSupported(option)) {
                return option;
            }
        }

        return '';
    };

    document.querySelectorAll('.micro-mode-option').forEach((button) => {
        button.addEventListener('click', () => {
            const mode = button.dataset.mode;
            hideAllMicroPanels();

            if (mode === 'voice') {
                voiceSearchPanel?.classList.remove('d-none');
                setVoiceTranscript(voiceTranscript || 'Chưa nhận được giọng nói nào.');
                setHummingStatus('Đang ở chế độ tìm kiếm giọng nói.', 'info');
                startVoiceSearchBtn?.click();
            }

            if (mode === 'humming') {
                if (!canUseHumming) {
                    setHummingStatus('Tính năng ngân nga chỉ dành cho tài khoản Premium.', 'warning');
                    return;
                }
                hummingForm?.classList.remove('d-none');
                setHummingStatus('Đang ở chế độ tìm kiếm ngân nga.', 'info');
            }
        });
    });

    if (startVoiceSearchBtn) {
        startVoiceSearchBtn.addEventListener('click', () => {
            const SpeechRecognition = getSpeechRecognitionCtor();
            if (!SpeechRecognition) {
                setHummingStatus('Trình duyệt không hỗ trợ nhận diện giọng nói.', 'danger');
                return;
            }

            stopVoiceRecognition();
            voiceRecognition = new SpeechRecognition();
            voiceRecognition.lang = 'vi-VN';
            voiceRecognition.interimResults = true;
            voiceRecognition.continuous = false;

            voiceRecognition.onstart = () => {
                setVoiceTranscript('', '');
                if (voiceSearchText) {
                    voiceSearchText.textContent = 'Đang nghe... hãy nói tên bài hát hoặc ca sĩ.';
                }
                showMicOverlay('voice');
                if (startVoiceSearchBtn) startVoiceSearchBtn.disabled = true;
                if (stopVoiceSearchBtn) stopVoiceSearchBtn.disabled = false;
                if (microModeModal) microModeModal.hide();
            };

            voiceRecognition.onresult = (event) => {
                const transcript = Array.from(event.results)
                    .map((result) => result[0]?.transcript || '')
                    .join(' ')
                    .trim();
                if (transcript) {
                    setVoiceTranscript(transcript);
                    if (microListenSubtitle) {
                        microListenSubtitle.textContent = transcript;
                    }
                }
            };

            voiceRecognition.onerror = (event) => {
                setVoiceTranscript('', '');
                setHummingStatus(`Không nhận diện được giọng nói: ${event.error}`, 'danger');
                hideMicOverlay();
                if (startVoiceSearchBtn) startVoiceSearchBtn.disabled = false;
                if (stopVoiceSearchBtn) stopVoiceSearchBtn.disabled = true;
            };

            voiceRecognition.onend = () => {
                hideMicOverlay();
                if (startVoiceSearchBtn) startVoiceSearchBtn.disabled = false;
                if (stopVoiceSearchBtn) stopVoiceSearchBtn.disabled = true;
            };

            try {
                voiceRecognition.start();
            } catch (error) {
                setHummingStatus('Không thể bắt đầu nhận diện giọng nói.', 'danger');
                hideMicOverlay();
                if (startVoiceSearchBtn) startVoiceSearchBtn.disabled = false;
                if (stopVoiceSearchBtn) stopVoiceSearchBtn.disabled = true;
            }
        });
    }

    if (stopVoiceSearchBtn) {
        stopVoiceSearchBtn.addEventListener('click', () => {
            stopVoiceRecognition();
        });
    }

    if (clearVoiceSearchBtn) {
        clearVoiceSearchBtn.addEventListener('click', () => {
            voiceTranscript = '';
            if (voiceSearchText) {
                voiceSearchText.textContent = 'Chưa nhận được giọng nói nào.';
            }
            if (submitVoiceSearchBtn) submitVoiceSearchBtn.disabled = true;
            setHummingStatus('', '');
        });
    }

    if (submitVoiceSearchBtn) {
        submitVoiceSearchBtn.addEventListener('click', runVoiceSearch);
    }

    const stopRecorderStream = () => {
        if (recorderStream) {
            recorderStream.getTracks().forEach((track) => track.stop());
            recorderStream = null;
        }
    };

    if (chooseHummingFileBtn && hummingInput) {
        chooseHummingFileBtn.addEventListener('click', () => hummingInput.click());
    }

    if (hummingInput) {
        hummingInput.addEventListener('change', () => {
            const file = hummingInput.files?.[0];
            if (file) {
                resetRecordedPreview();
                if (hummingFileLabel) hummingFileLabel.textContent = `Đã chọn: ${file.name}`;
                setHummingStatus('Sẵn sàng tìm kiếm từ file đã chọn.', 'success');
            }
        });
    }

    if (startHummingRecordBtn && stopHummingRecordBtn) {
        startHummingRecordBtn.addEventListener('click', async () => {
            if (!canUseHumming) {
                setHummingStatus('Tính năng ngân nga chỉ dành cho tài khoản Premium.', 'warning');
                return;
            }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                setHummingStatus('Trình duyệt không hỗ trợ ghi âm.', 'danger');
                return;
            }

            try {
                clearHummingState();
                setHummingStatus('Đang xin quyền micro...', 'warning');

                recorderStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                recorderChunks = [];
                const mimeType = pickRecorderMimeType();
                recorder = mimeType ? new MediaRecorder(recorderStream, { mimeType }) : new MediaRecorder(recorderStream);

                recorder.ondataavailable = (event) => {
                    if (event.data && event.data.size > 0) {
                        recorderChunks.push(event.data);
                    }
                };

                recorder.onstop = () => {
                    const mimeTypeUsed = recorder.mimeType || 'audio/webm';
                    const extension = mimeTypeUsed.includes('ogg') ? 'ogg' : mimeTypeUsed.includes('mp4') ? 'mp4' : 'webm';
                    recordedBlob = new Blob(recorderChunks, { type: mimeTypeUsed });
                    recordedObjectUrl = URL.createObjectURL(recordedBlob);

                    if (hummingPreview) {
                        hummingPreview.src = recordedObjectUrl;
                        hummingPreview.classList.remove('d-none');
                    }

                    if (hummingFileLabel) {
                        hummingFileLabel.textContent = `Đã ghi âm: humming.${extension}`;
                    }

                    setHummingStatus('Đã ghi âm xong, có thể tìm kiếm ngay.', 'success');
                    hideMicOverlay();
                    stopRecorderStream();
                    if (startHummingRecordBtn) startHummingRecordBtn.disabled = false;
                    if (stopHummingRecordBtn) stopHummingRecordBtn.disabled = true;
                };

                recorder.start();
                setHummingStatus('Đang ghi âm... hãy ngân nga đoạn nhạc.', 'success');
                showMicOverlay('humming');
                if (microModeModal) microModeModal.hide();
                startHummingRecordBtn.disabled = true;
                stopHummingRecordBtn.disabled = false;
            } catch (error) {
                console.error(error);
                stopRecorderStream();
                hideMicOverlay();
                setHummingStatus('Không thể bắt đầu ghi âm.', 'danger');
            }
        });
    }

    if (stopHummingRecordBtn) {
        stopHummingRecordBtn.addEventListener('click', () => {
            if (recorder && recorder.state !== 'inactive') {
                recorder.stop();
                setHummingStatus('Đang xử lý bản ghi...', 'warning');
            }
        });
    }

    if (microListenStopBtn) {
        microListenStopBtn.addEventListener('click', () => {
            if (activeMicMode === 'voice') {
                stopVoiceRecognition();
            }

            if (activeMicMode === 'humming' && recorder && recorder.state !== 'inactive') {
                recorder.stop();
                setHummingStatus('Đang xử lý bản ghi...', 'warning');
            }
        });
    }

    if (microListenCloseBtn) {
        microListenCloseBtn.addEventListener('click', () => {
            if (activeMicMode === 'voice') {
                stopVoiceRecognition();
            }

            if (activeMicMode === 'humming' && recorder && recorder.state !== 'inactive') {
                recorder.stop();
            }

            hideMicOverlay();
        });
    }

    if (clearHummingBtn) {
        clearHummingBtn.addEventListener('click', (event) => {
            event.preventDefault();
            if (recorder && recorder.state !== 'inactive') {
                recorder.stop();
            }
            stopRecorderStream();
            hideMicOverlay();
            clearHummingState();
        });
    }

    if (hummingForm) {
        hummingForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!canUseHumming) {
                setHummingStatus('Tính năng ngân nga chỉ dành cho tài khoản Premium.', 'warning');
                return;
            }

            const file = hummingInput?.files?.[0] || null;
            const topK = parseInt(hummingTopK?.value || '5', 10) || 5;

            if (!file && !recordedBlob) {
                setHummingStatus('Vui lòng chọn file hoặc ghi âm trước khi tìm kiếm.', 'warning');
                return;
            }

            const formData = new FormData();
            if (file) {
                formData.append('audio', file, file.name);
            } else {
                const ext = (recordedBlob?.type || 'audio/webm').includes('ogg') ? 'ogg' : (recordedBlob?.type || '').includes('mp4') ? 'mp4' : 'webm';
                formData.append('audio', recordedBlob, `humming.${ext}`);
            }
            formData.append('top_k', String(topK));

            try {
                setHummingStatus('Đang phân tích ngân nga...', 'warning');
                setHummingBlocking(true);
                if (searchHummingSubmitBtn) searchHummingSubmitBtn.disabled = true;

                const response = await fetch('{{ route('search.humming') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const rawText = await response.text();
                let data;
                try {
                    data = rawText ? JSON.parse(rawText) : {};
                } catch (parseError) {
                    throw new Error('Phản hồi từ máy chủ không hợp lệ. Vui lòng thử lại.');
                }

                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'Không thể tìm bài bằng ngân nga.');
                }

                renderHummingResults(data.matches || []);
                await waitForPaint();
                hummingForm?.classList.remove('d-none');
                setHummingStatus(`Đã tìm thấy ${data.matches?.length || 0} kết quả.`, 'success');
                setHummingBlocking(false);
            } catch (error) {
                console.error(error);
                const container = ensureHummingResultsContainer();
                if (container) {
                    container.innerHTML = `
                        <div class="humming-empty-state">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>${escapeHtml(error.message || 'Lỗi tìm kiếm')}
                        </div>
                    `;
                }
                await waitForPaint();
                setHummingStatus(error.message || 'Lỗi khi tìm bằng ngân nga.', 'danger');
                setHummingBlocking(false);
            } finally {
                if (searchHummingSubmitBtn) searchHummingSubmitBtn.disabled = false;
            }
        });
    }

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
}

document.addEventListener('DOMContentLoaded', initSearchPageAudioSearch);

if (window.htmx) {
    document.body.addEventListener('htmx:load', initSearchPageAudioSearch);
    document.body.addEventListener('htmx:afterSwap', initSearchPageAudioSearch);
}
</script>
@endpush
