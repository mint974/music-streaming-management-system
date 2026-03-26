@extends('layouts.artist')

@section('title', $album->title . ' – Chi tiết album')
@section('page-title', 'Chi tiết album')
@section('page-subtitle'){{ $album->title }}@endsection

@push('styles')
<style>
.ab-card {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 16px; padding: 1.4rem 1.5rem;
}
.ab-meta-row { display:flex; gap:8px; align-items:center; padding: 9px 0;
    border-bottom: 1px solid rgba(255,255,255,.05); }
.ab-meta-row:last-child { border-bottom:none; }
.ab-meta-label { font-size:.72rem; font-weight:600; letter-spacing:.05em;
    text-transform:uppercase; color:#475569; min-width:110px; }
.ab-meta-val { color:#e2e8f0; font-size:.85rem; }

.track-item {
    display:flex; align-items:center; gap:12px;
    padding: 10px 12px; border-radius:10px;
    border: 1px solid transparent; transition: all .15s;
}
.track-item:hover {
    background: rgba(168,85,247,.07);
    border-color: rgba(168,85,247,.15);
}
.track-num { width:24px; font-size:.82rem; font-weight:700; color:#334155; text-align:center; flex-shrink:0; }
.track-cover { width:40px; height:40px; border-radius:8px; object-fit:cover;
    border:1px solid rgba(255,255,255,.08); flex-shrink:0; }
.track-bar-bg { height:3px; background:rgba(255,255,255,.06); border-radius:2px; margin-top:5px; }
.track-bar    { height:3px; border-radius:2px;
    background:linear-gradient(90deg,#7c3aed,#a855f7); }

.status-pip { display:inline-block; width:7px; height:7px; border-radius:50%; }
</style>
@endpush

@section('content')
@php
    $statusMap = [
        'published' => ['Đã xuất bản', '#34d399'],
        'draft'     => ['Bản nháp',    '#64748b'],
    ];
    [$sLabel, $sColor] = $statusMap[$album->status] ?? [$album->status, '#64748b'];
    $maxListens = $album->songs->max('listens') ?: 1;
@endphp

{{-- ── Actions bar ──────────────────────────────────────────────────────────── --}}
<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <a href="{{ route('artist.albums.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i>Danh sách album
    </a>
    <a href="{{ route('artist.albums.edit', $album) }}" class="btn btn-sm"
       style="background:rgba(168,85,247,.15);border:1px solid rgba(168,85,247,.3);color:#c084fc;">
        <i class="fa-solid fa-pen me-1"></i>Chỉnh sửa
    </a>
    <span class="ms-auto badge rounded-pill px-3 py-2"
          style="background:{{ $sColor }}22;color:{{ $sColor }};border:1px solid {{ $sColor }}44;font-size:.78rem;">
        {{ $sLabel }}
    </span>
</div>

<div class="row g-4">
    {{-- ── Sidebar ──────────────────────────────────────────────────────────── --}}
    <div class="col-lg-4">
        {{-- Cover --}}
        <div class="ab-card mb-3 text-center">
            @if($album->cover_image)
                <img src="{{ $album->getCoverUrl() }}" alt="{{ $album->title }}"
                     style="width:100%;max-width:220px;aspect-ratio:1;object-fit:cover;border-radius:14px;border:1px solid rgba(255,255,255,.1);">
            @else
                <div style="width:100%;max-width:220px;aspect-ratio:1;border-radius:14px;background:rgba(168,85,247,.08);border:1px solid rgba(168,85,247,.15);display:flex;align-items:center;justify-content:center;margin:0 auto;">
                    <i class="fa-solid fa-compact-disc" style="font-size:3rem;color:rgba(168,85,247,.4)"></i>
                </div>
            @endif
            <h5 class="text-white fw-bold mt-3 mb-1">{{ $album->title }}</h5>
            @if($album->description)
                <p class="text-muted" style="font-size:.8rem;line-height:1.5;">{{ $album->description }}</p>
            @endif
        </div>

        {{-- Stats --}}
        <div class="row g-2 mb-3">
            <div class="col-6">
                <div class="ab-card text-center py-3">
                    <div style="font-size:1.5rem;font-weight:800;color:#a855f7;">{{ $album->songs->count() }}</div>
                    <div style="font-size:.68rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.05em;">Bài hát</div>
                </div>
            </div>
            <div class="col-6">
                <div class="ab-card text-center py-3">
                    <div style="font-size:1.5rem;font-weight:800;color:#60a5fa;">
                        {{ number_format($totalListens) }}
                    </div>
                    <div style="font-size:.68rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.05em;">Lượt nghe</div>
                </div>
            </div>
            <div class="col-12">
                <div class="ab-card text-center py-3">
                    @php
                        $mins = intdiv($totalDuration, 60);
                        $secs = $totalDuration % 60;
                    @endphp
                    <div style="font-size:1.3rem;font-weight:800;color:#34d399;">{{ $mins }}:{{ str_pad($secs, 2, '0', STR_PAD_LEFT) }}</div>
                    <div style="font-size:.68rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.05em;">Tổng thời lượng</div>
                </div>
            </div>
        </div>

        {{-- Metadata --}}
        <div class="ab-card">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#475569;margin-bottom:.85rem;letter-spacing:.06em;">
                <i class="fa-solid fa-circle-info me-2" style="color:#60a5fa"></i>Thông tin
            </div>
            <div class="ab-meta-row">
                <div class="ab-meta-label">Trạng thái</div>
                <span class="status-pip" style="background:{{ $sColor }}"></span>
                <span style="font-size:.8rem;color:{{ $sColor }};">{{ $sLabel }}</span>
            </div>
            <div class="ab-meta-row">
                <div class="ab-meta-label">Phát hành</div>
                <div class="ab-meta-val">{{ $album->released_date?->format('d/m/Y') ?? '—' }}</div>
            </div>
            <div class="ab-meta-row">
                <div class="ab-meta-label">Tạo ngày</div>
                <div class="ab-meta-val">{{ $album->created_at->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    {{-- ── Tracklist ─────────────────────────────────────────────────────────── --}}
    <div class="col-lg-8">
        <div class="ab-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#475569;letter-spacing:.06em;">
                    <i class="fa-solid fa-list-music me-2" style="color:#a855f7"></i>Danh sách bài hát ({{ $album->songs->count() }})
                </div>
                <a href="{{ route('artist.songs.create') }}"
                   style="font-size:.75rem;color:#a855f7;text-decoration:none;">
                    <i class="fa-solid fa-plus me-1"></i>Thêm bài
                </a>
            </div>

            @forelse($album->songs as $i => $song)
            @php
                $songPct = $maxListens > 0 ? round($song->listens / $maxListens * 100) : 0;
                $stColors = [
                    'published' => '#34d399', 'pending' => '#fbbf24',
                    'draft' => '#64748b', 'hidden' => '#f87171', 'scheduled' => '#60a5fa'
                ];
                $stColor = $stColors[$song->status] ?? '#64748b';
            @endphp
            <div class="track-item">
                <div class="track-num">{{ $i + 1 }}</div>
                <img src="{{ $song->getCoverUrl() }}" alt="{{ $song->title }}" class="track-cover">
                <div class="flex-grow-1 min-w-0">
                    <div class="text-white fw-semibold text-truncate" style="font-size:.85rem;">{{ $song->title }}</div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="status-pip" style="background:{{ $stColor }}"></span>
                        <span style="font-size:.7rem;color:{{ $stColor }};">{{ $song->statusLabel() }}</span>
                        @if($song->genre)
                            <span class="text-muted" style="font-size:.7rem;">· {{ $song->genre->name }}</span>
                        @endif
                    </div>
                    <div class="track-bar-bg mt-1">
                        <div class="track-bar" style="width:{{ $songPct }}%"></div>
                    </div>
                </div>
                <div class="text-end flex-shrink-0" style="min-width:70px;">
                    <div class="text-white fw-semibold" style="font-size:.82rem;">{{ number_format($song->listens) }}</div>
                    <div class="text-muted" style="font-size:.68rem;">lượt nghe</div>
                </div>
                <div class="text-muted flex-shrink-0" style="font-size:.78rem;min-width:38px;text-align:right;">
                    {{ $song->durationFormatted() }}
                </div>
                <a href="{{ route('artist.songs.show', $song) }}"
                   class="flex-shrink-0 ms-1" style="color:#475569;font-size:.75rem;text-decoration:none;">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>
            @empty
            <div class="text-center text-muted py-5">
                <i class="fa-solid fa-music fa-2x d-block mb-2 opacity-20"></i>
                <p style="font-size:.85rem;">Album chưa có bài hát nào</p>
                <a href="{{ route('artist.songs.create') }}" class="btn btn-sm"
                   style="background:rgba(168,85,247,.15);border:1px solid rgba(168,85,247,.3);color:#c084fc;font-size:.78rem;">
                    <i class="fa-solid fa-plus me-1"></i>Thêm bài hát
                </a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
