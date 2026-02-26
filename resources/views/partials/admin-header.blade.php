<header class="admin-header">
    <div class="admin-header-content">

        {{-- Left: mobile toggle + page title --}}
        <div class="d-flex align-items-center gap-3">
            <button class="btn mm-icon-btn d-inline-flex d-lg-none"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#adminSidebarOffcanvas"
                    aria-controls="adminSidebarOffcanvas"
                    title="Menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="admin-page-title">
                <h5 class="mb-0 fw-semibold text-white">@yield('page-title', 'Admin Dashboard')</h5>
                <small class="text-muted">@yield('page-subtitle', 'Blue Wave Music')</small>
            </div>
        </div>

        {{-- Right: user dropdown --}}
        <div class="d-flex align-items-center gap-3">
            @php
                $adminUser  = auth('admin')->user();
                $initial    = $adminUser ? strtoupper(substr($adminUser->name, 0, 1)) : 'A';
                $avatarSvg  = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%236366f1'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='18' fill='%23ffffff' font-weight='bold'%3E{$initial}%3C/text%3E%3C/svg%3E";
                $avatar     = ($adminUser && $adminUser->avatar && $adminUser->avatar !== '/storage/avt.jpg')
                                ? asset($adminUser->avatar)
                                : $avatarSvg;
            @endphp

            <div class="dropdown">
                <button class="btn admin-user-btn dropdown-toggle"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                    <img class="admin-user-avatar" src="{{ $avatar }}" alt="{{ $adminUser?->name }}">
                    <span class="d-none d-md-inline ms-2 text-white fw-semibold">
                        {{ $adminUser?->name ?? 'Admin' }}
                    </span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end mm-dropdown">
                    {{-- User info header --}}
                    <li class="px-3 py-2 d-flex align-items-center gap-2">
                        <img class="dropdown-avatar" src="{{ $avatar }}" alt="{{ $adminUser?->name }}">
                        <div class="min-w-0">
                            <div class="fw-semibold text-white text-truncate">{{ $adminUser?->name }}</div>
                            <div class="small text-muted text-truncate">{{ $adminUser?->email }}</div>
                            <span class="badge mt-1" style="background:rgba(99,102,241,.25);color:#a5b4fc;font-size:.65rem;border:1px solid rgba(99,102,241,.35);">
                                <i class="fa-solid fa-shield-halved me-1"></i>Admin
                            </span>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.profile.edit') }}">
                            <i class="fa-solid fa-user-gear me-2"></i>Thông tin cá nhân
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</header>
