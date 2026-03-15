@php
    $listenerRole = 'guest';

    if (auth()->check()) {
        $listenerRole = auth()->user()->isPremium() || auth()->user()->isArtist() || auth()->user()->isAdmin()
            ? 'premium'
            : 'free';
    }
@endphp

<footer
    class="app-player"
    role="contentinfo"
    data-listener-role="{{ $listenerRole }}"
    data-is-authenticated="{{ auth()->check() ? '1' : '0' }}"
    data-preview-seconds="45"
    data-login-url="{{ route('login') }}"
    data-register-url="{{ route('register') }}"
    data-upgrade-url="{{ auth()->check() ? route('subscription.index') : route('register') }}"
    data-favorite-toggle-template="{{ route('listener.song.toggleFavorite', ['songId' => '__SONG_ID__']) }}"
    data-ad-audio-url="{{ asset('storage/premium.WAV') }}">
    <div class="player-grid">

        <div class="player-track">
            <img class="track-thumb"
                 id="playerTrackThumb"
                 src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='56' height='56'%3E%3Crect width='56' height='56' fill='%231e293b'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='24' fill='%23e11d48'%3E%E2%99%AA%3C/text%3E%3C/svg%3E"
                 alt="Current Song">
            <div class="track-info">
                <div class="track-title" id="playerTrackTitle">Chưa phát bài nào</div>
                <div class="track-artist" id="playerTrackArtist">Blue Wave Music</div>
                <div class="small text-muted mt-1" id="playerNotice" style="min-height:18px;opacity:0;transition:opacity .2s ease"></div>
            </div>
            <button class="btn mm-icon-btn mm-icon-btn-sm btn-fav-track" id="playerFavoriteBtn" title="Like">
                <i class="fa-regular fa-heart"></i>
            </button>
        </div>

        <div class="player-controls">
            <div class="control-row">
                <button class="btn mm-icon-btn mm-icon-btn-sm" id="playerShuffleBtn" title="Shuffle"><i class="fa-solid fa-shuffle"></i></button>
                <button class="btn mm-icon-btn mm-icon-btn-sm" id="playerPrevBtn" title="Prev"><i class="fa-solid fa-backward-step"></i></button>
                <button class="btn mm-icon-btn mm-icon-btn-sm" id="playerStopBtn" title="Stop"><i class="fa-solid fa-stop"></i></button>

                <button class="btn mm-play-btn" id="playerPlayBtn" title="Play/Pause">
                    <i class="fa-solid fa-play"></i>
                </button>

                <button class="btn mm-icon-btn mm-icon-btn-sm" id="playerNextBtn" title="Next"><i class="fa-solid fa-forward-step"></i></button>
                <button class="btn mm-icon-btn mm-icon-btn-sm" id="playerRepeatBtn" title="Repeat"><i class="fa-solid fa-repeat"></i></button>
            </div>

            <div class="progress-row">
                <span class="time small" id="playerCurrentTime">0:00</span>
                <div class="mm-progress" data-mm-progress id="playerProgress">
                    <div class="mm-progress-fill" id="playerProgressFill" style="width:0%"></div>
                    <div class="mm-progress-thumb" id="playerProgressThumb" style="left:0%"></div>
                </div>
                <span class="time small" id="playerDuration">0:00</span>
            </div>
        </div>

        <div class="player-options">
            <button class="btn mm-icon-btn mm-icon-btn-sm d-none d-md-inline-flex" id="playerQueueBtn" title="Queue"><i class="fa-solid fa-list"></i></button>
            <span class="badge rounded-pill text-bg-dark d-none d-md-inline-flex align-items-center px-3" id="playerRoleBadge">{{ strtoupper($listenerRole) }}</span>

            <div class="volume d-none d-lg-flex">
                <button class="btn mm-icon-btn mm-icon-btn-sm" title="Volume"><i class="fa-solid fa-volume-high"></i></button>
                <input type="range" class="form-range mm-range" id="playerVolume" min="0" max="100" value="70">
            </div>

            <button class="btn mm-icon-btn mm-icon-btn-sm" title="Fullscreen"><i class="fa-solid fa-expand"></i></button>
        </div>

    </div>

    <audio id="globalAudioPlayer" preload="metadata"></audio>
</footer>
