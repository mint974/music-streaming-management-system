<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

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
    
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// Admin Routes
Route::middleware(['auth', 'active', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
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
