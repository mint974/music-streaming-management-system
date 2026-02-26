<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

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
});

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

    // User management
    Route::get('/users', function () {
        return 'Admin User Management';
    })->name('users.index');
});

// Singer Routes
Route::middleware(['auth', 'active', 'role:singer,admin'])->prefix('singer')->name('singer.')->group(function () {
    Route::get('/dashboard', function () {
        return view('singer.dashboard');
    })->name('dashboard');
    
    // Music management
    Route::get('/songs', function () {
        return 'Singer Songs Management';
    })->name('songs.index');
});
