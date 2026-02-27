@php
    $icon     = $icon    ?? 'fa-solid fa-hammer';
    $title    = $title   ?? 'Đang phát triển';
    $desc     = $desc    ?? 'Tính năng này đang được xây dựng.';
    $color    = $color   ?? '#c084fc';
    $bgColor  = $bgColor ?? 'rgba(168,85,247,.12)';
@endphp

<div class="d-flex flex-column align-items-center justify-content-center text-center"
     style="min-height:60vh;padding:2rem">

    <div style="width:80px;height:80px;border-radius:22px;background:{{ $bgColor }};border:1px solid {{ $color }}33;display:flex;align-items:center;justify-content:center;margin-bottom:24px">
        <i class="{{ $icon }}" style="font-size:2rem;color:{{ $color }}"></i>
    </div>

    <h3 class="text-white fw-bold mb-2">{{ $title }}</h3>
    <p class="text-muted mb-4" style="max-width:460px;font-size:.9rem;line-height:1.7">{{ $desc }}</p>

    <div class="d-inline-flex align-items-center gap-2 px-4 py-2 rounded-pill"
         style="background:rgba(255,255,255,.04);border:1px dashed rgba(255,255,255,.1);color:rgba(148,163,184,.7);font-size:.8rem">
        <i class="fa-solid fa-hammer" style="font-size:.8rem"></i>
        Đang được xây dựng — Sẽ ra mắt sớm
    </div>
</div>
