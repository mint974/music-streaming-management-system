@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="auth-container">
    {{-- Random sparkles --}}
    <x-sparkles :count="20" />

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-4">
            <div class="col-12 col-lg-10 col-xl-9">
                <div class="auth-card p-0 overflow-hidden">
                    <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate hx-boost="false">
                        @csrf
                        <div class="row g-0">

                            {{-- ═══ CỘT TRÁI: Branding + thông tin phụ ═══ --}}
                            <div class="col-lg-5 auth-register-left d-flex flex-column">
                                {{-- Header --}}
                                <div class="text-center mb-4">
                                    <img src="{{ asset('storage/logo.png') }}" alt="Blue Wave Music Logo" class="auth-logo mx-auto d-block mb-3">
                                    <h1 class="auth-title">Blue Wave Music</h1>
                                    <p class="auth-subtitle mt-2">Bắt đầu hành trình khám phá âm nhạc của bạn hôm nay!</p>
                                </div>

                                {{-- Divider --}}
                                <hr class="auth-register-divider">

                                {{-- Optional fields --}}
                                <p class="auth-register-optional-label">Thông tin thêm <span class="text-muted fw-normal">(không bắt buộc)</span></p>

                                {{-- Phone --}}
                                <div class="mb-3">
                                    <label for="phone" class="form-label text-uppercase">Số điện thoại</label>
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

                                {{-- Birthday --}}
                                <div class="mb-3">
                                    <label for="birthday" class="form-label text-uppercase">Ngày sinh</label>
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

                                {{-- Gender --}}
                                <div class="mb-3">
                                    <label for="gender" class="form-label text-uppercase">Giới tính</label>
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

                                {{-- Footer login link --}}
                                <div class="text-center mt-auto pt-4">
                                    <p class="text-muted small mb-2">Bạn đã có tài khoản?</p>
                                    <a href="{{ route('login') }}" class="auth-footer-link">Đăng nhập ngay</a>
                                </div>
                            </div>

                            {{-- ═══ CỘT PHẢI: Các trường chính ═══ --}}
                            <div class="col-lg-7 auth-register-right d-flex flex-column justify-content-center">
                                <h2 class="auth-register-right-title">Tạo tài khoản mới</h2>
                                <p class="text-muted small mb-4">Điền thông tin bên dưới để bắt đầu ngay.</p>

                                {{-- Name --}}
                                <div class="mb-3">
                                    <label for="name" class="form-label text-uppercase">Họ tên</label>
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
                                    <label for="email" class="form-label text-uppercase">Email</label>
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
                                    <label for="password" class="form-label text-uppercase">Mật khẩu</label>
                                    <div class="position-relative">
                                        <input
                                            type="password"
                                            class="form-control auth-input @error('password') is-invalid @enderror"
                                            id="password"
                                            name="password"
                                            placeholder="••••••••"
                                            required
                                            autocomplete="new-password"
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

                                {{-- Confirm Password --}}
                                <div class="mb-4">
                                    <label for="password_confirmation" class="form-label text-uppercase">Xác nhận mật khẩu</label>
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

                                {{-- Terms Agreement --}}
                                <div class="auth-checkbox mb-4">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        Tôi đồng ý với <a href="#" class="auth-link">dịch vụ</a>
                                        và <a href="#" class="auth-link">chính sách bảo mật</a>
                                    </label>
                                    @error('terms')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Submit --}}
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-auth" data-loading-text="ĐANG XỬ LÝ...">
                                        Tạo tài khoản
                                        <i class="fa-solid fa-user-plus ms-2"></i>
                                    </button>
                                </div>
                            </div>

                        </div>{{-- end .row --}}
                    </form>
                </div>{{-- end .auth-card --}}
            </div>
        </div>
    </div>
</div>
@endsection
