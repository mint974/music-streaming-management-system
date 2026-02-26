@extends('layouts.admin')

@section('title', 'Thể loại nhạc')
@section('page-title', 'Quản lý thể loại nhạc')
@section('page-subtitle', 'Thêm, sửa, sắp xếp và ẩn/hiện thể loại âm nhạc')

@push('styles')
<style>
.genre-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 14px;
}

.genre-card {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 16px;
    overflow: hidden;
    transition: border-color .2s, transform .15s, box-shadow .2s;
    cursor: grab;
    position: relative;
}
.genre-card:hover {
    border-color: rgba(255,255,255,.16);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,.3);
}
.genre-card.sortable-ghost {
    opacity: .35;
    border: 2px dashed rgba(99,102,241,.5);
}
.genre-card.sortable-chosen { cursor: grabbing; }
.genre-card.is-inactive { opacity: .5; }

.genre-card-cover {
    height: 90px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    background: rgba(255,255,255,.03);
}
.genre-card-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    inset: 0;
}
.genre-card-cover .genre-icon-wrap {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    position: relative;
    z-index: 1;
}

.genre-drag-handle {
    position: absolute;
    top: 8px;
    left: 10px;
    color: rgba(255,255,255,.2);
    font-size: .8rem;
    cursor: grab;
    z-index: 2;
    transition: color .15s;
}
.genre-card:hover .genre-drag-handle { color: rgba(255,255,255,.5); }

.genre-card-body { padding: 10px 12px 12px; }
.genre-card-name {
    font-weight: 700;
    font-size: .88rem;
    color: #e2e8f0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 2px;
}
.genre-card-slug {
    font-size: .68rem;
    color: #475569;
    font-family: monospace;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 6px;
}
.genre-card-desc {
    font-size: .72rem;
    color: #64748b;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 2.1em;
    margin-bottom: 8px;
}
.genre-card-actions {
    display: flex;
    gap: 4px;
    align-items: center;
}
.genre-card-actions .btn { padding: 3px 7px; font-size: .72rem; border-radius: 8px; }

/* icon preview in form */
.icon-preview-box {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
    transition: background .2s;
}

/* cover image preview */
.cover-preview {
    width: 100%;
    height: 100px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,.1);
    display: none;
}
.cover-preview.show { display: block; }

/* sort order badge */
.sort-badge {
    position: absolute;
    top: 8px;
    right: 10px;
    background: rgba(0,0,0,.5);
    color: rgba(255,255,255,.5);
    font-size: .6rem;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 50px;
    z-index: 2;
}

.saving-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.4);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    display: none;
}

/* ── Icon picker cells ────────────────── */
.icon-cell:hover {
    background: rgba(255,255,255,.09) !important;
    border-color: rgba(255,255,255,.15) !important;
    color: #e2e8f0 !important;
}
.icon-cell-selected,
.icon-cell.icon-cell-selected {
    background: rgba(99,102,241,.18) !important;
    border-color: rgba(99,102,241,.55) !important;
    color: #a5b4fc !important;
}

/* ── Icon picker tab buttons ──────────── */
.icon-tab-btn:hover:not(.active) {
    background: rgba(255,255,255,.06) !important;
    color: #cbd5e1 !important;
}
.icon-tab-btn.active {
    background: rgba(99,102,241,.2) !important;
    border-color: rgba(99,102,241,.4) !important;
    color: #a5b4fc !important;
}

/* ── Trigger button hover ─────────────── */
.icon-picker-trigger:hover {
    border-color: rgba(255,255,255,.25) !important;
}
.icon-picker-trigger.open {
    border-color: rgba(99,102,241,.5) !important;
}
.icon-picker-trigger.open .picker-chevron {
    transform: rotate(180deg);
}

/* color preset button hover */
.color-preset-btn:hover {
    transform: scale(1.25);
    border-color: rgba(255,255,255,.5) !important;
}
</style>
@endpush

@section('content')

{{-- ─── Flash messages ─── --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <i class="fa-solid fa-circle-check me-2"></i>{!! session('success') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>{!! session('error') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ─── Stat cards ─── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="rounded-3 p-3 text-center" style="background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2)">
            <div class="fw-bold text-white" style="font-size:1.6rem">{{ number_format($stats['total']) }}</div>
            <div class="text-muted small">Tổng thể loại</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="rounded-3 p-3 text-center" style="background:rgba(34,197,94,.07);border:1px solid rgba(34,197,94,.2)">
            <div class="fw-bold text-white" style="font-size:1.6rem">{{ number_format($stats['active']) }}</div>
            <div class="text-muted small">Đang hiện</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="rounded-3 p-3 text-center" style="background:rgba(107,114,128,.07);border:1px solid rgba(107,114,128,.2)">
            <div class="fw-bold text-white" style="font-size:1.6rem">{{ number_format($stats['inactive']) }}</div>
            <div class="text-muted small">Đang ẩn</div>
        </div>
    </div>
</div>

{{-- ─── Toolbar ─── --}}
<div class="d-flex align-items-center flex-wrap gap-2 mb-4">
    {{-- Search --}}
    <form method="GET" action="{{ route('admin.genres.index') }}" class="d-flex gap-2 flex-grow-1">
        <div class="input-group input-group-sm" style="max-width:320px">
            <span class="input-group-text bg-dark border-secondary text-muted">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input type="text" name="search" value="{{ $search }}"
                   class="form-control form-control-sm bg-dark border-secondary text-white"
                   placeholder="Tìm thể loại..." autocomplete="off">
            @if($search)
            <a href="{{ route('admin.genres.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-xmark"></i>
            </a>
            @endif
        </div>
        <button type="submit" class="btn btn-sm btn-outline-secondary">Tìm</button>
    </form>

    <div class="ms-auto d-flex gap-2">
        @if(!$search)
        <button class="btn btn-sm btn-outline-secondary" id="saveOrderBtn" style="display:none!important" onclick="saveOrder()">
            <i class="fa-solid fa-floppy-disk me-1"></i>Lưu thứ tự
        </button>
        @endif
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createGenreModal">
            <i class="fa-solid fa-plus me-1"></i>Thêm thể loại
        </button>
    </div>
</div>

{{-- ─── Genre grid (drag-sortable) ─── --}}
@if($genres->isEmpty())
<div class="text-center py-5" style="color:#475569">
    <i class="fa-solid fa-layer-group fa-3x mb-3 d-block" style="opacity:.15"></i>
    @if($search)
        Không tìm thấy thể loại nào cho "<strong>{{ $search }}</strong>".
    @else
        Chưa có thể loại nào. <button class="btn btn-link p-0 text-primary" data-bs-toggle="modal" data-bs-target="#createGenreModal">Thêm ngay</button>
    @endif
</div>
@else

<div class="genre-grid" id="genreGrid">
    @foreach($genres as $genre)
    <div class="genre-card {{ $genre->is_active ? '' : 'is-inactive' }}" data-id="{{ $genre->id }}">

        {{-- Drag handle --}}
        @if(!$search)
        <div class="genre-drag-handle" title="Kéo để sắp xếp">
            <i class="fa-solid fa-grip-vertical"></i>
        </div>
        @endif

        {{-- Sort order badge --}}
        <span class="sort-badge">#{{ $genre->sort_order + 1 }}</span>

        {{-- Cover / Icon --}}
        <div class="genre-card-cover">
            @if($genre->cover_image)
                <img src="{{ Storage::url($genre->cover_image) }}" alt="{{ $genre->name }}">
                <div style="position:absolute;inset:0;background:rgba(0,0,0,.45)"></div>
            @endif
            <div class="genre-icon-wrap" style="{{ $genre->iconBgStyle() }}">
                <i class="{{ $genre->icon }}" style="color:{{ $genre->color }}"></i>
            </div>
        </div>

        <div class="genre-card-body">
            <div class="genre-card-name">{{ $genre->name }}</div>
            <div class="genre-card-slug">/{{ $genre->slug }}</div>
            <div class="genre-card-desc">{{ $genre->description ?: '—' }}</div>

            <div class="d-flex align-items-center justify-content-between gap-1">
                {{-- Status badge --}}
                @if($genre->is_active)
                <span style="font-size:.65rem;background:rgba(34,197,94,.1);color:#86efac;border:1px solid rgba(34,197,94,.25);border-radius:50px;padding:2px 8px">
                    <i class="fa-solid fa-circle-check me-1"></i>Hiện
                </span>
                @else
                <span style="font-size:.65rem;background:rgba(107,114,128,.1);color:#9ca3af;border:1px solid rgba(107,114,128,.25);border-radius:50px;padding:2px 8px">
                    <i class="fa-solid fa-eye-slash me-1"></i>Ẩn
                </span>
                @endif

                <div class="genre-card-actions">
                    {{-- Edit --}}
                    <button class="btn btn-outline-secondary btn-edit-genre"
                            data-id="{{ $genre->id }}"
                            data-name="{{ $genre->name }}"
                            data-desc="{{ $genre->description }}"
                            data-icon="{{ $genre->icon }}"
                            data-color="{{ $genre->color }}"
                            data-cover="{{ $genre->cover_image ? Storage::url($genre->cover_image) : '' }}"
                            data-active="{{ $genre->is_active ? '1' : '0' }}"
                            data-bs-toggle="modal" data-bs-target="#editGenreModal"
                            title="Sửa">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>

                    {{-- Toggle active --}}
                    <form method="POST" action="{{ route('admin.genres.toggleActive', $genre->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn {{ $genre->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                title="{{ $genre->is_active ? 'Ẩn thể loại' : 'Kích hoạt' }}">
                            <i class="fa-solid {{ $genre->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                        </button>
                    </form>

                    {{-- Delete --}}
                    <button class="btn btn-outline-danger btn-delete-genre"
                            data-id="{{ $genre->id }}"
                            data-name="{{ $genre->name }}"
                            data-bs-toggle="modal" data-bs-target="#deleteGenreModal"
                            title="Xóa">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if(!$search)
<p class="text-muted small mt-3 mb-0">
    <i class="fa-solid fa-info-circle me-1"></i>
    Kéo thả các thể loại để thay đổi thứ tự hiển thị. Nhấn <strong>Lưu thứ tự</strong> sau khi sắp xếp xong.
</p>
@endif

@endif {{-- end genres not empty --}}


{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- ─── Modal: Thêm thể loại ─── --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="createGenreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-plus me-2" style="color:#818cf8"></i>Thêm thể loại mới
                </h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.genres.store') }}" enctype="multipart/form-data" id="createGenreForm">
                @csrf
                <div class="modal-body">
                    @include('admin.genres._form', ['genre' => null, 'prefix' => 'create'])
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-plus me-1"></i>Tạo thể loại
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─── Modal: Sửa thể loại ─── --}}
<div class="modal fade" id="editGenreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-pen-to-square me-2" style="color:#818cf8"></i>Sửa thể loại
                </h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editGenreForm" action="" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    @include('admin.genres._form', ['genre' => null, 'prefix' => 'edit'])
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─── Modal: Xóa thể loại ─── --}}
<div class="modal fade" id="deleteGenreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white">
                    <i class="fa-solid fa-trash me-2 text-danger"></i>Xóa thể loại
                </h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="deleteGenreForm" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p class="text-muted small mb-0">
                        Bạn có chắc muốn xóa thể loại
                        <strong class="text-white" id="deleteGenreName"></strong>?
                        Hành động này <strong class="text-danger">không thể hoàn tác</strong>.
                    </p>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fa-solid fa-trash me-1"></i>Xóa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- saving overlay --}}
<div class="saving-overlay" id="savingOverlay">
    <div class="text-center text-white">
        <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
        Đang lưu thứ tự...
    </div>
</div>

@endsection

@push('scripts')
{{-- SortableJS CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
// ─── Drag-and-drop sort ───────────────────────────────────────────────────────
const grid      = document.getElementById('genreGrid');
const saveBtn   = document.getElementById('saveOrderBtn');
let   orderChanged = false;

@if(!$search && $genres->isNotEmpty())
const sortable = Sortable.create(grid, {
    animation: 150,
    ghostClass: 'sortable-ghost',
    chosenClass: 'sortable-chosen',
    handle: '.genre-drag-handle',
    onEnd() {
        orderChanged = true;
        if (saveBtn) {
            saveBtn.style.display = '';
            saveBtn.style.removeProperty('display');
            // fix the !important override
            saveBtn.setAttribute('style', '');
        }
        // Update #N badges visually
        grid.querySelectorAll('.genre-card').forEach((card, i) => {
            card.querySelector('.sort-badge').textContent = '#' + (i + 1);
        });
    }
});
@endif

function saveOrder() {
    if (!orderChanged) return;
    const ids = [...grid.querySelectorAll('.genre-card')].map(c => parseInt(c.dataset.id));
    document.getElementById('savingOverlay').style.display = 'flex';

    fetch('{{ route('admin.genres.reorder') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ order: ids })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('savingOverlay').style.display = 'none';
        if (data.ok) {
            orderChanged = false;
            if (saveBtn) saveBtn.style.display = 'none';
            showToast('Đã lưu thứ tự thể loại.', 'success');
        }
    })
    .catch(() => {
        document.getElementById('savingOverlay').style.display = 'none';
        showToast('Lỗi khi lưu thứ tự. Vui lòng thử lại.', 'danger');
    });
}

// simple toast
function showToast(msg, type = 'success') {
    const colors = { success: '#22c55e', danger: '#ef4444', info: '#818cf8' };
    const el = document.createElement('div');
    el.innerHTML = `<div style="position:fixed;bottom:24px;right:24px;z-index:9999;background:#1e293b;border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:12px 18px;color:#e2e8f0;font-size:.85rem;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,.4)">
        <span style="width:8px;height:8px;border-radius:50%;background:${colors[type]};flex-shrink:0"></span>${msg}</div>`;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3000);
}

// Warn before leaving if unsaved sort
window.addEventListener('beforeunload', e => {
    if (orderChanged) { e.preventDefault(); e.returnValue = ''; }
});

// ─── Edit modal population ────────────────────────────────────────────────────
document.querySelectorAll('.btn-edit-genre').forEach(btn => {
    btn.addEventListener('click', function () {
        const form   = document.getElementById('editGenreForm');
        const id     = this.dataset.id;

        form.action = '{{ url("/admin/genres") }}/' + id;

        form.querySelector('[name=name]').value        = this.dataset.name;
        form.querySelector('[name=description]').value = this.dataset.desc || '';
        form.querySelector('[name=color]').value       = this.dataset.color || '#6366f1';
        form.querySelector('[name=is_active]').checked = this.dataset.active === '1';

        // sync icon picker
        syncPickerUI(form, this.dataset.icon || 'fa-solid fa-music', this.dataset.color || '#6366f1');

        // cover preview
        const coverPreview = form.querySelector('.cover-preview');
        const coverUrl     = this.dataset.cover;
        if (coverUrl) {
            coverPreview.src = coverUrl;
            coverPreview.classList.add('show');
        } else {
            coverPreview.classList.remove('show');
        }
    });
});

// ─── Delete modal population ──────────────────────────────────────────────────
document.querySelectorAll('.btn-delete-genre').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('deleteGenreName').textContent = this.dataset.name;
        document.getElementById('deleteGenreForm').action =
            '{{ url("/admin/genres") }}/' + this.dataset.id;
    });
});

// ─── Icon picker helpers ──────────────────────────────────────────────────────

/**
 * Parse a #rrggbb hex colour into { r, g, b }.
 */
function hexToRgb(hex) {
    const h = hex.replace('#', '');
    return {
        r: parseInt(h.substring(0, 2), 16),
        g: parseInt(h.substring(2, 4), 16),
        b: parseInt(h.substring(4, 6), 16),
    };
}

/**
 * Refresh the trigger-button preview box colour + icon.
 * previewEl = .genre-icon-preview inside the trigger button.
 */
function applyColorToPreview(previewEl, iconClass, color) {
    const { r, g, b } = hexToRgb(color);
    previewEl.style.background   = `rgba(${r},${g},${b},.18)`;
    previewEl.style.borderColor  = `rgba(${r},${g},${b},.35)`;
    previewEl.innerHTML = `<i class="${iconClass}" style="color:${color}"></i>`;
}

/**
 * Update the hidden input, trigger preview, trigger label, and selected cell
 * inside the given <form> element.
 */
function syncPickerUI(form, iconClass, color) {
    const hidden  = form.querySelector('.genre-icon-hidden');
    const trigger = form.querySelector('.icon-picker-trigger');
    if (!hidden || !trigger) return;

    const usedColor = color || form.querySelector('.genre-color-input')?.value || '#6366f1';

    // 1. hidden input
    hidden.value = iconClass;

    // 2. trigger preview box
    const preview = trigger.querySelector('.genre-icon-preview');
    if (preview) applyColorToPreview(preview, iconClass, usedColor);

    // 3. trigger label
    const label = trigger.querySelector('.genre-icon-label');
    if (label) label.textContent = iconClass;

    // 4. selected cell highlight
    form.querySelectorAll('.icon-cell').forEach(cell => {
        const isSelected = cell.dataset.icon === iconClass;
        cell.classList.toggle('icon-cell-selected', isSelected);
        // reset inline styles set at Blade render time
        if (isSelected) {
            cell.style.background   = '';
            cell.style.borderColor  = '';
            cell.style.color        = '';
        } else {
            cell.style.background   = '';
            cell.style.borderColor  = '';
            cell.style.color        = '';
        }
    });
}

/**
 * Bind all icon-picker behaviour inside a given <form>.
 */
function initIconPicker(form) {
    const trigger     = form.querySelector('.icon-picker-trigger');
    const panelId     = trigger?.dataset.pickerPrefix;
    const panel       = panelId ? document.getElementById('iconPanel_' + panelId) : null;
    const searchInput = panel?.querySelector('.icon-search-input');
    const colorInput  = form.querySelector('.genre-color-input');

    if (!trigger || !panel) return;

    // ── Toggle panel open / close ──
    trigger.addEventListener('click', () => {
        const isOpen = panel.style.display !== 'none';
        panel.style.display = isOpen ? 'none' : 'block';
        trigger.classList.toggle('open', !isOpen);
        if (!isOpen && searchInput) {
            searchInput.value = '';
            filterCells(panel, '', 'all');
            searchInput.focus();
        }
    });

    // Close panel when clicking outside
    document.addEventListener('click', e => {
        if (!form.contains(e.target)) {
            panel.style.display = 'none';
            trigger.classList.remove('open');
        }
    });

    // ── Search ──
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.trim().toLowerCase();
            const activeCat = panel.querySelector('.icon-tab-btn.active')?.dataset.cat || 'all';
            filterCells(panel, q, activeCat);
        });
    }

    // ── Category tabs ──
    panel.querySelectorAll('.icon-tab-btn').forEach(tab => {
        tab.addEventListener('click', () => {
            panel.querySelectorAll('.icon-tab-btn').forEach(t => {
                t.classList.remove('active');
                t.style.background   = '';
                t.style.borderColor  = '';
                t.style.color        = '';
            });
            tab.classList.add('active');
            const q = searchInput?.value.trim().toLowerCase() || '';
            filterCells(panel, q, tab.dataset.cat);
        });
    });

    // ── Cell click ──
    panel.querySelectorAll('.icon-cell').forEach(cell => {
        cell.addEventListener('click', () => {
            const chosen = cell.dataset.icon;
            syncPickerUI(form, chosen);
            panel.style.display = 'none';
            trigger.classList.remove('open');
        });
    });

    // ── Color change → refresh preview ──
    if (colorInput) {
        colorInput.addEventListener('input', () => {
            const hidden = form.querySelector('.genre-icon-hidden');
            const preview = trigger.querySelector('.genre-icon-preview');
            if (hidden && preview) applyColorToPreview(preview, hidden.value, colorInput.value);
        });
    }

    // ── Color preset buttons ──
    form.querySelectorAll('.color-preset-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const color = btn.dataset.color;
            if (colorInput) colorInput.value = color;
            const hidden = form.querySelector('.genre-icon-hidden');
            const preview = trigger.querySelector('.genre-icon-preview');
            if (hidden && preview) applyColorToPreview(preview, hidden.value, color);
        });
    });

    // ── Init preview on page load ──
    const hidden = form.querySelector('.genre-icon-hidden');
    if (hidden) {
        const usedColor = colorInput?.value || '#6366f1';
        const preview   = trigger.querySelector('.genre-icon-preview');
        if (preview) applyColorToPreview(preview, hidden.value, usedColor);
    }
}

/**
 * Show / hide cells matching search query and category.
 */
function filterCells(panel, query, cat) {
    panel.querySelectorAll('.icon-cell').forEach(cell => {
        const matchCat   = cat === 'all' || cell.dataset.cat === cat;
        const matchQuery = !query || cell.dataset.icon.includes(query);
        cell.style.display = (matchCat && matchQuery) ? '' : 'none';
    });
}

// ── Initialise pickers for both modals on page load ──
document.querySelectorAll('#createGenreForm, #editGenreForm').forEach(f => initIconPicker(f));

// ─── Cover image preview on file select ──────────────────────────────────────
document.querySelectorAll('.genre-cover-input').forEach(inp => {
    inp.addEventListener('change', function () {
        const preview = this.closest('form').querySelector('.cover-preview');
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.classList.add('show');
            };
            reader.readAsDataURL(this.files[0]);
        } else {
            preview.classList.remove('show');
        }
    });
});
</script>
@endpush
