@extends('layouts.main')

@section('title', $q ? "Kết quả cho \"{$q}\" – Blue Wave Music" : 'Tìm kiếm – Blue Wave Music')

@section('content')
@php
    $user        = auth()->user();
    $hasQuery    = $q !== '';
    $totalArtists = $artists->count();
    $totalResults = $totalArtists; // + songs + albums + ... khi có
@endphp

<div class="search-page px-1">

    {{-- ─── Header khi không có query ─── --}}
    @if(!$hasQuery)
    <div class="search-hero">
        <div class="search-hero-icon">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <h2>Khám phá âm nhạc</h2>
        <p>Tìm kiếm bài hát, nghệ sĩ, album và playlist yêu thích của bạn ngay bây giờ.</p>
    </div>
    @else
    {{-- Tiêu đề kết quả --}}
    <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
        <div>
            <h3 class="text-white fw-bold mb-1" style="font-size:1.25rem">
                Kết quả cho <span style="color:#c084fc">"{{ $q }}"</span>
            </h3>
            <p class="text-muted mb-0" style="font-size:.82rem">
                @if($totalResults > 0)
                    Tìm thấy {{ $totalResults }} kết quả
                @else
                    Không tìm thấy kết quả nào
                @endif
            </p>
        </div>
        <span class="result-count ms-auto">{{ $totalResults }} kết quả</span>
    </div>
    @endif

    {{-- ─── Lịch sử tìm kiếm (hiện khi không có query) ─── --}}
    @if(!$hasQuery && (count($history) > 0 || true))
    <div class="mb-5" id="historySection">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="search-section-title mb-0">
                <i class="fa-solid fa-clock-rotate-left"></i>Lịch sử tìm kiếm
            </div>
            @if(count($history) > 0)
                @auth
                <button class="btn btn-link p-0 text-muted"
                        style="font-size:.75rem;text-decoration:none"
                        id="clearHistoryBtn">
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
                <a href="{{ route('search', ['q' => $hq]) }}"
                   class="history-label text-decoration-none"
                   style="color:inherit">{{ $hq }}</a>
                @auth
                <button class="history-remove history-remove-btn"
                        data-query="{{ $hq }}"
                        title="Xóa">
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
                <i class="fa-solid fa-circle-info me-1"></i>
                Đăng nhập để lưu lịch sử tìm kiếm.
            @endauth
        </p>
        @endif
    </div>
    @endif

    @if($hasQuery)

    {{-- ─── Nghệ sĩ ─── --}}
    <div class="mb-5">
        <div class="search-section-title">
            <i class="fa-solid fa-microphone-lines" style="color:#a855f7"></i>
            Nghệ sĩ
            @if($totalArtists > 0)
                <span class="result-count ms-1">{{ $totalArtists }}</span>
            @endif
        </div>

        @if($artists->isEmpty())
            <p class="text-muted" style="font-size:.85rem">
                Không tìm thấy nghệ sĩ nào khớp với "{{ $q }}".
            </p>
        @else
            <div class="artist-grid">
                @foreach($artists as $artist)
                @php
                    $aInitial   = strtoupper(substr($artist->name, 0, 1));
                    $aAvatarSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80'%3E%3Ccircle cx='40' cy='40' r='40' fill='%23a855f7'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='28' fill='%23ffffff' font-weight='bold'%3E" . $aInitial . "%3C/text%3E%3C/svg%3E";
                    $aAvatar    = ($artist->avatar && $artist->avatar !== '/storage/avt.jpg')
                                    ? asset($artist->avatar) : $aAvatarSvg;
                    $aName      = $artist->artist_name ?: $artist->name;
                @endphp
                <a href="{{ route('search', ['q' => $aName]) }}" class="artist-card-search">
                    <img src="{{ $aAvatar }}"
                         alt="{{ $aName }}"
                         class="acs-avatar"
                         onerror="this.src='{{ $aAvatarSvg }}'">
                    <div class="acs-name">
                        {{ $aName }}
                        @if($artist->artist_verified_at)
                            <i class="fa-solid fa-circle-check acs-verified" title="Đã xác minh"></i>
                        @endif
                    </div>
                    <div class="acs-role">Nghệ sĩ</div>
                </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ─── Bài hát (coming soon) ─── --}}
    <div class="mb-5">
        <div class="search-section-title">
            <i class="fa-solid fa-music" style="color:#c084fc"></i>
            Bài hát
        </div>
        <div class="search-coming-soon">
            <div class="scs-icon" style="background:rgba(168,85,247,.1);color:#a855f7">
                <i class="fa-solid fa-music"></i>
            </div>
            <div>
                <div class="fw-semibold" style="color:#64748b;font-size:.85rem">Tìm kiếm bài hát</div>
                <div style="font-size:.77rem;margin-top:2px">Tính năng đang được phát triển — sẽ ra mắt sớm.</div>
            </div>
            <i class="fa-solid fa-hammer ms-auto" style="font-size:.85rem;color:#334155"></i>
        </div>
    </div>

    {{-- ─── Album (coming soon) ─── --}}
    <div class="mb-5">
        <div class="search-section-title">
            <i class="fa-solid fa-compact-disc" style="color:#60a5fa"></i>
            Album
        </div>
        <div class="search-coming-soon">
            <div class="scs-icon" style="background:rgba(59,130,246,.1);color:#60a5fa">
                <i class="fa-solid fa-compact-disc"></i>
            </div>
            <div>
                <div class="fw-semibold" style="color:#64748b;font-size:.85rem">Tìm kiếm album</div>
                <div style="font-size:.77rem;margin-top:2px">Tính năng đang được phát triển — sẽ ra mắt sớm.</div>
            </div>
            <i class="fa-solid fa-hammer ms-auto" style="font-size:.85rem;color:#334155"></i>
        </div>
    </div>

    {{-- ─── Playlist (coming soon) ─── --}}
    <div class="mb-5">
        <div class="search-section-title">
            <i class="fa-solid fa-list-music" style="color:#34d399"></i>
            Playlist
        </div>
        <div class="search-coming-soon">
            <div class="scs-icon" style="background:rgba(16,185,129,.1);color:#34d399">
                <i class="fa-solid fa-list-music"></i>
            </div>
            <div>
                <div class="fw-semibold" style="color:#64748b;font-size:.85rem">Tìm kiếm playlist</div>
                <div style="font-size:.77rem;margin-top:2px">Tính năng đang được phát triển — sẽ ra mắt sớm.</div>
            </div>
            <i class="fa-solid fa-hammer ms-auto" style="font-size:.85rem;color:#334155"></i>
        </div>
    </div>

    {{-- ─── Nếu không có kết quả nào ─── --}}
    @if($totalResults === 0)
    <div class="search-no-results mt-5">
        <div class="snr-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
        <h4>Không tìm thấy kết quả</h4>
        <p>Thử tìm với từ khóa khác hoặc kiểm tra chính tả.</p>
    </div>
    @endif

    @endif {{-- /$hasQuery --}}

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // ── Xóa toàn bộ lịch sử ─────────────────────────────────────── //
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
            } catch (e) { console.error(e); }
        });
    }

    // ── Xóa từng mục lịch sử ────────────────────────────────────── //
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
            } catch (e) { console.error(e); }
        });
    });
});
</script>
@endpush
