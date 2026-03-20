<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Blue Wave Music')</title>

    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://unpkg.com/htmx.org@2.0.4" crossorigin="anonymous"></script>

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="app-layout" hx-boost="true">
    {{-- Sidebar desktop --}}
    @include('partials.sidebar')

    {{-- Sidebar mobile offcanvas --}}
    <div class="offcanvas offcanvas-start app-offcanvas" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-header">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-music text-danger"></i>
                <strong id="sidebarOffcanvasLabel" class="text-white">Blue Wave Music</strong>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            {{-- reuse same sidebar content --}}
            @include('partials.sidebar', ['isOffcanvas' => true])
        </div>
    </div>

    <div class="content-area">
        @include('partials.header')

        <main class="app-content">
            @yield('content')
        </main>
    </div>

    <div hx-preserve id="persistent-player">
        @include('partials.player')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <script>
    // Global function to add song to user's personalized playlists via Dropdowns
    async function addSongToPlaylist(playlistId, songId) {
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const response = await fetch(`/listener/playlists/${playlistId}/songs`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ song_id: songId })
            });
            const data = await response.json();
            if (response.ok && data.success) {
                alert('✓ ' + (data.message || 'Đã thêm thành công'));
            } else {
                alert('⚠ ' + (data.message || 'Lỗi: Tác vụ thất bại'));
            }
        } catch (e) {
            alert('⚠ Lỗi kết nối máy chủ');
        }
    }
    </script>
    @stack('scripts')
</body>
</html>
