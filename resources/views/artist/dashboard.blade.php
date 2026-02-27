@extends('layouts.artist')

@section('title', 'Tổng quan – Artist Studio')
@section('page-title', 'Tổng quan')
@section('page-subtitle', 'Artist Studio – Blue Wave Music')

@section('content')
@php
    $user = auth()->user();
    $artistName = $user->name;
    $isVerified = $user->artist_verified_at;
@endphp

{{-- Welcome banner --}}
<div class="mb-5 p-4 rounded-3"
     style="background:linear-gradient(135deg,rgba(168,85,247,.12) 0%,rgba(236,72,153,.06) 100%);border:1px solid rgba(168,85,247,.2)">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div style="width:52px;height:52px;border-radius:14px;background:rgba(168,85,247,.18);border:1px solid rgba(168,85,247,.3);display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#c084fc;flex-shrink:0">
            <i class="fa-solid fa-microphone-lines"></i>
        </div>
        <div>
            <h4 class="text-white fw-bold mb-1">
                Xin chào, {{ $artistName }}!
                @if($isVerified)
                    <i class="fa-solid fa-circle-check ms-2" style="color:#60a5fa;font-size:1rem" title="Đã xác minh"></i>
                @endif
            </h4>
            <p class="text-muted mb-0" style="font-size:.88rem">
                @if($isVerified)
                    Tài khoản nghệ sĩ của bạn đã được xác minh. Hãy bắt đầu chia sẻ âm nhạc!
                @else
                    Tài khoản nghệ sĩ đang hoạt động. Tick xanh xác minh đang chờ quản trị viên duyệt.
                @endif
            </p>
        </div>
        @if(!$isVerified)
        <div class="ms-auto">
            <span class="badge" style="background:rgba(251,191,36,.12);color:#fbbf24;border:1px solid rgba(251,191,36,.25);font-size:.72rem;padding:6px 10px">
                <i class="fa-solid fa-clock me-1"></i>Chờ xác minh tick xanh
            </span>
        </div>
        @endif
    </div>
</div>

{{-- Stat cards --}}
<div class="row g-3 mb-5">
    <div class="col-6 col-lg-3">
        <div class="artist-stat-card">
            <div class="stat-icon" style="background:rgba(168,85,247,.15);color:#c084fc">
                <i class="fa-solid fa-music"></i>
            </div>
            <div>
                <div class="stat-label">Bài hát</div>
                <div class="stat-value">0</div>
                <div class="stat-sub">Đã đăng tải</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="artist-stat-card">
            <div class="stat-icon" style="background:rgba(59,130,246,.15);color:#60a5fa">
                <i class="fa-solid fa-compact-disc"></i>
            </div>
            <div>
                <div class="stat-label">Album</div>
                <div class="stat-value">0</div>
                <div class="stat-sub">Đã tạo</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="artist-stat-card">
            <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#34d399">
                <i class="fa-solid fa-headphones"></i>
            </div>
            <div>
                <div class="stat-label">Lượt nghe</div>
                <div class="stat-value">0</div>
                <div class="stat-sub">Tổng cộng</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="artist-stat-card">
            <div class="stat-icon" style="background:rgba(245,158,11,.15);color:#fbbf24">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="stat-label">Người theo dõi</div>
                <div class="stat-value">0</div>
                <div class="stat-sub">Followers</div>
            </div>
        </div>
    </div>
</div>

{{-- Quick actions --}}
<div class="mb-4">
    <h6 class="text-muted fw-semibold text-uppercase mb-3" style="font-size:.72rem;letter-spacing:.08em">Thao tác nhanh</h6>
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('artist.songs.create') }}"
               class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none"
               style="background:rgba(168,85,247,.08);border:1px solid rgba(168,85,247,.18);transition:all .2s"
               onmouseover="this.style.background='rgba(168,85,247,.14)'" onmouseout="this.style.background='rgba(168,85,247,.08)'">
                <div style="width:40px;height:40px;border-radius:10px;background:rgba(168,85,247,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fa-solid fa-cloud-arrow-up" style="color:#c084fc"></i>
                </div>
                <div>
                    <div class="text-white fw-semibold" style="font-size:.88rem">Tải lên bài hát</div>
                    <div class="text-muted" style="font-size:.75rem">MP3, FLAC, WAV</div>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('artist.albums.index') }}"
               class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none"
               style="background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.18);transition:all .2s"
               onmouseover="this.style.background='rgba(59,130,246,.14)'" onmouseout="this.style.background='rgba(59,130,246,.08)'">
                <div style="width:40px;height:40px;border-radius:10px;background:rgba(59,130,246,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fa-solid fa-compact-disc" style="color:#60a5fa"></i>
                </div>
                <div>
                    <div class="text-white fw-semibold" style="font-size:.88rem">Tạo Album</div>
                    <div class="text-muted" style="font-size:.75rem">Quản lý bộ sưu tập</div>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('artist.profile.edit') }}"
               class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none"
               style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.18);transition:all .2s"
               onmouseover="this.style.background='rgba(16,185,129,.14)'" onmouseout="this.style.background='rgba(16,185,129,.08)'">
                <div style="width:40px;height:40px;border-radius:10px;background:rgba(16,185,129,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fa-solid fa-user-pen" style="color:#34d399"></i>
                </div>
                <div>
                    <div class="text-white fw-semibold" style="font-size:.88rem">Cập nhật hồ sơ</div>
                    <div class="text-muted" style="font-size:.75rem">Tiểu sử, ảnh bìa</div>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('artist.stats.index') }}"
               class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none"
               style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.18);transition:all .2s"
               onmouseover="this.style.background='rgba(245,158,11,.14)'" onmouseout="this.style.background='rgba(245,158,11,.08)'">
                <div style="width:40px;height:40px;border-radius:10px;background:rgba(245,158,11,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fa-solid fa-chart-line" style="color:#fbbf24"></i>
                </div>
                <div>
                    <div class="text-white fw-semibold" style="font-size:.88rem">Xem thống kê</div>
                    <div class="text-muted" style="font-size:.75rem">Lượt nghe, xu hướng</div>
                </div>
            </a>
        </div>
    </div>
</div>

{{-- Coming soon notice --}}
<div class="p-4 rounded-3 text-center"
     style="background:rgba(255,255,255,.02);border:1px dashed rgba(255,255,255,.1)">
    <i class="fa-solid fa-hammer mb-2" style="font-size:1.5rem;color:rgba(168,85,247,.5)"></i>
    <p class="text-muted mb-0" style="font-size:.85rem">
        Các tính năng quản lý âm nhạc đang được xây dựng. Hãy quay lại sớm!
    </p>
</div>

@endsection
