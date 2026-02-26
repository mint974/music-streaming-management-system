{{--
    Reusable genre form fields.
    Variables:
      $genre  — Genre model (null when creating)
      $prefix — 'create' | 'edit' (for unique input IDs)
--}}
@php
$iconGroups = [
    'Âm nhạc' => [
        'fa-solid fa-music','fa-solid fa-guitar','fa-solid fa-drum',
        'fa-solid fa-headphones','fa-solid fa-headphones-simple',
        'fa-solid fa-microphone','fa-solid fa-microphone-lines',
        'fa-solid fa-radio','fa-solid fa-compact-disc',
        'fa-solid fa-volume-high','fa-solid fa-volume-low',
        'fa-solid fa-tower-broadcast','fa-solid fa-sliders',
        'fa-solid fa-podcast','fa-solid fa-record-vinyl',
        'fa-solid fa-wave-square',
    ],
    'Tâm trạng' => [
        'fa-solid fa-fire','fa-solid fa-fire-flame-curved',
        'fa-solid fa-heart','fa-solid fa-heart-pulse',
        'fa-solid fa-face-smile','fa-solid fa-face-laugh',
        'fa-solid fa-face-sad-tear','fa-solid fa-face-angry',
        'fa-solid fa-bolt','fa-solid fa-star','fa-solid fa-crown',
        'fa-solid fa-gem','fa-solid fa-infinity',
        'fa-solid fa-peace','fa-solid fa-yin-yang',
        'fa-solid fa-moon','fa-solid fa-sun',
        'fa-solid fa-snowflake','fa-solid fa-leaf','fa-solid fa-rainbow',
    ],
    'Phong cách' => [
        'fa-solid fa-shield-halved','fa-solid fa-skull',
        'fa-solid fa-ghost','fa-solid fa-robot',
        'fa-solid fa-hat-cowboy','fa-solid fa-church',
        'fa-solid fa-mosque','fa-solid fa-city',
        'fa-solid fa-mountain','fa-solid fa-globe',
        'fa-solid fa-earth-americas','fa-solid fa-flag',
        'fa-solid fa-palette','fa-solid fa-film',
        'fa-solid fa-clapperboard','fa-solid fa-tv',
    ],
    'Hoạt động' => [
        'fa-solid fa-dumbbell','fa-solid fa-gamepad',
        'fa-solid fa-trophy','fa-solid fa-medal',
        'fa-solid fa-person-running','fa-solid fa-bicycle',
        'fa-solid fa-car','fa-solid fa-jet-fighter',
        'fa-solid fa-rocket','fa-solid fa-sailboat',
        'fa-solid fa-champagne-glasses','fa-solid fa-dice',
    ],
    'Thiên nhiên' => [
        'fa-solid fa-cloud','fa-solid fa-cloud-bolt',
        'fa-solid fa-cloud-rain','fa-solid fa-wind',
        'fa-solid fa-water','fa-solid fa-fire-flame-simple',
        'fa-solid fa-seedling','fa-solid fa-tree',
        'fa-solid fa-mountain-sun','fa-solid fa-sun-plant-wilt',
        'fa-solid fa-dove','fa-solid fa-dragon',
    ],
    'Thương hiệu' => [
        'fa-brands fa-youtube','fa-brands fa-spotify',
        'fa-brands fa-itunes-note','fa-brands fa-apple',
        'fa-brands fa-soundcloud','fa-brands fa-lastfm',
    ],
];
$currentIcon  = old('icon',  $genre?->getOriginal('icon')  ?? 'fa-solid fa-music');
$currentColor = old('color', $genre?->getOriginal('color') ?? '#6366f1');
@endphp

<div class="row g-3">

    {{-- ── Left column ── --}}
    <div class="col-md-7">

        {{-- Name --}}
        <div class="mb-3">
            <label class="form-label text-muted small">
                Tên thể loại <span class="text-danger">*</span>
            </label>
            <input type="text" name="name"
                   value="{{ old('name', $genre?->name) }}"
                   class="form-control form-control-sm bg-dark border-secondary text-white @error('name') is-invalid @enderror"
                   placeholder="Ví dụ: Pop, Rock, Jazz..." maxlength="100" required>
            @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Description --}}
        <div class="mb-3">
            <label class="form-label text-muted small">Mô tả</label>
            <textarea name="description" rows="2"
                      class="form-control form-control-sm bg-dark border-secondary text-white @error('description') is-invalid @enderror"
                      placeholder="Mô tả ngắn về thể loại..." maxlength="500">{{ old('description', $genre?->description) }}</textarea>
            @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- ── Visual Icon Picker ──────────────────────────────────── --}}
        <div class="mb-3">
            <label class="form-label text-muted small">Icon thể loại</label>

            {{-- Hidden input — submitted with the form --}}
            <input type="hidden" name="icon" class="genre-icon-hidden" value="{{ $currentIcon }}">

            {{-- Trigger row (preview box + current class label + open button) --}}
            <button type="button"
                    class="icon-picker-trigger d-flex align-items-center gap-3 w-100 text-start"
                    data-picker-prefix="{{ $prefix }}"
                    style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.12);
                           border-radius:10px;padding:10px 14px;cursor:pointer;transition:border-color .2s">
                <div class="icon-preview-box genre-icon-preview"
                     style="width:44px;height:44px;font-size:1.15rem;flex-shrink:0;border-radius:10px;
                            display:flex;align-items:center;justify-content:center;
                            transition:background .2s,border-color .2s">
                    <i class="{{ $currentIcon }}" style="color:{{ $currentColor }}"></i>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <code class="text-info genre-icon-label d-block text-truncate" style="font-size:.72rem">{{ $currentIcon }}</code>
                    <span class="text-muted" style="font-size:.68rem">Nhấn để chọn icon</span>
                </div>
                <i class="fa-solid fa-chevron-down text-muted small picker-chevron" style="transition:transform .2s"></i>
            </button>

            {{-- Picker panel (hidden by default) --}}
            <div class="icon-picker-panel" id="iconPanel_{{ $prefix }}"
                 style="display:none;margin-top:6px;background:#1e293b;
                        border:1px solid rgba(255,255,255,.12);border-radius:12px;
                        overflow:hidden;box-shadow:0 12px 32px rgba(0,0,0,.5)">

                {{-- Search bar --}}
                <div style="padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.07)">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"
                              style="background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.12);color:#64748b">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" class="form-control icon-search-input"
                               style="background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.12);color:#e2e8f0"
                               placeholder="Tìm icon... (ví dụ: music, fire, heart)">
                    </div>
                </div>

                {{-- Category tabs --}}
                <div class="d-flex flex-wrap gap-1 px-3 pt-2 pb-1 icon-cat-tabs"
                     style="border-bottom:1px solid rgba(255,255,255,.06)">
                    <button type="button" class="icon-tab-btn active" data-cat="all"
                            style="font-size:.67rem;padding:2px 10px;border-radius:50px;
                                   border:1px solid rgba(99,102,241,.4);
                                   background:rgba(99,102,241,.2);color:#a5b4fc;cursor:pointer">
                        Tất cả
                    </button>
                    @foreach(array_keys($iconGroups) as $cat)
                    <button type="button" class="icon-tab-btn" data-cat="{{ $cat }}"
                            style="font-size:.67rem;padding:2px 10px;border-radius:50px;
                                   border:1px solid rgba(255,255,255,.1);
                                   background:transparent;color:#64748b;cursor:pointer">
                        {{ $cat }}
                    </button>
                    @endforeach
                </div>

                {{-- Icon grid --}}
                <div class="icon-picker-grid"
                     style="display:grid;grid-template-columns:repeat(auto-fill,minmax(52px,1fr));
                            gap:4px;padding:10px 12px;max-height:216px;overflow-y:auto;
                            scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.1) transparent">
                    @foreach($iconGroups as $cat => $icons)
                        @foreach($icons as $iconCls)
                        <button type="button"
                                class="icon-cell {{ $iconCls === $currentIcon ? 'icon-cell-selected' : '' }}"
                                data-icon="{{ $iconCls }}"
                                data-cat="{{ $cat }}"
                                title="{{ $iconCls }}"
                                style="aspect-ratio:1;border-radius:10px;
                                       border:1px solid {{ $iconCls === $currentIcon ? 'rgba(99,102,241,.6)' : 'transparent' }};
                                       background:{{ $iconCls === $currentIcon ? 'rgba(99,102,241,.18)' : 'rgba(255,255,255,.04)' }};
                                       display:flex;align-items:center;justify-content:center;
                                       cursor:pointer;font-size:1rem;
                                       color:{{ $iconCls === $currentIcon ? '#a5b4fc' : '#64748b' }};
                                       transition:all .15s">
                            <i class="{{ $iconCls }}"></i>
                        </button>
                        @endforeach
                    @endforeach
                </div>

            </div>{{-- /icon-picker-panel --}}

            @error('icon')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        {{-- Active toggle --}}
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch"
                   name="is_active" id="isActive_{{ $prefix }}" value="1"
                   {{ old('is_active', $genre ? ($genre->is_active ? '1' : '') : '1') ? 'checked' : '' }}>
            <label class="form-check-label text-muted small" for="isActive_{{ $prefix }}">
                Hiển thị thể loại này
            </label>
        </div>

    </div>

    {{-- ── Right column ── --}}
    <div class="col-md-5">

        {{-- Color picker --}}
        <div class="mb-3">
            <label class="form-label text-muted small">Màu icon</label>
            <div class="d-flex align-items-center gap-2">
                <input type="color" name="color"
                       value="{{ $currentColor }}"
                       class="form-control form-control-color genre-color-input"
                       style="width:48px;height:34px;padding:2px;background:transparent;border:1px solid rgba(255,255,255,.15);border-radius:8px">
                <span class="text-muted small">Chọn màu cho icon &amp; nền</span>
            </div>
            @error('color')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        {{-- Color palette shortcuts --}}
        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach(['#6366f1','#ec4899','#f59e0b','#10b981','#3b82f6','#ef4444','#8b5cf6','#06b6d4','#f97316','#84cc16'] as $preset)
            <button type="button" class="color-preset-btn"
                    data-color="{{ $preset }}"
                    style="width:22px;height:22px;border-radius:50%;background:{{ $preset }};
                           border:2px solid transparent;cursor:pointer;
                           transition:transform .15s,border-color .15s"
                    title="{{ $preset }}"></button>
            @endforeach
        </div>

        {{-- Cover image --}}
        <div class="mb-3">
            <label class="form-label text-muted small">Ảnh bìa thể loại</label>

            @if($genre?->cover_image)
            <img src="{{ Storage::url($genre->cover_image) }}"
                 class="cover-preview show mb-2" alt="Ảnh bìa">
            @else
            <img src="" class="cover-preview mb-2" alt="Ảnh bìa">
            @endif

            <input type="file" name="cover_image" accept="image/*"
                   class="form-control form-control-sm bg-dark border-secondary text-white genre-cover-input @error('cover_image') is-invalid @enderror">
            @error('cover_image')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="text-muted mt-1" style="font-size:.7rem">
                PNG, JPG, WEBP — tối đa 2 MB
            </div>

            @if($genre?->cover_image)
            <div class="form-check mt-2">
                <input type="checkbox" class="form-check-input" name="remove_cover" id="removeCover_{{ $prefix }}" value="1">
                <label class="form-check-label text-muted small" for="removeCover_{{ $prefix }}">
                    Xóa ảnh bìa hiện tại
                </label>
            </div>
            @endif
        </div>

    </div>
</div>
