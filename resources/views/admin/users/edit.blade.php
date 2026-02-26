@extends('layouts.admin')

@section('title', 'Chỉnh sửa – ' . $user->name)
@section('page-title', 'Chỉnh sửa tài khoản')
@section('page-subtitle', 'Cập nhật thông tin tài khoản người dùng')

@section('content')

{{-- Back --}}
<div class="mb-4">
    <a href="{{ route('admin.users.show', $user->id) }}" class="text-muted small text-decoration-none">
        <i class="fa-solid fa-arrow-left me-1"></i>Quay lại chi tiết
    </a>
</div>

@php
$avatarUrl = ($user->avatar && $user->avatar !== '/storage/avt.jpg')
    ? asset($user->avatar)
    : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=6366f1&color=fff&size=80';
@endphp

<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-10">
        <div class="rounded-4 p-4 p-md-5"
             style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)">

            {{-- Header --}}
            <div class="d-flex align-items-center gap-3 mb-4">
                <img src="{{ $avatarUrl }}" class="rounded-circle"
                     width="52" height="52"
                     style="object-fit:cover;border:2px solid rgba(255,255,255,.12)"
                     alt="{{ $user->name }}">
                <div>
                    <h5 class="text-white fw-bold mb-0">{{ $user->name }}</h5>
                    <div class="text-muted small">{{ $user->email }} &bull; ID #{{ $user->id }}</div>
                </div>
            </div>

            @if($errors->any())
            <div class="alert alert-danger mb-4">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <strong>Có lỗi xảy ra:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach($errors->all() as $err)
                        <li class="small">{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST"
                  action="{{ route('admin.users.update', $user->id) }}"
                  autocomplete="off">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    {{-- ── Thông tin cơ bản ── --}}
                    <div class="col-12">
                        <h6 class="text-muted small text-uppercase fw-bold mb-3" style="letter-spacing:.07em">
                            Thông tin cơ bản
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               value="{{ old('name', $user->name) }}"
                               class="form-control bg-dark border-secondary text-white @error('name') is-invalid @enderror"
                               placeholder="Nguyễn Văn A" maxlength="255" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email"
                               value="{{ old('email', $user->email) }}"
                               class="form-control bg-dark border-secondary text-white @error('email') is-invalid @enderror"
                               placeholder="example@email.com" maxlength="255" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small">Số điện thoại</label>
                        <input type="text" name="phone"
                               value="{{ old('phone', $user->phone) }}"
                               class="form-control bg-dark border-secondary text-white @error('phone') is-invalid @enderror"
                               placeholder="0912345678" maxlength="20">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small">Loại tài khoản <span class="text-danger">*</span></label>
                        <select name="role"
                                class="form-select bg-dark border-secondary text-white @error('role') is-invalid @enderror"
                                required>
                            <option value="free"    {{ old('role',$user->role)==='free'    ? 'selected':'' }}>Thính giả miễn phí</option>
                            <option value="premium" {{ old('role',$user->role)==='premium' ? 'selected':'' }}>Thính giả Premium</option>
                            <option value="artist"  {{ old('role',$user->role)==='artist'  ? 'selected':'' }}>Nghệ sĩ</option>
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small">Ngày sinh</label>
                        <input type="date" name="birthday"
                               value="{{ old('birthday', $user->birthday?->format('Y-m-d')) }}"
                               class="form-control bg-dark border-secondary text-white @error('birthday') is-invalid @enderror"
                               max="{{ now()->subDay()->format('Y-m-d') }}">
                        @error('birthday')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small">Giới tính</label>
                        <select name="gender"
                                class="form-select bg-dark border-secondary text-white @error('gender') is-invalid @enderror">
                            <option value="">-- Không chọn --</option>
                            <option value="Nam"  {{ old('gender',$user->gender)==='Nam'  ? 'selected':'' }}>Nam</option>
                            <option value="Nữ"   {{ old('gender',$user->gender)==='Nữ'   ? 'selected':'' }}>Nữ</option>
                            <option value="Khác" {{ old('gender',$user->gender)==='Khác' ? 'selected':'' }}>Khác</option>
                        </select>
                        @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- ── Đặt lại mật khẩu (tuỳ chọn) ── --}}
                    <div class="col-12 mt-2">
                        <hr style="border-color:rgba(255,255,255,.07)">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="text-muted small text-uppercase fw-bold mb-0" style="letter-spacing:.07em">
                                Đặt lại mật khẩu
                            </h6>
                            <span class="text-muted" style="font-size:.72rem">Để trống nếu không thay đổi</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small">Mật khẩu mới</label>
                        <div class="input-group">
                            <input type="password" name="new_password" id="new_password"
                                   class="form-control bg-dark border-secondary text-white @error('new_password') is-invalid @enderror"
                                   placeholder="Tối thiểu 8 ký tự" minlength="8">
                            <button type="button" class="btn btn-outline-secondary toggle-pass" data-target="new_password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small">Xác nhận mật khẩu mới</label>
                        <div class="input-group">
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                                   class="form-control bg-dark border-secondary text-white"
                                   placeholder="Nhập lại mật khẩu mới">
                            <button type="button" class="btn btn-outline-secondary toggle-pass" data-target="new_password_confirmation">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-flex gap-3 mt-4 pt-2" style="border-top:1px solid rgba(255,255,255,.07)">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Lưu thay đổi
                    </button>
                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline-secondary">
                        Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.toggle-pass').forEach(btn => {
    btn.addEventListener('click', function () {
        const inp  = document.getElementById(this.dataset.target);
        const icon = this.querySelector('i');
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            inp.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
});
</script>
@endpush
