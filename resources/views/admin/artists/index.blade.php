@extends('layouts.admin')

@section('title', 'Quản lý nghệ sĩ')
@section('page-title', 'Quản lý nghệ sĩ')
@section('page-subtitle', 'Xem, xác minh và quản lý tài khoản nghệ sĩ')

@section('content')

{{-- ─── Filter bar ─── --}}
<form method="GET" action="{{ route('admin.artists.index') }}" class="card bg-dark border-secondary border-opacity-25 mb-4">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label text-muted small mb-1">Tìm kiếm</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-dark border-secondary text-muted">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="search" class="form-control form-control-sm bg-dark border-secondary text-white"
                           placeholder="Tên nghệ sĩ, email, số điện thoại..."
                           value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label text-muted small mb-1">Xác minh</label>
                <select name="verified" class="form-select form-select-sm bg-dark border-secondary text-white">
                    <option value="" {{ !isset($filters['verified']) || $filters['verified'] === '' ? 'selected' : '' }}>Tất cả</option>
                    <option value="1" {{ ($filters['verified'] ?? '') === '1' ? 'selected' : '' }}>Đã xác minh</option>
                    <option value="0" {{ ($filters['verified'] ?? '') === '0' ? 'selected' : '' }}>Chưa xác minh</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label text-muted small mb-1">Trạng thái</label>
                <select name="status" class="form-select form-select-sm bg-dark border-secondary text-white">
                    <option value="" {{ !isset($filters['status']) || $filters['status'] === '' ? 'selected' : '' }}>Tất cả</option>
                    <option value="Đang hoạt động" {{ ($filters['status'] ?? '') === 'Đang hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="Bị khóa"        {{ ($filters['status'] ?? '') === 'Bị khóa'        ? 'selected' : '' }}>Bị khóa</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="fa-solid fa-filter me-1"></i>Lọc
                </button>
                <a href="{{ route('admin.artists.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            </div>
        </div>
    </div>
</form>

{{-- ─── Results summary ─── --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <span class="text-muted small">
        Tìm thấy <strong class="text-white">{{ $artists->total() }}</strong> nghệ sĩ
    </span>
    <span class="text-muted small">Trang {{ $artists->currentPage() }} / {{ $artists->lastPage() }}</span>
</div>

{{-- ─── Table ─── --}}
<div class="card bg-dark border-secondary border-opacity-25">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="border-secondary">
                    <th class="text-muted fw-normal small ps-3" style="width:46px">#</th>
                    <th class="text-muted fw-normal small">Nghệ sĩ</th>
                    <th class="text-muted fw-normal small">Xác minh</th>
                    <th class="text-muted fw-normal small">Trạng thái</th>
                    <th class="text-muted fw-normal small">Ngày xác minh</th>
                    <th class="text-muted fw-normal small text-end pe-3">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($artists as $artist)
                <tr class="border-secondary border-opacity-25">
                    <td class="ps-3 text-muted small">{{ $artist->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="position-relative flex-shrink-0">
                                <img src="{{ $artist->avatar && $artist->avatar !== '/storage/avt.jpg' ? asset($artist->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($artist->name).'&background=a855f7&color=fff&size=40' }}"
                                     class="rounded-circle"
                                     width="38" height="38"
                                     style="object-fit:cover;border:1px solid rgba(255,255,255,.1)"
                                     alt="{{ $artist->name }}">
                                @if($artist->isArtistVerified())
                                    <span class="position-absolute bottom-0 end-0 translate-middle-x"
                                          style="background:#1d9bf0;border-radius:50%;width:14px;height:14px;display:flex;align-items:center;justify-content:center;border:1.5px solid #1a1a2e"
                                          title="Tài khoản đã xác minh">
                                        <i class="fa-solid fa-check" style="font-size:.45rem;color:#fff"></i>
                                    </span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="fw-semibold text-white text-truncate" style="max-width:180px">{{ $artist->name }}</span>
                                    @if($artist->isArtistVerified())
                                        <i class="fa-solid fa-circle-check" style="color:#1d9bf0;font-size:.75rem" title="Đã xác minh chính thức"></i>
                                    @endif
                                </div>
                                <div class="small text-muted text-truncate" style="max-width:180px">{{ $artist->email }}</div>
                                @if($artist->phone)
                                    <div class="small text-muted">{{ $artist->phone }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($artist->isArtistVerified())
                            <span class="badge rounded-pill px-2 py-1"
                                  style="background:rgba(29,155,240,.12);color:#7dd3fc;border:1px solid rgba(29,155,240,.3);font-size:.72rem">
                                <i class="fa-solid fa-circle-check me-1"></i>Tick xanh
                            </span>
                        @else
                            <span class="badge rounded-pill px-2 py-1"
                                  style="background:rgba(107,114,128,.12);color:#9ca3af;border:1px solid rgba(107,114,128,.25);font-size:.72rem">
                                <i class="fa-regular fa-clock me-1"></i>Chưa xác minh
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($artist->status === 'Đang hoạt động')
                            <span class="badge rounded-pill px-2 py-1"
                                  style="background:rgba(34,197,94,.12);color:#86efac;border:1px solid rgba(34,197,94,.25);font-size:.72rem">
                                <i class="fa-solid fa-circle-check me-1"></i>Hoạt động
                            </span>
                        @else
                            <span class="badge rounded-pill px-2 py-1"
                                  style="background:rgba(239,68,68,.12);color:#fca5a5;border:1px solid rgba(239,68,68,.25);font-size:.72rem">
                                <i class="fa-solid fa-ban me-1"></i>{{ $artist->status }}
                            </span>
                        @endif
                    </td>
                    <td class="text-muted small">
                        @if($artist->artist_verified_at)
                            <span title="{{ $artist->artist_verified_at->format('d/m/Y H:i') }}">
                                {{ $artist->artist_verified_at->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end pe-3">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-ellipsis"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end mm-dropdown">

                                {{-- Khóa / Mở khóa --}}
                                <li>
                                    <form method="POST" action="{{ route('admin.artists.toggleStatus', $artist->id) }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item {{ $artist->status === 'Đang hoạt động' ? 'text-warning' : 'text-success' }}">
                                            @if($artist->status === 'Đang hoạt động')
                                                <i class="fa-solid fa-lock me-2"></i>Khóa tài khoản
                                            @else
                                                <i class="fa-solid fa-lock-open me-2"></i>Mở khóa
                                            @endif
                                        </button>
                                    </form>
                                </li>

                                {{-- Xác minh / Bỏ xác minh --}}
                                <li>
                                    <button class="dropdown-item {{ $artist->isArtistVerified() ? 'text-warning' : 'text-info' }}"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#verifyModal"
                                            data-artist-id="{{ $artist->id }}"
                                            data-artist-name="{{ $artist->name }}"
                                            data-is-verified="{{ $artist->isArtistVerified() ? '1' : '0' }}">
                                        @if($artist->isArtistVerified())
                                            <i class="fa-solid fa-circle-xmark me-2"></i>Bỏ xác minh
                                        @else
                                            <i class="fa-solid fa-circle-check me-2"></i>Cấp tick xanh
                                        @endif
                                    </button>
                                </li>

                                <li><hr class="dropdown-divider border-secondary"></li>

                                {{-- Thu hồi quyền nghệ sĩ --}}
                                <li>
                                    <button class="dropdown-item text-danger" type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#revokeModal"
                                            data-artist-id="{{ $artist->id }}"
                                            data-artist-name="{{ $artist->name }}">
                                        <i class="fa-solid fa-microphone-slash me-2"></i>Thu hồi vai trò
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="fa-solid fa-microphone fa-2x mb-3 opacity-25 d-block"></i>
                        Không tìm thấy nghệ sĩ nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($artists->hasPages())
    <div class="card-footer bg-transparent border-secondary border-opacity-25 py-3">
        {{ $artists->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- ─── Modal: Thu hồi vĩnh viễn quyền nghệ sĩ ─── --}}
<div class="modal fade" id="revokeModal" tabindex="-1" aria-labelledby="revokeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-danger border-opacity-50">
            <div class="modal-header border-danger border-opacity-25">
                <h6 class="modal-title text-white" id="revokeModalLabel">
                    <i class="fa-solid fa-skull-crossbones me-2 text-danger"></i>Thu hồi vĩnh viễn quyền Nghệ sĩ
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="revokeForm" action="">
                @csrf
                <div class="modal-body">
                    {{-- Cảnh báo --}}
                    <div class="alert alert-danger py-2 mb-3 small">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        <strong>Hành động này không thể hoàn tác và cấm vĩnh viễn vai trò Nghệ sĩ.</strong><br>
                        Bài hát và album hiện có được giữ nguyên theo trạng thái hiện tại.
                        User sẽ nhận được email thông báo.
                    </div>

                    <p class="text-muted small mb-3">
                        Nghệ sĩ: <strong class="text-white" id="revokeArtistName"></strong>
                    </p>

                    {{-- Lý do thu hồi --}}
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1" for="revokeReason">
                            Lý do thu hồi <span class="text-danger">*</span>
                        </label>
                        <textarea id="revokeReason" name="revoke_reason"
                                  class="form-control form-control-sm bg-dark border-secondary text-white"
                                  rows="3" placeholder="Nhập lý do cụ thể (tối thiểu 10 ký tự)"
                                  required minlength="10" maxlength="500"></textarea>
                    </div>

                    {{-- Xác nhận mật khẩu admin --}}
                    <div class="mb-0">
                        <label class="form-label text-muted small mb-1" for="revokeAdminPassword">
                            Mật khẩu quản trị viên <span class="text-danger">*</span>
                        </label>
                        <input type="password" id="revokeAdminPassword" name="admin_password"
                               class="form-control form-control-sm bg-dark border-secondary text-white"
                               placeholder="Nhập mật khẩu để xác nhận hành động"
                               required autocomplete="current-password">
                    </div>
                </div>
                <div class="modal-footer border-danger border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fa-solid fa-skull-crossbones me-1"></i>Xác nhận thu hồi vĩnh viễn
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─── Modal: Cấp / Bỏ xác minh nghệ sĩ ─── --}}
<div class="modal fade" id="verifyModal" tabindex="-1" aria-labelledby="verifyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white" id="verifyModalLabel">
                    <i id="verifyModalIcon" class="fa-solid fa-circle-check me-2 text-info"></i>Cấp tick xanh xác minh
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="verifyForm" action="">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-1">
                        Nghệ sĩ: <strong class="text-white" id="verifyArtistName"></strong>
                    </p>
                    <p class="text-muted small mb-0" id="verifyActionDesc"></p>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-info" id="verifyConfirmBtn">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('revokeModal').addEventListener('show.bs.modal', function (e) {
    const btn      = e.relatedTarget;
    const artistId = btn.dataset.artistId;
    const name     = btn.dataset.artistName;

    document.getElementById('revokeArtistName').textContent = name;
    document.getElementById('revokeForm').action =
        '{{ url("/admin/artists") }}/' + artistId + '/revoke';
});

document.getElementById('verifyModal').addEventListener('show.bs.modal', function (e) {
    const btn        = e.relatedTarget;
    const artistId   = btn.dataset.artistId;
    const name       = btn.dataset.artistName;
    const isVerified = btn.dataset.isVerified === '1';

    // Header
    const icon    = document.getElementById('verifyModalIcon');
    const heading = document.getElementById('verifyModalLabel');
    icon.className    = isVerified ? 'fa-solid fa-circle-xmark me-2 text-warning' : 'fa-solid fa-circle-check me-2 text-info';
    heading.lastChild.textContent = isVerified ? 'Bỏ xác minh nghệ sĩ' : 'Cấp tick xanh xác minh';

    // Body
    document.getElementById('verifyArtistName').textContent  = name;
    const actionDesc = document.getElementById('verifyActionDesc');
    if (isVerified) {
        actionDesc.innerHTML =
            'Huy hiệu <i class="fa-solid fa-circle-check text-info"></i> xác minh sẽ bị <strong class="text-warning">gỡ bỏ</strong> khỏi tài khoản nghệ sĩ này.';
    } else {
        actionDesc.innerHTML =
            'Huy hiệu <i class="fa-solid fa-circle-check text-info"></i> tick xanh sẽ được <strong class="text-info">cấp chính thức</strong> cho nghệ sĩ này.';
    }

    // Confirm button
    const confirmBtn = document.getElementById('verifyConfirmBtn');
    if (isVerified) {
        confirmBtn.className = 'btn btn-sm btn-warning';
        confirmBtn.textContent = 'Xác nhận bỏ xác minh';
    } else {
        confirmBtn.className = 'btn btn-sm btn-info';
        confirmBtn.textContent = 'Xác nhận cấp tick xanh';
    }

    document.getElementById('verifyForm').action =
        '{{ url("/admin/artists") }}/' + artistId + '/toggle-verify';
});
</script>
@endpush
