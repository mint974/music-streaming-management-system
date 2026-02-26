@extends('layouts.admin')

@section('title', 'Tạo tài khoản mới')
@section('page-title', 'Tạo tài khoản mới')
@section('page-subtitle', 'Tạo tài khoản người dùng mới trong hệ thống')

@section('content')

{{-- Back --}}
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="text-muted small text-decoration-none">
        <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-10">
        <div class="rounded-4 p-4 p-md-5"
             style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)">

            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width:44px;height:44px;background:rgba(34,197,94,.12);border-radius:12px;
                            display:flex;align-items:center;justify-content:center">
                    <i class="fa-solid fa-user-plus" style="color:#86efac"></i>
                </div>
                <div>
                    <h5 class="text-white fw-bold mb-0">Tạo tài khoản mới</h5>
                    <div class="text-muted small">Điền đầy đủ thông tin bên dưới</div>
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

            <form method="POST" action="{{ route('admin.users.store') }}" autocomplete="off">
                @csrf

                <div class="row g-4">
                    {{-- ── Thông tin cơ bản ── --}}
                    <div class="col-12">
                        <h6 class="text-muted small text-uppercase fw-bold mb-3" style="letter-spacing:.07em">
                            Thông tin cơ bản
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="form-control bg-dark border-secondary text-white @error('name') is-invalid @enderror"
                               placeholder="Nguyễn Văn A" maxlength="255" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="form-control bg-dark border-secondary text-white @error('email') is-invalid @enderror"
                               placeholder="example@email.com" maxlength="255" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small">Mật khẩu <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="password"
                                   class="form-control bg-dark border-secondary text-white @error('password') is-invalid @enderror"
                                   placeholder="Tối thiểu 8 ký tự" minlength="8" required>
                            <button type="button" class="btn btn-outline-secondary toggle-pass" data-target="password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control bg-dark border-secondary text-white"
                                   placeholder="Nhập lại mật khẩu" minlength="8" required>
                            <button type="button" class="btn btn-outline-secondary toggle-pass" data-target="password_confirmation">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    {{-- ── Thông tin cá nhân ── --}}
                    <div class="col-12 mt-2">
                        <hr style="border-color:rgba(255,255,255,.07)">
                        <h6 class="text-muted small text-uppercase fw-bold mb-3" style="letter-spacing:.07em">
                            Thông tin cá nhân
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small">Số điện thoại</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="form-control bg-dark border-secondary text-white @error('phone') is-invalid @enderror"
                               placeholder="0912345678" maxlength="20">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small">Ngày sinh</label>
                        <input type="date" name="birthday" value="{{ old('birthday') }}"
                               class="form-control bg-dark border-secondary text-white @error('birthday') is-invalid @enderror"
                               max="{{ now()->subDay()->format('Y-m-d') }}">
                        @error('birthday')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small">Giới tính</label>
                        <select name="gender" class="form-select bg-dark border-secondary text-white @error('gender') is-invalid @enderror">
                            <option value="">-- Không chọn --</option>
                            <option value="Nam"  {{ old('gender')==='Nam'  ? 'selected':'' }}>Nam</option>
                            <option value="Nữ"   {{ old('gender')==='Nữ'   ? 'selected':'' }}>Nữ</option>
                            <option value="Khác" {{ old('gender')==='Khác' ? 'selected':'' }}>Khác</option>
                        </select>
                        @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small">Loại tài khoản <span class="text-danger">*</span></label>
                        <select name="role" class="form-select bg-dark border-secondary text-white @error('role') is-invalid @enderror" required>
                            <option value="free"    {{ old('role','free')==='free'    ? 'selected':'' }}>Thính giả miễn phí</option>
                            <option value="premium" {{ old('role')==='premium' ? 'selected':'' }}>Thính giả Premium</option>
                            <option value="artist"  {{ old('role')==='artist'  ? 'selected':'' }}>Nghệ sĩ</option>
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-flex gap-3 mt-4 pt-2" style="border-top:1px solid rgba(255,255,255,.07)">
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fa-solid fa-plus me-2"></i>Tạo tài khoản
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
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
        const inp = document.getElementById(this.dataset.target);
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
