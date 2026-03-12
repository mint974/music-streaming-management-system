@extends('layouts.main')

@section('title', 'Nâng cấp Premium · Blue Wave')

@push('styles')
<style>
/* ══ Keyframes ══════════════════════════════════════════════════════ */
@keyframes gradientShift {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
@keyframes float {
    0%,100% { transform: translateY(0); }
    50%      { transform: translateY(-8px); }
}
@keyframes shimmer {
    0%   { background-position: -200% center; }
    100% { background-position: 200% center; }
}
@keyframes pulseRing {
    0%   { box-shadow: 0 0 0 0 rgba(99,102,241,.45); }
    70%  { box-shadow: 0 0 0 18px rgba(99,102,241,0); }
    100% { box-shadow: 0 0 0 0 rgba(99,102,241,0); }
}
@keyframes progressFill {
    from { width: 0%; }
}
@keyframes fadeUp {
    from { opacity:0; transform: translateY(20px); }
    to   { opacity:1; transform: translateY(0); }
}
@keyframes rotateGradient {
    0%   { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
@keyframes sparkle {
    0%,100% { opacity:.3; transform: scale(.8); }
    50%      { opacity:1;  transform: scale(1); }
}

/* ══ Hero ═══════════════════════════════════════════════════════════ */
.sub-hero {
    position: relative;
    overflow: hidden;
    border-radius: 24px;
    padding: 56px 40px 48px;
    margin-bottom: 32px;
    text-align: center;
    background: linear-gradient(135deg, #0d0d1a 0%, #1a0933 40%, #0a1628 70%, #0d1a2e 100%);
}
.sub-hero::before {
    content: '';
    position: absolute;
    inset: -60%;
    background: conic-gradient(
        from 0deg at 50% 50%,
        rgba(99,102,241,.18) 0deg,
        rgba(168,85,247,.22) 60deg,
        rgba(236,72,153,.18) 120deg,
        rgba(59,130,246,.15) 180deg,
        rgba(99,102,241,.18) 240deg,
        rgba(16,185,129,.12) 300deg,
        rgba(99,102,241,.18) 360deg
    );
    animation: rotateGradient 18s linear infinite;
    opacity: .6;
}
.sub-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 70% 60% at 50% 0%, rgba(99,102,241,.25) 0%, transparent 70%);
    pointer-events: none;
}
.sub-hero-content { position: relative; z-index: 1; }

.sub-hero-icon {
    width: 80px; height: 80px;
    margin: 0 auto 20px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6, #ec4899);
    display: flex; align-items: center; justify-content: center;
    font-size: 32px; color: #fff;
    animation: float 4s ease-in-out infinite, pulseRing 3s ease-in-out infinite;
}
.sub-hero-title {
    font-size: clamp(1.6rem, 4vw, 2.5rem);
    font-weight: 800;
    line-height: 1.15;
    background: linear-gradient(90deg, #e0e7ff, #c4b5fd, #a5b4fc, #818cf8, #c4b5fd, #e0e7ff);
    background-size: 300% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: shimmer 4s linear infinite;
    margin-bottom: 10px;
}
.sub-hero-subtitle {
    color: rgba(255,255,255,.55);
    font-size: .95rem;
    max-width: 480px;
    margin: 0 auto 28px;
    line-height: 1.6;
}
.plan-pill {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 10px 22px; border-radius: 50px;
    font-size: .85rem; font-weight: 600;
    backdrop-filter: blur(12px);
}
.plan-pill.free    { background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12); color: #94a3b8; }
.plan-pill.premium { background: linear-gradient(135deg,rgba(251,191,36,.15),rgba(245,158,11,.08)); border: 1px solid rgba(251,191,36,.35); color: #fbbf24; }

/* ══ Benefits ═══════════════════════════════════════════════════════ */
.benefit-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    gap: 16px; margin-bottom: 40px;
    animation: fadeUp .5s ease .1s both;
}
.benefit-card {
    background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.07);
    border-radius: 16px; padding: 20px;
    transition: border-color .25s, background .25s;
}
.benefit-card:hover { background: rgba(99,102,241,.06); border-color: rgba(99,102,241,.3); }
.benefit-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:12px; }
.benefit-title { color:#e2e8f0;font-size:.88rem;font-weight:600;margin-bottom:4px; }
.benefit-desc  { color:#64748b;font-size:.78rem;line-height:1.5; }

/* ══ Active plan card ════════════════════════════════════════════════ */
.active-plan-card {
    border-radius: 20px; padding: 28px 32px; margin-bottom: 40px;
    position: relative; overflow: hidden;
    background: linear-gradient(135deg,rgba(251,191,36,.07),rgba(245,158,11,.04));
    border: 1px solid rgba(251,191,36,.25);
    animation: fadeUp .5s ease .15s both;
}
.active-plan-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:2px;
    background: linear-gradient(90deg, transparent, #fbbf24, #f59e0b, transparent);
}
.days-progress-wrap { height:6px; background:rgba(255,255,255,.08); border-radius:99px; overflow:hidden; margin-top:12px; }
.days-progress-bar  {
    height:100%; border-radius:99px;
    background: linear-gradient(90deg, #fbbf24, #f59e0b);
    animation: progressFill 1.2s cubic-bezier(.4,0,.2,1) .5s both;
}
.days-progress-bar.danger { background: linear-gradient(90deg, #f87171, #ef4444); }

/* ══ Pricing ════════════════════════════════════════════════════════ */
.pricing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px; margin-bottom: 40px;
    animation: fadeUp .5s ease .2s both;
}
.pricing-card {
    position:relative; border-radius:20px; padding:32px 28px;
    display:flex; flex-direction:column;
    transition: transform .25s, box-shadow .25s, border-color .25s;
    background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.08);
    overflow: hidden;
}
.pricing-card:hover { transform: translateY(-4px); }

.pricing-card.monthly:hover   { border-color:rgba(165,180,252,.35); box-shadow:0 20px 60px rgba(99,102,241,.12); }
.pricing-card.quarterly       { background:rgba(139,92,246,.05); border-color:rgba(139,92,246,.25); }
.pricing-card.quarterly:hover { border-color:rgba(196,181,253,.45); box-shadow:0 20px 60px rgba(139,92,246,.18); }
.pricing-card.yearly          { background:rgba(99,102,241,.07); border-color:rgba(99,102,241,.35); }
.pricing-card.yearly::after   { content:''; position:absolute; top:0; left:0; right:0; height:2px; background:linear-gradient(90deg,transparent,#6366f1,#8b5cf6,transparent); }
.pricing-card.yearly:hover    { border-color:rgba(165,180,252,.5); box-shadow:0 24px 80px rgba(99,102,241,.22); }
.pricing-card.is-active       { border-color:rgba(251,191,36,.45)!important; background:rgba(251,191,36,.05)!important; box-shadow:0 0 0 1px rgba(251,191,36,.2),0 16px 48px rgba(251,191,36,.08)!important; }
.pricing-card.is-active::after { background:linear-gradient(90deg,transparent,#fbbf24,#f59e0b,transparent)!important; display:block!important; }

.badge-pill     { position:absolute; top:22px; right:22px; padding:4px 12px; border-radius:50px; font-size:.65rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; }
.badge-popular  { background:rgba(139,92,246,.2); color:#c4b5fd; border:1px solid rgba(139,92,246,.3); }
.badge-best     { background:rgba(99,102,241,.2);  color:#a5b4fc; border:1px solid rgba(99,102,241,.3); }
.badge-current  { background:rgba(251,191,36,.15); color:#fbbf24; border:1px solid rgba(251,191,36,.3); }

.pkg-name    { font-size:.8rem; color:#64748b; font-weight:500; margin-bottom:8px; text-transform:uppercase; letter-spacing:.07em; }
.pkg-price   { font-size:2.4rem; font-weight:800; color:#e2e8f0; line-height:1; margin-bottom:4px; }
.pkg-price span { font-size:.9rem; font-weight:400; color:#64748b; margin-left:4px; vertical-align:middle; }
.pkg-per-day { color:#64748b; font-size:.78rem; margin-bottom:16px; }
.pkg-days    { display:inline-block; background:rgba(255,255,255,.05); border-radius:8px; padding:4px 10px; font-size:.75rem; color:#94a3b8; margin-bottom:20px; }
.pkg-features { list-style:none; padding:0; margin:0 0 24px; }
.pkg-features li { display:flex; align-items:center; gap:8px; color:#94a3b8; font-size:.8rem; padding:5px 0; }
.pkg-features li i { color:#34d399; font-size:.7rem; flex-shrink:0; }
.pkg-desc { color:#64748b; font-size:.8rem; line-height:1.7; flex-grow:1; margin-bottom:24px; }

.btn-pricing {
    display:block; width:100%; padding:12px; border-radius:12px; border:none;
    font-size:.85rem; font-weight:600; cursor:pointer; transition:all .2s;
    text-align:center;
}
.btn-pricing.monthly-btn   { background:rgba(165,180,252,.15); color:#a5b4fc; border:1px solid rgba(165,180,252,.25); }
.btn-pricing.monthly-btn:hover   { background:rgba(99,102,241,.7); color:#fff; box-shadow:0 8px 24px rgba(99,102,241,.3); }
.btn-pricing.quarterly-btn { background:rgba(196,181,253,.15); color:#c4b5fd; border:1px solid rgba(196,181,253,.25); }
.btn-pricing.quarterly-btn:hover { background:rgba(139,92,246,.7); color:#fff; box-shadow:0 8px 24px rgba(139,92,246,.3); }
.btn-pricing.yearly-btn    { background:linear-gradient(135deg,rgba(99,102,241,.85),rgba(139,92,246,.85)); color:#fff; }
.btn-pricing.yearly-btn:hover    { background:linear-gradient(135deg,#6366f1,#8b5cf6); box-shadow:0 8px 32px rgba(99,102,241,.4); transform:translateY(-1px); }
.btn-pricing.active-btn    { background:rgba(251,191,36,.1); color:#fbbf24; border:1px solid rgba(251,191,36,.2); cursor:default; }

/* Sparkle */
.sparkle { position:absolute; width:4px; height:4px; background:#fbbf24; border-radius:50%; animation:sparkle 2s ease-in-out infinite; }

/* ══ History ════════════════════════════════════════════════════════ */
.history-wrap { border-radius:16px; overflow:hidden; border:1px solid rgba(255,255,255,.07); animation:fadeUp .5s ease .3s both; }
.history-wrap table { background:rgba(255,255,255,.015); }
.history-wrap thead th { background:rgba(255,255,255,.03); color:#475569; font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.07em; border-color:rgba(255,255,255,.06)!important; padding:14px 16px; }
.history-wrap tbody tr { border-color:rgba(255,255,255,.05)!important; transition:background .15s; }
.history-wrap tbody tr:hover { background:rgba(255,255,255,.02); }
.history-wrap tbody td { border-color:rgba(255,255,255,.05)!important; padding:14px 16px; vertical-align:middle; }

/* ══ Security strip ══════════════════════════════════════════════════ */
.security-strip { display:flex; align-items:center; justify-content:center; flex-wrap:wrap; gap:20px; padding:20px 0; border-top:1px solid rgba(255,255,255,.05); margin-top:40px; animation:fadeUp .5s ease .35s both; }
.security-item { display:flex; align-items:center; gap:6px; color:#475569; font-size:.78rem; }
</style>
@endpush

@section('content')
<div class="sub-page" style="max-width:900px;margin:0 auto;padding:24px 16px 60px">

    {{-- ── Flash messages ─────────────────────────────────────────── --}}
    @if(session('success'))
    <div class="alert alert-dismissible fade show mb-4" role="alert"
         style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3);color:#6ee7b7;border-radius:12px">
        <i class="fa-solid fa-circle-check me-2"></i>{!! session('success') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert" style="filter:invert(.5)"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-dismissible fade show mb-4" role="alert"
         style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:#fca5a5;border-radius:12px">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>{!! session('error') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert" style="filter:invert(.5)"></button>
    </div>
    @endif
    @if(session('info'))
    <div class="alert alert-dismissible fade show mb-4" role="alert"
         style="background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.3);color:#a5b4fc;border-radius:12px">
        <i class="fa-solid fa-circle-info me-2"></i>{!! session('info') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert" style="filter:invert(.5)"></button>
    </div>
    @endif

    {{-- ── Hero ──────────────────────────────────────────────────── --}}
    <div class="sub-hero">
        <div class="sparkle" style="top:18%;left:12%;animation-delay:.4s"></div>
        <div class="sparkle" style="top:25%;right:15%;animation-delay:.8s;width:5px;height:5px;background:#a5b4fc"></div>
        <div class="sparkle" style="bottom:22%;left:20%;animation-delay:1.2s;background:#c4b5fd"></div>
        <div class="sparkle" style="bottom:30%;right:10%;animation-delay:.2s;width:3px;height:3px"></div>

        <div class="sub-hero-content">
            <div class="sub-hero-icon">
                <i class="fa-solid fa-music"></i>
            </div>
            @if($activeSub)
            <h1 class="sub-hero-title">Bạn đang dùng<br>{{ $activeSub->vip->title }}</h1>
            <p class="sub-hero-subtitle">Tận hưởng âm nhạc không giới hạn, không quảng cáo mỗi ngày.</p>
            <div class="plan-pill premium">
                <i class="fa-solid fa-crown" style="font-size:.85rem"></i>
                <span>Premium &middot; Còn {{ $activeSub->daysRemaining() }} ngày</span>
            </div>
            @else
            <h1 class="sub-hero-title">Nâng cấp lên<br>Blue Wave Premium</h1>
            <p class="sub-hero-subtitle">Nghe nhạc không giới hạn, tải xuống offline, không có quảng cáo — trải nghiệm âm nhạc như chưa từng có.</p>
            <div class="plan-pill free">
                <i class="fa-solid fa-user" style="font-size:.8rem"></i>
                <span>Gói Free &middot; Hãy nâng cấp ngay</span>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Active plan status card ──────────────────────────────── --}}
    @if($activeSub)
    @php
        $totalDays = $activeSub->vip->duration_days ?: 1;
        $remaining = $activeSub->daysRemaining();
        $used      = $totalDays - $remaining;
        $pct       = max(0, min(100, round(($remaining / $totalDays) * 100)));
        $isDanger  = $remaining <= 7;
    @endphp
    <div class="active-plan-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <div style="width:50px;height:50px;border-radius:14px;background:linear-gradient(135deg,rgba(251,191,36,.2),rgba(245,158,11,.1));border:1px solid rgba(251,191,36,.3);display:flex;align-items:center;justify-content:center">
                    <i class="fa-solid fa-crown" style="color:#fbbf24;font-size:1.1rem"></i>
                </div>
                <div>
                    <div class="fw-bold text-white">{{ $activeSub->vip->title }}</div>
                    <div class="small" style="color:#94a3b8">
                        {{ $activeSub->start_date->format('d/m/Y') }}
                        <i class="fa-solid fa-arrow-right mx-1" style="font-size:.65rem"></i>
                        {{ $activeSub->end_date->format('d/m/Y') }}
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-center">
                    <div class="fw-bold" style="font-size:1.6rem;color:{{ $isDanger ? '#f87171' : '#fbbf24' }};line-height:1">
                        {{ $remaining }}
                    </div>
                    <div style="font-size:.7rem;color:#64748b">ngày còn lại</div>
                </div>
                <button type="button" class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#cancelSubModal"
                        style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.25);color:#fca5a5;border-radius:10px;font-size:.78rem">
                    <i class="fa-solid fa-ban me-1"></i>Hủy gói
                </button>
            </div>
        </div>
        <div class="d-flex justify-content-between mb-2" style="font-size:.75rem;color:#64748b">
            <span>Đã dùng {{ $used }} ngày</span>
            <span>{{ $pct }}% còn lại</span>
        </div>
        <div class="days-progress-wrap">
            <div class="days-progress-bar {{ $isDanger ? 'danger' : '' }}" style="width:{{ $pct }}%"></div>
        </div>
        @if($isDanger)
        <div class="mt-3 d-flex align-items-center gap-2 small" style="color:#f87171">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Gói sắp hết hạn! Gia hạn ngay bằng cách chọn gói bên dưới.
        </div>
        @endif
    </div>
    @endif

    {{-- ── Benefits (only when not subscribed) ─────────────────── --}}
    @if(!$activeSub)
    <div class="benefit-row">
        <div class="benefit-card">
            <div class="benefit-icon" style="background:rgba(99,102,241,.15)">
                <i class="fa-solid fa-infinity" style="color:#818cf8"></i>
            </div>
            <div class="benefit-title">Nghe không giới hạn</div>
            <div class="benefit-desc">Truy cập toàn bộ thư viện hàng triệu bài hát không bị giới hạn.</div>
        </div>
        <div class="benefit-card">
            <div class="benefit-icon" style="background:rgba(16,185,129,.12)">
                <i class="fa-solid fa-download" style="color:#34d399"></i>
            </div>
            <div class="benefit-title">Tải xuống offline</div>
            <div class="benefit-desc">Lưu nhạc yêu thích vào thiết bị và nghe ngay cả khi không có mạng.</div>
        </div>
        <div class="benefit-card">
            <div class="benefit-icon" style="background:rgba(251,191,36,.12)">
                <i class="fa-solid fa-shield-halved" style="color:#fbbf24"></i>
            </div>
            <div class="benefit-title">Không quảng cáo</div>
            <div class="benefit-desc">Thưởng thức âm nhạc liền mạch, không bị gián đoạn.</div>
        </div>
        <div class="benefit-card">
            <div class="benefit-icon" style="background:rgba(236,72,153,.12)">
                <i class="fa-solid fa-waveform-lines" style="color:#f472b6"></i>
            </div>
            <div class="benefit-title">Chất lượng cao</div>
            <div class="benefit-desc">Nghe nhạc lossless, chất lượng studio tại nhà bạn.</div>
        </div>
    </div>
    @endif

    {{-- ── Pricing cards ─────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div style="color:#475569;font-size:.75rem;font-weight:600;letter-spacing:.07em;text-transform:uppercase">
            {{ $activeSub ? 'Gia hạn / Đổi gói' : 'Chọn gói của bạn' }}
        </div>
        <div style="color:#475569;font-size:.75rem">Tất cả đều có đầy đủ quyền lợi Premium</div>
    </div>
    @if($activeSub)
    <div class="mb-4 p-3" style="background:rgba(251,191,36,.07);border:1px solid rgba(251,191,36,.25);border-radius:12px">
        <div class="d-flex align-items-start gap-2">
            <i class="fa-solid fa-triangle-exclamation mt-1" style="color:#fbbf24;font-size:.85rem;flex-shrink:0"></i>
            <div class="small" style="color:#94a3b8;line-height:1.6">
                <strong style="color:#fbbf24">Lưu ý về chính sách thanh toán:</strong>
                Chuyển sang gói khác sẽ <strong class="text-white">hủy gói hiện tại ngay lập tức</strong>.
                Thời gian còn lại của gói cũ <strong class="text-white">sẽ không được hoàn tiền</strong>.
                Bạn sẽ được thanh toán toàn bộ giá trị gói mới.
            </div>
        </div>
    </div>
    @endif

    <div class="pricing-grid">
        @forelse($vips as $vip)
        @php
            $isCurrentPlan = $activeSub && $activeSub->vip_id === $vip->id;
            $isYearly      = str_contains(strtolower($vip->id), 'year')    || $vip->duration_days >= 300;
            $isQuarterly   = str_contains(strtolower($vip->id), 'quarter') || ($vip->duration_days >= 80 && $vip->duration_days < 300);
            $typeClass     = $isYearly ? 'yearly' : ($isQuarterly ? 'quarterly' : 'monthly');
            $btnClass      = $isYearly ? 'yearly-btn' : ($isQuarterly ? 'quarterly-btn' : 'monthly-btn');
        @endphp
        <div class="pricing-card {{ $typeClass }} {{ $isCurrentPlan ? 'is-active' : '' }}">
            @if($isCurrentPlan)
                <span class="badge-pill badge-current"><i class="fa-solid fa-check me-1"></i>Đang dùng</span>
            @elseif($isYearly)
                <span class="badge-pill badge-best">✦ Tốt nhất</span>
            @elseif($isQuarterly)
                <span class="badge-pill badge-popular">Phổ biến</span>
            @endif

            <div class="pkg-name">{{ $vip->title }}</div>
            <div class="pkg-price">{{ number_format($vip->price) }}<span>₫</span></div>
            <div class="pkg-per-day">≈ {{ number_format($vip->price / max(1, $vip->duration_days), 0) }} ₫ / ngày</div>
            <div class="pkg-days"><i class="fa-regular fa-clock me-1"></i>{{ number_format($vip->duration_days) }} ngày</div>

            <ul class="pkg-features">
                <li><i class="fa-solid fa-circle-check"></i> Nghe không giới hạn</li>
                <li><i class="fa-solid fa-circle-check"></i> Tải xuống offline</li>
                <li><i class="fa-solid fa-circle-check"></i> Không quảng cáo</li>
                @if(!str_contains(strtolower($vip->id), 'month'))
                <li><i class="fa-solid fa-circle-check"></i> Ưu tiên hỗ trợ</li>
                @endif
                @if($isYearly)
                <li><i class="fa-solid fa-circle-check"></i> Tiết kiệm 30% so với tháng</li>
                @elseif($isQuarterly)
                <li><i class="fa-solid fa-circle-check"></i> Tiết kiệm 15% so với tháng</li>
                @endif
            </ul>

            @if($vip->description)
            <p class="pkg-desc">{{ $vip->description }}</p>
            @endif

            @if($isCurrentPlan)
            <button class="btn-pricing active-btn" disabled>
                <i class="fa-solid fa-check me-2"></i>Gói đang sử dụng
            </button>
            @else
            <form method="POST" action="{{ route('subscription.checkout', $vip->id) }}">
                @csrf
                @if($activeSub)
                <button type="submit" class="btn-pricing {{ $btnClass }}"
                        onclick="if(!confirm('Chuyển sang gói {{ addslashes($vip->title) }}?\n\nGói hiện tại sẽ bị hủy và thời gian còn lại KHÔNG được hoàn tiền.\nBạn xác nhận tiếp tục?')){return false;}this.disabled=true;this.innerHTML='<i class=\'fa-solid fa-spinner fa-spin me-2\'></i>Đang chuyển...';this.form.submit()">
                    <i class="fa-solid fa-bolt me-2"></i>Chuyển sang gói này
                </button>
                @else
                <button type="submit" class="btn-pricing {{ $btnClass }}"
                        onclick="this.disabled=true;this.innerHTML='<i class=\'fa-solid fa-spinner fa-spin me-2\'></i>Đang chuyển...';this.form.submit()">
                    <i class="fa-solid fa-bolt me-2"></i>Đăng ký ngay
                </button>
                @endif
            </form>
            @endif
        </div>
        @empty
        <div style="grid-column:1/-1;text-align:center;padding:60px 0;color:#475569">
            <i class="fa-solid fa-crown fa-2x mb-3 d-block" style="opacity:.2"></i>Chưa có gói nào.
        </div>
        @endforelse
    </div>

    {{-- ── Billing history ─────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div style="color:#475569;font-size:.75rem;font-weight:600;letter-spacing:.07em;text-transform:uppercase">
            Lịch sử thanh toán
        </div>
    </div>

    <div class="history-wrap">
        <div class="table-responsive">
            <table class="table table-dark mb-0">
                <thead>
                    <tr>
                        <th>Gói</th>
                        <th>Thời gian hiệu lực</th>
                        <th class="text-end">Số tiền</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Thanh toán</th>
                        <th class="text-center">Hoàn tiền</th>
                        <th class="text-end pe-4">Mã GD</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $sub)
                    @php
                        $sColor = match($sub->status) {
                            'active'    => ['bg'=>'rgba(52,211,153,.12)',  'border'=>'rgba(52,211,153,.3)',  'text'=>'#6ee7b7'],
                            'pending'   => ['bg'=>'rgba(251,191,36,.1)',   'border'=>'rgba(251,191,36,.3)',  'text'=>'#fbbf24'],
                            'cancelled' => ['bg'=>'rgba(248,113,113,.1)', 'border'=>'rgba(248,113,113,.25)','text'=>'#fca5a5'],
                            default     => ['bg'=>'rgba(100,116,139,.1)',  'border'=>'rgba(100,116,139,.25)','text'=>'#94a3b8'],
                        };
                        $pColor = $sub->payment ? match($sub->payment->status) {
                            'paid'    => ['bg'=>'rgba(52,211,153,.12)',  'border'=>'rgba(52,211,153,.3)',  'text'=>'#6ee7b7'],
                            'pending' => ['bg'=>'rgba(251,191,36,.1)',   'border'=>'rgba(251,191,36,.3)',  'text'=>'#fbbf24'],
                            'failed'  => ['bg'=>'rgba(248,113,113,.1)', 'border'=>'rgba(248,113,113,.25)','text'=>'#fca5a5'],
                            default   => ['bg'=>'rgba(100,116,139,.1)',  'border'=>'rgba(100,116,139,.2)', 'text'=>'#64748b'],
                        } : null;
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold" style="color:#e2e8f0;font-size:.85rem">{{ $sub->vip->title ?? '—' }}</div>
                            <div style="color:#475569;font-size:.72rem">{{ $sub->vip?->duration_days }} ngày</div>
                        </td>
                        <td>
                            <div style="color:#94a3b8;font-size:.82rem">{{ $sub->start_date->format('d/m/Y') }}</div>
                            <div style="color:#475569;font-size:.72rem">→ {{ $sub->end_date->format('d/m/Y') }}</div>
                        </td>
                        <td class="text-end">
                            <span class="fw-semibold" style="color:#fbbf24;font-size:.88rem">{{ number_format($sub->amount_paid) }} ₫</span>
                        </td>
                        <td class="text-center">
                            <span style="background:{{ $sColor['bg'] }};border:1px solid {{ $sColor['border'] }};color:{{ $sColor['text'] }};border-radius:50px;padding:3px 10px;font-size:.7rem;font-weight:600;white-space:nowrap">
                                {{ $sub->statusLabel() }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($pColor)
                            <span style="background:{{ $pColor['bg'] }};border:1px solid {{ $pColor['border'] }};color:{{ $pColor['text'] }};border-radius:50px;padding:3px 10px;font-size:.7rem;font-weight:600;white-space:nowrap">
                                {{ $sub->payment->statusLabel() }}
                            </span>
                            @else
                            <span style="color:#334155">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($sub->payment?->isRefunded())
                            <span style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.25);color:#34d399;border-radius:50px;padding:3px 10px;font-size:.7rem;font-weight:600;white-space:nowrap"
                                  title="Hoàn tiền ngày {{ $sub->payment->refunded_at?->format('d/m/Y') }}">
                                <i class="fa-solid fa-rotate-left me-1" style="font-size:.65rem"></i>{{ number_format($sub->payment->refund_amount) }} ₫
                            </span>
                            @else
                            <span style="color:#334155">—</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            @if($sub->payment?->transaction_code)
                            <span style="font-family:monospace;font-size:.7rem;color:#475569" title="{{ $sub->payment->transaction_code }}">
                                {{ \Illuminate\Support\Str::limit($sub->payment->transaction_code, 18) }}
                            </span>
                            @else
                            <span style="color:#334155">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:48px 0;color:#334155">
                            <i class="fa-solid fa-receipt fa-2x d-block mb-3" style="opacity:.2"></i>
                            Chưa có lịch sử thanh toán nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($history->hasPages())
    <div class="d-flex justify-content-center mt-3">{{ $history->links() }}</div>
    @endif

    {{-- ── Security strip ──────────────────────────────────────── --}}
    <div class="security-strip">
        <div class="security-item"><i class="fa-solid fa-lock" style="color:#34d399"></i><span>Thanh toán bảo mật SSL</span></div>
        <div class="security-item"><i class="fa-solid fa-shield-halved" style="color:#818cf8"></i><span>Xử lý qua VNPAY</span></div>
        <div class="security-item"><i class="fa-solid fa-rotate-left" style="color:#f472b6"></i><span>Hủy bất kỳ lúc nào</span></div>
        <div class="security-item"><i class="fa-solid fa-headset" style="color:#fbbf24"></i><span>Hỗ trợ 24/7</span></div>
    </div>

</div>

{{-- ── Modal xác nhận hủy gói ──────────────────────────────────────── --}}
@if(isset($activeSub))
<div class="modal fade" id="cancelSubModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#1e293b;border:1px solid rgba(255,255,255,.12);border-radius:16px">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-ban me-2 text-danger"></i>Xác nhận hủy gói
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <p class="text-muted small mb-3">
                    Bạn đang hủy gói <strong class="text-white">{{ $activeSub->vip->title ?? '' }}</strong>
                    với <strong class="text-white">{{ $remaining ?? 0 }} ngày</strong> còn lại.
                </p>
                <div class="mb-3 p-3" style="background:rgba(248,113,113,.07);border:1px solid rgba(248,113,113,.25);border-radius:10px">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-triangle-exclamation" style="color:#f87171"></i>
                        <span class="fw-semibold" style="color:#fca5a5;font-size:.9rem">Chính sách không hoàn tiền</span>
                    </div>
                    <div class="small" style="color:#94a3b8;line-height:1.6">
                        Việc hủy gói đăng ký <strong class="text-white">sẽ không được hoàn lại tiền</strong>
                        dù còn bao nhiêu ngày sử dụng. Thời gian còn lại của gói sẽ bị mất ngay khi xác nhận hủy.
                    </div>
                </div>
                <p class="small mb-0" style="color:#64748b">
                    Sau khi hủy, tài khoản sẽ trở về <strong class="text-white">Free</strong> ngay lập tức.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Giữ gói</button>
                <form method="POST" action="{{ route('subscription.cancel', $activeSub->id) }}" style="display:inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger px-4">
                        <i class="fa-solid fa-ban me-1"></i>Xác nhận hủy
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
