<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gói Premium sắp hết hạn</title>
    <style>
        body { margin: 0; padding: 0; background: #0f0f13; font-family: 'Segoe UI', Arial, sans-serif; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 32px 16px; }
        .card { background: #1a1a24; border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,.08); }
        .header { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 32px 32px 28px; text-align: center; }
        .header-icon { font-size: 36px; margin-bottom: 12px; }
        .header h1 { margin: 0; color: #fff; font-size: 22px; font-weight: 700; }
        .header p { margin: 6px 0 0; color: rgba(255,255,255,.85); font-size: 14px; }
        .body { padding: 28px 32px; }
        .greeting { color: #e2e8f0; font-size: 15px; margin-bottom: 20px; }
        .alert-box { background: rgba(245,158,11,.1); border: 1px solid rgba(245,158,11,.35); border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .alert-title { color: #fbbf24; font-size: 16px; font-weight: 700; margin: 0 0 10px; }
        .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,.06); }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #94a3b8; font-size: 13px; }
        .detail-value { color: #e2e8f0; font-size: 13px; font-weight: 600; }
        .detail-value.red { color: #f87171; }
        .detail-value.gold { color: #fbbf24; }
        .message { color: #94a3b8; font-size: 13px; line-height: 1.6; margin-bottom: 24px; }
        .btn { display: inline-block; background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff !important; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .footer { text-align: center; padding: 20px 32px; border-top: 1px solid rgba(255,255,255,.06); }
        .footer p { color: #475569; font-size: 12px; margin: 0; line-height: 1.6; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <div class="header-icon">⏰</div>
            <h1>Gói Premium sắp hết hạn!</h1>
            <p>Gia hạn ngay để không bị gián đoạn trải nghiệm âm nhạc</p>
        </div>

        <div class="body">
            <p class="greeting">
                Xin chào <strong style="color:#fbbf24">{{ $user->name }}</strong>,<br>
                Gói <strong>{{ $vip->title }}</strong> của bạn sẽ hết hạn vào <strong style="color:#f87171">ngày mai ({{ $subscription->end_date->format('d/m/Y') }})</strong>.
                Sau thời điểm này, tài khoản sẽ tự động trở về gói <strong style="color:#94a3b8">Free</strong>.
            </p>

            <div class="alert-box">
                <div class="alert-title">⚠ Thông tin gói của bạn</div>
                <div class="detail-row">
                    <span class="detail-label">Tên gói</span>
                    <span class="detail-value gold">{{ $vip->title }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Ngày bắt đầu</span>
                    <span class="detail-value">{{ $subscription->start_date->format('d/m/Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Ngày hết hạn</span>
                    <span class="detail-value red">{{ $subscription->end_date->format('d/m/Y') }}</span>
                </div>
            </div>

            <p class="message">
                Để tiếp tục thưởng thức âm nhạc không giới hạn, tải nhạc offline và trải nghiệm không quảng cáo,
                hãy gia hạn gói Premium ngay hôm nay. Bạn có thể chọn lại gói cũ hoặc nâng cấp lên gói dài hơn.
            </p>

            <div style="text-align:center;">
                <a href="{{ url('/subscription') }}" class="btn">Gia hạn ngay</a>
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
