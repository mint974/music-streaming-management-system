@extends('layouts.main')
@section('title', 'Nâng cấp Premium · Blue Wave')

@section('content')
    <style>
        /* CSS Gemini Advanced / Pro Style */
        :root {
            --bg-dark: #0f1015;
            --card-bg: #16181f;
            --border-dim: rgba(255, 255, 255, 0.08);
            --gemini-gradient: linear-gradient(110deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%);
            --gemini-gradient-reverse: linear-gradient(110deg, #ec4899 0%, #8b5cf6 50%, #3b82f6 100%);
            --gemini-text: linear-gradient(to right, #93c5fd, #c4b5fd, #f9a8d4);
        }

        .sub-page {
            position: relative;
            background-color: transparent;
            overflow: hidden;
        }

        /* Background effects */
        .sub-page::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -10%;
            right: -10%;
            bottom: 30%;
            background: radial-gradient(circle at 50% 0%, rgba(139, 92, 246, 0.15) 0%, rgba(15, 16, 21, 0) 60%);
            filter: blur(80px);
            z-index: -2;
            pointer-events: none;
        }

        .hero-title {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.02em;
            margin-bottom: 24px;
        }

        .hero-title .glow-text {
            background: var(--gemini-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradientShift 6s ease-in-out infinite alternate;
            background-size: 200% 200%;
        }

        .hero-subtitle {
            color: #a1a1aa;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Gemini Pricing Cards */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .gemini-card {
            position: relative;
            background: var(--card-bg);
            border-radius: 1.5rem;
            padding: 2.5rem 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
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
            background: linear-gradient(90deg, #3b82f6, #ec4899, #8b5cf6, #3b82f6);
            background-size: 300% 300%;
            animation: gradientSpin 5s linear infinite;
            z-index: -2;
        }

        .gemini-card.glow-card::after {
            content: '';
            position: absolute;
            inset: 1px;
            border-radius: 1.5rem;
            background: var(--card-bg);
            z-index: -1;
        }

        .gemini-card.standard-card {
            border: 1px solid var(--border-dim);
            transition: transform 0.3s;
        }

        .gemini-card.standard-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .p-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .p-price-wrap {
            margin: 1.5rem 0 2rem;
        }

        .p-price {
            font-size: 3rem;
            font-weight: 800;
            color: #fff;
            line-height: 1;
        }

        .p-curr {
            font-size: 1rem;
            color: #a1a1aa;
            font-weight: 500;
        }

        .p-desc {
            color: #a1a1aa;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .p-features {
            list-style: none;
            padding: 0;
            margin: 2rem 0;
            flex-grow: 1;
        }

        .p-features li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 1rem;
            color: #d4d4d8;
            font-size: 0.95rem;
        }

        .p-features li i {
            color: #c084fc;
            font-size: 0.9rem;
            margin-top: 3px;
        }

        .btn-g {
            display: block;
            width: 100%;
            padding: 14px;
            border-radius: 50px;
            text-align: center;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }

        .btn-g.solid {
            background: #fff;
            color: #000;
        }

        .btn-g.solid:hover {
            background: #e4e4e7;
        }

        .btn-g.gradient {
            background: var(--gemini-gradient);
            color: #fff;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        }

        .btn-g.gradient:hover {
            box-shadow: 0 6px 25px rgba(139, 92, 246, 0.5);
            transform: translateY(-2px);
            opacity: 0.95;
        }

        .btn-g.outline {
            background: transparent;
            color: #fff;
            border: 1px solid var(--border-dim);
        }

        .btn-g.active-st {
            background: rgba(255, 255, 255, 0.05);
            color: #8b5cf6;
            border: 1px solid rgba(139, 92, 246, 0.4);
            pointer-events: none;
        }

        /* Animations */
        @keyframes gradientSpin {
            0% {
                background-position: 0% 50%;
            }

            100% {
                background-position: 100% 50%;
            }
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            100% {
                background-position: 100% 50%;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }

        .delay-1 {
            animation-delay: 0.1s;
        }

        .delay-2 {
            animation-delay: 0.2s;
        }

        /* Status */
        .status-banner {
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-top: 2rem;
        }
    </style>
    <div class="sub-page py-5">
        <div class="container" style="max-width: 1100px;">

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="alert text-white mb-4"
                    style="background: rgba(52,211,153,0.15); border: 1px solid rgba(52,211,153,0.3); border-radius: 12px;">
                    <i class="fa-solid fa-circle-check me-2 text-success"></i>{{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert text-white mb-4"
                    style="background: rgba(248,113,113,0.15); border: 1px solid rgba(248,113,113,0.3); border-radius: 12px;">
                    <i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i>{{ session('error') }}
                </div>
            @endif

            {{-- Hero Section --}}
            <div class="text-center fade-in-up">
                <h1 class="hero-title">Trải nghiệm <span class="glow-text">Blue Wave Premium</span></h1>
                <p class="hero-subtitle">Nghe nhạc không quảng cáo, tải xuống offline và chất lượng âm thanh tốt nhất. Khai
                    phá sức mạnh giải trí vô hạn.</p>
            </div>

            {{-- Current Plan Status --}}
            @if ($activeSub)
                <div class="status-banner fade-in-up delay-1 mx-auto max-w-3xl mt-5">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                        <div class="d-flex align-items-center gap-3">
                            <div
                                style="width: 50px; height: 50px; background: var(--gemini-gradient); border-radius: 12px; display:flex; align-items:center; justify-content:center;">
                                <i class="fa-solid fa-gem text-white fs-4"></i>
                            </div>
                            <div>
                                <h5 class="text-white fw-bold mb-1">Gói hiện tại: {{ $activeSub->vip->title }}</h5>
                                <div class="text-muted small">Thời hạn từ {{ $activeSub->start_date->format('d/m/Y') }} đến
                                    {{ $activeSub->end_date->format('d/m/Y') }}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="text-white fs-4 fw-bold">{{ $activeSub->daysRemaining() }} ngày</div>
                            <div class="text-muted small mb-2">thời gian còn lại</div>
                                <form method="POST" action="{{ route('subscription.cancel', $activeSub->id) }}" class="needs-confirmation" data-confirm-message="Gói sẽ huỷ ngay lập tức và không được hoàn tiền. Bạn chắc chắn?">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3">Hủy gói ngay</button>
                                </form>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Pricing Options --}}
            <div class="pricing-grid fade-in-up delay-2">
                @forelse($vips as $vip)
                    @php
                        $isCurrentPlan = $activeSub && $activeSub->vip_id === $vip->id;
                        $isRecommended = str_contains(strtolower($vip->title), 'năm') || $vip->duration_days >= 300;
                    @endphp
                    <div class="gemini-card {{ $isRecommended ? 'glow-card' : 'standard-card' }}">
                        @if ($isRecommended)
                            <div class="position-absolute top-0 end-0 mt-3 me-3 text-white px-3 py-1 fw-bold rounded-pill"
                                style="font-size: 0.75rem; background: var(--gemini-gradient);">ĐỀ XUẤT</div>
                        @endif

                        <h3 class="p-name">
                            @if ($isRecommended)
                                <i class="fa-solid fa-bolt" style="color: #f472b6;"></i>
                            @else
                                <i class="fa-regular fa-circle-play" style="color: #8b5cf6;"></i>
                            @endif
                            {{ $vip->title }}
                        </h3>
                        <p class="p-desc">
                            {{ $vip->description ?: 'Trải nghiệm toàn bộ ưu đãi của nền tảng với mức giá tiết kiệm.' }}</p>

                        <div class="p-price-wrap">
                            <span class="p-price">{{ number_format($vip->price) }}</span><span class="p-curr">vnđ</span>
                            <div class="text-muted small mt-1">Sử dụng trong {{ $vip->duration_days }} ngày</div>
                        </div>

                        <ul class="p-features">
                            <li><i class="fa-solid fa-check"></i> Trải nghiệm âm nhạc không quảng cáo</li>
                            <li><i class="fa-solid fa-check"></i> Tải nhạc xuống nghe ngoại tuyến (Offline)</li>
                            <li><i class="fa-solid fa-check"></i> Chất lượng âm thanh chuẩn Lossless</li>
                            <li><i class="fa-solid fa-check"></i> Tạo Playlists lưu trữ cá nhân</li>
                        </ul>

                        @if ($isCurrentPlan)
                            <button class="btn-g active-st"><i class="fa-solid fa-check me-2"></i>Gói đang phân bổ</button>
                        @else
                            <form method="POST" action="{{ route('subscription.checkout', $vip->id) }}" hx-boost="false">
                                @csrf
                                @if ($activeSub)
                                    <button type="submit"
                                        class="btn-g {{ $isRecommended ? 'gradient' : 'solid' }} needs-confirmation"
                                        data-confirm-message="Chuyển gói sẽ xoá gói cũ và không hoàn tiền. Đồng ý?">Chuyển
                                        sang gói này</button>
                                @else
                                    <button type="submit" class="btn-g {{ $isRecommended ? 'gradient' : 'solid' }}">Đăng
                                        ký ngay</button>
                                @endif
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="text-center text-muted mx-auto py-5 w-100">
                        <i class="fa-solid fa-crown fa-3x opacity-50 mb-3"></i>
                        <p>Hệ thống chưa thiết lập các gói Premium. Vui lòng trở lại sau.</p>
                    </div>
                @endforelse
            </div>

            <div class="text-center mt-5 pt-4 text-muted small border-top"
                style="border-color: rgba(255,255,255,0.05) !important;">
                Thanh toán an toàn qua cổng VNPAY. Mọi giao dịch minh bạch, bảo mật SSL. Có thể huỷ tự động bất cứ lúc nào.
            </div>

            {{-- Lịch sử --}}
            @if (isset($history) && ($history->isNotEmpty() || request('status') || request('amount') || request('start_date') || request('end_date')))
                <div class="mt-5" id="history-section">
                    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                        <h5 class="text-white mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-muted"></i>Lịch sử thanh toán</h5>
                        <div class="text-end" style="background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 12px; padding: 10px 20px;">
                            <span class="text-white me-2">Tổng chi:</span>
                            <span class="fw-bold fs-5" style="color: #f472b6;">{{ number_format($totalSpent ?? 0) }} ₫</span>
                        </div>
                    </div>

                    {{-- Bộ lọc --}}
                    <div class="filter-bar mb-4">
                        <form method="GET" action="{{ route('subscription.index') }}#history-section" class="filter-bar-inner">
                            <div class="filter-field flex-grow-1" style="min-width: 160px;">
                                <label class="filter-label">Trạng thái thanh toán</label>
                                <select name="status" class="filter-select">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ thanh toán</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang hiệu lực</option>
                                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Đã hết hạn</option>
                                </select>
                            </div>
                            
                            <div class="filter-field" style="min-width: 160px;">
                                <label class="filter-label">Số tiền (VNĐ)</label>
                                <input type="number" name="amount" class="filter-input" placeholder="Tất cả mệnh giá" value="{{ request('amount') }}" min="0">
                            </div>
                            
                            <div class="filter-field" style="min-width: 140px;">
                                <label class="filter-label">Từ ngày</label>
                                <input type="date" name="start_date" id="start_date" class="filter-input" value="{{ request('start_date') }}" max="{{ date('Y-m-d') }}">
                            </div>
                            
                            <div class="filter-field" style="min-width: 140px;">
                                <label class="filter-label">Đến ngày</label>
                                <input type="date" name="end_date" id="end_date" class="filter-input" value="{{ request('end_date') }}" max="{{ date('Y-m-d') }}">
                            </div>
                            
                            <div class="filter-actions">
                                <button type="submit" class="btn mm-btn mm-btn-primary">
                                    <i class="fa-solid fa-filter"></i> Lọc
                                </button>
                                <a href="{{ route('subscription.index') }}#history-section" class="btn mm-btn mm-btn-ghost">
                                    <i class="fa-solid fa-rotate-left"></i> Đặt lại
                                </a>
                            </div>
                        </form>
                    </div>

                    @if ($history->isEmpty())
                        <div class="text-center text-muted border border-secondary py-5 rounded-3" style="border-color: rgba(255,255,255,0.05) !important;">
                            <i class="fa-solid fa-file-invoice fa-3x opacity-50 mb-3"></i>
                            <p>Không tìm thấy lịch sử thanh toán nào phù hợp với bộ lọc.</p>
                        </div>
                    @else
                    <div class="table-responsive rounded-3 border" style="border-color: rgba(255,255,255,0.1) !important;">
                        <table class="table table-dark table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Gói</th>
                                    <th>Hiệu lực</th>
                                    <th class="text-end">Số tiền</th>
                                    <th class="text-center">Trạng thái Gói</th>
                                    <th class="text-center">Tình trạng TT</th>
                                    <th class="text-center">Mã GD</th>
                                    <th class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($history as $sub)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $sub->vip->title ?? '—' }}</div>
                                            <div class="small text-muted">{{ $sub->vip?->duration_days }} ngày</div>
                                        </td>
                                        <td>
                                            <div class="small">{{ $sub->start_date->format('d/m/Y') }} →
                                                {{ $sub->end_date->format('d/m/Y') }}</div>
                                        </td>
                                        <td class="text-end fw-semibold text-warning">
                                            {{ number_format($sub->amount_paid) }} ₫
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge {{ $sub->status === 'active' ? 'bg-success' : ($sub->status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                                {{ $sub->statusLabel() }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if ($sub->payment)
                                                <span
                                                    class="badge border {{ $sub->payment->status === 'paid' ? 'border-success text-success' : 'border-secondary text-muted' }} bg-transparent">
                                                    {{ $sub->payment->statusLabel() }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <code class="text-muted"
                                                style="font-size: 0.75rem;">{{ $sub->payment?->transaction_code ?: '—' }}</code>
                                        </td>
                                        <td class="text-center">
                                            @if ($sub->status === 'pending')
                                                <div class="d-flex justify-content-center gap-2">
                                                    <form method="POST"
                                                        action="{{ route('subscription.payPending', $sub->id) }}"
                                                        hx-boost="false">
                                                        @csrf
                                                        <button type="submit"
                                                            class="btn btn-sm btn-primary rounded-pill px-3"
                                                            style="font-size: 0.75rem;"><i
                                                                class="fa-solid fa-credit-card me-1"></i>Thanh toán</button>
                                                    </form>
                                                    <form method="POST"
                                                        action="{{ route('subscription.cancelPending', $sub->id) }}">
                                                        @csrf
                                                        <button type="submit"
                                                            class="btn btn-sm btn-outline-danger rounded-pill px-3 needs-confirmation"
                                                            data-confirm-message="Bạn chắc chắn muốn hủy gói chờ thanh toán này không?"
                                                            style="font-size: 0.75rem;"><i
                                                                class="fa-solid fa-xmark me-1"></i>Hủy bỏ</button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                    @if ($history->hasPages())
                        <div class="mt-3">{{ $history->links() }}</div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Bootstrap 5 Confirm Modal cho Hủy gói / Chuyển gói -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-secondary bg-opacity-75"
                style="backdrop-filter: blur(10px);">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="confirmModalLabel"><i
                            class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Xác nhận thao tác</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="confirmModalBody">
                    Bạn có chắc chắn muốn thực hiện hành động này?
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy
                        bỏ</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="confirmModalBtn">Đồng ý</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            if (window._confirmModalDelegationBound) return;
            window._confirmModalDelegationBound = true;

            let currentForm = null;

            document.addEventListener('click', async function(e) {
                // Lắng nghe click mở Modal
                const triggerBtn = e.target.closest('.needs-confirmation');
                if (triggerBtn) {
                    e.preventDefault();
                    e.stopPropagation();

                    currentForm = triggerBtn.closest('form');
                    const modalEl = document.getElementById('confirmModal');

                    // Fallback nếu modal chưa sẵn sàng: dùng helper chung hoặc hủy an toàn.
                    if (!modalEl || typeof bootstrap === 'undefined') {
                        if (typeof window.showConfirmModal === 'function') {
                            const accepted = await window.showConfirmModal(
                                triggerBtn.getAttribute('data-confirm-message') || 'Xác nhận thực hiện thao tác?',
                                {
                                    title: triggerBtn.getAttribute('data-confirm-title') || 'Xác nhận',
                                }
                            );
                            if (accepted) {
                                currentForm.submit();
                            }
                        }
                        return;
                    }

                    const mBody = document.getElementById('confirmModalBody');
                    const mBtnConfirm = document.getElementById('confirmModalBtn');

                    if (mBody) {
                        mBody.textContent = triggerBtn.getAttribute('data-confirm-message') ||
                            'Bạn chắc chắn muốn thực hiện hành động này?';
                    }

                    if (mBtnConfirm) {
                        if (triggerBtn.classList.contains('btn-outline-danger')) {
                            mBtnConfirm.className = 'btn btn-danger rounded-pill px-4';
                        } else {
                            mBtnConfirm.className = 'btn btn-primary rounded-pill px-4';
                        }
                    }

                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }

                // Lắng nghe click nút Đồng ý trong Modal
                const confirmActionBtn = e.target.closest('#confirmModalBtn');
                if (confirmActionBtn) {
                    if (currentForm) {
                        const modalEl = document.getElementById('confirmModal');
                        if (modalEl && typeof bootstrap !== 'undefined') {
                            const mInst = bootstrap.Modal.getInstance(modalEl);
                            if (mInst) mInst.hide();
                        }
                        currentForm.submit();
                        currentForm = null;
                    }
                }
            });

            // Ràng buộc Ngày bắt đầu / Ngày kết thúc
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            
            if (startDateInput && endDateInput) {
                // Khởi tạo giới hạn ban đầu nếu đã có dữ liệu
                if (startDateInput.value) {
                    endDateInput.min = startDateInput.value;
                }
                if (endDateInput.value) {
                    startDateInput.max = endDateInput.value;
                }

                startDateInput.addEventListener('change', function() {
                    endDateInput.min = this.value;
                });
                
                endDateInput.addEventListener('change', function() {
                    startDateInput.max = this.value || '{{ date("Y-m-d") }}';
                });
            }
        })();
    </script>
@endsection

