@extends('layouts.admin')

@section('title', 'Hồ sơ quản trị viên')
@section('page-title', 'Hồ sơ cá nhân')
@section('page-subtitle', 'Quản lý thông tin tài khoản quản trị viên')

@push('styles')
<style>
.profile-card {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 16px;
}
.profile-avatar-wrap {
    position: relative;
    display: inline-block;
}
.profile-avatar-wrap img {
    width: 110px;
    height: 110px;
    object-fit: cover;
    border: 3px solid rgba(255,255,255,.12);
    border-radius: 50%;
}
.profile-avatar-edit {
    position: absolute;
    bottom: 4px;
    right: 4px;
    width: 30px;
    height: 30px;
    background: #6366f1;
    border: 2px solid #0f172a;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: .7rem;
    color: #fff;
    transition: background .2s;
}
.profile-avatar-edit:hover { background: #4f46e5; }
.section-title {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 1.25rem;
}
.form-control-dark, .form-select-dark {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.1);
    color: #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: .88rem;
    transition: border-color .2s, box-shadow .2s;
}
.form-control-dark:focus, .form-select-dark:focus {
    background: rgba(255,255,255,.06);
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,.18);
    color: #fff;
    outline: none;
}
.form-control-dark::placeholder { color: #475569; }
.form-control-dark.is-invalid { border-color: #f87171; }
.admin-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    background: rgba(239,68,68,.12);
    color: #fca5a5;
    border: 1px solid rgba(239,68,68,.25);
    font-size: .75rem;
    font-weight: 600;
}
.info-stat {
    padding: 12px 0;
    border-bottom: 1px solid rgba(255,255,255,.05);
}
.info-stat:last-child { border-bottom: none; }
.info-stat-label { color: #64748b; font-size: .75rem; }
.info-stat-value { color: #e2e8f0; font-size: .85rem; font-weight: 500; }
.save-btn {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border: none;
    color: #fff;
    padding: 9px 22px;
    border-radius: 10px;
    font-size: .85rem;
    font-weight: 600;
    transition: opacity .2s, transform .1s;
    cursor: pointer;
}
.save-btn:hover { opacity: .9; transform: translateY(-1px); }
.save-btn:active { transform: translateY(0); }
.pass-toggle-btn {
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.1);
    border-left: none;
    color: #94a3b8;
    border-radius: 0 10px 10px 0;
    padding: 0 14px;
    cursor: pointer;
    transition: color .2s;
}
.pass-toggle-btn:hover { color: #e2e8f0; }
.input-group .form-control-dark { border-radius: 10px 0 0 10px; }
.strength-bar { height: 4px; border-radius: 2px; transition: width .3s, background .3s; }
</style>
@endpush

@section('content')

@php
    $admin = Auth::guard('admin')->user();
    $avatarUrl = ($admin->avatar && $admin->avatar !== '/storage/avt.jpg')
        ? asset($admin->avatar)
        : 'https://ui-avatars.com/api/?name='.urlencode($admin->name).'&background=6366f1&color=fff&size=220&bold=true';
@endphp

{{-- Flash messages --}}
@if(session('success'))
<div class="alert alert-dismissible fade show mb-4"
     style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.28);color:#6ee7b7" role="alert">
    <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('password_success'))
<div class="alert alert-dismissible fade show mb-4"
     style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.28);color:#6ee7b7" role="alert">
    <i class="fa-solid fa-shield-check me-2"></i>{{ session('password_success') }}
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any() && !$errors->passwordUpdate->any())
<div class="alert alert-dismissible fade show mb-4"
     style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.28);color:#fca5a5" role="alert">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>
    <ul class="mb-0 ps-3 mt-1">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    {{-- ── LEFT column: avatar + quick info ── --}}
    <div class="col-lg-4">
        <div class="profile-card p-4 text-center mb-4">

            {{-- Avatar --}}
            <div class="profile-avatar-wrap mb-3">
                <img src="{{ $avatarUrl }}" id="avatarPreview" alt="{{ $admin->name }}">
                <label for="avatarInputSidebar" class="profile-avatar-edit" title="Đổi ảnh đại diện">
                    <i class="fa-solid fa-camera"></i>
                </label>
            </div>

            <h5 class="text-white fw-bold mb-1">{{ $admin->name }}</h5>
            <div class="text-muted small mb-3">{{ $admin->email }}</div>

            <div class="d-flex justify-content-center gap-2 flex-wrap mb-4">
                <span class="admin-badge">
                    <i class="fa-solid fa-shield-halved"></i>Quản trị viên
                </span>
                <span class="badge rounded-pill px-3 py-1"
                      style="background:rgba(34,197,94,.12);color:#86efac;border:1px solid rgba(34,197,94,.25);font-size:.72rem">
                    <i class="fa-solid fa-circle-check me-1"></i>Đang hoạt động
                </span>
            </div>

            <a href="#section-info" class="save-btn d-block text-center text-decoration-none mb-2" style="font-size:.83rem">
                <i class="fa-solid fa-pen me-2"></i>Chỉnh sửa hồ sơ
            </a>
            <a href="#section-password" class="d-block text-center text-muted small text-decoration-none py-1"
               style="border:1px solid rgba(255,255,255,.06);border-radius:10px;transition:color .2s"
               onmouseover="this.style.color='#e2e8f0'" onmouseout="this.style.color=''">
                <i class="fa-solid fa-key me-2"></i>Đổi mật khẩu
            </a>
        </div>

        {{-- Quick info --}}
        <div class="profile-card p-4">
            <div class="section-title">Thông tin nhanh</div>
            <div class="info-stat d-flex justify-content-between align-items-center">
                <span class="info-stat-label">ID</span>
                <span class="info-stat-value">#{{ $admin->id }}</span>
            </div>
            <div class="info-stat d-flex justify-content-between align-items-center">
                <span class="info-stat-label">Vai trò</span>
                <span class="info-stat-value">Admin</span>
            </div>
            <div class="info-stat d-flex justify-content-between align-items-center">
                <span class="info-stat-label">Số điện thoại</span>
                <span class="info-stat-value">{{ $admin->phone ?: '—' }}</span>
            </div>
            <div class="info-stat d-flex justify-content-between align-items-center">
                <span class="info-stat-label">Ngày tạo</span>
                <span class="info-stat-value">{{ $admin->created_at->format('d/m/Y') }}</span>
            </div>
            <div class="info-stat d-flex justify-content-between align-items-center">
                <span class="info-stat-label">Cập nhật</span>
                <span class="info-stat-value">{{ $admin->updated_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>
    </div>

    {{-- ── RIGHT column ── --}}
    <div class="col-lg-8 d-flex flex-column gap-4">

        {{-- ── Section 1: Profile info ── --}}
        <div class="profile-card p-4" id="section-info">
            <div class="section-title">
                <i class="fa-solid fa-user me-2"></i>Thông tin cá nhân
            </div>

            <form method="POST" action="{{ route('admin.profile.update') }}"
                  enctype="multipart/form-data" id="profileForm">
                @csrf
                @method('PATCH')

                {{-- Hidden file input synced with sidebar avatar button --}}
                <input type="file" id="avatarInputSidebar" name="avatar"
                       accept=".jpg,.jpeg,.png,.webp,.gif" class="d-none">

                {{-- Avatar inline upload (also shown in form for clarity) --}}
                <div class="mb-4">
                    <label class="form-label section-title mb-2">Ảnh đại diện</label>
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $avatarUrl }}" alt="avatar" id="avatarPreview2"
                             style="width:56px;height:56px;object-fit:cover;border-radius:12px;border:1px solid rgba(255,255,255,.1)">
                        <div>
                            <label for="avatarInput" class="save-btn d-inline-flex align-items-center gap-2"
                                   style="padding:7px 14px;font-size:.8rem;cursor:pointer">
                                <i class="fa-solid fa-upload"></i>Tải ảnh lên
                            </label>
                            <input type="file" id="avatarInput" name="avatar"
                                   accept=".jpg,.jpeg,.png,.webp,.gif" class="d-none">
                            <div class="text-muted mt-1" style="font-size:.72rem">JPG, PNG, WEBP, GIF — tối đa 3MB</div>
                            @error('avatar')
                                <div class="text-danger mt-1" style="font-size:.78rem">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label section-title mb-2">Họ tên</label>
                        <input type="text" name="name" id="admin_name"
                               class="form-control form-control-dark @error('name') is-invalid @enderror"
                               value="{{ old('name', $admin->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback" style="font-size:.78rem">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label section-title mb-2">Email</label>
                        <input type="email" name="email" id="admin_email"
                               class="form-control form-control-dark @error('email') is-invalid @enderror"
                               value="{{ old('email', $admin->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback" style="font-size:.78rem">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label section-title mb-2">Số điện thoại</label>
                        <input type="text" name="phone" id="admin_phone"
                               class="form-control form-control-dark @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $admin->phone) }}"
                               placeholder="+84 123 456 789">
                        @error('phone')
                            <div class="invalid-feedback" style="font-size:.78rem">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <div class="rounded-3 p-3 w-100"
                             style="background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.15)">
                            <div class="text-muted" style="font-size:.72rem;margin-bottom:4px">Vai trò hệ thống</div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-shield-halved" style="color:#818cf8"></i>
                                <span class="text-white fw-semibold" style="font-size:.88rem">Quản trị viên (Admin)</span>
                            </div>
                            <div class="text-muted mt-1" style="font-size:.7rem">Vai trò không thể thay đổi</div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="save-btn">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Lưu thay đổi
                    </button>
                    <a href="{{ route('admin.dashboard') }}"
                       style="padding:9px 18px;border-radius:10px;border:1px solid rgba(255,255,255,.1);color:#94a3b8;text-decoration:none;font-size:.85rem;display:inline-flex;align-items:center;gap:8px;transition:color .2s"
                       onmouseover="this.style.color='#e2e8f0'" onmouseout="this.style.color='#94a3b8'">
                        <i class="fa-solid fa-xmark"></i>Hủy
                    </a>
                </div>
            </form>
        </div>

        {{-- ── Section 2: Change password ── --}}
        <div class="profile-card p-4" id="section-password">
            <div class="section-title">
                <i class="fa-solid fa-key me-2"></i>Đổi mật khẩu
            </div>

            @if($errors->passwordUpdate->any())
            <div class="alert mb-3"
                 style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:#fca5a5;border-radius:10px;font-size:.83rem">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <ul class="mb-0 ps-3 mt-1">
                    @foreach($errors->passwordUpdate->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.profile.password') }}" id="passwordForm">
                @csrf
                @method('PATCH')

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label section-title mb-2">Mật khẩu hiện tại</label>
                        <div class="input-group">
                            <input type="password" name="current_password" id="current_password"
                                   class="form-control form-control-dark @error('current_password', 'passwordUpdate') is-invalid @enderror"
                                   placeholder="••••••••" required>
                            <button type="button" class="pass-toggle-btn" onclick="togglePwd('current_password', this)">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label section-title mb-2">Mật khẩu mới</label>
                        <div class="input-group">
                            <input type="password" name="password" id="new_password"
                                   class="form-control form-control-dark @error('password', 'passwordUpdate') is-invalid @enderror"
                                   placeholder="••••••••" required oninput="checkStrength(this.value)">
                            <button type="button" class="pass-toggle-btn" onclick="togglePwd('new_password', this)">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        {{-- Strength indicator --}}
                        <div class="mt-2 d-flex gap-1" id="strengthBars">
                            <div class="strength-bar flex-grow-1" id="bar1" style="background:rgba(255,255,255,.08)"></div>
                            <div class="strength-bar flex-grow-1" id="bar2" style="background:rgba(255,255,255,.08)"></div>
                            <div class="strength-bar flex-grow-1" id="bar3" style="background:rgba(255,255,255,.08)"></div>
                            <div class="strength-bar flex-grow-1" id="bar4" style="background:rgba(255,255,255,.08)"></div>
                        </div>
                        <div class="mt-1 text-muted" id="strengthLabel" style="font-size:.7rem"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label section-title mb-2">Xác nhận mật khẩu mới</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="confirm_password"
                                   class="form-control form-control-dark"
                                   placeholder="••••••••" required>
                            <button type="button" class="pass-toggle-btn" onclick="togglePwd('confirm_password', this)">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="text-muted" style="font-size:.75rem">
                            <i class="fa-solid fa-circle-info me-1" style="color:#818cf8"></i>
                            Mật khẩu phải có tối thiểu 8 ký tự. Nên bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="save-btn">
                            <i class="fa-solid fa-shield-check me-2"></i>Cập nhật mật khẩu
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>{{-- END right col --}}
</div>

@endsection

@push('scripts')
<script>
// ── Password visibility toggle ──
function togglePwd(fieldId, btn) {
    const input = document.getElementById(fieldId);
    const icon  = btn.querySelector('i');
    const show  = input.type === 'password';
    input.type  = show ? 'text' : 'password';
    icon.classList.toggle('fa-eye',       !show);
    icon.classList.toggle('fa-eye-slash',  show);
}

// ── Password strength ──
function checkStrength(val) {
    const bars   = [1,2,3,4].map(n => document.getElementById('bar' + n));
    const label  = document.getElementById('strengthLabel');
    const colors = ['#f87171','#fbbf24','#34d399','#4ade80'];
    const texts  = ['Rất yếu','Trung bình','Khá mạnh','Mạnh'];

    let score = 0;
    if (val.length >= 8)    score++;
    if (/[A-Z]/.test(val))  score++;
    if (/[0-9]/.test(val))  score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    bars.forEach((b, i) => {
        b.style.background = i < score ? colors[score - 1] : 'rgba(255,255,255,.08)';
    });
    label.textContent = val.length ? texts[score - 1] ?? '' : '';
    label.style.color = score > 0 ? colors[score - 1] : '#64748b';
}

// ── Avatar preview (sidebar + form button both sync) ──
function syncAvatarPreview(file) {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        ['avatarPreview', 'avatarPreview2'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.src = e.target.result;
        });
    };
    reader.readAsDataURL(file);
}

document.getElementById('avatarInput').addEventListener('change', function () {
    syncAvatarPreview(this.files[0]);
    // Sync the sidebar hidden input too (both have name="avatar" → only one in form)
    document.getElementById('avatarInputSidebar').files = this.files;
});

document.getElementById('avatarInputSidebar').addEventListener('change', function () {
    syncAvatarPreview(this.files[0]);
    document.getElementById('avatarInput').files = this.files;
});
</script>
@endpush
