@extends('layouts.admin')

@section('title', 'Xét duyệt đăng ký Nghệ sĩ')
@section('page-title', 'Đăng ký Nghệ sĩ')
@section('page-subtitle', 'Xem xét và phê duyệt các đơn đăng ký trở thành Nghệ sĩ từ người dùng')

@push('styles')
<style>
.req-card {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 14px; padding: 22px;
    transition: border-color .2s;
}
.req-card:hover { border-color: rgba(255,255,255,.14); }
.req-card.pending_payment { border-left: 3px solid #fbbf24; }
.req-card.pending_review { border-left: 3px solid #c084fc; }
.req-card.approved       { border-left: 3px solid #4ade80; }
.req-card.rejected       { border-left: 3px solid #f87171; }

.user-avatar-sm { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }

.bio-box {
    background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.07);
    border-radius: 8px; padding: 12px 14px;
    font-size: .85rem; line-height: 1.6;
    white-space: pre-wrap; word-break: break-word;
    color: #cbd5e1; max-height: 120px; overflow-y: auto;
}

.tab-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 16px; border-radius: 20px;
    font-size: .82rem; font-weight: 500;
    border: 1px solid rgba(255,255,255,.1);
    text-decoration: none; color: #94a3b8; transition: all .15s;
}
.tab-pill:hover { color: #fff; border-color: rgba(255,255,255,.25); background: rgba(255,255,255,.06); }
.tab-pill.active { color: #fff; border-color: rgba(255,255,255,.28); background: rgba(255,255,255,.07); }
.tab-pill.active.pending-tab  { background:rgba(192,132,252,.12); border-color:rgba(192,132,252,.4); color:#c084fc; }
.tab-pill.active.approved-tab { background:rgba(74,222,128,.1);   border-color:rgba(74,222,128,.4);  color:#4ade80; }
.tab-pill.active.rejected-tab { background:rgba(248,113,113,.1);  border-color:rgba(248,113,113,.4); color:#f87171; }

.mm-modal-content {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 16px;
}
.mmf-input {
    background: rgba(255,255,255,.05) !important;
    border: 1px solid rgba(255,255,255,.12) !important;
    border-radius: 10px !important; color: #e2e8f0 !important;
    padding: 10px 14px !important;
}
.mmf-input:focus {
    border-color: rgba(192,132,252,.5) !important;
    box-shadow: 0 0 0 3px rgba(168,85,247,.12) !important;
}
</style>
@endpush

@section('content')

{{-- ── Tabs ── --}}
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('admin.artist-registrations.index', ['tab' => 'all']) }}"
       class="tab-pill {{ $tab === 'all' ? 'active' : '' }}">
        <i class="fa-solid fa-layer-group"></i>Tất cả
        <span class="badge rounded-pill bg-secondary" style="font-size:.72rem">{{ $counts['all'] ?? 0 }}</span>
    </a>
    <a href="{{ route('admin.artist-registrations.index', ['tab' => 'pending_payment']) }}"
       class="tab-pill {{ $tab === 'pending_payment' ? 'active' : '' }}">
        <i class="fa-solid fa-credit-card"></i>Chờ thanh toán
        <span class="badge rounded-pill bg-secondary" style="font-size:.72rem">{{ $counts['pending_payment'] ?? 0 }}</span>
    </a>
    <a href="{{ route('admin.artist-registrations.index', ['tab' => 'pending_review']) }}"
       class="tab-pill pending-tab {{ $tab === 'pending_review' ? 'active' : '' }}">
        <i class="fa-solid fa-clock"></i>Chờ xét duyệt
        @if(($counts['pending_review'] ?? 0) > 0)
        <span class="badge rounded-pill" style="background:#c084fc;color:#fff;font-size:.72rem">{{ $counts['pending_review'] }}</span>
        @endif
    </a>
    <a href="{{ route('admin.artist-registrations.index', ['tab' => 'approved']) }}"
       class="tab-pill approved-tab {{ $tab === 'approved' ? 'active' : '' }}">
        <i class="fa-solid fa-circle-check"></i>Đã phê duyệt
        <span class="badge rounded-pill bg-secondary" style="font-size:.72rem">{{ $counts['approved'] ?? 0 }}</span>
    </a>
    <a href="{{ route('admin.artist-registrations.index', ['tab' => 'rejected']) }}"
       class="tab-pill rejected-tab {{ $tab === 'rejected' ? 'active' : '' }}">
        <i class="fa-solid fa-ban"></i>Đã từ chối
        <span class="badge rounded-pill bg-secondary" style="font-size:.72rem">{{ $counts['rejected'] ?? 0 }}</span>
    </a>
</div>

{{-- ── Sub-filter hoàn tiền (chỉ hiện ở tab Đã từ chối) ── --}}
@if($tab === 'rejected')
<div class="d-flex flex-wrap gap-2 mb-4 align-items-center">
    <span class="small text-muted me-1"><i class="fa-solid fa-rotate-left me-1"></i>Lọc hoàn tiền:</span>
    <a href="{{ route('admin.artist-registrations.index', ['tab' => 'rejected']) }}"
       class="tab-pill {{ $refundFilter === null ? 'active rejected-tab' : '' }}" style="font-size:.78rem;padding:4px 14px">
        Tất cả <span class="badge rounded-pill bg-secondary ms-1" style="font-size:.68rem">{{ $counts['rejected'] ?? 0 }}</span>
    </a>
    <a href="{{ route('admin.artist-registrations.index', ['tab' => 'rejected', 'refund_filter' => 'pending']) }}"
       class="tab-pill {{ $refundFilter === 'pending' ? 'active' : '' }}"
       style="font-size:.78rem;padding:4px 14px;{{ $refundFilter === 'pending' ? 'background:rgba(251,191,36,.12);border-color:rgba(251,191,36,.4);color:#fbbf24' : '' }}">
        <i class="fa-solid fa-clock me-1"></i>Chờ hoàn tiền
        @if(($refundCounts['pending'] ?? 0) > 0)
        <span class="badge rounded-pill ms-1" style="background:#fbbf24;color:#000;font-size:.68rem">{{ $refundCounts['pending'] }}</span>
        @else
        <span class="badge rounded-pill bg-secondary ms-1" style="font-size:.68rem">0</span>
        @endif
    </a>
    <a href="{{ route('admin.artist-registrations.index', ['tab' => 'rejected', 'refund_filter' => 'completed']) }}"
       class="tab-pill {{ $refundFilter === 'completed' ? 'active' : '' }}"
       style="font-size:.78rem;padding:4px 14px;{{ $refundFilter === 'completed' ? 'background:rgba(52,211,153,.1);border-color:rgba(52,211,153,.4);color:#34d399' : '' }}">
        <i class="fa-solid fa-circle-check me-1"></i>Đã hoàn tiền
        <span class="badge rounded-pill ms-1" style="background:{{ ($refundCounts['completed'] ?? 0) > 0 ? '#34d399;color:#000' : 'var(--bs-secondary)' }};font-size:.68rem">{{ $refundCounts['completed'] ?? 0 }}</span>
    </a>
    <a href="{{ route('admin.artist-registrations.index', ['tab' => 'rejected', 'refund_filter' => 'none']) }}"
       class="tab-pill {{ $refundFilter === 'none' ? 'active' : '' }}"
       style="font-size:.78rem;padding:4px 14px;{{ $refundFilter === 'none' ? 'background:rgba(100,116,139,.12);border-color:rgba(100,116,139,.4);color:#94a3b8' : '' }}">
        <i class="fa-solid fa-minus me-1"></i>Không hoàn tiền
        <span class="badge rounded-pill bg-secondary ms-1" style="font-size:.68rem">{{ $refundCounts['none'] ?? 0 }}</span>
    </a>
</div>
@endif

{{-- ── Danh sách ── --}}
@forelse($registrations as $reg)
@php
$avatarUrl = ($reg->user->avatar && $reg->user->avatar !== '/storage/avt.jpg')
    ? asset($reg->user->avatar)
    : 'https://ui-avatars.com/api/?name='.urlencode($reg->user->name).'&background=7c3aed&color=fff&size=40';
@endphp
<div class="req-card {{ $reg->status }} mb-3">
    <div class="d-flex flex-wrap gap-3 align-items-start justify-content-between mb-3">
        {{-- Thông tin user --}}
        <div class="d-flex align-items-center gap-3">
            <img src="{{ $avatarUrl }}" class="user-avatar-sm" alt="{{ $reg->user->name }}">
            <div>
                <div class="fw-semibold text-white">{{ $reg->user->name }}</div>
                <div class="small text-muted">{{ $reg->user->email }}</div>
            </div>
        </div>

        {{-- Badge trạng thái --}}
        <span class="badge rounded-pill px-3 py-2"
              style="background:rgba({{ match($reg->status) {
                                'pending_payment'=> '251,191,36',
                'pending_review' => '192,132,252',
                'approved'       => '74,222,128',
                'rejected'       => '248,113,113',
                default          => '148,163,184',
              } }},.15);color:{{ match($reg->status) {
                                'pending_payment'=> '#fbbf24',
                'pending_review' => '#c084fc',
                'approved'       => '#4ade80',
                'rejected'       => '#f87171',
                default          => '#94a3b8',
              } }};border:1px solid rgba({{ match($reg->status) {
                                'pending_payment'=> '251,191,36',
                'pending_review' => '192,132,252',
                'approved'       => '74,222,128',
                'rejected'       => '248,113,113',
                default          => '148,163,184',
              } }},.3);font-size:.75rem">
            {{ $reg->statusLabel() }}
        </span>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6">
            <div class="small text-muted mb-1">Tên nghệ danh</div>
            <div class="fw-semibold" style="color:#c084fc;font-size:1.05rem">{{ $reg->artist_name }}</div>
        </div>
        <div class="col-6 col-md-3">
            <div class="small text-muted mb-1">Gói đăng ký</div>
            <div class="text-white small">{{ $reg->package?->name ?? '—' }}</div>
        </div>
        <div class="col-6 col-md-3">
            <div class="small text-muted mb-1">Số tiền</div>
            <div class="fw-semibold" style="color:#fbbf24">{{ number_format($reg->amount_paid) }}₫</div>
            @if($reg->refund_status)
            <div class="mt-1">
                @if($reg->isRefundCompleted())
                <span style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.25);color:#34d399;border-radius:50px;padding:2px 8px;font-size:.68rem;font-weight:600">
                    <i class="fa-solid fa-circle-check me-1"></i>Đã hoàn: {{ number_format($reg->refund_amount) }}₫
                </span>
                @else
                <span style="background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.25);color:#fbbf24;border-radius:50px;padding:2px 8px;font-size:.68rem;font-weight:600">
                    <i class="fa-solid fa-rotate-left me-1"></i>Chờ hoàn: {{ number_format($reg->refund_amount) }}₫
                </span>
                @endif
            </div>
            @endif
        </div>
    </div>

    @if($reg->bio)
    <div class="mb-3">
        <div class="small text-muted mb-1">Giới thiệu</div>
        <div class="bio-box">{{ $reg->bio }}</div>
    </div>
    @endif

    <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="d-flex flex-wrap gap-3">
            <div class="small text-muted">
                <i class="fa-solid fa-clock me-1"></i>Đăng ký {{ $reg->created_at->diffForHumans() }}
            </div>
            @if($reg->paid_at)
            <div class="small text-muted">
                <i class="fa-solid fa-credit-card me-1"></i>Thanh toán {{ $reg->paid_at->format('d/m/Y H:i') }}
            </div>
            @endif
            @if($reg->refund_status)
            <div class="small" style="color:{{ $reg->isRefundCompleted() ? '#34d399' : '#fbbf24' }}">
                <i class="fa-solid fa-rotate-left me-1"></i>
                {{ $reg->refundStatusLabel() }}
                {{ number_format($reg->refund_amount) }} ₫
                @if($reg->isRefundCompleted() && $reg->refund_confirmed_at)
                    &mdash; {{ $reg->refund_confirmed_at->format('d/m/Y H:i') }}
                    @if($reg->refundConfirmer)
                        bởi <strong class="text-white">{{ $reg->refundConfirmer->name }}</strong>
                    @endif
                @endif
            </div>
            @endif
            @if($reg->reviewed_at)
            <div class="small text-muted">
                <i class="fa-solid fa-user-shield me-1"></i>Xét duyệt {{ $reg->reviewed_at->format('d/m/Y H:i') }}
                @if($reg->reviewer)
                    bởi <strong class="text-white">{{ $reg->reviewer->name }}</strong>
                @endif
            </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.users.show', $reg->user_id) }}"
               class="btn btn-sm btn-outline-info"
               title="Xem thông tin tài khoản người dùng">
                <i class="fa-solid fa-user me-1"></i>Xem tài khoản
            </a>
            @if($reg->isPendingReview())
            <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                    data-bs-target="#approveModal"
                    data-reg-id="{{ $reg->id }}"
                    data-artist-name="{{ $reg->artist_name }}"
                    data-user-name="{{ $reg->user->name }}">
                <i class="fa-solid fa-circle-check me-1"></i>Phê duyệt
            </button>
            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                    data-bs-target="#rejectModal"
                    data-reg-id="{{ $reg->id }}"
                    data-artist-name="{{ $reg->artist_name }}"
                    data-user-name="{{ $reg->user->name }}">
                <i class="fa-solid fa-ban me-1"></i>Từ chối
            </button>
            @endif
            @if($reg->isRefundPending())
            <form method="POST" action="{{ route('admin.artist-registrations.confirmRefund', $reg->id) }}"
                  data-confirm-message="Xác nhận đã hoàn tiền {{ number_format($reg->refund_amount) }} ₫ cho {{ $reg->user->name }}?"
                @csrf
                <button type="submit" class="btn btn-sm"
                        style="background:rgba(52,211,153,.12);border:1px solid rgba(52,211,153,.3);color:#34d399;border-radius:8px">
                    <i class="fa-solid fa-circle-check me-1"></i>Xác nhận đã hoàn tiền
                </button>
            </form>
            @endif
        </div>

        @if($reg->admin_note)
        <div class="w-100 mt-1">
            <div class="small text-muted mb-1">Ghi chú admin</div>
            <div class="small" style="color:#94a3b8;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:8px;padding:8px 12px">
                {{ $reg->admin_note }}
            </div>
        </div>
        @endif
    </div>
</div>
@empty
<div class="text-center py-5 text-muted">
    <i class="fa-solid fa-microphone-slash fa-2x mb-3 opacity-25 d-block"></i>
    Không có đơn đăng ký nào trong mục này.
</div>
@endforelse

{{-- Pagination --}}
@if($registrations->hasPages())
<div class="mt-3">
    {{ $registrations->links('pagination::bootstrap-5') }}
</div>
@endif

{{-- ── Modal: Phê duyệt ── --}}
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content mm-modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-circle-check me-2 text-success"></i>Phê duyệt đơn đăng ký
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="approveForm" action="">
                @csrf
                <div class="modal-body pt-3">
                    <p class="text-muted small mb-3">
                        Bạn đang phê duyệt đơn đăng ký Nghệ sĩ với tên nghệ danh
                        <strong class="text-white" id="approveArtistName"></strong>
                        của <strong class="text-white" id="approveUserName"></strong>.
                        Tài khoản sẽ được nâng cấp lên <strong style="color:#c084fc">Nghệ sĩ</strong> ngay lập tức và user sẽ nhận được thông báo.
                    </p>
                    <label class="form-label text-muted small">Ghi chú cho user (không bắt buộc)</label>
                    <textarea name="admin_note" rows="3" class="form-control mmf-input"
                              placeholder="VD: Chào mừng bạn đến với cộng đồng nghệ sĩ Blue Wave Music!"
                              maxlength="500"></textarea>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-success px-4">
                        <i class="fa-solid fa-circle-check me-1"></i>Xác nhận phê duyệt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal: Từ chối ── --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content mm-modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-ban me-2 text-danger"></i>Từ chối đơn đăng ký
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="rejectForm" action="">
                @csrf
                <div class="modal-body pt-3">
                    <p class="text-muted small mb-3">
                        Bạn đang từ chối đơn đăng ký Nghệ sĩ của
                        <strong class="text-white" id="rejectUserName"></strong>
                        (nghệ danh: <strong class="text-white" id="rejectArtistName"></strong>).
                        User sẽ nhận được email thông báo lý do từ chối.
                    </p>
                    <label class="form-label text-muted small">
                        Lý do từ chối <span class="text-danger">*</span>
                    </label>
                    <textarea name="admin_note" rows="3" class="form-control mmf-input"
                              placeholder="Nêu rõ lý do từ chối để người dùng có thể cải thiện..."
                              required minlength="10" maxlength="500"></textarea>
                    <div class="form-text text-muted mt-1" style="font-size:.72rem">Tối thiểu 10 ký tự.</div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-danger px-4">
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
document.getElementById('approveModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('approveArtistName').textContent = btn.dataset.artistName;
    document.getElementById('approveUserName').textContent   = btn.dataset.userName;
    document.getElementById('approveForm').action =
        '{{ url("/admin/artist-registrations") }}/' + btn.dataset.regId + '/approve';
});

document.getElementById('rejectModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('rejectArtistName').textContent = btn.dataset.artistName;
    document.getElementById('rejectUserName').textContent   = btn.dataset.userName;
    document.getElementById('rejectForm').action =
        '{{ url("/admin/artist-registrations") }}/' + btn.dataset.regId + '/reject';
});
</script>
@endpush
