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
                        @php
                            $selectedRole = old('role')
                                ?? ($user->isArtist() ? 'artist' : ($user->isPremium() ? 'premium' : 'free'));
                        @endphp
                        <select name="role"
                                id="editRoleSelect"
                                data-current-premium="{{ $user->hasRole('premium') ? '1' : '0' }}"
                                data-current-artist="{{ $user->hasRole('artist') ? '1' : '0' }}"
                                class="form-select bg-dark border-secondary text-white @error('role') is-invalid @enderror"
                                required>
                            <option value="free"    {{ $selectedRole==='free'    ? 'selected':'' }}>Thính giả miễn phí</option>
                            <option value="premium" {{ $selectedRole==='premium' ? 'selected':'' }}>Thính giả Premium</option>
                            <option value="artist"  {{ $selectedRole==='artist'  ? 'selected':'' }}>Nghệ sĩ</option>
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 d-none" id="editVipWrap">
                        <label class="form-label text-muted small">Gói Premium khi nâng cấp <span class="text-danger">*</span></label>
                        <select name="vip_id" id="editVipSelect"
                                class="form-select bg-dark border-secondary text-white @error('vip_id') is-invalid @enderror">
                            <option value="">-- Chọn gói Premium --</option>
                            @foreach($vipPlans as $vip)
                                <option value="{{ $vip->id }}" {{ old('vip_id') == $vip->id ? 'selected' : '' }}>
                                    {{ $vip->title }} — {{ number_format($vip->price) }} ₫ / {{ $vip->duration_days }} ngày
                                </option>
                            @endforeach
                        </select>
                        @error('vip_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text text-muted small">Khi nâng cấp Premium, hệ thống tạo payment 0 ₫ và đánh dấu đã thanh toán.</div>
                    </div>

                    <div class="col-md-6 d-none" id="editArtistWrap">
                        <label class="form-label text-muted small">Gói Nghệ sĩ khi nâng cấp <span class="text-danger">*</span></label>
                        <select name="artist_package_id" id="editArtistSelect"
                                class="form-select bg-dark border-secondary text-white @error('artist_package_id') is-invalid @enderror">
                            <option value="">-- Chọn gói Nghệ sĩ --</option>
                            @foreach($artistPackages as $pkg)
                                <option value="{{ $pkg->id }}" {{ old('artist_package_id') == $pkg->id ? 'selected' : '' }}>
                                    {{ $pkg->name }} — {{ number_format($pkg->price) }} ₫ / {{ $pkg->duration_days }} ngày
                                </option>
                            @endforeach
                        </select>
                        @error('artist_package_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text text-muted small">Khi nâng cấp Nghệ sĩ, hệ thống tạo đơn approved với số tiền 0 ₫.</div>
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
function syncEditRolePackageFields() {
    const roleSelect = document.getElementById('editRoleSelect');
    const vipWrap = document.getElementById('editVipWrap');
    const vipSelect = document.getElementById('editVipSelect');
    const artistWrap = document.getElementById('editArtistWrap');
    const artistSelect = document.getElementById('editArtistSelect');

    const isCurrentPremium = roleSelect.dataset.currentPremium === '1';
    const isCurrentArtist = roleSelect.dataset.currentArtist === '1';

    const needVipPackage = roleSelect.value === 'premium' && !isCurrentPremium;
    const needArtistPackage = roleSelect.value === 'artist' && !isCurrentArtist;

    vipWrap.classList.toggle('d-none', !needVipPackage);
    artistWrap.classList.toggle('d-none', !needArtistPackage);

    vipSelect.required = needVipPackage;
    artistSelect.required = needArtistPackage;

    if (!needVipPackage) {
        vipSelect.value = '';
    }
    if (!needArtistPackage) {
        artistSelect.value = '';
    }
}

const editRoleSelect = document.getElementById('editRoleSelect');
if (editRoleSelect) {
    editRoleSelect.addEventListener('change', syncEditRolePackageFields);
    syncEditRolePackageFields();
}


</script>
@endpush
