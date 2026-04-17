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

@if(session('error'))
    <div class="alert alert-dismissible fade show mb-4" role="alert"
         style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;border-radius:10px">
        <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
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
    {{-- CỘT TRÁI: DANH SÁCH LỜI BÀI HÁT --}}
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
                        <p class="mb-0">Bài hát chưa có dữ liệu lời nào.</p>
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
                                                <span class="badge" style="background:rgba(59,130,246,.15);color:#93c5fd;border:1px solid rgba(59,130,246,.3)">
                                                    <i class="fa-solid fa-clock me-1"></i>Đồng bộ (LRC)
                                                </span>
                                            @else
                                                <span class="badge" style="background:rgba(107,114,128,.15);color:#d1d5db;border:1px solid rgba(107,114,128,.3)">
                                                    <i class="fa-solid fa-align-left me-1"></i>Văn bản
                                                </span>
                                            @endif
                                            @if($lyric->is_default)
                                                <span class="badge ms-1" style="background:rgba(52,211,153,.15);color:#6ee7b7;border:1px solid rgba(52,211,153,.3)">
                                                    Đang sử dụng
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Hiển thị --}}
                                        <td>
                                            @if($lyric->is_visible)
                                                <span class="text-success small"><i class="fa-solid fa-eye me-1"></i>Đang hiển thị</span>
                                            @else
                                                <span class="text-muted small"><i class="fa-solid fa-eye-slash me-1"></i>Đang ẩn</span>
                                            @endif
                                        </td>

                                        {{-- Thời gian --}}
                                        <td class="text-muted small">
                                            {{ $lyric->created_at->format('d/m/Y H:i') }}
                                        </td>

                                        {{-- Thao tác --}}
                                        <td class="text-end pe-4">
                                            <div class="d-flex gap-2 justify-content-end">

                                                {{-- Xem trước (chỉ LRC) --}}
                                                @if($lyric->type === 'synced')
                                                    <a href="{{ route('artist.songs.lyrics.preview', [$song, $lyric]) }}"
                                                       class="btn btn-sm btn-outline-info"
                                                       title="Xem trước / Xác nhận">
                                                        <i class="fa-regular fa-eye"></i>
                                                    </a>
                                                @endif

                                                {{-- Cài làm mặc định (chỉ khi chưa là default) --}}
                                                @if(! $lyric->is_default)
                                                    <form method="POST"
                                                          action="{{ route('artist.songs.lyrics.verify', [$song, $lyric]) }}">
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn btn-sm btn-outline-success"
                                                                title="Cài làm phiên bản mặc định">
                                                            <i class="fa-solid fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- Bật / Tắt hiển thị --}}
                                                <form method="POST"
                                                      action="{{ route('artist.songs.lyrics.toggleVisibility', [$song, $lyric]) }}">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-sm {{ $lyric->is_visible ? 'btn-outline-warning' : 'btn-outline-info' }}"
                                                            title="{{ $lyric->is_visible ? 'Ẩn phiên bản này' : 'Hiện phiên bản này' }}">
                                                        <i class="fa-solid {{ $lyric->is_visible ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                                    </button>
                                                </form>

                                                {{-- Xóa --}}
                                                @if($lyrics->count() <= 1)
                                                    {{-- Chỉ còn 1 phiên bản → khóa nút xóa --}}
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-secondary"
                                                            disabled
                                                            title="Không thể xóa phiên bản lời duy nhất">
                                                        <i class="fa-solid fa-lock"></i>
                                                    </button>
                                                @else
                                                    {{-- Mở modal xác nhận xóa --}}
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Xóa phiên bản lời này"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteLyricModal"
                                                            data-action="{{ route('artist.songs.lyrics.destroy', [$song, $lyric]) }}"
                                                            data-name="{{ $lyric->name ?: ('Phiên bản #' . $lyric->id) }}">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                @endif

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

    {{-- CỘT PHẢI: FORM UPLOAD LYRICS --}}
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

{{-- ══ MODAL XÁC NHẬN XÓA PHIÊN BẢN LỜI ══ --}}
<div class="modal fade" id="deleteLyricModal" tabindex="-1" aria-labelledby="deleteLyricModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#1a1a2e;border:1px solid rgba(239,68,68,.35);border-radius:14px">

            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:42px;height:42px;border-radius:10px;background:rgba(239,68,68,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fa-solid fa-trash" style="color:#f87171;font-size:1.1rem"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 text-white fw-semibold" id="deleteLyricModalLabel">Xóa phiên bản lời</h5>
                        <p class="mb-0 text-muted" style="font-size:.8rem">Thao tác này không thể hoàn tác</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body pt-3">
                <p class="text-muted mb-1" style="font-size:.9rem">Bạn có chắc muốn xóa vĩnh viễn phiên bản lời:</p>
                <p id="deleteLyricName" class="text-white fw-semibold mb-0"
                   style="background:rgba(255,255,255,.05);border-radius:8px;padding:8px 12px;border:1px solid rgba(255,255,255,.08)"></p>
            </div>

            <div class="modal-footer border-0 pt-0 gap-2">
                <button type="button"
                        class="btn btn-sm px-4"
                        data-bs-dismiss="modal"
                        style="background:#1f2937;border:1px solid #374151;color:#9ca3af">
                    Hủy bỏ
                </button>

                {{-- Form xóa – action được cập nhật bởi JS bên dưới --}}
                <form id="deleteLyricForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="btn btn-sm px-4"
                            style="background:linear-gradient(135deg,#dc2626,#b91c1c);border:none;color:#fff">
                        <i class="fa-solid fa-trash me-1"></i>Xóa vĩnh viễn
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // ── Toggle khu vực nhập lời ──────────────────────────────────────────────
    function toggleLyricInputs() {
        const source = document.getElementById('lyric_source').value;
        document.getElementById('file_upload_area').style.display = source === 'lrc_file' ? 'block' : 'none';
        document.getElementById('text_paste_area').style.display  = source === 'lrc_file' ? 'none'  : 'block';
    }

    // ── Gán action & tên phiên bản vào modal xóa khi mở ─────────────────────
    document.getElementById('deleteLyricModal').addEventListener('show.bs.modal', function (event) {
        const btn    = event.relatedTarget;                        // nút đã click
        const action = btn.getAttribute('data-action');            // URL destroy
        const name   = btn.getAttribute('data-name');             // tên phiên bản

        document.getElementById('deleteLyricForm').action = action;
        document.getElementById('deleteLyricName').textContent = name;
    });
</script>
@endpush
