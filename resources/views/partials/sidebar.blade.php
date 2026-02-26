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
            <a href="{{ url('/playlists') }}" class="nav-link {{ request()->is('playlists*') ? 'active' : '' }}">
                <i class="fa-solid fa-list-music"></i><span>Playlists</span>
            </a>
            <a href="{{ url('/library') }}" class="nav-link {{ request()->is('library*') ? 'active' : '' }}">
                <i class="fa-solid fa-book"></i><span>Your Library</span>
                <i class="fa-solid fa-plus ms-auto small opacity-75"></i>
            </a>
        </nav>

        {{-- ── Premium section (plan-aware) ───────────── --}}
        @auth
        @php($sUser = auth()->user())
        @if($sUser->isPremium())
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

        {{-- ── Playlists ───────────────────────────────── --}}
        <div class="sidebar-playlists">
            <div class="playlist-header">
                <h6 class="playlist-heading m-0">Playlists</h6>
                <button class="btn mm-icon-btn mm-icon-btn-sm" title="Create playlist">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>

            <div class="playlist-card active">
                <div>
                    <div class="playlist-title">Midnight Reverie</div>
                    <div class="playlist-sub">By Dave • 10 songs</div>
                </div>
                <button class="btn mm-icon-btn mm-icon-btn-sm" title="Options">
                    <i class="fa-solid fa-ellipsis"></i>
                </button>
            </div>

            <div class="playlist-card create">
                <div class="create-icon"><i class="fa-solid fa-plus"></i></div>
                <div>
                    <div class="playlist-title">Create your first playlist</div>
                    <div class="playlist-sub">It's easy, we'll help you</div>
                </div>
            </div>
        </div>

        <div class="sidebar-footer">
            <button class="btn mm-btn mm-btn-ghost w-100">
                <i class="fa-solid fa-globe me-2"></i> English
            </button>
        </div>

    </div>
</aside>
