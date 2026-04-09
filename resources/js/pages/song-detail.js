function initSongDetail(container) {
    const lyricsBox = (container || document).querySelector('#lyricsBox');
    const toggleBtn = (container || document).querySelector('#lyricsToggleBtn');

    if (!lyricsBox || !toggleBtn) return;

    // Prevent duplicate binding
    if (lyricsBox.dataset.initialized) return;
    lyricsBox.dataset.initialized = 'true';

    function toggleLyrics() {
        const isExpanded = lyricsBox.classList.contains('lyrics-expanded');

        if (isExpanded) {
            // Collapse
            lyricsBox.classList.remove('lyrics-expanded');
            lyricsBox.classList.add('lyrics-preview');
            if(toggleBtn) toggleBtn.innerHTML = '<i class="fa-solid fa-chevron-down me-1"></i>Xem thêm lời bài hát';
        } else {
            // Expand
            lyricsBox.classList.remove('lyrics-preview');
            lyricsBox.classList.add('lyrics-expanded');
            if(toggleBtn) toggleBtn.innerHTML = '<i class="fa-solid fa-chevron-up me-1"></i>Ẩn lời bài hát';
        }
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleLyrics);
    }

    // Feature: Click in lyrics box to expand/collapse
    lyricsBox.addEventListener('click', toggleLyrics);
}

document.addEventListener('DOMContentLoaded', () => initSongDetail(document));
document.addEventListener('htmx:load', (e) => initSongDetail(e.detail.elt));
