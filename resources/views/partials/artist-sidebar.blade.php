@php
    $offcanvas  = $isOffcanvas ?? false;
    $artistUser = auth()->user();
    $initial    = $artistUser ? strtoupper(substr($artistUser->name, 0, 1)) : 'A';
    $avatarSvg  = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='36' height='36'%3E%3Ccircle cx='18' cy='18' r='18' fill='%23a855f7'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='16' fill='%23ffffff' font-weight='bold'%3E{$initial}%3C/text%3E%3C/svg%3E";
    $avatar     = ($artistUser && $artistUser->avatar && $artistUser->avatar !== '/storage/avt.jpg')
                    ? asset($artistUser->avatar)
                    : $avatarSvg;
@endphp

<aside class="artist-sidebar {{ $offcanvas ? 'is-offcanvas' : '' }}">
    <div class="artist-sidebar-inner">

        {{-- ── Brand ── --}}
        <div class="artist-sidebar-brand">
            <div class="artist-brand-logo">
                <i class="fa-solid fa-microphone-lines"></i>
            </div>
            <div>
                <div class="artist-brand-title">Artist Studio</div>
                <div class="artist-brand-sub">Blue Wave Music</div>
            </div>
        </div>

        {{-- ── Artist profile card ── --}}
        @if($artistUser)
        <div class="artist-sidebar-profile">
            <div class="d-flex align-items-center gap-2">
                <img src="{{ $avatar }}" alt="{{ $artistUser->name }}"
                     style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid rgba(168,85,247,.4);flex-shrink:0">
                <div class="min-w-0">
                    <div class="artist-profile-name text-truncate">{{ $artistUser->name }}</div>
                    <div class="artist-profile-role">
                        @if($artistUser->artist_verified_at)
                            <i class="fa-solid fa-circle-check me-1" style="color:#60a5fa;font-size:.7rem"></i>Đã xác minh
                        @else
                            Nghệ sĩ
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Navigation ── --}}
        <nav class="artist-nav">

            {{-- Tổng quan --}}
            <a href="{{ route('artist.dashboard') }}"
               class="artist-nav-link {{ request()->routeIs('artist.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Tổng quan</span>
            </a>

            {{-- ── Hồ sơ nghệ sĩ ── --}}
            <div class="artist-nav-group-label">Hồ sơ</div>

            <a href="{{ route('artist.profile.edit') }}"
               class="artist-nav-link {{ request()->routeIs('artist.profile.*') ? 'active' : '' }}">
                <i class="fa-solid fa-user-pen"></i>
                <span>Hồ sơ nghệ sĩ</span>
            </a>

            {{-- ── Âm nhạc ── --}}
            <div class="artist-nav-group-label">Âm nhạc</div>

            <a href="{{ route('artist.songs.index') }}"
               class="artist-nav-link {{ request()->routeIs('artist.songs.*') && !request()->routeIs('artist.songs.create') ? 'active' : '' }}">
                <i class="fa-solid fa-music"></i>
                <span>Bài hát của tôi</span>
            </a>

            <a href="{{ route('artist.songs.create') }}"
               class="artist-nav-link {{ request()->routeIs('artist.songs.create') ? 'active' : '' }}">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <span>Tải lên bài hát</span>
            </a>

            <a href="{{ route('artist.albums.index') }}"
               class="artist-nav-link {{ request()->routeIs('artist.albums.*') ? 'active' : '' }}">
                <i class="fa-solid fa-compact-disc"></i>
                <span>Album</span>
            </a>

            {{-- ── Thống kê ── --}}
            <div class="artist-nav-group-label">Phân tích</div>

            <a href="{{ route('artist.stats.index') }}"
               class="artist-nav-link {{ request()->routeIs('artist.stats.*') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line"></i>
                <span>Thống kê</span>
            </a>

        </nav>

        {{-- ── Footer: back to user + logout ── --}}
        <div class="artist-sidebar-footer">
            <a href="{{ route('home') }}" class="artist-back-link">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Về trang người dùng</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="artist-logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Đăng xuất</span>
                </button>
            </form>
        </div>

    </div>
</aside>
