<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gói Nghệ sĩ đã hết hạn</title>
    <style>
        body { margin: 0; padding: 0; background: #0f0f13; font-family: 'Segoe UI', Arial, sans-serif; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 32px 16px; }
        .card { background: #1a1a24; border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,.08); }
        .header { background: linear-gradient(135deg, #64748b 0%, #475569 100%); padding: 32px 32px 28px; text-align: center; }
        .header-icon { font-size: 36px; margin-bottom: 12px; }
        .header h1 { margin: 0; color: #fff; font-size: 22px; font-weight: 700; }
        .header p { margin: 6px 0 0; color: rgba(255,255,255,.75); font-size: 14px; }
        .body { padding: 28px 32px; }
        .greeting { color: #e2e8f0; font-size: 15px; margin-bottom: 20px; }
        .info-box { background: rgba(100,116,139,.12); border: 1px solid rgba(100,116,139,.3); border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .info-title { color: #94a3b8; font-size: 15px; font-weight: 700; margin: 0 0 12px; }
        .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,.06); }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #94a3b8; font-size: 13px; }
        .detail-value { color: #e2e8f0; font-size: 13px; font-weight: 600; }
        .notice-box { background: rgba(52,211,153,.07); border: 1px solid rgba(52,211,153,.2); border-radius: 10px; padding: 14px 16px; margin-bottom: 20px; }
        .notice-text { color: #94a3b8; font-size: 13px; line-height: 1.6; margin: 0; }
        .renew-box { background: rgba(236,72,153,.08); border: 1px solid rgba(236,72,153,.25); border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; }
        .renew-title { color: #f472b6; font-size: 14px; font-weight: 600; margin: 0 0 6px; }
        .renew-desc { color: #64748b; font-size: 13px; line-height: 1.5; margin: 0; }
        .btn { display: inline-block; background: linear-gradient(135deg, #ec4899, #be185d); color: #fff !important; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .footer { text-align: center; padding: 20px 32px; border-top: 1px solid rgba(255,255,255,.06); }
        .footer p { color: #475569; font-size: 12px; margin: 0; line-height: 1.6; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <div class="header-icon">🎤</div>
            <h1>Gói Nghệ sĩ đã hết hạn</h1>
            <p>Tài khoản của bạn đã chuyển về gói thường</p>
        </div>

        <div class="body">
            <p class="greeting">
                Xin chào <strong style="color:#a5b4fc">{{ $user->artist_name ?: $user->name }}</strong>,<br>
                Gói Nghệ sĩ <strong>{{ $package->name }}</strong> của bạn đã hết hạn vào ngày
                <strong>{{ $registration->expires_at->format('d/m/Y') }}</strong>.
                Tài khoản đã được chuyển về <strong style="color:#94a3b8">tài khoản thường</strong>.
            </p>

            <div class="info-box">
                <div class="info-title">Thông tin gói đã hết hạn</div>
                <div class="detail-row">
                    <span class="detail-label">Gói đăng ký</span>
                    <span class="detail-value">{{ $package->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Nghệ danh</span>
                    <span class="detail-value">{{ $user->artist_name ?: $user->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Thời hạn gói</span>
                    <span class="detail-value">{{ $package->duration_days }} ngày</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Ngày hết hạn</span>
                    <span class="detail-value">{{ $registration->expires_at->format('d/m/Y') }}</span>
                </div>
            </div>

            <div class="notice-box">
                <p class="notice-text">
                    ✓ <strong style="color:#34d399">Dữ liệu được bảo toàn:</strong>
                    Tất cả bài hát và album bạn đã đăng tải <strong style="color:#e2e8f0">vẫn còn nguyên vẹn</strong>
                    trên hệ thống. Chúng sẽ tiếp tục hiển thị với thính giả.
                    Khi gia hạn lại, bạn sẽ khôi phục đầy đủ quyền quản lý.
                </p>
            </div>

            <div class="renew-box">
                <div class="renew-title">🎤 Đăng ký lại để tiếp tục sáng tác</div>
                <p class="renew-desc">
                    Gia hạn gói Nghệ sĩ để tiếp tục đăng tải bài hát, quản lý album
                    và tiếp cận hàng triệu thính giả trên Blue Wave.
                </p>
            </div>

            <div style="text-align:center;">
                <a href="{{ url('/artist/register') }}" class="btn">Đăng ký lại gói Nghệ sĩ</a>
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
