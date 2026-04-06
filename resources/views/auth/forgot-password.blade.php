@extends('layouts.auth')

@section('title', 'Quên mật khẩu')

@section('content')
<div class="auth-container">
    <x-sparkles :count="20" />

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-4">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                <div class="auth-card">
                    <div class="text-center mb-4">
                        <img src="{{ asset('storage/logo.png') }}" alt="Blue Wave Music Logo" class="auth-logo mx-auto d-block mb-3">
                        <h1 class="auth-title" style="font-size:1.5rem">Quên mật khẩu</h1>
                        <p class="text-muted small mt-2">Nhập email đăng ký để nhận liên kết đặt lại mật khẩu.</p>
                    </div>

                    @if(session('success'))
                        <div class="alert border-0 mb-4 py-3 px-4" role="alert"
                             style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3)!important;border-radius:12px">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-circle-check mt-1" style="color:#34d399"></i>
                                <span style="color:#a7f3d0;font-size:.85rem">{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert border-0 mb-4 py-3 px-4" role="alert"
                             style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3)!important;border-radius:12px">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-triangle-exclamation mt-1" style="color:#ef4444"></i>
                                <span style="color:#fca5a5;font-size:.85rem">{{ $errors->first() }}</span>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" class="needs-validation" novalidate>
                        @csrf

                        <div class="mb-4">
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
                                autofocus
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-3 mb-3">
                            <button type="submit" class="btn btn-auth" data-loading-text="ĐANG GỬI...">
                                GỬI LIÊN KẾT ĐẶT LẠI
                                <i class="fa-solid fa-paper-plane ms-2"></i>
                            </button>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ route('login') }}" class="text-muted text-decoration-none small text-uppercase fw-semibold">
                                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại đăng nhập
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
