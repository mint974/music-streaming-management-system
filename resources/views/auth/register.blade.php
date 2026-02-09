@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="auth-container">
    {{-- Random sparkles --}}
    <x-sparkles :count="20" />

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-4">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
                <div class="auth-card">
                    {{-- Logo / Title --}}
                    <div class="text-center mb-4">
                        <img src="{{ asset('storage/logo.png') }}" alt="Blue Wave Music Logo" class="auth-logo mx-auto d-block mb-3">
                        <h1 class="auth-title mb-2">Blue Wave Music</h1>
                        <p class="auth-subtitle text-muted mb-0">Bắt đầu hành trình khám phá âm nhạc của bạn hôm nay!</p>
                    </div>

                    {{-- Form --}}
                    <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate>
                        @csrf

                        {{-- Name --}}
                        <div class="mb-3">
                            <label for="name" class="form-label text-uppercase small fw-semibold text-muted">Họ tên</label>
                            <input 
                                type="text" 
                                class="form-control auth-input @error('name') is-invalid @enderror" 
                                id="name" 
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Nguyen Van A"
                                required 
                                autocomplete="name"
                                autofocus
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

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
                            <label for="password" class="form-label text-uppercase small fw-semibold text-muted">Mật khẩu</label>
                            <input 
                                type="password" 
                                class="form-control auth-input @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password"
                                placeholder="••••••••"
                                required 
                                autocomplete="new-password"
                            >
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Confirm Password --}}
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label text-uppercase small fw-semibold text-muted">Xác nhận mật khẩu</label>
                            <input 
                                type="password" 
                                class="form-control auth-input" 
                                id="password_confirmation" 
                                name="password_confirmation"
                                placeholder="••••••••"
                                required 
                                autocomplete="new-password"
                            >
                        </div>

                        {{-- Phone (Optional) --}}
                        <div class="mb-3">
                            <label for="phone" class="form-label text-uppercase small fw-semibold text-muted">Số điện thoại <span class="text-muted fw-normal">(Optional)</span></label>
                            <input 
                                type="tel" 
                                class="form-control auth-input @error('phone') is-invalid @enderror" 
                                id="phone" 
                                name="phone"
                                value="{{ old('phone') }}"
                                placeholder="+84 123 456 789"
                                autocomplete="tel"
                            >
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Birthday (Optional) --}}
                        <div class="mb-3">
                            <label for="birthday" class="form-label text-uppercase small fw-semibold text-muted">Ngày sinh <span class="text-muted fw-normal">(Optional)</span></label>
                            <input 
                                type="date" 
                                class="form-control auth-input @error('birthday') is-invalid @enderror" 
                                id="birthday" 
                                name="birthday"
                                value="{{ old('birthday') }}"
                                max="{{ date('Y-m-d') }}"
                                autocomplete="bday"
                            >
                            @error('birthday')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Gender (Optional) --}}
                        <div class="mb-3">
                            <label for="gender" class="form-label text-uppercase small fw-semibold text-muted">Giới tính <span class="text-muted fw-normal">(Optional)</span></label>
                            <select 
                                class="form-select auth-input @error('gender') is-invalid @enderror" 
                                id="gender" 
                                name="gender"
                                autocomplete="sex"
                            >
                                <option value="" selected>Chọn giới tính</option>
                                <option value="Nam" {{ old('gender') === 'Nam' ? 'selected' : '' }}>Nam</option>
                                <option value="Nữ" {{ old('gender') === 'Nữ' ? 'selected' : '' }}>Nữ</option>
                                <option value="Khác" {{ old('gender') === 'Khác' ? 'selected' : '' }}>Khác</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Terms Agreement --}}
                        <div class="auth-checkbox mb-3">
                            <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                Tôi đồng ý với <a href="#" class="auth-link">dịch vụ</a> 
                                và <a href="#" class="auth-link">chính sách</a>
                            </label>
                            @error('terms')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-auth">
                                Tạo tài khoản
                                <i class="fa-solid fa-user-plus ms-2"></i>
                            </button>
                        </div>
                    </form>

                    {{-- Login Link --}}
                    <div class="text-center mt-4">
                        <p class="text-muted small mb-2">Bạn đã có tài khoản?</p>
                        <a href="{{ route('login') }}" class="auth-footer-link">Đăng nhập ngay</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
