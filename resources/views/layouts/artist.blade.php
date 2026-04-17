<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Studio Nghệ sĩ') | Blue Wave Music</title>

    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="artist-layout">

    {{-- ─── Sidebar desktop ─── --}}
    @include('partials.artist-sidebar')

    {{-- ─── Sidebar mobile offcanvas ─── --}}
    <div class="offcanvas offcanvas-start artist-offcanvas"
         tabindex="-1"
         id="artistSidebarOffcanvas"
         aria-labelledby="artistSidebarOffcanvasLabel">
        <div class="offcanvas-header border-bottom border-secondary border-opacity-25">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-microphone-lines" style="color:#a855f7"></i>
                <strong id="artistSidebarOffcanvasLabel" class="text-white">Artist Studio</strong>
            </div>
            <button type="button" class="btn-close btn-close-white"
                    data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            @include('partials.artist-sidebar', ['isOffcanvas' => true])
        </div>
    </div>

    {{-- ─── Main content area (no player) ─── --}}
    <div class="artist-content-area">
        @include('partials.artist-header')

        <main class="artist-main">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>{!! session('success') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>{!! session('error') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('modals')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    @stack('scripts')
</body>
</html>
