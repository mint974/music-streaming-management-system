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

                @auth
                @php
                    $unreadCount   = auth()->user()->unreadNotifications()->count();
                    $recentNotifs  = auth()->user()->notifications()->latest()->take(5)->get();
                @endphp
                <div class="dropdown">
                    <button class="btn mm-icon-btn position-relative"
                            title="Thông báo"
                            data-bs-toggle="dropdown"
                            data-bs-auto-close="outside"
                            aria-expanded="false"
                            id="notificationBtn">
                        <i class="fa-solid fa-bell"></i>
                        @if($unreadCount > 0)
                            <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill"
                                  style="background:#ef4444;font-size:.6rem;min-width:16px;height:16px;display:flex;align-items:center;justify-content:center;padding:0 3px">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end mm-dropdown p-0"
                        style="width:340px;max-height:480px;overflow:hidden" aria-labelledby="notificationBtn">

                        {{-- Header --}}
                        <li class="px-3 py-2 d-flex align-items-center justify-content-between border-bottom border-secondary border-opacity-25">
                            <span class="fw-semibold text-white" style="font-size:.9rem">
                                <i class="fa-solid fa-bell me-1" style="color:#818cf8"></i>Thông báo
                            </span>
                            @if($unreadCount > 0)
                            <form method="POST" action="{{ route('notifications.markAllRead') }}">
                                @csrf
                                <button type="submit" class="btn btn-link p-0 text-muted" style="font-size:.75rem;text-decoration:none">
                                    <i class="fa-solid fa-check-double me-1"></i>Đọc tất cả
                                </button>
                            </form>
                            @endif
                        </li>

                        {{-- Recent notifications --}}
                        <div style="max-height:340px;overflow-y:auto">
                        @forelse($recentNotifs as $notif)
                        @php
                            $nd     = $notif->data;
                            $nRead  = $notif->read_at !== null;
                            $nColor = $nd['color'] ?? '#818cf8';
                            $nIcon  = $nd['icon']  ?? 'fa-bell';
                        @endphp
                        <li>
                            <a href="{{ route('notifications.read', $notif->id) }}"
                               class="dropdown-item py-2 px-3 {{ $nRead ? '' : 'notif-unread' }}"
                               style="{{ !$nRead ? 'background:rgba(99,102,241,.06)' : '' }}">
                                <div class="d-flex gap-2 align-items-start">
                                    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center mt-1"
                                         style="width:32px;height:32px;background:{{ $nColor }}20;border:1px solid {{ $nColor }}35">
                                        <i class="fa-solid {{ $nIcon }}" style="color:{{ $nColor }};font-size:.75rem"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex align-items-center justify-content-between gap-1">
                                            <span class="fw-semibold text-white text-truncate" style="font-size:.8rem">{{ $nd['title'] ?? 'Thông báo' }}</span>
                                            @if(!$nRead)
                                                <span style="width:7px;height:7px;background:#818cf8;border-radius:50%;flex-shrink:0;display:inline-block"></span>
                                            @endif
                                        </div>
                                        <div class="text-muted text-truncate" style="font-size:.75rem">{{ $nd['message'] ?? '' }}</div>
                                        <div class="text-muted" style="font-size:.7rem">{{ $notif->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        @empty
                        <li class="text-center text-muted py-4" style="font-size:.85rem">
                            <i class="fa-solid fa-bell-slash mb-2 d-block opacity-25"></i>
                            Chưa có thông báo nào
                        </li>
                        @endforelse
                        </div>

                        {{-- Footer --}}
                        <li class="border-top border-secondary border-opacity-25">
                            <a href="{{ route('notifications.index') }}"
                               class="dropdown-item text-center py-2"
                               style="color:#818cf8;font-size:.8rem">
                                Xem tất cả thông báo
                            </a>
                        </li>
                    </ul>
                </div>
                @endauth

                @auth
                    <div class="dropdown">
                        @php
                            $initial   = strtoupper(substr(auth()->user()->name, 0, 1));
                            $avatarSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%23e11d48'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='18' fill='%23ffffff' font-weight='bold'%3E{$initial}%3C/text%3E%3C/svg%3E";
                        @endphp
                        <button class="btn mm-user-btn {{ auth()->user()->isPremium() ? 'avatar-ring-premium' : '' }} dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fa-solid fa-user me-2"></i>Profile</a></li>
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
