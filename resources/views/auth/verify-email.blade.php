@extends('layouts.main')

@section('title', 'Xác minh email – Blue Wave Music')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-6">
            <div class="card bg-dark bg-opacity-50 border border-secondary-subtle rounded-4">
                <div class="card-body p-4 p-md-5 text-center">
                    <div class="mb-3">
                        <i class="fa-solid fa-envelope-circle-check text-danger" style="font-size: 3rem;"></i>
                    </div>

                    <h3 class="text-white mb-2">Xác minh địa chỉ email</h3>
                    <p class="text-muted mb-4">
                        Chúng tôi đã gửi một liên kết xác minh đến email của bạn.
                        Vui lòng mở email và bấm vào liên kết để hoàn tất xác minh tài khoản.
                    </p>

                    @if(session('success'))
                        <div class="alert alert-success border-0 text-start" role="alert">
                            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn mm-btn mm-btn-primary">
                                <i class="fa-solid fa-paper-plane"></i>
                                <span>Gửi lại email xác minh</span>
                            </button>
                        </form>

                        <a href="{{ route('profile.edit') }}" class="btn mm-btn mm-btn-outline">
                            <i class="fa-solid fa-user"></i>
                            <span>Đi đến hồ sơ</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
