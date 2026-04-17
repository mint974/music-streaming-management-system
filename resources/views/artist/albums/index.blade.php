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
<form method="GET" action="{{ route('artist.albums.index') }}" class="filter-bar">
    <div class="filter-bar-inner">

        <div class="filter-field" style="flex:1;min-width:200px;">
            <label class="filter-label"><i class="fa-solid fa-magnifying-glass"></i>Tìm kiếm</label>
            <div class="filter-search-wrap">
                <i class="fa-solid fa-magnifying-glass filter-search-icon"></i>
                <input type="text" name="search" class="filter-input"
                       placeholder="Tên album..."
                       value="{{ request('search') }}">
            </div>
        </div>

        <div class="filter-field" style="min-width:145px;">
            <label class="filter-label"><i class="fa-solid fa-toggle-on"></i>Trạng thái</label>
            <select name="status" class="filter-select">
                <option value="" {{ !request('status') ? 'selected' : '' }}>Tất cả</option>
                <option value="draft"     {{ request('status')==='draft'     ? 'selected' : '' }}>Bản nháp</option>
                <option value="published" {{ request('status')==='published' ? 'selected' : '' }}>Đã xuất bản</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="filter-btn-submit">
                <i class="fa-solid fa-filter"></i>Lọc
                @if(request('search') || request('status'))
                    <span class="filter-active-dot"></span>
                @endif
            </button>
            <a href="{{ route('artist.albums.index') }}" class="filter-btn-reset" title="Xóa bộ lọc">
                <i class="fa-solid fa-xmark"></i>
            </a>
            <a href="{{ route('artist.albums.create') }}"
               style="display:inline-flex;align-items:center;gap:.45rem;padding:.52rem 1rem;border-radius:10px;background:linear-gradient(135deg,rgba(124,58,237,.5),rgba(168,85,247,.4));border:1px solid rgba(168,85,247,.4);color:#e9d5ff;font-size:.82rem;font-weight:600;text-decoration:none;white-space:nowrap;transition:all .18s ease"
               onmouseover="this.style.background='linear-gradient(135deg,rgba(124,58,237,.7),rgba(168,85,247,.6))'"
               onmouseout="this.style.background='linear-gradient(135deg,rgba(124,58,237,.5),rgba(168,85,247,.4))'">
                <i class="fa-solid fa-plus"></i>Tạo album
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
                <button type="button"
                        class="btn btn-sm btn-outline-danger"
                        title="Xóa album"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteAlbumModal"
                        data-action="{{ route('artist.albums.destroy', $album) }}"
                        data-name="{{ $album->title }}">
                    <i class="fa-solid fa-trash"></i>
                </button>
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

{{-- ══ MODAL XÁC NHẬN XÓA ALBUM ══ --}}
@push('modals')
<div class="modal fade" id="deleteAlbumModal" tabindex="-1" aria-labelledby="deleteAlbumModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#1a1a2e;border:1px solid rgba(239,68,68,.35);border-radius:14px">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:42px;height:42px;border-radius:10px;background:rgba(239,68,68,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fa-solid fa-trash" style="color:#f87171;font-size:1.1rem"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 text-white fw-semibold" id="deleteAlbumModalLabel">Xóa album</h5>
                        <p class="mb-0 text-muted" style="font-size:.8rem">Thao tác này không thể hoàn tác</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body pt-3">
                <p class="text-muted mb-1" style="font-size:.9rem">Bạn có chắc muốn xóa album:</p>
                <p id="deleteAlbumName" class="text-white fw-semibold mb-0"
                   style="background:rgba(255,255,255,.05);border-radius:8px;padding:8px 12px;border:1px solid rgba(255,255,255,.08)"></p>
            </div>
            <div class="modal-footer border-0 pt-0 gap-2">
                <button type="button" class="btn btn-sm px-4" data-bs-dismiss="modal"
                        style="background:#1f2937;border:1px solid #374151;color:#9ca3af">Hủy bỏ</button>
                <form id="deleteAlbumForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm px-4"
                            style="background:linear-gradient(135deg,#dc2626,#b91c1c);border:none;color:#fff">
                        <i class="fa-solid fa-trash me-1"></i>Xóa album
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    document.getElementById('deleteAlbumModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        document.getElementById('deleteAlbumForm').action = btn.getAttribute('data-action');
        document.getElementById('deleteAlbumName').textContent = btn.getAttribute('data-name');
    });
</script>
@endpush
