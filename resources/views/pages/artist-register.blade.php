@extends('layouts.main')
@section('title', 'Đăng ký Nghệ sĩ · Blue Wave')

@section('content')
<style>
/* CSS Gemini Advanced / Artist Pro Style */
:root {
    --art-bg: #0f1015;
    --art-card: #16181f;
    --art-border: rgba(255, 255, 255, 0.08);
    --art-grad: linear-gradient(110deg, #8b5cf6 0%, #d946ef 50%, #f43f5e 100%);
    --art-text: linear-gradient(to right, #c4b5fd, #f9a8d4, #fda4af);
}

.sub-page {
    position: relative;
    background-color: transparent;
    overflow: hidden;
}

.sub-page::before {
    content: '';
    position: absolute;
    top: -20%; left: 0%; right: 0%; bottom: 40%;
    background: radial-gradient(circle at 50% 0%, rgba(217, 70, 239, 0.15) 0%, rgba(0,0,0,0) 60%);
    filter: blur(80px);
    z-index: -2;
    pointer-events: none;
}

.hero-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 24px;
}
.hero-title .glow-text {
    background: var(--art-grad);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: gradientShift 6s ease-in-out infinite alternate;
    background-size: 200% 200%;
}

.hero-subtitle {
    color: #a1a1aa;
    font-size: 1.1rem;
    max-width: 650px;
    margin: 0 auto;
    line-height: 1.6;
}

/* Artist Pricing Cards */
.pricing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.gemini-card {
    position: relative;
    background: var(--art-card);
    border-radius: 1.5rem;
    padding: 2.5rem 2rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.5);
    z-index: 1;
    display: flex;
    flex-direction: column;
}

.gemini-card.glow-card {
    border: none;
}

.gemini-card.glow-card::before {
    content: '';
    position: absolute;
    inset: -2px;
    border-radius: 1.6rem;
    background: linear-gradient(90deg, #f43f5e, #d946ef, #8b5cf6, #f43f5e);
    background-size: 300% 300%;
    animation: gradientSpin 5s linear infinite;
    z-index: -2;
}

.gemini-card.glow-card::after {
    content: '';
    position: absolute;
    inset: 1px;
    border-radius: 1.5rem;
    background: var(--art-card);
    z-index: -1;
}

.gemini-card.standard-card {
    border: 1px solid var(--art-border);
    transition: transform 0.3s;
}
.gemini-card.standard-card:hover {
    transform: translateY(-5px);
    border-color: rgba(255,255,255,0.2);
}

.p-name {
    font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 0.5rem;
}
.p-price-wrap { margin: 1.5rem 0 2rem; }
.p-price { font-size: 3rem; font-weight: 800; color: #fff; line-height: 1; }
.p-curr { font-size: 1rem; color: #a1a1aa; font-weight: 500; }
.p-desc { color: #a1a1aa; font-size: 0.9rem; line-height: 1.5; }

.p-features { list-style: none; padding: 0; margin: 2rem 0; flex-grow: 1; }
.p-features li { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 1rem; color: #d4d4d8; font-size: 0.95rem; }
.p-features li i { color: #d946ef; font-size: 0.9rem; margin-top: 3px; }

.btn-g { display: block; width: 100%; padding: 14px; border-radius: 50px; text-align: center; font-weight: 600; font-size: 1rem; transition: all 0.3s ease; text-decoration: none; border: none; }
.btn-g.solid { background: #fff; color: #000; }
.btn-g.solid:hover { background: #e4e4e7; }
.btn-g.gradient { background: var(--art-grad); color: #fff; box-shadow: 0 4px 15px rgba(217, 70, 239, 0.3); }
.btn-g.gradient:hover { box-shadow: 0 6px 25px rgba(217, 70, 239, 0.5); transform: translateY(-2px); }
.btn-g.disabled-st { background: rgba(255,255,255,0.05); color: #a1a1aa; pointer-events: none; }

/* Notice Banners */
.notice-banner {
    background: rgba(217, 70, 239, 0.1);
    border: 1px solid rgba(217, 70, 239, 0.3);
    border-radius: 1rem; padding: 1.5rem; margin-top: 2rem;
}

@keyframes gradientSpin {
    0% { background-position: 0% 50%; }
    100% { background-position: 100% 50%; }
}
@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    100% { background-position: 100% 50%; }
}
.fade-in-up { animation: fadeInUp 0.6s ease forwards; opacity: 0; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>
<div class="sub-page py-5">
    <div class="container" style="max-width: 1100px;">
        
        {{-- Hero --}}
        <div class="text-center fade-in-up">
            <h1 class="hero-title">Trở thành <span class="glow-text">Nghệ Sĩ PRO</span></h1>
            <p class="hero-subtitle">Bước lên sân khấu số, phát hành album độc quyền, tương tác với fan toàn cầu và đón nhận tích xanh chính chủ cho nhãn hiệu cá nhân.</p>
        </div>

        {{-- Pending Alert --}}
        @if(isset($pending) && $pending)
        <div class="notice-banner fade-in-up mx-auto max-w-3xl mt-5">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <i class="fa-solid fa-clock-rotate-left fs-2" style="color: #f472b6;"></i>
                <div class="flex-grow-1">
                    <h5 class="text-white fw-bold mb-1">
                        {{ $pending->isPendingPayment() ? 'Đơn đang chờ thanh toán' : 'Đơn của bạn đang được duyệt!' }}
                    </h5>
                    <p class="text-muted small mb-0">
                        Nghệ danh "{{ $pending->artist_name }}" đang ở trạng thái
                        <strong class="text-white">{{ $pending->statusLabel() }}</strong>.
                        @if($pending->isPendingPayment())
                            Bạn có thể tiếp tục thanh toán hoặc hủy đơn này để đăng ký lại từ đầu.
                        @else
                            Đội ngũ admin sẽ gửi email xác nhận ngay khi hồ sơ được thông qua.
                        @endif
                    </p>
                </div>
                @if($pending->isPendingPayment())
                <div class="d-flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('artist-register.payPending', $pending->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-light fw-semibold">
                            <i class="fa-solid fa-credit-card me-1"></i>Tiếp tục thanh toán
                        </button>
                    </form>
                    <form method="POST" action="{{ route('artist-register.cancelPending', $pending->id) }}" onsubmit="return confirm('Hủy đơn chờ thanh toán này?')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light fw-semibold">
                            <i class="fa-solid fa-xmark me-1"></i>Hủy đơn
                        </button>
                    </form>
                </div>
                @endif
                </div>
            </div>
        </div>
        @endif
        
        @if(isset($cooldownEnds) && $cooldownEnds)
        <div class="notice-banner fade-in-up mx-auto max-w-3xl mt-5" style="background: rgba(244, 63, 94, 0.1); border-color: rgba(244, 63, 94, 0.3);">
            <div class="d-flex align-items-center gap-3">
                <i class="fa-solid fa-ban fs-2 text-danger"></i>
                <div>
                    <h5 class="text-white fw-bold mb-1">Đơn bị từ chối</h5>
                    <p class="text-muted small mb-0">Vui lòng nộp lại vào lúc {{ $cooldownEnds->format('H:i, d/m/Y') }} sau khi đã chuẩn bị hồ sơ đầy đủ.</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Packages --}}
        <div class="pricing-grid fade-in-up" style="animation-delay: 0.2s">
            @forelse($packages as $pkg)
            <div class="gemini-card {{ $loop->first ? 'glow-card' : 'standard-card' }}">
                @if($loop->first)
                    <div class="position-absolute top-0 end-0 mt-3 me-3 text-white px-3 py-1 fw-bold rounded-pill" style="font-size: 0.75rem; background: var(--art-grad);">ĐỐI TÁC TIN CẬY</div>
                @endif

                <h3 class="p-name"><i class="fa-solid fa-microphone-lines" style="color: #fda4af;"></i> {{ $pkg->name }}</h3>
                <p class="p-desc">{{ $pkg->description ?: 'Bản quyền phát hành, dashboard báo cáo thu nhập và quản lý fanbase.' }}</p>
                
                <div class="p-price-wrap">
                    <span class="p-price">{{ number_format($pkg->price) }}</span><span class="p-curr">vnđ</span>
                    <div class="text-muted small mt-1">Một lần kích hoạt</div>
                </div>

                <ul class="p-features">
                    @forelse($pkg->features as $feat)
                        <li><i class="fa-solid fa-check"></i> {{ $feat->feature }}</li>
                    @empty
                        <li><i class="fa-solid fa-check"></i> Huy hiệu "Nghệ sĩ chính thức"</li>
                        <li><i class="fa-solid fa-check"></i> Tự do upload Album & Bài hát</li>
                        <li><i class="fa-solid fa-check"></i> Tuỳ chỉnh trang tiểu sử</li>
                        <li><i class="fa-solid fa-check"></i> Email Thông báo riêng tới Người theo dõi</li>
                    @endforelse
                </ul>

                @if(isset($pending) && $pending && $pending->isPendingPayment())
                    @if($pending->package_id == $pkg->id)
                        <form method="POST" action="{{ route('artist-register.payPending', $pending->id) }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn-g gradient" hx-boost="false"><i class="fa-solid fa-credit-card me-2"></i>Tiếp tục Thanh toán</button>
                        </form>
                    @else
                        <button class="btn-g disabled-st">Đang có đơn chờ thanh toán</button>
                    @endif
                @elseif(isset($pending) && $pending && !$pending->isPendingPayment())
                    <button class="btn-g disabled-st">Đang xét duyệt...</button>
                @elseif(isset($cooldownEnds) && $cooldownEnds)
                    <button class="btn-g disabled-st">Chờ {{ $cooldownEnds->diffForHumans() }} nữa</button>
                @else
                    <a href="{{ route('artist-register.create', $pkg->id) }}" class="btn-g {{ $loop->first ? 'gradient' : 'solid' }}" hx-boost="false">Bắt đầu hồ sơ & Thanh toán</a>
                @endif
            </div>
            @empty
            <div class="text-center text-muted mx-auto py-5 w-100">
                <i class="fa-solid fa-record-vinyl fa-3x opacity-50 mb-3"></i>
                <p>Nền tảng chưa mở cổng đăng ký gói Nghệ Sĩ cho cộng đồng.</p>
            </div>
            @endforelse
        </div>
        
        <div class="text-center mt-5 pt-4 text-muted small border-top" style="border-color: rgba(255,255,255,0.05) !important;">
            Bước tiến vững chắc cho nền công nghiệp âm nhạc kỹ thuật số. Cung cấp API, bảo mật bản quyền hoàn hảo.
        </div>

        {{-- Lịch sử giao dịch --}}
        @if(isset($registrationHistory) && $registrationHistory->isNotEmpty())
        <div class="mt-5">
            <h5 class="text-white mb-3"><i class="fa-solid fa-receipt me-2 text-muted"></i>Lịch sử đăng ký</h5>
            <div class="table-responsive rounded-3 border" style="border-color: rgba(255,255,255,0.1) !important;">
                 <table class="table table-dark table-hover mb-0 align-middle">
                     <thead>
                         <tr>
                             <th>Nghệ danh</th>
                             <th>Gói</th>
                             <th class="text-end">Thanh toán</th>
                             <th class="text-center">Trạng thái</th>
                             <th class="text-center">Ngày tạo</th>
                         </tr>
                     </thead>
                     <tbody>
                         @foreach($registrationHistory as $reg)
                         <tr>
                             <td>
                                 <div class="fw-bold">{{ $reg->artist_name }}</div>
                             </td>
                             <td>
                                 <div class="small fw-semibold text-info">{{ $reg->package->name ?? '—' }}</div>
                             </td>
                             <td class="text-end fw-semibold text-warning">
                                 {{ number_format($reg->amount_paid) }} ₫
                             </td>
                             <td class="text-center">
                                 <span class="badge {{ $reg->status === 'approved' ? 'bg-success' : ($reg->status === 'pending_review' ? 'bg-primary' : 'bg-danger') }}">
                                     {{ $reg->statusLabel() }}
                                 </span>
                             </td>
                             <td class="text-center text-muted small">
                                 {{ $reg->created_at->format('d/m/Y') }}
                             </td>
                         </tr>
                         @endforeach
                     </tbody>
                 </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
