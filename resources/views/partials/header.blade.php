<header class="app-header">
    <div class="header-content">

        <div class="d-flex align-items-center gap-3">
            {{-- Mobile sidebar toggle --}}
            <button class="btn mm-icon-btn d-inline-flex d-lg-none"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#sidebarOffcanvas"
                    aria-controls="sidebarOffcanvas"
                    title="Menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <nav class="breadcrumb-nav">
                <a href="{{ url('/') }}" class="breadcrumb-link">Home</a>
                @if(isset($breadcrumbs))
                    @foreach($breadcrumbs as $crumb)
                        <i class="fa-solid fa-chevron-right breadcrumb-sep"></i>
                        @if($loop->last)
                            <span class="breadcrumb-current">{{ $crumb['label'] }}</span>
                        @else
                            <a href="{{ $crumb['url'] }}" class="breadcrumb-link">{{ $crumb['label'] }}</a>
                        @endif
                    @endforeach
                @else
                    <i class="fa-solid fa-chevron-right breadcrumb-sep"></i>
                    <span class="breadcrumb-current">Albums</span>
                @endif
            </nav>
        </div>

        <div class="header-actions">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" class="search-input" placeholder="What do you want to play?" id="searchInput">
                <button class="search-clear" id="searchClear" aria-label="Clear search">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="header-icons">
                <button class="btn mm-icon-btn d-none d-md-inline-flex" title="Settings">
                    <i class="fa-solid fa-gear"></i>
                </button>

                <button class="btn mm-icon-btn position-relative" title="Notifications" id="notificationBtn">
                    <i class="fa-solid fa-bell"></i>
                    <span class="notification-dot"></span>
                </button>

                @auth
                    <div class="dropdown">
                        <button class="btn mm-user-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @php
                                $initial = strtoupper(substr(auth()->user()->name, 0, 1));
                                $avatarSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%23e11d48'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='18' fill='%23ffffff' font-weight='bold'%3E{$initial}%3C/text%3E%3C/svg%3E";
                            @endphp
                            <img class="user-avatar" src="{{ auth()->user()->avatar ?? $avatarSvg }}" alt="{{ auth()->user()->name }}">
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end mm-dropdown">
                            <li class="px-3 py-2 d-flex align-items-center gap-2">
                                <img class="dropdown-avatar" src="{{ auth()->user()->avatar ?? $avatarSvg }}" alt="{{ auth()->user()->name }}">
                                <div class="min-w-0">
                                    <div class="fw-semibold text-white text-truncate">{{ auth()->user()->name }}</div>
                                    <div class="small text-muted text-truncate">{{ auth()->user()->email }}</div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ url('/profile') }}"><i class="fa-solid fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="{{ url('/settings') }}"><i class="fa-solid fa-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fa-solid fa-right-from-bracket me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn mm-btn mm-btn-light">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Login
                    </a>
                @endauth
            </div>
        </div>

    </div>
</header>
