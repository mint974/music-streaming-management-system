@extends('layouts.artist')

@section('title', 'Hồ sơ nghệ sĩ – Artist Studio')
@section('page-title', 'Hồ sơ nghệ sĩ')
@section('page-subtitle', 'Cập nhật nghệ danh, tiểu sử và hình ảnh trang cá nhân nghệ sĩ')

@push('styles')
<style>
    /* Cover image preview */
    #coverPreviewContainer img,
    .cover-preview {
        width: 100% !important;
        height: 200px !important;
        border-radius: 14px;
        object-fit: cover !important;
        border: 1px solid rgba(168,85,247,.25);
        display: block;
    }
    .cover-placeholder {
        width: 100%;
        height: 200px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(168,85,247,.15) 0%, rgba(236,72,153,.08) 100%);
        border: 2px dashed rgba(168,85,247,.35);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: rgba(148,163,184,.6);
        font-size: .85rem;
    }

    /* Avatar preview */
    .avatar-preview-wrap { position: relative; display: inline-block; }
    .avatar-preview-wrap img {
        width: 96px; height: 96px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(168,85,247,.5);
    }
    .avatar-change-badge {
        position: absolute; bottom: 2px; right: 2px;
        width: 26px; height: 26px;
        border-radius: 50%;
        background: rgba(168,85,247,.85);
        border: 2px solid #0f0f1a;
        display: flex; align-items: center; justify-content: center;
        font-size: .68rem; color: #fff;
        cursor: pointer;
    }

    /* Social link row */
    .social-row .input-group-text {
        background: rgba(30,30,50,.7);
        border-color: rgba(100,116,139,.4);
        min-width: 44px;
        justify-content: center;
    }

    /* Section card */
    .ap-card {
        background: rgba(255,255,255,.03);
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 16px;
        padding: 1.75rem;
    }
    .ap-section-title {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: rgba(148,163,184,.7);
        margin-bottom: 1.25rem;
    }

    /* Char count */
    .char-count { font-size: .72rem; color: rgba(148,163,184,.55); }
</style>
@endpush

@section('content')
@php
    $user        = $user->loadMissing('socialLinks');
    $socialLinks = $user->socialLinks->pluck('url', 'platform')->toArray();
    $initial     = strtoupper(substr($user->name, 0, 1));
    $avatarSvg   = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='96' height='96'%3E%3Ccircle cx='48' cy='48' r='48' fill='%23a855f7'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='36' fill='%23ffffff' font-weight='bold'%3E" . $initial . "%3C/text%3E%3C/svg%3E";
    $avatarSrc   = ($user->avatar && $user->avatar !== '/storage/avt.jpg') ? asset($user->avatar) : $avatarSvg;
@endphp

{{-- ─── Cover image preview ──────────────────────────────────────────────── --}}
<div class="mb-4" id="coverPreviewContainer">
    @if($user->cover_image)
        <img src="{{ asset($user->cover_image) }}" alt="Ảnh bìa" id="coverPreviewImg"
             style="width:100%;height:200px;object-fit:cover;border-radius:14px;border:1px solid rgba(168,85,247,.25);display:block;">
    @else
        <div class="cover-placeholder" id="coverPlaceholder">
            <i class="fa-solid fa-panorama" style="font-size:1.8rem;color:rgba(168,85,247,.45)"></i>
            <span>Chưa có ảnh bìa kênh</span>
            <span style="font-size:.75rem">1500×500 px được khuyến nghị</span>
        </div>
        <img src="" alt="Ảnh bìa" id="coverPreviewImg"
             style="width:100%;height:200px;object-fit:cover;border-radius:14px;border:1px solid rgba(168,85,247,.25);display:none;">
    @endif
</div>

<div class="row g-4">

    {{-- ─────────────────────── LEFT: Profile card ───────────────────────── --}}
    <div class="col-12 col-xl-3">
        <div class="ap-card text-center">
            {{-- Avatar --}}
            <div class="mb-3 d-flex justify-content-center">
                <div class="avatar-preview-wrap">
                    <img src="{{ $avatarSrc }}" alt="{{ $user->name }}" id="avatarPreviewImg">
                    <label for="avatarInputSidebar" class="avatar-change-badge" title="Đổi ảnh đại diện">
                        <i class="fa-solid fa-camera"></i>
                    </label>
                </div>
            </div>

            <h5 class="text-white fw-bold mb-1">
                {{ $user->getDisplayArtistName() }}
                @if($user->isArtistVerified())
                    <i class="fa-solid fa-circle-check ms-1" style="color:#60a5fa;font-size:.9rem" title="Đã xác minh"></i>
                @endif
            </h5>
            <p class="text-muted mb-3" style="font-size:.8rem">{{ $user->email }}</p>

            @if($user->isArtistVerified())
                <span class="badge rounded-pill px-3 py-2 mb-3" style="background:rgba(59,130,246,.15);color:#60a5fa;border:1px solid rgba(59,130,246,.3);font-size:.72rem">
                    <i class="fa-solid fa-circle-check me-1"></i>Nghệ sĩ đã xác minh
                </span>
            @else
                <span class="badge rounded-pill px-3 py-2 mb-3" style="background:rgba(251,191,36,.1);color:#fbbf24;border:1px solid rgba(251,191,36,.25);font-size:.72rem">
                    <i class="fa-solid fa-clock me-1"></i>Chờ xác minh tick xanh
                </span>
            @endif

            <hr style="border-color:rgba(255,255,255,.08)">

            {{-- Social links display --}}
            @php $filtered = $user->getSocialLinksFiltered(); @endphp
            @if(count($filtered) > 0)
                <div class="d-flex flex-wrap justify-content-center gap-2 mt-2">
                    @foreach([
                        'facebook'  => ['fab fa-facebook',  '#1877f2'],
                        'instagram' => ['fab fa-instagram', '#e1306c'],
                        'youtube'   => ['fab fa-youtube',   '#ff0000'],
                        'tiktok'    => ['fab fa-tiktok',    '#ffffff'],
                        'spotify'   => ['fab fa-spotify',   '#1ed760'],
                        'website'   => ['fas fa-globe',     '#94a3b8'],
                    ] as $key => [$ico, $color])
                        @if(!empty($filtered[$key]))
                            <a href="{{ $filtered[$key] }}" target="_blank" rel="noopener"
                               class="d-flex align-items-center justify-content-center rounded-circle"
                               style="width:34px;height:34px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:{{ $color }};font-size:.85rem;text-decoration:none;transition:background .2s"
                               onmouseover="this.style.background='rgba(255,255,255,.1)'"
                               onmouseout="this.style.background='rgba(255,255,255,.05)'"
                               title="{{ ucfirst($key) }}">
                                <i class="{{ $ico }}"></i>
                            </a>
                        @endif
                    @endforeach
                </div>
            @else
                <p class="text-muted" style="font-size:.78rem">Chưa cập nhật liên kết MXH.</p>
            @endif

            <hr style="border-color:rgba(255,255,255,.08)">

            <div class="text-start small d-grid gap-2" style="font-size:.78rem">
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Tên thật</span>
                    <span class="text-white fw-medium">{{ $user->name }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Tham gia</span>
                    <span class="text-white fw-medium">{{ $user->created_at?->format('d/m/Y') }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Trạng thái</span>
                    <span class="text-success fw-medium">{{ $user->status }}</span>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('profile.edit') }}" class="btn btn-sm w-100"
                   style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#94a3b8;font-size:.78rem">
                    <i class="fa-solid fa-gear me-1"></i>Thông tin tài khoản
                </a>
            </div>
        </div>
    </div>

    {{-- ─────────────────────── RIGHT: Forms ─────────────────────────────── --}}
    <div class="col-12 col-xl-9 d-flex flex-column gap-4">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert border-0 mb-0"
                 style="background:rgba(16,185,129,.12);border-left:3px solid #34d399 !important;color:#34d399;border-radius:10px"
                 role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="alert border-0 mb-0"
                 style="background:rgba(59,130,246,.12);border-left:3px solid #60a5fa !important;color:#93c5fd;border-radius:10px"
                 role="alert">
                <i class="fa-solid fa-circle-info me-2"></i>{{ session('info') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="alert border-0 mb-0"
                 style="background:rgba(245,158,11,.12);border-left:3px solid #f59e0b !important;color:#fcd34d;border-radius:10px"
                 role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('warning') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert border-0 mb-0"
                 style="background:rgba(239,68,68,.1);border-left:3px solid #ef4444 !important;color:#fca5a5;border-radius:10px"
                 role="alert">
                <i class="fa-solid fa-circle-xmark me-2"></i>{{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert border-0 mb-0"
                 style="background:rgba(239,68,68,.1);border-left:3px solid #ef4444 !important;color:#fca5a5;border-radius:10px"
                 role="alert">
                <div class="fw-semibold mb-2">Có lỗi xảy ra, vui lòng kiểm tra lại:</div>
                <ul class="mb-0 ps-3" style="font-size:.85rem">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── FORM: Hồ sơ nghệ sĩ ── --}}
        <form method="POST"
              action="{{ route('artist.profile.update') }}"
              enctype="multipart/form-data"
              id="artistProfileForm">
            @csrf
            @method('PATCH')

            {{-- ── Thông tin cơ bản ── --}}
            <div class="ap-card">
                <div class="ap-section-title">
                    <i class="fa-solid fa-user-pen me-2" style="color:rgba(168,85,247,.7)"></i>
                    Thông tin nghệ sĩ
                </div>

                <div class="row g-3">

                    {{-- Nghệ danh --}}
                    <div class="col-12">
                        <label for="artist_name" class="form-label text-light fw-medium mb-1" style="font-size:.85rem">
                            Tên nghệ danh <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('artist_name') is-invalid @enderror"
                               id="artist_name"
                               name="artist_name"
                               value="{{ old('artist_name', $user->artist_name ?: $user->name) }}"
                               placeholder="Nghệ danh hiển thị công khai"
                               maxlength="100"
                               style="background:rgba(255,255,255,.05);border-color:rgba(100,116,139,.4);color:#f1f5f9;border-radius:10px">
                        @error('artist_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text" style="color:rgba(148,163,184,.55);font-size:.72rem">
                            Tên nghệ danh hiển thị với người nghe. Tên thật có thể khác.
                        </div>
                    </div>

                    {{-- Tiểu sử --}}
                    <div class="col-12">
                        <label for="bio" class="form-label text-light fw-medium mb-1" style="font-size:.85rem">
                            Tiểu sử
                        </label>
                        <textarea class="form-control @error('bio') is-invalid @enderror"
                                  id="bio"
                                  name="bio"
                                  rows="4"
                                  maxlength="1000"
                                  placeholder="Giới thiệu về bản thân, phong cách âm nhạc, câu chuyện nghệ thuật..."
                                  style="background:rgba(255,255,255,.05);border-color:rgba(100,116,139,.4);color:#f1f5f9;border-radius:10px;resize:vertical">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="d-flex justify-content-between mt-1">
                            <span class="form-text char-count">Mô tả ngắn gọn về bạn cho người nghe.</span>
                            <span class="char-count" id="bioCount">{{ strlen(old('bio', $user->bio ?? '')) }}/1000</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Hình ảnh ── --}}
            <div class="ap-card mt-4">
                <div class="ap-section-title">
                    <i class="fa-solid fa-image me-2" style="color:rgba(168,85,247,.7)"></i>
                    Hình ảnh
                </div>

                <div class="row g-4">
                    {{-- Avatar --}}
                    <div class="col-12 col-md-6">
                        <label class="form-label text-light fw-medium mb-2" style="font-size:.85rem">Ảnh đại diện</label>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <img src="{{ $avatarSrc }}" alt="Avatar hiện tại"
                                 id="avatarPreviewMain"
                                 style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid rgba(168,85,247,.4)">
                            <div class="flex-grow-1">
                                <input type="file"
                                       class="form-control form-control-sm @error('avatar') is-invalid @enderror"
                                       id="avatarInputSidebar"
                                       name="avatar"
                                       accept=".jpg,.jpeg,.png,.webp,.gif"
                                       style="background:rgba(255,255,255,.05);border-color:rgba(100,116,139,.4);color:#f1f5f9;border-radius:8px">
                                @error('avatar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text char-count mt-1">JPG, PNG, WEBP, GIF · Tối đa 3 MB</div>
                            </div>
                        </div>
                    </div>

                    {{-- Cover image --}}
                    <div class="col-12 col-md-6">
                        <label class="form-label text-light fw-medium mb-2" style="font-size:.85rem">Ảnh bìa kênh</label>
                        <input type="file"
                               class="form-control form-control-sm @error('cover_image') is-invalid @enderror"
                               id="coverImageInput"
                               name="cover_image"
                               accept=".jpg,.jpeg,.png,.webp"
                               style="background:rgba(255,255,255,.05);border-color:rgba(100,116,139,.4);color:#f1f5f9;border-radius:8px">
                        @error('cover_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text char-count mt-1">JPG, PNG, WEBP · Tối đa 5 MB · Đề xuất 1500×500</div>

                        @if($user->cover_image)
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox"
                                       id="remove_cover_image" name="remove_cover_image" value="1">
                                <label class="form-check-label" for="remove_cover_image"
                                       style="font-size:.78rem;color:#f87171">
                                    <i class="fa-solid fa-trash-can me-1"></i>Xóa ảnh bìa hiện tại
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Mạng xã hội ── --}}
            <div class="ap-card mt-4">
                <div class="ap-section-title">
                    <i class="fa-solid fa-share-nodes me-2" style="color:rgba(168,85,247,.7)"></i>
                    Liên kết mạng xã hội
                </div>

                <div class="row g-3 social-row">
                    @foreach([
                        ['facebook',  'fab fa-facebook',  '#1877f2', 'Facebook',  'https://facebook.com/ten-cua-ban'],
                        ['instagram', 'fab fa-instagram', '#e1306c', 'Instagram', 'https://instagram.com/ten-cua-ban'],
                        ['youtube',   'fab fa-youtube',   '#ff0000', 'YouTube',   'https://youtube.com/@kenh-cua-ban'],
                        ['tiktok',    'fab fa-tiktok',    '#ffffff', 'TikTok',    'https://tiktok.com/@ten-cua-ban'],
                        ['spotify',   'fab fa-spotify',   '#1ed760', 'Spotify',   'https://open.spotify.com/artist/...'],
                        ['website',   'fas fa-globe',     '#94a3b8', 'Website',   'https://website-cua-ban.com'],
                    ] as [$key, $ico, $color, $label, $placeholder])
                        <div class="col-12 col-md-6">
                            <label class="form-label mb-1" style="font-size:.78rem;color:rgba(148,163,184,.8)">{{ $label }}</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">
                                    <i class="{{ $ico }}" style="color:{{ $color }}"></i>
                                </span>
                                <input type="url"
                                       class="form-control @error('social_' . $key) is-invalid @enderror"
                                       name="social_{{ $key }}"
                                       value="{{ old('social_' . $key, $socialLinks[$key] ?? '') }}"
                                       placeholder="{{ $placeholder }}"
                                       style="background:rgba(255,255,255,.05);border-color:rgba(100,116,139,.4);color:#f1f5f9;border-radius:0 8px 8px 0">
                                @error('social_' . $key)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ── Actions ── --}}
            <div class="d-flex gap-3 flex-wrap mt-4">
                <button type="submit" class="btn"
                        style="background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;border:none;border-radius:10px;padding:.6rem 1.5rem;font-weight:600;font-size:.88rem">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Lưu hồ sơ nghệ sĩ
                </button>
                <a href="{{ route('artist.dashboard') }}"
                   class="btn"
                   style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#94a3b8;border-radius:10px;padding:.6rem 1.5rem;font-size:.88rem">
                    <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
                </a>
            </div>

        </form>

        {{-- ── Thông tin tài khoản cá nhân (link sang profile.edit) ── --}}
        <div class="ap-card d-flex align-items-center gap-3 flex-wrap">
            <div style="width:42px;height:42px;border-radius:12px;background:rgba(148,163,184,.08);border:1px solid rgba(148,163,184,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fa-solid fa-shield-halved" style="color:#94a3b8"></i>
            </div>
            <div class="flex-grow-1">
                <div class="text-white fw-semibold" style="font-size:.88rem">Thông tin tài khoản cá nhân</div>
                <div class="text-muted" style="font-size:.78rem">Cập nhật tên thật, email, SĐT, ngày sinh hoặc đổi mật khẩu.</div>
            </div>
            <a href="{{ route('profile.edit') }}"
               class="btn btn-sm"
               style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#cbd5e1;border-radius:8px;font-size:.8rem;white-space:nowrap">
                <i class="fa-solid fa-arrow-right me-1"></i>Quản lý tài khoản
            </a>
        </div>

    </div>{{-- /col right --}}
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Bio char counter ──────────────────────────────────────────────────── //
    const bioEl    = document.getElementById('bio');
    const bioCount = document.getElementById('bioCount');
    if (bioEl && bioCount) {
        bioEl.addEventListener('input', function () {
            const len = this.value.length;
            bioCount.textContent = len + '/1000';
            bioCount.style.color = len > 900 ? '#fca5a5' : 'rgba(148,163,184,.55)';
        });
    }

    // ── Avatar preview ───────────────────────────────────────────────────── //
    function previewImage(inputId, ...previewIds) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewIds.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.src = e.target.result;
                    });
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    previewImage('avatarInputSidebar', 'avatarPreviewImg', 'avatarPreviewMain');

    // ── Cover image preview ──────────────────────────────────────────────── //
    const coverInput       = document.getElementById('coverImageInput');
    const coverPreviewImg  = document.getElementById('coverPreviewImg');
    const coverPlaceholder = document.getElementById('coverPlaceholder');

    if (coverInput && coverPreviewImg) {
        coverInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    coverPreviewImg.src = e.target.result;
                    coverPreviewImg.style.display = 'block';
                    if (coverPlaceholder) coverPlaceholder.style.display = 'none';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // ── Remove cover checkbox hides preview ──────────────────────────────── //
    const removeCoverCb = document.getElementById('remove_cover_image');
    if (removeCoverCb && coverPreviewImg) {
        removeCoverCb.addEventListener('change', function () {
            if (this.checked) {
                coverPreviewImg.classList.add('d-none');
                if (coverPlaceholder) coverPlaceholder.style.display = '';
            } else {
                if (coverPreviewImg.src && coverPreviewImg.src !== window.location.href) {
                    coverPreviewImg.classList.remove('d-none');
                    if (coverPlaceholder) coverPlaceholder.style.display = 'none';
                }
            }
        });
    }
});
</script>
@endpush
