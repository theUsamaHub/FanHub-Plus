<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\MediaServeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriberController;
use Illuminate\Support\Facades\Route;


Route::get('/', \App\Http\Controllers\HomeController::class)->name('home');

Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/dashboard', function () {
    session()->keep('onboarding-success');
    $user = auth()->user();

    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('registered-user')) {
        return redirect()->route('user.dashboard');
    }

    return redirect()->route('profile.edit')->with('error', __('Your account has no role assigned. Please contact an administrator.'));
})->middleware(['auth'])->name('dashboard');

Route::get('/user/dashboard', [\App\Http\Controllers\User\DashboardController::class, 'index'])
    ->middleware(['auth', 'role:registered-user'])
    ->name('user.dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/newsletter-preferences', [ProfileController::class, 'updateNewsletterPreferences'])->name('profile.newsletter-preferences');
});

Route::get('/unsubscribe/{token}', [SubscriberController::class, 'unsubscribe'])->name('unsubscribe');

require __DIR__.'/member.php';

Route::get('/storage/{path}', [MediaServeController::class, 'serve'])
    ->where('path', '.*')
    ->name('storage.serve');
