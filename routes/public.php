<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicSiteController;

Route::get('/explore', [PublicSiteController::class, 'explore'])->name('public.explore');
Route::get('/discover/{section}', [PublicSiteController::class, 'section'])->name('public.section');
Route::get('/account/{section}', [PublicSiteController::class, 'account'])->middleware('auth')->name('public.account');
Route::view('/sitemap', 'public.sitemap')->name('public.sitemap');

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
|
| Public-facing routes that don't require authentication.
| These are separate from web.php to keep concerns clean.
|
*/

Route::get('/about', function () {
    return view('public.about');
})->name('public.about');

Route::get('/services', function () {
    return view('public.services');
})->name('public.services');

Route::get('/pricing', function () {
    return view('public.pricing');
})->name('public.pricing');

Route::get('/contact', function () {
    return view('public.contact');
})->name('public.contact');

Route::post('/subscribe', [\App\Http\Controllers\SubscriberController::class, 'store'])->name('public.subscribe');
