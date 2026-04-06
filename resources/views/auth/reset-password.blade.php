@extends('layouts.auth')

@section('title', 'Đặt lại mật khẩu')

@section('content')
<div class="auth-container">
    <x-sparkles :count="20" />

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-4">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                <div class="auth-card">
                    <div class="text-center mb-4">
                        <img src="{{ asset('storage/logo.png') }}" alt="Blue Wave Music Logo" class="auth-logo mx-auto d-block mb-3">
                        <h1 class="auth-title" style="font-size:1.5rem">Đặt lại mật khẩu</h1>
                        <p class="text-muted small mt-2">Nhập mật khẩu mới cho tài khoản <strong style="color:#a5b4fc">{{ $email }}</strong></p>
                    </div>

                    @if($errors->any())
                        <div class="alert border-0 mb-4 py-3 px-4" role="alert"
                             style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3)!important;border-radius:12px">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-triangle-exclamation mt-1" style="color:#ef4444"></i>
                                <div>
                                    @foreach($errors->all() as $error)
                                        <div style="color:#fca5a5;font-size:.85rem">{{ $error }}</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="mb-3">
                            <label for="password" class="form-label text-uppercase small fw-semibold text-muted">Mật khẩu mới</label>
                            <div class="position-relative">
                                <input
                                    type="password"
                                    class="form-control auth-input @error('password') is-invalid @enderror"
                                    id="password"
                                    name="password"
                                    placeholder="••••••••"
                                    required
                                    autocomplete="new-password"
                                    autofocus
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

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label text-uppercase small fw-semibold text-muted">Xác nhận mật khẩu mới</label>
                            <div class="position-relative">
                                <input
                                    type="password"
                                    class="form-control auth-input"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    placeholder="••••••••"
                                    required
                                    autocomplete="new-password"
                                    style="padding-right:2.8rem"
                                >
                                <button type="button"
                                    class="position-absolute top-50 end-0 translate-middle-y border-0 bg-transparent me-2 p-1"
                                    style="z-index:10;color:#94a3b8;outline:none"
                                    onclick="var i=document.getElementById('password_confirmation');i.type=i.type==='password'?'text':'password';this.querySelector('i').classList.toggle('fa-eye');this.querySelector('i').classList.toggle('fa-eye-slash')">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-text text-muted mb-4" style="font-size:.78rem">
                            Mật khẩu nên có tối thiểu 8 ký tự và bao gồm chữ hoa, chữ thường, số hoặc ký tự đặc biệt.
                        </div>

                        <div class="d-grid gap-3 mb-3">
                            <button type="submit" class="btn btn-auth" data-loading-text="ĐANG XỬ LÝ...">
                                ĐẶT LẠI MẬT KHẨU
                                <i class="fa-solid fa-key ms-2"></i>
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
