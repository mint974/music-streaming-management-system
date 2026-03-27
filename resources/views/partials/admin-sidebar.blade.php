@php($offcanvas = $isOffcanvas ?? false)

<aside class="admin-sidebar {{ $offcanvas ? 'is-offcanvas' : '' }}">
    <div class="admin-sidebar-inner">

        {{-- ─── Brand ─── --}}
        <div class="admin-sidebar-brand">
            <div class="admin-brand-logo">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <div class="admin-brand-title">Blue Wave</div>
                <div class="admin-brand-sub">Admin Panel</div>
            </div>
        </div>

        {{-- ─── Nav ─── --}}
        <nav class="admin-nav">

            {{-- Tổng quan --}}
            <a href="{{ route('admin.dashboard') }}"
               class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Tổng quan</span>
            </a>

            {{-- ─── Quản lý tài khoản cá nhân ─── --}}
            <div class="admin-nav-group-label">Tài khoản</div>

            <a href="{{ route('admin.profile.edit') }}"
               class="admin-nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                <i class="fa-solid fa-user-gear"></i>
                <span>Thông tin cá nhân</span>
            </a>

            {{-- ─── Quản lý người dùng ─── --}}
            <div class="admin-nav-group-label">Người dùng</div>

            <a href="{{ route('admin.users.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i>
                <span>Danh sách người dùng</span>
            </a>

            {{-- ─── Quản lý nghệ sĩ ─── --}}
            <div class="admin-nav-group-label">Nghệ sĩ</div>

            <a href="{{ route('admin.artists.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.artists.*') ? 'active' : '' }}">
                <i class="fa-solid fa-microphone-lines"></i>
                <span>Danh sách nghệ sĩ</span>
            </a>

            <a href="{{ route('admin.artist-registrations.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.artist-registrations.*') ? 'active' : '' }}">
                <i class="fa-solid fa-user-plus"></i>
                <span>Đăng ký Nghệ sĩ</span>
                @if($pendingArtist > 0)
                    <span class="badge rounded-pill ms-auto" style="background:rgba(192,132,252,.2);color:#c084fc;border:1px solid rgba(192,132,252,.3);font-size:.65rem">{{ $pendingArtist }}</span>
                @endif
            </a>

            {{-- ─── Quản lý bài hát ─── --}}
            <div class="admin-nav-group-label">Bài hát</div>

            <a href="{{ route('admin.songs.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.songs.*') ? 'active' : '' }}">
                <i class="fa-solid fa-music"></i>
                <span>Quản lý bài hát</span>
            </a>

            <a href="{{ route('admin.genres.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.genres.*') ? 'active' : '' }}">
                <i class="fa-solid fa-layer-group"></i>
                <span>Thể loại nhạc</span>
            </a>

            <a href="{{ route('admin.banners.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                <i class="fa-solid fa-panorama"></i>
                <span>Banner / Quảng cáo</span>
            </a>

            {{-- ─── Gói đăng kí ─── --}}
            <div class="admin-nav-group-label">Gói đăng kí</div>

            <a href="{{ route('admin.vips.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.vips.*') ? 'active' : '' }}">
                <i class="fa-solid fa-crown"></i>
                <span>Gói VIP</span>
            </a>

            <a href="{{ route('admin.subscriptions.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
                <i class="fa-solid fa-receipt"></i>
                <span>Lịch sử đăng kí</span>
            </a>

            {{-- ─── Thống kê ─── --}}
            <div class="admin-nav-group-label">Báo cáo</div>

            <a href="{{ route('admin.reports.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line"></i>
                <span>Thống kê & Báo cáo</span>
            </a>

        </nav>

        {{-- ─── Sidebar footer: logout ─── --}}
        <div class="admin-sidebar-footer">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="admin-logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Đăng xuất</span>
                </button>
            </form>
        </div>

    </div>
</aside>
