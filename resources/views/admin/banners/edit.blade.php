@extends('layouts.admin')

@section('title', 'Sửa Banner/Quảng cáo')
@section('page-title', 'Chỉnh sửa Banner')
@section('page-subtitle', 'Sửa đổi cấu hình và hình ảnh chiến dịch')

@section('content')

<div class="d-flex align-items-center mb-4">
    <a href="{{ route('admin.banners.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-2"></i>Quay lại danh sách
    </a>
</div>

<div class="row">
    <div class="col-12 col-xl-10">
        <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card bg-dark border-secondary border-opacity-25 shadow-sm">
                <div class="card-header bg-transparent border-secondary border-opacity-25 py-3 d-flex justify-content-between align-items-center">
                    <span class="text-white fw-semibold"><i class="fa-solid fa-pen-to-square me-2 text-warning"></i>Sửa thông tin Banner #{{ $banner->id }}</span>
                    <span class="badge" style="background:rgba(56,189,248,.1);color:#7dd3fc;border:1px solid rgba(56,189,248,.2)">
                        <i class="fa-solid fa-hand-pointer me-1"></i> {{ number_format($banner->clicks) }} Clicks
                    </span>
                               <div class="card-header bg-transparent border-secondary border-opacity-25 py-2 border-top d-flex justify-content-between align-items-center" style="font-size:.8rem">
                                   <div class="text-muted">
                                       @if($banner->creator)
                                           <i class="fa-solid fa-user me-1"></i>Tạo bởi: <strong>{{ $banner->creator->name }}</strong> • {{ $banner->created_at->format('d/m/Y H:i') }}
                                       @endif
                                   </div>
                                   <div class="text-muted">
                                       <i class="fa-solid fa-pen me-1"></i>Cập nhật: {{ $banner->updated_at->format('d/m/Y H:i') }}
                                   </div>
                               </div>
                </div>
                <div class="card-body p-4">
                    
                    <div class="row g-4">
                        {{-- Left Column: Info --}}
                        <div class="col-12 col-lg-7">
                            <h6 class="text-white mb-3" style="font-size: .9rem">1. Thiết lập chung</h6>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted small">Tên chiến dịch / banner <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control bg-dark border-secondary text-white shadow-none @error('title') is-invalid @enderror" 
                                       value="{{ old('title', $banner->title) }}" required>
                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Phân loại <span class="text-danger">*</span></label>
                                    <select name="type" id="bannerTypeSelect" class="form-select bg-dark border-secondary text-white shadow-none @error('type') is-invalid @enderror" required>
                                        <option value="hero" {{ old('type', $banner->type) === 'hero' ? 'selected' : '' }}>Banner Trang chủ</option>
                                        <option value="ad"   {{ old('type', $banner->type) === 'ad' ? 'selected' : '' }}>Quảng cáo Audio</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Url đích (Khi click) <span class="text-muted">(Optional)</span></label>
                                    <input type="url" name="target_url" class="form-control bg-dark border-secondary text-white shadow-none @error('target_url') is-invalid @enderror" 
                                           value="{{ old('target_url', $banner->target_url) }}" placeholder="https://...">
                                    @error('target_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <h6 class="text-white mt-4 mb-3" style="font-size: .9rem">2. Phát hành & Lên lịch</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Bắt đầu hiển thị <span class="text-muted">(Optional)</span></label>
                                    <input type="datetime-local" name="start_time" class="form-control bg-dark border-secondary text-white shadow-none" 
                                           value="{{ old('start_time', $banner->start_time ? $banner->start_time->format('Y-m-d\TH:i') : '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Kết thúc hiển thị <span class="text-muted">(Optional)</span></label>
                                    <input type="datetime-local" name="end_time" class="form-control bg-dark border-secondary text-white shadow-none" 
                                           value="{{ old('end_time', $banner->end_time ? $banner->end_time->format('Y-m-d\TH:i') : '') }}">
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Trạng thái <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select bg-dark border-secondary text-white shadow-none @error('status') is-invalid @enderror" required>
                                        <option value="active"   {{ old('status', $banner->status) === 'active' ? 'selected' : '' }}>Đang Bật (Active)</option>
                                        <option value="inactive" {{ old('status', $banner->status) === 'inactive' ? 'selected' : '' }}>Tắt / Tạm ngưng</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Thứ tự ưu tiên <span class="text-danger">*</span></label>
                                    <input type="number" name="order_index" class="form-control bg-dark border-secondary text-white shadow-none" 
                                           value="{{ old('order_index', $banner->order_index) }}" min="0" required>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Media --}}
                        <div class="col-12 col-lg-5">
                            <h6 class="text-white mb-3" style="font-size: .9rem">3. Hình ảnh (Media)</h6>
                            <div class="p-4 rounded border border-secondary border-opacity-50 text-center" style="background: rgba(255,255,255,.02); position: relative; overflow: hidden;">
                                
                                <div id="imagePreviewContainer" class="mb-3 position-relative">
                                    <img id="imagePreview" src="{{ asset($banner->image_path) }}" alt="Preview" class="img-fluid rounded w-100 shadow-sm" style="max-height: 200px; object-fit: contain; background: #000;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 rounded-circle d-none" id="btnRemoveImage" style="width:28px; height:28px; padding:0; display:flex; align-items:center; justify-content:center; box-shadow: 0 2px 5px rgba(0,0,0,0.5);">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>

                                <label class="form-label text-white d-block mb-1">Thay đổi ảnh nền</label>
                                <div class="form-text text-muted mb-3" style="font-size:.75rem">Tỷ lệ khuyên dùng 2.6:1<br/>Để trống nếu giữ nguyên ảnh cũ.</div>
                                <input type="file" name="image" id="imageInput" class="form-control bg-dark border-secondary text-white shadow-none @error('image') is-invalid @enderror" accept="image/*">
                                @error('image') <div class="invalid-feedback text-start mt-2">{{ $message }}</div> @enderror
                            </div>

                            <div class="mt-4 p-4 rounded border border-secondary border-opacity-50" style="background: rgba(255,255,255,.02);">
                                <label class="form-label text-white d-block mb-1">Tệp audio quảng cáo</label>
                                <div class="form-text text-muted mb-3" style="font-size:.75rem">Chỉ bắt buộc khi banner là loại Quảng cáo Audio. Để trống nếu giữ nguyên audio cũ.</div>
                                <input type="file" name="audio_file" id="audioInput" class="form-control bg-dark border-secondary text-white shadow-none @error('audio_file') is-invalid @enderror" accept="audio/*,.mp3,.wav,.ogg,.m4a,.webm">
                                @error('audio_file') <div class="invalid-feedback text-start mt-2">{{ $message }}</div> @enderror

                                @if($banner->hasAudioFile())
                                <div class="mt-3">
                                    <audio id="audioPreview" controls class="w-100">
                                        <source src="{{ $banner->audio_url }}">
                                    </audio>
                                </div>
                                @else
                                <audio id="audioPreview" controls class="w-100 mt-3 d-none"></audio>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer bg-transparent border-secondary border-opacity-25 py-3 d-flex justify-content-end gap-3">
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary px-4">Hủy bỏ</a>
                    <button type="submit" class="btn btn-warning px-4" style="background:rgba(251,191,36,.15);color:#fbbf24;border:1px solid rgba(251,191,36,.3)">
                        <i class="fa-solid fa-save me-2"></i>Cập nhật Banner
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const btnRemoveImage = document.getElementById('btnRemoveImage');
    const originalSrc = imagePreview.src;
    const audioInput = document.getElementById('audioInput');
    const audioPreview = document.getElementById('audioPreview');
    const bannerTypeSelect = document.getElementById('bannerTypeSelect');

    function syncAudioRequired() {
        audioInput.required = bannerTypeSelect.value === 'ad' && !audioPreview.querySelector('source');
    }

    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                btnRemoveImage.classList.remove('d-none');
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    btnRemoveImage.addEventListener('click', function() {
        imageInput.value = '';
        imagePreview.src = originalSrc;
        this.classList.add('d-none');
    });

    audioInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const url = URL.createObjectURL(this.files[0]);
            audioPreview.src = url;
            audioPreview.classList.remove('d-none');
        }
    });

    bannerTypeSelect.addEventListener('change', syncAudioRequired);
    syncAudioRequired();
});
</script>
@endpush
