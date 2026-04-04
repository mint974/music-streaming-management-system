<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; background: #fff; }

  .header { background: #7c3aed; color: #fff; padding: 24px 32px; }
  .header h1 { font-size: 20px; font-weight: bold; margin-bottom: 4px; }
  .header p  { font-size: 11px; opacity: .85; }

  .badge { display: inline-block; background: rgba(255,255,255,.2); border-radius: 4px; padding: 2px 8px; font-size: 10px; margin-top: 8px; }

  .body { padding: 24px 32px; }

  /* KPI row */
  .kpi-row { display: flex; gap: 12px; margin-bottom: 24px; }
  .kpi-box { flex: 1; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; text-align: center; }
  .kpi-box .label { font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: .5px; }
  .kpi-box .value { font-size: 22px; font-weight: bold; color: #7c3aed; margin: 4px 0 0; }

  /* Section title */
  .section-title { font-size: 12px; font-weight: bold; color: #7c3aed; border-bottom: 2px solid #e9d5ff; padding-bottom: 6px; margin-bottom: 12px; }

  /* Table */
  table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
  thead th { background: #7c3aed; color: #fff; font-size: 10px; padding: 7px 10px; text-align: left; }
  tbody tr:nth-child(even) td { background: #f5f3ff; }
  tbody td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }

  .badge-pub  { background:#d1fae5; color:#065f46; border-radius:4px; padding:1px 6px; font-size:9px; }
  .badge-pend { background:#fef9c3; color:#713f12; border-radius:4px; padding:1px 6px; font-size:9px; }
  .badge-other{ background:#f1f5f9; color:#475569; border-radius:4px; padding:1px 6px; font-size:9px; }

  .footer { margin-top: 32px; font-size: 9px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 10px; }
</style>
</head>
<body>

<div class="header">
  <h1>📊 Báo cáo thống kê nghệ sĩ</h1>
  <p>{{ $artist->artist_name ?: $artist->name }}</p>
  <span class="badge">{{ $dateFrom->format('d/m/Y') }} – {{ $dateTo->format('d/m/Y') }}</span>
  <span class="badge" style="margin-left:8px;">Xuất: {{ now()->format('H:i d/m/Y') }}</span>
</div>

<div class="body">

  {{-- KPI --}}
  <div class="kpi-row">
    <div class="kpi-box">
      <div class="label">Lượt nghe kỳ này</div>
      <div class="value">{{ number_format($totalInPeriod) }}</div>
    </div>
    <div class="kpi-box">
      <div class="label">Tổng bài hát</div>
      <div class="value">{{ $totalSongs }}</div>
    </div>
    <div class="kpi-box">
      <div class="label">Top bài hát</div>
      <div class="value">{{ $topSongs->first()?->title ? \Illuminate\Support\Str::limit($topSongs->first()->title, 14) : '—' }}</div>
    </div>
  </div>

  {{-- Daily listens --}}
  <div class="section-title">Lượt nghe theo ngày</div>
  <table>
    <thead>
      <tr>
        <th>Ngày</th>
        <th>Lượt nghe</th>
        <th>So với trung bình</th>
      </tr>
    </thead>
    <tbody>
      @php $avg = $totalInPeriod > 0 && $dailyRaw->count() > 0 ? $totalInPeriod / $dailyRaw->count() : 0; @endphp
      @foreach($dailyRaw as $row)
      <tr>
        <td>{{ \Carbon\Carbon::parse($row->stat_date)->format('d/m/Y (D)') }}</td>
        <td><strong>{{ number_format($row->total) }}</strong></td>
        <td>
          @php $diff = $row->total - $avg; @endphp
          @if($diff > 0)
            <span style="color:#16a34a">▲ {{ number_format(abs($diff)) }}</span>
          @elseif($diff < 0)
            <span style="color:#dc2626">▼ {{ number_format(abs($diff)) }}</span>
          @else
            <span style="color:#94a3b8">—</span>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{-- Top songs --}}
  <div class="section-title">Top 10 bài hát phổ biến nhất</div>
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Tên bài hát</th>
        <th>Tổng lượt nghe</th>
        <th>Trạng thái</th>
      </tr>
    </thead>
    <tbody>
      @foreach($topSongs as $i => $song)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $song->title }}</td>
        <td><strong>{{ number_format($song->listens) }}</strong></td>
        <td>
          @if($song->status === 'published')
            <span class="badge-pub">Công khai</span>
          @elseif($song->status === 'pending')
            <span class="badge-pend">Chờ duyệt</span>
          @else
            <span class="badge-other">{{ $song->status }}</span>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

</div>

<div class="footer">
  Blue Wave Music · Tài liệu này được tạo tự động · {{ now()->format('d/m/Y') }}
</div>

</body>
</html>
