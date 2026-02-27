@extends('layouts.artist')

@section('title', 'Bài hát – Artist Studio')
@section('page-title', 'Bài hát của tôi')
@section('page-subtitle', 'Quản lý tất cả bài hát đã đăng tải')

@section('content')
@include('artist.partials.coming-soon', [
    'icon'    => 'fa-solid fa-music',
    'title'   => 'Quản lý bài hát',
    'desc'    => 'Tại đây bạn có thể xem, sửa, ẩn/hiện và xóa các bài hát đã tải lên.',
    'color'   => '#c084fc',
    'bgColor' => 'rgba(168,85,247,.12)',
])
@endsection
