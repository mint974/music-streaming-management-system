@extends('layouts.artist')

@section('title', 'Tài khoản Nghệ sĩ · Artist Studio')
@section('page-title', 'Tài khoản Nghệ sĩ')
@section('page-subtitle', 'Quản lý gói, nâng cấp và lịch sử đăng ký trong một giao diện thống nhất')

@section('content')
<style>
/* ── Account Page Styles ──────────────────────────────────── */
.acc-card {
    background: #13151d;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 1.25rem;
    padding: 1.75rem;
}
.acc-card-title {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #6b7280;
    margin-bottom: 1.1rem;
}

/* Active package card */
.active-pkg-card {
    background: linear-gradient(135deg, rgba(139,92,246,.12) 0%, rgba(217,70,239,.08) 100%);
    border: 1px solid rgba(139,92,246,.35);
    border-radius: 1rem;
    padding: 1.25rem 1.5rem;
}
.active-pkg-name {
    font-size: 1.15rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: .35rem;
}
.days-left-bar {
    height: 6px;
    border-radius: 3px;
    background: rgba(255,255,255,.08);
    overflow: hidden;
    margin-top: .5rem;
}
.days-left-fill {
    height: 100%;
    border-radius: 3px;
    background: linear-gradient(90deg, #8b5cf6, #d946ef);
}

/* Upgrade cards */
.upgrade-card {
    position: relative;
    background: #1a1d27;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 1rem;
    padding: 1.5rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    transition: border-color .25s, transform .25s;
}
.upgrade-card:hover {
    border-color: rgba(217,70,239,.4);
    transform: translateY(-4px);
}
.upgrade-card.featured {
    border-color: rgba(217,70,239,.5);
    background: linear-gradient(145deg, #1c1627 0%, #1a1d27 100%);
}
.upgrade-card.featured::before {
    content: 'ĐỀ XUẤT';
    position: absolute;
    top: -1px; right: 1.25rem;
    background: linear-gradient(90deg, #8b5cf6, #d946ef);
    color: #fff;
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .08em;
    padding: .25rem .75rem;
    border-radius: 0 0 .5rem .5rem;
}
.upgrade-price {
    font-size: 1.75rem;
    font-weight: 800;
    color: #fff;
    line-height: 1;
}
.upgrade-price sup { font-size: .85rem; vertical-align: super; }
.upgrade-duration-badge {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    background: rgba(217,70,239,.12);
    border: 1px solid rgba(217,70,239,.25);
    color: #f0abfc;
    font-size: .75rem;
    font-weight: 600;
    padding: .25rem .75rem;
    border-radius: 2rem;
    margin-top: .5rem;
}
.upgrade-feature-list {
    list-style: none;
    padding: 0;
    margin: 1rem 0 1.5rem;
    flex-grow: 1;
}
.upgrade-feature-list li {
    display: flex;
    align-items: flex-start;
    gap: .6rem;
    color: #d4d4d8;
    font-size: .875rem;
    margin-bottom: .6rem;
}
.upgrade-feature-list li i { color: #d946ef; margin-top: 2px; font-size: .8rem; }
.btn-upgrade {
    display: block;
    width: 100%;
    padding: .7rem;
    border-radius: 2rem;
    font-weight: 600;
    font-size: .9rem;
    text-align: center;
    border: none;
    cursor: pointer;
    transition: all .25s;
}
.btn-upgrade.grad {
    background: linear-gradient(90deg, #8b5cf6, #d946ef);
    color: #fff;
    box-shadow: 0 4px 15px rgba(217,70,239,.3);
}
.btn-upgrade.grad:hover { box-shadow: 0 6px 25px rgba(217,70,239,.5); transform: translateY(-2px); }
.btn-upgrade.outline {
    background: transparent;
    border: 1.5px solid rgba(255,255,255,.2);
    color: #fff;
}
.btn-upgrade.outline:hover { border-color: rgba(217,70,239,.5); }
</style>

<div class="d-flex flex-column gap-4">
    {{-- Flash messages --}}
    @foreach(['success', 'info', 'warning', 'error'] as $msg)
        @if(session($msg))
            <div class="alert alert-{{ $msg === 'error' ? 'danger' : $msg }} mb-0">{{ session($msg) }}</div>
        @endif
    @endforeach

    {{-- ── Top Row: Active Package ──────────────────────────────────── --}}
    <div class="row g-3">
        <div class="col-12">
            <div class="acc-card">
                <div class="acc-card-title"><i class="fa-solid fa-circle-check me-2" style="color:#8b5cf6;"></i>Gói đang sử dụng</div>
                @if($activeRegistration && $activeRegistration->package)
                    @php
                        $totalDays = max(1, (int) ($activeRegistration->approved_at
                            ? $activeRegistration->approved_at->diffInDays($activeRegistration->expires_at)
                            : $activeRegistration->package->duration_days));
                        $daysLeft  = max(0, (int) floor(now()->diffInDays($activeRegistration->expires_at, false)));
                        $pct       = min(100, round(($daysLeft / $totalDays) * 100));
                    @endphp
                    <div class="active-pkg-card">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <div>
                                <div class="active-pkg-name">{{ $activeRegistration->package->name }}</div>
                                <div class="text-white-50 small">{{ number_format((int) $activeRegistration->package->price) }} VNĐ &bull; {{ $activeRegistration->package->duration_days }} ngày</div>
                                <div class="mt-2 small">
                                    <span class="text-white-50">Hiệu lực đến:</span>
                                    <span class="text-white fw-semibold ms-1">{{ optional($activeRegistration->expires_at)->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="days-left-bar mt-2" style="width:220px;">
                                    <div class="days-left-fill" style="width:{{ $pct }}%;"></div>
                                </div>
                                <div class="mt-1 small" style="color:#a78bfa;">Còn {{ $daysLeft }} ngày</div>
                            </div>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#cancelPackageModal">
                                <i class="fa-solid fa-ban me-1"></i>Hủy gói
                            </button>
                        </div>
                    </div>
                @else
                    <div class="text-white-50">Bạn chưa có gói nghệ sĩ còn hiệu lực.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── NÂNG CẤP GÓI ──────────────────────────────────────────────── --}}
    <div class="acc-card">
        <div class="acc-card-title"><i class="fa-solid fa-arrow-up-right-dots me-2" style="color:#d946ef;"></i>Nâng cấp gói</div>
        @if(($upgradePackages ?? collect())->isNotEmpty())
            <div class="row g-3">
                @foreach($upgradePackages as $pkg)
                    @php
                        $isFeatured = $loop->last;
                        $days = $pkg->duration_days;
                        $years = intdiv($days, 365);
                        $durationLabel = ($years >= 1 && ($days % 365) <= 5) ? $years . ' năm' : $days . ' ngày';
                    @endphp
                    <div class="col-12 col-md-6 col-xxl-4">
                        <div class="upgrade-card {{ $isFeatured ? 'featured' : '' }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="fw-bold text-white" style="font-size:1.05rem;">{{ $pkg->name }}</div>
                            </div>
                            <div class="upgrade-price">
                                <sup>₫</sup>{{ number_format((int) $pkg->price) }}
                            </div>
                            <div>
                                <span class="upgrade-duration-badge">
                                    <i class="fa-regular fa-calendar"></i>{{ $durationLabel }} ({{ $days }} ngày)
                                </span>
                            </div>
                            @if($pkg->features->isNotEmpty())
                                <ul class="upgrade-feature-list mt-3">
                                    @foreach($pkg->features->take(5) as $feat)
                                        <li><i class="fa-solid fa-check"></i>{{ $feat->feature }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            <button type="button"
                                    class="btn-upgrade {{ $isFeatured ? 'grad' : 'outline' }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#upgradePackageModal"
                                    data-pkg-name="{{ $pkg->name }}"
                                    data-pkg-action="{{ route('artist-register.checkout', $pkg->id) }}">
                                <i class="fa-solid fa-bolt me-1"></i>Nâng cấp gói này
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-white-50"><i class="fa-solid fa-circle-check me-2" style="color:#6b7280;"></i>Bạn đang dùng gói cao nhất hoặc chưa có gói để nâng cấp.</div>
        @endif
    </div>

    {{-- ── LỊCH SỬ ĐĂNG KÝ ──────────────────────────────────────────── --}}
    <div class="acc-card" id="history-section">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div class="acc-card-title mb-0"><i class="fa-solid fa-receipt me-2" style="color:#6b7280;"></i>Lịch sử thanh toán & đơn đăng ký</div>
            @if(isset($totalSpent) && $totalSpent > 0)
            <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-pill" style="background:rgba(217,70,239,.1); border:1px solid rgba(217,70,239,.25); font-size:.85rem;">
                <i class="fa-solid fa-coins" style="color:#d946ef;"></i>
                <span class="text-white-50">Tổng đã chi:</span>
                <span class="fw-bold" style="color:#f0abfc;">{{ number_format($totalSpent) }} ₫</span>
            </div>
            @endif
        </div>

        {{-- Filter Bar --}}
        <div class="filter-bar mb-4">
            <form method="GET" action="{{ route('artist.account.index') }}#history-section" class="filter-bar-inner">
                <div class="filter-field" style="min-width:170px;">
                    <label class="filter-label">Trạng thái</label>
                    <select name="filter_status" class="filter-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending_payment" {{ ($filter['filter_status'] ?? '') === 'pending_payment' ? 'selected' : '' }}>Chờ thanh toán</option>
                        <option value="pending_review"  {{ ($filter['filter_status'] ?? '') === 'pending_review'  ? 'selected' : '' }}>Chờ xét duyệt</option>
                        <option value="approved"        {{ ($filter['filter_status'] ?? '') === 'approved'        ? 'selected' : '' }}>Đã phê duyệt</option>
                        <option value="rejected"        {{ ($filter['filter_status'] ?? '') === 'rejected'        ? 'selected' : '' }}>Bị từ chối</option>
                        <option value="expired"         {{ ($filter['filter_status'] ?? '') === 'expired'         ? 'selected' : '' }}>Đã hết hạn</option>
                    </select>
                </div>

                <div class="filter-field" style="min-width:170px;">
                    <label class="filter-label">Gói đăng ký</label>
                    <select name="filter_package_id" class="filter-select">
                        <option value="">Tất cả gói</option>
                        @foreach($allPackages as $pkg)
                        <option value="{{ $pkg->id }}" {{ (string)($filter['filter_package_id'] ?? '') === (string)$pkg->id ? 'selected' : '' }}>
                            {{ $pkg->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-field" style="min-width:140px;">
                    <label class="filter-label">Từ ngày</label>
                    <input type="date" name="filter_start_date" id="filter_start_date" class="filter-input"
                           value="{{ $filter['filter_start_date'] ?? '' }}" max="{{ date('Y-m-d') }}">
                </div>

                <div class="filter-field" style="min-width:140px;">
                    <label class="filter-label">Đến ngày</label>
                    <input type="date" name="filter_end_date" id="filter_end_date" class="filter-input"
                           value="{{ $filter['filter_end_date'] ?? '' }}" max="{{ date('Y-m-d') }}">
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn mm-btn mm-btn-primary">
                        <i class="fa-solid fa-filter"></i> Lọc
                    </button>
                    <a href="{{ route('artist.account.index') }}#history-section" class="btn mm-btn mm-btn-ghost">
                        <i class="fa-solid fa-rotate-left"></i> Đặt lại
                    </a>
                </div>
            </form>
        </div>

        @if($registrationHistory->isEmpty())
            <div class="text-center text-white-50 py-5">
                <i class="fa-solid fa-file-circle-xmark fa-3x opacity-40 mb-3"></i>
                <p>Không tìm thấy lịch sử nào phù hợp với bộ lọc.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-dark align-middle mb-0" style="border-spacing:0 .35rem;">
                <thead>
                    <tr style="font-size:.78rem; color:#6b7280; text-transform:uppercase; letter-spacing:.06em;">
                        <th>Gói</th>
                        <th>Trạng thái</th>
                        <th class="text-center">Thời gian hiệu lực</th>
                        <th>Thanh toán</th>
                        <th>Tạo lúc</th>
                        <th class="text-end">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registrationHistory as $reg)
                    <tr style="border-bottom:1px solid rgba(255,255,255,.05);">
                        <td>
                            <div class="fw-semibold text-white">{{ $reg->package?->name ?? '–' }}</div>
                            <div class="small text-white-50">{{ number_format((int) $reg->amount_paid) }} VNĐ</div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $reg->statusColor() }}">{{ $reg->statusLabel() }}</span>
                        </td>
                        <td class="text-center small" style="white-space:nowrap;">
                            @if($reg->approved_at && $reg->expires_at)
                                <div class="text-white-50">{{ $reg->approved_at->format('d/m/Y') }}</div>
                                <div class="text-white-50" style="font-size:.7rem;">→</div>
                                <div class="{{ $reg->isExpired() ? 'text-secondary' : 'text-success fw-semibold' }}">
                                    {{ $reg->expires_at->format('d/m/Y') }}
                                </div>
                            @elseif($reg->isRejected())
                                <span class="text-danger small">Không cấp</span>
                            @else
                                <span class="text-white-50">–</span>
                            @endif
                        </td>
                        <td>
                            <div class="small text-white-50">Mã GD: {{ $reg->transaction_code ?: '–' }}</div>
                            <div class="small text-white-50">{{ optional($reg->paid_at)->format('d/m/Y H:i') ?: 'Chưa thanh toán' }}</div>
                        </td>
                        <td class="small text-white-50">{{ optional($reg->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#reg-detail-{{ $reg->id }}" aria-expanded="false">
                                <i class="fa-solid fa-chevron-down" style="font-size:.7rem;"></i>
                            </button>
                        </td>
                    </tr>
                    <tr class="collapse" id="reg-detail-{{ $reg->id }}">
                        <td colspan="6" style="background:rgba(255,255,255,.02); border-radius:.5rem; padding:1rem 1.25rem;">
                            <div class="row g-2 small">
                                <div class="col-md-6 text-white-50">Nghệ danh: <span class="text-white">{{ $reg->artist_name ?? '–' }}</span></div>
                                <div class="col-md-6 text-white-50">VNPAY Txn: <span class="text-white">{{ $reg->vnp_transaction_no ?: '–' }}</span></div>
                                <div class="col-md-6 text-white-50">VNPAY Pay Date: <span class="text-white">{{ $reg->vnp_pay_date ?: '–' }}</span></div>
                                <div class="col-md-6 text-white-50">Hết hạn: <span class="text-white">{{ optional($reg->expires_at)->format('d/m/Y H:i') ?: '–' }}</span></div>
                                @if($reg->admin_note)
                                <div class="col-12 text-white-50">Ghi chú admin: <span class="text-white">{{ $reg->admin_note }}</span></div>
                                @endif
                                @if($reg->isRejected() && $reg->rejection_reason)
                                <div class="col-12 text-warning">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                    {{ $reg->rejectionReasonLabel() }}: <span class="text-white-50">{{ $reg->rejectionNextStepGuidance() }}</span>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($registrationHistory->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $registrationHistory->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

@push('modals')
{{-- ── MODAL HỦY GÓI ──────────────────────────────────────────────── --}}
@if($activeRegistration && $activeRegistration->package)
<div class="modal fade" id="cancelPackageModal" tabindex="-1" aria-labelledby="cancelPackageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#1a1a2e;border:1px solid rgba(239,68,68,.35);border-radius:14px">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:42px;height:42px;border-radius:10px;background:rgba(239,68,68,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fa-solid fa-ban" style="color:#f87171;font-size:1.1rem"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 text-white fw-semibold" id="cancelPackageModalLabel">Hủy gói nghệ sĩ</h5>
                        <p class="mb-0 text-muted" style="font-size:.8rem">Thao tác này không thể hoàn tác</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body pt-3">
                <p class="text-muted mb-1" style="font-size:.9rem">Bạn có chắc muốn hủy gói hiện tại:</p>
                <p class="text-white fw-semibold mb-2"
                   style="background:rgba(255,255,255,.05);border-radius:8px;padding:8px 12px;border:1px solid rgba(255,255,255,.08)">
                    {{ $activeRegistration->package->name }}
                </p>
                <p class="text-warning small mb-0"><i class="fa-solid fa-triangle-exclamation me-1"></i>Gói sẽ bị hủy ngay lập tức và bạn sẽ mất quyền quản lý nhạc.</p>
            </div>
            <div class="modal-footer border-0 pt-0 gap-2">
                <button type="button" class="btn btn-sm px-4" data-bs-dismiss="modal"
                        style="background:#1f2937;border:1px solid #374151;color:#9ca3af">Hủy bỏ</button>
                <form method="POST" action="{{ route('artist.account.package.cancel', $activeRegistration->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm px-4"
                            style="background:linear-gradient(135deg,#dc2626,#b91c1c);border:none;color:#fff">
                        <i class="fa-solid fa-ban me-1"></i>Xác nhận hủy gói
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── MODAL NÂNG CẤP GÓI ──────────────────────────────────────────── --}}
<div class="modal fade" id="upgradePackageModal" tabindex="-1" aria-labelledby="upgradePackageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#1a1a2e;border:1px solid rgba(139,92,246,.35);border-radius:14px">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:42px;height:42px;border-radius:10px;background:rgba(139,92,246,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fa-solid fa-bolt" style="color:#a78bfa;font-size:1.1rem"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 text-white fw-semibold" id="upgradePackageModalLabel">Xác nhận nâng cấp</h5>
                        <p class="mb-0 text-muted" style="font-size:.8rem">Bạn sẽ được chuyển đến trang thanh toán</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body pt-3">
                <p class="text-muted mb-1" style="font-size:.9rem">Xác nhận nâng cấp lên gói:</p>
                <p id="upgradePkgName" class="text-white fw-semibold mb-0"
                   style="background:rgba(139,92,246,.08);border-radius:8px;padding:8px 12px;border:1px solid rgba(139,92,246,.25)"></p>
            </div>
            <div class="modal-footer border-0 pt-0 gap-2">
                <button type="button" class="btn btn-sm px-4" data-bs-dismiss="modal"
                        style="background:#1f2937;border:1px solid #374151;color:#9ca3af">Hủy bỏ</button>
                <form id="upgradePkgForm" method="POST" action="">
                    @csrf
                    <input type="hidden" name="upgrade" value="1">
                    <input type="hidden" name="accept_terms" value="1">
                    <input type="hidden" name="artist_name" value="{{ auth()->user()->artist_name ?: auth()->user()->name }}">
                    <input type="hidden" name="bio" value="{{ auth()->user()->bio }}">
                    <button type="submit" class="btn btn-sm px-4"
                            style="background:linear-gradient(90deg,#8b5cf6,#d946ef);border:none;color:#fff">
                        <i class="fa-solid fa-bolt me-1"></i>Tiếp tục nâng cấp
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    const sd = document.getElementById('filter_start_date');
    const ed = document.getElementById('filter_end_date');
    const today = '{{ date('Y-m-d') }}';
    if (sd && ed) {
        sd.max = today; ed.max = today;
        sd.addEventListener('change', function () {
            ed.min = this.value || '';
            if (ed.value && ed.value < this.value) ed.value = this.value;
        });
        ed.addEventListener('change', function () {
            if (sd.value && this.value < sd.value) this.value = sd.value;
        });
    }

    // Gán action cho modal nâng cấp
    const upgradeModal = document.getElementById('upgradePackageModal');
    if (upgradeModal) {
        upgradeModal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            document.getElementById('upgradePkgForm').action = btn.getAttribute('data-pkg-action');
            document.getElementById('upgradePkgName').textContent = btn.getAttribute('data-pkg-name');
        });
    }
</script>
@endpush
@endsection

