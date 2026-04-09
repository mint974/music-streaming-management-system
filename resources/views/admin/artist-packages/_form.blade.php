@php($formPrefix = $formPrefix ?? 'artist_pkg')

<div class="mb-3">
    <label class="form-label text-muted small">Tên gói <span class="text-danger">*</span></label>
    <input type="text" name="name"
           class="form-control form-control-sm bg-dark border-secondary text-white @error('name') is-invalid @enderror"
           placeholder="Gói Tiêu chuẩn"
           value="{{ old('name') }}">
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label text-muted small">Mô tả</label>
    <textarea name="description" rows="3"
              class="form-control form-control-sm bg-dark border-secondary text-white @error('description') is-invalid @enderror"
              placeholder="Mô tả ngắn về gói nghệ sĩ...">{{ old('description') }}</textarea>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label text-muted small">Quyền lợi (mỗi dòng 1 quyền lợi)</label>
    <textarea name="features_text" rows="5"
              class="form-control form-control-sm bg-dark border-secondary text-white @error('features_text') is-invalid @enderror"
              placeholder="Tải lên tối đa 30 bài hát / tháng&#10;Tạo tối đa 5 album&#10;Thống kê lượt nghe chi tiết">{{ old('features_text') }}</textarea>
    @error('features_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row g-3 mb-3">
    <div class="col-6">
        <label class="form-label text-muted small">Thời hạn (ngày) <span class="text-danger">*</span></label>
        <input type="number" name="duration_days" min="1" max="3650"
               class="form-control form-control-sm bg-dark border-secondary text-white @error('duration_days') is-invalid @enderror"
               placeholder="365"
               value="{{ old('duration_days') }}">
        @error('duration_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-6">
        <label class="form-label text-muted small">Giá (VNĐ) <span class="text-danger">*</span></label>
        <input type="number" name="price" min="0"
               class="form-control form-control-sm bg-dark border-secondary text-white @error('price') is-invalid @enderror"
               placeholder="249000"
               value="{{ old('price') }}">
        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="form-check form-switch">
    <input class="form-check-input" type="checkbox" name="is_active" id="{{ $formPrefix }}_is_active" value="1" checked>
    <label class="form-check-label text-muted small" for="{{ $formPrefix }}_is_active">Kích hoạt gói (hiển thị cho người dùng)</label>
</div>
