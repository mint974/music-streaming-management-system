<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận nâng cấp Premium</title>
    <style>
        body { margin: 0; padding: 0; background: #0f0f13; font-family: 'Segoe UI', Arial, sans-serif; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 32px 16px; }
        .card { background: #1a1a24; border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,.08); }
        .header { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 32px 32px 28px; text-align: center; }
        .header-icon { font-size: 36px; margin-bottom: 12px; }
        .header h1 { margin: 0; color: #fff; font-size: 22px; font-weight: 700; }
        .header p { margin: 6px 0 0; color: rgba(255,255,255,.8); font-size: 14px; }
        .body { padding: 28px 32px; }
        .greeting { color: #e2e8f0; font-size: 15px; margin-bottom: 20px; }
        .package-box { background: rgba(99,102,241,.1); border: 1px solid rgba(99,102,241,.3); border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .package-name { color: #a5b4fc; font-size: 18px; font-weight: 700; margin: 0 0 12px; }
        .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,.06); }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #94a3b8; font-size: 13px; }
        .detail-value { color: #e2e8f0; font-size: 13px; font-weight: 600; }
        .detail-value.gold { color: #fbbf24; }
        .detail-value.green { color: #34d399; }
        .tx-box { background: rgba(255,255,255,.03); border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; }
        .tx-label { color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
        .tx-code { color: #94a3b8; font-size: 13px; font-family: monospace; word-break: break-all; }
        .message { color: #94a3b8; font-size: 13px; line-height: 1.6; margin-bottom: 24px; }
        .btn { display: inline-block; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff !important; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .footer { text-align: center; padding: 20px 32px; border-top: 1px solid rgba(255,255,255,.06); }
        .footer p { color: #475569; font-size: 12px; margin: 0; line-height: 1.6; }
        .badge { display: inline-block; background: rgba(251,191,36,.15); color: #fbbf24; border: 1px solid rgba(251,191,36,.3); border-radius: 20px; padding: 3px 10px; font-size: 11px; font-weight: 600; margin-left: 8px; vertical-align: middle; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <div class="header-icon">🎵</div>
            <h1>Chúc mừng! Bạn đã là Premium <span class="badge">VIP</span></h1>
            <p>Tài khoản của bạn đã được nâng cấp thành công</p>
        </div>

        <div class="body">
            <p class="greeting">
                Xin chào <strong style="color:#a5b4fc">{{ $user->name }}</strong>,<br>
                Cảm ơn bạn đã tin tưởng và nâng cấp tài khoản. Dưới đây là thông tin chi tiết gói đăng ký của bạn.
            </p>

            <div class="package-box">
                <div class="package-name">
                    <i style="color:#fbbf24">★</i> {{ $vip->title }}
                </div>

                <div class="detail-row">
                    <span class="detail-label">Ngày bắt đầu</span>
                    <span class="detail-value">{{ $subscription->start_date->format('d/m/Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Ngày hết hạn</span>
                    <span class="detail-value green">{{ $subscription->end_date->format('d/m/Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Thời hạn</span>
                    <span class="detail-value">{{ $vip->duration_days }} ngày</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Số tiền đã thanh toán</span>
                    <span class="detail-value gold">{{ number_format($subscription->amount_paid) }} ₫</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phương thức</span>
                    <span class="detail-value">{{ $payment?->method ?? 'VNPAY' }}</span>
                </div>
            </div>

            @if($payment?->transaction_code)
            <div class="tx-box">
                <div class="tx-label">Mã giao dịch</div>
                <div class="tx-code">{{ $payment->transaction_code }}</div>
            </div>
            @endif

            <p class="message">
                Gói Premium giúp bạn nghe nhạc không giới hạn, tải nhạc offline và tận hưởng trải nghiệm không quảng cáo.
                Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi.
            </p>

            <div style="text-align:center;">
                <a href="{{ url('/subscription') }}" class="btn">Xem chi tiết đăng ký</a>
            </div>
        </div>

        <div class="footer">
            <p>
                Email này được gửi tự động từ hệ thống <strong>Blue Wave Music</strong>.<br>
                Vui lòng không trả lời email này.
            </p>
        </div>
    </div>
</div>
</body>
</html>
