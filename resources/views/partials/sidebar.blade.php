@php($offcanvas = $isOffcanvas ?? false)

<aside class="app-sidebar {{ $offcanvas ? 'is-offcanvas' : '' }}">
    <div class="sidebar-inner">

        {{-- ── Logo ──────────────────────────────────── --}}
        <div class="sidebar-header">
            <div class="logo">
                <i class="fa-solid fa-music logo-icon"></i>
                <span class="logo-text">Blue Wave Music</span>
            </div>
        </div>

        {{-- ── Navigation ─────────────────────────────── --}}
        <nav class="sidebar-nav">
            <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
                <i class="fa-solid fa-house"></i><span>Home</span>
            </a>
            <a href="{{ url('/search') }}" class="nav-link {{ request()->is('search*') ? 'active' : '' }}">
                <i class="fa-solid fa-magnifying-glass"></i><span>Search</span>
            </a>
            <a href="{{ url('/songs') }}" class="nav-link {{ request()->is('songs*') ? 'active' : '' }}">
                <i class="fa-solid fa-music"></i><span>Songs</span>
            </a>
            <a href="{{ url('/albums') }}" class="nav-link {{ request()->is('albums*') ? 'active' : '' }}">
                <i class="fa-solid fa-compact-disc"></i><span>Albums</span>
            </a>
            <a href="{{ route('listener.playlists.index') }}"
                class="nav-link {{ request()->routeIs('listener.playlists.*') ? 'active' : '' }}">
                <i class="fa-solid fa-list-music"></i>
                <span>Playlists</span>
                <i class="fa-solid fa-crown text-warning ms-2 shadow-sm" style="font-size: 0.75rem;"
                    title="Premium Feature"></i>
            </a>
            <a href="{{ url('/library') }}" class="nav-link {{ request()->is('library*') ? 'active' : '' }}">
                <i class="fa-solid fa-book"></i><span>Your Library</span>
                <i class="fa-solid fa-plus ms-auto small opacity-75"></i>
            </a>
        </nav>

        {{-- ── Premium section (plan-aware) ───────────── --}}
        @auth
            @php($sUser = auth()->user())
            @if ($sUser->isPremium())
                {{-- Premium active card --}}
                <a href="{{ route('subscription.index') }}"
                    class="sidebar-premium-card premium-card {{ request()->is('subscription*') ? 'active' : '' }}">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fa-solid fa-crown" style="color:#fbbf24;font-size:.85rem"></i>
                        <span style="color:#fbbf24;font-weight:700;font-size:.8rem">Blue Wave Premium</span>
                    </div>
                    <div style="color:#94a3b8;font-size:.7rem;line-height:1.5">
                        Không quảng cáo &middot; Tải offline &middot; Chất lượng cao
                    </div>
                    <div style="margin-top:8px;display:flex;align-items:center;gap:5px;font-size:.67rem;color:#34d399">
                        <i class="fa-solid fa-circle-check"></i> Đang kích hoạt &mdash; Quản lý gói
                    </div>
                </a>
            @else
                {{-- Upgrade CTA card --}}
                <a href="{{ route('subscription.index') }}"
                    class="sidebar-premium-card free-card {{ request()->is('subscription*') ? 'active' : '' }}">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="sidebar-plan-crown-icon">
                            <i class="fa-solid fa-crown" style="color:#fbbf24;font-size:.7rem"></i>
                        </div>
                        <span style="color:#e2e8f0;font-weight:700;font-size:.8rem">Nâng cấp Premium</span>
                    </div>
                    <div style="color:#94a3b8;font-size:.7rem;line-height:1.5;margin-bottom:10px">
                        Nghe không giới hạn, không quảng cáo,
                        tải nhạc offline.
                    </div>
                    <div class="sidebar-upgrade-btn">
                        <i class="fa-solid fa-bolt" style="font-size:.65rem"></i>
                        Xem các gói ngay
                    </div>
                </a>
            @endif
        @endauth

        {{-- ── Artist section (plan-aware) ────────────── --}}
        @auth
            @php($sUser = auth()->user())
            @if ($sUser->isArtist())
                {{-- Artist active card --}}
                <a href="{{ route('artist.dashboard') }}"
                    class="sidebar-premium-card artist-card {{ request()->is('artist-register*') ? 'active' : '' }}"
                    hx-boost="false"
                    style="background:linear-gradient(135deg,rgba(168,85,247,.12),rgba(236,72,153,.08));border-color:rgba(168,85,247,.25)">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fa-solid fa-microphone-lines" style="color:#a855f7;font-size:.85rem"></i>
                        <span style="color:#a855f7;font-weight:700;font-size:.8rem">Tài khoản Nghệ sĩ</span>
                        @if ($sUser->artist_verified_at)
                            <i class="fa-solid fa-circle-check ms-auto" style="color:#60a5fa;font-size:.8rem"
                                title="Đã xác minh"></i>
                        @endif
                    </div>
                    <div style="color:#94a3b8;font-size:.7rem;line-height:1.5">
                        Đăng nhạc &middot; Quản lý bài hát &middot; Thống kê
                    </div>
                    <div style="margin-top:8px;display:flex;align-items:center;gap:5px;font-size:.67rem;color:#34d399">
                        <i class="fa-solid fa-circle-check"></i>
                        @if ($sUser->artist_verified_at)
                            Đã xác minh &mdash; Quản lý
                        @else
                            Đang hoạt động &mdash; Studio
                        @endif
                    </div>
                </a>
            @elseif($sUser->hasPendingArtistRegistration())
                {{-- Pending artist card --}}
                <a href="{{ route('artist-register.index') }}"
                    class="sidebar-premium-card {{ request()->is('artist-register*') ? 'active' : '' }}"
                    style="background:rgba(251,191,36,.07);border-color:rgba(251,191,36,.25)">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fa-solid fa-clock" style="color:#fbbf24;font-size:.85rem"></i>
                        <span style="color:#fbbf24;font-weight:700;font-size:.8rem">Đăng ký Nghệ sĩ</span>
                    </div>
                    <div style="color:#94a3b8;font-size:.7rem;line-height:1.5">
                        Đơn đăng ký đang được xem xét.
                    </div>
                    <div style="margin-top:8px;display:flex;align-items:center;gap:5px;font-size:.67rem;color:#fbbf24">
                        <i class="fa-solid fa-hourglass-half"></i> Đang chờ xét duyệt
                    </div>
                </a>
            @elseif(! $sUser->isAdmin())
                {{-- Artist upgrade CTA card --}}
                <a href="{{ route('artist-register.index') }}"
                    class="sidebar-premium-card {{ request()->is('artist-register*') ? 'active' : '' }}"
                    style="background:rgba(168,85,247,.07);border-color:rgba(168,85,247,.2)">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="sidebar-plan-crown-icon" style="background:rgba(168,85,247,.15)">
                            <i class="fa-solid fa-microphone-lines" style="color:#a855f7;font-size:.7rem"></i>
                        </div>
                        <span style="color:#e2e8f0;font-weight:700;font-size:.8rem">Trở thành Nghệ sĩ</span>
                    </div>
                    <div style="color:#94a3b8;font-size:.7rem;line-height:1.5;margin-bottom:10px">
                        Đăng tải nhạc, tiếp cận hàng triệu người nghe.
                    </div>
                    <div class="sidebar-upgrade-btn"
                        style="background:rgba(168,85,247,.2);color:#c084fc;border-color:rgba(168,85,247,.3)">
                        <i class="fa-solid fa-bolt" style="font-size:.65rem"></i>
                        Đăng ký ngay
                    </div>
                </a>
            @endif
        @endauth

        {{-- ── Playlists ───────────────────────────────── --}}
        <div class="sidebar-playlists">
            <div class="playlist-header">
                <h6 class="playlist-heading m-0 text-white">Playlists <i class="fa-solid fa-crown text-warning ms-1"
                        style="font-size: 0.7rem;"></i></h6>
                <a href="{{ route('listener.playlists.index') }}" class="btn mm-icon-btn mm-icon-btn-sm"
                    title="Quản lý playlist">
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            @auth
                @foreach (auth()->user()->playlists->take(3) as $pl)
                    <div class="playlist-card {{ request()->url() == route('listener.playlists.show', $pl) ? 'active' : '' }}"
                        onclick="window.location.href='{{ route('listener.playlists.show', $pl) }}'"
                        style="cursor: pointer; transition: background 0.2s;"
                        onmouseover="this.style.backgroundColor='rgba(255,255,255,0.05)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                        <div>
                            <div class="playlist-title text-white fw-bold">{{ $pl->name }}</div>
                            <div class="playlist-sub" style="font-size: 0.75rem;">Bởi {{ auth()->user()->name }} •
                                {{ $pl->songs()->count() }} bài hát</div>
                        </div>
                    </div>
                @endforeach
            @endauth

            <a href="{{ route('listener.playlists.index') }}" style="text-decoration: none;">
                <div class="playlist-card create" style="cursor: pointer; transition: background 0.2s;"
                    onmouseover="this.style.backgroundColor='rgba(255,255,255,0.05)'"
                    onmouseout="this.style.backgroundColor='transparent'">
                    <div class="create-icon d-flex align-items-center justify-content-center"><i
                            class="fa-solid fa-plus"></i></div>
                    <div>
                        <div class="playlist-title text-white fw-bold">Tạo playlist mới</div>
                        <div class="playlist-sub" style="font-size: 0.75rem;">Cá nhân hóa thư viện</div>
                    </div>
                </div>
            </a>
        </div>


    </div>
</aside>
