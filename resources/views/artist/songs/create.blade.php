@extends('layouts.artist')

@section('title', 'Tải lên bài hát – Artist Studio')
@section('page-title', 'Tải lên bài hát')
@section('page-subtitle', 'Đăng tải nhạc MP3, FLAC, WAV')

@section('content')
@include('artist.partials.coming-soon', [
    'icon'    => 'fa-solid fa-cloud-arrow-up',
    'title'   => 'Tải lên bài hát',
    'desc'    => 'Upload file nhạc (MP3, FLAC, WAV). Nhập tên, thể loại, album, năm phát hành, ảnh bìa. Nhập lời bài hát. Gắn tag: tâm trạng, hoạt động.',
    'color'   => '#c084fc',
    'bgColor' => 'rgba(168,85,247,.12)',
])
@endsection
