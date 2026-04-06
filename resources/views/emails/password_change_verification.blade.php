<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đổi mật khẩu</title>
    <style>
        body { margin: 0; padding: 0; background: #0f0f13; font-family: 'Segoe UI', Arial, sans-serif; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 32px 16px; }
        .card { background: #1a1a24; border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,.08); }
        .header { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 32px 32px 28px; text-align: center; }
        .header-icon { font-size: 36px; margin-bottom: 12px; }
        .header h1 { margin: 0; color: #fff; font-size: 22px; font-weight: 700; }
        .header p { margin: 6px 0 0; color: rgba(255,255,255,.8); font-size: 14px; }
        .body { padding: 28px 32px; }
        .greeting { color: #e2e8f0; font-size: 15px; margin-bottom: 20px; line-height: 1.6; }
        .info-box { background: rgba(99,102,241,.1); border: 1px solid rgba(99,102,241,.3); border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .info-label { color: #94a3b8; font-size: 13px; margin-bottom: 4px; }
        .info-value { color: #e2e8f0; font-size: 15px; font-weight: 600; }
        .warning-box { background: rgba(251,191,36,.08); border: 1px solid rgba(251,191,36,.25); border-radius: 12px; padding: 16px; margin-bottom: 20px; }
        .warning-text { color: #fbbf24; font-size: 13px; line-height: 1.6; margin: 0; }
        .message { color: #94a3b8; font-size: 13px; line-height: 1.6; margin-bottom: 24px; }
        .btn { display: inline-block; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff !important; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .btn:hover { opacity: .9; }
        .footer { text-align: center; padding: 20px 32px; border-top: 1px solid rgba(255,255,255,.06); }
        .footer p { color: #475569; font-size: 12px; margin: 0; line-height: 1.6; }
        .expire-note { color: #64748b; font-size: 12px; margin-top: 16px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <div class="header-icon">🔐</div>
            <h1>Xác nhận đổi mật khẩu</h1>
            <p>Yêu cầu thay đổi mật khẩu tài khoản của bạn</p>
        </div>

        <div class="body">
            <p class="greeting">
                Xin chào <strong style="color:#a5b4fc">{{ $user->name }}</strong>,<br>
                Chúng tôi nhận được yêu cầu thay đổi mật khẩu cho tài khoản Blue Wave Music của bạn.
                Vui lòng bấm nút bên dưới để xác nhận thay đổi.
            </p>

            <div class="info-box">
                <div class="info-label">Tài khoản</div>
                <div class="info-value">{{ $user->email }}</div>
            </div>

            <div class="warning-box">
                <p class="warning-text">
                    ⚠️ Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này. Mật khẩu hiện tại của bạn sẽ không bị thay đổi.
                </p>
            </div>

            <p class="message">
                Bấm nút bên dưới để xác nhận và áp dụng mật khẩu mới cho tài khoản của bạn.
            </p>

            <div style="text-align:center;">
                <a href="{{ $verificationUrl }}" class="btn">Xác nhận đổi mật khẩu</a>
            </div>

            <p class="expire-note" style="text-align:center;">
                Liên kết này sẽ hết hạn sau 60 phút.
            </p>
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
