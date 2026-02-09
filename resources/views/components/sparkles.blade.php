@props([
    'count' => 15,
    'containerClass' => 'sparkles-container'
])

<div class="{{ $containerClass }}" data-sparkle-count="{{ $count }}"></div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const containers = document.querySelectorAll('.sparkles-container');
    
    // Màu sắc từ theme - lấy từ CSS variables
    const rootStyles = getComputedStyle(document.documentElement);
    const colors = [
        hexToRgba(rootStyles.getPropertyValue('--primary-blue-light').trim(), 0.8),  // primary-blue-light
        hexToRgba(rootStyles.getPropertyValue('--purple-main').trim(), 0.8),         // purple-main
        hexToRgba(rootStyles.getPropertyValue('--purple-light').trim(), 0.8),        // purple-light
        hexToRgba(rootStyles.getPropertyValue('--primary-blue').trim(), 0.7),        // primary-blue
        hexToRgba(rootStyles.getPropertyValue('--white-main').trim(), 0.6)           // white-main
    ];
    
    // Helper function để chuyển đổi hex sang rgba
    function hexToRgba(hex, alpha) {
        hex = hex.replace('#', '');
        const r = parseInt(hex.substring(0, 2), 16);
        const g = parseInt(hex.substring(2, 4), 16);
        const b = parseInt(hex.substring(4, 6), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }
    
    containers.forEach(container => {
        const count = parseInt(container.dataset.sparkleCount) || 15;
        
        for (let i = 0; i < count; i++) {
            createSparkle(container, colors);
        }
    });
    
    function createSparkle(container, colors) {
        const sparkle = document.createElement('div');
        sparkle.className = 'sparkle-random';
        sparkle.textContent = '✦';
        
        // Random position
        sparkle.style.left = Math.random() * 100 + '%';
        sparkle.style.top = Math.random() * 100 + '%';
        
        // Random color
        sparkle.style.color = colors[Math.floor(Math.random() * colors.length)];
        
        // Random size
        const size = 0.5 + Math.random() * 1.2;
        sparkle.style.fontSize = size + 'rem';
        
        // Random animation delay
        sparkle.style.animationDelay = Math.random() * 4 + 's';
        
        // Random animation duration
        sparkle.style.animationDuration = (1.5 + Math.random() * 2) + 's';
        
        container.appendChild(sparkle);
    }
});
</script>
@endpush

@once
@push('styles')
<style>
/* === Sparkles: soft twinkling stars as background padding (no rotation) === */

.sparkles-container {
  position: absolute;
  inset: 0;
  pointer-events: none;
  overflow: hidden;
  z-index: 1; /* keep low, just as background layer */
}

/* Base star */
.sparkle-random {
  position: absolute;
  line-height: 1;
  user-select: none;
  pointer-events: none;

  /* soft glow */
  text-shadow:
    0 0 6px currentColor,
    0 0 14px rgba(255, 255, 255, 0.18);

  /* softer look */
  filter: blur(0.15px);
  opacity: 0.12;

  /* twinkle only: no rotation */
  transform: translate3d(0, 0, 0) scale(0.9);
  transform-origin: center;
  will-change: opacity, transform, filter;

  animation-name: starTwinkle;
  animation-timing-function: ease-in-out;
  animation-iteration-count: infinite;

  /* optional: make it blend gently with background */
  mix-blend-mode: screen;
}

/* Add a subtle "bloom" layer (still no rotation) */
.sparkle-random::after {
  content: '✦';
  position: absolute;
  inset: 0;
  opacity: 0.22;
  transform: scale(1.25);
  filter: blur(1.4px);
  pointer-events: none;
}

/* Twinkle: gentle fade + slight breathing scale + tiny blur change */
@keyframes starTwinkle {
  0%, 100% {
    opacity: 0.08;
    transform: translate3d(0, 0, 0) scale(0.85);
    filter: blur(0.2px);
  }
  20% {
    opacity: 0.22;
    transform: translate3d(0, 0, 0) scale(0.95);
    filter: blur(0.15px);
  }
  45% {
    opacity: 0.55;
    transform: translate3d(0, 0, 0) scale(1.08);
    filter: blur(0.05px);
  }
  60% {
    opacity: 0.28;
    transform: translate3d(0, 0, 0) scale(0.98);
    filter: blur(0.12px);
  }
  80% {
    opacity: 0.42;
    transform: translate3d(0, 0, 0) scale(1.03);
    filter: blur(0.08px);
  }
}

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {
  .sparkle-random {
    animation: none !important;
    opacity: 0.18;
  }
}

</style>
@endpush
@endonce
