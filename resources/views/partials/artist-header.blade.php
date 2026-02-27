<header class="artist-header">
    <div class="artist-header-content">

        {{-- Left: mobile toggle + page title --}}
        <div class="d-flex align-items-center gap-3">
            <button class="btn mm-icon-btn d-inline-flex d-lg-none"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#artistSidebarOffcanvas"
                    aria-controls="artistSidebarOffcanvas"
                    title="Menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="artist-page-title">
                <h5 class="mb-0 fw-semibold text-white">@yield('page-title', 'Artist Studio')</h5>
                <small class="text-muted">@yield('page-subtitle', 'Blue Wave Music')</small>
            </div>
        </div>

        {{-- Right: notifications + user dropdown --}}
        <div class="d-flex align-items-center gap-3">
            @php
                $artistUser = auth()->user();
                $unreadCount = $artistUser ? $artistUser->unreadNotifications()->count() : 0;
                $initial    = $artistUser ? strtoupper(substr($artistUser->name, 0, 1)) : 'A';
                $avatarSvg  = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%23a855f7'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='18' fill='%23ffffff' font-weight='bold'%3E{$initial}%3C/text%3E%3C/svg%3E";
                $avatar     = ($artistUser && $artistUser->avatar && $artistUser->avatar !== '/storage/avt.jpg')
                                ? asset($artistUser->avatar)
                                : $avatarSvg;
            @endphp

            {{-- Notification bell --}}
            <a href="{{ route('notifications.index') }}"
               class="btn mm-icon-btn position-relative"
               title="Thông báo">
                <i class="fa-solid fa-bell"></i>
                @if($unreadCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill"
                          style="background:#a855f7;font-size:.55rem;padding:2px 5px;min-width:16px">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                @endif
            </a>

            {{-- User dropdown --}}
            <div class="dropdown">
                <button class="btn artist-user-btn dropdown-toggle"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                    <img class="artist-user-avatar" src="{{ $avatar }}" alt="{{ $artistUser?->name }}">
                    <span class="d-none d-md-inline ms-2 text-white fw-semibold">
                        {{ $artistUser?->name ?? 'Artist' }}
                    </span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end mm-dropdown">
                    {{-- User info header --}}
                    <li class="px-3 py-2 d-flex align-items-center gap-2">
                        <img class="dropdown-avatar" src="{{ $avatar }}" alt="{{ $artistUser?->name }}">
                        <div class="min-w-0">
                            <div class="fw-semibold text-white text-truncate">{{ $artistUser?->name }}</div>
                            <div class="small text-muted text-truncate">{{ $artistUser?->email }}</div>
                            <span class="badge mt-1" style="background:rgba(168,85,247,.25);color:#c084fc;font-size:.65rem;border:1px solid rgba(168,85,247,.35);">
                                <i class="fa-solid fa-microphone-lines me-1"></i>Nghệ sĩ
                                @if($artistUser?->artist_verified_at)
                                    &nbsp;<i class="fa-solid fa-circle-check" style="color:#60a5fa"></i>
                                @endif
                            </span>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('artist.profile.edit') }}">
                            <i class="fa-solid fa-user-pen me-2"></i>Hồ sơ nghệ sĩ
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fa-solid fa-user-gear me-2"></i>Tài khoản cá nhân
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('home') }}">
                            <i class="fa-solid fa-arrow-left me-2"></i>Về trang người dùng
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
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
