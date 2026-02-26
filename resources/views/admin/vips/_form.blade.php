{{--
  Reusable form fields for VIP create/edit.
  Pass $hideIdField = true to suppress the ID field (used in edit modal).
--}}

@unless($hideIdField ?? false)
<div class="mb-3">
    <label class="form-label text-muted small">
        ID gói <span class="text-danger">*</span>
        <span class="ms-1 text-muted" style="font-size:.7rem">(slug, vd: monthly / quarterly / yearly)</span>
    </label>
    <input type="text" name="id"
           class="form-control form-control-sm bg-dark border-secondary text-white @error('id') is-invalid @enderror"
           placeholder="monthly"
           value="{{ old('id') }}">
    @error('id') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
@endunless

<div class="mb-3">
    <label class="form-label text-muted small">Tên gói <span class="text-danger">*</span></label>
    <input type="text" name="title"
           class="form-control form-control-sm bg-dark border-secondary text-white @error('title') is-invalid @enderror"
           placeholder="Premium Tháng"
           value="{{ old('title') }}">
    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label text-muted small">Mô tả quyền lợi</label>
    <textarea name="description" rows="3"
              class="form-control form-control-sm bg-dark border-secondary text-white @error('description') is-invalid @enderror"
              placeholder="Nghe nhạc không giới hạn, tải offline...">{{ old('description') }}</textarea>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row g-3 mb-3">
    <div class="col-6">
        <label class="form-label text-muted small">Thời hạn (ngày) <span class="text-danger">*</span></label>
        <input type="number" name="duration_days" min="1" max="3650"
               class="form-control form-control-sm bg-dark border-secondary text-white @error('duration_days') is-invalid @enderror"
               placeholder="30"
               value="{{ old('duration_days') }}">
        @error('duration_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-6">
        <label class="form-label text-muted small">Giá (VNĐ) <span class="text-danger">*</span></label>
        <input type="number" name="price" min="0"
               class="form-control form-control-sm bg-dark border-secondary text-white @error('price') is-invalid @enderror"
               placeholder="49000"
               value="{{ old('price') }}">
        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="form-check form-switch">
    <input class="form-check-input" type="checkbox" name="is_active" id="is_active_field" value="1" checked>
    <label class="form-check-label text-muted small" for="is_active_field">Kích hoạt gói (hiển thị cho người dùng)</label>
</div>
