(() => {
    'use strict';

    const audio = document.getElementById('globalAudioPlayer');
    const playBtn = document.getElementById('playerPlayBtn');
    const favoriteBtn = document.getElementById('playerFavoriteBtn');
    const trackThumb = document.getElementById('playerTrackThumb');
    const trackTitle = document.getElementById('playerTrackTitle');
    const trackArtist = document.getElementById('playerTrackArtist');
    const currentTimeEl = document.getElementById('playerCurrentTime');
    const durationEl = document.getElementById('playerDuration');
    const progressWrap = document.getElementById('playerProgress');
    const progressFill = document.getElementById('playerProgressFill');
    const progressThumb = document.getElementById('playerProgressThumb');
    const volumeInput = document.getElementById('playerVolume');

    if (!audio || !playBtn) {
        return;
    }

    let currentSongId = null;

    function formatTime(seconds) {
        const s = Math.max(0, Math.floor(seconds || 0));
        const minutes = Math.floor(s / 60);
        const sec = s % 60;
        return `${minutes}:${String(sec).padStart(2, '0')}`;
    }

    function updatePlayIcon() {
        const icon = playBtn.querySelector('i');
        if (!icon) return;

        const isPlaying = !audio.paused && !audio.ended;
        icon.classList.toggle('fa-play', !isPlaying);
        icon.classList.toggle('fa-pause', isPlaying);
    }

    function updateProgress() {
        const current = audio.currentTime || 0;
        const duration = Number.isFinite(audio.duration) ? audio.duration : 0;
        const percent = duration > 0 ? Math.min(100, (current / duration) * 100) : 0;

        if (currentTimeEl) currentTimeEl.textContent = formatTime(current);
        if (durationEl) durationEl.textContent = formatTime(duration);
        if (progressFill) progressFill.style.width = `${percent}%`;
        if (progressThumb) progressThumb.style.left = `${percent}%`;
    }

    function setNowPlaying(trigger) {
        const songId = trigger.dataset.songId || null;
        const streamUrl = trigger.dataset.streamUrl || '';

        if (!streamUrl) return;

        currentSongId = songId;

        if (trackTitle) {
            trackTitle.textContent = trigger.dataset.songTitle || 'Bài hát';
        }

        if (trackArtist) {
            trackArtist.textContent = trigger.dataset.songArtist || 'Nghệ sĩ';
        }

        if (trackThumb && trigger.dataset.songCover) {
            trackThumb.src = trigger.dataset.songCover;
        }

        if (audio.src !== streamUrl) {
            audio.src = streamUrl;
            audio.load();
        }

        audio.play().catch(() => {
            // Ignore autoplay rejection; user can click play again.
        });

        document.querySelectorAll('.js-play-song').forEach((el) => {
            el.classList.toggle('is-current', currentSongId && el.dataset.songId === currentSongId);
        });
    }

    playBtn.addEventListener('click', () => {
        if (!audio.src) return;
        if (audio.paused) {
            audio.play().catch(() => {});
        } else {
            audio.pause();
        }
    });

    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', () => {
            favoriteBtn.classList.toggle('active');
            const icon = favoriteBtn.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-regular');
                icon.classList.toggle('fa-solid');
            }
        });
    }

    if (progressWrap) {
        progressWrap.addEventListener('click', (e) => {
            if (!Number.isFinite(audio.duration) || audio.duration <= 0) return;
            const rect = progressWrap.getBoundingClientRect();
            const ratio = Math.min(1, Math.max(0, (e.clientX - rect.left) / rect.width));
            audio.currentTime = ratio * audio.duration;
        });
    }

    if (volumeInput) {
        audio.volume = Number(volumeInput.value) / 100;
        volumeInput.addEventListener('input', () => {
            audio.volume = Number(volumeInput.value) / 100;
        });
    }

    audio.addEventListener('loadedmetadata', updateProgress);
    audio.addEventListener('timeupdate', updateProgress);
    audio.addEventListener('play', updatePlayIcon);
    audio.addEventListener('pause', updatePlayIcon);
    audio.addEventListener('ended', updatePlayIcon);

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('.js-play-song');
        if (!trigger) return;

        e.preventDefault();
        setNowPlaying(trigger);
    });

    updatePlayIcon();
    updateProgress();
})();
