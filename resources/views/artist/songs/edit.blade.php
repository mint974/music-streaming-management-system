@extends('layouts.artist')

@section('title', 'Chỉnh sửa bài hát – Artist Studio')
@section('page-title', 'Chỉnh sửa bài hát')
@section('page-subtitle', '{{ $song->title }}')

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
.sf-select { appearance:auto; }
.sf-label { font-size:.82rem; color:#94a3b8; margin-bottom:.4rem; display:block; }
.sf-label .req { color:#f87171; }
.sf-tabs .nav-link { color:#64748b; border:none; border-bottom:2px solid transparent; padding:.6rem 1rem; font-size:.875rem; background:transparent; }
.sf-tabs .nav-link:hover { color:#94a3b8; }
.sf-tabs .nav-link.active { color:#c084fc !important; border-bottom-color:#a855f7 !important; }
.tag-chip .chip-label {
    display:inline-block; padding:5px 13px; border-radius:20px;
    border:1px solid rgba(148,163,184,.2); background:rgba(30,41,59,.5);
    color:#94a3b8; font-size:.8rem; cursor:pointer; transition:.15s; user-select:none;
}
.tag-chip input:checked + .chip-label {
    background:rgba(168,85,247,.2); border-color:rgba(168,85,247,.55); color:#c084fc;
}
.btn-purple {
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    padding:10px 22px; background:linear-gradient(135deg,#7c3aed,#a855f7); color:#fff;
    border:none; border-radius:10px; font-size:.875rem; font-weight:600;
    cursor:pointer; box-shadow:0 4px 14px rgba(168,85,247,.35); transition:.2s; width:100%;
}
.btn-purple:hover { box-shadow:0 6px 20px rgba(168,85,247,.5); transform:translateY(-1px); }
.btn-cancel { display:block; text-align:center; color:#475569; font-size:.83rem; text-decoration:none; padding:.5rem; transition:.15s; }
.btn-cancel:hover { color:#94a3b8; }
.current-file-row { display:flex; align-items:center; gap:12px; padding:12px 14px; border-radius:10px; background:rgba(168,85,247,.08); border:1px solid rgba(168,85,247,.18); margin-bottom:.85rem; }
#coverPreviewWrap { aspect-ratio:1; background:rgba(30,41,59,.4); border:1px solid rgba(255,255,255,.07); border-radius:12px; display:flex; align-items:center; justify-content:center; overflow:hidden; margin-bottom:.85rem; }
.vip-check-row { display:flex; align-items:center; gap:10px; padding:.7rem 1rem; border-radius:10px; background:rgba(251,191,36,.06); border:1px solid rgba(251,191,36,.15); cursor:pointer; }
.vip-check-row input { accent-color:#fbbf24; width:16px; height:16px; }
.remove-cover-row { display:flex; align-items:center; gap:8px; padding:.5rem .75rem; border-radius:8px; background:rgba(239,68,68,.08); border:1px solid rgba(239,68,68,.18); cursor:pointer; margin-bottom:.65rem; }
.remove-cover-row input { accent-color:#f87171; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('artist.songs.update', $song) }}" enctype="multipart/form-data">
@csrf @method('PATCH')

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
    {{-- ─── Left column ─── --}}
    <div class="col-lg-8">

        {{-- Current audio + replace --}}
        <div class="sf-card mb-4 p-4">
            <p class="sf-section-label"><i class="fa-solid fa-file-audio me-1" style="color:#a855f7"></i>File âm nhạc</p>
            @if($song->file_path)
                <div class="current-file-row">
                    <i class="fa-solid fa-music" style="color:#a855f7;font-size:1.3rem;flex-shrink:0"></i>
                    <div style="min-width:0;flex:1">
                        <div style="color:#e2e8f0;font-size:.87rem;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ basename($song->file_path) }}</div>
                        <div style="color:#64748b;font-size:.75rem;margin-top:2px">{{ $song->fileSizeFormatted() }} &middot; {{ $song->durationFormatted() }}</div>
                    </div>
                    <i class="fa-solid fa-circle-check" style="color:#34d399;flex-shrink:0"></i>
                </div>
            @endif
            <label style="display:flex;align-items:center;justify-content:center;gap:8px;padding:.6rem;border-radius:10px;background:rgba(30,41,59,.5);border:1px solid rgba(255,255,255,.1);color:#94a3b8;font-size:.83rem;cursor:pointer;transition:.15s"
                   for="audio_file"
                   onmouseover="this.style.borderColor='rgba(168,85,247,.4)';this.style.color='#c084fc'"
                   onmouseout="this.style.borderColor='rgba(255,255,255,.1)';this.style.color='#94a3b8'">
                <i class="fa-solid fa-arrows-rotate"></i> Thay thế file nhạc (MP3 / FLAC / WAV, max 100 MB)
            </label>
            <input type="file" id="audio_file" name="audio_file" accept=".mp3,.flac,.wav" class="d-none"
                   onchange="document.getElementById('newAudioName').textContent='Đã chọn: '+this.files[0].name">
            <div id="newAudioName" style="font-size:.8rem;color:#94a3b8;margin-top:.5rem"></div>
            @error('audio_file')
                <p style="color:#fca5a5;font-size:.8rem;margin-top:.5rem;margin-bottom:0">{{ $message }}</p>
            @enderror
        </div>

        {{-- Basic info --}}
        <div class="sf-card mb-4 p-4">
            <p class="sf-section-label"><i class="fa-solid fa-circle-info me-1" style="color:#60a5fa"></i>Thông tin cơ bản</p>

            <div class="mb-3">
                <label class="sf-label">Tên bài hát <span class="req">*</span></label>
                <input type="text" name="title" class="sf-input" value="{{ old('title', $song->title) }}">
                @error('title') <p style="color:#fca5a5;font-size:.8rem;margin-top:.35rem;margin-bottom:0">{{ $message }}</p> @enderror
            </div>

            <div class="mb-3">
                <label class="sf-label">Tác giả / Nhạc sĩ</label>
                <input type="text" name="author" class="sf-input" value="{{ old('author', $song->author) }}" placeholder="Tên nhạc sĩ…">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label class="sf-label">Thể loại</label>
                    <select name="genre_id" class="sf-select">
                        <option value="">— Chọn thể loại —</option>
                        @foreach($genres as $g)
                            <option value="{{ $g->id }}" {{ old('genre_id',$song->genre_id)==$g->id?'selected':'' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="sf-label">Album</label>
                    <select name="album_id" class="sf-select">
                        <option value="">— Không thuộc album —</option>
                        @foreach($albums as $a)
                            <option value="{{ $a->id }}" {{ old('album_id',$song->album_id)==$a->id?'selected':'' }}>{{ $a->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="sf-label">Ngày phát hành</label>
                    <input type="date" name="released_date" class="sf-input"
                           value="{{ old('released_date', $song->released_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-sm-6">
                    <label class="sf-label">Trạng thái <span class="req">*</span></label>
                    <select name="status" class="sf-select">
                        <option value="draft"     {{ old('status',$song->status)=='draft'    ?'selected':'' }}>Bản nháp</option>
                        <option value="pending"   {{ old('status',$song->status)=='pending'  ?'selected':'' }}>Chờ duyệt</option>
                        <option value="published" {{ old('status',$song->status)=='published'?'selected':'' }}>Đã xuất bản</option>
                    </select>
                </div>
            </div>

            <label class="vip-check-row">
                <input type="checkbox" name="is_vip" value="1" {{ old('is_vip',$song->is_vip)?'checked':'' }}>
                <i class="fa-solid fa-crown" style="color:#fbbf24"></i>
                <div>
                    <div style="color:#fde68a;font-size:.87rem;font-weight:600">Đây là bài hát VIP</div>
                    <div style="color:#78716c;font-size:.75rem">Chỉ thành viên Premium mới nghe được</div>
                </div>
            </label>
        </div>

        {{-- Lyrics + Tags tabs --}}
        <div class="sf-card mb-4" style="overflow:hidden">
            <ul class="nav sf-tabs px-4 pt-1" style="border-bottom:1px solid rgba(255,255,255,.07)">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#lyricsPane" type="button">
                        <i class="fa-solid fa-align-left me-2"></i>Lời bài hát
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tagsPane" type="button">
                        <i class="fa-solid fa-tags me-2"></i>Tags
                    </button>
                </li>
            </ul>
            <div class="tab-content p-4">
                <div class="tab-pane fade show active" id="lyricsPane">
                    <div class="d-flex gap-3 mb-3">
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
                            <input type="radio" name="lyrics_type" value="plain"
                                   {{ old('lyrics_type',$song->lyrics_type)=='plain'?'checked':'' }} style="accent-color:#a855f7">
                            <span style="color:#94a3b8;font-size:.85rem">Văn bản thường</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
                            <input type="radio" name="lyrics_type" value="lrc"
                                   {{ old('lyrics_type',$song->lyrics_type)=='lrc'?'checked':'' }} style="accent-color:#a855f7">
                            <span style="color:#94a3b8;font-size:.85rem">LRC <span style="color:#64748b;font-size:.75rem">(đồng bộ thời gian)</span></span>
                        </label>
                    </div>
                    <textarea name="lyrics" rows="12" class="sf-textarea font-monospace"
                              style="resize:vertical;font-size:.82rem">{{ old('lyrics', $song->lyrics) }}</textarea>
                    <p style="color:#475569;font-size:.73rem;margin-top:.4rem;margin-bottom:0">
                        Định dạng LRC: <code style="color:#a855f7">[mm:ss.xx] Lời</code>
                    </p>
                </div>

                <div class="tab-pane fade" id="tagsPane">
                    @php
                        $editMood     = old('tags.mood',     $song->getMoodTags());
                        $editActivity = old('tags.activity', $song->getActivityTags());
                        $editTopic    = old('tags.topic',    $song->getTopicTags());
                    @endphp
                    <div class="mb-4">
                        <p class="sf-section-label" style="color:#c084fc"><i class="fa-solid fa-face-smile me-1"></i>Tâm trạng</p>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(\App\Models\Song::$MOOD_TAGS as $k => $v)
                                <label class="tag-chip">
                                    <input type="checkbox" name="tags[mood][]" value="{{ $k }}" class="d-none" {{ in_array($k,$editMood)?'checked':'' }}>
                                    <span class="chip-label">{{ $v }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-4">
                        <p class="sf-section-label" style="color:#34d399"><i class="fa-solid fa-person-running me-1"></i>Hoạt động</p>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(\App\Models\Song::$ACTIVITY_TAGS as $k => $v)
                                <label class="tag-chip">
                                    <input type="checkbox" name="tags[activity][]" value="{{ $k }}" class="d-none" {{ in_array($k,$editActivity)?'checked':'' }}>
                                    <span class="chip-label">{{ $v }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <p class="sf-section-label" style="color:#60a5fa"><i class="fa-solid fa-hashtag me-1"></i>Chủ đề</p>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(\App\Models\Song::$TOPIC_TAGS as $k => $v)
                                <label class="tag-chip">
                                    <input type="checkbox" name="tags[topic][]" value="{{ $k }}" class="d-none" {{ in_array($k,$editTopic)?'checked':'' }}>
                                    <span class="chip-label">{{ $v }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /col-lg-8 --}}

    {{-- ─── Right sidebar ─── --}}
    <div class="col-lg-4">

        {{-- Cover image --}}
        <div class="sf-card mb-4 p-4">
            <p class="sf-section-label"><i class="fa-solid fa-image me-1" style="color:#f472b6"></i>Ảnh bìa</p>
            <div id="coverPreviewWrap">
                <img id="coverPreview" src="{{ $song->getCoverUrl() }}" alt=""
                     style="width:100%;height:100%;object-fit:cover;{{ !$song->cover_image ? 'display:none' : '' }}">
                <i id="coverIcon" class="fa-regular fa-image"
                   style="font-size:3rem;color:#2a3a52;{{ $song->cover_image ? 'display:none' : '' }}"></i>
            </div>
            @if($song->cover_image)
                <label class="remove-cover-row" for="removeCoverCheck">
                    <input type="checkbox" name="remove_cover" value="1" id="removeCoverCheck">
                    <i class="fa-solid fa-trash" style="color:#f87171;font-size:.8rem"></i>
                    <span style="color:#fca5a5;font-size:.82rem">Ảnh bìa hiện tại – xóa</span>
                </label>
            @endif
            <label style="display:flex;align-items:center;justify-content:center;gap:8px;padding:.6rem;border-radius:10px;background:rgba(30,41,59,.5);border:1px solid rgba(255,255,255,.1);color:#94a3b8;font-size:.83rem;cursor:pointer;transition:.15s"
                   for="cover_image"
                   onmouseover="this.style.borderColor='rgba(168,85,247,.4)';this.style.color='#c084fc'"
                   onmouseout="this.style.borderColor='rgba(255,255,255,.1)';this.style.color='#94a3b8'">
                <i class="fa-solid fa-upload"></i> Thay ảnh bìa
            </label>
            <input type="file" id="cover_image" name="cover_image" accept="image/*" class="d-none" onchange="handleCoverSelect(this)">
            @error('cover_image')
                <p style="color:#fca5a5;font-size:.8rem;margin-top:.5rem;margin-bottom:0">{{ $message }}</p>
            @enderror
        </div>

        {{-- Save --}}
        <div class="sf-card p-4">
            <p class="sf-section-label"><i class="fa-solid fa-floppy-disk me-1"></i>Cập nhật</p>
            <button type="submit" class="btn-purple mb-3">
                <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
            </button>
            <a href="{{ route('artist.songs.index') }}" class="btn-cancel">Huỷ bỏ</a>
        </div>

    </div>{{-- /col-lg-4 --}}
</div>
</form>
@endsection

@push('scripts')
<script>
function handleCoverSelect(input) {
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

@section('content')
<form method="POST" action="{{ route('artist.songs.update', $song) }}" enctype="multipart/form-data">
@csrf @method('PATCH')

{{-- Errors --}}
@if($errors->any())
    <div class="alert mb-4" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;border-radius:10px">
        <i class="fa-solid fa-circle-exclamation me-2"></i><strong>Vui lòng kiểm tra lại:</strong>
        <ul class="mb-0 mt-2 ps-4">
            @foreach($errors->all() as $e) <li style="font-size:.87rem">{{ $e }}</li> @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">

        {{-- Audio replacement --}}
        <div class="card mb-4" style="background:#111827;border:1px solid #1f2937;border-radius:16px">
            <div class="card-body p-4">
                <h6 class="text-white fw-semibold mb-3"><i class="fa-solid fa-file-audio me-2" style="color:#a855f7"></i>File âm nhạc</h6>
                @if($song->file_path)
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-3" style="background:#1a1a2e;border:1px solid #2a2a45">
                        <i class="fa-solid fa-music" style="color:#a855f7;font-size:1.4rem"></i>
                        <div>
                            <div style="color:#f1f5f9;font-size:.88rem">{{ basename($song->file_path) }}</div>
                            <div style="color:#6b7280;font-size:.75rem">{{ $song->fileSizeFormatted() }} &middot; {{ $song->durationFormatted() }}</div>
                        </div>
                    </div>
                @endif
                <label class="btn btn-sm" style="background:#1a1a2e;border:1px solid #2a2a45;color:#94a3b8" for="audio_file">
                    <i class="fa-solid fa-arrows-rotate me-2"></i>Thay thế file nhạc (MP3/FLAC/WAV, max 100 MB)
                </label>
                <input type="file" id="audio_file" name="audio_file" accept=".mp3,.flac,.wav" class="d-none"
                       onchange="document.getElementById('newAudioName').textContent='Đã chọn: '+this.files[0].name">
                <div id="newAudioName" class="mt-2 text-muted" style="font-size:.8rem"></div>
                @error('audio_file') <div class="text-danger mt-1" style="font-size:.82rem">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Basic info --}}
        <div class="card mb-4" style="background:#111827;border:1px solid #1f2937;border-radius:16px">
            <div class="card-body p-4">
                <h6 class="text-white fw-semibold mb-3"><i class="fa-solid fa-circle-info me-2" style="color:#60a5fa"></i>Thông tin cơ bản</h6>

                <div class="mb-3">
                    <label class="form-label" style="color:#94a3b8;font-size:.85rem">Tên bài hát <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $song->title) }}"
                           style="background:#1a1a2e;border-color:#2a2a45;color:#e2e8f0">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" style="color:#94a3b8;font-size:.85rem">Tác giả / Nhạc sĩ</label>
                    <input type="text" name="author" class="form-control @error('author') is-invalid @enderror"
                           value="{{ old('author', $song->author) }}"
                           style="background:#1a1a2e;border-color:#2a2a45;color:#e2e8f0">
                    @error('author') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" style="color:#94a3b8;font-size:.85rem">Thể loại</label>
                        <select name="genre_id" class="form-select" style="background:#1a1a2e;border-color:#2a2a45;color:#e2e8f0">
                            <option value="">-- Chọn thể loại --</option>
                            @foreach($genres as $g)
                                <option value="{{ $g->id }}" {{ old('genre_id', $song->genre_id)==$g->id?'selected':'' }}>{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="color:#94a3b8;font-size:.85rem">Album</label>
                        <select name="album_id" class="form-select" style="background:#1a1a2e;border-color:#2a2a45;color:#e2e8f0">
                            <option value="">-- Không thuộc album --</option>
                            @foreach($albums as $a)
                                <option value="{{ $a->id }}" {{ old('album_id', $song->album_id)==$a->id?'selected':'' }}>{{ $a->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="color:#94a3b8;font-size:.85rem">Ngày phát hành</label>
                        <input type="date" name="released_date" class="form-control"
                               value="{{ old('released_date', $song->released_date?->format('Y-m-d')) }}"
                               style="background:#1a1a2e;border-color:#2a2a45;color:#e2e8f0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="color:#94a3b8;font-size:.85rem">Trạng thái <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" style="background:#1a1a2e;border-color:#2a2a45;color:#e2e8f0">
                            <option value="draft" {{ old('status',$song->status)=='draft'?'selected':'' }}>Bản nháp</option>
                            <option value="pending" {{ old('status',$song->status)=='pending'?'selected':'' }}>Chờ duyệt</option>
                            <option value="published" {{ old('status',$song->status)=='published'?'selected':'' }}>Đã xuất bản</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_vip" value="1" id="isVip"
                               {{ old('is_vip', $song->is_vip) ? 'checked' : '' }}
                               style="background:#1a1a2e;border-color:#2a2a45">
                        <label class="form-check-label" for="isVip" style="color:#94a3b8;font-size:.87rem">
                            <i class="fa-solid fa-crown me-1" style="color:#fbbf24"></i>Chỉ dành cho thành viên VIP
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lyrics + Tags tabs --}}
        <div class="card mb-4" style="background:#111827;border:1px solid #1f2937;border-radius:16px">
            <div class="card-body p-0">
                <ul class="nav nav-tabs px-4 pt-3" id="editTabs" style="border-bottom:1px solid #1f2937">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#lyricsPane" type="button"
                                style="color:#94a3b8;border:none;border-bottom:2px solid transparent">
                            <i class="fa-solid fa-align-left me-1"></i>Lời bài hát
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tagsPane" type="button"
                                style="color:#94a3b8;border:none;border-bottom:2px solid transparent">
                            <i class="fa-solid fa-tags me-1"></i>Tags
                        </button>
                    </li>
                </ul>
                <div class="tab-content p-4">
                    <div class="tab-pane fade show active" id="lyricsPane">
                        <div class="mb-3">
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="lyrics_type" value="plain" id="lyricsPlain"
                                           {{ old('lyrics_type',$song->lyrics_type)=='plain'?'checked':'' }}>
                                    <label class="form-check-label" for="lyricsPlain" style="color:#94a3b8;font-size:.87rem">Văn bản thường</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="lyrics_type" value="lrc" id="lyricsLrc"
                                           {{ old('lyrics_type',$song->lyrics_type)=='lrc'?'checked':'' }}>
                                    <label class="form-check-label" for="lyricsLrc" style="color:#94a3b8;font-size:.87rem">LRC <span style="font-size:.73rem;color:#6b7280">(đồng bộ thời gian)</span></label>
                                </div>
                            </div>
                        </div>
                        <textarea name="lyrics" rows="12" class="form-control font-monospace"
                                  style="background:#0d1117;border-color:#2a2a45;color:#e2e8f0;font-size:.85rem;resize:vertical">{{ old('lyrics', $song->lyrics) }}</textarea>
                        <p class="text-muted mt-1" style="font-size:.74rem">Định dạng LRC: <code style="color:#a855f7">[mm:ss.xx] Lời</code></p>
                    </div>

                    <div class="tab-pane fade" id="tagsPane">
                        @php
                            $editMood     = old('tags.mood', $song->getMoodTags());
                            $editActivity = old('tags.activity', $song->getActivityTags());
                            $editTopic    = old('tags.topic', $song->getTopicTags());
                        @endphp

                        <div class="mb-4">
                            <label class="form-label fw-semibold" style="color:#c084fc;font-size:.85rem"><i class="fa-solid fa-face-smile me-1"></i>Tâm trạng</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach(\App\Models\Song::$MOOD_TAGS as $key => $label)
                                    <label class="tag-chip">
                                        <input type="checkbox" name="tags[mood][]" value="{{ $key }}" class="d-none tag-check"
                                               {{ in_array($key, $editMood)?'checked':'' }}>
                                        <span class="chip-label">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold" style="color:#34d399;font-size:.85rem"><i class="fa-solid fa-person-running me-1"></i>Hoạt động</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach(\App\Models\Song::$ACTIVITY_TAGS as $key => $label)
                                    <label class="tag-chip">
                                        <input type="checkbox" name="tags[activity][]" value="{{ $key }}" class="d-none tag-check"
                                               {{ in_array($key, $editActivity)?'checked':'' }}>
                                        <span class="chip-label">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold" style="color:#60a5fa;font-size:.85rem"><i class="fa-solid fa-hashtag me-1"></i>Chủ đề</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach(\App\Models\Song::$TOPIC_TAGS as $key => $label)
                                    <label class="tag-chip">
                                        <input type="checkbox" name="tags[topic][]" value="{{ $key }}" class="d-none tag-check"
                                               {{ in_array($key, $editTopic)?'checked':'' }}>
                                        <span class="chip-label">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Right sidebar --}}
    <div class="col-lg-4">

        {{-- Cover image --}}
        <div class="card mb-4" style="background:#111827;border:1px solid #1f2937;border-radius:16px">
            <div class="card-body p-4">
                <h6 class="text-white fw-semibold mb-3"><i class="fa-solid fa-image me-2" style="color:#f472b6"></i>Ảnh bìa</h6>
                <div class="rounded-3 overflow-hidden mb-3" style="aspect-ratio:1;background:#1a1a2e;display:flex;align-items:center;justify-content:center">
                    <img id="coverPreview" src="{{ $song->getCoverUrl() }}" alt=""
                         style="width:100%;height:100%;object-fit:cover;{{ !$song->cover_image ? 'display:none' : '' }}">
                    <i id="coverIcon" class="fa-solid fa-image" style="font-size:3rem;color:#2a2a45;{{ $song->cover_image ? 'display:none' : '' }}"></i>
                </div>
                @if($song->cover_image)
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="remove_cover" value="1" id="removeCover">
                        <label class="form-check-label" for="removeCover" style="color:#f87171;font-size:.83rem">Xóa ảnh bìa hiện tại</label>
                    </div>
                @endif
                <label class="btn btn-sm w-100" style="background:#1a1a2e;border:1px solid #2a2a45;color:#94a3b8" for="cover_image">
                    <i class="fa-solid fa-upload me-2"></i>Thay ảnh bìa
                </label>
                <input type="file" id="cover_image" name="cover_image" accept="image/*" class="d-none"
                       onchange="handleCoverSelect(this)">
                @error('cover_image') <div class="text-danger mt-2" style="font-size:.82rem">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Actions --}}
        <div class="card" style="background:#111827;border:1px solid #1f2937;border-radius:16px">
            <div class="card-body p-4">
                <h6 class="text-white fw-semibold mb-3"><i class="fa-solid fa-gear me-2" style="color:#94a3b8"></i>Lưu thay đổi</h6>
                <button type="submit" class="btn btn-gradient-purple w-100 mb-2">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Cập nhật bài hát
                </button>
                <a href="{{ route('artist.songs.index') }}" class="btn btn-sm w-100"
                   style="background:transparent;border:none;color:#6b7280">
                    Hủy
                </a>
            </div>
        </div>

    </div>
</div>
</form>
@endsection

@push('styles')
<style>
.tag-chip .chip-label {
    display:inline-block;padding:4px 12px;border-radius:20px;border:1px solid #2a2a45;
    background:#1a1a2e;color:#94a3b8;font-size:.8rem;cursor:pointer;transition:.15s;user-select:none;
}
.tag-chip input:checked + .chip-label {
    background:rgba(168,85,247,.18);border-color:rgba(168,85,247,.5);color:#c084fc;
}
#editTabs .nav-link.active { color:#c084fc !important; border-bottom-color:#a855f7 !important; }
</style>
@endpush

@push('scripts')
<script>
function handleCoverSelect(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('coverPreview');
        img.src = e.target.result;
        img.style.display = 'block';
        document.getElementById('coverIcon').style.display = 'none';
    };
    reader.readAsDataURL(file);
}
</script>
@endpush
