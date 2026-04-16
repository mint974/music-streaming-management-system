@extends('layouts.artist')

@section('title', 'Quản lý Lời bài hát – Artist Studio')
@section('page-title', 'Lời bài hát: ' . $song->title)
@section('page-subtitle', 'Tải lên và đồng bộ lời hát theo thời gian thực (LRC)')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert"
         style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.28);color:#6ee7b7">
        <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert"
         style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.28);color:#fca5a5">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>Vui lòng kiểm tra lại thông tin nhập.
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <!-- CỘT TRÁI: DANH SÁCH LỜI BÀI HÁT -->
    <div class="col-12 col-xl-7">
        <div class="card bg-dark border-secondary bg-opacity-50">
            <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 text-white">
                    <i class="fa-solid fa-list-ul me-2 text-info"></i>Các phiên bản Lời
                </h5>
                <a href="{{ route('artist.songs.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>Trở lại
                </a>
            </div>
            <div class="card-body p-0">
                @if($lyrics->isEmpty())
                    <div class="text-center p-5 text-muted">
                        <i class="fa-solid fa-microphone-slash fa-3x mb-3 opacity-25"></i>
                        <p class="mb-0">Bài hát chạy chưa có dữ liệu lời nào.</p>
                        <p class="small">Hãy nhấn thêm mới phần tải lên.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 align-middle" style="background: transparent;">
                            <thead>
                                <tr class="text-muted small">
                                    <th class="ps-4">Tên phiên bản</th>
                                    <th class="ps-4">Loại</th>
                                    <th>Hiển thị</th>
                                    <th>Thời gian tạo</th>
                                    <th class="text-end pe-4">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lyrics as $lyric)
                                    <tr class="border-secondary border-opacity-25">
                                        <td class="ps-4 text-white small fw-semibold">
                                            {{ $lyric->name ?: ('Phiên bản #' . $lyric->id) }}
                                        </td>
                                        {{-- Loại --}}
                                        <td class="ps-4">
                                            @if($lyric->type === 'synced')
                                                <span class="badge" style="background:rgba(59,130,246,.15);color:#93c5fd;border:1px solid rgba(59,130,246,.3)"><i class="fa-solid fa-clock me-1"></i>Đồng bộ (LRC)</span>
                                            @else
                                                <span class="badge" style="background:rgba(107,114,128,.15);color:#d1d5db;border:1px solid rgba(107,114,128,.3)"><i class="fa-solid fa-align-left me-1"></i>Văn bản</span>
                                            @endif
                                            @if($lyric->is_default)
                                                <span class="badge ms-1" style="background:rgba(52,211,153,.15);color:#6ee7b7;border:1px solid rgba(52,211,153,.3)">Đang sử dụng</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if($lyric->is_visible)
                                                <span class="text-success small"><i class="fa-solid fa-eye me-1"></i>Đang hiển thị</span>
                                            @else
                                                <span class="text-muted small"><i class="fa-solid fa-eye-slash me-1"></i>Đang ẩn</span>
                                            @endif
                                        </td>

                                        <td class="text-muted small">
                                            {{ $lyric->created_at->format('d/m/Y H:i') }}
                                        </td>

                                        {{-- Thao tác --}}
                                        <td class="text-end pe-4">
                                            <div class="d-flex gap-2 justify-content-end">
                                                @if($lyric->type === 'synced')
                                                    <a href="{{ route('artist.songs.lyrics.preview', [$song, $lyric]) }}" class="btn btn-sm btn-outline-info" title="Xem trước / Xác nhận">
                                                        <i class="fa-regular fa-eye"></i>
                                                    </a>
                                                @endif
                                                @if(! $lyric->is_default)
                                                    <form method="POST" action="{{ route('artist.songs.lyrics.verify', [$song, $lyric]) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Xác nhận & Cài làm mặc định" data-confirm-message="Bạn muốn cài đặt lời này làm hiển thị chính?">
                                                            <i class="fa-solid fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('artist.songs.lyrics.toggleVisibility', [$song, $lyric]) }}">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm {{ $lyric->is_visible ? 'btn-outline-warning' : 'btn-outline-info' }}"
                                                        title="{{ $lyric->is_visible ? 'Ẩn phiên bản này khỏi trang người dùng' : 'Hiện phiên bản này trên trang người dùng' }}">
                                                        <i class="fa-solid {{ $lyric->is_visible ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('artist.songs.lyrics.destroy', [$song, $lyric]) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa" data-confirm-message="Xóa vĩnh viễn dữ liệu lời này?">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- CỘT PHẢI: FORM UPLOAD LYRICS -->
    <div class="col-12 col-xl-5">
        <div class="card bg-dark border-secondary bg-opacity-50">
            <div class="card-header border-secondary">
                <h5 class="card-title mb-0 text-white">
                    <i class="fa-solid fa-cloud-arrow-up me-2 text-primary"></i>Tải lên Lyric mới
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('artist.songs.lyrics.store', $song) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label text-muted small">Tên phiên bản lời <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            class="form-control bg-dark border-secondary text-white"
                            maxlength="100"
                            placeholder="VD: Lời Việt, Lời Trung, Bản live..."
                            required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-muted small">Định dạng Lyric</label>
                        <select name="lyric_source" id="lyric_source" class="form-select bg-dark border-secondary text-white" onchange="toggleLyricInputs()">
                            <option value="lrc_file">Tải lên File LRC (.lrc)</option>
                            <option value="lrc_text">Dán trực tiếp mã LRC</option>
                            <option value="plain">Văn bản thuần (Không đồng bộ thời gian)</option>
                        </select>
                        <div class="form-text mt-2 text-muted" style="font-size: 0.8rem">
                            Khuyến nghị sử dụng định dạng <b>LRC</b> để có hiệu ứng chữ chạy theo nhạc.
                        </div>
                    </div>

                    <!-- Khu vực Upload File -->
                    <div id="file_upload_area" class="mb-4">
                        <label class="form-label text-muted small">File LRC</label>
                        <input type="file" name="lrc_file" class="form-control bg-dark border-secondary text-white" accept=".lrc,.txt">
                    </div>

                    <!-- Khu vực Paste Text -->
                    <div id="text_paste_area" class="mb-4" style="display: none;">
                        <label class="form-label text-muted small">Nội dung Lời bài hát</label>
                        <textarea name="raw_text" class="form-control bg-dark border-secondary text-white" rows="10" placeholder="[...] Dán nội dung vào đây..."></textarea>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg,#7c3aed,#a855f7);border:none">
                            <i class="fa-solid fa-arrow-up-from-bracket me-2"></i>Lưu phiên bản
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleLyricInputs() {
        const source = document.getElementById('lyric_source').value;
        const fileArea = document.getElementById('file_upload_area');
        const textArea = document.getElementById('text_paste_area');
        
        if (source === 'lrc_file') {
            fileArea.style.display = 'block';
            textArea.style.display = 'none';
        } else {
            fileArea.style.display = 'none';
            textArea.style.display = 'block';
        }
    }
</script>
@endsection
