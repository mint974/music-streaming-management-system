<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Blue Wave Music</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div style="min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem;">
        <div style="max-width: 800px; width: 100%; background: rgba(30, 39, 54, 0.85); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 24px; padding: 3rem;">
            <h1 style="color: #E5E7EB; font-size: 3rem; margin-bottom: 1rem; text-align: center;">
                🎵 Welcome to Blue Wave Music
            </h1>
            
            @if(session('success'))
                <div style="background: rgba(124, 58, 237, 0.2); border: 1px solid rgba(124, 58, 237, 0.4); border-radius: 12px; padding: 1rem; margin-bottom: 2rem; color: #E5E7EB; text-align: center;">
                    {{ session('success') }}
                </div>
            @endif
            
            <div style="text-align: center; margin: 2rem 0;">
                <p style="color: #94A3B8; font-size: 1.2rem; margin-bottom: 2rem;">
                    Hello, <strong style="color: #3B82F6;">{{ Auth::user()->name }}</strong>!
                </p>
                
                <p style="color: #94A3B8; margin-bottom: 2rem;">
                    Email: {{ Auth::user()->email }}
                </p>
                
                <p style="color: #94A3B8; font-size: 0.9rem; font-style: italic; margin-bottom: 3rem;">
                    Your musical journey starts here...
                </p>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-login" style="margin: 0 auto;">
                        LOGOUT
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>
            
            <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid rgba(255, 255, 255, 0.08);">
                <p style="color: #94A3B8; font-size: 0.85rem; text-align: center;">
                    Member since {{ Auth::user()->created_at->format('F d, Y') }}
                </p>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</body>
</html>
