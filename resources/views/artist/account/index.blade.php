@extends('layouts.artist')

@section('title', 'Tai khoan nghe si - Artist Studio')
@section('page-title', 'Tai khoan nghe si')
@section('page-subtitle', 'Quan ly goi nghe si trong mot giao dien thong nhat')

@section('content')
<div class="d-flex flex-column gap-3">
    @if(session('success'))
        <div class="alert alert-success mb-0">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info mb-0">{{ session('info') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning mb-0">{{ session('warning') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-0">{{ session('error') }}</div>
    @endif

    <section class="artist-account-card card border-0">
        <div class="card-header artist-account-head">
            <div>
                <h6 class="mb-1">Quản lý gói nghệ sĩ</h6>
                <p class="mb-0 text-muted small">Theo dõi gói hiện tại, nâng cấp và lịch sử thanh toán</p>
            </div>
            <span class="badge rounded-pill artist-account-badge">
                <i class="fa-solid fa-shield-halved me-1"></i>Artist Studio
            </span>
        </div>

        <div class="card-body p-3 p-md-4">
            <div class="row g-3 mb-4">
                <div class="col-12 col-xl-7">
                    <div class="artist-account-panel h-100">
                        <div class="artist-account-panel-title">Gói đang sử dụng</div>
                        @if($activeRegistration && $activeRegistration->package)
                            <div class="d-flex flex-wrap justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold text-white mb-1">{{ $activeRegistration->package->name }}</div>
                                    <div class="small text-muted">Giá gói: {{ number_format((int) $activeRegistration->package->price) }} VND</div>
                                    <div class="small text-muted">Hiệu lực đến: {{ optional($activeRegistration->expires_at)->format('d/m/Y H:i') }}</div>
                                    @if($activeRegistration->expires_at)
                                        <div class="small text-success">Còn {{ max(0, now()->diffInDays($activeRegistration->expires_at, false)) }} ngày</div>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('artist.account.package.cancel', $activeRegistration->id) }}" onsubmit="return confirm('Bạn chắc chắn muốn hủy gói hiện tại?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fa-solid fa-ban me-1"></i>Hủy gói
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="text-muted">Bạn chưa có gói nghệ sĩ còn hiệu lực.</div>
                        @endif
                    </div>
                </div>

                <div class="col-12 col-xl-5">
                    <div class="artist-account-panel h-100">
                        <div class="artist-account-panel-title">Đơn đang xử lý</div>
                        @if($pendingRegistration)
                            <div class="small text-muted mb-2">Gói: <span class="text-white fw-semibold">{{ $pendingRegistration->package?->name }}</span></div>
                            <div class="small text-muted mb-3">Trạng thái: <span class="text-white">{{ $pendingRegistration->statusLabel() }}</span></div>
                            @if($pendingRegistration->status === 'pending_payment')
                                <div class="d-flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('artist-register.payPending', $pendingRegistration->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-primary" type="submit">Thanh toán tiếp</button>
                                    </form>
                                    <form method="POST" action="{{ route('artist-register.cancelPending', $pendingRegistration->id) }}" onsubmit="return confirm('Hủy đơn chờ thanh toán?');">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-warning" type="submit">Hủy đơn chờ</button>
                                    </form>
                                </div>
                            @endif
                        @else
                            <div class="text-muted">Hiện không có đơn chờ xử lý.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="artist-account-panel mb-4">
                <div class="artist-account-panel-title">Nâng cấp gói</div>
                @if(($upgradePackages ?? collect())->isNotEmpty())
                    <div class="row g-3">
                        @foreach($upgradePackages as $pkg)
                            <div class="col-12 col-md-6 col-xxl-4">
                                <div class="artist-upgrade-item h-100">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                        <div class="fw-semibold text-white">{{ $pkg->name }}</div>
                                        <span class="badge rounded-pill text-bg-secondary">{{ number_format((int) $pkg->price) }} VND</span>
                                    </div>
                                    <div class="small text-muted mb-2">Thời hạn: {{ (int) $pkg->duration_days }} ngày</div>
                                    @if($pkg->features->isNotEmpty())
                                        <ul class="small text-muted ps-3 mb-3">
                                            @foreach($pkg->features->take(4) as $feature)
                                                <li>{{ $feature->feature }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    <form method="POST" action="{{ route('artist-register.checkout', $pkg->id) }}" onsubmit="return confirm('Xác nhận nâng cấp lên gói {{ $pkg->name }}?');">
                                        @csrf
                                        <input type="hidden" name="upgrade" value="1">
                                        <input type="hidden" name="artist_name" value="{{ auth()->user()->artist_name ?: auth()->user()->name }}">
                                        <input type="hidden" name="bio" value="{{ auth()->user()->bio }}">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">Nâng cấp gói</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted">Hiện chưa có gói cao hơn để nâng cấp.</div>
                @endif
            </div>

            <div class="artist-account-panel">
                <div class="artist-account-panel-title">Lịch sử thanh toán & đơn đăng ký</div>
                <div class="table-responsive">
                    <table class="table table-dark align-middle mb-0 artist-account-table">
                        <thead>
                            <tr>
                                <th>Gói</th>
                                <th>Trạng thái</th>
                                <th>Thanh toán</th>
                                <th>Tạo lúc</th>
                                <th class="text-end">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($registrationHistory ?? collect()) as $reg)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $reg->package?->name ?? '-' }}</div>
                                        <div class="small text-muted">{{ number_format((int) $reg->amount_paid) }} VND</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $reg->statusColor() }}">{{ $reg->statusLabel() }}</span>
                                    </td>
                                    <td>
                                        <div class="small text-muted">Mã GD: {{ $reg->transaction_code ?: '-' }}</div>
                                        <div class="small text-muted">{{ optional($reg->paid_at)->format('d/m/Y H:i') ?: 'Chưa thanh toán' }}</div>
                                    </td>
                                    <td class="small text-muted">{{ optional($reg->created_at)->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#reg-detail-{{ $reg->id }}" aria-expanded="false">
                                            Xem
                                        </button>
                                    </td>
                                </tr>
                                <tr class="collapse" id="reg-detail-{{ $reg->id }}">
                                    <td colspan="5" class="artist-account-detail-row">
                                        <div class="row g-2 small">
                                            <div class="col-md-6 text-muted">Nghệ danh: <span class="text-white">{{ $reg->artist_name }}</span></div>
                                            <div class="col-md-6 text-muted">VNPAY Txn: <span class="text-white">{{ $reg->vnp_transaction_no ?: '-' }}</span></div>
                                            <div class="col-md-6 text-muted">VNPAY pay date: <span class="text-white">{{ $reg->vnp_pay_date ?: '-' }}</span></div>
                                            <div class="col-md-6 text-muted">Hết hạn: <span class="text-white">{{ optional($reg->expires_at)->format('d/m/Y H:i') ?: '-' }}</span></div>
                                            <div class="col-md-12 text-muted">Ghi chú admin: <span class="text-white">{{ $reg->admin_note ?: '-' }}</span></div>
                                            <div class="col-md-12 text-muted">Bio đăng ký: <span class="text-white">{{ $reg->bio ?: '-' }}</span></div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Chưa có lịch sử đăng ký nghệ sĩ.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
