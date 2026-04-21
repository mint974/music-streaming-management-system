@extends('layouts.admin')

@section('title', 'Chi tiết Nghệ sĩ: ' . $artist->name)
@section('page-title', 'Chi tiết Nghệ sĩ')
@section('page-subtitle', $artist->artist_name ?? $artist->name)

@section('content')

{{-- Flash messages --}}
@if(session('success'))
<div class="alert alert-dismissible fade show mb-4"
     style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.28);color:#6ee7b7" role="alert">
    <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.artists.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i>Quay lại
    </a>
    
    <div class="ms-auto d-flex gap-2">
        <form method="POST" action="{{ route('admin.artists.toggleStatus', $artist->id) }}">
            @csrf
            @if($artist->status === 'Bị khóa')
            <button type="submit" class="btn btn-sm btn-outline-success">
                <i class="fa-solid fa-lock-open me-1"></i>Mở khóa tài khoản
            </button>
            @else
            <button type="submit" class="btn btn-sm btn-outline-warning">
                <i class="fa-solid fa-lock me-1"></i>Khóa tài khoản
            </button>
            @endif
        </form>

        <form method="POST" action="{{ route('admin.artists.toggleVerify', $artist->id) }}">
            @csrf
            @if($artist->artist_verified_at)
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-circle-xmark me-1"></i>Thu hồi xác minh
            </button>
            @else
            <button type="submit" class="btn btn-sm" style="background:rgba(56,189,248,.15);color:#38bdf8;border:1px solid rgba(56,189,248,.3)">
                <i class="fa-solid fa-circle-check me-1"></i>Cấp xác minh (Tick xanh)
            </button>
            @endif
        </form>
    </div>
</div>

<div class="row g-4">
    {{-- Left Column: Artist Profile Card --}}
    <div class="col-12 col-lg-4">
        <div class="rounded-3 p-4 text-center" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07)">
            <div class="position-relative d-inline-block mb-3">
                <img src="{{ $artist->avatar && $artist->avatar !== '/storage/avt.jpg' ? asset($artist->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($artist->name).'&background=a855f7&color=fff&size=120' }}" 
                     alt="{{ $artist->name }}"
                     class="rounded-circle" style="width:120px;height:120px;object-fit:cover;border:3px solid rgba(255,255,255,.1)">
                @if($artist->artist_verified_at)
                <div class="position-absolute bottom-0 end-0 rounded-circle d-flex align-items-center justify-content-center"
                     style="width:32px;height:32px;background:#18181b;border:2px solid #18181b">
                    <i class="fa-solid fa-circle-check" style="color:#38bdf8;font-size:1.4rem"></i>
                </div>
                @endif
            </div>

            <h4 class="text-white fw-bold mb-1">{{ $artist->artist_name ?? $artist->name }}</h4>
            <div class="text-muted small mb-3">{{ $artist->email }}</div>

            <div class="d-flex justify-content-center gap-3 mb-4">
                <div class="text-center px-3 border-end border-secondary border-opacity-25">
                    <div class="text-white fw-bold fs-5">{{ number_format($artist->songs_count ?? 0) }}</div>
                    <div class="text-muted small">Bài hát</div>
                </div>
                <div class="text-center px-3 border-end border-secondary border-opacity-25">
                    <div class="text-white fw-bold fs-5">{{ number_format($artist->albums_count ?? 0) }}</div>
                    <div class="text-muted small">Album</div>
                </div>
                <div class="text-center px-3">
                    <div class="text-white fw-bold fs-5" style="color:#a78bfa !important">{{ number_format($artist->songs_sum_listens ?? 0) }}</div>
                    <div class="text-muted small">Lượt nghe</div>
                </div>
            </div>

            <div class="text-start mt-4 pt-3 border-top border-secondary border-opacity-25">
                <div class="mb-2">
                    <span class="text-muted small d-block mb-1">Tên thật:</span>
                    <span class="text-white small fw-medium">{{ $artist->name }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block mb-1">Trạng thái tài khoản:</span>
                    @if($artist->status === 'Bị khóa')
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">Bị khóa</span>
                    @else
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Đang hoạt động</span>
                    @endif
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block mb-1">Ngày tham gia:</span>
                    <span class="text-white small">{{ $artist->created_at->format('d/m/Y') }}</span>
                </div>
                @if($artist->bio)
                <div class="mb-2 mt-3">
                    <span class="text-muted small d-block mb-1">Tiểu sử:</span>
                    <p class="text-white small mb-0" style="line-height:1.6">{{ $artist->bio }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right Column: Songs List --}}
    <div class="col-12 col-lg-8">
        <div class="card bg-dark border-secondary border-opacity-25 h-100">
            <div class="card-header bg-transparent border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 text-white"><i class="fa-solid fa-music me-2 text-muted"></i>Danh sách bài hát</h6>
                <a href="{{ route('admin.songs.index', ['artist_id' => $artist->id]) }}" class="btn btn-sm btn-outline-secondary" style="font-size:.75rem">
                    Xem tất cả trong Quản lý Bài hát
                </a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-muted fw-normal small ps-3">Bài hát</th>
                            <th class="text-muted fw-normal small">Thể loại</th>
                            <th class="text-muted fw-normal small">Lượt nghe</th>
                            <th class="text-muted fw-normal small">Trạng thái</th>
                            <th class="text-end pe-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($songs as $song)
                            @php
                            $statusStyle = match($song->status) {
                                'published' => ['label'=>'Phát sóng', 'color'=>'#4ade80'],
                                'pending'   => ['label'=>'Chờ duyệt', 'color'=>'#fbbf24'],
                                'hidden'    => ['label'=>'Đã ẩn', 'color'=>'#9ca3af'],
                                'draft'     => ['label'=>'Nháp', 'color'=>'#818cf8'],
                                'scheduled' => ['label'=>'Hẹn giờ', 'color'=>'#38bdf8'],
                                default     => ['label'=>$song->status, 'color'=>'#9ca3af'],
                            };
                            @endphp
                            <tr class="border-secondary border-opacity-25 {{ $song->deleted ? 'opacity-50' : '' }}">
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $song->getCoverUrl() }}" alt="Cover"
                                             style="width:40px;height:40px;object-fit:cover;border-radius:6px;border:1px solid rgba(255,255,255,.1)">
                                        <div>
                                            <div class="text-white fw-semibold small text-truncate" style="max-width:180px">{{ $song->title }}</div>
                                            @if($song->album)
                                                <div class="text-muted" style="font-size:.68rem">{{ $song->album->title }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $song->genre->name ?? '—' }}</span>
                                </td>
                                <td class="text-muted small">
                                    {{ number_format($song->listens) }}
                                </td>
                                <td>
                                    @if($song->deleted)
                                        <span style="color:#fca5a5;font-size:.75rem"><i class="fa-solid fa-ban me-1"></i>Đã gỡ bỏ</span>
                                    @else
                                        <span style="color:{{ $statusStyle['color'] }};font-size:.75rem">
                                            <i class="fa-solid fa-circle me-1" style="font-size:.4rem;vertical-align:middle"></i>{{ $statusStyle['label'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.songs.show', $song->id) }}" class="btn btn-sm btn-outline-secondary" style="padding:2px 8px;font-size:.75rem">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5 small">Nghệ sĩ này chưa có bài hát nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($songs->hasPages())
                <div class="card-footer bg-transparent border-secondary border-opacity-25 py-2">
                    {{ $songs->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

