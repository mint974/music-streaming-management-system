<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo Thống kê - BlueWave Music</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #6366f1; padding-bottom: 10px; }
        .logo { font-size: 24px; font-weight: bold; color: #6366f1; }
        .report-info { margin-bottom: 20px; font-style: italic; color: #666; }
        .section-title { font-size: 16px; font-weight: bold; margin: 20px 0 10px; color: #1e293b; border-left: 4px solid #6366f1; padding-left: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background: #f8fafc; color: #64748b; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .text-end { text-align: right; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 5px; }
        .summary-box { background: #f8fafc; border-radius: 8px; padding: 15px; margin-bottom: 20px; }
        .summary-item { display: inline-block; width: 30%; vertical-align: top; }
        .summary-label { font-size: 10px; color: #64748b; margin-bottom: 5px; }
        .summary-value { font-size: 18px; font-weight: bold; color: #1e293b; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: bold; }
        .badge-success { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">BlueWave Music Analysis</div>
        <div style="font-size: 14px; margin-top: 5px;">BÁO CÁO PHÂN TÍCH HỆ THỐNG</div>
    </div>

    <div class="report-info">
        Hệ thống: BlueWave Music | Loại báo cáo: {{ strtoupper($tab) }} | Phạm vi: {{ $startDate }} đến {{ $endDate }}
    </div>

    <div class="summary-box">
        <div class="summary-item">
            @if($tab === 'users')
                <div class="summary-label">TỔNG USERS HỆ THỐNG</div>
                <div class="summary-value">{{ number_format(array_sum($roles)) }}</div>
            @else
                <div class="summary-label">TỔNG DOANH THU KỲ</div>
                <div class="summary-value">{{ number_format($totalRevenue) }} ₫</div>
            @endif
        </div>
        <div class="summary-item">
            <div class="summary-label">NGÀY XUẤT BẢN</div>
            <div class="summary-value">{{ now()->format('d/m/Y H:i') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label"> TRẠNG THÁI</div>
            <div class="summary-value"><span class="badge badge-success">HOÀN TẤT</span></div>
        </div>
    </div>

    @if($tab === 'revenue')
        <div class="section-title">Chi tiết lịch sử doanh thu</div>
        <table>
            <thead>
                <tr>
                    <th>Ngày thanh toán</th>
                    <th>Người dùng</th>
                    <th>Loại giao dịch</th>
                    <th class="text-end">Số tiền (VNĐ)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $payments = \App\Models\Payment::with('user')->where('status', 'paid')
                        ->whereBetween('paid_at', [$startDate, $endDate])
                        ->orderByDesc('paid_at')->get();
                @endphp
                @forelse($payments as $p)
                <tr>
                    <td>{{ $p->paid_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $p->user->name ?? 'N/A' }}</td>
                    <td>{{ str_contains($p->payable_type, 'Subscription') ? 'Gói VIP' : 'Đăng ký Nghệ sĩ' }}</td>
                    <td class="text-end">{{ number_format($p->amount) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align: center;">Không có dữ liệu trong kỳ</td></tr>
                @endforelse
            </tbody>
        </table>
    @elseif($tab === 'content')
        <div class="section-title">Top Trending Bài hát</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">Hạng</th>
                    <th>Bài hát</th>
                    <th>Nghệ sĩ</th>
                    <th>Album</th>
                    <th class="text-end">Lượt nghe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topTrendingSongs as $index => $song)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $song->name }}</td>
                    <td>{{ $song->artist }}</td>
                    <td>{{ $song->album ?? '-' }}</td>
                    <td class="text-end">{{ number_format($song->total) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="section-title">Phân tích Thể loại phổ biến</div>
        <table>
            <thead>
                <tr>
                    <th>Thể loại</th>
                    <th>Số bài hát</th>
                    <th class="text-end">Tổng lượt nghe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topGenresContent as $genre)
                <tr>
                    <td>{{ $genre->name }}</td>
                    <td>{{ $genre->song_count }}</td>
                    <td class="text-end">{{ number_format($genre->total) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="section-title">Top Từ khóa Tìm kiếm</div>
        <table>
            <thead>
                <tr>
                    <th>Từ khóa</th>
                    <th class="text-end">Số lượt tìm</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topSearchQueries as $q)
                <tr>
                    <td>{{ $q->query }}</td>
                    <td class="text-end">{{ number_format($q->search_count) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($tab === 'users')
        <div class="section-title">Danh sách người dùng tham gia mới (Trích đoạn 50 bản ghi)</div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên người dùng</th>
                    <th>Email</th>
                    <th>Giới tính</th>
                    <th>Ngày sinh</th>
                    <th>Ngày tham gia</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $users = \App\Models\User::where('deleted', false)->whereBetween('created_at', [$startDate, $endDate])->orderByDesc('created_at')->take(50)->get();
                @endphp
                @foreach($users as $u)
                <tr>
                    <td>#{{ $u->id }}</td>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>{{ $u->gender == 1 ? 'Nam' : ($u->gender == 2 ? 'Nữ' : 'Khác') }}</td>
                    <td>{{ $u->birthday ? \Carbon\Carbon::parse($u->birthday)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $u->created_at->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="section-title">Thống kê lượt nghe hằng ngày</div>
        <table>
            <thead>
                <tr>
                    <th>Ngày</th>
                    <th class="text-end">Tổng lượt nghe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($listenTrend as $t)
                <tr>
                    <td>{{ $t->date }}</td>
                    <td class="text-end">{{ number_format($t->total) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Trang báo cáo này được tạo tự động bởi BlueWave Admin Dashboard. &copy; {{ date('Y') }} BlueWave Music.
    </div>
</body>
</html>
