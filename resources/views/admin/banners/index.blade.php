@extends('layouts.admin')

@section('title', 'Quản lý Banner Trang chủ')
@section('page-title', 'Banner Trang chủ')
@section('page-subtitle', 'Quản lý ảnh banner hiển thị trên trang chủ')

@section('content')

@if(session('success'))
<div class="alert alert-dismissible fade show mb-4" style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.28);color:#6ee7b7" role="alert">
    <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-3 mb-4">
    @php
    $statCards = [
        ['label' => 'Tổng cộng', 'value' => $stats['total'], 'icon' => 'fa-images', 'color' => '#818cf8', 'bg' => 'rgba(99,102,241,.12)'],
        ['label' => 'Đang bật', 'value' => $stats['active'], 'icon' => 'fa-toggle-on', 'color' => '#4ade80', 'bg' => 'rgba(74,222,128,.12)'],
        ['label' => 'Tổng click', 'value' => $stats['total_clicks'], 'icon' => 'fa-hand-pointer', 'color' => '#c084fc', 'bg' => 'rgba(192,132,252,.12)'],
    ];
    @endphp
    @foreach($statCards as $s)
    <div class="col-6 col-xl-4">
        <div class="rounded-3 p-3 d-flex align-items-center gap-3 h-100" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07)">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;background:{{ $s['bg'] }};border:1px solid {{ $s['color'] }}25">
                <i class="fa-solid {{ $s['icon'] }}" style="color:{{ $s['color'] }};font-size:.88rem"></i>
            </div>
            <div>
                <div class="fw-bold text-white" style="font-size:1.25rem;line-height:1">{{ number_format($s['value']) }}</div>
                <div class="text-muted" style="font-size:.72rem">{{ $s['label'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="d-flex flex-wrap gap-2 mb-3">
    <form method="GET" action="{{ route('admin.banners.index') }}" class="filter-bar flex-grow-1 m-0">
        <div class="filter-bar-inner">
            <div class="filter-field" style="flex:1.8;min-width:200px;">
                <label class="filter-label"><i class="fa-solid fa-magnifying-glass"></i>Tìm kiếm</label>
                <div class="filter-search-wrap">
                    <i class="fa-solid fa-magnifying-glass filter-search-icon"></i>
                    <input type="text" name="search" class="filter-input" placeholder="Tên banner..." value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>

            <div class="filter-field" style="min-width:130px;">
                <label class="filter-label"><i class="fa-solid fa-toggle-on"></i>Trạng thái</label>
                <select name="status" class="filter-select">
                    <option value="">Tất cả</option>
                    <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Đang bật</option>
                    <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Đang tắt</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="filter-btn-submit">
                    <i class="fa-solid fa-filter"></i>Lọc
                </button>
                <a href="{{ route('admin.banners.index') }}" class="filter-btn-reset" title="Xóa bộ lọc">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            </div>
        </div>
    </form>

    <a href="{{ route('admin.banners.create') }}" class="btn d-flex align-items-center gap-2" style="background:rgba(168,85,247,.15);color:#c084fc;border:1px solid rgba(168,85,247,.3);font-size:.85rem;padding:0 1rem;border-radius:8px">
        <i class="fa-solid fa-plus"></i>Tạo mới
    </a>
</div>

<div class="card bg-dark border-secondary border-opacity-25">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="border-secondary">
                    <th class="ps-3 text-muted fw-normal small" style="width:60px">Thứ tự</th>
                    <th class="text-muted fw-normal small">Thông tin Banner</th>
                    <th class="text-muted fw-normal small d-none d-lg-table-cell">Lịch hiển thị</th>
                    <th class="text-muted fw-normal small d-none d-xl-table-cell">Tạo bởi</th>
                    <th class="text-muted fw-normal small">Trạng thái</th>
                    <th class="text-muted fw-normal small text-end pe-3">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banners as $item)
                <tr class="border-secondary border-opacity-25">
                    <td class="ps-3 text-muted small">{{ $item->order_index }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" style="width:100px;height:52px;object-fit:cover;border-radius:6px;border:1px solid rgba(255,255,255,.1)">
                            <div class="min-w-0">
                                <div class="fw-semibold text-white text-truncate" style="max-width:260px;font-size:.9rem">{{ $item->title }}</div>
                                @if($item->target_url)
                                <div class="text-muted" style="font-size:.72rem;max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                    <i class="fa-solid fa-link me-1"></i>{{ $item->target_url }}
                                </div>
                                @endif
                                <div class="text-muted mt-1" style="font-size:.7rem">
                                    <i class="fa-solid fa-hand-pointer me-1"></i>{{ number_format($item->clicks) }} clicks
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="d-none d-lg-table-cell text-muted" style="font-size:.75rem">
                        @if($item->start_time || $item->end_time)
                            @if($item->start_time)
                                <div><span style="color:#a78bfa">Bắt đầu:</span> {{ $item->start_time->format('d/m/Y H:i') }}</div>
                            @endif
                            @if($item->end_time)
                                <div><span style="color:#f87171">Kết thúc:</span> {{ $item->end_time->format('d/m/Y H:i') }}</div>
                            @endif
                            @if($item->end_time && $item->end_time->isPast())
                                <span class="badge bg-danger mt-1" style="font-size:.6rem">Đã hết hạn</span>
                            @endif
                        @else
                            <i class="fa-solid fa-infinity text-muted me-1"></i>Không giới hạn
                        @endif
                    </td>
                    <td class="d-none d-xl-table-cell text-muted small">
                        @if($item->creator)
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $item->creator->avatar ?? asset('images/null-avatar.jpg') }}" alt="{{ $item->creator->name }}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;border:1px solid rgba(255,255,255,.1)">
                                <div>
                                    <div class="text-white" style="font-size:.8rem">{{ $item->creator->name }}</div>
                                    <div class="text-muted" style="font-size:.65rem">{{ $item->created_at->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.banners.toggle', $item->id) }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm shadow-none" style="padding:2px 0;background:none;border:none">
                                @if($item->status === 'active')
                                    <i class="fa-solid fa-toggle-on fa-lg" style="color:#4ade80" title="Bấm để tắt"></i>
                                @else
                                    <i class="fa-solid fa-toggle-off fa-lg text-muted" title="Bấm để bật"></i>
                                @endif
                            </button>
                        </form>
                    </td>
                    <td class="text-end pe-3">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('admin.banners.edit', $item->id) }}" class="btn btn-sm btn-outline-secondary" style="padding:4px 8px" title="Chỉnh sửa">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" style="padding:4px 8px" data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $item->id }}" title="Xóa vĩnh viễn">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                
                {{-- Modal Xóa Banner --}}
                <div class="modal fade" id="deleteModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-dark border border-secondary border-opacity-50">
                            <div class="modal-header border-secondary border-opacity-25 pb-3">
                                <h6 class="modal-title text-white">
                                    <i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i>Xóa Banner
                                </h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body py-3">
                                <p class="text-white small mb-0">Bạn có chắc chắn muốn xóa vĩnh viễn banner <strong>{{ $item->title }}</strong> không? Thao tác này không thể hoàn tác.</p>
                            </div>
                            <div class="modal-footer border-secondary border-opacity-25 pt-3">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                                <form method="POST" action="{{ route('admin.banners.destroy', $item->id) }}" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger px-4">
                                        <i class="fa-solid fa-trash me-1"></i>Xóa
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="fa-solid fa-images fa-2x mb-3 opacity-25 d-block"></i>
                        Chưa có banner nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($banners->hasPages())
    <div class="card-footer bg-transparent border-secondary border-opacity-25 py-2">
        {{ $banners->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection

