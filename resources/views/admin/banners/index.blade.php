@extends('layouts.admin')

@section('title', 'Quản lý Banner & Quảng cáo')
@section('page-title', 'Banner & Quảng cáo')
@section('page-subtitle', 'Sắp xếp, lên lịch và quản lý nội dung hiển thị trên nền tảng')

@section('content')

{{-- Flash --}}
@if(session('success'))
<div class="alert alert-dismissible fade show mb-4"
     style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.28);color:#6ee7b7" role="alert">
    <i class="fa-solid fa-circle-check me-2"></i>{!! session('success') !!}
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ─── Stat cards ─── --}}
<div class="row g-3 mb-4">
    @php
    $statCards = [
        ['label'=>'Tổng cộng',   'value'=>$stats['total'],        'icon'=>'fa-images',      'color'=>'#818cf8', 'bg'=>'rgba(99,102,241,.12)'],
        ['label'=>'Banner đang chạy', 'value'=>$stats['active_hero'],  'icon'=>'fa-panorama',    'color'=>'#4ade80', 'bg'=>'rgba(74,222,128,.12)'],
        ['label'=>'Quảng cáo (Ads)',  'value'=>$stats['active_ad'],    'icon'=>'fa-ad',          'color'=>'#fbbf24', 'bg'=>'rgba(251,191,36,.12)'],
        ['label'=>'Tổng truy cập/click','value'=>$stats['total_clicks'],'icon'=>'fa-hand-pointer','color'=>'#c084fc', 'bg'=>'rgba(192,132,252,.12)'],
    ];
    @endphp
    @foreach($statCards as $s)
    <div class="col-6 col-xl-3">
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

{{-- ─── Filter bar & Create Button ─── --}}
<div class="d-flex flex-wrap gap-2 mb-3">
    <form method="GET" action="{{ route('admin.banners.index') }}" class="filter-bar flex-grow-1 m-0">
        <div class="filter-bar-inner">
            <div class="filter-field" style="flex:1.8;min-width:200px;">
                <label class="filter-label"><i class="fa-solid fa-magnifying-glass"></i>Tìm kiếm</label>
                <div class="filter-search-wrap">
                    <i class="fa-solid fa-magnifying-glass filter-search-icon"></i>
                    <input type="text" name="search" class="filter-input"
                           placeholder="Tên banner / chiến dịch..."
                           value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>

            <div class="filter-field" style="min-width:145px;">
                <label class="filter-label"><i class="fa-solid fa-layer-group"></i>Loại hiển thị</label>
                <select name="type" class="filter-select">
                    <option value="">Tất cả</option>
                    <option value="hero" {{ ($filters['type'] ?? '') === 'hero' ? 'selected' : '' }}>Banner lớn (Trang chủ)</option>
                    <option value="ad"   {{ ($filters['type'] ?? '') === 'ad'   ? 'selected' : '' }}>Quảng cáo (Ads Audio)</option>
                </select>
            </div>

            <div class="filter-field" style="min-width:130px;">
                <label class="filter-label"><i class="fa-solid fa-toggle-on"></i>Trạng thái</label>
                <select name="status" class="filter-select">
                    <option value="">Tất cả</option>
                    <option value="active"   {{ ($filters['status'] ?? '') === 'active'   ? 'selected' : '' }}>Đang bật</option>
                    <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Đã tắt</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="filter-btn-submit">
                    <i class="fa-solid fa-filter"></i>Lọc
                </button>
                <a href="{{ route('admin.banners.index') }}" class="filter-btn-reset" title="Xóa bộ lọc">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            </div>
        </div>
    </form>

    <a href="{{ route('admin.banners.create') }}" class="btn d-flex align-items-center gap-2"
       style="background:rgba(168,85,247,.15);color:#c084fc;border:1px solid rgba(168,85,247,.3);font-size:.85rem;padding:0 1rem;border-radius:8px">
        <i class="fa-solid fa-plus"></i>Tạo mới
    </a>
</div>

{{-- ─── Table ─── --}}
<div class="card bg-dark border-secondary border-opacity-25">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="border-secondary">
                    <th class="ps-3 text-muted fw-normal small" style="width:60px">STT</th>
                    <th class="text-muted fw-normal small">Thông tin Banner</th>
                    <th class="text-muted fw-normal small d-none d-md-table-cell">Phân loại</th>
                    <th class="text-muted fw-normal small d-none d-lg-table-cell">Lịch hiển thị</th>
                    <th class="text-muted fw-normal small">Trạng thái</th>
                    <th class="text-muted fw-normal small text-end pe-3">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banners as $item)
                <tr class="border-secondary border-opacity-25">
                    <td class="ps-3 text-muted small">{{ $item->order_index }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ asset($item->image_path) }}" alt="{{ $item->title }}"
                                 style="width:90px;height:45px;object-fit:cover;border-radius:4px;border:1px solid rgba(255,255,255,.1)">
                            <div class="min-w-0">
                                <div class="fw-semibold text-white text-truncate" style="max-width:200px;font-size:.88rem">
                                    {{ $item->title }}
                                </div>
                                @if($item->target_url)
                                    <div class="text-muted" style="font-size:.7rem;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                        <i class="fa-solid fa-link me-1"></i>{{ $item->target_url }}
                                    </div>
                                @endif
                                <div class="text-muted mt-1" style="font-size:.7rem">
                                    <i class="fa-solid fa-hand-pointer me-1"></i>{{ number_format($item->clicks) }} clicks
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="d-none d-md-table-cell">
                        @if($item->type === 'hero')
                            <span class="badge rounded-pill" style="background:rgba(56,189,248,.1);color:#7dd3fc;border:1px solid rgba(56,189,248,.2);font-size:.7rem">
                                <i class="fa-solid fa-panorama me-1"></i>Banner Trang chủ
                            </span>
                        @else
                            <span class="badge rounded-pill" style="background:rgba(251,191,36,.1);color:#fcd34d;border:1px solid rgba(251,191,36,.2);font-size:.7rem">
                                <i class="fa-solid fa-ad me-1"></i>Quảng cáo
                            </span>
                        @endif
                    </td>
                    <td class="d-none d-lg-table-cell text-muted" style="font-size:.75rem">
                        @if($item->start_time || $item->end_time)
                            @if($item->start_time)
                                <div><span style="color:#a78bfa">Bắt đầu:</span> {{ $item->start_time->format('d/m/Y H:i') }}</div>
                            @endif
                            @if($item->end_time)
                                <div><span style="color:#f87171">Kết thúc:</span> {{ $item->end_time->format('d/m/Y H:i') }}</div>
                            @endif
                            @if($item->end_time && $item->end_time->isPast())
                                <span class="badge bg-danger mt-1" style="font-size:.6rem">Đã hết hạn</span>
                            @endif
                        @else
                            <i class="fa-solid fa-infinity text-muted me-1"></i>Không giới hạn
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.banners.toggle', $item->id) }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm shadow-none" 
                                    style="padding:2px 0;background:none;border:none">
                                @if($item->status === 'active')
                                    <i class="fa-solid fa-toggle-on fa-lg" style="color:#4ade80" title="Bấm để tắt"></i>
                                @else
                                    <i class="fa-solid fa-toggle-off fa-lg text-muted" title="Bấm để bật"></i>
                                @endif
                            </button>
                        </form>
                    </td>
                    <td class="text-end pe-3">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('admin.banners.edit', $item->id) }}" class="btn btn-sm btn-outline-secondary" style="padding:4px 8px" title="Chỉnh sửa">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.banners.destroy', $item->id) }}" onsubmit="return confirm('Chắc chắn xóa?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" style="padding:4px 8px" title="Xóa vĩnh viễn">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="fa-solid fa-images fa-2x mb-3 opacity-25 d-block"></i>
                        Vẫn chưa có banner / quảng cáo nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($banners->hasPages())
    <div class="card-footer bg-transparent border-secondary border-opacity-25 py-2">
        {{ $banners->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
