<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ArtistController as AdminArtistController;
use App\Http\Controllers\Admin\VipController as AdminVipController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\GenreController as AdminGenreController;
use App\Http\Controllers\UnlockRequestController;
use App\Http\Controllers\Admin\UnlockRequestController as AdminUnlockRequestController;
use App\Http\Controllers\ArtistRegistrationController;
use App\Http\Controllers\Admin\ArtistRegistrationController as AdminArtistRegistrationController;
use App\Http\Controllers\ArtistProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Artist\SongController as ArtistSongController;
use App\Http\Controllers\Artist\AlbumController as ArtistAlbumController;
use App\Http\Controllers\ListenerDataController;
use App\Http\Controllers\SongBrowseController;
use App\Http\Controllers\AlbumBrowseController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\Listener\PlaylistController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// ─── Stream nhạc (công khai; VIP & status check bên trong controller) ────────
Route::get('/stream/{song}', [StreamController::class, 'stream'])->name('songs.stream');
Route::post('/listen/record', [\App\Http\Controllers\ListeningStatController::class, 'record'])->name('listen.record');
Route::get('/songs', [SongBrowseController::class, 'index'])->name('songs.index');
Route::get('/songs/{song}', [SongBrowseController::class, 'show'])->name('songs.show');
Route::get('/api/songs/{song}/lyrics', [SongBrowseController::class, 'lyrics'])->name('api.songs.lyrics');
Route::get('/albums', [AlbumBrowseController::class, 'index'])->name('albums.index');
Route::get('/albums/{album}', [AlbumBrowseController::class, 'show'])->name('albums.show');

// ─── Tìm kiếm (công khai – cả khách vãng lai) ────────────────────────────────
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/artists/{artistId}', [SearchController::class, 'artistShow'])->name('search.artist.show');
Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');

// Lịch sử tìm kiếm (yêu cầu đăng nhập)
Route::middleware(['auth', 'active'])->group(function () {
    Route::delete('/search/history', [SearchController::class, 'clearHistory'])->name('search.history.clear');
    Route::delete('/search/history/item', [SearchController::class, 'removeHistoryItem'])->name('search.history.remove');

    // Listener data module
    Route::get('/listener', [ListenerDataController::class, 'index'])->name('listener.index');
    Route::post('/listener/follow-artist/{artistId}', [ListenerDataController::class, 'toggleFollowArtist'])
        ->name('listener.artist.toggleFollow');
    Route::post('/listener/save-album/{albumId}', [ListenerDataController::class, 'toggleSaveAlbum'])
        ->name('listener.album.toggleSave');
    Route::get('/listener/history', [ListenerDataController::class, 'history'])->name('listener.history');
    Route::get('/listener/favorites', [ListenerDataController::class, 'favorites'])->name('listener.favorites');
    Route::get('/listener/albums', [ListenerDataController::class, 'albums'])->name('listener.albums');
    Route::post('/listener/favorites/{songId}', [ListenerDataController::class, 'toggleFavoriteSong'])
        ->name('listener.song.toggleFavorite');
    Route::delete('/listener/history', [ListenerDataController::class, 'clearHistory'])->name('listener.history.clear');
    Route::delete('/listener/history/{id}', [ListenerDataController::class, 'removeHistoryItem'])
        ->name('listener.history.remove');
    Route::get('/listener/settings', [ListenerDataController::class, 'settings'])->name('listener.settings');
    Route::patch('/listener/settings', [ListenerDataController::class, 'updateSettings'])->name('listener.settings.update');

    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');

    // Playlist module (Listener / User Personal Playlists)
    Route::get('/listener/playlists', [PlaylistController::class, 'index'])->name('listener.playlists.index');
    Route::post('/listener/playlists', [PlaylistController::class, 'store'])->name('listener.playlists.store');
    Route::get('/listener/playlists/{playlist}', [PlaylistController::class, 'show'])->name('listener.playlists.show');
    Route::put('/listener/playlists/{playlist}', [PlaylistController::class, 'update'])->name('listener.playlists.update');
    Route::delete('/listener/playlists/{playlist}', [PlaylistController::class, 'destroy'])->name('listener.playlists.destroy');
    Route::post('/listener/playlists/{playlist}/songs', [PlaylistController::class, 'addSong'])->name('listener.playlists.addSong');
    Route::get('/listener/playlists/{playlist}/search-songs', [PlaylistController::class, 'searchSongsForPlaylist'])->name('listener.playlists.searchSongs');
    Route::delete('/listener/playlists/{playlist}/songs', [PlaylistController::class, 'removeSong'])->name('listener.playlists.removeSong');
    Route::post('/listener/playlists/{playlist}/reorder', [PlaylistController::class, 'reorder'])->name('listener.playlists.reorder');
});

// Authentication Routes (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

// Protected Routes (Authenticated users only)
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/profile', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/email/verify', [UserProfileController::class, 'showVerificationNotice'])
        ->name('verification.notice');
    Route::post('/email/verification-notification', [UserProfileController::class, 'sendVerificationNotification'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::get('/email/verify/{id}/{hash}', [UserProfileController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');

    // Subscription (user-facing — VNPAY)
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/checkout/{vipId}', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::post('/subscription/{id}/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::post('/subscription/{id}/pay-pending', [SubscriptionController::class, 'payPending'])->name('subscription.payPending');
    Route::post('/subscription/{id}/cancel-pending', [SubscriptionController::class, 'cancelPending'])->name('subscription.cancelPending');

    // Artist registration (user-facing)
    Route::get('/artist-register', [ArtistRegistrationController::class, 'index'])->name('artist-register.index');
    Route::get('/artist-register/{packageId}', [ArtistRegistrationController::class, 'create'])->name('artist-register.create');
    Route::post('/artist-register/{packageId}', [ArtistRegistrationController::class, 'checkout'])->name('artist-register.checkout');
});

// VNPAY return URL — outside auth middleware (VNPAY redirects back, session may differ)
Route::get('/subscription/vnpay/return', [SubscriptionController::class, 'vnpayReturn'])
    ->name('subscription.vnpay.return');

Route::get('/artist-register/vnpay/return', [ArtistRegistrationController::class, 'vnpayReturn'])
    ->name('artist-register.vnpay.return');

// ─── Yêu cầu mở khóa tài khoản (không cần đăng nhập - dành cho user bị khóa) ──
Route::get('/account/unlock-request', [UnlockRequestController::class, 'create'])->name('unlock-request.create');
Route::post('/account/unlock-request', [UnlockRequestController::class, 'store'])->name('unlock-request.store');
Route::get('/account/unlock-request/sent', [UnlockRequestController::class, 'sent'])->name('unlock-request.sent');

// ─── Admin Site (separate guard = separate session from 'web') ────────────────
// Guest routes: only accessible when NOT logged in via the 'admin' guard
Route::middleware('guest:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'store']);
});

// Protected admin routes: must be authenticated via the 'admin' guard
Route::middleware(['auth:admin', 'active:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Profile
    Route::get('/profile', function () { return 'Admin Profile'; })->name('profile.edit');

    // User management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('/users/{id}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('users.update');
    Route::post('/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggleStatus');
    Route::post('/users/{id}/change-role', [AdminUserController::class, 'changeRole'])->name('users.changeRole');
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Unlock requests management
    Route::get('/unlock-requests', [AdminUnlockRequestController::class, 'index'])->name('unlock-requests.index');
    Route::post('/unlock-requests/{id}/approve', [AdminUnlockRequestController::class, 'approve'])->name('unlock-requests.approve');
    Route::post('/unlock-requests/{id}/reject', [AdminUnlockRequestController::class, 'reject'])->name('unlock-requests.reject');

    // Artist registration review
    Route::get('/artist-registrations', [AdminArtistRegistrationController::class, 'index'])->name('artist-registrations.index');
    Route::post('/artist-registrations/{id}/approve', [AdminArtistRegistrationController::class, 'approve'])->name('artist-registrations.approve');
    Route::post('/artist-registrations/{id}/reject', [AdminArtistRegistrationController::class, 'reject'])->name('artist-registrations.reject');
    Route::post('/artist-registrations/{id}/confirm-refund', [AdminArtistRegistrationController::class, 'confirmRefund'])->name('artist-registrations.confirmRefund');

    // Artist management
    Route::get('/artists', [AdminArtistController::class, 'index'])->name('artists.index');
    Route::post('/artists/{id}/toggle-status', [AdminArtistController::class, 'toggleStatus'])->name('artists.toggleStatus');
    Route::post('/artists/{id}/toggle-verify', [AdminArtistController::class, 'toggleVerify'])->name('artists.toggleVerify');
    Route::post('/artists/{id}/revoke', [AdminArtistController::class, 'revoke'])->name('artists.revoke');

    // Song management
    Route::get('/songs', function () { return 'Admin Song Management'; })->name('songs.index');

    // VIP package management
    Route::get('/vips', [AdminVipController::class, 'index'])->name('vips.index');
    Route::post('/vips', [AdminVipController::class, 'store'])->name('vips.store');
    Route::put('/vips/{id}', [AdminVipController::class, 'update'])->name('vips.update');
    Route::post('/vips/{id}/toggle-active', [AdminVipController::class, 'toggleActive'])->name('vips.toggleActive');
    Route::delete('/vips/{id}', [AdminVipController::class, 'destroy'])->name('vips.destroy');

    // Subscription management
    Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions', [AdminSubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::post('/subscriptions/{id}/cancel', [AdminSubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::post('/subscriptions/{id}/expire', [AdminSubscriptionController::class, 'expire'])->name('subscriptions.expire');

    // Genre management
    Route::get('/genres', [AdminGenreController::class, 'index'])->name('genres.index');
    Route::post('/genres', [AdminGenreController::class, 'store'])->name('genres.store');
    Route::post('/genres/reorder', [AdminGenreController::class, 'reorder'])->name('genres.reorder');
    Route::put('/genres/{id}', [AdminGenreController::class, 'update'])->name('genres.update');
    Route::post('/genres/{id}/toggle-active', [AdminGenreController::class, 'toggleActive'])->name('genres.toggleActive');
    Route::delete('/genres/{id}', [AdminGenreController::class, 'destroy'])->name('genres.destroy');

    // Reports
    Route::get('/reports', function () { return 'Admin Reports'; })->name('reports.index');
});

// Artist Studio Routes
Route::middleware(['auth', 'active', 'role:artist,admin'])->prefix('artist')->name('artist.')->group(function () {
    Route::get('/dashboard', function () {
        return view('artist.dashboard');
    })->name('dashboard');

    // Profile
    Route::get('/profile', [ArtistProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ArtistProfileController::class, 'update'])->name('profile.update');

    // Songs
    Route::resource('/songs', ArtistSongController::class);

    // Song Lyrics Management
    Route::prefix('songs/{song}/lyrics')->name('songs.lyrics.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Artist\LyricController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Artist\LyricController::class, 'store'])->name('store');
        Route::get('/{lyric}/preview', [\App\Http\Controllers\Artist\LyricController::class, 'preview'])->name('preview');
        Route::post('/{lyric}/verify', [\App\Http\Controllers\Artist\LyricController::class, 'verify'])->name('verify');
        Route::delete('/{lyric}', [\App\Http\Controllers\Artist\LyricController::class, 'destroy'])->name('destroy');
    });

    // Albums
    Route::resource('/albums', ArtistAlbumController::class);

    // Stats
    Route::get('/stats', [\App\Http\Controllers\Artist\StatsController::class, 'index'])->name('stats.index');
});
