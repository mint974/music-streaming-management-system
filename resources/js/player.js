// Optimized: Execute immediately when script loads (after DOM is ready due to defer)
(function() {
    'use strict';
    
    // ==================== PLAYER CONTROLS ====================
    const playBtn = document.querySelector('.btn-play');
    const mainPlayBtn = document.getElementById('mainPlayBtn');
    let isPlaying = false;

    function togglePlay() {
        isPlaying = !isPlaying;
        const icon = playBtn?.querySelector('i');
        const mainIcon = mainPlayBtn?.querySelector('i');
        
        if (icon) {
            icon.classList.toggle('fa-play');
            icon.classList.toggle('fa-pause');
        }
        
        if (mainIcon) {
            mainIcon.classList.toggle('fa-play');
            mainIcon.classList.toggle('fa-pause');
        }
    }

    if (playBtn) {
        playBtn.addEventListener('click', togglePlay);
    }

    if (mainPlayBtn) {
        mainPlayBtn.addEventListener('click', togglePlay);
    }

    // ==================== FAVORITE BUTTONS ====================
    const favoriteBtn = document.getElementById('favoriteBtn');
    const playerFavoriteBtn = document.querySelector('.btn-favorite');
    
    function toggleFavorite(btn) {
        if (!btn) return;
        btn.classList.toggle('active');
        const icon = btn.querySelector('i');
        if (icon) {
            icon.classList.toggle('fa-regular');
            icon.classList.toggle('fa-solid');
        }
    }

    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', () => toggleFavorite(favoriteBtn));
    }

    if (playerFavoriteBtn) {
        playerFavoriteBtn.addEventListener('click', () => toggleFavorite(playerFavoriteBtn));
    }

    // Song list favorite buttons
    const songFavoriteBtns = document.querySelectorAll('.btn-favorite-song');
    songFavoriteBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleFavorite(this);
        });
    });

    // ==================== SONG ROW INTERACTIONS ====================
    const songRows = document.querySelectorAll('.song-row');
    
    songRows.forEach(row => {
        const playBtn = row.querySelector('.btn-play-sm');
        
        row.addEventListener('click', function(e) {
            // Don't trigger if clicking on buttons
            if (e.target.closest('button')) return;
            
            // Play the song
            console.log('Playing song:', this.dataset.songId);
        });

        if (playBtn) {
            playBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                console.log('Play button clicked for song:', row.dataset.songId);
                
                // Toggle play icon
                const icon = this.querySelector('i');
                if (icon) {
                    const isCurrentlyPlaying = icon.classList.contains('fa-pause');
                    
                    // Reset all other play buttons
                    document.querySelectorAll('.btn-play-sm i').forEach(i => {
                        i.classList.remove('fa-pause');
                        i.classList.add('fa-play');
                    });
                    
                    // Toggle current button
                    if (isCurrentlyPlaying) {
                        icon.classList.remove('fa-pause');
                        icon.classList.add('fa-play');
                    } else {
                        icon.classList.remove('fa-play');
                        icon.classList.add('fa-pause');
                    }
                }
            });
        }
    });

    // ==================== PROGRESS BAR ====================
    const progressBar = document.querySelector('.player-progress .progress-bar');
    const progressContainer = document.querySelector('.player-progress .progress');
    
    if (progressContainer && progressBar) {
        progressContainer.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const percent = ((e.clientX - rect.left) / rect.width) * 100;
            progressBar.style.width = percent + '%';
            progressBar.setAttribute('aria-valuenow', percent);
        });

        // Simulate progress (for demo)
        let currentProgress = 25;
        setInterval(() => {
            if (isPlaying && currentProgress < 100) {
                currentProgress += 0.1;
                progressBar.style.width = currentProgress + '%';
                progressBar.setAttribute('aria-valuenow', currentProgress);
                
                // Update time (this is just a demo, use actual audio duration)
                const currentTime = Math.floor((currentProgress / 100) * 182); // 3:02 = 182 seconds
                const minutes = Math.floor(currentTime / 60);
                const seconds = currentTime % 60;
                const timeDisplay = document.querySelector('.time-current');
                if (timeDisplay) {
                    timeDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                }
            } else if (isPlaying && currentProgress >= 100) {
                currentProgress = 0;
                isPlaying = false;
                togglePlay();
            }
        }, 100);
    }

    // ==================== VOLUME CONTROL ====================
    const volumeControl = document.querySelector('.volume-control input[type="range"]');
    const volumeIcon = document.querySelector('.player-options .btn-icon-sm .fa-volume-high');
    
    if (volumeControl) {
        volumeControl.addEventListener('input', function() {
            const volume = this.value;
            
            // Update icon based on volume
            if (volumeIcon) {
                volumeIcon.classList.remove('fa-volume-high', 'fa-volume-low', 'fa-volume-off');
                
                if (volume == 0) {
                    volumeIcon.classList.add('fa-volume-off');
                } else if (volume < 50) {
                    volumeIcon.classList.add('fa-volume-low');
                } else {
                    volumeIcon.classList.add('fa-volume-high');
                }
            }
        });
    }

    // ==================== SEARCH BAR ====================
    const searchInput = document.querySelector('.search-box input');
    
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            
            searchTimeout = setTimeout(() => {
                const query = this.value.trim();
                if (query.length > 0) {
                    console.log('Searching for:', query);
                    // Implement search logic here
                }
            }, 300); // Debounce for 300ms
        });

        searchInput.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });

        searchInput.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    }

    // ==================== SHUFFLE & REPEAT ====================
    const shuffleBtn = document.querySelector('.btn-control[title="Shuffle"]');
    const repeatBtn = document.querySelector('.btn-control[title="Repeat"]');
    
    if (shuffleBtn) {
        shuffleBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            const icon = this.querySelector('i');
            if (this.classList.contains('active')) {
                icon.style.color = '#1db954';
            } else {
                icon.style.color = '';
            }
        });
    }

    if (repeatBtn) {
        repeatBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            const icon = this.querySelector('i');
            if (this.classList.contains('active')) {
                icon.style.color = '#1db954';
            } else {
                icon.style.color = '';
            }
        });
    }

    // ==================== DROPDOWN MENUS ====================
    const dropdownToggles = document.querySelectorAll('[data-bs-toggle="dropdown"]');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;
            
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(m => {
                if (m !== menu) {
                    m.classList.remove('show');
                }
            });
            
            // Toggle current dropdown
            if (menu) {
                menu.classList.toggle('show');
            }
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown-menu') && !e.target.closest('[data-bs-toggle="dropdown"]')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

    // ==================== SMOOTH SCROLLING ====================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // ==================== KEYBOARD SHORTCUTS ====================
    document.addEventListener('keydown', function(e) {
        // Space bar - Play/Pause
        if (e.code === 'Space' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            if (playBtn) playBtn.click();
        }
        
        // Arrow Right - Next song
        if (e.code === 'ArrowRight' && e.ctrlKey) {
            e.preventDefault();
            const nextBtn = document.querySelector('.btn-control[title="Next"]');
            if (nextBtn) nextBtn.click();
        }
        
        // Arrow Left - Previous song
        if (e.code === 'ArrowLeft' && e.ctrlKey) {
            e.preventDefault();
            const prevBtn = document.querySelector('.btn-control[title="Previous"]');
            if (prevBtn) prevBtn.click();
        }
    });

    // ==================== LOADING ANIMATIONS ====================
    // Add skeleton loader for images
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('load', function() {
            this.classList.add('loaded');
        });
    });

    // ==================== HOVER EFFECTS ====================
    // Add ripple effect to buttons
    const buttons = document.querySelectorAll('button, .btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
})();
