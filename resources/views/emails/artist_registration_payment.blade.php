<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận thanh toán đăng ký Nghệ sĩ</title>
    <style>
        body { margin: 0; padding: 0; background: #0f0f13; font-family: 'Segoe UI', Arial, sans-serif; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 32px 16px; }
        .card { background: #1a1a24; border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,.08); }
        .header { background: linear-gradient(135deg, #7c3aed 0%, #c084fc 100%); padding: 32px 32px 28px; text-align: center; }
        .header-icon { font-size: 36px; margin-bottom: 12px; }
        .header h1 { margin: 0; color: #fff; font-size: 22px; font-weight: 700; }
        .header p { margin: 6px 0 0; color: rgba(255,255,255,.8); font-size: 14px; }
        .body { padding: 28px 32px; }
        .greeting { color: #e2e8f0; font-size: 15px; margin-bottom: 20px; }
        .package-box { background: rgba(168,85,247,.1); border: 1px solid rgba(168,85,247,.3); border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .package-name { color: #c084fc; font-size: 18px; font-weight: 700; margin: 0 0 12px; }
        .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,.06); }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #94a3b8; font-size: 13px; }
        .detail-value { color: #e2e8f0; font-size: 13px; font-weight: 600; }
        .detail-value.purple { color: #c084fc; }
        .detail-value.green  { color: #34d399; }
        .detail-value.gold   { color: #fbbf24; }
        .tx-box { background: rgba(255,255,255,.03); border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; }
        .tx-label { color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
        .tx-code { color: #94a3b8; font-size: 13px; font-family: monospace; word-break: break-all; }
        .notice-box { background: rgba(251,191,36,.07); border: 1px solid rgba(251,191,36,.2); border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; }
        .notice-box p { color: #fde68a; font-size: 13px; margin: 0; line-height: 1.6; }
        .message { color: #94a3b8; font-size: 13px; line-height: 1.6; margin-bottom: 24px; }
        .btn { display: inline-block; background: linear-gradient(135deg, #7c3aed, #c084fc); color: #fff !important; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .footer { text-align: center; padding: 20px 32px; border-top: 1px solid rgba(255,255,255,.06); }
        .footer p { color: #475569; font-size: 12px; margin: 0; line-height: 1.6; }
        .badge { display: inline-block; background: rgba(192,132,252,.15); color: #c084fc; border: 1px solid rgba(192,132,252,.3); border-radius: 20px; padding: 3px 10px; font-size: 11px; font-weight: 600; margin-left: 8px; vertical-align: middle; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <div class="header-icon">🎤</div>
            <h1>Thanh toán thành công <span class="badge">Nghệ sĩ</span></h1>
            <p>Đơn đăng ký của bạn đang được xét duyệt</p>
        </div>

        <div class="body">
            <p class="greeting">
                Xin chào <strong style="color:#c084fc">{{ $user->name }}</strong>,<br>
                Cảm ơn bạn đã đăng ký gói Nghệ sĩ tại Blue Wave Music. Dưới đây là thông tin chi tiết đơn đăng ký của bạn.
            </p>

            <div class="package-box">
                <div class="package-name">
                    🎵 {{ $package->name }}
                </div>

                <div class="detail-row">
                    <span class="detail-label">Tên nghệ danh đăng ký</span>
                    <span class="detail-value purple">{{ $registration->artist_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Gói đăng ký</span>
                    <span class="detail-value">{{ $package->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Số tiền đã thanh toán</span>
                    <span class="detail-value gold">{{ number_format($registration->amount_paid) }} ₫</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Thời gian thanh toán</span>
                    <span class="detail-value green">{{ $registration->paid_at?->format('d/m/Y H:i') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phương thức</span>
                    <span class="detail-value">VNPAY</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Trạng thái</span>
                    <span class="detail-value green">Chờ xét duyệt</span>
                </div>
            </div>

            @if($registration->transaction_code)
            <div class="tx-box">
                <div class="tx-label">Mã giao dịch</div>
                <div class="tx-code">{{ $registration->transaction_code }}</div>
            </div>
            @endif

            <div class="notice-box">
                <p>
                    ⏳ <strong>Lưu ý:</strong> Đơn đăng ký của bạn đang chờ đội ngũ Blue Wave Music xét duyệt. Thông thường quá trình này mất 1–3 ngày làm việc. Bạn sẽ nhận được email thông báo kết quả.
                </p>
            </div>

            <p class="message">
                Sau khi được phê duyệt, tài khoản của bạn sẽ được nâng cấp lên <strong style="color:#c084fc">Nghệ sĩ</strong> và bạn có thể bắt đầu tải lên, quản lý âm nhạc và nhận tích xanh chính thức từ admin.
            </p>

            <div style="text-align:center;">
                <a href="{{ url('/dashboard') }}" class="btn">Về trang chủ</a>
            </div>
        </div>

        <div class="footer">
            <p>
                Email này được gửi tự động từ hệ thống <strong>Blue Wave Music</strong>.<br>
                Vui lòng không trả lời email này. Nếu cần hỗ trợ, liên hệ chúng tôi qua trang web.
            </p>
        </div>
    </div>
</div>
</body>
</html>
