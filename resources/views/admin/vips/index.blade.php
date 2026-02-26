@extends('layouts.admin')

@section('title', 'Gói VIP')
@section('page-title', 'Quản lý gói VIP')
@section('page-subtitle', 'Tạo, sửa, ẩn/hiện và xóa các gói Premium')

@section('content')

{{-- ─── Validation errors banner ─── --}}
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>
    <strong>Vui lòng kiểm tra lại thông tin gói VIP:</strong>
    <ul class="mb-0 mt-1 small">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ─── Stat cards ─── --}}
<div class="row g-3 mb-4">
    @foreach($vips as $vip)
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="rounded-3 p-3 h-100"
             style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07)">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="fw-semibold text-white">{{ $vip->title }}</span>
                        @if(!$vip->is_active)
                            <span class="badge rounded-pill"
                                  style="background:rgba(107,114,128,.2);color:#9ca3af;font-size:.65rem">ẩn</span>
                        @endif
                    </div>
                    <div class="text-muted small mb-2">{{ $vip->duration_days }} ngày — {{ number_format($vip->price) }} ₫</div>
                    <div class="d-flex gap-3 small">
                        <span class="text-white">
                            <i class="fa-solid fa-users me-1" style="color:#818cf8"></i>
                            {{ number_format($vip->active_subscriptions_count) }} đang dùng
                        </span>
                        <span class="text-muted">
                            {{ number_format($vip->subscriptions_count) }} tổng
                        </span>
                    </div>
                </div>
                <div class="d-flex gap-1 ms-2 flex-shrink-0">
                    <button class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="modal" data-bs-target="#editVipModal"
                            data-vip-id="{{ $vip->id }}"
                            data-vip-title="{{ $vip->title }}"
                            data-vip-desc="{{ $vip->description }}"
                            data-vip-days="{{ $vip->duration_days }}"
                            data-vip-price="{{ $vip->price }}"
                            data-vip-active="{{ $vip->is_active ? '1' : '0' }}"
                            title="Sửa gói">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <form method="POST" action="{{ route('admin.vips.toggleActive', $vip->id) }}" class="d-inline">
                        @csrf
                        <button type="submit"
                                class="btn btn-sm {{ $vip->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                title="{{ $vip->is_active ? 'Ẩn gói' : 'Kích hoạt gói' }}">
                            <i class="fa-solid {{ $vip->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                        </button>
                    </form>
                    @if($vip->subscriptions_count === 0)
                    <button class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal" data-bs-target="#deleteVipModal"
                            data-vip-id="{{ $vip->id }}"
                            data-vip-title="{{ $vip->title }}"
                            title="Xóa gói">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Add new package card --}}
    <div class="col-12 col-sm-6 col-xl-4">
        <button class="btn w-100 h-100 rounded-3 d-flex align-items-center justify-content-center gap-2 text-muted"
                style="background:rgba(255,255,255,.02);border:1px dashed rgba(255,255,255,.15);min-height:90px"
                data-bs-toggle="modal" data-bs-target="#createVipModal">
            <i class="fa-solid fa-plus"></i>
            <span>Thêm gói mới</span>
        </button>
    </div>
</div>

{{-- ─── Table ─── --}}
<div class="card bg-dark border-secondary border-opacity-25">
    <div class="card-header bg-transparent border-secondary border-opacity-25 py-3">
        <h6 class="mb-0 text-white fw-semibold">
            <i class="fa-solid fa-crown me-2" style="color:#fbbf24"></i>Chi tiết các gói VIP
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="border-secondary">
                    <th class="text-muted fw-normal small ps-3">ID</th>
                    <th class="text-muted fw-normal small">Tên gói</th>
                    <th class="text-muted fw-normal small">Mô tả</th>
                    <th class="text-muted fw-normal small text-center">Thời hạn</th>
                    <th class="text-muted fw-normal small text-end">Giá</th>
                    <th class="text-muted fw-normal small text-center">Đang dùng</th>
                    <th class="text-muted fw-normal small text-center">Trạng thái</th>
                    <th class="text-muted fw-normal small text-end pe-3">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vips as $vip)
                <tr class="border-secondary border-opacity-25">
                    <td class="ps-3">
                        <code class="text-muted small">{{ $vip->id }}</code>
                    </td>
                    <td class="fw-semibold text-white">{{ $vip->title }}</td>
                    <td class="text-muted small" style="max-width:220px">
                        <span class="text-truncate d-block" title="{{ $vip->description }}">
                            {{ $vip->description ?? '—' }}
                        </span>
                    </td>
                    <td class="text-center text-white small">{{ number_format($vip->duration_days) }} ngày</td>
                    <td class="text-end fw-semibold" style="color:#fbbf24">
                        {{ number_format($vip->price) }} ₫
                    </td>
                    <td class="text-center">
                        <span class="badge rounded-pill px-2 py-1"
                              style="background:rgba(99,102,241,.15);color:#a5b4fc;border:1px solid rgba(99,102,241,.3);font-size:.72rem">
                            {{ number_format($vip->active_subscriptions_count) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($vip->is_active)
                            <span class="badge rounded-pill px-2 py-1"
                                  style="background:rgba(34,197,94,.12);color:#86efac;border:1px solid rgba(34,197,94,.25);font-size:.72rem">
                                <i class="fa-solid fa-circle-check me-1"></i>Hoạt động
                            </span>
                        @else
                            <span class="badge rounded-pill px-2 py-1"
                                  style="background:rgba(107,114,128,.12);color:#9ca3af;border:1px solid rgba(107,114,128,.25);font-size:.72rem">
                                <i class="fa-solid fa-eye-slash me-1"></i>Đang ẩn
                            </span>
                        @endif
                    </td>
                    <td class="text-end pe-3">
                        <div class="d-flex gap-1 justify-content-end">
                            <button class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal" data-bs-target="#editVipModal"
                                    data-vip-id="{{ $vip->id }}"
                                    data-vip-title="{{ $vip->title }}"
                                    data-vip-desc="{{ $vip->description }}"
                                    data-vip-days="{{ $vip->duration_days }}"
                                    data-vip-price="{{ $vip->price }}"
                                    data-vip-active="{{ $vip->is_active ? '1' : '0' }}"
                                    title="Sửa">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.vips.toggleActive', $vip->id) }}" class="d-inline">
                                @csrf
                                <button type="submit"
                                        class="btn btn-sm {{ $vip->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                        title="{{ $vip->is_active ? 'Ẩn gói' : 'Kích hoạt' }}">
                                    <i class="fa-solid {{ $vip->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                </button>
                            </form>
                            @if($vip->subscriptions_count === 0)
                            <button class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal" data-bs-target="#deleteVipModal"
                                    data-vip-id="{{ $vip->id }}"
                                    data-vip-title="{{ $vip->title }}"
                                    title="Xóa">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="fa-solid fa-crown fa-2x mb-3 opacity-25 d-block"></i>
                        Chưa có gói VIP nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ─── Modal: Tạo gói mới ─── --}}
<div class="modal fade" id="createVipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-plus me-2" style="color:#818cf8"></i>Thêm gói VIP mới
                </h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.vips.store') }}">
                @csrf
                <div class="modal-body">
                    @include('admin.vips._form')
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary">Tạo gói</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─── Modal: Sửa gói ─── --}}
<div class="modal fade" id="editVipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-pen-to-square me-2" style="color:#818cf8"></i>Sửa gói VIP
                </h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editVipForm" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">ID gói (không thể thay đổi)</label>
                        <input type="text" id="edit_vip_id_display" class="form-control form-control-sm bg-dark border-secondary text-muted" disabled>
                    </div>
                    @include('admin.vips._form', ['hideIdField' => true])
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─── Modal: Xóa gói ─── --}}
<div class="modal fade" id="deleteVipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-trash me-2 text-danger"></i>Xóa gói VIP
                </h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="deleteVipForm" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p class="text-muted small mb-0">
                        Bạn có chắc muốn xóa gói <strong class="text-white" id="deleteVipName"></strong>?
                        Hành động này <strong class="text-danger">không thể hoàn tác</strong>.
                    </p>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-danger">Xóa gói</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
{{-- Re-open create modal if validation failed on store --}}
@if($errors->any())
document.addEventListener('DOMContentLoaded', function () {
    var createModal = new bootstrap.Modal(document.getElementById('createVipModal'));
    createModal.show();
});
@endif

document.getElementById('editVipModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const id    = btn.dataset.vipId;
    const title = btn.dataset.vipTitle;
    const desc  = btn.dataset.vipDesc;
    const days  = btn.dataset.vipDays;
    const price = btn.dataset.vipPrice;
    const act   = btn.dataset.vipActive;

    document.getElementById('edit_vip_id_display').value = id;
    document.getElementById('editVipForm').action = '{{ url("/admin/vips") }}/' + id;

    const form = document.getElementById('editVipForm');
    form.querySelector('[name=title]').value         = title;
    form.querySelector('[name=description]').value   = desc ?? '';
    form.querySelector('[name=duration_days]').value = days;
    form.querySelector('[name=price]').value         = price;
    form.querySelector('[name=is_active]').checked   = act === '1';
});

document.getElementById('deleteVipModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('deleteVipName').textContent = btn.dataset.vipTitle;
    document.getElementById('deleteVipForm').action = '{{ url("/admin/vips") }}/' + btn.dataset.vipId;
});
</script>
@endpush
