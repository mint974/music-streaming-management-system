(() => {
    'use strict';

    const playerRoot = document.querySelector('.app-player');
    const audio = document.getElementById('globalAudioPlayer');
    const playBtn = document.getElementById('playerPlayBtn');
    const stopBtn = document.getElementById('playerStopBtn');
    const prevBtn = document.getElementById('playerPrevBtn');
    const nextBtn = document.getElementById('playerNextBtn');
    const shuffleBtn = document.getElementById('playerShuffleBtn');
    const repeatBtn = document.getElementById('playerRepeatBtn');
    const queueBtn = document.getElementById('playerQueueBtn');
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
    const noticeEl = document.getElementById('playerNotice');
    const roleBadgeEl = document.getElementById('playerRoleBadge');

    if (!playerRoot || !audio || !playBtn) {
        return;
    }

    const listenerRole = playerRoot.dataset.listenerRole || 'guest';
    const previewSeconds = Number(playerRoot.dataset.previewSeconds || 45);
    const loginUrl = playerRoot.dataset.loginUrl || '/login';
    const registerUrl = playerRoot.dataset.registerUrl || '/register';
    const upgradeUrl = playerRoot.dataset.upgradeUrl || '/login';
    const adAudioUrl = playerRoot.dataset.adAudioUrl || '';
    const isAuthenticated = playerRoot.dataset.isAuthenticated === '1';
    const favoriteToggleTemplate = playerRoot.dataset.favoriteToggleTemplate || '';
    const persistentStateKey = 'bwm_player_state';
    const capabilities = resolveCapabilities(listenerRole);

    let currentSong = null;
    let playbackQueue = [];
    let queueIndex = -1;
    let shuffleEnabled = false;
    let repeatMode = 'off';
    let previewTimerId = null;
    let noticeTimerId = null;
    let adPlaying = false;
    let adAudio = null;
    let pausedByVisibility = false;

    function resolveCapabilities(role) {
        switch (role) {
            case 'premium':
            case 'artist':
            case 'admin':
                return {
                    canPlayPremium: true,
                    canSkip: true,
                    canSeek: true,
                    canChangeVolume: true,
                    canQueue: true,
                    canPlaybackModes: true,
                    canBackground: true,
                    adAfterTrack: false,
                    previewOnly: false,
                    label: role === 'premium' ? 'Premium' : role === 'artist' ? 'Artist' : 'Admin',
                };
            case 'free':
                return {
                    canPlayPremium: false,
                    canSkip: true,
                    canSeek: true,
                    canChangeVolume: true,
                    canQueue: true,
                    canPlaybackModes: false,
                    canBackground: false,
                    adAfterTrack: true,
                    previewOnly: false,
                    label: 'Free',
                };
            default:
                return {
                    canPlayPremium: false,
                    canSkip: false,
                    canSeek: false,
                    canChangeVolume: false,
                    canQueue: false,
                    canPlaybackModes: false,
                    canBackground: false,
                    adAfterTrack: true,
                    previewOnly: true,
                    label: 'Guest',
                };
        }
    }

    function formatTime(seconds) {
        const total = Math.max(0, Math.floor(seconds || 0));
        const minutes = Math.floor(total / 60);
        const sec = total % 60;
        return `${minutes}:${String(sec).padStart(2, '0')}`;
    }

    function showNotice(message, timeout = 4500) {
        if (!noticeEl) return;

        noticeEl.textContent = message;
        noticeEl.style.opacity = '1';

        if (noticeTimerId) {
            window.clearTimeout(noticeTimerId);
        }

        if (timeout > 0) {
            noticeTimerId = window.setTimeout(() => {
                noticeEl.textContent = '';
                noticeEl.style.opacity = '0';
            }, timeout);
        }
    }

    function setButtonEnabled(button, enabled, fallbackTitle) {
        if (!button) return;

        button.disabled = !enabled;
        button.style.opacity = enabled ? '1' : '.45';
        button.style.cursor = enabled ? '' : 'not-allowed';

        if (!enabled && fallbackTitle) {
            button.title = fallbackTitle;
        }
    }

    function updateRoleBadge() {
        if (roleBadgeEl) {
            roleBadgeEl.textContent = capabilities.label;
        }
    }

    function updatePlayIcon() {
        const icon = playBtn.querySelector('i');
        if (!icon) return;

        const isPlaying = !audio.paused && !audio.ended;
        icon.classList.toggle('fa-play', !isPlaying);
        icon.classList.toggle('fa-pause', isPlaying);
    }

    function syncFavoriteIcon() {
        if (!favoriteBtn || !currentSong) return;

        const icon = favoriteBtn.querySelector('i');
        const favorited = !!currentSong.favorited;

        favoriteBtn.classList.toggle('active', favorited);
        if (icon) {
            icon.classList.toggle('fa-solid', favorited);
            icon.classList.toggle('fa-regular', !favorited);
        }
    }

    function clearPreviewGuard() {
        if (previewTimerId !== null) {
            window.clearInterval(previewTimerId);
            previewTimerId = null;
        }
    }

    function persistState() {
        if (!currentSong) return;

        window.localStorage.setItem(persistentStateKey, JSON.stringify({
            currentSong,
            queue: playbackQueue,
            queueIndex,
            currentTime: audio.currentTime || 0,
            volume: audio.volume,
            repeatMode,
            shuffleEnabled,
            paused: audio.paused,
        }));
    }

    function updateProgress() {
        const current = audio.currentTime || 0;
        const duration = Number.isFinite(audio.duration) ? audio.duration : 0;
        const percent = duration > 0 ? Math.min(100, (current / duration) * 100) : 0;

        if (currentTimeEl) currentTimeEl.textContent = formatTime(current);
        if (durationEl) durationEl.textContent = formatTime(duration);
        if (progressFill) progressFill.style.width = `${percent}%`;
        if (progressThumb) progressThumb.style.left = `${percent}%`;

        if (capabilities.previewOnly && current >= previewSeconds) {
            stopPreviewPlayback();
        }

        persistState();
    }

    function stopPlayback(resetTime = true) {
        audio.pause();
        if (resetTime) {
            audio.currentTime = 0;
        }
        clearPreviewGuard();
        updatePlayIcon();
        updateProgress();
    }

    function setAdLockState(locked) {
        adPlaying = locked;
        document.body.classList.toggle('ad-playing-lock', locked);

        // During ad playback, lock all player controls and song pick buttons.
        setButtonEnabled(playBtn, !locked, 'Đang phát quảng cáo, vui lòng chờ hết để nghe nhạc');
        setButtonEnabled(stopBtn, !locked, 'Đang phát quảng cáo, vui lòng chờ hết để nghe nhạc');
        setButtonEnabled(prevBtn, !locked && capabilities.canSkip, 'Đang phát quảng cáo, vui lòng chờ hết để nghe nhạc');
        setButtonEnabled(nextBtn, !locked && capabilities.canSkip, 'Đang phát quảng cáo, vui lòng chờ hết để nghe nhạc');
        setButtonEnabled(queueBtn, !locked && capabilities.canQueue, 'Đang phát quảng cáo, vui lòng chờ hết để nghe nhạc');
        setButtonEnabled(shuffleBtn, !locked && capabilities.canPlaybackModes, 'Đang phát quảng cáo, vui lòng chờ hết để nghe nhạc');
        setButtonEnabled(repeatBtn, !locked && capabilities.canPlaybackModes, 'Đang phát quảng cáo, vui lòng chờ hết để nghe nhạc');

        document.querySelectorAll('.js-play-song').forEach((button) => {
            button.disabled = locked;
            button.style.pointerEvents = locked ? 'none' : '';
            button.style.opacity = locked ? '.55' : '';
            button.title = locked ? 'Đang phát quảng cáo, tạm thời không thể chọn bài' : '';
        });

        if (volumeInput) {
            volumeInput.disabled = locked || !capabilities.canChangeVolume;
            volumeInput.style.opacity = (locked || !capabilities.canChangeVolume) ? '.45' : '1';
        }

        if (progressWrap) {
            const allowSeek = !locked && capabilities.canSeek;
            progressWrap.style.opacity = allowSeek ? '1' : '.55';
            progressWrap.style.cursor = allowSeek ? 'pointer' : 'not-allowed';
        }
    }

    function isPlaybackLockedByAd() {
        if (!adPlaying) return false;

        showNotice('Quảng cáo Premium đang phát. Vui lòng chờ hết trước khi chọn bài mới.', 3500);
        return true;
    }

    function playAdInterlude(reason) {
        if (!capabilities.adAfterTrack || adPlaying) {
            return Promise.resolve();
        }

        setAdLockState(true);
        const adMessage = reason === 'guest_preview'
            ? 'Blue Wave Music. Đăng ký tài khoản Free để nghe trọn bài và mở khóa nhiều quyền điều khiển hơn.'
            : 'Blue Wave Music. Nâng cấp Premium để nghe nhạc không quảng cáo và mở khóa toàn bộ tính năng.';

        showNotice('Đang phát quảng cáo...', 5000);

        if (adAudioUrl) {
            adAudio = new Audio(adAudioUrl);

            return new Promise((resolve) => {
                const cleanup = () => {
                    if (!adAudio) {
                        setAdLockState(false);
                        resolve();
                        return;
                    }

                    adAudio.onended = null;
                    adAudio.onerror = null;
                    adAudio = null;
                    setAdLockState(false);
                    resolve();
                };

                adAudio.onended = cleanup;
                adAudio.onerror = cleanup;

                adAudio.play().catch(() => {
                    cleanup();
                });
            });
        }

        if ('speechSynthesis' in window) {
            return new Promise((resolve) => {
                const utterance = new SpeechSynthesisUtterance(adMessage);
                utterance.lang = 'vi-VN';
                utterance.onend = () => {
                    setAdLockState(false);
                    resolve();
                };
                utterance.onerror = () => {
                    setAdLockState(false);
                    resolve();
                };
                window.speechSynthesis.cancel();
                window.speechSynthesis.speak(utterance);
            });
        }

        return new Promise((resolve) => {
            window.setTimeout(() => {
                setAdLockState(false);
                resolve();
            }, 3000);
        });
    }

    function stopPreviewPlayback() {
        stopPlayback(true);
        showNotice(`Khách chỉ nghe preview ${previewSeconds} giây. Đăng ký Free để nghe trọn bài.`, 7000);
        playAdInterlude('guest_preview');
    }

    function startPreviewGuard() {
        clearPreviewGuard();

        if (!capabilities.previewOnly) return;

        previewTimerId = window.setInterval(() => {
            if (audio.currentTime >= previewSeconds) {
                stopPreviewPlayback();
            }
        }, 300);
    }

    function syncCurrentMarkers() {
        document.querySelectorAll('.js-play-song').forEach((element) => {
            element.classList.toggle('is-current', !!currentSong && element.dataset.songId === currentSong.id);
        });
    }

    function updateNowPlaying(song) {
        if (trackTitle) trackTitle.textContent = song.title || 'Bài hát';
        if (trackArtist) trackArtist.textContent = song.artist || 'Nghệ sĩ';
        if (trackThumb && song.cover) trackThumb.src = song.cover;
        currentSong = song;
        syncFavoriteIcon();
        syncCurrentMarkers();
    }

    function songFromTrigger(trigger) {
        return {
            id: trigger.dataset.songId || '',
            title: trigger.dataset.songTitle || 'Bài hát',
            artist: trigger.dataset.songArtist || 'Nghệ sĩ',
            cover: trigger.dataset.songCover || '',
            streamUrl: trigger.dataset.streamUrl || '',
            premium: trigger.dataset.songPremium === '1',
            favorited: trigger.dataset.songFavorited === '1',
        };
    }

    async function toggleCurrentSongFavorite() {
        if (!currentSong?.id) {
            showNotice('Hãy chọn bài hát trước khi bấm yêu thích.', 3000);
            return;
        }

        if (!isAuthenticated) {
            showNotice('Bạn cần đăng nhập để dùng chức năng yêu thích.', 3500);
            window.setTimeout(() => {
                window.location.href = loginUrl || registerUrl;
            }, 900);
            return;
        }

        if (!favoriteToggleTemplate) {
            showNotice('Không tìm thấy endpoint yêu thích.', 3000);
            return;
        }

        const url = favoriteToggleTemplate.replace('__SONG_ID__', String(currentSong.id));
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                showNotice('Không thể cập nhật yêu thích lúc này.', 3000);
                return;
            }

            const data = await response.json();
            currentSong.favorited = !!data.favorited;
            syncFavoriteIcon();
            showNotice(data.message || (currentSong.favorited ? 'Đã thêm yêu thích.' : 'Đã bỏ yêu thích.'), 2500);

            document.querySelectorAll(`.js-play-song[data-song-id="${currentSong.id}"]`).forEach((button) => {
                button.dataset.songFavorited = currentSong.favorited ? '1' : '0';
            });
        } catch {
            showNotice('Không thể cập nhật yêu thích lúc này.', 3000);
        }
    }

    function ensureQueued(song) {
        const existingIndex = playbackQueue.findIndex((item) => item.id === song.id);

        if (existingIndex >= 0) {
            queueIndex = existingIndex;
            return;
        }

        playbackQueue.push(song);
        queueIndex = playbackQueue.length - 1;
    }

    function activateSong(song, autoPlay = true) {
        if (isPlaybackLockedByAd()) return;
        if (!song.streamUrl) return;

        updateNowPlaying(song);
        ensureQueued(song);

        if (audio.src !== song.streamUrl) {
            audio.src = song.streamUrl;
            audio.load();
        }

        if (autoPlay) {
            audio.play().then(() => {
                startPreviewGuard();
            }).catch(() => {
                showNotice('Trình duyệt đã chặn autoplay. Hãy bấm Play lần nữa.', 4500);
            });
        }

        persistState();
    }

    function canInteractWithPremiumSong(song) {
        if (!song.premium) return true;
        if (capabilities.canPlayPremium) return true;

        if (listenerRole === 'guest') {
            showNotice('Bài hát Premium yêu cầu đăng nhập. Hãy tạo tài khoản hoặc đăng nhập.', 7000);
            return false;
        }

        showNotice('Bài hát Premium yêu cầu nâng cấp gói Premium để nghe.', 7000);
        return false;
    }

    function playNext(fromEnded = false) {
        if (isPlaybackLockedByAd()) return;
        if (!capabilities.canSkip && !fromEnded) {
            showNotice('Tài khoản hiện tại không có quyền chuyển bài.', 4500);
            return;
        }

        if (playbackQueue.length === 0) {
            showNotice('Chưa có bài nào trong hàng chờ.', 3500);
            return;
        }

        if (shuffleEnabled && playbackQueue.length > 1) {
            let nextIndex = queueIndex;
            while (nextIndex === queueIndex) {
                nextIndex = Math.floor(Math.random() * playbackQueue.length);
            }
            queueIndex = nextIndex;
            activateSong(playbackQueue[queueIndex]);
            return;
        }

        if (queueIndex < playbackQueue.length - 1) {
            queueIndex += 1;
            activateSong(playbackQueue[queueIndex]);
            return;
        }

        if (repeatMode === 'all' && playbackQueue.length > 0) {
            queueIndex = 0;
            activateSong(playbackQueue[queueIndex]);
            return;
        }

        if (fromEnded) {
            showNotice('Đã phát xong bài hiện tại.', 2500);
        }
    }

    function playPrevious() {
        if (isPlaybackLockedByAd()) return;
        if (!capabilities.canSkip) {
            showNotice('Tài khoản hiện tại không có quyền chuyển bài.', 4500);
            return;
        }

        if (playbackQueue.length === 0 || queueIndex <= 0) {
            showNotice('Không có bài trước đó trong hàng chờ.', 3500);
            return;
        }

        queueIndex -= 1;
        activateSong(playbackQueue[queueIndex]);
    }

    function cycleRepeatMode() {
        if (!capabilities.canPlaybackModes) {
            showNotice('Chế độ lặp chỉ dành cho Premium.', 4500);
            return;
        }

        repeatMode = repeatMode === 'off' ? 'all' : repeatMode === 'all' ? 'one' : 'off';
        repeatBtn?.querySelector('i')?.classList.toggle('text-info', repeatMode !== 'off');
        showNotice(
            repeatMode === 'off'
                ? 'Đã tắt chế độ lặp.'
                : repeatMode === 'all'
                    ? 'Đang lặp toàn bộ danh sách.'
                    : 'Đang lặp bài hiện tại.',
            2500
        );
        persistState();
    }

    function toggleShuffle() {
        if (!capabilities.canPlaybackModes) {
            showNotice('Chế độ phát ngẫu nhiên chỉ dành cho Premium.', 4500);
            return;
        }

        shuffleEnabled = !shuffleEnabled;
        shuffleBtn?.classList.toggle('active', shuffleEnabled);
        showNotice(shuffleEnabled ? 'Đã bật phát ngẫu nhiên.' : 'Đã tắt phát ngẫu nhiên.', 2500);
        persistState();
    }

    function restoreState() {
        const raw = window.localStorage.getItem(persistentStateKey);
        if (!raw) return;

        try {
            const data = JSON.parse(raw);
            if (!data?.currentSong?.streamUrl) return;

            playbackQueue = Array.isArray(data.queue) ? data.queue : [];
            queueIndex = Number.isInteger(data.queueIndex) ? data.queueIndex : -1;
            repeatMode = data.repeatMode || 'off';
            shuffleEnabled = !!data.shuffleEnabled;
            audio.volume = typeof data.volume === 'number' ? data.volume : audio.volume;
            if (volumeInput) {
                volumeInput.value = String(Math.round(audio.volume * 100));
            }

            updateNowPlaying(data.currentSong);
            if (audio.src !== data.currentSong.streamUrl) {
                audio.src = data.currentSong.streamUrl;
                audio.load();
            }

            audio.addEventListener('loadedmetadata', function restoreTimeOnce() {
                audio.currentTime = Math.max(0, Number(data.currentTime || 0));
                audio.removeEventListener('loadedmetadata', restoreTimeOnce);
                updateProgress();
            });

            if (!data.paused) {
                audio.play().then(() => {
                    startPreviewGuard();
                }).catch(() => undefined);
            }
        } catch {
            window.localStorage.removeItem(persistentStateKey);
        }
    }

    function showQueueSummary() {
        if (isPlaybackLockedByAd()) return;
        if (!capabilities.canQueue) {
            showNotice('Tính năng hàng chờ cần đăng nhập để sử dụng.', 4500);
            return;
        }

        if (playbackQueue.length === 0) {
            showNotice('Hàng chờ đang trống.', 3000);
            return;
        }

        const previewList = playbackQueue.slice(0, 3).map((song) => song.title).join(' • ');
        const suffix = playbackQueue.length > 3 ? ` và ${playbackQueue.length - 3} bài khác` : '';
        showNotice(`Hàng chờ: ${previewList}${suffix}.`, 5000);
    }

    function applyCapabilitiesToUi() {
        if (adPlaying) {
            return;
        }

        updateRoleBadge();
        setButtonEnabled(prevBtn, capabilities.canSkip, 'Đăng nhập để dùng nút Previous');
        setButtonEnabled(nextBtn, capabilities.canSkip, 'Đăng nhập để dùng nút Next');
        setButtonEnabled(queueBtn, capabilities.canQueue, 'Đăng nhập để dùng hàng chờ');
        setButtonEnabled(shuffleBtn, capabilities.canPlaybackModes, 'Premium mới có phát ngẫu nhiên');
        setButtonEnabled(repeatBtn, capabilities.canPlaybackModes, 'Premium mới có chế độ lặp');

        if (volumeInput) {
            volumeInput.disabled = !capabilities.canChangeVolume;
            volumeInput.style.opacity = capabilities.canChangeVolume ? '1' : '.45';
        }

        if (progressWrap) {
            progressWrap.style.opacity = capabilities.canSeek ? '1' : '.55';
            progressWrap.style.cursor = capabilities.canSeek ? 'pointer' : 'not-allowed';
        }
    }

    playBtn.addEventListener('click', () => {
        if (isPlaybackLockedByAd()) return;
        if (!audio.src) {
            showNotice('Hãy chọn một bài hát để bắt đầu phát.', 3000);
            return;
        }

        if (audio.paused) {
            audio.play().then(() => {
                startPreviewGuard();
            }).catch(() => {
                showNotice('Không thể tiếp tục phát bài hát này.', 4500);
            });
            return;
        }

        audio.pause();
    });

    stopBtn?.addEventListener('click', () => {
        stopPlayback(true);
    });

    prevBtn?.addEventListener('click', playPrevious);
    nextBtn?.addEventListener('click', () => playNext(false));
    shuffleBtn?.addEventListener('click', toggleShuffle);
    repeatBtn?.addEventListener('click', cycleRepeatMode);
    queueBtn?.addEventListener('click', showQueueSummary);

    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', () => {
            if (isPlaybackLockedByAd()) return;
            toggleCurrentSongFavorite();
        });
    }

    if (progressWrap) {
        progressWrap.addEventListener('click', (event) => {
            if (isPlaybackLockedByAd()) return;
            if (!capabilities.canSeek) {
                showNotice('Khách chỉ có thể Play/Pause/Stop. Đăng ký Free để tua bài hát.', 5500);
                return;
            }

            if (!Number.isFinite(audio.duration) || audio.duration <= 0) return;

            const rect = progressWrap.getBoundingClientRect();
            const ratio = Math.min(1, Math.max(0, (event.clientX - rect.left) / rect.width));
            audio.currentTime = ratio * audio.duration;
        });
    }

    if (volumeInput) {
        audio.volume = Number(volumeInput.value) / 100;
        volumeInput.addEventListener('input', () => {
            if (isPlaybackLockedByAd()) {
                volumeInput.value = String(Math.round(audio.volume * 100));
                return;
            }
            if (!capabilities.canChangeVolume) {
                volumeInput.value = String(Math.round(audio.volume * 100));
                showNotice('Khách không thể điều chỉnh âm lượng từ player.', 4500);
                return;
            }
            audio.volume = Number(volumeInput.value) / 100;
            persistState();
        });
    }

    audio.addEventListener('loadedmetadata', updateProgress);
    audio.addEventListener('timeupdate', updateProgress);
    audio.addEventListener('play', updatePlayIcon);
    audio.addEventListener('pause', updatePlayIcon);
    audio.addEventListener('error', () => {
        showNotice('Không thể phát bài hát này với quyền hiện tại.', 6000);
        stopPlayback(false);
    });
    audio.addEventListener('ended', async () => {
        clearPreviewGuard();
        updatePlayIcon();

        if (capabilities.adAfterTrack) {
            await playAdInterlude('track_end');
        }

        if (repeatMode === 'one' && capabilities.canPlaybackModes && currentSong) {
            activateSong(currentSong);
            return;
        }

        if (capabilities.canPlaybackModes) {
            playNext(true);
            return;
        }

        stopPlayback(true);
    });

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('.js-play-song');
        if (!trigger) return;

        event.preventDefault();

        const song = songFromTrigger(trigger);
        if (!song.streamUrl) return;

        if (isPlaybackLockedByAd()) {
            return;
        }

        if (!canInteractWithPremiumSong(song)) {
            window.setTimeout(() => {
                window.location.href = listenerRole === 'guest' ? registerUrl : upgradeUrl;
            }, 1200);
            return;
        }

        activateSong(song);
    });

    window.addEventListener('beforeunload', persistState);
    window.addEventListener('pagehide', persistState);

    document.addEventListener('visibilitychange', () => {
        // Non-premium users are not allowed to keep playback in other windows/tabs.
        if (capabilities.canBackground) {
            return;
        }

        if (document.hidden) {
            if (!audio.paused) {
                pausedByVisibility = true;
                audio.pause();
                showNotice('Chế độ nền chỉ dành cho Premium. Đã tạm dừng phát khi rời cửa sổ.', 4000);
            }
            return;
        }

        if (!document.hidden && pausedByVisibility && currentSong && !isPlaybackLockedByAd()) {
            pausedByVisibility = false;
            audio.play().then(() => {
                startPreviewGuard();
            }).catch(() => {
                showNotice('Nhấn Play để tiếp tục nghe bài hát.', 3000);
            });
        }
    });

    applyCapabilitiesToUi();
    setAdLockState(false);
    restoreState();
    updatePlayIcon();
    syncFavoriteIcon();
    updateProgress();
})();
