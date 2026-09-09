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

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Contact form
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Dashboard - redirect based on role
Route::get('/dashboard', function () {
    if (auth()->user()->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('user.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// User Dashboard
Route::get('/user/dashboard', [\App\Http\Controllers\User\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:user'])
    ->name('user.dashboard');

// Profile
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
