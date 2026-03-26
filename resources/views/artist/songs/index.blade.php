@extends('layouts.artist')

@section('title', 'Bài hát của tôi – Artist Studio')
@section('page-title', 'Bài hát của tôi')
@section('page-subtitle', 'Quản lý toàn bộ bài hát bạn đã tải lên')

@section('content')

{{-- Flash --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert"
         style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.28);color:#6ee7b7">
        <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter bar --}}
<form method="GET" action="{{ route('artist.songs.index') }}"
      class="mb-4 p-3 rounded-3" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label text-muted small mb-1">Tìm kiếm</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-dark border-secondary text-muted">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" name="search"
                       class="form-control form-control-sm bg-dark border-secondary text-white"
                       placeholder="Tên bài hát..."
                       value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label text-muted small mb-1">Trạng thái</label>
            <select name="status" class="form-select form-select-sm bg-dark border-secondary text-white">
                <option value="" {{ !request('status') ? 'selected' : '' }}>Tất cả</option>
                <option value="draft"     {{ request('status')==='draft'     ? 'selected' : '' }}>Bản nháp</option>
                <option value="pending"   {{ request('status')==='pending'   ? 'selected' : '' }}>Chờ duyệt</option>
                <option value="published" {{ request('status')==='published' ? 'selected' : '' }}>Đã xuất bản</option>
                <option value="scheduled" {{ request('status')==='scheduled' ? 'selected' : '' }}>Hẹn giờ</option>
                <option value="hidden"    {{ request('status')==='hidden'    ? 'selected' : '' }}>Ẩn</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label text-muted small mb-1">Thể loại</label>
            <select name="genre_id" class="form-select form-select-sm bg-dark border-secondary text-white">
                <option value="" {{ !request('genre_id') ? 'selected' : '' }}>Tất cả</option>
                @foreach($genres as $g)
                    <option value="{{ $g->id }}" {{ request('genre_id')==$g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-fill">
                <i class="fa-solid fa-filter me-1"></i>Lọc
            </button>
            <a href="{{ route('artist.songs.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-xmark"></i>
            </a>
            <a href="{{ route('artist.songs.create') }}" class="btn btn-sm flex-fill"
               style="background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;border:none">
                <i class="fa-solid fa-plus me-1"></i>Tải lên
            </a>
        </div>
    </div>
</form>

{{-- Results summary --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <span class="text-muted small">
        Tìm thấy <strong class="text-white">{{ $songs->total() }}</strong> bài hát
    </span>
    <span class="text-muted small">Trang {{ $songs->currentPage() }} / {{ $songs->lastPage() }}</span>
</div>

{{-- Table --}}
<x-data-table
    :headers="[
        ['label' => '#',           'class' => 'ps-3',                           'style' => 'width:52px'],
        ['label' => 'Bài hát'],
        ['label' => 'Thể loại',    'class' => 'd-none d-md-table-cell'],
        ['label' => 'Album',       'class' => 'd-none d-lg-table-cell'],
        ['label' => 'Thời lượng',  'class' => 'd-none d-md-table-cell'],
        ['label' => 'Trạng thái'],
        ['label' => 'Lượt nghe',   'class' => 'text-end d-none d-md-table-cell'],
        ['label' => 'Thao tác',    'class' => 'text-end pe-3',                  'style' => 'width:110px'],
    ]"
    :isEmpty="$songs->isEmpty()"
    emptyIcon="fa-music"
    emptyText="Bạn chưa có bài hát nào. Hãy tải lên bài hát đầu tiên!"
>
    @foreach($songs as $song)
    @php
    $statusBadge = match($song->status) {
        'published' => ['bg'=>'rgba(52,211,153,.12)',  'color'=>'#6ee7b7', 'border'=>'rgba(52,211,153,.28)',  'icon'=>'fa-circle-check', 'label'=>$song->statusLabel()],
        'scheduled' => ['bg'=>'rgba(59,130,246,.12)',  'color'=>'#93c5fd', 'border'=>'rgba(59,130,246,.28)',  'icon'=>'fa-calendar-check', 'label'=>$song->statusLabel()],
        'hidden'    => ['bg'=>'rgba(239,68,68,.12)',   'color'=>'#fca5a5', 'border'=>'rgba(239,68,68,.28)',   'icon'=>'fa-eye-slash', 'label'=>$song->statusLabel()],
        'pending'   => ['bg'=>'rgba(251,191,36,.12)',  'color'=>'#fcd34d', 'border'=>'rgba(251,191,36,.28)',  'icon'=>'fa-clock',        'label'=>$song->statusLabel()],
        default     => ['bg'=>'rgba(100,116,139,.14)', 'color'=>'#94a3b8', 'border'=>'rgba(100,116,139,.28)', 'icon'=>'fa-pencil',       'label'=>$song->statusLabel()],
    };
    @endphp
    <tr class="border-secondary border-opacity-25">
        {{-- # --}}
        <td class="ps-3 text-muted small">
            {{ $loop->index + 1 + ($songs->currentPage() - 1) * $songs->perPage() }}
        </td>

        {{-- Bài hát --}}
        <td>
            <div class="d-flex align-items-center gap-3">
                <div style="position:relative;flex-shrink:0">
                    <img src="{{ $song->getCoverUrl() }}" alt=""
                         style="width:44px;height:44px;border-radius:8px;object-fit:cover;border:1px solid rgba(255,255,255,.08)">
                    @if($song->is_vip)
                        <span style="position:absolute;bottom:-4px;right:-4px;background:#0f172a;border-radius:50%;width:17px;height:17px;display:flex;align-items:center;justify-content:center;border:1px solid rgba(251,191,36,.4)">
                            <i class="fa-solid fa-crown" style="font-size:.5rem;color:#fbbf24"></i>
                        </span>
                    @endif
                </div>
                <div class="min-w-0">
                    <div class="fw-semibold text-white text-truncate" style="max-width:200px">{{ $song->title }}</div>
                    @if($song->author)
                        <div class="small text-muted">{{ $song->author }}</div>
                    @endif
                </div>
            </div>
        </td>

        {{-- Thể loại --}}
        <td class="d-none d-md-table-cell">
            @if($song->genre)
                <span class="badge rounded-pill px-2 py-1"
                      style="background:rgba(168,85,247,.15);color:#c084fc;border:1px solid rgba(168,85,247,.3);font-size:.72rem">
                    {{ $song->genre->name }}
                </span>
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>

        {{-- Album --}}
        <td class="d-none d-lg-table-cell text-muted small">
            {{ $song->album?->title ?? '—' }}
        </td>

        {{-- Thời lượng --}}
        <td class="d-none d-md-table-cell text-muted small" style="font-variant-numeric:tabular-nums">
            {{ $song->durationFormatted() }}
        </td>

        {{-- Trạng thái --}}
        <td>
            <span class="badge rounded-pill px-2 py-1"
                  style="background:{{ $statusBadge['bg'] }};color:{{ $statusBadge['color'] }};border:1px solid {{ $statusBadge['border'] }};font-size:.72rem">
                <i class="fa-solid {{ $statusBadge['icon'] }} me-1"></i>{{ $statusBadge['label'] }}
            </span>
        </td>

        {{-- Lượt nghe --}}
        <td class="d-none d-md-table-cell text-end text-muted small" style="font-variant-numeric:tabular-nums">
            <i class="fa-solid fa-headphones me-1 opacity-50"></i>{{ number_format($song->listens) }}
        </td>

        {{-- Thao tác --}}
        <td class="text-end pe-3">
            <div class="d-flex gap-1 justify-content-end">
                <a href="{{ route('artist.songs.show', $song) }}"
                   class="btn btn-sm btn-outline-secondary" title="Xem chi tiết">
                    <i class="fa-solid fa-eye"></i>
                </a>
                <a href="{{ route('artist.songs.lyrics.index', $song) }}"
                   class="btn btn-sm btn-outline-info" title="Lời bài hát">
                    <i class="fa-solid fa-microphone-lines"></i>
                </a>
                <a href="{{ route('artist.songs.edit', $song) }}"
                   class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                    <i class="fa-solid fa-pen"></i>
                </a>
                <form method="POST" action="{{ route('artist.songs.destroy', $song) }}"
                      onsubmit="return confirm('Xóa bài hát \'{{ addslashes($song->title) }}\'?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        </td>
    </tr>
    @endforeach

    @if($songs->hasPages())
        <x-slot:pagination>
            {{ $songs->links('pagination::bootstrap-5') }}
        </x-slot:pagination>
    @endif
</x-data-table>

@endsection
