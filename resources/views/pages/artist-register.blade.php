@extends('layouts.main')

@section('title', 'Đăng ký Nghệ sĩ · Blue Wave Music')

@push('styles')
<style>
@keyframes rotateGradient { 0%{transform:rotate(0deg)} 100%{transform:rotate(360deg)} }
@keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
@keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }

.art-hero {
    position: relative; overflow: hidden;
    border-radius: 24px; padding: 56px 40px 48px;
    margin-bottom: 32px; text-align: center;
    background: linear-gradient(135deg, #0d0d1a 0%, #1a0533 40%, #0a0f28 70%, #120d1a 100%);
}
.art-hero::before {
    content: ''; position: absolute; inset: -60%;
    background: conic-gradient(
        from 0deg at 50% 50%,
        rgba(168,85,247,.2) 0deg, rgba(236,72,153,.18) 60deg,
        rgba(99,102,241,.15) 120deg, rgba(168,85,247,.2) 240deg,
        rgba(192,132,252,.15) 300deg, rgba(168,85,247,.2) 360deg
    );
    animation: rotateGradient 18s linear infinite; opacity:.6;
}
.art-hero::after {
    content: ''; position: absolute; inset: 0;
    background: radial-gradient(ellipse 70% 60% at 50% 0%, rgba(168,85,247,.3) 0%, transparent 70%);
    pointer-events: none;
}
.art-hero-content { position: relative; z-index: 1; }

.art-hero-icon {
    width: 80px; height: 80px; margin: 0 auto 20px;
    background: linear-gradient(135deg, rgba(168,85,247,.25), rgba(192,132,252,.15));
    border: 1px solid rgba(192,132,252,.4); border-radius: 22px;
    display: flex; align-items: center; justify-content: center;
    font-size: 36px;
    animation: float 4s ease-in-out infinite;
}

.pkg-card {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 20px; padding: 32px 28px;
    transition: transform .25s, border-color .25s, box-shadow .25s;
    cursor: pointer; height: 100%;
}
.pkg-card:hover {
    transform: translateY(-6px);
    border-color: rgba(192,132,252,.45);
    box-shadow: 0 20px 50px rgba(168,85,247,.18);
}
.pkg-card.featured {
    border-color: rgba(192,132,252,.4);
    background: linear-gradient(135deg, rgba(168,85,247,.08) 0%, rgba(99,102,241,.05) 100%);
}
.pkg-icon {
    width: 60px; height: 60px; margin: 0 auto 20px;
    background: linear-gradient(135deg, rgba(168,85,247,.2), rgba(192,132,252,.12));
    border: 1px solid rgba(192,132,252,.35); border-radius: 16px;
    display: flex; align-items: center; justify-content: center; font-size: 26px;
}
.pkg-price {
    font-size: 2.2rem; font-weight: 800;
    background: linear-gradient(135deg, #c084fc, #f0abfc);
    -webkit-background-clip: text; background-clip: text; color: transparent;
    line-height: 1;
}
.pkg-price-label { color: #94a3b8; font-size: .8rem; margin-top: 4px; }
.feature-item { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 10px; }
.feature-icon { color: #c084fc; font-size: .8rem; margin-top: 2px; flex-shrink: 0; }
.feature-text { color: #cbd5e1; font-size: .85rem; line-height: 1.4; }
.btn-register {
    background: linear-gradient(135deg, #7c3aed, #c084fc) !important;
    border: none; border-radius: 12px; font-weight: 600; letter-spacing: .02em;
    padding: 12px 32px; width: 100%; font-size: .95rem;
    transition: opacity .2s, transform .2s;
}
.btn-register:hover { opacity: .9; transform: translateY(-1px); }

.pending-banner {
    background: rgba(251,191,36,.07);
    border: 1px solid rgba(251,191,36,.25);
    border-left: 4px solid #fbbf24;
    border-radius: 14px; padding: 20px 24px;
    margin-bottom: 28px;
}
.step-item { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 20px; }
.step-num {
    width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
    background: rgba(192,132,252,.15); border: 1px solid rgba(192,132,252,.3);
    display: flex; align-items: center; justify-content: center;
    color: #c084fc; font-weight: 700; font-size: .9rem;
}
</style>
@endpush

@section('content')
<div class="container py-4" style="max-width:900px;animation:fadeUp .5s ease both">

    {{-- ── Hero ── --}}
    <div class="art-hero mb-4">
        <div class="art-hero-content">
            <div class="art-hero-icon">🎤</div>
            <h1 class="fw-bold text-white mb-2" style="font-size:2rem">Trở thành Nghệ sĩ trên Blue Wave</h1>
            <p class="text-muted mb-0" style="font-size:1.05rem;max-width:520px;margin:auto">
                Chia sẻ âm nhạc của bạn, xây dựng cộng đồng fan và nhận tích xanh chính thức sau khi được xét duyệt.
            </p>
        </div>
    </div>

    {{-- ── Banner đơn đang chờ ── --}}
    @if(isset($pending) && $pending)
    <div class="pending-banner mb-4">
        <div class="d-flex align-items-start gap-3">
            <i class="fa-solid fa-clock-rotate-left mt-1" style="color:#fbbf24;font-size:1.2rem"></i>
            <div>
                <div class="fw-semibold text-white mb-1">Đơn đăng ký của bạn đang được xử lý</div>
                @if($pending->isPendingPayment())
                    <div class="small text-muted">Bạn đã bắt đầu đăng ký với tên nghệ danh <strong class="text-white">{{ $pending->artist_name }}</strong> nhưng chưa hoàn tất thanh toán.</div>
                @else
                    <div class="small text-muted">Đơn đăng ký với tên nghệ danh <strong class="text-white">{{ $pending->artist_name }}</strong> đang được đội ngũ chúng tôi xét duyệt. Vui lòng chờ email thông báo kết quả.</div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ── Banner thời gian chờ đăng ký lại (sau khi bị từ chối) ── --}}
    @if(isset($cooldownEnds) && $cooldownEnds)
    <div class="mb-4 p-3" style="background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.3);border-radius:12px">
        <div class="d-flex align-items-start gap-3">
            <i class="fa-solid fa-clock mt-1" style="color:#f87171;font-size:1.1rem;flex-shrink:0"></i>
            <div>
                <div class="fw-semibold mb-1" style="color:#fca5a5">Đơn đăng ký của bạn đã bị từ chối</div>
                <div class="small" style="color:#94a3b8">
                    Để đảm bảo chất lượng, bạn có thể nộp đơn đăng ký mới sau
                    <strong style="color:#f87171">{{ $cooldownEnds->format('H:i, d/m/Y') }}</strong>.
                    Vui lòng kiểm tra email để biết lý do từ chối và chuẩn bị kỹ trước khi đăng ký lại.
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Gói đăng ký ── --}}
    <h5 class="text-white fw-semibold mb-3">
        <i class="fa-solid fa-box me-2" style="color:#c084fc"></i>Chọn gói đăng ký Nghệ sĩ
    </h5>

    @if($packages->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="fa-solid fa-box-open fa-2x mb-3 opacity-25 d-block"></i>
        Hiện chưa có gói đăng ký nào. Vui lòng quay lại sau.
    </div>
    @else
    <div class="row g-3 mb-5">
        @foreach($packages as $pkg)
        <div class="col-12 col-md-6">
            <div class="pkg-card {{ $loop->first ? 'featured' : '' }}">
                @if($loop->first)
                <div class="mb-3">
                    <span class="badge rounded-pill px-3 py-1" style="background:rgba(192,132,252,.2);color:#c084fc;border:1px solid rgba(192,132,252,.35);font-size:.72rem">
                        ✦ PHỔ BIẾN NHẤT
                    </span>
                </div>
                @endif

                <div class="pkg-icon">🎵</div>

                <h4 class="text-white fw-bold mb-1 text-center">{{ $pkg->name }}</h4>
                @if($pkg->description)
                <p class="text-muted small text-center mb-3">{{ $pkg->description }}</p>
                @endif

                <div class="text-center mb-4">
                    <div class="pkg-price">{{ number_format($pkg->price) }}₫</div>
                    <div class="pkg-price-label">một lần duy nhất</div>
                </div>

                @if($pkg->features->isNotEmpty())
                <div class="mb-4">
                    @foreach($pkg->features as $feat)
                    <div class="feature-item">
                        <i class="fa-solid fa-circle-check feature-icon"></i>
                        <span class="feature-text">{{ $feat->feature }}</span>
                    </div>
                    @endforeach
                </div>
                @endif

                @if(isset($pending) && $pending && !$pending->isPendingPayment())
                <button class="btn btn-register text-white" disabled>
                    <i class="fa-solid fa-hourglass-half me-2"></i>Đơn đang chờ xét duyệt
                </button>
                @elseif(isset($cooldownEnds) && $cooldownEnds)
                <button class="btn btn-register text-white" disabled
                        title="Có thể đăng ký lại sau {{ $cooldownEnds->format('H:i, d/m/Y') }}">
                    <i class="fa-solid fa-clock me-2"></i>Chờ {{ $cooldownEnds->diffForHumans() }}
                </button>
                @else
                <a href="{{ route('artist-register.create', $pkg->id) }}" class="btn btn-register text-white">
                    <i class="fa-solid fa-arrow-right me-2"></i>Đăng ký gói này
                </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── Quy trình ── --}}
    <div class="p-4 rounded-4 mb-4" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07)">
        <h5 class="text-white fw-semibold mb-4">
            <i class="fa-solid fa-list-check me-2" style="color:#c084fc"></i>Quy trình đăng ký
        </h5>
        @foreach([
            ['icon'=>'fa-credit-card','color'=>'#c084fc','title'=>'Chọn gói & Thanh toán','desc'=>'Chọn gói phù hợp và thanh toán qua VNPAY. Email xác nhận thanh toán sẽ được gửi ngay sau khi hoàn tất.'],
            ['icon'=>'fa-clock','color'=>'#fbbf24','title'=>'Chờ xét duyệt','desc'=>'Đội ngũ Blue Wave Music sẽ xét duyệt đơn của bạn trong vòng 1–3 ngày làm việc.'],
            ['icon'=>'fa-circle-check','color'=>'#4ade80','title'=>'Nhận kết quả','desc'=>'Bạn sẽ nhận được email thông báo kết quả. Nếu được phê duyệt, tài khoản sẽ được nâng cấp lên Nghệ sĩ ngay lập tức.'],
            ['icon'=>'fa-badge-check','color'=>'#38bdf8','title'=>'Nhận tích xanh (tuỳ chọn)','desc'=>'Sau khi trở thành Nghệ sĩ, bạn có thể được admin cấp tích xanh chính thức để tăng độ tin cậy.'],
        ] as $i => $step)
        <div class="step-item">
            <div class="step-num">{{ $i + 1 }}</div>
            <div>
                <div class="fw-semibold mb-1" style="color:{{ $step['color'] }}">
                    <i class="fa-solid {{ $step['icon'] }} me-2"></i>{{ $step['title'] }}
                </div>
                <div class="small text-muted">{{ $step['desc'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Lịch sử giao dịch ── --}}
    @if(isset($registrationHistory) && $registrationHistory->isNotEmpty())
    <div class="mb-5">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="text-white fw-semibold mb-0">
                <i class="fa-solid fa-receipt me-2" style="color:#c084fc"></i>Lịch sử giao dịch đăng ký
            </h5>
        </div>

        <x-data-table
            :headers="[
                ['label' => '#',              'style' => 'width:46px', 'class' => 'ps-3'],
                ['label' => 'Nghệ danh'],
                ['label' => 'Gói'],
                ['label' => 'Số tiền',        'class' => 'text-end'],
                ['label' => 'Trạng thái',     'class' => 'text-center'],
                ['label' => 'Hoàn tiền',      'class' => 'text-center'],
                ['label' => 'Ngày thanh toán','class' => 'text-center'],
                ['label' => 'Chi tiết',       'class' => 'text-center', 'style' => 'width:90px'],
            ]"
            :isEmpty="false"
        >
            @foreach($registrationHistory as $i => $reg)
            @php
                $sColor = match($reg->status) {
                    'pending_review' => ['bg'=>'rgba(99,102,241,.12)', 'border'=>'rgba(99,102,241,.3)',  'text'=>'#a5b4fc'],
                    'approved'       => ['bg'=>'rgba(52,211,153,.12)', 'border'=>'rgba(52,211,153,.3)',  'text'=>'#6ee7b7'],
                    'rejected'       => ['bg'=>'rgba(248,113,113,.1)','border'=>'rgba(248,113,113,.25)','text'=>'#fca5a5'],
                    default          => ['bg'=>'rgba(100,116,139,.1)','border'=>'rgba(100,116,139,.25)','text'=>'#94a3b8'],
                };
            @endphp
            <tr class="border-secondary border-opacity-25">
                <td class="ps-3 text-muted small">{{ $i + 1 }}</td>
                <td>
                    <div class="fw-semibold" style="color:#e2e8f0;font-size:.87rem">{{ $reg->artist_name }}</div>
                    @if($reg->bio)
                    <div class="text-muted" style="font-size:.72rem;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $reg->bio }}</div>
                    @endif
                </td>
                <td>
                    <div style="color:#c084fc;font-size:.82rem;font-weight:600">{{ $reg->package->name ?? '—' }}</div>
                    @if($reg->package)
                    <div class="text-muted" style="font-size:.72rem">{{ $reg->package->duration_days }} ngày</div>
                    @endif
                </td>
                <td class="text-end">
                    <span class="fw-semibold" style="color:#fbbf24;font-size:.88rem">{{ number_format($reg->amount_paid) }} ₫</span>
                </td>
                <td class="text-center">
                    <span style="background:{{ $sColor['bg'] }};border:1px solid {{ $sColor['border'] }};color:{{ $sColor['text'] }};border-radius:50px;padding:3px 10px;font-size:.7rem;font-weight:600;white-space:nowrap">
                        {{ $reg->statusLabel() }}
                    </span>
                </td>
                <td class="text-center">
                    @if($reg->isRefunded())
                        @if($reg->isRefundCompleted())
                        <span style="background:rgba(52,211,153,.12);border:1px solid rgba(52,211,153,.3);color:#6ee7b7;border-radius:50px;padding:3px 10px;font-size:.7rem;font-weight:600;white-space:nowrap">
                            <i class="fa-solid fa-circle-check me-1" style="font-size:.6rem"></i>Đã hoàn {{ number_format($reg->refund_amount) }}₫
                        </span>
                        @else
                        <span style="background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.3);color:#fbbf24;border-radius:50px;padding:3px 10px;font-size:.7rem;font-weight:600;white-space:nowrap">
                            <i class="fa-solid fa-clock me-1" style="font-size:.6rem"></i>Chờ hoàn {{ number_format($reg->refund_amount) }}₫
                        </span>
                        @endif
                    @else
                        <span class="text-muted" style="font-size:.78rem">—</span>
                    @endif
                </td>
                <td class="text-center">
                    <div style="color:#94a3b8;font-size:.8rem">{{ $reg->paid_at ? $reg->paid_at->format('d/m/Y') : '—' }}</div>
                    @if($reg->paid_at)
                    <div class="text-muted" style="font-size:.7rem">{{ $reg->paid_at->format('H:i') }}</div>
                    @endif
                </td>
                <td class="text-center">
                    <button type="button"
                            class="btn btn-sm px-3"
                            style="background:rgba(192,132,252,.12);border:1px solid rgba(192,132,252,.25);color:#c084fc;border-radius:8px;font-size:.75rem"
                            onclick="showRegDetail({{ $reg->id }})">
                        <i class="fa-solid fa-eye me-1"></i>Xem
                    </button>
                </td>
            </tr>
            @endforeach
        </x-data-table>
    </div>
    @endif

</div>

{{-- ── Modal chi tiết giao dịch ── --}}
<div class="modal fade" id="regDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px">
        <div class="modal-content" style="background:#0f172a;border:1px solid rgba(255,255,255,.1);border-radius:20px">
            <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,.07);padding:20px 24px">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,rgba(168,85,247,.2),rgba(192,132,252,.12));border:1px solid rgba(192,132,252,.3);display:flex;align-items:center;justify-content:center">
                        <i class="fa-solid fa-receipt" style="color:#c084fc;font-size:.95rem"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-white" style="font-size:1rem">Chi tiết giao dịch</div>
                        <div class="text-muted" style="font-size:.75rem">Đăng ký gói Nghệ sĩ</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:24px" id="regDetailBody">
                <div class="text-center py-4">
                    <i class="fa-solid fa-spinner fa-spin" style="color:#c084fc;font-size:1.2rem"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Data for modal --}}
@if(isset($registrationHistory) && $registrationHistory->isNotEmpty())
<script>
const regData = {
    @foreach($registrationHistory as $reg)
    {{ $reg->id }}: {
        artist_name:     @json($reg->artist_name),
        bio:             @json($reg->bio ?? ''),
        package_name:    @json($reg->package->name ?? '—'),
        package_days:    {{ $reg->package->duration_days ?? 0 }},
        amount_paid:     {{ $reg->amount_paid }},
        transaction_code:@json($reg->transaction_code ?? '—'),
        status:          @json($reg->status),
        status_label:    @json($reg->statusLabel()),
        paid_at:         @json($reg->paid_at ? $reg->paid_at->format('d/m/Y H:i') : null),
        reviewed_at:     @json($reg->reviewed_at ? $reg->reviewed_at->format('d/m/Y H:i') : null),
        expires_at:      @json($reg->expires_at ? $reg->expires_at->format('d/m/Y') : null),
        admin_note:      @json($reg->admin_note ?? ''),
        refund_amount:   {{ $reg->refund_amount ?? 0 }},
        refund_status:   @json($reg->refund_status ?? ''),
        refund_confirmed_at: @json($reg->refund_confirmed_at ? $reg->refund_confirmed_at->format('d/m/Y H:i') : null),
    },
    @endforeach
};

function showRegDetail(id) {
    const r = regData[id];
    if (!r) return;

    const statusColors = {
        pending_review: { bg:'rgba(99,102,241,.12)', border:'rgba(99,102,241,.3)', text:'#a5b4fc' },
        approved:       { bg:'rgba(52,211,153,.12)', border:'rgba(52,211,153,.3)', text:'#6ee7b7' },
        rejected:       { bg:'rgba(248,113,113,.1)', border:'rgba(248,113,113,.25)', text:'#fca5a5' },
    };
    const sc = statusColors[r.status] || { bg:'rgba(100,116,139,.1)', border:'rgba(100,116,139,.25)', text:'#94a3b8' };

    const row = (label, value) => `
        <div class="d-flex justify-content-between align-items-start py-2" style="border-bottom:1px solid rgba(255,255,255,.05)">
            <span style="color:#64748b;font-size:.82rem;min-width:140px">${label}</span>
            <span style="color:#e2e8f0;font-size:.82rem;text-align:right;word-break:break-all">${value}</span>
        </div>`;

    let refundHtml = '';
    if (r.refund_amount > 0) {
        const refLabel = r.refund_status === 'completed'
            ? `<span style="color:#6ee7b7;font-size:.78rem"><i class="fa-solid fa-circle-check me-1"></i>Đã hoàn tiền${r.refund_confirmed_at ? ' lúc ' + r.refund_confirmed_at : ''}</span>`
            : `<span style="color:#fbbf24;font-size:.78rem"><i class="fa-solid fa-clock me-1"></i>Chờ hoàn tiền</span>`;
        refundHtml = row('Hoàn tiền', `<div>${r.refund_amount.toLocaleString('vi-VN')} ₫</div><div class="mt-1">${refLabel}</div>`);
    }

    let noteHtml = '';
    if (r.admin_note) {
        noteHtml = `<div class="mt-3 p-3 rounded-3" style="background:rgba(248,113,113,.07);border:1px solid rgba(248,113,113,.2)">
            <div class="small fw-semibold mb-1" style="color:#fca5a5"><i class="fa-solid fa-comment-dots me-1"></i>Lý do từ Admin</div>
            <div class="small" style="color:#cbd5e1">${r.admin_note}</div>
        </div>`;
    }

    const html = `
        <div style="background:linear-gradient(135deg,rgba(168,85,247,.08),rgba(192,132,252,.05));border:1px solid rgba(192,132,252,.2);border-radius:14px;padding:16px 20px;margin-bottom:20px">
            <div class="fw-bold text-white mb-1" style="font-size:1.05rem">🎤 ${r.artist_name}</div>
            ${r.bio ? `<div class="small text-muted">${r.bio}</div>` : ''}
        </div>

        <div>
            ${row('Gói đăng ký', `<span style="color:#c084fc;font-weight:600">${r.package_name}</span> <span class="text-muted">(${r.package_days} ngày)</span>`)}
            ${row('Số tiền thanh toán', `<span style="color:#fbbf24;font-weight:700">${r.amount_paid.toLocaleString('vi-VN')} ₫</span>`)}
            ${row('Mã giao dịch', `<code style="color:#94a3b8;font-size:.78rem;word-break:break-all">${r.transaction_code}</code>`)}
            ${row('Ngày thanh toán', r.paid_at || '—')}
            ${row('Trạng thái đơn', `<span style="background:${sc.bg};border:1px solid ${sc.border};color:${sc.text};border-radius:50px;padding:2px 10px;font-size:.72rem;font-weight:600">${r.status_label}</span>`)}
            ${r.reviewed_at ? row('Ngày xét duyệt', r.reviewed_at) : ''}
            ${r.expires_at ? row('Hiệu lực đến', r.expires_at) : ''}
            ${refundHtml}
        </div>
        ${noteHtml}
    `;

    document.getElementById('regDetailBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('regDetailModal')).show();
}
</script>
@endif

@endsection
