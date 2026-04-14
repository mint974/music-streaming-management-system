@extends('layouts.main')

@section('title', 'Đăng ký Nghệ sĩ — ' . $package->name . ' · Blue Wave Music')

@push('styles')
<style>
@keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
.form-card {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 20px; padding: 36px 32px;
    animation: fadeUp .5s ease both;
}
.side-card {
    background: rgba(168,85,247,.07);
    border: 1px solid rgba(192,132,252,.25);
    border-radius: 16px; padding: 24px 20px;
    position: sticky; top: 80px;
}
.pkg-price-display {
    font-size: 2rem; font-weight: 800;
    background: linear-gradient(135deg, #c084fc, #f0abfc);
    -webkit-background-clip: text; background-clip: text; color: transparent;
}
.mmf-label { color: #94a3b8; font-size: .82rem; margin-bottom: 6px; font-weight: 500; }
.mmf-input {
    background: rgba(255,255,255,.05) !important;
    border: 1px solid rgba(255,255,255,.1) !important;
    border-radius: 10px !important; color: #e2e8f0 !important;
    padding: 12px 14px !important;
    transition: border-color .2s, box-shadow .2s;
}
.mmf-input:focus {
    border-color: rgba(192,132,252,.5) !important;
    box-shadow: 0 0 0 3px rgba(168,85,247,.12) !important;
    outline: none !important;
}
.mmf-input::placeholder { color: #475569 !important; }
.char-counter { font-size: .72rem; color: #475569; }
.btn-pay {
    background: linear-gradient(135deg, #7c3aed, #c084fc);
    border: none; border-radius: 12px; font-weight: 700;
    padding: 14px; font-size: 1rem; width: 100%;
    transition: opacity .2s, transform .2s; cursor: pointer;
}
.btn-pay:hover { opacity: .9; transform: translateY(-1px); }
.vnpay-badge {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    margin-top: 12px; color: #64748b; font-size: .75rem;
}
.feature-item { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 8px; }
.feature-icon { color: #c084fc; font-size: .8rem; margin-top: 3px; flex-shrink: 0; }
</style>
@endpush

@section('content')
<div class="container py-4" style="max-width:960px;animation:fadeUp .5s ease both">

    {{-- Breadcrumb --}}
    <nav class="mb-4">
        <a href="{{ route('artist-register.index') }}" class="text-muted small text-decoration-none">
            <i class="fa-solid fa-chevron-left me-1"></i>Gói đăng ký Nghệ sĩ
        </a>
    </nav>

    <div class="row g-4">
        {{-- Form --}}
        <div class="col-12 col-lg-7">
            <div class="form-card">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div style="width:48px;height:48px;background:rgba(168,85,247,.15);border:1px solid rgba(192,132,252,.3);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px">
                        🎤
                    </div>
                    <div>
                        <h4 class="text-white fw-bold mb-0">Thông tin đăng ký Nghệ sĩ</h4>
                        <div class="small text-muted">Điền thông tin nghệ danh của bạn</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('artist-register.checkout', $package->id) }}" id="regForm" hx-boost="false">
                    @csrf

                    {{-- Tên nghệ danh --}}
                    <div class="mb-4">
                        <label class="mmf-label">
                            Tên nghệ danh <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="artist_name" id="artistName"
                               class="form-control mmf-input @error('artist_name') is-invalid @enderror"
                               placeholder="VD: Sơn Tùng M-TP, HIEUTHUHAI, ..."
                               value="{{ old('artist_name', $user->name) }}"
                               minlength="2" maxlength="100" required>
                        <div class="d-flex justify-content-between mt-1">
                            @error('artist_name')
                                <div class="text-danger" style="font-size:.8rem">{{ $message }}</div>
                            @else
                                <div class="text-muted" style="font-size:.75rem">Tên này sẽ hiển thị công khai trên trang cá nhân của bạn.</div>
                            @enderror
                            <span class="char-counter"><span id="nameCount">{{ strlen(old('artist_name', $user->name)) }}</span>/100</span>
                        </div>
                    </div>

                    {{-- Giới thiệu --}}
                    <div class="mb-4">
                        <label class="mmf-label">Giới thiệu bản thân (không bắt buộc)</label>
                        <textarea name="bio" id="bio" rows="4"
                                  class="form-control mmf-input @error('bio') is-invalid @enderror"
                                  placeholder="Mô tả ngắn về bản thân, phong cách âm nhạc, thành tựu nổi bật..."
                                  maxlength="1000">{{ old('bio') }}</textarea>
                        <div class="d-flex justify-content-end mt-1">
                            <span class="char-counter"><span id="bioCount">{{ strlen(old('bio', '')) }}</span>/1000</span>
                        </div>
                        @error('bio')
                            <div class="text-danger mt-1" style="font-size:.8rem">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Lưu ý --}}
                    <div class="p-3 rounded-3 mb-4" style="background:rgba(251,191,36,.06);border:1px solid rgba(251,191,36,.2)">
                        <div class="d-flex gap-2">
                            <i class="fa-solid fa-triangle-exclamation mt-1" style="color:#fbbf24;flex-shrink:0"></i>
                            <div class="small text-muted">
                                Sau khi nhấn <strong class="text-white">Thanh toán ngay</strong>, bạn sẽ được chuyển đến cổng thanh toán VNPAY. 
                                Trước khi thanh toán, vui lòng đọc <a href="{{ route('artist-register.terms') }}" class="text-info text-decoration-none">Điều khoản và dịch vụ dành cho Nghệ sĩ</a>. 
                                Đơn đăng ký sẽ được gửi để xét duyệt sau khi thanh toán thành công. 
            
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="accept_terms"
                                name="accept_terms"
                                value="1"
                                {{ old('accept_terms') ? 'checked' : '' }}
                                required>
                            <label class="form-check-label text-muted small" for="accept_terms">
                                Tôi xác nhận đã đọc và đồng ý <a href="{{ route('artist-register.terms') }}" target="_blank" rel="noopener" class="text-info text-decoration-none">Điều khoản và dịch vụ dành cho Nghệ sĩ</a> trên Blue Wave Music.
                            </label>
                        </div>
                        @error('accept_terms')
                            <div class="text-danger mt-1" style="font-size:.8rem">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-pay text-white">
                        <i class="fa-solid fa-lock me-2"></i>Thanh toán {{ number_format($package->price) }}₫ qua VNPAY
                    </button>
                    <div class="vnpay-badge">
                        <i class="fa-solid fa-shield-halved"></i> Thanh toán bảo mật qua VNPAY
                    </div>
                </form>
            </div>
        </div>

        {{-- Tóm tắt gói --}}
        <div class="col-12 col-lg-5">
            <div class="side-card">
                <div class="text-center mb-4">
                    <div style="font-size:36px;margin-bottom:12px">🎵</div>
                    <h5 class="text-white fw-bold mb-1">{{ $package->name }}</h5>
                    @if($package->description)
                    <p class="text-muted small mb-3">{{ $package->description }}</p>
                    @endif
                    <div class="pkg-price-display">{{ number_format($package->price) }}₫</div>
                    <div class="text-muted" style="font-size:.8rem;margin-top:4px">một lần duy nhất</div>
                </div>

                <hr style="border-color:rgba(255,255,255,.08)">

                @if($package->features->isNotEmpty())
                <div class="mb-4">
                    <div class="small text-muted fw-semibold mb-3 text-uppercase" style="letter-spacing:.06em">Quyền lợi</div>
                    @foreach($package->features as $feat)
                    <div class="feature-item">
                        <i class="fa-solid fa-circle-check feature-icon"></i>
                        <span class="text-muted small">{{ $feat->feature }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="mb-4">
                    @foreach([
                        'Tải lên và quản lý âm nhạc',
                        'Trang hồ sơ nghệ sĩ riêng',
                        'Thống kê lượt nghe & tương tác',
                        'Khả năng nhận tích xanh từ admin',
                        'Hỗ trợ ưu tiên từ Blue Wave Music',
                    ] as $feat)
                    <div class="feature-item">
                        <i class="fa-solid fa-circle-check feature-icon"></i>
                        <span class="text-muted small">{{ $feat }}</span>
                    </div>
                    @endforeach
                </div>
                @endif

                <hr style="border-color:rgba(255,255,255,.08)">

                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Tổng thanh toán</span>
                    <span class="fw-bold" style="color:#c084fc;font-size:1.1rem">{{ number_format($package->price) }}₫</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('artistName').addEventListener('input', function() {
    document.getElementById('nameCount').textContent = this.value.length;
});
document.getElementById('bio').addEventListener('input', function() {
    document.getElementById('bioCount').textContent = this.value.length;
});
</script>
@endpush
