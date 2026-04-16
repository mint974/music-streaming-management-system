@extends('layouts.artist')

@section('title', 'Chỉnh sửa bài hát – Artist Studio')
@section('page-title', 'Chỉnh sửa bài hát')
@section('page-subtitle', $song->title)

@section('content')
<form method="POST" action="{{ route('artist.songs.update', $song) }}" enctype="multipart/form-data" id="songEditForm">
@csrf
@method('PATCH')

@if($errors->any())
    <div class="alert mb-4" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;border-radius:12px">
        <i class="fa-solid fa-circle-exclamation me-2"></i><strong>Vui lòng kiểm tra lại:</strong>
        <ul class="mb-0 mt-2 ps-4">
            @foreach($errors->all() as $e)
                <li style="font-size:.86rem">{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4" style="background:#111827;border:1px solid #1f2937;border-radius:16px">
            <div class="card-body p-4">
                <h6 class="text-white fw-semibold mb-3"><i class="fa-solid fa-file-audio me-2" style="color:#a855f7"></i>File âm nhạc</h6>
                @if($song->file_path)
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-3" style="background:#1a1a2e;border:1px solid #2a2a45">
                        <i class="fa-solid fa-music" style="color:#a855f7;font-size:1.4rem"></i>
                        <div>
                            <div style="color:#f1f5f9;font-size:.88rem">{{ basename($song->file_path) }}</div>
                            <div style="color:#6b7280;font-size:.75rem">{{ $song->fileSizeFormatted() }} • {{ $song->durationFormatted() }}</div>
                        </div>
                    </div>
                @endif

                <label class="btn btn-sm" style="background:#1a1a2e;border:1px solid #2a2a45;color:#94a3b8" for="audio_file">
                    <i class="fa-solid fa-arrows-rotate me-2"></i>Thay thế file nhạc (MP3/FLAC/WAV, max 100 MB)
                </label>
                <input type="file" id="audio_file" name="audio_file" accept=".mp3,.flac,.wav" class="d-none" onchange="handleAudioSelect(this)">
                <div id="newAudioName" class="mt-2 text-muted" style="font-size:.8rem"></div>
                @error('audio_file')
                    <div class="text-danger mt-1" style="font-size:.82rem">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="card mb-4" style="background:#111827;border:1px solid #1f2937;border-radius:16px">
            <div class="card-body p-4">
                <h6 class="text-white fw-semibold mb-3"><i class="fa-solid fa-circle-info me-2" style="color:#60a5fa"></i>Thông tin bài hát</h6>

                <div class="mb-3">
                    <label class="form-label" style="color:#94a3b8;font-size:.85rem">Tên bài hát <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $song->title) }}" style="background:#1a1a2e;border-color:#2a2a45;color:#e2e8f0" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" style="color:#94a3b8;font-size:.85rem">Thể loại</label>
                        <select name="genre_id" class="form-select" style="background:#1a1a2e;border-color:#2a2a45;color:#e2e8f0">
                            <option value="">-- Chọn thể loại --</option>
                            @foreach($genres as $g)
                                <option value="{{ $g->id }}" {{ old('genre_id', $song->genre_id) == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" style="color:#94a3b8;font-size:.85rem">Album</label>
                        <select name="album_id" class="form-select" style="background:#1a1a2e;border-color:#2a2a45;color:#e2e8f0">
                            <option value="">-- Không thuộc album --</option>
                            @foreach($albums as $a)
                                <option value="{{ $a->id }}" {{ old('album_id', $song->album_id) == $a->id ? 'selected' : '' }}>{{ $a->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" style="color:#94a3b8;font-size:.85rem">Năm phát hành</label>
                        <input type="number" min="1900" max="{{ now()->year + 1 }}" name="released_year"
                               value="{{ old('released_year', optional($song->released_date)->format('Y')) }}"
                               class="form-control" style="background:#1a1a2e;border-color:#2a2a45;color:#e2e8f0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" style="color:#94a3b8;font-size:.85rem">Trạng thái <span class="text-danger">*</span></label>
                        <select name="status" id="statusSelect" class="form-select" style="background:#1a1a2e;border-color:#2a2a45;color:#e2e8f0">
                            <option value="draft" {{ old('status', $song->status) === 'draft' ? 'selected' : '' }}>Bản nháp</option>
                            <option value="published" {{ old('status', $song->status) === 'published' ? 'selected' : '' }}>Đã xuất bản</option>
                            <option value="scheduled" {{ old('status', $song->status) === 'scheduled' ? 'selected' : '' }}>Hẹn giờ xuất bản</option>
                            <option value="hidden" {{ old('status', $song->status) === 'hidden' ? 'selected' : '' }}>Ẩn</option>
                            <option value="pending" {{ old('status', $song->status) === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="publishAtWrap" style="display:{{ old('status', $song->status) === 'scheduled' ? 'block' : 'none' }}">
                        <label class="form-label" style="color:#94a3b8;font-size:.85rem">Hẹn giờ xuất bản</label>
                        <input type="datetime-local" name="publish_at"
                               value="{{ old('publish_at', $song->publish_at ? $song->publish_at->format('Y-m-d\\TH:i') : '') }}"
                               class="form-control" style="background:#1a1a2e;border-color:#2a2a45;color:#e2e8f0">
                    </div>
                </div>

                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="is_vip" value="1" id="isVip" {{ old('is_vip', $song->is_vip) ? 'checked' : '' }}>
                    <label class="form-check-label" for="isVip" style="color:#94a3b8">
                        <i class="fa-solid fa-crown me-1" style="color:#fbbf24"></i> Chỉ dành cho thành viên VIP
                    </label>
                </div>
            </div>
        </div>

        <div class="card mb-4" style="background:#111827;border:1px solid #1f2937;border-radius:16px">
            <div class="card-body p-4">
                @php
                    $defaultLyricsType = old('lyrics_type', ($song->defaultLyric?->type === 'synced' ? 'lrc' : ($song->defaultLyric?->type ?? 'plain')));
                @endphp
                <h6 class="text-white fw-semibold mb-3"><i class="fa-solid fa-align-left me-2" style="color:#c084fc"></i>Lời bài hát</h6>
                <div class="d-flex gap-3 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="lyrics_type" value="plain" id="lyricsPlain" {{ $defaultLyricsType === 'plain' ? 'checked' : '' }}>
                        <label class="form-check-label" for="lyricsPlain" style="color:#94a3b8">Văn bản thường</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="lyrics_type" value="lrc" id="lyricsLrc" {{ $defaultLyricsType === 'lrc' ? 'checked' : '' }}>
                        <label class="form-check-label" for="lyricsLrc" style="color:#94a3b8">LRC (đồng bộ)</label>
                    </div>
                </div>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label" style="color:#94a3b8;font-size:.85rem">Tên phiên bản lời</label>
                        <input type="text" name="lyrics_name" class="form-control" value="{{ old('lyrics_name', $song->defaultLyric?->name) }}" placeholder="VD: Lời đồng bộ gốc" style="background:#1a1a2e;border-color:#2a2a45;color:#e2e8f0" maxlength="100">
                        <p class="text-muted mt-1 mb-0" style="font-size:.74rem">Để trống để tự động tạo tên (vd: Lời đồng bộ #1)</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="color:#94a3b8;font-size:.85rem">&nbsp;</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="is_lyrics_visible" value="1" id="isLyricsVisible" {{ old('is_lyrics_visible', $song->defaultLyric?->is_visible ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isLyricsVisible" style="color:#94a3b8;cursor:pointer">
                                <i class="fa-solid fa-eye me-1"></i>Hiển thị lời
                            </label>
                        </div>
                    </div>
                </div>

                <textarea name="lyrics" rows="10" class="form-control font-monospace" style="background:#0d1117;border-color:#2a2a45;color:#e2e8f0;font-size:.85rem">{{ old('lyrics', $song->defaultLyric?->raw_text) }}</textarea>
                <p class="text-muted mt-2 mb-0" style="font-size:.74rem">LRC mẫu: [00:15.00] Dòng lời bài hát...</p>
            </div>
        </div>

        @php
            $editMood = old('tags.mood', $song->getMoodTags());
            $editActivity = old('tags.activity', $song->getActivityTags());
            $editTopic = old('tags.topic', $song->getTopicTags());
        @endphp
        <div class="card mb-4" style="background:#111827;border:1px solid #1f2937;border-radius:16px">
            <div class="card-body p-4">
                <h6 class="text-white fw-semibold mb-3"><i class="fa-solid fa-tags me-2" style="color:#22d3ee"></i>Gắn tag</h6>

                <div class="mb-3">
                    <label class="form-label" style="color:#c084fc">Tâm trạng</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(\App\Models\Song::$MOOD_TAGS as $key => $label)
                            <input type="checkbox" class="btn-check" id="tag-mood-{{ $key }}" name="tags[mood][]" value="{{ $key }}" autocomplete="off" {{ in_array($key, $editMood) ? 'checked' : '' }}>
                            <label class="btn btn-sm tag-mood" for="tag-mood-{{ $key }}">{{ $label }}</label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="color:#34d399">Hoạt động</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(\App\Models\Song::$ACTIVITY_TAGS as $key => $label)
                            <input type="checkbox" class="btn-check" id="tag-act-{{ $key }}" name="tags[activity][]" value="{{ $key }}" autocomplete="off" {{ in_array($key, $editActivity) ? 'checked' : '' }}>
                            <label class="btn btn-sm tag-activity" for="tag-act-{{ $key }}">{{ $label }}</label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="form-label" style="color:#60a5fa">Chủ đề</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(\App\Models\Song::$TOPIC_TAGS as $key => $label)
                            <input type="checkbox" class="btn-check" id="tag-top-{{ $key }}" name="tags[topic][]" value="{{ $key }}" autocomplete="off" {{ in_array($key, $editTopic) ? 'checked' : '' }}>
                            <label class="btn btn-sm tag-topic" for="tag-top-{{ $key }}">{{ $label }}</label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4" style="background:#111827;border:1px solid #1f2937;border-radius:16px">
            <div class="card-body p-4">
                <h6 class="text-white fw-semibold mb-3"><i class="fa-solid fa-image me-2" style="color:#f472b6"></i>Ảnh bìa</h6>
                <div style="aspect-ratio:1;border:1px solid #2a2a45;border-radius:12px;display:flex;align-items:center;justify-content:center;background:#0b1220;overflow:hidden" class="mb-3">
                    <img id="coverPreview" src="{{ $song->getCoverUrl() }}" alt="" style="width:100%;height:100%;object-fit:cover;{{ !$song->cover_image ? 'display:none' : '' }}">
                    <i id="coverIcon" class="fa-regular fa-image" style="font-size:2.4rem;color:#2a3a52;{{ $song->cover_image ? 'display:none' : '' }}"></i>
                </div>

                @if($song->cover_image)
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="remove_cover" value="1" id="removeCover">
                        <label class="form-check-label" for="removeCover" style="color:#f87171;font-size:.83rem">Xóa ảnh bìa hiện tại</label>
                    </div>
                @endif

                <label for="cover_image" class="btn btn-sm w-100" style="background:#1a1a2e;border:1px solid #2a2a45;color:#cbd5e1">
                    <i class="fa-solid fa-upload me-1"></i>Thay ảnh bìa
                </label>
                <input type="file" id="cover_image" name="cover_image" accept="image/*" class="d-none" onchange="handleCoverSelect(this)">
                <p class="text-muted mt-2 mb-0" style="font-size:.74rem">JPG, PNG, WEBP (tối đa 5MB)</p>
            </div>
        </div>

        <div class="card" style="background:#111827;border:1px solid #1f2937;border-radius:16px">
            <div class="card-body p-4">
                <button type="submit" class="btn btn-sm w-100" style="background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;border:none">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Lưu thay đổi
                </button>
                <a href="{{ route('artist.songs.index') }}" class="btn btn-sm w-100 mt-2" style="background:#1a1a2e;border:1px solid #2a2a45;color:#94a3b8">
                    Hủy bỏ
                </a>
            </div>
        </div>
    </div>
</div>
</form>
@endsection

@push('styles')
<style>
.tag-mood,
.tag-activity,
.tag-topic {
    position: relative;
    border-width: 1px;
    border-style: solid;
    border-radius: 10px;
    font-weight: 600;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease, color .18s ease;
}

.tag-mood {
    background: linear-gradient(135deg, rgba(168,85,247,.18), rgba(217,70,239,.14));
    border-color: rgba(196,181,253,.35);
    color: #ddd6fe;
}

.tag-activity {
    background: linear-gradient(135deg, rgba(16,185,129,.18), rgba(45,212,191,.14));
    border-color: rgba(110,231,183,.35);
    color: #ccfbf1;
}

.tag-topic {
    background: linear-gradient(135deg, rgba(59,130,246,.18), rgba(14,165,233,.14));
    border-color: rgba(147,197,253,.35);
    color: #dbeafe;
}

.tag-mood:hover,
.tag-activity:hover,
.tag-topic:hover {
    transform: translateY(-1px);
    color: #ffffff;
}

.tag-mood:hover {
    background: linear-gradient(135deg, rgba(168,85,247,.34), rgba(217,70,239,.3));
    border-color: rgba(233,213,255,.85);
    box-shadow: 0 8px 18px rgba(168,85,247,.28), inset 0 1px 0 rgba(255,255,255,.18);
}

.tag-activity:hover {
    background: linear-gradient(135deg, rgba(16,185,129,.34), rgba(45,212,191,.3));
    border-color: rgba(167,243,208,.85);
    box-shadow: 0 8px 18px rgba(16,185,129,.25), inset 0 1px 0 rgba(255,255,255,.18);
}

.tag-topic:hover {
    background: linear-gradient(135deg, rgba(59,130,246,.34), rgba(14,165,233,.3));
    border-color: rgba(191,219,254,.85);
    box-shadow: 0 8px 18px rgba(59,130,246,.28), inset 0 1px 0 rgba(255,255,255,.18);
}

.btn-check:checked + .tag-mood {
    background: linear-gradient(135deg, #a855f7, #d946ef) !important;
    border-color: #f0abfc !important;
    color: #ffffff !important;
    box-shadow: 0 10px 22px rgba(168,85,247,.4), 0 0 0 .16rem rgba(168,85,247,.28);
}

.btn-check:checked + .tag-activity {
    background: linear-gradient(135deg, #10b981, #2dd4bf) !important;
    border-color: #99f6e4 !important;
    color: #ffffff !important;
    box-shadow: 0 10px 22px rgba(16,185,129,.36), 0 0 0 .16rem rgba(16,185,129,.25);
}

.btn-check:checked + .tag-topic {
    background: linear-gradient(135deg, #3b82f6, #0ea5e9) !important;
    border-color: #bfdbfe !important;
    color: #ffffff !important;
    box-shadow: 0 10px 22px rgba(59,130,246,.38), 0 0 0 .16rem rgba(59,130,246,.25);
}
</style>
@endpush

@push('scripts')
<script>
function handleCoverSelect(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        const img = document.getElementById('coverPreview');
        img.src = e.target.result;
        img.style.display = 'block';
        document.getElementById('coverIcon').style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function handleAudioSelect(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('newAudioName').textContent = 'Đã chọn: ' + file.name;
}

const statusSelect = document.getElementById('statusSelect');
const publishAtWrap = document.getElementById('publishAtWrap');
if (statusSelect && publishAtWrap) {
    statusSelect.addEventListener('change', function () {
        publishAtWrap.style.display = this.value === 'scheduled' ? 'block' : 'none';
    });
}
</script>
@endpush
