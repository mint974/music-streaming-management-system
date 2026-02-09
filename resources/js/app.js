import './bootstrap';
import './player';
import '../scss/app.scss';

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
