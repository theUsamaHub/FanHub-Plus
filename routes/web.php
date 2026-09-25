<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Routes for the web interface. These routes use the "web" middleware group.
| Authentication routes are in routes/auth.php
|
*/

Route::get('/', \App\Http\Controllers\HomeController::class)->name('home');

// Contact form
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Dashboard - redirect based on role
Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('registered-user')) {
        return redirect()->route('home');
    }

    return redirect()->route('profile.edit')->with('error', __('Your account has no role assigned. Please contact an administrator.'));
})->middleware(['auth'])->name('dashboard');

// User Dashboard
Route::get('/user/dashboard', [\App\Http\Controllers\User\DashboardController::class, 'index'])
    ->middleware(['auth', 'role:registered-user'])
    ->name('user.dashboard');

// Profile
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Live Chat
Route::middleware(['auth'])->prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [\App\Http\Controllers\LiveChatController::class, 'index'])->name('index');
    Route::post('/send', [\App\Http\Controllers\LiveChatController::class, 'send'])->name('send');
});
