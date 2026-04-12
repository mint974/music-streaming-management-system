import './bootstrap';
import './player';
import './pages/song-detail.js';
import './scroll-animations.js';

(() => {
    const confirmModalEl = document.getElementById('globalConfirmModal');
    const confirmTitleEl = document.getElementById('globalConfirmModalTitle');
    const confirmSubtitleEl = document.getElementById('globalConfirmModalSubtitle');
    const confirmMessageEl = document.getElementById('globalConfirmModalMessage');
    const confirmAcceptBtn = confirmModalEl?.querySelector('[data-confirm-accept]');
    const confirmCancelBtn = confirmModalEl?.querySelector('[data-confirm-cancel]');

    let confirmModalInstance = null;
    let confirmResolver = null;

    function getConfirmModal() {
        if (!confirmModalEl || typeof bootstrap === 'undefined') {
            return null;
        }

        if (!confirmModalInstance) {
            confirmModalInstance = bootstrap.Modal.getOrCreateInstance(confirmModalEl);
        }

        return confirmModalInstance;
    }

    window.showConfirmModal = function showConfirmModal(message, options = {}) {
        return new Promise((resolve) => {
            if (!confirmModalEl || typeof bootstrap === 'undefined') {
                resolve(false);
                return;
            }

            confirmResolver = resolve;
            confirmTitleEl.textContent = options.title || 'Xác nhận';
            confirmSubtitleEl.textContent = options.subtitle || 'Hành động này không thể hoàn tác.';
            confirmMessageEl.textContent = message;

            confirmAcceptBtn.textContent = options.acceptLabel || 'Xác nhận';
            confirmCancelBtn.textContent = options.cancelLabel || 'Hủy';

            getConfirmModal()?.show();
        });
    };

    confirmAcceptBtn?.addEventListener('click', () => {
        if (confirmResolver) {
            confirmResolver(true);
            confirmResolver = null;
        }
        getConfirmModal()?.hide();
    });

    confirmCancelBtn?.addEventListener('click', () => {
        if (confirmResolver) {
            confirmResolver(false);
            confirmResolver = null;
        }
        getConfirmModal()?.hide();
    });

    confirmModalEl?.addEventListener('hidden.bs.modal', () => {
        if (confirmResolver) {
            confirmResolver(false);
            confirmResolver = null;
        }
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.dataset.confirmed === '1') {
            delete form.dataset.confirmed;
            return;
        }

        const message = form.dataset.confirmMessage;
        if (!message) {
            return;
        }

        event.preventDefault();
        const accepted = await window.showConfirmModal(message, {
            title: form.dataset.confirmTitle || 'Xác nhận',
            subtitle: form.dataset.confirmSubtitle || 'Hành động này không thể hoàn tác.',
            acceptLabel: form.dataset.confirmAcceptLabel || 'Xác nhận',
            cancelLabel: form.dataset.confirmCancelLabel || 'Hủy',
        });

        if (accepted) {
            form.dataset.confirmed = '1';
            form.submit();
        }
    }, true);

    document.addEventListener('click', async (event) => {
        const trigger = event.target.closest('[data-confirm-message]');
        if (!trigger) {
            return;
        }

        const form = trigger.closest('form');

        event.preventDefault();
        event.stopImmediatePropagation();

        const message = form?.dataset.confirmMessage || trigger.dataset.confirmMessage;
        if (!message) {
            return;
        }

        const accepted = await window.showConfirmModal(message, {
            title: form?.dataset.confirmTitle || trigger.dataset.confirmTitle || 'Xác nhận',
            subtitle: form?.dataset.confirmSubtitle || trigger.dataset.confirmSubtitle || 'Hành động này không thể hoàn tác.',
            acceptLabel: form?.dataset.confirmAcceptLabel || trigger.dataset.confirmAcceptLabel || 'Xác nhận',
            cancelLabel: form?.dataset.confirmCancelLabel || trigger.dataset.confirmCancelLabel || 'Hủy',
        });

        if (!accepted) {
            return;
        }

        if (form) {
            if (form.dataset.confirmed === '1') {
                delete form.dataset.confirmed;
                return;
            }

            form.dataset.confirmed = '1';
            form.submit();
            return;
        }

        const originalMessage = trigger.dataset.confirmMessage;
        delete trigger.dataset.confirmMessage;
        trigger.click();
        trigger.dataset.confirmMessage = originalMessage;
    }, true);
})();

// User Dropdown Toggle
window.toggleUserDropdown = function() {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
};

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('userDropdown');
    const userMenu = document.querySelector('.user-menu');
    
    if (dropdown && userMenu && !userMenu.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});

// Search functionality with debouncing
(() => {
    const searchInput = document.getElementById('searchInput');
    const searchClear = document.getElementById('searchClear');
    
    if (searchInput && searchClear) {
        // Show/hide clear button based on input value
        const updateClearButton = () => {
            searchClear.style.display = searchInput.value ? 'flex' : 'none';
        };
        
        // Initial state
        updateClearButton();
        
        // Update on input
        searchInput.addEventListener('input', updateClearButton);
        
        // Clear search
        searchClear.addEventListener('click', () => {
            searchInput.value = '';
            updateClearButton();
            searchInput.focus();
        });
        
        // Search with debouncing
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const query = e.target.value.trim();
                if (query.length >= 2) {
                    console.log('Searching for:', query);
                    // TODO: Implement search API call here
                }
            }, 300);
        });
    }
})();

// Sidebar playlist interactions
(() => {
    const addPlaylistBtn = document.querySelector('.btn-add-playlist');
    const createPlaylistCard = document.querySelector('.playlist-card.create');
    
    // Add playlist button
    if (addPlaylistBtn) {
        addPlaylistBtn.addEventListener('click', () => {
            console.log('Create playlist clicked');
            // TODO: Implement create playlist modal here
        });
    }
    
    // Create first playlist card
    if (createPlaylistCard) {
        createPlaylistCard.addEventListener('click', () => {
            console.log('Create your first playlist clicked');
            // TODO: Implement create playlist flow here
        });
    }
    
    // Playlist options buttons
    const playlistOptions = document.querySelectorAll('.btn-playlist-options');
    playlistOptions.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            console.log('Playlist options clicked');
            // TODO: Implement playlist options menu here
        });
    });
    
    // Playlist card click
    const playlistCards = document.querySelectorAll('.playlist-card:not(.create)');
    playlistCards.forEach(card => {
        card.addEventListener('click', () => {
            // Remove active from all cards
            playlistCards.forEach(c => c.classList.remove('active'));
            // Add active to clicked card
            card.classList.add('active');
            console.log('Playlist selected');
            // TODO: Load playlist songs
        });
    });
})();

// Notification badge interaction
(() => {
    const notificationBtn = document.querySelector('.btn-notification');
    
    if (notificationBtn) {
        notificationBtn.addEventListener('click', () => {
            console.log('Notifications clicked');
            // TODO: Implement notification dropdown
        });
    }
})();
