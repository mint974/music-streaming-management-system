@extends('layouts.artist')

@section('title', 'Tạo album – Artist Studio')
@section('page-title', 'Tạo album mới')
@section('page-subtitle', 'Tập hợp bài hát thành một album')

@push('styles')
<style>
.sf-card { background:rgba(15,23,42,.85); border:1px solid rgba(255,255,255,.07); border-radius:16px; }
.sf-section-label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.09em; color:#64748b; margin-bottom:.9rem; }
.sf-input, .sf-select, .sf-textarea {
    background:rgba(30,41,59,.65); border:1px solid rgba(148,163,184,.2);
    color:#e2e8f0; border-radius:10px; font-size:.875rem; padding:.55rem .85rem;
    transition:border-color .2s, box-shadow .2s; width:100%;
}
.sf-input::placeholder, .sf-textarea::placeholder { color:#475569; }
.sf-input:focus, .sf-select:focus, .sf-textarea:focus {
    outline:none; border-color:rgba(168,85,247,.6);
    box-shadow:0 0 0 3px rgba(168,85,247,.13); background:rgba(30,41,59,.85);
}
.sf-select option { background:#0f172a; }
.sf-label { font-size:.82rem; color:#94a3b8; margin-bottom:.4rem; display:block; }
.sf-label .req { color:#f87171; }
#coverPreviewWrap { aspect-ratio:1; background:rgba(30,41,59,.4); border:1px solid rgba(255,255,255,.07); border-radius:12px; display:flex; align-items:center; justify-content:center; overflow:hidden; margin-bottom:.85rem; }
.btn-purple {
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    padding:10px 22px; background:linear-gradient(135deg,#7c3aed,#a855f7); color:#fff;
    border:none; border-radius:10px; font-size:.875rem; font-weight:600;
    cursor:pointer; box-shadow:0 4px 14px rgba(168,85,247,.35); transition:.2s; width:100%;
}
.btn-purple:hover { box-shadow:0 6px 20px rgba(168,85,247,.5); transform:translateY(-1px); }
.btn-cancel { display:block; text-align:center; color:#475569; font-size:.83rem; text-decoration:none; padding:.5rem; transition:.15s; }
.btn-cancel:hover { color:#94a3b8; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('artist.albums.store') }}" enctype="multipart/form-data">
@csrf

@if($errors->any())
    <div class="mb-4" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.28);color:#fca5a5;border-radius:14px;padding:1rem 1.25rem">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid fa-circle-exclamation"></i>
            <strong style="font-size:.9rem">Vui lòng kiểm tra lại:</strong>
        </div>
        <ul class="mb-0 ps-4" style="font-size:.83rem">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="sf-card p-4">
            <p class="sf-section-label"><i class="fa-solid fa-compact-disc me-1" style="color:#60a5fa"></i>Thông tin album</p>

            <div class="mb-3">
                <label class="sf-label">Tên album <span class="req">*</span></label>
                <input type="text" name="title" class="sf-input" value="{{ old('title') }}" placeholder="Nhập tên album…">
                @error('title') <p style="color:#fca5a5;font-size:.8rem;margin-top:.35rem;margin-bottom:0">{{ $message }}</p> @enderror
            </div>

            <div class="mb-3">
                <label class="sf-label">Mô tả</label>
                <textarea name="description" rows="5" class="sf-textarea" placeholder="Giới thiệu về album…" style="resize:vertical">{{ old('description') }}</textarea>
                @error('description') <p style="color:#fca5a5;font-size:.8rem;margin-top:.35rem;margin-bottom:0">{{ $message }}</p> @enderror
            </div>

            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="sf-label">Ngày phát hành</label>
                    <input type="date" name="released_date" class="sf-input" value="{{ old('released_date') }}">
                </div>
                <div class="col-sm-6">
                    <label class="sf-label">Trạng thái <span class="req">*</span></label>
                    <select name="status" class="sf-select">
                        <option value="draft"     {{ old('status','draft')=='draft'    ?'selected':'' }}>Bản nháp</option>
                        <option value="published" {{ old('status')=='published'        ?'selected':'' }}>Đã xuất bản</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Cover --}}
        <div class="sf-card mb-4 p-4">
            <p class="sf-section-label"><i class="fa-solid fa-image me-1" style="color:#f472b6"></i>Ảnh bìa album</p>
            <div id="coverPreviewWrap">
                <img id="coverPreview" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:none">
                <i id="coverIcon" class="fa-solid fa-compact-disc" style="font-size:3rem;color:#2a3a52"></i>
            </div>
            <label style="display:flex;align-items:center;justify-content:center;gap:8px;padding:.6rem;border-radius:10px;background:rgba(30,41,59,.5);border:1px solid rgba(255,255,255,.1);color:#94a3b8;font-size:.83rem;cursor:pointer;transition:.15s"
                   for="cover_image"
                   onmouseover="this.style.borderColor='rgba(168,85,247,.4)';this.style.color='#c084fc'"
                   onmouseout="this.style.borderColor='rgba(255,255,255,.1)';this.style.color='#94a3b8'">
                <i class="fa-solid fa-upload"></i> Chọn ảnh (JPG / PNG / WebP)
            </label>
            <input type="file" id="cover_image" name="cover_image" accept="image/*" class="d-none" onchange="previewCover(this)">
            @error('cover_image') <p style="color:#fca5a5;font-size:.8rem;margin-top:.5rem;margin-bottom:0">{{ $message }}</p> @enderror
        </div>

        {{-- Actions --}}
        <div class="sf-card p-4">
            <p class="sf-section-label"><i class="fa-solid fa-plus me-1"></i>Hoàn tất</p>
            <button type="submit" class="btn-purple mb-3">
                <i class="fa-solid fa-plus"></i> Tạo album
            </button>
            <a href="{{ route('artist.albums.index') }}" class="btn-cancel">Hủy bỏ</a>
        </div>
    </div>
</div>
</form>
@endsection

@push('scripts')
<script>
function previewCover(input) {
    const file = input.files[0]; if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('coverPreview');
        img.src = e.target.result; img.style.display = 'block';
        document.getElementById('coverIcon').style.display = 'none';
    };
    reader.readAsDataURL(file);
}
</script>
@endpush
