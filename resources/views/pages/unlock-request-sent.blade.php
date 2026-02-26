@extends('layouts.auth')

@section('title', 'Đã gửi yêu cầu mở khóa')

@section('content')
<div class="auth-container">
    <x-sparkles :count="15" />

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-4">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">
                <div class="auth-card text-center">
                    {{-- Icon success --}}
                    <div class="mb-4" style="font-size:3rem;color:#4ade80">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>

                    <h2 class="auth-title mb-2">Yêu cầu đã được gửi!</h2>
                    <p class="text-muted small mb-4">
                        Yêu cầu mở khóa của bạn đã được tiếp nhận thành công.
                        Quản trị viên sẽ xem xét và phản hồi qua email
                        @if(session('unlock_email'))
                            <strong class="text-white">{{ session('unlock_email') }}</strong>
                        @else
                            của bạn
                        @endif
                        trong vòng <strong class="text-white">1–3 ngày làm việc</strong>.
                    </p>

                    <div class="p-3 rounded-3 mb-4 text-start" style="background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.25)">
                        <p class="text-muted small mb-0">
                            <i class="fa-solid fa-bell me-2" style="color:#818cf8"></i>
                            Trong lúc chờ đợi, bạn có thể theo dõi hộp thư đến để nhận thông báo từ
                            <strong class="text-white">Blue Wave Music</strong>.
                        </p>
                    </div>

                    <a href="{{ route('login') }}" class="btn btn-auth d-block">
                        <i class="fa-solid fa-arrow-left me-2"></i>Quay lại đăng nhập
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
