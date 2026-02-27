@extends('layouts.artist')

@section('title', 'Hồ sơ nghệ sĩ – Artist Studio')
@section('page-title', 'Hồ sơ nghệ sĩ')
@section('page-subtitle', 'Cập nhật thông tin trang cá nhân nghệ sĩ')

@section('content')
@include('artist.partials.coming-soon', [
    'icon'    => 'fa-solid fa-user-pen',
    'title'   => 'Hồ sơ nghệ sĩ',
    'desc'    => 'Cập nhật nghệ danh, tiểu sử, ảnh đại diện, ảnh bìa, liên kết MXH. Tùy chỉnh trang cá nhân nghệ sĩ hiển thị với người nghe.',
    'color'   => '#34d399',
    'bgColor' => 'rgba(16,185,129,.12)',
])
@endsection
