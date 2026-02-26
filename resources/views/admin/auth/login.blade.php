@extends('layouts.auth')

@section('title', 'Admin Login')

@push('styles')
<style>
    /* Admin badge — styled inline to avoid touching shared SCSS */
    .admin-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .28rem .75rem;
        border-radius: 50px;
        background: rgba(99, 102, 241, .18);
        border: 1px solid rgba(99, 102, 241, .4);
        color: #a5b4fc;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
<div class="auth-container">
    {{-- Background sparkles --}}
    <x-sparkles :count="18" />

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-4">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                <div class="auth-card">

                    {{-- Logo / Title --}}
                    <div class="text-center mb-4">
                        <img src="{{ asset('storage/logo.png') }}"
                             alt="Blue Wave Music Logo"
                             class="auth-logo mx-auto d-block mb-3">
                        <h1 class="auth-title">Blue Wave Music</h1>
                        {{-- Admin indicator badge --}}
                        <div class="d-flex justify-content-center mt-2">
                            <span class="admin-badge">
                                <i class="fa-solid fa-shield-halved"></i>
                                Admin Panel
                            </span>
                        </div>
                    </div>

                    {{-- Flash success (e.g. after logout) --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-3 py-2 small" role="alert">
                            <i class="fa-solid fa-circle-check me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Form --}}
                    <form method="POST"
                          action="{{ route('admin.login') }}"
                          class="needs-validation"
                          novalidate>
                        @csrf

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email"
                                   class="form-label text-uppercase small fw-semibold text-muted">
                                Email
                            </label>
                            <input
                                type="email"
                                class="form-control auth-input @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="admin@example.com"
                                required
                                autocomplete="email"
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label for="password"
                                   class="form-label text-uppercase small fw-semibold text-muted">
                                Mật khẩu
                            </label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    class="form-control auth-input @error('password') is-invalid @enderror"
                                    id="password"
                                    name="password"
                                    placeholder="••••••••"
                                    required
                                    autocomplete="current-password"
                                    style="border-right: none;"
                                >
                                <button type="button"
                                        class="btn input-group-text"
                                        onclick="toggleAdminPassword()"
                                        id="toggleAdminPwd"
                                        tabindex="-1"
                                        style="
                                            background: rgba(0,0,0,.7);
                                            border: 1px solid rgba(255,255,255,.15);
                                            border-left: none;
                                            border-radius: 0 12px 12px 0;
                                            color: rgba(255,255,255,.5);
                                            transition: color .2s;
                                        ">
                                    <i class="fa-regular fa-eye" id="toggleAdminPwdIcon"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Remember Me --}}
                        <div class="form-check mb-3">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="remember"
                                   name="remember">
                            <label class="form-check-label small text-muted" for="remember">
                                Ghi nhớ đăng nhập
                            </label>
                        </div>

                        {{-- Submit --}}
                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-auth">
                                <i class="fa-solid fa-right-to-bracket me-2"></i>
                                ĐĂNG NHẬP QUẢN TRỊ
                            </button>
                        </div>
                    </form>

                    {{-- Back to user site link --}}
                    <div class="text-center mt-4 pt-3 border-top">
                        <a href="{{ route('login') }}" class="auth-footer-link">
                            <i class="fa-solid fa-arrow-left me-1"></i>
                            Về trang người dùng
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleAdminPassword() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('toggleAdminPwdIcon');
    const btn   = document.getElementById('toggleAdminPwd');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
        btn.style.color = 'rgba(255,255,255,.8)';
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
        btn.style.color = 'rgba(255,255,255,.5)';
    }
}
</script>
@endpush
