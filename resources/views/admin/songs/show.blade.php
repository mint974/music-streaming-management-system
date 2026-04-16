@extends('layouts.admin')

@section('title', 'Chi tiết bài hát #' . $song->id)
@section('page-subtitle', $song->title)

@section('content')

{{-- Flash --}}
@if(session('success'))
<div class="alert alert-dismissible fade show mb-4"
     style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.28);color:#6ee7b7" role="alert">
    <i class="fa-solid fa-circle-check me-2"></i>{!! session('success') !!}
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.songs.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i>Quay lại
    </a>
    @if(!$song->deleted)
        <form method="POST" action="{{ route('admin.songs.toggleHide', $song->id) }}" class="ms-auto">
            @csrf
            <button type="submit" class="btn btn-sm {{ $song->status === 'hidden' ? 'btn-outline-success' : 'btn-outline-warning' }}">
                <i class="fa-solid {{ $song->status === 'hidden' ? 'fa-eye' : 'fa-eye-slash' }} me-1"></i>
                {{ $song->status === 'hidden' ? 'Bỏ ẩn bài hát' : 'Ẩn bài hát' }}
            </button>
        </form>
        <button class="btn btn-sm btn-outline-danger btn-remove-song"
                data-song-id="{{ $song->id }}"
                data-song-title="{{ $song->title }}">
            <i class="fa-solid fa-ban me-1"></i>Gỡ bỏ vi phạm
        </button>
    @else
        <form method="POST" action="{{ route('admin.songs.restore', $song->id) }}" class="ms-auto">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-success">
                <i class="fa-solid fa-rotate-left me-1"></i>Khôi phục bài hát
            </button>
        </form>
        <button class="btn btn-sm btn-danger btn-force-delete"
                data-song-id="{{ $song->id }}"
                data-song-title="{{ $song->title }}">
            <i class="fa-solid fa-trash me-1"></i>Xóa vĩnh viễn
        </button>
    @endif
</div>

<div class="row g-4">
    {{-- Cột trái: cover + meta --}}
    <div class="col-12 col-lg-4">
        <div class="rounded-3 p-3" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07)">
            <div class="text-center mb-4">
                <img src="{{ $song->getCoverUrl() }}" alt="{{ $song->title }}"
                     class="rounded-3 img-fluid"
                     style="max-height:250px;object-fit:cover;border:2px solid rgba(255,255,255,.08)">
            </div>

            <h5 class="text-white fw-bold mb-1">{{ $song->title }}</h5>
            @if($song->author)
            <div class="text-muted small mb-3">{{ $song->author }}</div>
            @endif

            <div class="d-flex flex-wrap gap-2 mb-4">
                {{-- VIP badge --}}
                @if($song->is_vip)
                <span class="badge rounded-pill px-3 py-1"
                      style="background:rgba(192,132,252,.15);color:#c084fc;border:1px solid rgba(192,132,252,.3);font-size:.75rem">
                    <i class="fa-solid fa-crown me-1"></i>VIP
                </span>
                @else
                <span class="badge rounded-pill px-3 py-1"
                      style="background:rgba(74,222,128,.1);color:#6ee7b7;border:1px solid rgba(74,222,128,.25);font-size:.75rem">
                    <i class="fa-solid fa-users me-1"></i>Miễn phí
                </span>
                @endif

                {{-- Status badge --}}
                @php
                $statusStyle = match($song->status) {
                    'published' => ['color'=>'#4ade80', 'bg'=>'rgba(74,222,128,.12)', 'border'=>'rgba(74,222,128,.25)', 'icon'=>'fa-circle-check', 'label'=>'Đang phát sóng'],
                    'hidden'    => ['color'=>'#9ca3af', 'bg'=>'rgba(107,114,128,.12)', 'border'=>'rgba(107,114,128,.25)', 'icon'=>'fa-eye-slash', 'label'=>'Đã ẩn'],
                    'pending'   => ['color'=>'#fbbf24', 'bg'=>'rgba(251,191,36,.12)', 'border'=>'rgba(251,191,36,.25)', 'icon'=>'fa-clock', 'label'=>'Chờ xử lý'],
                    'draft'     => ['color'=>'#818cf8', 'bg'=>'rgba(99,102,241,.12)', 'border'=>'rgba(99,102,241,.25)', 'icon'=>'fa-file-pen', 'label'=>'Bản nháp'],
                    'scheduled' => ['color'=>'#38bdf8', 'bg'=>'rgba(56,189,248,.12)', 'border'=>'rgba(56,189,248,.25)', 'icon'=>'fa-calendar-check', 'label'=>'Hẹn giờ'],
                    default     => ['color'=>'#9ca3af', 'bg'=>'rgba(107,114,128,.12)', 'border'=>'rgba(107,114,128,.25)', 'icon'=>'fa-circle', 'label'=>$song->status],
                };
                @endphp
                @if($song->deleted)
                <span class="badge rounded-pill px-3 py-1"
                      style="background:rgba(248,113,113,.12);color:#fca5a5;border:1px solid rgba(248,113,113,.25);font-size:.75rem">
                    <i class="fa-solid fa-ban me-1"></i>Đã gỡ bỏ
                </span>
                @else
                <span class="badge rounded-pill px-3 py-1"
                      style="background:{{ $statusStyle['bg'] }};color:{{ $statusStyle['color'] }};border:1px solid {{ $statusStyle['border'] }};font-size:.75rem">
                    <i class="fa-solid {{ $statusStyle['icon'] }} me-1"></i>{{ $statusStyle['label'] }}
                </span>
                @endif
            </div>

            <dl class="row g-0 small">
                <dt class="col-5 text-muted">Nghệ sĩ</dt>
                <dd class="col-7 text-white mb-2">{{ $song->artist?->name ?? '—' }}</dd>

                <dt class="col-5 text-muted">Thể loại</dt>
                <dd class="col-7 text-white mb-2">{{ $song->genre?->name ?? '—' }}</dd>

                <dt class="col-5 text-muted">Album</dt>
                <dd class="col-7 text-white mb-2">{{ $song->album?->title ?? '—' }}</dd>

                <dt class="col-5 text-muted">Thời lượng</dt>
                <dd class="col-7 text-white mb-2">{{ $song->durationFormatted() }}</dd>

                <dt class="col-5 text-muted">Lượt nghe</dt>
                <dd class="col-7 mb-2" style="color:#818cf8">{{ number_format($song->listens) }}</dd>

                <dt class="col-5 text-muted">Dung lượng</dt>
                <dd class="col-7 text-white mb-2">{{ $song->fileSizeFormatted() }}</dd>

                <dt class="col-5 text-muted">Ngày phát hành</dt>
                <dd class="col-7 text-white mb-2">{{ $song->released_date ? $song->released_date->format('d/m/Y') : '—' }}</dd>

                <dt class="col-5 text-muted">Ngày tạo</dt>
                <dd class="col-7 text-white mb-2">{{ $song->created_at->format('d/m/Y H:i') }}</dd>

                @if($song->has_lyrics)
                <dt class="col-5 text-muted">Lời bài hát</dt>
                <dd class="col-7 mb-2">
                    <span style="color:#4ade80;font-size:.78rem"><i class="fa-solid fa-check me-1"></i>Có lời</span>
                </dd>
                @endif
            </dl>

            {{-- Tags --}}
            @if($song->tags->isNotEmpty())
            <div class="mt-3">
                <div class="text-muted small mb-2">Tags</div>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($song->tags as $tag)
                    <span class="badge rounded-pill px-2"
                          style="background:rgba(99,102,241,.1);color:#a5b4fc;border:1px solid rgba(99,102,241,.2);font-size:.68rem">
                        {{ $tag->name ?? $tag->slug }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Cột phải: thông tin chi tiết --}}
    <div class="col-12 col-lg-8">
        {{-- Audio player (nếu có file) --}}
        @if($song->file_path && $song->hasAudioFile())
        <div class="rounded-3 p-3 mb-4"
             style="background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.2)">
            <div class="text-muted small mb-2">
                <i class="fa-solid fa-play-circle me-1" style="color:#818cf8"></i>Nghe thử bài hát
            </div>
            <audio controls class="w-100" style="height:36px;filter:invert(1) hue-rotate(220deg) brightness(.85)">
                <source src="{{ route('songs.stream', $song->id) }}" type="{{ $song->file_mime ?? 'audio/mpeg' }}">
            </audio>
        </div>
        @endif

        {{-- File info --}}
        <div class="rounded-3 p-3 mb-4"
             style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06)">
            <div class="text-muted small mb-3 fw-semibold text-uppercase" style="letter-spacing:.05em;font-size:.68rem">
                <i class="fa-solid fa-file-audio me-1"></i>Thông tin file
            </div>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="text-muted" style="font-size:.72rem">Định dạng</div>
                    <div class="text-white small fw-semibold">{{ strtoupper(pathinfo($song->file_path ?? '—', PATHINFO_EXTENSION)) }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted" style="font-size:.72rem">Kích thước</div>
                    <div class="text-white small fw-semibold">{{ $song->fileSizeFormatted() }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted" style="font-size:.72rem">Thời lượng</div>
                    <div class="text-white small fw-semibold">{{ $song->durationFormatted() }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted" style="font-size:.72rem">Loại MIME</div>
                    <div class="text-white small fw-semibold">{{ $song->file_mime ?? '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Lyric preview --}}
        @if($song->defaultLyric)
        <div class="rounded-3 p-3 mb-4"
             style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06)">
            <div class="text-muted small mb-3 fw-semibold text-uppercase" style="letter-spacing:.05em;font-size:.68rem">
                <i class="fa-solid fa-align-left me-1"></i>Lời bài hát
                <span class="badge ms-2 px-2" style="background:rgba(99,102,241,.15);color:#a5b4fc;font-size:.65rem">
                    {{ $song->defaultLyric->type === 'synced' ? 'lrc' : 'plain' }}
                </span>
            </div>
            @php $lyricText = $song->defaultLyric->raw_text ?? ''; @endphp
            <pre class="text-muted mb-0" style="white-space:pre-wrap;font-size:.78rem;line-height:1.7;max-height:280px;overflow-y:auto">{{ Str::limit($lyricText, 800, '...') }}</pre>
        </div>
        @endif

        {{-- Activity log placeholder --}}
        <div class="rounded-3 p-3"
             style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06)">
            <div class="text-muted small mb-3 fw-semibold text-uppercase" style="letter-spacing:.05em;font-size:.68rem">
                <i class="fa-solid fa-clock-rotate-left me-1"></i>Thông tin thêm
            </div>
            <div class="row g-3">
                <div class="col-6 col-md-4">
                    <div class="text-muted" style="font-size:.72rem">ID bài hát</div>
                    <div class="text-white small fw-semibold">#{{ $song->id }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted" style="font-size:.72rem">ID nghệ sĩ</div>
                    <div class="text-white small fw-semibold">{{ $song->artist_profile_id }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted" style="font-size:.72rem">Cập nhật lần cuối</div>
                    <div class="text-white small fw-semibold">{{ $song->updated_at->format('d/m/Y H:i') }}</div>
                </div>
                @if($song->publish_at)
                <div class="col-6 col-md-4">
                    <div class="text-muted" style="font-size:.72rem">Hẹn giờ xuất bản</div>
                    <div class="text-white small fw-semibold">{{ $song->publish_at->format('d/m/Y H:i') }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ─── Modal: Gỡ bỏ vi phạm ─── --}}
<div class="modal fade" id="removeSongModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-danger border-opacity-50">
            <div class="modal-header border-danger border-opacity-25">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-ban me-2 text-danger"></i>Gỡ bỏ bài hát vi phạm
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="removeSongForm" action="">
                @csrf
                <div class="modal-body">
                    <div class="alert py-2 mb-3 small"
                         style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:#fca5a5">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Bài hát sẽ bị <strong>ẩn khỏi nền tảng</strong> ngay lập tức nhưng dữ liệu vẫn được giữ lại.
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-muted small mb-1">
                            Lý do gỡ bỏ <span class="text-danger">*</span>
                        </label>
                        <textarea name="reason" rows="3"
                                  class="form-control form-control-sm bg-dark border-secondary text-white"
                                  placeholder="Ví dụ: Vi phạm bản quyền, nội dung không phù hợp..."
                                  required minlength="10" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-danger border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fa-solid fa-ban me-1"></i>Gỡ bỏ bài hát
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─── Modal: Xóa vĩnh viễn ─── --}}
<div class="modal fade" id="forceDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content bg-dark border border-danger border-opacity-50">
            <div class="modal-header border-danger border-opacity-25">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-trash me-2 text-danger"></i>Xóa vĩnh viễn
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="forceDeleteForm" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p class="text-muted small mb-0">
                        Xóa vĩnh viễn <strong class="text-white">{{ $song->title }}</strong>?
                        File âm thanh và cover sẽ bị <strong class="text-danger">xóa hoàn toàn không thể phục hồi</strong>.
                    </p>
                </div>
                <div class="modal-footer border-danger border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-danger">Xóa vĩnh viễn</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const btn = document.querySelector('.btn-remove-song');
if (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('removeSongForm').action =
            '{{ url("/admin/songs") }}/' + this.dataset.songId + '/remove';
        document.getElementById('removeSongForm').querySelector('textarea').value = '';
        new bootstrap.Modal(document.getElementById('removeSongModal')).show();
    });
}

const btnDel = document.querySelector('.btn-force-delete');
if (btnDel) {
    btnDel.addEventListener('click', function () {
        document.getElementById('forceDeleteForm').action =
            '{{ url("/admin/songs") }}/' + this.dataset.songId;
        new bootstrap.Modal(document.getElementById('forceDeleteModal')).show();
    });
}
</script>
@endpush
