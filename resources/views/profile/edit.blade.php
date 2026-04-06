@extends('layouts.main')

@section('title', 'Thông tin cá nhân – Blue Wave Music')

@section('content')
<div class="container-fluid py-2">
    <div class="row g-4">
        <div class="col-12 col-xl-4">
            <div class="card bg-dark bg-opacity-50 border border-secondary-subtle rounded-4 h-100">
                <div class="card-body p-4 text-center">
                    @php
                        $avatarSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Ccircle cx='80' cy='80' r='80' fill='%23e11d48'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='56' fill='%23ffffff' font-weight='bold'%3E" . strtoupper(substr($user->name, 0, 1)) . "%3C/text%3E%3C/svg%3E";
                        $avatarSrc = $user->avatar ?: $avatarSvg;
                    @endphp

                    <img src="{{ $avatarSrc }}"
                         alt="{{ $user->name }}"
                         class="rounded-circle border border-2 border-light-subtle mb-3"
                         style="width: 128px; height: 128px; object-fit: cover;">

                    <h4 class="text-white mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">{{ $user->email }}</p>

                    @if($user->hasVerifiedEmail())
                        <span class="badge rounded-pill text-bg-success px-3 py-2">
                            <i class="fa-solid fa-circle-check me-1"></i> Email đã xác minh
                        </span>
                    @else
                        <span class="badge rounded-pill text-bg-warning px-3 py-2">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Email chưa xác minh
                        </span>
                    @endif

                    <hr class="border-secondary-subtle my-4">

                    <div class="text-start small text-muted d-grid gap-2">
                        <div>
                            <strong class="text-light">Vai trò:</strong>
                            {{ strtoupper(implode(' / ', $user->getRoleNames())) }}
                        </div>
                        <div><strong class="text-light">Trạng thái:</strong> {{ $user->status }}</div>
                        <div><strong class="text-light">Ngày tạo:</strong> {{ $user->created_at?->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            {{-- ═══ THÔNG TIN CÁ NHÂN ═══ --}}
            <div class="card bg-dark bg-opacity-50 border border-secondary-subtle rounded-4">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                        <div>
                            <h3 class="text-white mb-1">Chỉnh sửa thông tin cá nhân</h3>
                            <p class="text-muted mb-0">Cập nhật hồ sơ và ảnh đại diện của bạn.</p>
                        </div>

                        @if(!$user->hasVerifiedEmail())
                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button type="submit" class="btn mm-btn mm-btn-outline">
                                    <i class="fa-solid fa-envelope-circle-check"></i>
                                    <span>Gửi lại email xác minh</span>
                                </button>
                            </form>
                        @endif
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success border-0 mb-4" role="alert">
                            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any() && !$errors->hasBag('passwordUpdate') && !$errors->hasBag('emailUpdate'))
                        <div class="alert alert-danger border-0 mb-4" role="alert">
                            <div class="fw-semibold mb-2">Có lỗi xảy ra, vui lòng kiểm tra lại:</div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        @method('PATCH')

                        <div class="col-12">
                            <label for="avatar" class="form-label text-uppercase small fw-semibold text-muted">Ảnh đại diện</label>
                            <input class="form-control bg-dark text-light border-secondary @error('avatar') is-invalid @enderror"
                                   type="file"
                                   id="avatar"
                                   name="avatar"
                                   accept=".jpg,.jpeg,.png,.webp,.gif">
                            @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted">Hỗ trợ JPG, PNG, WEBP, GIF. Tối đa 3MB.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="name" class="form-label text-uppercase small fw-semibold text-muted">Họ tên</label>
                            <input type="text"
                                   class="form-control bg-dark text-light border-secondary @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $user->name) }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-uppercase small fw-semibold text-muted">Email</label>
                            <input type="email"
                                   class="form-control bg-dark text-light border-secondary"
                                   value="{{ $user->email }}"
                                   disabled
                                   readonly>
                            <div class="form-text text-muted">
                                <i class="fa-solid fa-circle-info me-1"></i>Để thay đổi email, vui lòng dùng mục "Thay đổi email" bên dưới.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label text-uppercase small fw-semibold text-muted">Số điện thoại</label>
                            <input type="text"
                                   class="form-control bg-dark text-light border-secondary @error('phone') is-invalid @enderror"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone', $user->phone) }}"
                                   placeholder="+84 123 456 789">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="birthday" class="form-label text-uppercase small fw-semibold text-muted">Ngày sinh</label>
                            <input type="date"
                                   class="form-control bg-dark text-light border-secondary @error('birthday') is-invalid @enderror"
                                   id="birthday"
                                   name="birthday"
                                   value="{{ old('birthday', optional($user->birthday)->format('Y-m-d')) }}"
                                   max="{{ now()->format('Y-m-d') }}">
                            @error('birthday')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="gender" class="form-label text-uppercase small fw-semibold text-muted">Giới tính</label>
                            <select class="form-select bg-dark text-light border-secondary @error('gender') is-invalid @enderror"
                                    id="gender"
                                    name="gender">
                                <option value="">-- Chọn giới tính --</option>
                                <option value="Nam" @selected(old('gender', $user->gender) === 'Nam')>Nam</option>
                                <option value="Nữ" @selected(old('gender', $user->gender) === 'Nữ')>Nữ</option>
                                <option value="Khác" @selected(old('gender', $user->gender) === 'Khác')>Khác</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 pt-2 d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn mm-btn mm-btn-primary">
                                <i class="fa-solid fa-floppy-disk"></i>
                                <span>Lưu thay đổi</span>
                            </button>
                            <a href="{{ route('home') }}" class="btn mm-btn mm-btn-ghost">
                                <i class="fa-solid fa-arrow-left"></i>
                                <span>Quay lại</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ═══ THAY ĐỔI EMAIL ═══ --}}
            <div class="card bg-dark bg-opacity-50 border border-secondary-subtle rounded-4 mt-4">
                <div class="card-body p-4 p-md-5">
                    <div class="mb-4">
                        <h4 class="text-white mb-1">
                            <i class="fa-solid fa-envelope me-2" style="color:#818cf8"></i>Thay đổi email
                        </h4>
                        <p class="text-muted mb-0">Thay đổi email đăng nhập. Hệ thống sẽ gửi email xác nhận đến email hiện tại trước khi áp dụng.</p>
                    </div>

                    @if(session('email_success'))
                        <div class="alert border-0 mb-4 py-3 px-4" role="alert"
                             style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3)!important;border-radius:12px">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-paper-plane mt-1" style="color:#34d399"></i>
                                <span style="color:#a7f3d0;font-size:.85rem">{{ session('email_success') }}</span>
                            </div>
                        </div>
                    @endif

                    @if(session('email_info'))
                        <div class="alert border-0 mb-4 py-3 px-4" role="alert"
                             style="background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.3)!important;border-radius:12px">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-circle-info mt-1" style="color:#818cf8"></i>
                                <span style="color:#c7d2fe;font-size:.85rem">{{ session('email_info') }}</span>
                            </div>
                        </div>
                    @endif

                    @if($errors->emailUpdate->any())
                        <div class="alert alert-danger border-0 mb-4" role="alert">
                            <div class="fw-semibold mb-2">Không thể thay đổi email:</div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->emailUpdate->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.email.update') }}" class="row g-3">
                        @csrf

                        <div class="col-12">
                            <label class="form-label text-uppercase small fw-semibold text-muted">Email hiện tại</label>
                            <input type="email" class="form-control bg-dark text-light border-secondary"
                                   value="{{ $user->email }}" disabled readonly>
                        </div>

                        <div class="col-md-6">
                            <label for="new_email" class="form-label text-uppercase small fw-semibold text-muted">Email mới</label>
                            <input type="email"
                                   class="form-control bg-dark text-light border-secondary @error('new_email', 'emailUpdate') is-invalid @enderror"
                                   id="new_email"
                                   name="new_email"
                                   value="{{ old('new_email') }}"
                                   placeholder="newemail@example.com"
                                   required>
                            @error('new_email', 'emailUpdate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email_password" class="form-label text-uppercase small fw-semibold text-muted">Mật khẩu xác nhận</label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control bg-dark text-light border-secondary @error('email_password', 'emailUpdate') is-invalid @enderror"
                                       id="email_password"
                                       name="email_password"
                                       autocomplete="current-password"
                                       required>
                                <button class="btn btn-outline-secondary border-secondary px-3" type="button"
                                        onclick="togglePassword('email_password', this)"
                                        title="Xem mật khẩu">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                                @error('email_password', 'emailUpdate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-text text-muted">
                                <i class="fa-solid fa-shield-halved me-1"></i>
                                Sau khi gửi yêu cầu, hệ thống sẽ gửi email xác nhận đến email hiện tại (<strong>{{ $user->email }}</strong>).
                                Bấm liên kết trong email để hoàn tất thay đổi.
                            </div>
                        </div>

                        <div class="col-12 pt-2 d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn mm-btn mm-btn-primary">
                                <i class="fa-solid fa-paper-plane"></i>
                                <span>Gửi yêu cầu thay đổi</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ═══ ĐỔI MẬT KHẨU ═══ --}}
            <div class="card bg-dark bg-opacity-50 border border-secondary-subtle rounded-4 mt-4">
                <div class="card-body p-4 p-md-5">
                    <div class="mb-4">
                        <h4 class="text-white mb-1">
                            <i class="fa-solid fa-key me-2" style="color:#fbbf24"></i>Đổi mật khẩu
                        </h4>
                        <p class="text-muted mb-0">Nhập mật khẩu hiện tại và mật khẩu mới. Hệ thống sẽ gửi email xác nhận trước khi áp dụng.</p>
                    </div>

                    @if(session('password_success'))
                        <div class="alert border-0 mb-4 py-3 px-4" role="alert"
                             style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3)!important;border-radius:12px">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-shield-check mt-1" style="color:#34d399"></i>
                                <span style="color:#a7f3d0;font-size:.85rem">{{ session('password_success') }}</span>
                            </div>
                        </div>
                    @endif

                    @if($errors->passwordUpdate->any())
                        <div class="alert alert-danger border-0 mb-4" role="alert">
                            <div class="fw-semibold mb-2">Không thể đổi mật khẩu, vui lòng kiểm tra lại:</div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->passwordUpdate->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.password.update') }}" class="row g-3">
                        @csrf
                        @method('PATCH')

                        <div class="col-12">
                            <label for="current_password" class="form-label text-uppercase small fw-semibold text-muted">Mật khẩu hiện tại</label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control bg-dark text-light border-secondary @error('current_password', 'passwordUpdate') is-invalid @enderror"
                                       id="current_password"
                                       name="current_password"
                                       autocomplete="current-password"
                                       required>
                                <button class="btn btn-outline-secondary border-secondary px-3" type="button"
                                        onclick="togglePassword('current_password', this)"
                                        title="Xem mật khẩu">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                                @error('current_password', 'passwordUpdate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label text-uppercase small fw-semibold text-muted">Mật khẩu mới</label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control bg-dark text-light border-secondary @error('password', 'passwordUpdate') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       autocomplete="new-password"
                                       required>
                                <button class="btn btn-outline-secondary border-secondary px-3" type="button"
                                        onclick="togglePassword('password', this)"
                                        title="Xem mật khẩu">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                                @error('password', 'passwordUpdate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label text-uppercase small fw-semibold text-muted">Xác nhận mật khẩu mới</label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control bg-dark text-light border-secondary"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       autocomplete="new-password"
                                       required>
                                <button class="btn btn-outline-secondary border-secondary px-3" type="button"
                                        onclick="togglePassword('password_confirmation', this)"
                                        title="Xem mật khẩu">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-text text-muted">
                                <i class="fa-solid fa-shield-halved me-1"></i>
                                Mật khẩu nên có tối thiểu 8 ký tự. Sau khi gửi, hệ thống sẽ gửi email xác nhận đến
                                <strong>{{ $user->email }}</strong> trước khi áp dụng mật khẩu mới.
                            </div>
                        </div>

                        <div class="col-12 pt-2 d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn mm-btn mm-btn-primary">
                                <i class="fa-solid fa-paper-plane"></i>
                                <span>Gửi yêu cầu đổi mật khẩu</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(fieldId, btn) {
    const input = document.getElementById(fieldId);
    const icon  = btn.querySelector('i');
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    icon.classList.toggle('fa-eye',        !isHidden);
    icon.classList.toggle('fa-eye-slash',   isHidden);
}
</script>
@endpush
