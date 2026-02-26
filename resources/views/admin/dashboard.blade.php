@extends('layouts.admin')

@section('title', 'Tổng quan')
@section('page-title', 'Tổng quan')
@section('page-subtitle', 'Chào mừng trở lại, ' . auth('admin')->user()?->name)

@section('content')
<div class="row g-4 mb-4">

    {{-- Stat card: Người dùng --}}
    <div class="col-6 col-xl-3">
        <div class="card bg-dark border-secondary border-opacity-25 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3" style="background:rgba(99,102,241,.15);">
                    <i class="fa-solid fa-users fa-lg" style="color:#818cf8"></i>
                </div>
                <div>
                    <div class="text-muted small">Người dùng</div>
                    <div class="text-white fw-bold fs-4">0</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat card: Ca sĩ --}}
    <div class="col-6 col-xl-3">
        <div class="card bg-dark border-secondary border-opacity-25 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3" style="background:rgba(168,85,247,.15);">
                    <i class="fa-solid fa-microphone-lines fa-lg" style="color:#c084fc"></i>
                </div>
                <div>
                    <div class="text-muted small">Ca sĩ</div>
                    <div class="text-white fw-bold fs-4">0</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat card: Bài hát --}}
    <div class="col-6 col-xl-3">
        <div class="card bg-dark border-secondary border-opacity-25 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3" style="background:rgba(20,184,166,.15);">
                    <i class="fa-solid fa-music fa-lg" style="color:#2dd4bf"></i>
                </div>
                <div>
                    <div class="text-muted small">Bài hát</div>
                    <div class="text-white fw-bold fs-4">0</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat card: Lượt nghe --}}
    <div class="col-6 col-xl-3">
        <div class="card bg-dark border-secondary border-opacity-25 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3" style="background:rgba(245,158,11,.15);">
                    <i class="fa-solid fa-headphones fa-lg" style="color:#fbbf24"></i>
                </div>
                <div>
                    <div class="text-muted small">Lượt nghe</div>
                    <div class="text-white fw-bold fs-4">0</div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="row g-4">
    {{-- Quick links --}}
    <div class="col-12 col-lg-4">
        <div class="card bg-dark border-secondary border-opacity-25">
            <div class="card-header bg-transparent border-secondary border-opacity-25">
                <span class="fw-semibold text-white small">Truy cập nhanh</span>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('admin.users.index') }}"
                   class="list-group-item list-group-item-action bg-transparent border-secondary border-opacity-25 text-muted d-flex align-items-center gap-2 py-3">
                    <i class="fa-solid fa-users fa-fw" style="color:#818cf8"></i> Quản lý người dùng
                </a>
                <a href="{{ route('admin.artists.index') }}"
                   class="list-group-item list-group-item-action bg-transparent border-secondary border-opacity-25 text-muted d-flex align-items-center gap-2 py-3">
                    <i class="fa-solid fa-microphone-lines fa-fw" style="color:#c084fc"></i> Quản lý nghệ sĩ
                </a>
                <a href="{{ route('admin.songs.index') }}"
                   class="list-group-item list-group-item-action bg-transparent border-secondary border-opacity-25 text-muted d-flex align-items-center gap-2 py-3">
                    <i class="fa-solid fa-music fa-fw" style="color:#2dd4bf"></i> Quản lý bài hát
                </a>
                <a href="{{ route('admin.reports.index') }}"
                   class="list-group-item list-group-item-action bg-transparent border-secondary border-opacity-25 text-muted d-flex align-items-center gap-2 py-3">
                    <i class="fa-solid fa-chart-line fa-fw" style="color:#fbbf24"></i> Thống kê & Báo cáo
                </a>
            </div>
        </div>
    </div>

    {{-- Placeholder chart area --}}
    <div class="col-12 col-lg-8">
        <div class="card bg-dark border-secondary border-opacity-25">
            <div class="card-header bg-transparent border-secondary border-opacity-25">
                <span class="fw-semibold text-white small">Lượt nghe theo thời gian</span>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center" style="min-height:220px;">
                <div class="text-center text-muted">
                    <i class="fa-solid fa-chart-area fa-3x mb-3 opacity-25"></i>
                    <p class="small mb-0">Biểu đồ sẽ hiển thị tại đây</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
