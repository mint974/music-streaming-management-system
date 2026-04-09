@extends('layouts.admin')

@section('title', 'Gói đăng ký Nghệ sĩ')
@section('page-title', 'Quản lý gói đăng ký Nghệ sĩ')
@section('page-subtitle', 'Tạo, sửa, ẩn/hiện và xóa các gói dành cho nghệ sĩ')

@section('content')

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>
    <strong>Vui lòng kiểm tra lại thông tin gói nghệ sĩ:</strong>
    <ul class="mb-0 mt-1 small">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-3 mb-4">
    @foreach($packages as $package)
    @php($featuresText = $package->features->pluck('feature')->implode("\n"))
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="artist-package-admin-card h-100">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="fw-semibold text-white">{{ $package->name }}</span>
                        @if(!$package->is_active)
                            <span class="badge rounded-pill artist-package-pill-muted">ẩn</span>
                        @endif
                    </div>
                    <div class="text-muted small mb-2">{{ $package->duration_days }} ngày — {{ number_format($package->price) }} ₫</div>
                    <div class="d-flex gap-3 small">
                        <span class="text-white">
                            <i class="fa-solid fa-users me-1" style="color:#818cf8"></i>
                            {{ number_format($package->active_registrations_count) }} đang dùng
                        </span>
                        <span class="text-muted">
                            {{ number_format($package->registrations_count) }} tổng
                        </span>
                    </div>
                    @if($package->features->isNotEmpty())
                    <div class="artist-package-feature-preview mt-2">
                        {{ $package->features->take(2)->pluck('feature')->join(' • ') }}
                    </div>
                    @endif
                </div>
                <div class="d-flex gap-1 ms-2 flex-shrink-0">
                    <button class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="modal" data-bs-target="#editArtistPackageModal"
                            data-package-id="{{ $package->id }}"
                            data-package-name="{{ $package->name }}"
                            data-package-desc="{{ $package->description ?? '' }}"
                            data-package-days="{{ $package->duration_days }}"
                            data-package-price="{{ $package->price }}"
                            data-package-active="{{ $package->is_active ? '1' : '0' }}"
                            data-package-features="{{ $featuresText }}"
                            title="Sửa gói">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <form method="POST" action="{{ route('admin.artist-packages.toggleActive', $package->id) }}" class="d-inline">
                        @csrf
                        <button type="submit"
                                class="btn btn-sm {{ $package->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                title="{{ $package->is_active ? 'Ẩn gói' : 'Kích hoạt gói' }}">
                            <i class="fa-solid {{ $package->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                        </button>
                    </form>
                    @if($package->registrations_count === 0)
                    <button class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal" data-bs-target="#deleteArtistPackageModal"
                            data-package-id="{{ $package->id }}"
                            data-package-name="{{ $package->name }}"
                            title="Xóa gói">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <div class="col-12 col-sm-6 col-xl-4">
        <button class="btn artist-package-add-card w-100 h-100 rounded-3 d-flex align-items-center justify-content-center gap-2 text-muted"
                data-bs-toggle="modal" data-bs-target="#createArtistPackageModal">
            <i class="fa-solid fa-plus"></i>
            <span>Thêm gói mới</span>
        </button>
    </div>
</div>

<div class="card bg-dark border-secondary border-opacity-25">
    <div class="card-header bg-transparent border-secondary border-opacity-25 py-3">
        <h6 class="mb-0 text-white fw-semibold">
            <i class="fa-solid fa-microphone-lines me-2" style="color:#c084fc"></i>Chi tiết gói đăng ký Nghệ sĩ
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="border-secondary">
                    <th class="text-muted fw-normal small ps-3">ID</th>
                    <th class="text-muted fw-normal small">Tên gói</th>
                    <th class="text-muted fw-normal small">Mô tả</th>
                    <th class="text-muted fw-normal small">Quyền lợi</th>
                    <th class="text-muted fw-normal small text-center">Thời hạn</th>
                    <th class="text-muted fw-normal small text-end">Giá</th>
                    <th class="text-muted fw-normal small text-center">Đang dùng</th>
                    <th class="text-muted fw-normal small text-center">Trạng thái</th>
                    <th class="text-muted fw-normal small text-end pe-3">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($packages as $package)
                @php($featuresText = $package->features->pluck('feature')->implode("\n"))
                <tr class="border-secondary border-opacity-25">
                    <td class="ps-3">
                        <code class="text-muted small">{{ $package->id }}</code>
                    </td>
                    <td class="fw-semibold text-white">{{ $package->name }}</td>
                    <td class="text-muted small" style="max-width:220px">
                        <span class="text-truncate d-block" title="{{ $package->description }}">
                            {{ $package->description ?? '—' }}
                        </span>
                    </td>
                    <td class="text-muted small" style="max-width:280px">
                        @if($package->features->isNotEmpty())
                            <ul class="mb-0 ps-3">
                                @foreach($package->features->take(3) as $feature)
                                    <li>{{ $feature->feature }}</li>
                                @endforeach
                            </ul>
                            @if($package->features->count() > 3)
                                <div class="small mt-1" style="color:#94a3b8">+{{ $package->features->count() - 3 }} quyền lợi khác</div>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-center text-white small">{{ number_format($package->duration_days) }} ngày</td>
                    <td class="text-end fw-semibold" style="color:#fbbf24">
                        {{ number_format($package->price) }} ₫
                    </td>
                    <td class="text-center">
                        <span class="badge rounded-pill px-2 py-1"
                              style="background:rgba(99,102,241,.15);color:#a5b4fc;border:1px solid rgba(99,102,241,.3);font-size:.72rem">
                            {{ number_format($package->active_registrations_count) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($package->is_active)
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
                                    data-bs-toggle="modal" data-bs-target="#editArtistPackageModal"
                                    data-package-id="{{ $package->id }}"
                                    data-package-name="{{ $package->name }}"
                                    data-package-desc="{{ $package->description ?? '' }}"
                                    data-package-days="{{ $package->duration_days }}"
                                    data-package-price="{{ $package->price }}"
                                    data-package-active="{{ $package->is_active ? '1' : '0' }}"
                                    data-package-features="{{ $featuresText }}"
                                    title="Sửa">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.artist-packages.toggleActive', $package->id) }}" class="d-inline">
                                @csrf
                                <button type="submit"
                                        class="btn btn-sm {{ $package->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                        title="{{ $package->is_active ? 'Ẩn gói' : 'Kích hoạt' }}">
                                    <i class="fa-solid {{ $package->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                </button>
                            </form>
                            @if($package->registrations_count === 0)
                            <button class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal" data-bs-target="#deleteArtistPackageModal"
                                    data-package-id="{{ $package->id }}"
                                    data-package-name="{{ $package->name }}"
                                    title="Xóa">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="fa-solid fa-microphone-slash fa-2x mb-3 opacity-25 d-block"></i>
                        Chưa có gói đăng ký nghệ sĩ nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="createArtistPackageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-plus me-2" style="color:#818cf8"></i>Thêm gói đăng ký Nghệ sĩ
                </h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.artist-packages.store') }}">
                @csrf
                <div class="modal-body">
                    @include('admin.artist-packages._form', ['formPrefix' => 'create_artist_package'])
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary">Tạo gói</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editArtistPackageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-pen-to-square me-2" style="color:#818cf8"></i>Sửa gói đăng ký Nghệ sĩ
                </h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editArtistPackageForm" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">ID gói (không thể thay đổi)</label>
                        <input type="text" id="edit_artist_package_id_display" class="form-control form-control-sm bg-dark border-secondary text-muted" disabled>
                    </div>
                    @include('admin.artist-packages._form', ['formPrefix' => 'edit_artist_package'])
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteArtistPackageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-trash me-2 text-danger"></i>Xóa gói đăng ký Nghệ sĩ
                </h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="deleteArtistPackageForm" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p class="text-muted small mb-0">
                        Bạn có chắc muốn xóa gói <strong class="text-white" id="deleteArtistPackageName"></strong>?
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
@if($errors->any())
document.addEventListener('DOMContentLoaded', function () {
    var createModal = new bootstrap.Modal(document.getElementById('createArtistPackageModal'));
    createModal.show();
});
@endif

document.getElementById('editArtistPackageModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const id = btn.dataset.packageId;
    const name = btn.dataset.packageName;
    const desc = btn.dataset.packageDesc;
    const days = btn.dataset.packageDays;
    const price = btn.dataset.packagePrice;
    const active = btn.dataset.packageActive;
    const features = btn.dataset.packageFeatures;

    document.getElementById('edit_artist_package_id_display').value = id;
    document.getElementById('editArtistPackageForm').action = '{{ url("/admin/artist-packages") }}/' + id;

    const form = document.getElementById('editArtistPackageForm');
    form.querySelector('[name=name]').value = name ?? '';
    form.querySelector('[name=description]').value = desc ?? '';
    form.querySelector('[name=duration_days]').value = days ?? '';
    form.querySelector('[name=price]').value = price ?? '';
    form.querySelector('[name=features_text]').value = features ?? '';
    form.querySelector('[name=is_active]').checked = active === '1';
});

document.getElementById('deleteArtistPackageModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('deleteArtistPackageName').textContent = btn.dataset.packageName;
    document.getElementById('deleteArtistPackageForm').action = '{{ url("/admin/artist-packages") }}/' + btn.dataset.packageId;
});
</script>
@endpush
