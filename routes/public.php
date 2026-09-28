<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicSiteController;

Route::get('/chatbot/faqs', [\App\Http\Controllers\ChatbotController::class, 'faqs'])->middleware('throttle:60,1')->name('chatbot.faqs');
Route::post('/chatbot/message', [\App\Http\Controllers\ChatbotController::class, 'message'])->middleware('throttle:12,1')->name('chatbot.message');

Route::get('/explore', [PublicSiteController::class, 'explore'])->name('public.explore');
Route::get('/fan-content', [\App\Http\Controllers\FanContentController::class, 'index'])->name('public.fan-content.index');
Route::get('/fan-content/{content:slug}', [\App\Http\Controllers\FanContentController::class, 'show'])->name('public.fan-content.show');
Route::get('/fandom/{category:slug}', [PublicSiteController::class, 'fandom'])->name('public.fandom');
Route::get('/events', [\App\Http\Controllers\EventController::class, 'index'])->name('events.index');
Route::get('/events/nearby', \App\Http\Controllers\NearbyEventsController::class)->name('events.nearby');
Route::post('/events/nearby', \App\Http\Controllers\NearbyEventsController::class)->name('events.nearby.search');
Route::get('/events/{event:slug}/calendar', [\App\Http\Controllers\EventController::class, 'calendar'])->name('events.calendar');
Route::get('/events/{event:slug}', [\App\Http\Controllers\EventController::class, 'show'])->name('events.show');
Route::redirect('/discover/events', '/events', 301);
Route::get('/stories/{content:slug}', [PublicSiteController::class, 'content'])->name('public.content');
Route::get('/characters/{character:slug}', [PublicSiteController::class, 'character'])->name('public.character');
Route::get('/collection/{merchandise:slug}', [PublicSiteController::class, 'merchandise'])->name('public.merchandise');
Route::post('/collection/{merchandise:slug}/bookmark', \App\Http\Controllers\MerchandiseBookmarkController::class)
    ->middleware(['auth', 'throttle:60,1'])->name('public.merchandise.bookmark');
Route::get('/discover/{section}', [PublicSiteController::class, 'section'])->name('public.section');
Route::get('/account/{section}', [PublicSiteController::class, 'account'])->middleware('auth')->name('public.account');
Route::view('/sitemap', 'public.sitemap')->name('public.sitemap');


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

Route::post('/subscribe', [\App\Http\Controllers\SubscriberController::class, 'store'])
    ->middleware('throttle:60,1')
    ->name('public.subscribe');
