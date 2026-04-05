@extends('layouts.admin')

@section('title', 'Chi tiết người dùng – ' . $user->name)
@section('page-title', 'Chi tiết người dùng')
@section('page-subtitle', 'Xem thông tin đầy đủ và lịch sử hoạt động của tài khoản')

@push('styles')
<style>
.info-row { padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,.06); }
.info-row:last-child { border-bottom: none; }
.info-label { color: #64748b; font-size: .78rem; min-width: 140px; }
.info-value { color: #e2e8f0; font-size: .85rem; }
.history-dot {
    width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 4px;
}
.history-line {
    width: 1px; background: rgba(255,255,255,.07); margin: 2px 0 2px 4px; flex-shrink: 0;
}
.action-btn-group .btn { font-size: .78rem; padding: 5px 12px; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <i class="fa-solid fa-circle-check me-2"></i>{!! session('success') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>{!! session('error') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Back --}}
<div class="mb-3">
    <a href="{{ route('admin.users.index') }}" class="text-muted small text-decoration-none">
        <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
    </a>
</div>

@php
$avatarUrl = ($user->avatar && $user->avatar !== '/storage/avt.jpg')
    ? asset($user->avatar)
    : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=6366f1&color=fff&size=120';

$roleBadgeMap = [
    'admin'   => ['bg'=>'rgba(239,68,68,.15)',  'color'=>'#fca5a5', 'icon'=>'fa-shield-halved',    'label'=>'Admin'],
    'artist'  => ['bg'=>'rgba(168,85,247,.15)','color'=>'#c084fc', 'icon'=>'fa-microphone-lines', 'label'=>'Nghệ sĩ'],
    'premium' => ['bg'=>'rgba(245,158,11,.15)','color'=>'#fbbf24', 'icon'=>'fa-crown',            'label'=>'Premium'],
    'free'    => ['bg'=>'rgba(99,102,241,.12)','color'=>'#818cf8', 'icon'=>'fa-user',             'label'=>'Miễn phí'],
];
$roleBadges = collect($user->getRoleNames())
    ->map(fn ($roleSlug) => $roleBadgeMap[$roleSlug] ?? null)
    ->filter()
    ->values();

$selectedRole = $user->isArtist()
    ? 'artist'
    : ($user->isPremium() ? 'premium' : 'free');
@endphp

<div class="row g-4">

    {{-- ── LEFT: Profile card ── --}}
    <div class="col-lg-4">
        <div class="rounded-4 p-4 mb-4 text-center"
             style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)">
            <img src="{{ $avatarUrl }}"
                 class="rounded-circle mb-3"
                 width="90" height="90"
                 style="object-fit:cover;border:3px solid rgba(255,255,255,.12)"
                 alt="{{ $user->name }}">
            <h5 class="text-white fw-bold mb-1">{{ $user->name }}</h5>
            <div class="text-muted small mb-3">{{ $user->email }}</div>
            <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
                {{-- Role badges --}}
                @foreach($roleBadges as $roleBadge)
                    <span class="badge rounded-pill px-3 py-1"
                        style="background:{{ $roleBadge['bg'] }};color:{{ $roleBadge['color'] }};border:1px solid {{ $roleBadge['color'] }}33">
                        <i class="fa-solid {{ $roleBadge['icon'] }} me-1"></i>{{ $roleBadge['label'] }}
                    </span>
                @endforeach
                {{-- Status badge --}}
                @if($user->status === 'Đang hoạt động')
                <span class="badge rounded-pill px-3 py-1"
                      style="background:rgba(34,197,94,.12);color:#86efac;border:1px solid rgba(34,197,94,.25)">
                    <i class="fa-solid fa-circle-check me-1"></i>Hoạt động
                </span>
                @else
                <span class="badge rounded-pill px-3 py-1"
                      style="background:rgba(239,68,68,.12);color:#fca5a5;border:1px solid rgba(239,68,68,.25)">
                    <i class="fa-solid fa-ban me-1"></i>{{ $user->status }}
                </span>
                @endif
            </div>

            {{-- Action buttons --}}
            @if(!$user->isAdmin())
            <div class="d-flex flex-column gap-2 action-btn-group">
                <a href="{{ route('admin.users.edit', $user->id) }}"
                   class="btn btn-primary btn-sm w-100">
                    <i class="fa-solid fa-pen me-2"></i>Chỉnh sửa thông tin
                </a>

                @if($user->status === 'Đang hoạt động')
                {{-- Nút khóa → mở modal nhập lý do --}}
                <button type="button"
                        class="btn btn-sm btn-outline-warning w-100"
                        data-bs-toggle="modal" data-bs-target="#lockModal"
                        data-user-id="{{ $user->id }}"
                        data-user-name="{{ $user->name }}">
                    <i class="fa-solid fa-lock me-2"></i>Khóa tài khoản
                </button>
                @else
                {{-- Mở khóa trực tiếp --}}
                <form method="POST" action="{{ route('admin.users.toggleStatus', $user->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-success w-100">
                        <i class="fa-solid fa-lock-open me-2"></i>Mở khóa tài khoản
                    </button>
                </form>
                @endif

                <button type="button" class="btn btn-sm btn-outline-secondary"
                        data-bs-toggle="modal" data-bs-target="#changeRoleModal">
                    <i class="fa-solid fa-arrows-rotate me-2"></i>Đổi loại tài khoản
                </button>

                <button type="button" class="btn btn-sm btn-outline-danger"
                        data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fa-solid fa-trash me-2"></i>Xóa tài khoản
                </button>
            </div>
            @endif
        </div>

        {{-- Quick info --}}
        <div class="rounded-4 p-4"
             style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)">
            <h6 class="text-white fw-semibold mb-3">
                <i class="fa-solid fa-circle-info me-2" style="color:#818cf8"></i>Thông tin nhanh
            </h6>
            <div class="info-row d-flex gap-3">
                <span class="info-label">ID</span>
                <span class="info-value">#{{ $user->id }}</span>
            </div>
            <div class="info-row d-flex gap-3">
                <span class="info-label">Số điện thoại</span>
                <span class="info-value">{{ $user->phone ?: '—' }}</span>
            </div>
            <div class="info-row d-flex gap-3">
                <span class="info-label">Ngày sinh</span>
                <span class="info-value">{{ $user->birthday ? $user->birthday->format('d/m/Y') : '—' }}</span>
            </div>
            <div class="info-row d-flex gap-3">
                <span class="info-label">Giới tính</span>
                <span class="info-value">{{ $user->gender ?: '—' }}</span>
            </div>
            <div class="info-row d-flex gap-3">
                <span class="info-label">Email xác minh</span>
                <span class="info-value">
                    @if($user->email_verified_at)
                        <span class="text-success" style="font-size:.8rem">
                            <i class="fa-solid fa-circle-check me-1"></i>{{ $user->email_verified_at->format('d/m/Y') }}
                        </span>
                    @else
                        <span class="text-warning" style="font-size:.8rem">
                            <i class="fa-solid fa-clock me-1"></i>Chưa xác minh
                        </span>
                    @endif
                </span>
            </div>
            <div class="info-row d-flex gap-3">
                <span class="info-label">Ngày tạo</span>
                <span class="info-value">{{ $user->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row d-flex gap-3">
                <span class="info-label">Cập nhật</span>
                <span class="info-value">{{ $user->updated_at->format('d/m/Y H:i') }}</span>
            </div>
            @if($user->lock_reason)
            <div class="info-row d-flex gap-3">
                <span class="info-label text-danger">Lý do khóa</span>
                <span class="info-value text-warning small">{{ $user->lock_reason }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- ── RIGHT: History ── --}}
    <div class="col-lg-8">
        <div class="rounded-4 p-4"
             style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h6 class="text-white fw-semibold mb-0">
                    <i class="fa-solid fa-clock-rotate-left me-2" style="color:#818cf8"></i>
                    Lịch sử tài khoản
                    <span class="badge ms-2 rounded-pill"
                          style="background:rgba(99,102,241,.2);color:#818cf8;font-size:.7rem">
                        {{ $history->count() }}
                    </span>
                </h6>
            </div>

            @if($history->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fa-solid fa-clock-rotate-left fa-2x mb-3 opacity-25 d-block"></i>
                Chưa có lịch sử hoạt động nào.
            </div>
            @else
            <div class="position-relative">
                @foreach($history as $i => $h)
                @php
                $isAdmin = str_starts_with($h->action, '[Admin]');
                $dotColor = $isAdmin ? '#f59e0b' : '#818cf8';
                $isLast   = $i === $history->count() - 1;
                @endphp
                <div class="d-flex gap-3 {{ $isLast ? '' : 'mb-1' }}">
                    {{-- Timeline spine --}}
                    <div class="d-flex flex-column align-items-center" style="width:12px">
                        <div class="history-dot" style="background:{{ $dotColor }}"></div>
                        @if(!$isLast)
                        <div class="history-line flex-grow-1" style="min-height:24px"></div>
                        @endif
                    </div>
                    {{-- Content --}}
                    <div class="flex-grow-1 pb-3" style="{{ $isLast ? '' : 'border-bottom:1px solid rgba(255,255,255,.05)' }}">
                        <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                            <div>
                                <span class="text-white small" style="font-size:.83rem">
                                    {{ $isAdmin ? substr($h->action, 8) : $h->action }}
                                </span>
                                @if($isAdmin)
                                <span class="badge ms-1 rounded-pill"
                                      style="background:rgba(245,158,11,.15);color:#fbbf24;font-size:.65rem">
                                    Admin
                                </span>
                                @endif
                            </div>
                            <span class="text-muted" style="font-size:.7rem;white-space:nowrap">
                                {{ $h->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        <div class="mt-1 d-flex align-items-center gap-3 flex-wrap">
                            <span class="text-muted" style="font-size:.72rem">
                                <i class="fa-solid fa-circle-dot me-1" style="color:{{ $h->status === 'Đang hoạt động' ? '#86efac' : '#fca5a5' }}"></i>
                                {{ $h->status }}
                            </span>
                            @if($h->creator)
                            <span class="text-muted" style="font-size:.72rem">
                                <i class="fa-solid fa-user me-1"></i>{{ $h->creator->name }}
                            </span>
                            @endif
                            <span class="text-muted" style="font-size:.7rem">
                                {{ $h->created_at->diffForHumans() }}
                            </span>
                        </div>
                        @if($h->lock_reason)
                        <div class="mt-1 px-2 py-1 rounded-2" style="background:rgba(239,68,68,.08);border-left:2px solid rgba(239,68,68,.4)">
                            <span class="text-muted" style="font-size:.72rem">
                                <i class="fa-solid fa-lock me-1" style="color:#fca5a5"></i>
                                <strong style="color:#fca5a5">Lý do:</strong> {{ $h->lock_reason }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    @if($subscriptions->isNotEmpty() || $artistRegistrations->isNotEmpty())
    <div class="col-12 mt-4">
        <div class="row g-4">
            <div class="col-12 col-xl-6">
                <div class="rounded-4 p-4 h-100" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="text-white fw-semibold mb-0">
                            <i class="fa-solid fa-crown me-2" style="color:#fbbf24"></i>Lịch sử đăng ký Premium
                        </h6>
                        <span class="badge rounded-pill bg-secondary">{{ $subscriptions->count() }}</span>
                    </div>

                    @if($subscriptions->isEmpty())
                    <div class="text-muted small">Chưa có lượt đăng ký Premium nào.</div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-dark table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Gói</th>
                                    <th class="text-end">Thanh toán</th>
                                    <th class="text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subscriptions as $sub)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-white">{{ $sub->vip?->title ?? '—' }}</div>
                                        <div class="text-muted small">{{ $sub->start_date?->format('d/m/Y') }} → {{ $sub->end_date?->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="text-end text-warning fw-semibold">{{ number_format($sub->amount_paid) }} ₫</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $sub->statusColor() }}">{{ $sub->statusLabel() }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="rounded-4 p-4 h-100" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="text-white fw-semibold mb-0">
                            <i class="fa-solid fa-microphone-lines me-2" style="color:#c084fc"></i>Lịch sử đăng ký Nghệ sĩ
                        </h6>
                        <span class="badge rounded-pill bg-secondary">{{ $artistRegistrations->count() }}</span>
                    </div>

                    @if($artistRegistrations->isEmpty())
                    <div class="text-muted small">Chưa có đơn đăng ký Nghệ sĩ nào.</div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-dark table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Nghệ danh</th>
                                    <th class="text-end">Thanh toán</th>
                                    <th class="text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($artistRegistrations as $reg)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-white">{{ $reg->artist_name }}</div>
                                        <div class="text-muted small">{{ $reg->package?->name ?? '—' }}</div>
                                    </td>
                                    <td class="text-end text-warning fw-semibold">{{ number_format($reg->amount_paid) }} ₫</td>
                                    <td class="text-center">
                                        <span class="badge {{ $reg->status === 'approved' ? 'bg-success' : ($reg->status === 'pending_review' ? 'bg-info text-dark' : ($reg->status === 'pending_payment' ? 'bg-warning text-dark' : 'bg-danger')) }}">
                                            {{ $reg->statusLabel() }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- ───── Modal: Đổi loại ───── --}}
<div class="modal fade" id="changeRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-arrows-rotate me-2" style="color:#818cf8"></i>Đổi loại tài khoản
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.users.changeRole', $user->id) }}">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3">Tài khoản: <strong class="text-white">{{ $user->name }}</strong></p>
                    <label class="form-label text-muted small">Loại tài khoản mới</label>
                    <select name="role" class="form-select bg-dark border-secondary text-white">
                        <option value="free"    {{ $selectedRole==='free'    ? 'selected':'' }}>Thính giả miễn phí</option>
                        <option value="premium" {{ $selectedRole==='premium' ? 'selected':'' }}>Thính giả Premium</option>
                        <option value="artist"  {{ $selectedRole==='artist'  ? 'selected':'' }}>Nghệ sĩ</option>
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
            <form method="POST" id="lockFormShow" action="{{ route('admin.users.toggleStatus', $user->id) }}">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Bạn đang khóa tài khoản: <strong class="text-white">{{ $user->name }}</strong>
                    </p>
                    <label class="form-label text-muted small">
                        Lý do khóa tài khoản <span class="text-danger">*</span>
                    </label>
                    <textarea name="lock_reason" id="lockReasonShowInput" rows="3"
                              class="form-control bg-dark border-secondary text-white"
                              placeholder="Ví dụ: Vi phạm điều khoản sử dụng, hành vi spam..."
                              maxlength="500" required></textarea>
                    <div class="form-text text-muted small mt-1">
                        <span id="lockReasonShowCount">0</span>/500 ký tự. Lý do sẽ được gửi email đến người dùng.
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

{{-- ───── Modal: Xóa ───── --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white"><i class="fa-solid fa-trash me-2 text-danger"></i>Xóa tài khoản</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p class="text-muted small mb-0">
                        Bạn có chắc muốn xóa tài khoản
                        <strong class="text-white">{{ $user->name }}</strong>?
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
const lockReasonShowInput = document.getElementById('lockReasonShowInput');
if (lockReasonShowInput) {
    lockReasonShowInput.addEventListener('input', function () {
        document.getElementById('lockReasonShowCount').textContent = this.value.length;
    });
}
</script>
@endpush
