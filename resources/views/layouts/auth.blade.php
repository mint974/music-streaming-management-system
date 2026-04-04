<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title') | Blue Wave Music</title>

    {{-- DNS Prefetch for faster CDN connection --}}
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">

    {{-- Preconnect to CDN for faster loading --}}
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome CSS --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body>

    @yield('content')

    {{-- Bootstrap 5 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check for forms with needs-validation class
            const forms = document.querySelectorAll('.needs-validation');
            
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    // Check if form is valid (if using Bootstrap validation)
                    if (form.checkValidity()) {
                        const submitBtn = form.querySelector('button[type="submit"]');
                        if (submitBtn && !submitBtn.disabled) {
                            // Small delay to ensure the event is captured but not blocked
                            setTimeout(() => {
                                submitBtn.disabled = true;
                                
                                // Save original content
                                const originalContent = submitBtn.innerHTML;
                                
                                // Get loading text or default
                                const loadingText = submitBtn.dataset.loadingText || 'PROCESSING...';
                                
                                // Set spinner and text
                                submitBtn.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin me-2"></i> ${loadingText}`;
                            }, 50);
                        }
                    }
                }, false);
            });
        });
    </script>
</body>
</html>
