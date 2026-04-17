@extends('layouts.admin')

@section('title', 'Quản lý đăng ký')
@section('page-title', 'Quản lý đăng ký')
@section('page-subtitle', 'Toàn bộ lịch sử đăng ký gói Premium của người dùng')

@section('content')

{{-- ─── Stats ─── --}}
<div class="row g-3 mb-4">
    @php
        $statCards = [
            ['label' => 'Tổng đăng ký',      'value' => $stats['total'],     'color' => '#818cf8', 'icon' => 'fa-list'],
            ['label' => 'Đang hiệu lực',      'value' => $stats['active'],    'color' => '#4ade80', 'icon' => 'fa-circle-check'],
            ['label' => 'Đã hết hạn',         'value' => $stats['expired'],   'color' => '#94a3b8', 'icon' => 'fa-clock-rotate-left'],
            ['label' => 'Đã hủy',             'value' => $stats['cancelled'], 'color' => '#f87171', 'icon' => 'fa-ban'],
        ];
    @endphp
    @foreach($statCards as $card)
    <div class="col-6 col-xl-3">
        <div class="rounded-3 p-3 d-flex align-items-center gap-3"
             style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07)">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:38px;height:38px;background:{{ $card['color'] }}18;border:1px solid {{ $card['color'] }}30">
                <i class="fa-solid {{ $card['icon'] }}" style="color:{{ $card['color'] }};font-size:.85rem"></i>
            </div>
            <div>
                <div class="fw-bold text-white" style="font-size:1.2rem">{{ number_format($card['value']) }}</div>
                <div class="text-muted" style="font-size:.75rem">{{ $card['label'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Revenue highlight --}}
<div class="alert border mb-4 py-2 px-3 d-flex align-items-center gap-3"
     style="background:rgba(251,191,36,.06);border-color:rgba(251,191,36,.2)!important">
    <i class="fa-solid fa-sack-dollar" style="color:#fbbf24;font-size:1.1rem"></i>
    <div>
        <span class="text-muted small">Tổng doanh thu thực nhận: </span>
        <strong style="color:#fbbf24;font-size:1rem">{{ number_format($stats['revenue']) }} ₫</strong>
        <span class="text-muted small ms-2">(từ đăng ký đang hiệu lực + đã hết hạn)</span>
    </div>
</div>

{{-- ─── Filter bar ─── --}}
<form method="GET" action="{{ route('admin.subscriptions.index') }}" class="filter-bar">
    <div class="filter-bar-inner">

        <div class="filter-field" style="flex:1;min-width:200px;">
            <label class="filter-label"><i class="fa-solid fa-magnifying-glass"></i>Tìm kiếm người dùng</label>
            <div class="filter-search-wrap">
                <i class="fa-solid fa-magnifying-glass filter-search-icon"></i>
                <input type="text" name="search" class="filter-input"
                       placeholder="Tên, email..."
                       value="{{ $filters['search'] ?? '' }}">
            </div>
        </div>

        <div class="filter-field" style="min-width:160px;">
            <label class="filter-label"><i class="fa-solid fa-crown"></i>Gói VIP</label>
            <select name="vip_id" class="filter-select">
                <option value="">Tất cả gói</option>
                @foreach($vips as $vip)
                    <option value="{{ $vip->id }}" {{ ($filters['vip_id'] ?? '') == $vip->id ? 'selected' : '' }}>
                        {{ $vip->title }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-field" style="min-width:145px;">
            <label class="filter-label"><i class="fa-solid fa-toggle-on"></i>Trạng thái</label>
            <select name="status" class="filter-select">
                <option value=""          {{ ($filters['status'] ?? '') === ''          ? 'selected' : '' }}>Tất cả</option>
                <option value="active"    {{ ($filters['status'] ?? '') === 'active'    ? 'selected' : '' }}>Đang hiệu lực</option>
                <option value="expired"   {{ ($filters['status'] ?? '') === 'expired'   ? 'selected' : '' }}>Đã hết hạn</option>
                <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="filter-btn-submit">
                <i class="fa-solid fa-filter"></i>Lọc
                @if(!empty($filters['search']) || !empty($filters['vip_id']) || !empty($filters['status']))
                    <span class="filter-active-dot"></span>
                @endif
            </button>
            <a href="{{ route('admin.subscriptions.index') }}" class="filter-btn-reset" title="Xóa bộ lọc">
                <i class="fa-solid fa-xmark"></i>
            </a>
        </div>

    </div>
</form>


{{-- Results + grant button --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <span class="text-muted small">
        Tìm thấy <strong class="text-white">{{ $subscriptions->total() }}</strong> lượt đăng ký
    </span>
    <button class="btn btn-sm btn-primary"
            data-bs-toggle="modal" data-bs-target="#grantModal">
        <i class="fa-solid fa-plus me-1"></i>Cấp thủ công
    </button>
</div>

{{-- ─── Table ─── --}}
<div class="card bg-dark border-secondary border-opacity-25">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="border-secondary">
                    <th class="text-muted fw-normal small ps-3" style="width:46px">#</th>
                    <th class="text-muted fw-normal small">Người dùng</th>
                    <th class="text-muted fw-normal small">Gói VIP</th>
                    <th class="text-muted fw-normal small">Ngày bắt đầu</th>
                    <th class="text-muted fw-normal small">Ngày kết thúc</th>
                    <th class="text-muted fw-normal small text-end">Thanh toán</th>
                                    <th class="text-muted fw-normal small">Chi tiết payment</th>
                    <th class="text-muted fw-normal small text-center">Trạng thái</th>
                    <th class="text-muted fw-normal small text-end pe-3">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $sub)
                @php
                    $statusStyle = match($sub->status) {
                        'active'    => ['bg' => 'rgba(34,197,94,.12)',  'color' => '#86efac', 'border' => 'rgba(34,197,94,.25)',  'icon' => 'fa-circle-check',   'label' => 'Đang hiệu lực'],
                        'expired'   => ['bg' => 'rgba(107,114,128,.12)','color' => '#9ca3af', 'border' => 'rgba(107,114,128,.25)','icon' => 'fa-clock-rotate-left','label' => 'Đã hết hạn'],
                        'cancelled' => ['bg' => 'rgba(239,68,68,.12)',  'color' => '#fca5a5', 'border' => 'rgba(239,68,68,.25)',  'icon' => 'fa-ban',            'label' => 'Đã hủy'],
                        default     => ['bg' => 'rgba(99,102,241,.12)', 'color' => '#818cf8', 'border' => 'rgba(99,102,241,.25)', 'icon' => 'fa-circle',         'label' => $sub->status],
                    };
                    $daysLeft = $sub->daysRemaining();
                @endphp
                <tr class="border-secondary border-opacity-25">
                    <td class="ps-3 text-muted small">{{ $sub->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ ($sub->user?->avatar && $sub->user->avatar !== '/storage/avt.jpg') ? asset($sub->user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($sub->user?->name ?? 'U').'&background=6366f1&color=fff&size=32' }}"
                                 class="rounded-circle flex-shrink-0"
                                 width="30" height="30" style="object-fit:cover"
                                 alt="{{ $sub->user?->name }}">
                            <div class="min-w-0">
                                <div class="text-white small fw-semibold text-truncate" style="max-width:160px">{{ $sub->user?->name ?? '—' }}</div>
                                <div class="text-muted" style="font-size:.72rem">{{ $sub->user?->email ?? '—' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge rounded-pill px-2 py-1"
                              style="background:rgba(251,191,36,.12);color:#fbbf24;border:1px solid rgba(251,191,36,.25);font-size:.72rem">
                            <i class="fa-solid fa-crown me-1"></i>{{ $sub->vip?->title ?? $sub->vip_id }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $sub->start_date->format('d/m/Y') }}</td>
                    <td class="small">
                        <div class="text-muted">{{ $sub->end_date->format('d/m/Y') }}</div>
                        @if($sub->isActive() && $daysLeft <= 7)
                            <div class="text-warning" style="font-size:.7rem">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>còn {{ $daysLeft }} ngày
                            </div>
                        @elseif($sub->isActive())
                            <div class="text-success" style="font-size:.7rem">còn {{ $daysLeft }} ngày</div>
                        @endif
                    </td>
                    <td class="text-end fw-semibold" style="color:#fbbf24;font-size:.85rem">
                        <div>{{ number_format($sub->payment?->amount ?? $sub->amount_paid) }} ₫</div>
                        <div class="text-muted" style="font-size:.7rem">
                            {{ $sub->payment?->provider ?? '—' }}
                            @if($sub->payment?->paid_at)
                                · {{ $sub->payment->paid_at->format('d/m/Y H:i') }}
                            @endif
                        </div>
                    </td>
                    <td>
                        @if($sub->payment?->raw_response)
                        <details>
                            <summary class="small text-info" style="cursor:pointer">Xem raw_response</summary>
                            <pre class="mt-2 mb-0 small text-muted" style="max-width:320px;white-space:pre-wrap">{{ json_encode($sub->payment->raw_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </details>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge rounded-pill px-2 py-1"
                              style="background:{{ $statusStyle['bg'] }};color:{{ $statusStyle['color'] }};border:1px solid {{ $statusStyle['border'] }};font-size:.72rem">
                            <i class="fa-solid {{ $statusStyle['icon'] }} me-1"></i>{{ $statusStyle['label'] }}
                        </span>
                    </td>
                    <td class="text-end pe-3">
                        @if($sub->isActive())
                        <div class="d-flex gap-1 justify-content-end">
                            <button type="button" class="btn btn-sm btn-outline-warning"
                                    title="Hủy đăng ký"
                                    data-bs-toggle="modal"
                                    data-bs-target="#cancelModal-{{ $sub->id }}">
                                <i class="fa-solid fa-ban me-1"></i>Hủy
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    title="Đánh dấu hết hạn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#expireModal-{{ $sub->id }}">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </button>
                        </div>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>

                {{-- Modal Hủy Đăng Ký --}}
                <div class="modal fade" id="cancelModal-{{ $sub->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-dark border border-secondary border-opacity-50">
                            <div class="modal-header border-secondary border-opacity-25 pb-3">
                                <h6 class="modal-title text-white">
                                    <i class="fa-solid fa-triangle-exclamation me-2 text-warning"></i>Hủy gói đăng ký
                                </h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('admin.subscriptions.cancel', $sub->id) }}" class="m-0">
                                @csrf
                                <div class="modal-body py-3">
                                    <p class="text-white small mb-3">Bạn đang chuẩn bị hủy gói <strong>{{ $sub->vip?->title }}</strong> của người dùng <strong>{{ $sub->user?->name }}</strong>.</p>
                                    <div class="mb-2">
                                        <label class="form-label text-muted small mb-1">Lý do hủy <span class="text-danger">*</span></label>
                                        <textarea name="reason" class="form-control form-control-sm bg-dark border-secondary text-white" rows="2" placeholder="Nhập lý do gửi đến người dùng" required></textarea>
                                        @error('reason')<div class="text-danger mt-1" style="font-size:0.75rem">{{ $message }}</div>@enderror
                                    </div>
                                    <p class="text-muted small mb-0 mt-2"><i class="fa-solid fa-circle-info me-1"></i> Nếu người dùng không còn gói active nào khác, tài khoản sẽ bị hạ về Free.</p>
                                </div>
                                <div class="modal-footer border-secondary border-opacity-25 pt-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                                    <button type="submit" class="btn btn-sm btn-warning">
                                        <i class="fa-solid fa-ban me-1"></i>Xác nhận hủy
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Modal Hết Hạn --}}
                <div class="modal fade" id="expireModal-{{ $sub->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-dark border border-secondary border-opacity-50">
                            <div class="modal-header border-secondary border-opacity-25 pb-3">
                                <h6 class="modal-title text-white">
                                    <i class="fa-solid fa-clock-rotate-left me-2 text-info"></i>Đánh dấu hết hạn
                                </h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('admin.subscriptions.expire', $sub->id) }}" class="m-0">
                                @csrf
                                <div class="modal-body py-3">
                                    <p class="text-white small mb-0">Hành động này sẽ cập nhật trạng thái gói <strong>{{ $sub->vip?->title }}</strong> của <strong>{{ $sub->user?->name }}</strong> sang <em>Đã hết hạn</em> ngay lập tức. Người dùng sẽ nhận được email thông báo gói đã hết hạn.</p>
                                </div>
                                <div class="modal-footer border-secondary border-opacity-25 pt-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                                    <button type="submit" class="btn btn-sm btn-info text-dark">
                                        <i class="fa-solid fa-check me-1"></i>Xác nhận
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="fa-solid fa-receipt fa-2x mb-3 opacity-25 d-block"></i>
                        Không tìm thấy lượt đăng ký nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($subscriptions->hasPages())
    <div class="card-footer bg-transparent border-secondary border-opacity-25 py-3">
        {{ $subscriptions->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- ─── Modal: Cấp đăng ký thủ công ─── --}}
<div class="modal fade" id="grantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-crown me-2" style="color:#fbbf24"></i>Cấp đăng ký thủ công
                </h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.subscriptions.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">ID người dùng <span class="text-danger">*</span></label>
                        <input type="number" name="user_id" min="1"
                               class="form-control form-control-sm bg-dark border-secondary text-white"
                               placeholder="Nhập ID người dùng">
                        <div class="form-text text-muted" style="font-size:.72rem">
                            Tìm ID trong trang Danh sách người dùng.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Gói VIP <span class="text-danger">*</span></label>
                        <select name="vip_id" class="form-select form-select-sm bg-dark border-secondary text-white">
                            <option value="">— Chọn gói —</option>
                            @foreach($vips as $vip)
                                <option value="{{ $vip->id }}">{{ $vip->labelWithPrice() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Ngày bắt đầu <span class="text-danger">*</span></label>
                        <input type="date" name="start_date"
                               class="form-control form-control-sm bg-dark border-secondary text-white"
                               value="{{ now()->toDateString() }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Số tiền thanh toán (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="amount_paid" min="0"
                               class="form-control form-control-sm bg-dark border-secondary text-white"
                               placeholder="0">
                        <div class="form-text text-muted" style="font-size:.72rem">
                            Nhập 0 nếu cấp miễn phí / khuyến mãi.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-crown me-1"></i>Cấp đăng ký
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
