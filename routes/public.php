<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicSiteController;

Route::get('/chatbot/faqs', [\App\Http\Controllers\ChatbotController::class, 'faqs'])->middleware('throttle:60,1')->name('chatbot.faqs');
Route::post('/chatbot/message', [\App\Http\Controllers\ChatbotController::class, 'message'])->middleware('throttle:12,1')->name('chatbot.message');

Route::get('/explore', [PublicSiteController::class, 'explore'])->name('public.explore');
Route::get('/events', [\App\Http\Controllers\EventController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}/calendar', [\App\Http\Controllers\EventController::class, 'calendar'])->name('events.calendar');
Route::get('/events/{event:slug}', [\App\Http\Controllers\EventController::class, 'show'])->name('events.show');
Route::redirect('/discover/events', '/events', 301);
Route::get('/stories/{content:slug}', [PublicSiteController::class, 'content'])->name('public.content');
Route::get('/characters/{character:slug}', [PublicSiteController::class, 'character'])->name('public.character');
Route::get('/collection/{merchandise:slug}', [PublicSiteController::class, 'merchandise'])->name('public.merchandise');
Route::get('/releases/{upcoming_release:slug}', [PublicSiteController::class, 'upcomingRelease'])->name('public.upcoming-release');
Route::post('/collection/{merchandise:slug}/bookmark', \App\Http\Controllers\MerchandiseBookmarkController::class)
    ->middleware(['auth', 'throttle:60,1'])->name('public.merchandise.bookmark');
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
