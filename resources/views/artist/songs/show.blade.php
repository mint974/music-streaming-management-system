@extends('layouts.artist')

@section('title', $song->title . ' – Chi tiết bài hát')
@section('page-title', 'Chi tiết bài hát')
@section('page-subtitle'){{ $song->title }}@endsection

@push('styles')
<style>
.sd-card {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 16px; padding: 1.4rem 1.5rem;
}
.sd-meta-row { display:flex; gap:8px; align-items:center; padding: 9px 0;
    border-bottom: 1px solid rgba(255,255,255,.05); }
.sd-meta-row:last-child { border-bottom:none; }
.sd-meta-label { font-size:.72rem; font-weight:600; letter-spacing:.05em;
    text-transform:uppercase; color:#475569; min-width:110px; }
.sd-meta-val { color:#e2e8f0; font-size:.85rem; }

.lrc-line { display:flex; gap:10px; padding:5px 0; border-radius:6px; transition:background .15s; }
.lrc-line:hover { background: rgba(168,85,247,.08); }
.lrc-time { color:#475569; font-size:.72rem; font-family:monospace; min-width:40px; }
.lrc-text { color:#94a3b8; font-size:.82rem; }

.tag-chip {
    display:inline-flex; align-items:center; padding:3px 10px;
    border-radius:20px; font-size:.72rem; font-weight:600;
    background:rgba(168,85,247,.1); color:#c084fc;
    border:1px solid rgba(168,85,247,.2);
}
.tag-chip.mood     { background:rgba(236,72,153,.1); color:#f472b6; border-color:rgba(236,72,153,.2); }
.tag-chip.activity { background:rgba(16,185,129,.1); color:#34d399; border-color:rgba(16,185,129,.2); }
.tag-chip.topic    { background:rgba(59,130,246,.1);  color:#60a5fa; border-color:rgba(59,130,246,.2); }
</style>
@endpush

@section('content')
@php
    $statusMap = [
        'published' => ['Đã xuất bản', 'success', '#34d399'],
        'pending'   => ['Chờ duyệt',   'warning', '#fbbf24'],
        'draft'     => ['Bản nháp',     'secondary','#64748b'],
        'hidden'    => ['Đã ẩn',        'danger',  '#f87171'],
        'scheduled' => ['Hẹn giờ',      'info',    '#60a5fa'],
    ];
    [$sLabel, $sClass, $sColor] = $statusMap[$song->status] ?? [$song->status, 'secondary', '#64748b'];
@endphp

{{-- ── Breadcrumb actions ───────────────────────────────────────────────────── --}}
<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <a href="{{ route('artist.songs.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i>Danh sách bài hát
    </a>
    <a href="{{ route('artist.songs.edit', $song) }}" class="btn btn-sm"
       style="background:rgba(168,85,247,.15);border:1px solid rgba(168,85,247,.3);color:#c084fc;">
        <i class="fa-solid fa-pen me-1"></i>Chỉnh sửa
    </a>
    @if($song->has_lyrics)
    <a href="{{ route('artist.songs.lyrics.index', $song) }}" class="btn btn-sm"
       style="background:rgba(59,130,246,.12);border:1px solid rgba(59,130,246,.25);color:#60a5fa;">
        <i class="fa-solid fa-align-left me-1"></i>Quản lý lời
    </a>
    @endif
    <span class="ms-auto badge rounded-pill px-3 py-2"
          style="background:{{ $sColor }}22;color:{{ $sColor }};border:1px solid {{ $sColor }}44;font-size:.78rem;">
        {{ $sLabel }}
    </span>
</div>

<div class="row g-4">
    {{-- ── Left: Cover + Stats ─────────────────────────────────────────────── --}}
    <div class="col-lg-4">

        {{-- Cover --}}
        <div class="sd-card mb-3 text-center">
            <img src="{{ $song->getCoverUrl() }}" alt="{{ $song->title }}"
                 style="width:100%;max-width:220px;aspect-ratio:1;object-fit:cover;border-radius:14px;border:1px solid rgba(255,255,255,.1);">
            <h5 class="text-white fw-bold mt-3 mb-1">{{ $song->title }}</h5>
            @if($song->author)
                <p class="text-muted mb-0" style="font-size:.82rem;">{{ $song->author }}</p>
            @endif
            @if($song->genre)
                <span class="tag-chip mt-2">{{ $song->genre->name }}</span>
            @endif
        </div>

        {{-- Stats cards --}}
        <div class="row g-2 mb-3">
            <div class="col-6">
                <div class="sd-card text-center py-3">
                    <div style="font-size:1.5rem;font-weight:800;color:#60a5fa;">{{ number_format($song->listens) }}</div>
                    <div style="font-size:.68rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.05em;">Lượt nghe</div>
                </div>
            </div>
            <div class="col-6">
                <div class="sd-card text-center py-3">
                    <div style="font-size:1.5rem;font-weight:800;color:#f472b6;">{{ number_format($favoritesCount) }}</div>
                    <div style="font-size:.68rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.05em;">Yêu thích</div>
                </div>
            </div>
            <div class="col-6">
                <div class="sd-card text-center py-3">
                    <div style="font-size:1.2rem;font-weight:800;color:#34d399;">{{ $song->durationFormatted() }}</div>
                    <div style="font-size:.68rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.05em;">Thời lượng</div>
                </div>
            </div>
            <div class="col-6">
                <div class="sd-card text-center py-3">
                    <div style="font-size:1.2rem;font-weight:800;color:#fbbf24;">{{ $song->fileSizeFormatted() }}</div>
                    <div style="font-size:.68rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.05em;">Kích thước</div>
                </div>
            </div>
        </div>

        {{-- Tags --}}
        @if($song->tags->isNotEmpty())
        <div class="sd-card">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#475569;margin-bottom:.75rem;letter-spacing:.06em;">
                <i class="fa-solid fa-tags me-1"></i>Tags
            </div>
            <div class="d-flex flex-wrap gap-1">
                @foreach($song->tags as $tag)
                    <span class="tag-chip {{ $tag->type }}">{{ $tag->label }}</span>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- ── Right: Metadata + Chart + Lyrics ───────────────────────────────── --}}
    <div class="col-lg-8">

        {{-- Thông tin bài hát --}}
        <div class="sd-card mb-3">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#475569;margin-bottom:.85rem;letter-spacing:.06em;">
                <i class="fa-solid fa-circle-info me-2" style="color:#60a5fa"></i>Thông tin bài hát
            </div>
            <div class="sd-meta-row">
                <div class="sd-meta-label">Trạng thái</div>
                <span class="badge" style="background:{{ $sColor }}22;color:{{ $sColor }};border:1px solid {{ $sColor }}44;font-size:.75rem;">{{ $sLabel }}</span>
            </div>
            <div class="sd-meta-row">
                <div class="sd-meta-label">Thể loại</div>
                <div class="sd-meta-val">{{ $song->genre?->name ?? '—' }}</div>
            </div>
            <div class="sd-meta-row">
                <div class="sd-meta-label">Album</div>
                <div class="sd-meta-val">
                    @if($song->album)
                        <a href="{{ route('artist.albums.show', $song->album) }}" style="color:#a855f7;text-decoration:none;">{{ $song->album->title }}</a>
                    @else —
                    @endif
                </div>
            </div>
            <div class="sd-meta-row">
                <div class="sd-meta-label">Ngày phát hành</div>
                <div class="sd-meta-val">{{ $song->released_date?->format('d/m/Y') ?? '—' }}</div>
            </div>
            <div class="sd-meta-row">
                <div class="sd-meta-label">Loại nội dung</div>
                <div class="sd-meta-val">
                    @if($song->is_vip)
                        <span class="badge" style="background:rgba(251,191,36,.15);color:#fbbf24;border:1px solid rgba(251,191,36,.3);">
                            <i class="fa-solid fa-crown me-1"></i>Premium
                        </span>
                    @else
                        <span class="badge" style="background:rgba(100,116,139,.12);color:#94a3b8;border:1px solid rgba(100,116,139,.2);">Miễn phí</span>
                    @endif
                </div>
            </div>
            <div class="sd-meta-row">
                <div class="sd-meta-label">Định dạng</div>
                <div class="sd-meta-val">{{ strtoupper(pathinfo($song->file_path ?? '', PATHINFO_EXTENSION)) ?: '—' }}</div>
            </div>
            @if($song->publish_at)
            <div class="sd-meta-row">
                <div class="sd-meta-label">Hẹn giờ</div>
                <div class="sd-meta-val">{{ $song->publish_at->format('H:i d/m/Y') }}</div>
            </div>
            @endif
            <div class="sd-meta-row">
                <div class="sd-meta-label">Tải lên</div>
                <div class="sd-meta-val">{{ $song->created_at->format('d/m/Y') }}</div>
            </div>
        </div>

        {{-- Chart 30 ngày --}}
        <div class="sd-card mb-3">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#475569;margin-bottom:.85rem;letter-spacing:.06em;">
                <i class="fa-solid fa-chart-area me-2" style="color:#a855f7"></i>Lượt nghe 30 ngày gần nhất
            </div>
            <div style="height:180px;position:relative;">
                <canvas id="songDailyChart"></canvas>
            </div>
        </div>

        {{-- Lời bài hát --}}
        @if($defaultLyric)
        <div class="sd-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#475569;letter-spacing:.06em;">
                    <i class="fa-solid fa-align-left me-2" style="color:#34d399"></i>Lời bài hát
                    @if($defaultLyric->is_verified)
                        <span style="color:#34d399;font-size:.7rem;"><i class="fa-solid fa-circle-check ms-1"></i>Đã xác minh</span>
                    @endif
                </div>
                <a href="{{ route('artist.songs.lyrics.index', $song) }}"
                   style="font-size:.72rem;color:#a855f7;text-decoration:none;">Quản lý <i class="fa-solid fa-arrow-right ms-1" style="font-size:.6rem"></i></a>
            </div>

            @if($defaultLyric->lines && $defaultLyric->lines->count() > 0)
                <div style="max-height:280px;overflow-y:auto;">
                    @foreach($defaultLyric->lines->take(30) as $line)
                    <div class="lrc-line">
                        <span class="lrc-time">{{ gmdate('i:s', (int)($line->start_time_ms / 1000)) }}</span>
                        <span class="lrc-text">{{ $line->content }}</span>
                    </div>
                    @endforeach
                    @if($defaultLyric->lines->count() > 30)
                        <div class="text-center text-muted py-2" style="font-size:.75rem;">
                            + {{ $defaultLyric->lines->count() - 30 }} dòng nữa...
                        </div>
                    @endif
                </div>
            @elseif($defaultLyric->raw_text)
                <pre style="color:#94a3b8;font-size:.8rem;white-space:pre-wrap;max-height:240px;overflow-y:auto;background:transparent;margin:0;padding:0;">{{ $defaultLyric->raw_text }}</pre>
            @else
                <p class="text-muted" style="font-size:.82rem;">Chưa có dữ liệu lời.</p>
            @endif
        </div>
        @elseif(!$song->has_lyrics)
        <div class="sd-card text-center py-4">
            <i class="fa-solid fa-file-lines fa-2x mb-2 d-block opacity-20"></i>
            <p class="text-muted mb-2" style="font-size:.85rem;">Bài hát chưa có lời</p>
            <a href="{{ route('artist.songs.lyrics.index', $song) }}" class="btn btn-sm"
               style="background:rgba(168,85,247,.15);border:1px solid rgba(168,85,247,.3);color:#c084fc;font-size:.78rem;">
                <i class="fa-solid fa-plus me-1"></i>Thêm lời bài hát
            </a>
        </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const days = @json($dailyDays);
    const vals = @json($dailyVals);
    const ctx  = document.getElementById('songDailyChart').getContext('2d');

    const grad = ctx.createLinearGradient(0, 0, 0, 180);
    grad.addColorStop(0, 'rgba(168,85,247,.3)');
    grad.addColorStop(1, 'rgba(168,85,247,.01)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: days,
            datasets: [{
                label: 'Lượt nghe',
                data: vals,
                borderColor: '#a855f7',
                backgroundColor: grad,
                borderWidth: 2, tension: 0.4, fill: true,
                pointRadius: 1, pointHoverRadius: 5,
                pointBackgroundColor: '#a855f7',
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: ctx => ' ' + ctx.parsed.y.toLocaleString('vi-VN') + ' lượt' }
                }
            },
            scales: {
                x: { grid: { color: 'rgba(255,255,255,.04)' }, ticks: { color: '#475569', maxTicksLimit: 10, font:{size:10} } },
                y: { grid: { color: 'rgba(255,255,255,.04)' }, beginAtZero: true,
                     ticks: { color: '#475569', font:{size:10},
                              callback: v => v >= 1e6 ? (v/1e6).toFixed(1)+'M' : v >= 1e3 ? (v/1e3).toFixed(0)+'K' : v
                     }
                }
            }
        }
    });
});
</script>
@endpush
