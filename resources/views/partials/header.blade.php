<header class="app-header">
    <div class="header-content">

        <div class="d-flex align-items-center gap-3">
            {{-- Mobile sidebar toggle --}}
            <button class="btn mm-icon-btn d-inline-flex d-lg-none"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#sidebarOffcanvas"
                    aria-controls="sidebarOffcanvas"
                    title="Menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <nav class="breadcrumb-nav">
                <a href="{{ url('/') }}" class="breadcrumb-link">Home</a>
                @if(isset($breadcrumbs))
                    @foreach($breadcrumbs as $crumb)
                        <i class="fa-solid fa-chevron-right breadcrumb-sep"></i>
                        @if($loop->last)
                            <span class="breadcrumb-current">{{ $crumb['label'] }}</span>
                        @else
                            <a href="{{ $crumb['url'] }}" class="breadcrumb-link">{{ $crumb['label'] }}</a>
                        @endif
                    @endforeach
                @else
                    <i class="fa-solid fa-chevron-right breadcrumb-sep"></i>
                    <span class="breadcrumb-current">Albums</span>
                @endif
            </nav>
        </div>

        <div class="header-actions">
            <form action="{{ route('search') }}" method="GET"
                  class="search-box"
                  id="globalSearchForm"
                  autocomplete="off"
                  role="search">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text"
                       class="search-input"
                       placeholder="Tìm bài hát, nghệ sĩ, album..."
                       name="q"
                       id="globalSearchInput"
                       value="{{ request('q') }}"
                       aria-label="Tìm kiếm"
                       autocomplete="off">
                <button type="button" class="search-clear" id="searchClear" aria-label="Clear search">
                    <i class="fa-solid fa-xmark"></i>
                </button>

                {{-- Autocomplete dropdown --}}
                <div class="search-dropdown" id="searchDropdown" role="listbox">
                    {{-- Filled by JS --}}
                </div>
            </form>
            
            <button class="btn btn-dark rounded-circle ms-2 d-none d-md-flex align-items-center justify-content-center flex-shrink-0" 
                    id="startRecording" 
                    style="width: 42px; height: 42px; background-color: var(--black-soft); border: 1px solid var(--black-hover);"
                    title="Tìm kiếm bằng giọng nói"
                    data-bs-toggle="modal" data-bs-target="#voiceSearchModal">
                <i class="fa-solid fa-microphone text-white"></i>
            </button>

            <div class="header-icons ms-2">
                @auth
                @php
                    $unreadCount   = auth()->user()->unreadNotifications()->count();
                    $recentNotifs  = auth()->user()->notifications()->latest()->take(5)->get();
                @endphp
                <div class="dropdown">
                    <button class="btn mm-icon-btn position-relative"
                            title="Thông báo"
                            data-bs-toggle="dropdown"
                            data-bs-auto-close="outside"
                            aria-expanded="false"
                            id="notificationBtn">
                        <i class="fa-solid fa-bell"></i>
                        @if($unreadCount > 0)
                            <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill"
                                  style="background:#ef4444;font-size:.6rem;min-width:16px;height:16px;display:flex;align-items:center;justify-content:center;padding:0 3px">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end mm-dropdown p-0"
                        style="width:340px;max-height:480px;overflow:hidden" aria-labelledby="notificationBtn">

                        {{-- Header --}}
                        <li class="px-3 py-2 d-flex align-items-center justify-content-between border-bottom border-secondary border-opacity-25">
                            <span class="fw-semibold text-white" style="font-size:.9rem">
                                <i class="fa-solid fa-bell me-1" style="color:#818cf8"></i>Thông báo
                            </span>
                            @if($unreadCount > 0)
                            <form method="POST" action="{{ route('notifications.markAllRead') }}">
                                @csrf
                                <button type="submit" class="btn btn-link p-0 text-muted" style="font-size:.75rem;text-decoration:none">
                                    <i class="fa-solid fa-check-double me-1"></i>Đọc tất cả
                                </button>
                            </form>
                            @endif
                        </li>

                        {{-- Recent notifications --}}
                        <div style="max-height:340px;overflow-y:auto">
                        @forelse($recentNotifs as $notif)
                        @php
                            $nd     = $notif->data;
                            $nRead  = $notif->read_at !== null;
                            $nColor = $nd['color'] ?? '#818cf8';
                            $nIcon  = $nd['icon']  ?? 'fa-bell';
                        @endphp
                        <li>
                            <a href="{{ route('notifications.read', $notif->id) }}"
                               class="dropdown-item py-2 px-3 {{ $nRead ? '' : 'notif-unread' }}"
                               style="{{ !$nRead ? 'background:rgba(99,102,241,.06)' : '' }}">
                                <div class="d-flex gap-2 align-items-start">
                                    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center mt-1"
                                         style="width:32px;height:32px;background:{{ $nColor }}20;border:1px solid {{ $nColor }}35">
                                        <i class="fa-solid {{ $nIcon }}" style="color:{{ $nColor }};font-size:.75rem"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex align-items-center justify-content-between gap-1">
                                            <span class="fw-semibold text-white text-truncate" style="font-size:.8rem">{{ $nd['title'] ?? 'Thông báo' }}</span>
                                            @if(!$nRead)
                                                <span style="width:7px;height:7px;background:#818cf8;border-radius:50%;flex-shrink:0;display:inline-block"></span>
                                            @endif
                                        </div>
                                        <div class="text-muted text-truncate" style="font-size:.75rem">{{ $nd['message'] ?? '' }}</div>
                                        <div class="text-muted" style="font-size:.7rem">{{ $notif->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        @empty
                        <li class="text-center text-muted py-4" style="font-size:.85rem">
                            <i class="fa-solid fa-bell-slash mb-2 d-block opacity-25"></i>
                            Chưa có thông báo nào
                        </li>
                        @endforelse
                        </div>

                        {{-- Footer --}}
                        <li class="border-top border-secondary border-opacity-25">
                            <a href="{{ route('notifications.index') }}"
                               class="dropdown-item text-center py-2"
                               style="color:#818cf8;font-size:.8rem">
                                Xem tất cả thông báo
                            </a>
                        </li>
                    </ul>
                </div>
                @endauth

                @auth
                    <div class="dropdown">
                        @php
                            $initial   = strtoupper(substr(auth()->user()->name, 0, 1));
                            $avatarSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%23e11d48'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='18' fill='%23ffffff' font-weight='bold'%3E{$initial}%3C/text%3E%3C/svg%3E";
                        @endphp
                        <button class="btn mm-user-btn {{ auth()->user()->isPremium() ? 'avatar-ring-premium' : '' }} dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img class="user-avatar" src="{{ auth()->user()->avatar ?? $avatarSvg }}" alt="{{ auth()->user()->name }}">
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end mm-dropdown">
                            <li class="px-3 py-2 d-flex align-items-center gap-2">
                                <img class="dropdown-avatar" src="{{ auth()->user()->avatar ?? $avatarSvg }}" alt="{{ auth()->user()->name }}">
                                <div class="min-w-0">
                                    <div class="fw-semibold text-white text-truncate">{{ auth()->user()->name }}</div>
                                    <div class="small text-muted text-truncate">{{ auth()->user()->email }}</div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fa-solid fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('listener.history') }}"><i class="fa-solid fa-clock-rotate-left me-2"></i>Lịch sử nghe</a></li>
                            <li><a class="dropdown-item" href="{{ route('listener.favorites') }}"><i class="fa-solid fa-heart me-2"></i>Bài hát yêu thích</a></li>
                            <li><a class="dropdown-item" href="{{ route('listener.albums') }}"><i class="fa-solid fa-compact-disc me-2"></i>Album đã lưu</a></li>
                            <li><a class="dropdown-item" href="{{ route('listener.index') }}"><i class="fa-solid fa-database me-2"></i>Thư viện listener</a></li>
                            @if(!auth()->user()->isAdmin())
                            <li><a class="dropdown-item" href="{{ route('subscription.index') }}">
                                @if(auth()->user()->isPremium())
                                    <i class="fa-solid fa-crown me-2" style="color:#fbbf24"></i>Gói Premium của tôi
                                @else
                                    <i class="fa-solid fa-star me-2" style="color:#818cf8"></i>Nâng cấp Premium
                                @endif
                            </a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fa-solid fa-right-from-bracket me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn mm-btn mm-btn-light">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Login
                    </a>
                @endauth
            </div>
        </div>

    </div>
</header>

<!-- Voice Search Modal (YouTube-like) -->
<div class="modal fade" id="voiceSearchModal" tabindex="-1" aria-hidden="true" style="z-index: 1060; backdrop-filter: blur(5px);">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0" style="background-color: var(--black-main); border-radius: 16px; height: 350px;">
      <div class="modal-body d-flex flex-column align-items-center justify-content-center position-relative">
        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-4" data-bs-dismiss="modal" aria-label="Close" id="stopRecordingBtn"></button>
        <div class="fs-4 fw-bold text-white mb-5 text-center px-3" id="transcriptText" style="min-height: 40px; word-wrap: break-word;">Đang nghe...</div>
        <button type="button" class="btn btn-danger rounded-circle d-flex align-items-center justify-content-center shadow-lg" style="width: 80px; height: 80px; font-size: 2.2rem; border: none; animation: pulseMic 1.5s infinite; background-color: #ef4444;" id="micListeningWave">
            <i class="fa-solid fa-microphone"></i>
        </button>
      </div>
    </div>
  </div>
</div>
<style>
@keyframes pulseMic {
    0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
    70% { box-shadow: 0 0 0 25px rgba(239, 68, 68, 0); }
    100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}
</style>

@push('scripts')
<script>
(function () {
    'use strict';

    const input     = document.getElementById('globalSearchInput');
    const dropdown  = document.getElementById('searchDropdown');
    const clearBtn  = document.getElementById('searchClear');
    const form      = document.getElementById('globalSearchForm');

    if (!input || !dropdown) return;

    const AUTOCOMPLETE_URL = '{{ route("search.autocomplete") }}';
    const SEARCH_URL       = '{{ route("search") }}';
    const CSRF             = document.querySelector('meta[name="csrf-token"]')?.content;

    // LocalStorage key untuk guest history
    const LS_KEY = 'bwm_search_history';

    // ── Guest localStorage history helpers ─────────────────────── //
    function lsGetHistory() {
        try { return JSON.parse(localStorage.getItem(LS_KEY) || '[]'); }
        catch { return []; }
    }
    function lsAddHistory(q) {
        let h = lsGetHistory().filter(x => x.toLowerCase() !== q.toLowerCase());
        h.unshift(q);
        h = h.slice(0, 20);
        localStorage.setItem(LS_KEY, JSON.stringify(h));
    }
    function lsRemoveHistory(q) {
        const h = lsGetHistory().filter(x => x.toLowerCase() !== q.toLowerCase());
        localStorage.setItem(LS_KEY, JSON.stringify(h));
    }

    // ── Debounce ────────────────────────────────────────────────── //
    let debounceTimer = null;
    let abortCtrl     = null;
    let focusedIdx    = -1;

    function debounce(fn, ms) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fn, ms);
    }

    // ── Render dropdown ─────────────────────────────────────────── //
    function openDropdown(html) {
        dropdown.innerHTML = html;
        dropdown.classList.add('is-open');
        focusedIdx = -1;
    }

    function closeDropdown() {
        dropdown.classList.remove('is-open');
        dropdown.innerHTML = '';
        focusedIdx = -1;
    }

    function renderItem(item) {
        return `
        <a class="sd-item" href="${item.url}" role="option" tabindex="-1" data-label="${escHtml(item.label)}">
            <img class="sd-avatar" src="${item.avatar}" alt="${escHtml(item.label)}" loading="lazy"
                 onerror="this.src=''">
            <div class="sd-text">
                <div class="sd-label">
                    ${escHtml(item.label)}
                    ${item.verified ? '<i class="fa-solid fa-circle-check ms-1" style="color:#60a5fa;font-size:.7rem"></i>' : ''}
                </div>
                <div class="sd-sub">${escHtml(item.sublabel)}</div>
            </div>
            <i class="fa-solid fa-arrow-right sd-arrow"></i>
        </a>`;
    }

    function renderHistoryItem(q) {
        const url = SEARCH_URL + '?q=' + encodeURIComponent(q);
        return `
        <a class="sd-item" href="${url}" role="option" tabindex="-1" data-label="${escHtml(q)}">
            <span class="sd-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
            <div class="sd-text">
                <div class="sd-label">${escHtml(q)}</div>
            </div>
            <button class="btn p-0 sd-arrow remove-hist-btn"
                    title="Xóa" style="background:none;border:none;cursor:pointer;color:#64748b"
                    data-query="${escHtml(q)}"
                    onclick="event.preventDefault();event.stopPropagation()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </a>`;
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Fetch autocomplete ───────────────────────────────────────── //
    async function fetchSuggestions(q) {
        if (abortCtrl) abortCtrl.abort();
        abortCtrl = new AbortController();
        try {
            const res = await fetch(`${AUTOCOMPLETE_URL}?q=${encodeURIComponent(q)}`, {
                signal: abortCtrl.signal,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) return;
            const data = await res.json();
            renderSuggestions(q, data);
        } catch (e) {
            if (e.name !== 'AbortError') console.error(e);
        }
    }

    function renderSuggestions(q, data) {
        let html = '';

        // DB history results (filtered by prefix)
        const dbHistory = data.history || [];
        // Guest local history
        const lsHistory = lsGetHistory().filter(h => h.toLowerCase().startsWith(q.toLowerCase())).slice(0, 4);
        // Merge & deduplicate
        const allHistory = [...new Set([...dbHistory, ...lsHistory])].slice(0, 5);

        if (allHistory.length > 0) {
            html += `<div class="sd-section-label">Lịch sử</div>`;
            allHistory.forEach(hq => { html += renderHistoryItem(hq); });
        }

        const results = data.results || [];
        if (results.length > 0) {
            html += `<div class="sd-section-label">Nghệ sĩ</div>`;
            results.forEach(item => { html += renderItem(item); });
        }

        if (html === '') {
            html = `<div class="sd-empty"><i class="fa-solid fa-magnifying-glass me-1 opacity-50"></i>Không tìm thấy kết quả cho "<strong>${escHtml(q)}</strong>"</div>`;
        }

        html += `<div class="sd-footer">Nhấn <kbd>Enter</kbd> để xem tất cả kết quả · <a href="${SEARCH_URL}?q=${encodeURIComponent(q)}">Xem thêm</a></div>`;
        openDropdown(html);
        attachDropdownHandlers();
    }

    function showEmptyDropdown() {
        // Hiển thị lịch sử khi input trống và đang focus
        const lsHistory = lsGetHistory().slice(0, 8);
        // Lịch sử từ server đã được nhúng vào data attribute của header
        const serverHistory = window.__bwmSearchHistory || [];
        const allHistory = [...new Set([...serverHistory, ...lsHistory])].slice(0, 8);

        if (allHistory.length === 0) {
            closeDropdown();
            return;
        }

        let html = `<div class="sd-section-label">Lịch sử gần đây</div>`;
        allHistory.forEach(hq => { html += renderHistoryItem(hq); });

        const hasAuth = document.querySelector('meta[name="user-authenticated"]')?.content === '1';
        if (hasAuth) {
            html += `<div class="sd-footer"><a href="#" id="sdClearAllLink">Xóa tất cả lịch sử</a></div>`;
        } else {
            html += `<div class="sd-footer">Đăng nhập để đồng bộ lịch sử trên tất cả thiết bị</div>`;
        }

        openDropdown(html);
        attachDropdownHandlers();
    }

    function attachDropdownHandlers() {
        // Remove history item buttons
        dropdown.querySelectorAll('.remove-hist-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();
                const q = btn.dataset.query;
                lsRemoveHistory(q);

                // Also remove from DB if logged in
                try {
                    await fetch('{{ route("search.history.remove") }}', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ query: q }),
                    });
                } catch {}

                btn.closest('.sd-item')?.remove();
            });
        });

        // Clear all link in dropdown footer
        const clearAllLink = document.getElementById('sdClearAllLink');
        if (clearAllLink) {
            clearAllLink.addEventListener('click', async (e) => {
                e.preventDefault();
                localStorage.removeItem(LS_KEY);
                try {
                    await fetch('{{ route("search.history.clear") }}', {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    });
                } catch {}
                closeDropdown();
            });
        }
    }

    // ── Keyboard navigation ─────────────────────────────────────── //
    function getItems() {
        return Array.from(dropdown.querySelectorAll('.sd-item'));
    }

    function moveFocus(dir) {
        const items = getItems();
        if (items.length === 0) return;
        items.forEach(i => i.classList.remove('is-focused'));
        focusedIdx = (focusedIdx + dir + items.length) % items.length;
        items[focusedIdx].classList.add('is-focused');
        // Prefill input with label
        const label = items[focusedIdx].dataset.label;
        if (label) input.value = label;
    }

    // ── Events ──────────────────────────────────────────────────── //
    input.addEventListener('focus', () => {
        if (input.value.trim() === '') {
            showEmptyDropdown();
        } else if (input.value.trim().length >= 1) {
            debounce(() => fetchSuggestions(input.value.trim()), 50);
        }
    });

    input.addEventListener('input', () => {
        const q = input.value.trim();
        if (clearBtn) clearBtn.style.display = q ? 'flex' : '';

        if (q === '') { showEmptyDropdown(); return; }
        debounce(() => fetchSuggestions(q), 280);
    });

    input.addEventListener('keydown', (e) => {
        if (!dropdown.classList.contains('is-open')) return;

        if (e.key === 'ArrowDown') { e.preventDefault(); moveFocus(1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); moveFocus(-1); }
        else if (e.key === 'Escape') { closeDropdown(); input.blur(); }
        else if (e.key === 'Enter') {
            const items = getItems();
            if (focusedIdx >= 0 && items[focusedIdx]) {
                e.preventDefault();
                const href = items[focusedIdx].getAttribute('href');
                if (href) window.location.href = href;
            }
            // Otherwise let the form submit normally
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!form.contains(e.target)) closeDropdown();
    });

    // Clear button
    if (clearBtn) {
        clearBtn.style.display = input.value.trim() ? 'flex' : '';
        clearBtn.addEventListener('click', () => {
            input.value = '';
            clearBtn.style.display = '';
            input.focus();
            showEmptyDropdown();
        });
    }

    // Record to localStorage on form submit
    form.addEventListener('submit', () => {
        const q = input.value.trim();
        if (q) lsAddHistory(q);
    });

    // Expose server-side search history for the dropdown
    window.__bwmSearchHistory = @json(auth()->check() ? \App\Models\SearchHistory::recent(auth()->id(), 8) : []);
    
    // ── Voice Search Integration ──────────────────────────────────── //
    const transcriptSpan = document.getElementById('transcriptText');
    const voiceModalEl = document.getElementById('voiceSearchModal');
    
    let recognition;
    let isRecording = false;

    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        recognition.continuous = false;
        recognition.lang = 'vi-VN';
        recognition.interimResults = true; 

        recognition.onstart = () => {
            isRecording = true;
            if(transcriptSpan) transcriptSpan.textContent = 'Đang nghe...';
        };

        recognition.onresult = (event) => {
            let finalTranscript = '';
            let interimTranscript = '';
            for (let i = event.resultIndex; i < event.results.length; ++i) {
                if (event.results[i].isFinal) {
                    finalTranscript += event.results[i][0].transcript;
                } else {
                    interimTranscript += event.results[i][0].transcript;
                }
            }
            if(transcriptSpan) {
                transcriptSpan.textContent = finalTranscript !== '' ? finalTranscript : interimTranscript;
            }
            if (finalTranscript !== '') {
                input.value = finalTranscript.trim();
            }
        };

        recognition.onerror = (event) => {
            if(transcriptSpan) transcriptSpan.textContent = 'Lỗi nhận diện âm thanh: ' + event.error;
            setTimeout(() => {
                if(voiceModalEl) {
                    const bsModal = bootstrap.Modal.getInstance(voiceModalEl);
                    if(bsModal) bsModal.hide();
                }
            }, 3000);
        };

        recognition.onend = () => {
            isRecording = false;
            if (input.value.trim() !== '') {
                form.submit();
            } else {
                if(transcriptSpan) transcriptSpan.textContent = 'Không nhận diện được âm thanh. Đang đóng...';
                setTimeout(() => {
                    if(voiceModalEl) {
                        const bsModal = bootstrap.Modal.getInstance(voiceModalEl);
                        if(bsModal) bsModal.hide();
                    }
                }, 2000);
            }
        };
    }

    if (voiceModalEl) {
        voiceModalEl.addEventListener('show.bs.modal', function () {
            input.value = ''; // Reset ô text khi bắt đầu nghe
            if(transcriptSpan) transcriptSpan.textContent = 'Đang khởi động mic...';
            if (recognition) {
                try { recognition.start(); } catch(e){}
            } else {
                if(transcriptSpan) transcriptSpan.textContent = 'Trình duyệt của bạn không hỗ trợ nhận diện giọng nói (Web Speech API).';
            }
        });
        
        voiceModalEl.addEventListener('hide.bs.modal', function () {
            if (recognition && isRecording) {
                recognition.stop();
            }
        });
    }
})();
</script>
@endpush

@if(auth()->check())
<meta name="user-authenticated" content="1">
@endif
