<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gói Premium đã hết hạn</title>
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
        .renew-box { background: rgba(99,102,241,.08); border: 1px solid rgba(99,102,241,.3); border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; }
        .renew-title { color: #a5b4fc; font-size: 14px; font-weight: 600; margin: 0 0 6px; }
        .renew-desc { color: #64748b; font-size: 13px; line-height: 1.5; margin: 0; }
        .message { color: #94a3b8; font-size: 13px; line-height: 1.6; margin-bottom: 24px; }
        .btn { display: inline-block; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff !important; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .footer { text-align: center; padding: 20px 32px; border-top: 1px solid rgba(255,255,255,.06); }
        .footer p { color: #475569; font-size: 12px; margin: 0; line-height: 1.6; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <div class="header-icon">🎵</div>
            <h1>Gói Premium đã hết hạn</h1>
            <p>Tài khoản của bạn đã chuyển về gói Free</p>
        </div>

        <div class="body">
            <p class="greeting">
                Xin chào <strong style="color:#a5b4fc">{{ $user->name }}</strong>,<br>
                Gói <strong>{{ $vip->title }}</strong> của bạn đã hết hạn vào ngày <strong>{{ $subscription->end_date->format('d/m/Y') }}</strong>.
                Tài khoản đã được chuyển về <strong style="color:#94a3b8">Free</strong>.
            </p>

            <div class="info-box">
                <div class="info-title">Thông tin gói đã hết hạn</div>
                <div class="detail-row">
                    <span class="detail-label">Tên gói</span>
                    <span class="detail-value">{{ $vip->title }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Ngày bắt đầu</span>
                    <span class="detail-value">{{ $subscription->start_date->format('d/m/Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Ngày hết hạn</span>
                    <span class="detail-value">{{ $subscription->end_date->format('d/m/Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Số ngày sử dụng</span>
                    <span class="detail-value">{{ $vip->duration_days }} ngày</span>
                </div>
            </div>

            <div class="renew-box">
                <div class="renew-title">✦ Đăng ký lại để tiếp tục</div>
                <p class="renew-desc">
                    Gia hạn bất cứ lúc nào để tiếp tục thưởng thức âm nhạc không giới hạn,
                    tải nhạc offline và trải nghiệm không quảng cáo.
                </p>
            </div>

            <div style="text-align:center;">
                <a href="{{ url('/subscription') }}" class="btn">Đăng ký lại Premium</a>
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
