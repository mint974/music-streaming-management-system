<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Blue Wave Music</title>
    {{-- DNS Prefetch for faster CDN connection --}}
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">

    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Animate.css --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    @vite(['resources/scss/app.scss'])
</head>
<body class="error-page-404">
    <div class="error-container">
        <!-- Floating Stars Background via CSS -->
        <div class="stars-layer-1"></div>
        <div class="stars-layer-2"></div>
        <div class="stars-layer-3"></div>
        
        <div class="content-wrapper animate__animated animate__fadeIn">
            <div class="row align-items-center justify-content-center">
                <!-- Left side: Pure CSS Animated Vinyl Record -->
                <div class="col-lg-6 text-center mb-5 mb-lg-0">
                    <div class="css-vinyl-wrapper">
                        <!-- Holographic energy rings around the vinyl -->
                        <div class="energy-ring energy-ring-1"></div>
                        <div class="energy-ring energy-ring-2"></div>
                        
                        <div class="css-vinyl-record">
                            <div class="vinyl-groove groove-1"></div>
                            <div class="vinyl-groove groove-2"></div>
                            <div class="vinyl-groove groove-3"></div>
                            <div class="vinyl-label">
                                <div class="vinyl-hole"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right side: Content -->
                <div class="col-lg-6 text-center text-lg-start error-text-column">
                    <h1 class="error-code">
                        <span class="digit blue">4</span>
                        <span class="digit purple">0</span>
                        <span class="digit blue">4</span>
                    </h1>
                    <h2 class="error-title">BÀI HÁT BẠN ĐANG TÌM<br>ĐÃ BAY VÀO VŨ TRỤ...</h2>
                    <p class="error-description">
                        Trang web nghe nhạc của chúng tôi không thể kết nối đến thiên hà này. Đừng lo, hãy quay lại Trái Đất để khám phá thêm nhiều âm nhạc mới nhé!
                    </p>
                    
                    <a href="{{ route('home') }}" class="btn-return-earth mt-3">
                        QUAY LẠI TRÁI ĐẤT
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
