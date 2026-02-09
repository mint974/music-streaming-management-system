@php($offcanvas = $isOffcanvas ?? false)

<aside class="app-sidebar {{ $offcanvas ? 'is-offcanvas' : '' }}">
    <div class="sidebar-inner">

        <div class="sidebar-header">
            <div class="logo">
                <i class="fa-solid fa-music logo-icon"></i>
                <span class="logo-text">Blue Wave Music</span>
            </div>
        </div>

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
