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
                    <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
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
                            <input 
                                type="password" 
                                class="form-control auth-input @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password"
                                placeholder="••••••••"
                                required 
                                autocomplete="current-password"
                            >
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Remember Me --}}
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label small text-muted" for="remember">
                                Remember me
                            </label>
                        </div>

                        {{-- Submit Button --}}
                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-auth">
                                LOG IN
                                <i class="fa-solid fa-arrow-right ms-2"></i>
                            </button>
                        </div>

                        {{-- Forgot Password Link --}}
                        <div class="text-center mb-3">
                            <a href="#" class="text-muted text-decoration-none small text-uppercase fw-semibold">
                                Forgot your password?
                            </a>
                        </div>
                    </form>

                    {{-- Register Link --}}
                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="text-muted small mb-2">Don't have an account?</p>
                        <a href="{{ route('register') }}" class="auth-footer-link">REGISTER HERE</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection