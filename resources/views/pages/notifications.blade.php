@extends('layouts.main')

@section('title', 'Thông báo')

@section('content')
<div class="container-fluid py-4" style="max-width: 720px">

    {{-- Page header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold text-white mb-0">
                <i class="fa-solid fa-bell me-2" style="color:#818cf8"></i>Thông báo
            </h4>
            <small class="text-muted">Cập nhật mới nhất về tài khoản của bạn</small>
        </div>
        @if(auth()->user()->notifications->isNotEmpty())
        <form method="POST" action="{{ route('notifications.markAllRead') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-check-double me-1"></i>Đánh dấu tất cả đã đọc
            </button>
        </form>
        @endif
    </div>

    {{-- Notification list --}}
    @forelse($notifications as $notification)
    @php
        $data    = $notification->data;
        $isRead  = $notification->read_at !== null;
        $icon    = $data['icon']  ?? 'fa-bell';
        $color   = $data['color'] ?? '#818cf8';
        $title   = $data['title'] ?? 'Thông báo';
        $message = $data['message'] ?? '';
        $label   = $data['action_label'] ?? 'Xem';
    @endphp

    <div class="d-flex gap-3 mb-3 p-3 rounded-3 position-relative
                {{ $isRead ? '' : 'border border-opacity-25' }}"
         style="background: {{ $isRead ? 'rgba(255,255,255,.03)' : 'rgba(99,102,241,.06)' }};
                {{ !$isRead ? 'border-color:rgba(99,102,241,.35)!important' : '' }}">

        {{-- Unread dot --}}
        @if(!$isRead)
        <span class="position-absolute top-0 end-0 mt-2 me-3"
              style="width:8px;height:8px;background:#818cf8;border-radius:50%;display:inline-block"></span>
        @endif

        {{-- Icon --}}
        <div class="flex-shrink-0 d-flex align-items-start pt-1">
            <div class="rounded-circle d-flex align-items-center justify-content-center"
                 style="width:40px;height:40px;background:{{ $color }}20;border:1px solid {{ $color }}40">
                <i class="fa-solid {{ $icon }}" style="color:{{ $color }};font-size:.9rem"></i>
            </div>
        </div>

        {{-- Content --}}
        <div class="flex-grow-1 min-w-0">
            <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                <span class="fw-semibold text-white" style="font-size:.9rem">{{ $title }}</span>
                <span class="text-muted flex-shrink-0" style="font-size:.72rem">
                    {{ $notification->created_at->diffForHumans() }}
                </span>
            </div>
            <p class="text-muted mb-2" style="font-size:.85rem;line-height:1.5">{{ $message }}</p>
            <div class="d-flex gap-2">
                <a href="{{ route('notifications.read', $notification->id) }}"
                   class="btn btn-sm"
                   style="font-size:.75rem;background:{{ $color }}18;color:{{ $color }};border:1px solid {{ $color }}30;padding:.2rem .7rem">
                    {{ $label }}&nbsp;<i class="fa-solid fa-arrow-right ms-1" style="font-size:.65rem"></i>
                </a>
                <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm text-muted"
                            style="font-size:.75rem;padding:.2rem .6rem"
                            title="Xóa thông báo này">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </form>
            </div>
        </div>

    </div>
    @empty
    <div class="text-center py-5 text-muted">
        <i class="fa-solid fa-bell-slash fa-2x mb-3 opacity-25 d-block"></i>
        <div>Bạn chưa có thông báo nào.</div>
    </div>
    @endforelse

    {{-- Pagination --}}
    @if($notifications->hasPages())
    <div class="mt-4">
        {{ $notifications->links('pagination::bootstrap-5') }}
    </div>
    @endif

</div>
@endsection
