@php
    $listenerRole = 'guest';

    if (auth()->check()) {
        $listenerRole = auth()->user()->isPremium() || auth()->user()->isAdmin()
            ? 'premium'
            : 'free';
    }
@endphp

<footer
    class="app-player"
    role="contentinfo"
    data-listener-role="{{ $listenerRole }}"
    data-is-authenticated="{{ auth()->check() ? '1' : '0' }}"
    data-preview-seconds="{{ \App\Http\Controllers\StreamController::GUEST_PREVIEW_SECONDS }}"
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
                 alt="Current Song"
                 onerror="this.src='{{ \Illuminate\Support\Facades\Storage::url('disk.png') }}'">
            <div class="track-info">
                <form id="playerTrackTitleForm" action="{{ route('songs.redirect') }}" method="GET" hx-boost="true" class="m-0 p-0" style="display:inline;">
                    <input type="hidden" name="song_id" id="playerTrackTitleInput" value="">
                    <button type="submit" class="track-title text-decoration-none border-0 bg-transparent p-0 text-start" id="playerTrackTitle" style="color: inherit; transition: color 0.2s; outline: none; display: inline-block;" onmouseover="this.style.color='var(--primary-blue-light)'" onmouseout="this.style.color='inherit'">
                        Chưa phát bài nào
                    </button>
                </form>
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

        <div class="player-options d-flex align-items-center gap-1">
            <div class="dropdown d-inline-block dropup mb-0">
                <button class="btn mm-icon-btn mm-icon-btn-sm text-muted fw-bold" style="font-size: 0.8rem; height: 36px; padding: 0 8px;" data-bs-toggle="dropdown" id="playerSpeedBtn">1x</button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg" style="background-color: var(--black-soft); border: 1px solid var(--black-hover); min-width: 80px;">
                    <li><button type="button" class="dropdown-item text-white text-center speed-option" data-speed="0.5">0.5x</button></li>
                    <li><button type="button" class="dropdown-item text-white text-center speed-option" data-speed="1.0">1x</button></li>
                    <li><button type="button" class="dropdown-item text-white text-center speed-option" data-speed="1.25">1.25x</button></li>
                    <li><button type="button" class="dropdown-item text-white text-center speed-option" data-speed="1.5">1.5x</button></li>
                    <li><button type="button" class="dropdown-item text-white text-center speed-option" data-speed="2.0">2x</button></li>
                </ul>
            </div>

            <button class="btn mm-icon-btn mm-icon-btn-sm" type="button" id="playerQueueBtn" title="Queue" data-bs-toggle="modal" data-bs-target="#queueModal"><i class="fa-solid fa-list"></i></button>
            <button class="btn mm-icon-btn mm-icon-btn-sm text-white" type="button" id="playerLyricBtn" title="Lyrics" data-bs-toggle="modal" data-bs-target="#lyricModal"><i class="fa-solid fa-microphone-lines"></i></button>
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

{{-- QUEUE MODAL UI --}}
<div class="modal fade" id="queueModal" tabindex="-1" aria-labelledby="queueModalLabel" aria-hidden="true" style="z-index: 1060;">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content" style="background-color: var(--black-soft); border: 1px solid var(--black-hover); border-radius: 12px; max-height: 85vh;">
      <div class="modal-header border-bottom" style="border-color: rgba(255,255,255,0.05) !important;">
        <h5 class="modal-title text-white fw-bold fs-5" id="queueModalLabel">
            <i class="fa-solid fa-list-ul me-2"></i>Hàng đợi phát (<span id="queueCount">0</span>)
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-2" id="queueList">
        <!-- Render track items here -->
      </div>
    </div>
  </div>
</div>

{{-- LYRICS MODAL UI --}}
<div class="modal fade lyrics-modal" id="lyricModal" tabindex="-1" aria-labelledby="lyricModalLabel" aria-hidden="true" style="z-index: 1060;">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex align-items-center gap-3">
            <img src="" id="lyricThumb" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;" onerror="this.src='/storage/disk.png'">
            <div>
                <h5 class="modal-title text-white fw-bold fs-5 mb-0" id="lyricTitle">Lời bài hát</h5>
                <div class="text-muted small" id="lyricArtist">Nghệ sĩ</div>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0 position-relative">
          <div class="px-3 pt-3 pb-2 border-bottom" style="border-color: rgba(255,255,255,0.06) !important;">
              <div class="d-flex flex-wrap gap-2 mb-2" id="lyricTypeTabs"></div>
              <div class="d-flex flex-wrap gap-2" id="lyricVersionTabs"></div>
          </div>
          <div class="lyric-container" id="lyricScrollContainer">
              <div class="lyric-wrapper" id="lyricContent">
                  <!-- Lyrics will be injected here via JS -->
                  <div class="text-muted text-center mt-5" id="lyricLoadingState"><i class="fa-solid fa-spinner fa-spin me-2"></i>Đang tải lời bài hát...</div>
              </div>
          </div>
      </div>
    </div>
  </div>
</div>

<style>
.queue-item-card {
    background-color: transparent; border-radius: 8px; transition: background 0.2s;
}
.queue-item-card:hover {
    background-color: var(--black-hover);
}
.queue-item-card.is-playing {
    background-color: rgba(11, 94, 215, 0.1); border-left: 3px solid var(--primary-blue);
}
.queue-item-card.is-dragging {
    opacity: 0.5; background-color: rgba(255, 255, 255, 0.1); border: 1px dashed var(--text-muted);
}
.drag-handle {
    cursor: grab; color: var(--text-muted); opacity: 0.5; transition: opacity 0.2s;
}
.queue-item-card:hover .drag-handle {
    opacity: 1;
}
.queue-delete-btn {
    background: transparent; border: none; color: var(--text-muted); padding: 4px; transition: color 0.2s;
}
.queue-delete-btn:hover {
    color: var(--red-light);
}

.lyric-selector-chip {
    border: 1px solid rgba(255,255,255,0.2);
    background: rgba(255,255,255,0.03);
    color: #cbd5e1;
    border-radius: 999px;
    padding: 0.18rem 0.65rem;
    font-size: 0.76rem;
}

.lyric-selector-chip.active {
    border-color: rgba(96,165,250,0.65);
    background: rgba(96,165,250,0.2);
    color: #dbeafe;
}
</style>
