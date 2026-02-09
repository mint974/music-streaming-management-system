<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Blue Wave Music')</title>

    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="app-layout">
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

    @include('partials.player')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    @stack('scripts')
</body>
</html>
