@extends('layouts.admin')

@section('title', 'Thêm Banner/Quảng cáo')
@section('page-title', 'Thêm Banner/Quảng cáo')
@section('page-subtitle', 'Upload hình ảnh và cấu hình hiển thị')

@section('content')

<div class="d-flex align-items-center mb-4">
    <a href="{{ route('admin.banners.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
    </a>
</div>

<div class="row">
    <div class="col-12 col-xl-10">
        <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card bg-dark border-secondary border-opacity-25 shadow-sm">
                <div class="card-header bg-transparent border-secondary border-opacity-25 py-3">
                    <span class="text-white fw-semibold"><i class="fa-solid fa-plus me-2 text-info"></i>Thông tin Banner / Quảng cáo</span>
                </div>
                <div class="card-body p-4">
                    
                    <div class="row g-4">
                        {{-- Left Column: Info --}}
                        <div class="col-12 col-lg-7">
                            <h6 class="text-white mb-3" style="font-size: .9rem">1. Thiết lập chung</h6>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted small">Tên chiến dịch / banner <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control bg-dark border-secondary text-white shadow-none @error('title') is-invalid @enderror" 
                                       value="{{ old('title') }}" placeholder="Ví dụ: Chiến dịch mùa hè 2024..." required>
                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Phân loại <span class="text-danger">*</span></label>
                                    <select name="type" id="bannerTypeSelect" class="form-select bg-dark border-secondary text-white shadow-none @error('type') is-invalid @enderror" required>
                                        <option value="hero" {{ old('type') === 'hero' ? 'selected' : '' }}>Banner Trang chủ (Sẽ hiển thị trên slide)</option>
                                        <option value="ad"   {{ old('type') === 'ad' ? 'selected' : '' }}>Quảng cáo Audio (Chèn cho User Free)</option>
                                    </select>
                                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Url đích (Khi click) <span class="text-muted">(Optional)</span></label>
                                    <input type="url" name="target_url" class="form-control bg-dark border-secondary text-white shadow-none @error('target_url') is-invalid @enderror" 
                                           value="{{ old('target_url') }}" placeholder="https://...">
                                    @error('target_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <h6 class="text-white mt-4 mb-3" style="font-size: .9rem">2. Phát hành & Lên lịch</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Bắt đầu hiển thị <span class="text-muted">(Optional)</span></label>
                                    <input type="datetime-local" name="start_time" class="form-control bg-dark border-secondary text-white shadow-none" 
                                           value="{{ old('start_time') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Kết thúc hiển thị <span class="text-muted">(Optional)</span></label>
                                    <input type="datetime-local" name="end_time" class="form-control bg-dark border-secondary text-white shadow-none" 
                                           value="{{ old('end_time') }}">
                                </div>
                                <div class="col-12 mt-1">
                                    <div class="form-text text-muted" style="font-size:.7rem">Không nhập thời gian nếu bạn muốn banner hiển thị ngay lập tức và không bao giờ tự tắt.</div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Trạng thái <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select bg-dark border-secondary text-white shadow-none @error('status') is-invalid @enderror" required>
                                        <option value="active"   {{ old('status') === 'active' ? 'selected' : '' }}>Đang Bật (Active)</option>
                                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Tắt / Tạm ngưng</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Thứ tự ưu tiên <span class="text-danger">*</span></label>
                                    <input type="number" name="order_index" class="form-control bg-dark border-secondary text-white shadow-none" 
                                           value="{{ old('order_index', 0) }}" min="0" required>
                                    <div class="form-text text-muted" style="font-size:.7rem">Số càng nhỏ, xếp càng đầu (Ví dụ: 0 là đầu tiên)</div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Media --}}
                        <div class="col-12 col-lg-5">
                            <h6 class="text-white mb-3" style="font-size: .9rem">3. Hình ảnh (Media)</h6>
                            <div class="p-4 rounded text-center border border-secondary border-opacity-50" style="background: rgba(255,255,255,.02); position: relative; overflow: hidden;">
                                
                                <div id="imagePlaceholder">
                                    <i class="fa-solid fa-cloud-arrow-up fa-3x mb-3 text-muted"></i>
                                    <label class="form-label text-white d-block mb-1">Upload khung hình <span class="text-danger">*</span></label>
                                    <div class="form-text text-muted mb-3" style="font-size:.75rem">Tỷ lệ khuyên dùng 2.6:1<br/>Dung lượng tối đa 5MB (JPEG, PNG, WEBP)</div>
                                </div>

                                <div id="imagePreviewContainer" class="d-none mb-3 position-relative">
                                    <img id="imagePreview" src="#" alt="Preview" class="img-fluid rounded w-100 shadow-sm" style="max-height: 200px; object-fit: contain; background: #000;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 rounded-circle" id="btnRemoveImage" style="width:28px; height:28px; padding:0; display:flex; align-items:center; justify-content:center; box-shadow: 0 2px 5px rgba(0,0,0,0.5);">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>

                                <input type="file" name="image" id="imageInput" class="form-control bg-dark border-secondary text-white shadow-none @error('image') is-invalid @enderror" 
                                       accept="image/*" required>
                                @error('image') <div class="invalid-feedback text-start mt-2">{{ $message }}</div> @enderror
                            </div>

                            <div class="mt-4 p-4 rounded border border-secondary border-opacity-50" style="background: rgba(255,255,255,.02);">
                                <label class="form-label text-white d-block mb-1">Tệp audio quảng cáo</label>
                                <div class="form-text text-muted mb-3" style="font-size:.75rem">Chỉ bắt buộc khi chọn loại Quảng cáo Audio. Hỗ trợ MP3, WAV, OGG, M4A, WEBM.</div>
                                <input type="file" name="audio_file" id="audioInput" class="form-control bg-dark border-secondary text-white shadow-none @error('audio_file') is-invalid @enderror" accept="audio/*,.mp3,.wav,.ogg,.m4a,.webm">
                                @error('audio_file') <div class="invalid-feedback text-start mt-2">{{ $message }}</div> @enderror

                                <audio id="audioPreview" controls class="w-100 mt-3 d-none"></audio>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer bg-transparent border-secondary border-opacity-25 py-3 d-flex justify-content-end gap-3">
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary px-4">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary px-4" style="background:rgba(99,102,241,.15);color:#a5b4fc;border:1px solid rgba(99,102,241,.3)">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i>Tạo mới Banner
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
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const imagePreview = document.getElementById('imagePreview');
    const imagePlaceholder = document.getElementById('imagePlaceholder');
    const btnRemoveImage = document.getElementById('btnRemoveImage');
    const audioInput = document.getElementById('audioInput');
    const audioPreview = document.getElementById('audioPreview');
    const bannerTypeSelect = document.getElementById('bannerTypeSelect');

    function syncAudioRequired() {
        audioInput.required = bannerTypeSelect.value === 'ad';
    }

    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreviewContainer.classList.remove('d-none');
                imagePlaceholder.style.display = 'none';
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    btnRemoveImage.addEventListener('click', function() {
        imageInput.value = '';
        imagePreviewContainer.classList.add('d-none');
        imagePlaceholder.style.display = 'block';
    });

    audioInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const url = URL.createObjectURL(this.files[0]);
            audioPreview.src = url;
            audioPreview.classList.remove('d-none');
        } else {
            audioPreview.src = '';
            audioPreview.classList.add('d-none');
        }
    });

    bannerTypeSelect.addEventListener('change', syncAudioRequired);
    syncAudioRequired();
});
</script>
@endpush
