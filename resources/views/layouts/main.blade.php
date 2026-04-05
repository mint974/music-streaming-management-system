<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Blue Wave Music')</title>

    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://unpkg.com/htmx.org@2.0.4" crossorigin="anonymous"></script>

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <style>
        .global-toast-container {
            z-index: 9999;
        }
        .custom-toast {
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            background: rgba(30,30,35,0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .toast-success { border-left: 4px solid #10b981; }
        .toast-warning { border-left: 4px solid #f59e0b; }
        .toast-info { border-left: 4px solid #3b82f6; }
        .toast-danger { border-left: 4px solid #ef4444; }
    </style>
    @stack('styles')
</head>

<body class="app-layout" hx-boost="true">
    {{-- Sidebar desktop --}}
    @include('partials.sidebar')

    {{-- Sidebar mobile offcanvas --}}
    <div class="offcanvas offcanvas-start app-offcanvas" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-header">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-music text-danger"></i>
                <strong id="sidebarOffcanvasLabel" class="text-white">Blue Wave Music</strong>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            {{-- reuse same sidebar content --}}
            @include('partials.sidebar', ['isOffcanvas' => true])
        </div>
    </div>

    <div class="content-area">
        @include('partials.header')

        <main class="app-content">
            @yield('content')
        </main>
    </div>

    <div id="persistent-player" hx-preserve="true">
        @include('partials.player')
    </div>

    {{-- Global Add to Playlist Modal --}}
    @auth
    <div class="modal fade" id="globalAddToPlaylistModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0" style="background-color: var(--black-soft); border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                <div class="modal-header border-bottom border-dark px-3 py-2">
                    <h6 class="modal-title text-white fw-bold mb-0">Thêm vào Playlist</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.8rem;"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="list-group list-group-flush global-playlist-list" style="max-height: 300px; overflow-y: auto;">
                        @forelse(auth()->user()->playlists as $pl)
                            <button type="button" class="list-group-item list-group-item-action bg-transparent text-white border-dark px-3 py-2 text-truncate" onclick="confirmAddToPlaylist({{ $pl->id }}, this)">
                                <i class="fa-solid fa-music me-2 text-muted"></i>{{ $pl->name }}
                            </button>
                        @empty
                            <div class="text-center p-3 text-muted small">Chưa có playlist nào.</div>
                        @endforelse
                    </div>
                </div>
                <div class="modal-footer border-top border-dark px-3 py-2">
                    <a href="{{ route('listener.playlists.index') }}" class="btn btn-sm btn-outline-light w-100 rounded-pill"><i class="fa-solid fa-plus me-1"></i>Tạo Playlist Mới</a>
                </div>
            </div>
        </div>
    </div>
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <script>
    // Global Toast Function
    function showToast(message, type = 'success') {
        let toastContainer = document.getElementById('global-toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'global-toast-container';
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-4 global-toast-container';
            document.body.appendChild(toastContainer);
        }
        
        const toastId = 'toast' + Date.now();
        const iconClass = type === 'success' ? 'fa-circle-check text-success' : 
                          type === 'info' ? 'fa-circle-info text-info' : 
                          type === 'warning' ? 'fa-triangle-exclamation text-warning' : 
                          'fa-circle-exclamation text-danger';
        const typeClass = type === 'success' ? 'toast-success' :
                          type === 'info' ? 'toast-info' :
                          type === 'warning' ? 'toast-warning' :
                          'toast-danger';
        
        toastContainer.insertAdjacentHTML('beforeend', `
            <div id="${toastId}" class="toast custom-toast ${typeClass} align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex p-1">
                    <div class="toast-body fw-bold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                        <i class="fa-solid ${iconClass} fs-5"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `);
        
        if (typeof bootstrap !== 'undefined') {
            const toastEl = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
            toast.show();
            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        } else {
            const toastEl = document.getElementById(toastId);
            toastEl.classList.add('show');
            setTimeout(() => {
                toastEl.classList.remove('show');
                setTimeout(() => toastEl.remove(), 500);
            }, 3500);
        }
    }

    // Global Modal Trigger Variables
    window.currentSongIdForPlaylist = window.currentSongIdForPlaylist || null;
    
    function openAddToPlaylistModal(songId) {
        window.currentSongIdForPlaylist = songId;
        if (typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(document.getElementById('globalAddToPlaylistModal'));
            modal.show();
        }
    }

    async function confirmAddToPlaylist(playlistId, btnElement) {
        if (!window.currentSongIdForPlaylist) return;
        
        const originalHtml = btnElement.innerHTML;
        btnElement.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2 text-muted"></i>Đang thêm...';
        btnElement.disabled = true;

        await addSongToPlaylist(playlistId, window.currentSongIdForPlaylist);
        
        btnElement.innerHTML = originalHtml;
        btnElement.disabled = false;
        
        if (typeof bootstrap !== 'undefined') {
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('globalAddToPlaylistModal'));
            if (modalInstance) modalInstance.hide();
        }
    }

    // Underlying function to add song
    async function addSongToPlaylist(playlistId, songId) {
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const response = await fetch(`/listener/playlists/${playlistId}/songs`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ song_id: songId })
            });
            const data = await response.json();
            if (response.ok && data.success) {
                showToast(data.message || 'Đã thêm thành công', 'success');
            } else {
                showToast(data.message || 'Lỗi liên kết dữ liệu', 'danger');
            }
        } catch (e) {
            showToast('Lỗi kết nối máy chủ', 'danger');
        }
    }

    window.BWMOffline = {
        cacheName: 'bwm-offline-tunes-v1',

        isSupported() {
            return 'caches' in window;
        },

        normalizeUrls(urls) {
            return [...new Set((urls || []).filter((url) => typeof url === 'string' && url.trim() !== ''))];
        },

        async syncUrls(urls, onProgress) {
            const normalized = this.normalizeUrls(urls);
            if (!this.isSupported()) {
                throw new Error('Cache API is not supported');
            }

            const cache = await caches.open(this.cacheName);
            let done = 0;
            const total = normalized.length;

            for (const url of normalized) {
                const existing = await cache.match(url);
                if (!existing) {
                    await cache.add(url);
                }
                done += 1;
                if (typeof onProgress === 'function') {
                    onProgress({ done, total, url });
                }
            }

            return { done, total };
        },

        async getUsageMB() {
            if (!navigator.storage || !navigator.storage.estimate) {
                return null;
            }
            const estimate = await navigator.storage.estimate();
            return (estimate.usage / (1024 * 1024)).toFixed(2);
        },

        async renderUsageStatus(targetId, labels = {}) {
            const el = document.getElementById(targetId);
            if (!el) return;

            const usage = await this.getUsageMB();
            if (usage === null) {
                el.textContent = 'Không lấy được thông tin dung lượng bộ nhớ cục bộ.';
                return;
            }

            const usageLabel = labels.usageLabel || 'Dung lượng cache đang dùng';
            const clearLabel = labels.clearLabel || 'Xóa dữ liệu Offline';
            el.innerHTML = `${usageLabel}: <b>${usage} MB</b><br><a href="#" onclick="clearCacheApp(); return false;" class="text-danger">${clearLabel}</a>`;
        },

        async clearCache() {
            if (!this.isSupported()) {
                return false;
            }
            return caches.delete(this.cacheName);
        },
    };
    </script>
    @stack('scripts')
</body>
</html>
