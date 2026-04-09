@extends('layouts.admin')

@section('title', 'Quản lý người dùng')
@section('page-title', 'Quản lý người dùng')
@section('page-subtitle', 'Xem, tạo, chỉnh sửa và quản lý tài khoản người dùng')

@push('styles')
<style>
.stat-card {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 14px;
    padding: 16px 20px;
    transition: border-color .2s;
}
.stat-card:hover { border-color: rgba(255,255,255,.16); }
.stat-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem; flex-shrink: 0;
}
.user-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    object-fit: cover; border: 1px solid rgba(255,255,255,.1); flex-shrink: 0;
}
.mm-dropdown {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 12px;
    min-width: 180px;
}
.mm-dropdown .dropdown-item {
    color: #cbd5e1; font-size: .82rem; padding: 7px 14px; border-radius: 8px;
}
.mm-dropdown .dropdown-item:hover { background: rgba(255,255,255,.06); color: #fff; }
.mm-dropdown .dropdown-item.text-danger:hover  { background: rgba(239,68,68,.1); }
.mm-dropdown .dropdown-item.text-warning:hover { background: rgba(234,179,8,.08); }
.mm-dropdown .dropdown-item.text-success:hover { background: rgba(34,197,94,.08); }
.mm-dropdown hr { border-color: rgba(255,255,255,.08); margin: 4px 0; }
</style>
@endpush

@section('content')

{{-- ───── Stat cards ───── --}}
<div class="row g-3 mb-4">
    @php
    $statCards = [
        ['label'=>'Tổng người dùng', 'value'=>$stats['total'],     'icon'=>'fa-users',             'bg'=>'rgba(99,102,241,.12)',  'color'=>'#818cf8'],
        ['label'=>'Miễn phí',        'value'=>$stats['free'],      'icon'=>'fa-user',              'bg'=>'rgba(99,102,241,.08)',  'color'=>'#a5b4fc'],
        ['label'=>'Premium',         'value'=>$stats['premium'],   'icon'=>'fa-crown',             'bg'=>'rgba(245,158,11,.12)', 'color'=>'#fbbf24'],
        ['label'=>'Nghệ sĩ',         'value'=>$stats['artist'],    'icon'=>'fa-microphone-lines',  'bg'=>'rgba(168,85,247,.12)', 'color'=>'#c084fc'],
        ['label'=>'Bị khóa',         'value'=>$stats['locked'],    'icon'=>'fa-ban',               'bg'=>'rgba(239,68,68,.12)',  'color'=>'#fca5a5'],
        ['label'=>'Mới tháng này',   'value'=>$stats['new_month'], 'icon'=>'fa-user-plus',         'bg'=>'rgba(34,197,94,.10)',  'color'=>'#86efac'],
    ];
    @endphp
    @foreach($statCards as $s)
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="stat-icon" style="background:{{ $s['bg'] }}">
                    <i class="fa-solid {{ $s['icon'] }}" style="color:{{ $s['color'] }}"></i>
                </div>
                <div class="fw-bold text-white" style="font-size:1.35rem">{{ number_format($s['value']) }}</div>
            </div>
            <div class="text-muted small">{{ $s['label'] }}</div>
        </div>
    </div>
    @endforeach

    {{-- Card chờ mở khóa (chỉ hiện khi có request pending) --}}
    @if(isset($stats['pending_unlock']) && $stats['pending_unlock'] > 0)
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.unlock-requests.index') }}" class="text-decoration-none">
            <div class="stat-card" style="border-color:rgba(251,191,36,.3)">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="stat-icon" style="background:rgba(251,191,36,.15)">
                        <i class="fa-solid fa-unlock" style="color:#fbbf24"></i>
                    </div>
                    <div class="fw-bold" style="font-size:1.35rem;color:#fbbf24">{{ $stats['pending_unlock'] }}</div>
                </div>
                <div class="small" style="color:#fbbf24">Chờ mở khóa</div>
            </div>
        </a>
    </div>
    @endif

    {{-- Card chờ xét duyệt nghệ sĩ (chỉ hiện khi có đơn pending) --}}
    @if(isset($stats['pending_artist']) && $stats['pending_artist'] > 0)
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.artist-registrations.index') }}" class="text-decoration-none">
            <div class="stat-card" style="border-color:rgba(192,132,252,.3)">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="stat-icon" style="background:rgba(192,132,252,.15)">
                        <i class="fa-solid fa-microphone-lines" style="color:#c084fc"></i>
                    </div>
                    <div class="fw-bold" style="font-size:1.35rem;color:#c084fc">{{ $stats['pending_artist'] }}</div>
                </div>
                <div class="small" style="color:#c084fc">Đăng ký NS</div>
            </div>
        </a>
    </div>
    @endif
</div>

{{-- ───── Filter bar ───── --}}
<div class="d-flex align-items-center gap-2 mb-0">
    <a href="{{ route('admin.users.create') }}" class="btn btn-sm ms-auto mb-2"
       style="background:rgba(52,211,153,.15);border:1px solid rgba(52,211,153,.3);color:#6ee7b7;white-space:nowrap">
        <i class="fa-solid fa-plus me-1"></i>Tạo mới
    </a>
</div>
<form method="GET" action="{{ route('admin.users.index') }}" class="filter-bar">
    <div class="filter-bar-inner">

        <div class="filter-field" style="flex:1;min-width:200px;">
            <label class="filter-label"><i class="fa-solid fa-magnifying-glass"></i>Tìm kiếm</label>
            <div class="filter-search-wrap">
                <i class="fa-solid fa-magnifying-glass filter-search-icon"></i>
                <input type="text" name="search" class="filter-input"
                       placeholder="Tên, email, số điện thoại..."
                       value="{{ $filters['search'] ?? '' }}">
            </div>
        </div>

        <div class="filter-field" style="min-width:145px;">
            <label class="filter-label"><i class="fa-solid fa-user-tag"></i>Loại tài khoản</label>
            <select name="role" class="filter-select">
                <option value="" {{ empty($filters['role']) ? 'selected' : '' }}>Tất cả</option>
                <option value="free"    {{ ($filters['role']??'')==='free'    ? 'selected' : '' }}>Miễn phí</option>
                <option value="premium" {{ ($filters['role']??'')==='premium' ? 'selected' : '' }}>Premium</option>
                <option value="artist"  {{ ($filters['role']??'')==='artist'  ? 'selected' : '' }}>Nghệ sĩ</option>
            </select>
        </div>

        <div class="filter-field" style="min-width:135px;">
            <label class="filter-label"><i class="fa-solid fa-toggle-on"></i>Trạng thái</label>
            <select name="status" class="filter-select">
                <option value="" {{ !isset($filters['status']) || $filters['status']==='' ? 'selected' : '' }}>Tất cả</option>
                <option value="Đang hoạt động" {{ ($filters['status']??'')==='Đang hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                <option value="Bị khóa"        {{ ($filters['status']??'')==='Bị khóa'        ? 'selected' : '' }}>Bị khóa</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="filter-btn-submit">
                <i class="fa-solid fa-filter"></i>Lọc
                @if(!empty($filters['search']) || !empty($filters['role']) || !empty($filters['status']))
                    <span class="filter-active-dot"></span>
                @endif
            </button>
            <a href="{{ route('admin.users.index') }}" class="filter-btn-reset" title="Xóa bộ lọc">
                <i class="fa-solid fa-xmark"></i>
            </a>
        </div>

    </div>
</form>

{{-- ───── Results summary ───── --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <span class="text-muted small">
        Tìm thấy <strong class="text-white">{{ $users->total() }}</strong> tài khoản
    </span>
    <span class="text-muted small">Trang {{ $users->currentPage() }} / {{ $users->lastPage() }}</span>
</div>

{{-- ───── Table ───── --}}
<div class="card bg-dark border-secondary border-opacity-25">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="border-secondary">
                    <th class="text-muted fw-normal small ps-3" style="width:46px">#</th>
                    <th class="text-muted fw-normal small">Người dùng</th>
                    <th class="text-muted fw-normal small">Loại</th>
                    <th class="text-muted fw-normal small">Trạng thái</th>
                    <th class="text-muted fw-normal small">Ngày tạo</th>
                    <th class="text-muted fw-normal small text-end pe-3">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                @php
                $avatarUrl = ($user->avatar && $user->avatar !== '/storage/avt.jpg')
                    ? asset($user->avatar)
                    : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=6366f1&color=fff&size=40';
                $roleNamesRaw = $user->getRoleNames();
                $roleNames = is_array($roleNamesRaw)
                    ? $roleNamesRaw
                    : collect($roleNamesRaw)->all();
                $selectedRole = $user->isArtist()
                    ? 'artist'
                    : ($user->isPremium() ? 'premium' : 'free');
                @endphp
                <tr class="border-secondary border-opacity-25">
                    <td class="ps-3 text-muted small">{{ $user->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $avatarUrl }}" class="user-avatar" alt="{{ $user->name }}">
                            <div class="min-w-0">
                                <a href="{{ route('admin.users.show', $user->id) }}"
                                   class="fw-semibold text-white text-decoration-none text-truncate d-block"
                                   style="max-width:200px">{{ $user->name }}</a>
                                <div class="small text-muted text-truncate" style="max-width:200px">{{ $user->email }}</div>
                                @if($user->phone)
                                    <div class="small text-muted" style="font-size:.72rem">{{ $user->phone }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @if(in_array('admin', $roleNames, true))
                                <span class="badge rounded-pill px-2 py-1"
                                    style="background:rgba(239,68,68,.15);color:#fca5a5;border:1px solid rgba(239,68,68,.2);font-size:.72rem">
                                    <i class="fa-solid fa-shield-halved me-1"></i>Admin
                                </span>
                            @endif
                            @if(in_array('artist', $roleNames, true))
                                <span class="badge rounded-pill px-2 py-1"
                                    style="background:rgba(168,85,247,.15);color:#c084fc;border:1px solid rgba(168,85,247,.2);font-size:.72rem">
                                    <i class="fa-solid fa-microphone-lines me-1"></i>Nghệ sĩ
                                </span>
                            @endif
                            @if(in_array('premium', $roleNames, true))
                                <span class="badge rounded-pill px-2 py-1"
                                    style="background:rgba(245,158,11,.15);color:#fbbf24;border:1px solid rgba(245,158,11,.2);font-size:.72rem">
                                    <i class="fa-solid fa-crown me-1"></i>Premium
                                </span>
                            @endif
                            @if(in_array('free', $roleNames, true))
                                <span class="badge rounded-pill px-2 py-1"
                                    style="background:rgba(99,102,241,.12);color:#818cf8;border:1px solid rgba(99,102,241,.2);font-size:.72rem">
                                    <i class="fa-solid fa-user me-1"></i>Miễn phí
                                </span>
                            @endif
                        </div>
                    </td>
                    <td>
                        @if($user->status === 'Đang hoạt động')
                        <span class="badge rounded-pill px-2 py-1"
                              style="background:rgba(34,197,94,.12);color:#86efac;border:1px solid rgba(34,197,94,.25);font-size:.72rem">
                            <i class="fa-solid fa-circle-check me-1"></i>Hoạt động
                        </span>
                        @else
                        <span class="badge rounded-pill px-2 py-1"
                              style="background:rgba(239,68,68,.12);color:#fca5a5;border:1px solid rgba(239,68,68,.25);font-size:.72rem"
                              @if($user->lock_reason) title="{{ $user->lock_reason }}" data-bs-toggle="tooltip" @endif>
                            <i class="fa-solid fa-ban me-1"></i>Bị khóa
                        </span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $user->created_at->format('d/m/Y') }}</td>
                    <td class="text-end pe-3">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('admin.users.show', $user->id) }}"
                               class="btn btn-sm btn-outline-secondary" title="Chi tiết">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.users.edit', $user->id) }}"
                               class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                        style="padding-left:8px;padding-right:8px">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end mm-dropdown">
                                    {{-- Khóa → modal | Mở khóa → trực tiếp --}}
                                    @if($user->status === 'Đang hoạt động')
                                    <li>
                                        <button type="button"
                                                class="dropdown-item text-warning"
                                                data-bs-toggle="modal" data-bs-target="#lockModal"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}">
                                            <i class="fa-solid fa-lock me-2"></i>Khóa tài khoản
                                        </button>
                                    </li>
                                    @else
                                    <li>
                                        <form method="POST" action="{{ route('admin.users.toggleStatus', $user->id) }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-success">
                                                <i class="fa-solid fa-lock-open me-2"></i>Mở khóa
                                            </button>
                                        </form>
                                    </li>
                                    @endif

                                    @if(!$user->isAdmin())
                                    <li>
                                        <button class="dropdown-item" type="button"
                                                data-bs-toggle="modal" data-bs-target="#changeRoleModal"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                data-current-role="{{ $selectedRole }}">
                                            <i class="fa-solid fa-arrows-rotate me-2"></i>Đổi loại tài khoản
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider border-secondary"></li>
                                    <li>
                                        <button class="dropdown-item text-danger" type="button"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}">
                                            <i class="fa-solid fa-trash me-2"></i>Xóa tài khoản
                                        </button>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="fa-solid fa-users fa-2x mb-3 opacity-25 d-block"></i>
                        Không tìm thấy người dùng nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="card-footer bg-transparent border-secondary border-opacity-25 py-3">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- ───── Modal: Khóa tài khoản ───── --}}
<div class="modal fade" id="lockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-lock me-2 text-warning"></i>Khóa tài khoản
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="lockForm" action="">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Bạn đang khóa tài khoản: <strong class="text-white" id="lockUserName"></strong>
                    </p>
                    <label class="form-label text-muted small">
                        Lý do khóa tài khoản <span class="text-danger">*</span>
                    </label>
                    <textarea name="lock_reason" id="lockReasonInput" rows="3"
                              class="form-control bg-dark border-secondary text-white"
                              placeholder="Ví dụ: Vi phạm điều khoản sử dụng, hành vi spam, nội dung không phù hợp..."
                              maxlength="500" required></textarea>
                    <div class="form-text text-muted small mt-1">
                        <span id="lockReasonCount">0</span>/500 ký tự. Lý do này sẽ được gửi email đến người dùng.
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-warning">
                        <i class="fa-solid fa-lock me-1"></i>Xác nhận khóa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ───── Modal: Đổi loại tài khoản ───── --}}
<div class="modal fade" id="changeRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-arrows-rotate me-2" style="color:#818cf8"></i>Đổi loại tài khoản
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="changeRoleForm" action="">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Tài khoản: <strong class="text-white" id="changeRoleName"></strong>
                    </p>
                    <label class="form-label text-muted small">Loại tài khoản mới</label>
                    <select name="role" id="changeRoleSelect" class="form-select bg-dark border-secondary text-white">
                        <option value="free">Thính giả miễn phí</option>
                        <option value="premium">Thính giả Premium</option>
                        <option value="artist">Nghệ sĩ</option>
                    </select>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ───── Modal: Xóa tài khoản ───── --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-trash me-2 text-danger"></i>Xóa tài khoản
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="deleteForm" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p class="text-muted small mb-0">
                        Bạn có chắc muốn xóa tài khoản
                        <strong class="text-white" id="deleteUserName"></strong>?
                        Hành động này <strong class="text-danger">không thể hoàn tác</strong>.
                    </p>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-danger">Xóa tài khoản</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Lock modal — nhập lý do trước khi khóa
document.getElementById('lockModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('lockUserName').textContent = btn.dataset.userName;
    document.getElementById('lockForm').action =
        '{{ url("/admin/users") }}/' + btn.dataset.userId + '/toggle-status';
    document.getElementById('lockReasonInput').value = '';
    document.getElementById('lockReasonCount').textContent = '0';
});
document.getElementById('lockReasonInput').addEventListener('input', function () {
    document.getElementById('lockReasonCount').textContent = this.value.length;
});

// Change Role modal
document.getElementById('changeRoleModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('changeRoleName').textContent = btn.dataset.userName;
    document.getElementById('changeRoleSelect').value     = btn.dataset.currentRole;
    document.getElementById('changeRoleForm').action      =
        '{{ url("/admin/users") }}/' + btn.dataset.userId + '/change-role';
});

// Delete modal
document.getElementById('deleteModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('deleteUserName').textContent = btn.dataset.userName;
    document.getElementById('deleteForm').action =
        '{{ url("/admin/users") }}/' + btn.dataset.userId;
});

// Tooltip cho lý do khóa
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el, { trigger: 'hover' });
});
</script>
@endpush

