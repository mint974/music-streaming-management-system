function initGlobalScrollAnimations() {
    const observerOptions = {
        root: null,
        rootMargin: '0px 0px -50px 0px', // Trigger slightly before it hits the bottom
        threshold: 0.05 // Trigger when 5% visible
    };

    // Keep track of timeout for batching
    let staggerTimeout = null;
    let pendingElements = [];
    let currentIndex = 0;

    const animateObserver = new IntersectionObserver((entries, observer) => {
        // Find elements that just entered the viewport
        const intersecting = entries.filter(entry => entry.isIntersecting);
        
        if (intersecting.length > 0) {
            intersecting.forEach(entry => {
                pendingElements.push(entry.target);
                observer.unobserve(entry.target);
            });

            // Process the batch
            if (!staggerTimeout) {
                staggerTimeout = setTimeout(() => {
                    pendingElements.forEach((el, i) => {
                        setTimeout(() => {
                            el.classList.add('animate-in');
                        }, 150 * i); // 150ms stagger between elements in the same batch
                    });
                    
                    // Reset batch
                    pendingElements = [];
                    staggerTimeout = null;
                }, 50); // Wait 50ms to gather all elements entering in this frame
            }
        }
    }, observerOptions);

    // Select all elements that should animate (parents AND children directly)
    const selectors = [
        '.animate-on-scroll',
        '.stagger-item',
        '.song-media-card',
        '.genre-card',
        '.artist-card-item',
        '.wire-top3-item',
        '.wire-list-item'
    ];
    
    // Convert NodeList to Array and filter out unique elements
    const elementsToAnimate = Array.from(document.querySelectorAll(selectors.join(', ')));
    
    elementsToAnimate.forEach(el => {
        // Important: Prevent double observation / reset
        el.classList.remove('animate-in');
        
        // Setup initial physical css states directly via inline to override
        el.style.opacity = '0';
        el.style.transform = 'translateY(40px)';
        el.style.transition = 'opacity 1s cubic-bezier(0.25, 1, 0.5, 1), transform 1s cubic-bezier(0.25, 1, 0.5, 1)';
        
        animateObserver.observe(el);
    });
}

// Setup listeners for normal load and simulated SPA navigations
document.addEventListener('DOMContentLoaded', initGlobalScrollAnimations);
document.addEventListener('turbo:load', initGlobalScrollAnimations);
document.addEventListener('livewire:navigated', initGlobalScrollAnimations);
document.addEventListener('pjax:end', initGlobalScrollAnimations);
document.addEventListener('htmx:load', initGlobalScrollAnimations);
document.addEventListener('htmx:afterSettle', initGlobalScrollAnimations);

// Global CSS appended dynamically
if (!document.getElementById('global-anim-style')) {
    const style = document.createElement('style');
    style.id = 'global-anim-style';
    style.innerHTML = `
        .animate-in {
            opacity: 1 !important;
            transform: translateY(0) scale(1) !important;
        }
    `;
    document.head.appendChild(style);
}
