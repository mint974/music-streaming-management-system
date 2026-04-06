@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="auth-container">
    {{-- Random sparkles --}}
    <x-sparkles :count="20" />

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-4">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                <div class="auth-card">
                    {{-- Logo / Title --}}
                    <div class="text-center mb-4">
                        <img src="{{ asset('storage/logo.png') }}" alt="Blue Wave Music Logo" class="auth-logo mx-auto d-block mb-3">
                        <h1 class="auth-title">Blue Wave Music</h1>
                    </div>

                    {{-- Form --}}
                    @if(session('success'))
                        <div class="alert border-0 mb-4 py-3 px-4" role="alert"
                             style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3)!important;border-radius:12px">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-circle-check mt-1" style="color:#34d399"></i>
                                <span style="color:#a7f3d0;font-size:.85rem">{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate hx-boost="false">
                        @csrf

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label text-uppercase small fw-semibold text-muted">Email</label>
                            <input 
                                type="email" 
                                class="form-control auth-input @error('email') is-invalid @enderror" 
                                id="email" 
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="hello@example.com"
                                required 
                                autocomplete="email"
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label for="password" class="form-label text-uppercase small fw-semibold text-muted">Password</label>
                            <div class="position-relative">
                                <input 
                                    type="password" 
                                    class="form-control auth-input @error('password') is-invalid @enderror" 
                                    id="password" 
                                    name="password"
                                    placeholder="••••••••"
                                    required 
                                    autocomplete="current-password"
                                    style="padding-right:2.8rem"
                                >
                                <button type="button"
                                    class="position-absolute top-50 end-0 translate-middle-y border-0 bg-transparent me-2 p-1"
                                    style="z-index:10;color:#94a3b8;outline:none"
                                    onclick="var i=document.getElementById('password');i.type=i.type==='password'?'text':'password';this.querySelector('i').classList.toggle('fa-eye');this.querySelector('i').classList.toggle('fa-eye-slash')">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Remember Me --}}
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label small text-muted" for="remember">
                                Remember me
                            </label>
                        </div>

                        {{-- Submit Buttons --}}
                        <div class="d-grid gap-3 mb-3">
                            <button type="submit" class="btn btn-auth" data-loading-text="ĐANG ĐĂNG NHẬP...">
                                LOG IN
                                <i class="fa-solid fa-arrow-right ms-2"></i>
                            </button>
                            
                            <div class="position-relative my-2 text-center">
                                <hr class="border-secondary opacity-25">
                                <span class="position-absolute top-50 start-50 translate-middle px-2 text-uppercase small fw-semibold" style="background: rgba(30, 39, 54, 0.95); color: var(--text-primary);">Or</span>
                            </div>

                            <a href="{{ route('register') }}" class="btn-register-outline">
                                REGISTER NEW ACCOUNT
                                <i class="fa-solid fa-user-plus ms-2"></i>
                            </a>
                        </div>

                        {{-- Forgot Password Link --}}
                        <div class="text-center mt-4">
                            <a href="{{ route('password.request') }}" class="text-muted text-decoration-none small text-uppercase fw-semibold">
                                Forgot your password?
                            </a>
                        </div>
                    </form>

                    {{-- Unlock request link — only shown after a locked-account error --}}
                    @if(session('show_unlock_link'))
                    <div class="mt-3 p-3 rounded-3 text-center"
                         style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25)">
                        <p class="text-muted small mb-2">
                            <i class="fa-solid fa-lock me-1 text-danger"></i>
                            Tài khoản bị khóa? Gửi yêu cầu khiếu nại đến quản trị viên.
                        </p>
                        <a href="{{ route('unlock-request.create') }}?email={{ urlencode(session('locked_email','')) }}"
                           class="btn btn-sm btn-outline-danger px-4">
                            <i class="fa-solid fa-unlock me-1"></i>Gửi yêu cầu mở khóa
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection