@extends('layouts.admin')

@section('title', 'Quản lý bài hát')
@section('page-title', 'Quản lý bài hát')
@section('page-subtitle', 'Xem, lọc và can thiệp nội dung bài hát trên nền tảng')

@section('content')

{{-- Flash --}}
@if(session('success'))
<div class="alert alert-dismissible fade show mb-4"
     style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.28);color:#6ee7b7"
     role="alert">
    <i class="fa-solid fa-circle-check me-2"></i>{!! session('success') !!}
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-dismissible fade show mb-4"
     style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.28);color:#fca5a5"
     role="alert">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ─── Stat cards ─── --}}
<div class="row g-3 mb-4">
    @php
    $statCards = [
        ['label'=>'Tổng bài hát',   'value'=>$stats['total'],     'icon'=>'fa-music',       'color'=>'#818cf8', 'bg'=>'rgba(99,102,241,.12)'],
        ['label'=>'Đang phát sóng', 'value'=>$stats['published'],  'icon'=>'fa-circle-check','color'=>'#4ade80', 'bg'=>'rgba(74,222,128,.12)'],
        ['label'=>'Bị ẩn/nháp',     'value'=>$stats['hidden'],     'icon'=>'fa-eye-slash',   'color'=>'#fbbf24', 'bg'=>'rgba(251,191,36,.12)'],
        ['label'=>'Đã gỡ bỏ',       'value'=>$stats['deleted'],    'icon'=>'fa-ban',         'color'=>'#f87171', 'bg'=>'rgba(248,113,113,.12)'],
        ['label'=>'Bài hát VIP',    'value'=>$stats['vip'],        'icon'=>'fa-crown',       'color'=>'#c084fc', 'bg'=>'rgba(192,132,252,.12)'],
    ];
    @endphp
    @foreach($statCards as $s)
    <div class="col-6 col-md-4 col-xl-2-4">
        <div class="rounded-3 p-3 d-flex align-items-center gap-3 h-100"
             style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07)">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:40px;height:40px;background:{{ $s['bg'] }};border:1px solid {{ $s['color'] }}25">
                <i class="fa-solid {{ $s['icon'] }}" style="color:{{ $s['color'] }};font-size:.88rem"></i>
            </div>
            <div>
                <div class="fw-bold text-white" style="font-size:1.25rem;line-height:1">{{ number_format($s['value']) }}</div>
                <div class="text-muted" style="font-size:.72rem">{{ $s['label'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ─── Filter bar ─── --}}
<form method="GET" action="{{ route('admin.songs.index') }}" class="filter-bar">
    <div class="filter-bar-inner">

        {{-- Tìm kiếm chung --}}
        <div class="filter-field" style="flex:2;min-width:260px;">
            <label class="filter-label"><i class="fa-solid fa-magnifying-glass"></i>Tìm bài hát, ca sĩ, tác giả</label>
            <div class="filter-search-wrap">
                <i class="fa-solid fa-magnifying-glass filter-search-icon"></i>
                <input type="text" name="search" class="filter-input"
                       placeholder="Nhập từ khóa tìm kiếm..."
                       value="{{ $filters['search'] ?? '' }}">
            </div>
        </div>

        {{-- Thể loại --}}
        <div class="filter-field" style="min-width:135px;">
            <label class="filter-label"><i class="fa-solid fa-guitar"></i>Thể loại</label>
            <select name="genre_id" class="filter-select">
                <option value="">Tất cả</option>
                @foreach($genres as $g)
                    <option value="{{ $g->id }}" {{ ($filters['genre_id'] ?? '') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Năm phát hành --}}
        <div class="filter-field" style="min-width:125px;">
            <label class="filter-label"><i class="fa-solid fa-calendar-days"></i>Năm phát hành</label>
            <select name="released_year" class="filter-select">
                <option value="">Tất cả</option>
                @foreach($releaseYears as $yr)
                    <option value="{{ $yr }}" {{ ($filters['released_year'] ?? '') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                @endforeach
            </select>
        </div>

        {{-- Trạng thái --}}
        <div class="filter-field" style="min-width:140px;">
            <label class="filter-label"><i class="fa-solid fa-toggle-on"></i>Trạng thái</label>
            <select name="status" class="filter-select">
                <option value="" {{ ($filters['status'] ?? '') === '' ? 'selected' : '' }}>Tất cả</option>
                <option value="published" {{ ($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>Đang phát sóng</option>
                <option value="pending"   {{ ($filters['status'] ?? '') === 'pending'   ? 'selected' : '' }}>Chờ xử lý</option>
                <option value="hidden"    {{ ($filters['status'] ?? '') === 'hidden'    ? 'selected' : '' }}>Đã ẩn</option>
                <option value="draft"     {{ ($filters['status'] ?? '') === 'draft'     ? 'selected' : '' }}>Bản nháp</option>
                <option value="scheduled" {{ ($filters['status'] ?? '') === 'scheduled' ? 'selected' : '' }}>Hẹn giờ</option>
            </select>
        </div>

        {{-- Loại VIP --}}
        <div class="filter-field" style="min-width:110px;">
            <label class="filter-label"><i class="fa-solid fa-crown"></i>Loại</label>
            <select name="is_vip" class="filter-select">
                <option value="" {{ ($filters['is_vip'] ?? '') === '' ? 'selected' : '' }}>Tất cả</option>
                <option value="1" {{ ($filters['is_vip'] ?? '') === '1' ? 'selected' : '' }}>VIP</option>
                <option value="0" {{ ($filters['is_vip'] ?? '') === '0' ? 'selected' : '' }}>Miễn phí</option>
            </select>
        </div>

        {{-- Đã xóa --}}
        <div class="filter-field" style="min-width:110px;">
            <label class="filter-label"><i class="fa-solid fa-trash"></i>Hiển thị</label>
            <select name="deleted" class="filter-select">
                <option value="" {{ ($filters['deleted'] ?? '') === '' ? 'selected' : '' }}>Đang hiện</option>
                <option value="1" {{ ($filters['deleted'] ?? '') === '1' ? 'selected' : '' }}>Đã gỡ bỏ</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="filter-btn-submit">
                <i class="fa-solid fa-filter"></i>Lọc
                @if(!empty($filters['search']) || !empty($filters['genre_id']) || !empty($filters['released_year']) || (isset($filters['status']) && $filters['status'] !== '') || (isset($filters['is_vip']) && $filters['is_vip'] !== '') || (isset($filters['deleted']) && $filters['deleted'] !== ''))
                    <span class="filter-active-dot"></span>
                @endif
            </button>
            <a href="{{ route('admin.songs.index') }}" class="filter-btn-reset" title="Xóa bộ lọc">
                <i class="fa-solid fa-xmark"></i>
            </a>
        </div>

    </div>
</form>

{{-- ─── Results summary ─── --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <span class="text-muted small">
        Tìm thấy <strong class="text-white">{{ $songs->total() }}</strong> bài hát
    </span>
    <span class="text-muted small">Trang {{ $songs->currentPage() }} / {{ $songs->lastPage() }}</span>
</div>

{{-- ─── Song table ─── --}}
<div class="card bg-dark border-secondary border-opacity-25">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="border-secondary">
                    <th class="text-muted fw-normal small ps-3" style="width:46px">#</th>
                    <th class="text-muted fw-normal small">Bài hát</th>
                    <th class="text-muted fw-normal small">Nghệ sĩ</th>
                    <th class="text-muted fw-normal small d-none d-md-table-cell">Thể loại</th>
                    <th class="text-muted fw-normal small d-none d-lg-table-cell">Lượt nghe</th>
                    <th class="text-muted fw-normal small">Loại</th>
                    <th class="text-muted fw-normal small">Trạng thái</th>
                    <th class="text-muted fw-normal small d-none d-lg-table-cell">Ngày tạo</th>
                    <th class="text-muted fw-normal small text-end pe-3">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($songs as $song)
                @php
                    $statusStyle = match($song->status) {
                        'published' => ['bg'=>'rgba(74,222,128,.12)',  'color'=>'#4ade80', 'border'=>'rgba(74,222,128,.25)',  'icon'=>'fa-circle-check',        'label'=>'Phát sóng'],
                        'pending'   => ['bg'=>'rgba(251,191,36,.12)',  'color'=>'#fbbf24', 'border'=>'rgba(251,191,36,.25)',  'icon'=>'fa-clock',               'label'=>'Chờ xử lý'],
                        'hidden'    => ['bg'=>'rgba(107,114,128,.12)', 'color'=>'#9ca3af', 'border'=>'rgba(107,114,128,.25)', 'icon'=>'fa-eye-slash',           'label'=>'Đã ẩn'],
                        'draft'     => ['bg'=>'rgba(99,102,241,.12)',  'color'=>'#818cf8', 'border'=>'rgba(99,102,241,.25)',  'icon'=>'fa-file-pen',            'label'=>'Nháp'],
                        'scheduled' => ['bg'=>'rgba(56,189,248,.12)',  'color'=>'#38bdf8', 'border'=>'rgba(56,189,248,.25)',  'icon'=>'fa-calendar-check',      'label'=>'Hẹn giờ'],
                        default     => ['bg'=>'rgba(107,114,128,.12)', 'color'=>'#9ca3af', 'border'=>'rgba(107,114,128,.25)', 'icon'=>'fa-circle',              'label'=>$song->status],
                    };
                    $coverUrl = $song->getCoverUrl();
                @endphp
                <tr class="border-secondary border-opacity-25 {{ $song->deleted ? 'opacity-50' : '' }}">
                    <td class="ps-3 text-muted small">{{ $song->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="position-relative flex-shrink-0">
                                <img src="{{ $coverUrl }}" alt="{{ $song->title }}"
                                     style="width:42px;height:42px;object-fit:cover;border-radius:8px;border:1px solid rgba(255,255,255,.1)">
                                @if($song->deleted)
                                    <div style="position:absolute;inset:0;border-radius:8px;background:rgba(239,68,68,.4);display:flex;align-items:center;justify-content:center">
                                        <i class="fa-solid fa-ban" style="color:#fca5a5;font-size:.7rem"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="fw-semibold text-white text-truncate" style="max-width:200px;font-size:.88rem">
                                    {{ $song->title }}
                                </div>
                                @if($song->author)
                                <div class="text-muted" style="font-size:.72rem">{{ $song->author }}</div>
                                @endif
                                @if($song->album)
                                <div style="font-size:.68rem;color:#a78bfa">
                                    <i class="fa-solid fa-record-vinyl me-1"></i>{{ $song->album->title }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($song->artist)
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $song->artist->avatar && $song->artist->avatar !== '/storage/avt.jpg' ? asset($song->artist->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($song->artist->name).'&background=a855f7&color=fff&size=28' }}"
                                 class="rounded-circle flex-shrink-0"
                                 width="26" height="26" style="object-fit:cover">
                            <div class="min-w-0">
                                <div class="text-white small text-truncate" style="max-width:130px;font-size:.82rem">{{ $song->artist->name }}</div>
                            </div>
                        </div>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="d-none d-md-table-cell">
                        @if($song->genre)
                        <span class="badge rounded-pill px-2 py-1"
                              style="background:rgba(99,102,241,.12);color:#a5b4fc;border:1px solid rgba(99,102,241,.25);font-size:.68rem">
                            {{ $song->genre->name }}
                        </span>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="d-none d-lg-table-cell text-muted small">
                        {{ number_format($song->listens) }}
                    </td>
                    <td>
                        @if($song->is_vip)
                        <span class="badge rounded-pill px-2"
                              style="background:rgba(192,132,252,.12);color:#c084fc;border:1px solid rgba(192,132,252,.25);font-size:.68rem">
                            <i class="fa-solid fa-crown me-1"></i>VIP
                        </span>
                        @else
                        <span class="badge rounded-pill px-2"
                              style="background:rgba(74,222,128,.08);color:#6ee7b7;border:1px solid rgba(74,222,128,.2);font-size:.68rem">
                            <i class="fa-solid fa-users me-1"></i>Free
                        </span>
                        @endif
                    </td>
                    <td>
                        @if($song->deleted)
                        <span class="badge rounded-pill px-2 py-1"
                              style="background:rgba(248,113,113,.12);color:#fca5a5;border:1px solid rgba(248,113,113,.25);font-size:.68rem">
                            <i class="fa-solid fa-ban me-1"></i>Đã gỡ
                        </span>
                        @else
                        <span class="badge rounded-pill px-2 py-1"
                              style="background:{{ $statusStyle['bg'] }};color:{{ $statusStyle['color'] }};border:1px solid {{ $statusStyle['border'] }};font-size:.68rem">
                            <i class="fa-solid {{ $statusStyle['icon'] }} me-1"></i>{{ $statusStyle['label'] }}
                        </span>
                        @endif
                    </td>
                    <td class="d-none d-lg-table-cell text-muted small">
                        {{ $song->created_at->format('d/m/Y') }}
                    </td>
                    <td class="text-end pe-3">
                        <div class="d-flex gap-1 justify-content-end">
                            {{-- Chi tiết --}}
                            <a href="{{ route('admin.songs.show', $song->id) }}"
                               class="btn btn-sm btn-outline-secondary" title="Xem chi tiết"
                               style="padding:4px 8px">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            @if(!$song->deleted)
                                {{-- Ẩn / Hiện --}}
                                <form method="POST" action="{{ route('admin.songs.toggleHide', $song->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-sm {{ $song->status === 'hidden' ? 'btn-outline-success' : 'btn-outline-warning' }}"
                                            title="{{ $song->status === 'hidden' ? 'Bỏ ẩn bài hát' : 'Ẩn bài hát' }}"
                                            style="padding:4px 8px">
                                        <i class="fa-solid {{ $song->status === 'hidden' ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                    </button>
                                </form>

                                {{-- Gỡ bỏ vi phạm --}}
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-song"
                                        data-song-id="{{ $song->id }}"
                                        data-song-title="{{ $song->title }}"
                                        title="Gỡ bỏ vi phạm"
                                        style="padding:4px 8px">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                            @else
                                {{-- Khôi phục --}}
                                <form method="POST" action="{{ route('admin.songs.restore', $song->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success"
                                            title="Khôi phục bài hát"
                                            style="padding:4px 8px">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                </form>

                                {{-- Xóa vĩnh viễn --}}
                                <button type="button" class="btn btn-sm btn-outline-danger btn-force-delete"
                                        data-song-id="{{ $song->id }}"
                                        data-song-title="{{ $song->title }}"
                                        title="Xóa vĩnh viễn"
                                        style="padding:4px 8px">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="fa-solid fa-music fa-2x mb-3 opacity-25 d-block"></i>
                        Không tìm thấy bài hát nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($songs->hasPages())
    <div class="card-footer bg-transparent border-secondary border-opacity-25 py-3">
        {{ $songs->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- ─── Modal: Gỡ bỏ vi phạm bản quyền ─── --}}
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
                        Bài hát sẽ bị <strong>ẩn khỏi nền tảng</strong> ngay lập tức nhưng dữ liệu vẫn được giữ lại. Bạn có thể khôi phục sau.
                    </div>
                    <p class="text-muted small mb-3">
                        Bài hát: <strong class="text-white" id="removeSongTitle"></strong>
                    </p>
                    <div class="mb-0">
                        <label class="form-label text-muted small mb-1">
                            Lý do gỡ bỏ <span class="text-danger">*</span>
                        </label>
                        <textarea name="reason" rows="3"
                                  class="form-control form-control-sm bg-dark border-secondary text-white"
                                  placeholder="Ví dụ: Vi phạm bản quyền, nội dung không phù hợp..."
                                  required minlength="10" maxlength="500"></textarea>
                        <div class="form-text text-muted mt-1" style="font-size:.7rem">Tối thiểu 10 ký tự</div>
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
                        Xóa vĩnh viễn <strong class="text-white" id="forceDeleteTitle"></strong>?
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
// Gỡ bỏ vi phạm
document.querySelectorAll('.btn-remove-song').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('removeSongTitle').textContent = this.dataset.songTitle;
        document.getElementById('removeSongForm').action =
            '{{ url("/admin/songs") }}/' + this.dataset.songId + '/remove';
        // Clear textarea
        document.getElementById('removeSongForm').querySelector('textarea').value = '';
        new bootstrap.Modal(document.getElementById('removeSongModal')).show();
    });
});

// Xóa vĩnh viễn
document.querySelectorAll('.btn-force-delete').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('forceDeleteTitle').textContent = this.dataset.songTitle;
        document.getElementById('forceDeleteForm').action =
            '{{ url("/admin/songs") }}/' + this.dataset.songId;
        new bootstrap.Modal(document.getElementById('forceDeleteModal')).show();
    });
});
</script>
</script>
@endpush

