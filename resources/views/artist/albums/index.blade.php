@extends('layouts.artist')

@section('title', 'Album – Artist Studio')
@section('page-title', 'Album')
@section('page-subtitle', 'Tạo và quản lý bộ sưu tập âm nhạc')

@section('content')
@include('artist.partials.coming-soon', [
    'icon'    => 'fa-solid fa-compact-disc',
    'title'   => 'Quản lý album',
    'desc'    => 'Tạo album mới (tên, mô tả, ảnh bìa). Thêm/xóa/sắp xếp bài hát. Xuất bản hoặc ẩn album.',
    'color'   => '#60a5fa',
    'bgColor' => 'rgba(59,130,246,.12)',
])
@endsection
