@extends('layouts.admin')

@section('title', 'Yêu cầu mở khóa tài khoản')
@section('page-title', 'Yêu cầu mở khóa')
@section('page-subtitle', 'Xem xét và xử lý các yêu cầu mở khóa từ người dùng')

@push('styles')
<style>
.req-card {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 14px;
    padding: 20px;
    transition: border-color .2s;
}
.req-card:hover { border-color: rgba(255,255,255,.14); }
.req-card.pending  { border-left: 3px solid #fbbf24; }
.req-card.approved { border-left: 3px solid #4ade80; }
.req-card.rejected { border-left: 3px solid #f87171; }
.user-avatar-sm {
    width: 36px; height: 36px; border-radius: 50%;
    object-fit: cover; flex-shrink: 0;
}
.content-box {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 8px;
    padding: 12px 14px;
    font-size: .85rem;
    line-height: 1.6;
    white-space: pre-wrap;
    word-break: break-word;
}
.tab-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 16px; border-radius: 20px;
    font-size: .82rem; font-weight: 500;
    border: 1px solid rgba(255,255,255,.1);
    text-decoration: none; color: #94a3b8;
    transition: all .15s;
}
.tab-pill:hover, .tab-pill.active { color: #fff; border-color: rgba(255,255,255,.28); background: rgba(255,255,255,.07); }
.tab-pill.active.pending-tab  { background:rgba(251,191,36,.12); border-color:rgba(251,191,36,.4); color:#fbbf24; }
.tab-pill.active.approved-tab { background:rgba(74,222,128,.1);  border-color:rgba(74,222,128,.4); color:#4ade80; }
.tab-pill.active.rejected-tab { background:rgba(248,113,113,.1); border-color:rgba(248,113,113,.4); color:#f87171; }
</style>
@endpush

@section('content')

{{-- Flash --}}
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

{{-- Tabs --}}
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('admin.unlock-requests.index', ['status' => 'pending']) }}"
       class="tab-pill pending-tab {{ $status === 'pending' ? 'active' : '' }}">
        <i class="fa-solid fa-clock"></i>Chờ xử lý
        @if($counts['pending'] > 0)
        <span class="badge rounded-pill" style="background:#fbbf24;color:#000;font-size:.72rem">{{ $counts['pending'] }}</span>
        @endif
    </a>
    <a href="{{ route('admin.unlock-requests.index', ['status' => 'approved']) }}"
       class="tab-pill approved-tab {{ $status === 'approved' ? 'active' : '' }}">
        <i class="fa-solid fa-circle-check"></i>Đã chấp thuận
        <span class="badge rounded-pill bg-secondary text-white" style="font-size:.72rem">{{ $counts['approved'] }}</span>
    </a>
    <a href="{{ route('admin.unlock-requests.index', ['status' => 'rejected']) }}"
       class="tab-pill rejected-tab {{ $status === 'rejected' ? 'active' : '' }}">
        <i class="fa-solid fa-ban"></i>Đã từ chối
        <span class="badge rounded-pill bg-secondary text-white" style="font-size:.72rem">{{ $counts['rejected'] }}</span>
    </a>
    <a href="{{ route('admin.unlock-requests.index', ['status' => 'all']) }}"
       class="tab-pill {{ $status === 'all' ? 'active' : '' }}">
        <i class="fa-solid fa-list"></i>Tất cả
    </a>
</div>

{{-- Request list --}}
@forelse($requests as $req)
@php
$user = $req->user;
$avatarUrl = ($user && $user->avatar && $user->avatar !== '/storage/avt.jpg')
    ? asset($user->avatar)
    : 'https://ui-avatars.com/api/?name='.urlencode($user?->name ?? '?').'&background=6366f1&color=fff&size=40';
@endphp
<div class="req-card {{ $req->status }} mb-3">
    <div class="d-flex align-items-start gap-3">
        {{-- Avatar --}}
        <img src="{{ $avatarUrl }}" class="user-avatar-sm mt-1" alt="{{ $user?->name }}">

        {{-- Main content --}}
        <div class="flex-grow-1 min-w-0">
            {{-- Header --}}
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <a href="{{ $user ? route('admin.users.show', $user->id) : '#' }}"
                   class="fw-semibold text-white text-decoration-none">
                    {{ $user?->name ?? 'Tài khoản đã bị xóa' }}
                </a>
                <span class="text-muted small">{{ $user?->email }}</span>

                {{-- Status badge --}}
                @if($req->isPending())
                <span class="badge rounded-pill" style="background:rgba(251,191,36,.15);color:#fbbf24;border:1px solid rgba(251,191,36,.3);font-size:.72rem">
                    <i class="fa-solid fa-clock me-1"></i>Chờ xử lý
                </span>
                @elseif($req->isApproved())
                <span class="badge rounded-pill" style="background:rgba(74,222,128,.12);color:#4ade80;border:1px solid rgba(74,222,128,.3);font-size:.72rem">
                    <i class="fa-solid fa-circle-check me-1"></i>Đã chấp thuận
                </span>
                @else
                <span class="badge rounded-pill" style="background:rgba(248,113,113,.12);color:#f87171;border:1px solid rgba(248,113,113,.3);font-size:.72rem">
                    <i class="fa-solid fa-ban me-1"></i>Đã từ chối
                </span>
                @endif

                {{-- User account status --}}
                @if($user)
                @if($user->status === 'Bị khóa')
                <span class="badge rounded-pill" style="background:rgba(239,68,68,.1);color:#fca5a5;font-size:.72rem">
                    <i class="fa-solid fa-lock me-1"></i>Tài khoản đang bị khóa
                </span>
                @else
                <span class="badge rounded-pill" style="background:rgba(34,197,94,.1);color:#86efac;font-size:.72rem">
                    <i class="fa-solid fa-unlock me-1"></i>Đã mở khóa
                </span>
                @endif
                @endif

                <span class="text-muted ms-auto small">{{ $req->created_at->format('d/m/Y H:i') }}</span>
            </div>

            {{-- Lý do khóa (nếu có) --}}
            @if($user?->lock_reason)
            <div class="mb-2 small text-muted">
                <i class="fa-solid fa-lock me-1" style="color:#fca5a5"></i>
                <span class="text-muted">Lý do bị khóa:</span>
                <span class="text-white">{{ $user->lock_reason }}</span>
            </div>
            @endif

            {{-- Nội dung khiếu nại --}}
            <div class="content-box text-muted mb-3">{{ $req->content }}</div>

            {{-- Admin note (if handled) --}}
            @if($req->admin_note)
            <div class="mb-3 p-2 rounded-2"
                 style="background:rgba({{ $req->isApproved() ? '74,222,128' : '248,113,113' }},.06);border:1px solid rgba({{ $req->isApproved() ? '74,222,128' : '248,113,113' }},.2)">
                <div class="small mb-1" style="color:{{ $req->isApproved() ? '#4ade80' : '#f87171' }}">
                    <i class="fa-solid fa-user-shield me-1"></i>Phản hồi của admin:
                </div>
                <div class="text-muted small">{{ $req->admin_note }}</div>
            </div>
            @endif

            {{-- Actions (chỉ khi pending) --}}
            @if($req->isPending())
            <div class="d-flex flex-wrap gap-2">
                {{-- Approve --}}
                <button type="button" class="btn btn-sm btn-success"
                        data-bs-toggle="modal" data-bs-target="#approveModal"
                        data-req-id="{{ $req->id }}"
                        data-user-name="{{ $user?->name }}">
                    <i class="fa-solid fa-check me-1"></i>Chấp thuận
                </button>
                {{-- Reject --}}
                <button type="button" class="btn btn-sm btn-outline-danger"
                        data-bs-toggle="modal" data-bs-target="#rejectModal"
                        data-req-id="{{ $req->id }}"
                        data-user-name="{{ $user?->name }}">
                    <i class="fa-solid fa-ban me-1"></i>Từ chối
                </button>
            </div>
            @else
            <div class="small text-muted">
                Xử lý lúc {{ $req->handled_at?->format('d/m/Y H:i') }}
            </div>
            @endif
        </div>
    </div>
</div>
@empty
<div class="text-center py-5 text-muted">
    <i class="fa-solid fa-inbox fa-2x d-block mb-3 opacity-25"></i>
    @if($status === 'pending')
        Không có yêu cầu nào đang chờ xử lý.
    @elseif($status === 'approved')
        Chưa có yêu cầu nào được chấp thuận.
    @elseif($status === 'rejected')
        Chưa có yêu cầu nào bị từ chối.
    @else
        Không có yêu cầu mở khóa nào.
    @endif
</div>
@endforelse

@if($requests->hasPages())
<div class="mt-4">{{ $requests->links('pagination::bootstrap-5') }}</div>
@endif

{{-- ───── Modal: Chấp thuận ───── --}}
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-circle-check me-2 text-success"></i>Chấp thuận yêu cầu mở khóa
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="approveForm" action="">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Chấp thuận yêu cầu của <strong class="text-white" id="approveUserName"></strong>.
                        Tài khoản sẽ được mở khóa và người dùng sẽ nhận được email thông báo.
                    </p>
                    <label class="form-label text-muted small">Ghi chú cho người dùng (tuỳ chọn)</label>
                    <textarea name="admin_note" rows="3"
                              class="form-control bg-dark border-secondary text-white"
                              placeholder="Ví dụ: Yêu cầu của bạn đã được xét duyệt. Vui lòng tuân thủ điều khoản dịch vụ trong tương lai."
                              maxlength="500"></textarea>
                    
                    <label class="form-label text-muted small mt-2">
                        Xác nhận mật khẩu của bạn <span class="text-danger">*</span>
                    </label>
                    <input type="password" name="password"
                           class="form-control bg-dark border-secondary text-white"
                           placeholder="Nhập mật khẩu admin..." required>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fa-solid fa-check me-1"></i>Xác nhận chấp thuận
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ───── Modal: Từ chối ───── --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-ban me-2 text-danger"></i>Từ chối yêu cầu mở khóa
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="rejectForm" action="">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Từ chối yêu cầu của <strong class="text-white" id="rejectUserName"></strong>.
                    </p>
                    <label class="form-label text-muted small">
                        Lý do từ chối <span class="text-danger">*</span>
                    </label>
                    <textarea name="admin_note" rows="3"
                              class="form-control bg-dark border-secondary text-white"
                              placeholder="Ví dụ: Yêu cầu của bạn chưa cung cấp đủ thông tin. Tài khoản vẫn sẽ bị khóa do vi phạm..."
                              maxlength="500" required></textarea>
                    <div class="form-text text-muted small mt-1">
                        Lý do này sẽ được gửi email đến người dùng.
                    </div>
                    
                    <label class="form-label text-muted small mt-2">
                        Xác nhận mật khẩu của bạn <span class="text-danger">*</span>
                    </label>
                    <input type="password" name="password"
                           class="form-control bg-dark border-secondary text-white"
                           placeholder="Nhập mật khẩu admin..." required>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fa-solid fa-ban me-1"></i>Xác nhận từ chối
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('approveModal').addEventListener('show.bs.modal', function (e) {
    const btn   = e.relatedTarget;
    document.getElementById('approveUserName').textContent = btn.dataset.userName;
    document.getElementById('approveForm').action =
        '{{ url("/admin/unlock-requests") }}/' + btn.dataset.reqId + '/approve';
    this.querySelector('textarea[name="admin_note"]').value = '';
});

document.getElementById('rejectModal').addEventListener('show.bs.modal', function (e) {
    const btn   = e.relatedTarget;
    document.getElementById('rejectUserName').textContent = btn.dataset.userName;
    document.getElementById('rejectForm').action =
        '{{ url("/admin/unlock-requests") }}/' + btn.dataset.reqId + '/reject';
    this.querySelector('textarea[name="admin_note"]').value = '';
});
</script>
@endpush
