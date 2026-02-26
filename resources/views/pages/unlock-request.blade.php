@extends('layouts.auth')

@section('title', 'Yêu cầu mở khóa tài khoản')

@section('content')
<div class="auth-container">
    <x-sparkles :count="15" />

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-4">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                <div class="auth-card">
                    {{-- Logo --}}
                    <div class="text-center mb-4">
                        <img src="{{ asset('storage/logo.png') }}" alt="Blue Wave Music" class="auth-logo mx-auto d-block mb-3">
                        <h1 class="auth-title">Yêu cầu mở khóa</h1>
                        <p class="text-muted small">Điền thông tin bên dưới để gửi yêu cầu khiếu nại đến quản trị viên.</p>
                    </div>

                    @if($errors->any())
                    <div class="alert alert-danger rounded-3 small mb-4">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        {{ $errors->first() }}
                    </div>
                    @endif

                    <form method="POST" action="{{ route('unlock-request.store') }}">
                        @csrf

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label text-uppercase small fw-semibold text-muted">
                                Email tài khoản bị khóa
                            </label>
                            <input type="email"
                                   class="form-control auth-input @error('email') is-invalid @enderror"
                                   id="email" name="email"
                                   value="{{ old('email', request('email')) }}"
                                   placeholder="hello@example.com"
                                   required autocomplete="email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nội dung khiếu nại --}}
                        <div class="mb-4">
                            <label for="content" class="form-label text-uppercase small fw-semibold text-muted">
                                Nội dung khiếu nại
                            </label>
                            <textarea class="form-control auth-input @error('content') is-invalid @enderror"
                                      id="content" name="content"
                                      rows="5"
                                      placeholder="Vui lòng mô tả chi tiết lý do bạn cho rằng tài khoản bị khóa nhầm, hoặc cam kết không vi phạm quy định trong tương lai..."
                                      maxlength="2000"
                                      required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted small mt-1">
                                Tối thiểu 30 ký tự. Càng chi tiết, yêu cầu của bạn càng dễ được xem xét.
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-auth">
                                GỬI YÊU CẦU
                                <i class="fa-solid fa-paper-plane ms-2"></i>
                            </button>
                        </div>

                        {{-- Back to login --}}
                        <div class="text-center">
                            <a href="{{ route('login') }}" class="text-muted text-decoration-none small text-uppercase fw-semibold">
                                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại đăng nhập
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Info box --}}
                <div class="mt-4 p-3 rounded-3" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08)">
                    <p class="text-muted small mb-2">
                        <i class="fa-solid fa-circle-info me-2" style="color:#818cf8"></i>
                        <strong class="text-white">Lưu ý quan trọng</strong>
                    </p>
                    <ul class="text-muted small mb-0 ps-3">
                        <li>Yêu cầu của bạn sẽ được quản trị viên xem xét trong vòng 1–3 ngày làm việc.</li>
                        <li>Kết quả xử lý sẽ được gửi đến email đăng ký của bạn.</li>
                        <li>Mỗi tài khoản chỉ có một yêu cầu đang chờ xử lý tại một thời điểm.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
