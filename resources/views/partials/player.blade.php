<footer class="app-player" role="contentinfo">
    <div class="player-grid">

        <div class="player-track">
            <img class="track-thumb"
                 src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='56' height='56'%3E%3Crect width='56' height='56' fill='%231e293b'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='24' fill='%23e11d48'%3E%E2%99%AA%3C/text%3E%3C/svg%3E"
                 alt="Current Song">
            <div class="track-info">
                <div class="track-title">Cheat On Me</div>
                <div class="track-artist">Dave</div>
            </div>
            <button class="btn mm-icon-btn mm-icon-btn-sm btn-fav-track" title="Like">
                <i class="fa-regular fa-heart"></i>
            </button>
        </div>

        <div class="player-controls">
            <div class="control-row">
                <button class="btn mm-icon-btn mm-icon-btn-sm" title="Shuffle"><i class="fa-solid fa-shuffle"></i></button>
                <button class="btn mm-icon-btn mm-icon-btn-sm" title="Prev"><i class="fa-solid fa-backward-step"></i></button>

                <button class="btn mm-play-btn" id="playerPlayBtn" title="Play/Pause">
                    <i class="fa-solid fa-play"></i>
                </button>

                <button class="btn mm-icon-btn mm-icon-btn-sm" title="Next"><i class="fa-solid fa-forward-step"></i></button>
                <button class="btn mm-icon-btn mm-icon-btn-sm" title="Repeat"><i class="fa-solid fa-repeat"></i></button>
            </div>

            <div class="progress-row">
                <span class="time small">0:32</span>
                <div class="mm-progress" data-mm-progress>
                    <div class="mm-progress-fill" style="width:25%"></div>
                    <div class="mm-progress-thumb" style="left:25%"></div>
                </div>
                <span class="time small">3:02</span>
            </div>
        </div>

        <div class="player-options">
            <button class="btn mm-icon-btn mm-icon-btn-sm d-none d-md-inline-flex" title="Queue"><i class="fa-solid fa-list"></i></button>
            <button class="btn mm-icon-btn mm-icon-btn-sm d-none d-md-inline-flex" title="Device"><i class="fa-solid fa-laptop"></i></button>

            <div class="volume d-none d-lg-flex">
                <button class="btn mm-icon-btn mm-icon-btn-sm" title="Volume"><i class="fa-solid fa-volume-high"></i></button>
                <input type="range" class="form-range mm-range" min="0" max="100" value="70">
            </div>

            <button class="btn mm-icon-btn mm-icon-btn-sm" title="Fullscreen"><i class="fa-solid fa-expand"></i></button>
        </div>

    </div>
</footer>
