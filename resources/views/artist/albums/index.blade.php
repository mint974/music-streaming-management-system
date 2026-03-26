@extends('layouts.artist')

@section('title', 'Quản lý album – Artist Studio')
@section('page-title', 'Album của tôi')
@section('page-subtitle', 'Tạo và quản lý album nhạc')

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
<form method="GET" action="{{ route('artist.albums.index') }}"
      class="mb-4 p-3 rounded-3" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label text-muted small mb-1">Tìm kiếm</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-dark border-secondary text-muted">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" name="search"
                       class="form-control form-control-sm bg-dark border-secondary text-white"
                       placeholder="Tên album..."
                       value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label text-muted small mb-1">Trạng thái</label>
            <select name="status" class="form-select form-select-sm bg-dark border-secondary text-white">
                <option value="" {{ !request('status') ? 'selected' : '' }}>Tất cả</option>
                <option value="draft"     {{ request('status')==='draft'     ? 'selected' : '' }}>Bản nháp</option>
                <option value="published" {{ request('status')==='published' ? 'selected' : '' }}>Đã xuất bản</option>
            </select>
        </div>
        <div class="col-12 col-md-5 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-fill">
                <i class="fa-solid fa-filter me-1"></i>Lọc
            </button>
            <a href="{{ route('artist.albums.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-xmark"></i>
            </a>
            <a href="{{ route('artist.albums.create') }}" class="btn btn-sm flex-fill"
               style="background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;border:none">
                <i class="fa-solid fa-plus me-1"></i>Tạo album
            </a>
        </div>
    </div>
</form>

{{-- Results summary --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <span class="text-muted small">
        Tìm thấy <strong class="text-white">{{ $albums->total() }}</strong> album
    </span>
    <span class="text-muted small">Trang {{ $albums->currentPage() }} / {{ $albums->lastPage() }}</span>
</div>

{{-- Table --}}
<x-data-table
    :headers="[
        ['label' => '#',         'class' => 'ps-3',       'style' => 'width:52px'],
        ['label' => 'Album'],
        ['label' => 'Bài hát',   'class' => 'd-none d-md-table-cell'],
        ['label' => 'Phát hành', 'class' => 'd-none d-md-table-cell'],
        ['label' => 'Trạng thái'],
        ['label' => 'Thao tác',  'class' => 'text-end pe-3', 'style' => 'width:110px'],
    ]"
    :isEmpty="$albums->isEmpty()"
    emptyIcon="fa-compact-disc"
    emptyText="Bạn chưa có album nào."
>
    @foreach($albums as $album)
    @php
    $statusBadge = $album->status === 'published'
        ? ['bg'=>'rgba(52,211,153,.12)',  'color'=>'#6ee7b7', 'border'=>'rgba(52,211,153,.28)',  'icon'=>'fa-circle-check', 'label'=>$album->statusLabel()]
        : ['bg'=>'rgba(100,116,139,.14)', 'color'=>'#94a3b8', 'border'=>'rgba(100,116,139,.28)', 'icon'=>'fa-pencil',       'label'=>$album->statusLabel()];
    @endphp
    <tr class="border-secondary border-opacity-25">
        {{-- # --}}
        <td class="ps-3 text-muted small">
            {{ $loop->index + 1 + ($albums->currentPage() - 1) * $albums->perPage() }}
        </td>

        {{-- Album --}}
        <td>
            <div class="d-flex align-items-center gap-3">
                <img src="{{ $album->getCoverUrl() }}" alt=""
                     style="width:44px;height:44px;border-radius:8px;object-fit:cover;border:1px solid rgba(255,255,255,.08);flex-shrink:0">
                <div class="min-w-0">
                    <div class="fw-semibold text-white text-truncate" style="max-width:220px">{{ $album->title }}</div>
                    @if($album->description)
                        <div class="small text-muted text-truncate" style="max-width:220px">{{ $album->description }}</div>
                    @endif
                </div>
            </div>
        </td>

        {{-- Bài hát --}}
        <td class="d-none d-md-table-cell text-muted small">
            <i class="fa-solid fa-music me-1 opacity-50"></i>{{ $album->songs_count }}
        </td>

        {{-- Phát hành --}}
        <td class="d-none d-md-table-cell text-muted small">
            {{ $album->released_date ? $album->released_date->format('d/m/Y') : '—' }}
        </td>

        {{-- Trạng thái --}}
        <td>
            <span class="badge rounded-pill px-2 py-1"
                  style="background:{{ $statusBadge['bg'] }};color:{{ $statusBadge['color'] }};border:1px solid {{ $statusBadge['border'] }};font-size:.72rem">
                <i class="fa-solid {{ $statusBadge['icon'] }} me-1"></i>{{ $statusBadge['label'] }}
            </span>
        </td>

        {{-- Thao tác --}}
        <td class="text-end pe-3">
            <div class="d-flex gap-1 justify-content-end">
                <a href="{{ route('artist.albums.show', $album) }}"
                   class="btn btn-sm btn-outline-secondary" title="Xem chi tiết">
                    <i class="fa-solid fa-eye"></i>
                </a>
                <a href="{{ route('artist.albums.edit', $album) }}"
                   class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                    <i class="fa-solid fa-pen"></i>
                </a>
                <form method="POST" action="{{ route('artist.albums.destroy', $album) }}"
                      onsubmit="return confirm('Xóa album \'{{ addslashes($album->title) }}\'?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        </td>
    </tr>
    @endforeach

    @if($albums->hasPages())
        <x-slot:pagination>
            {{ $albums->links('pagination::bootstrap-5') }}
        </x-slot:pagination>
    @endif
</x-data-table>

@endsection
