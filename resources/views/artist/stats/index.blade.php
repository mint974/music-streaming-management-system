@extends('layouts.artist')

@section('title', 'Thống kê – Artist Studio')
@section('page-title', 'Thống kê')
@section('page-subtitle', 'Phân tích lượt nghe và tương tác')

@section('content')
@include('artist.partials.coming-soon', [
    'icon'    => 'fa-solid fa-chart-line',
    'title'   => 'Thống kê cho nghệ sĩ',
    'desc'    => 'Tổng lượt nghe theo ngày/tuần/tháng. Số người theo dõi, xu hướng. Phân bổ thống kê theo giới tính, độ tuổi, vùng miền. Bài hát phổ biến nhất. Biểu đồ tăng trưởng.',
    'color'   => '#fbbf24',
    'bgColor' => 'rgba(245,158,11,.12)',
])
@endsection
