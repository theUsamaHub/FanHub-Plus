<?php

use App\Http\Controllers\User\{AccountController, InteractionController};
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('user')->name('user.')->group(function () {
    Route::get('bookmarks', [AccountController::class, 'bookmarks'])->name('bookmarks');
    Route::get('favorites', [AccountController::class, 'favorites'])->name('favorites');
    Route::get('activity', [AccountController::class, 'activity'])->name('activity');
    Route::get('reviews', [AccountController::class, 'reviews'])->name('reviews');
    Route::get('submissions', [AccountController::class, 'submissions'])->name('submissions');
    Route::get('submissions/create', [AccountController::class, 'create'])->name('submissions.create');
    Route::get('submissions/{submission}/edit', [AccountController::class, 'edit'])->name('submissions.edit');
    Route::get('feedback', [AccountController::class, 'feedback'])->name('feedback');
    Route::middleware('throttle:30,1')->group(function () {
        Route::patch('preferences', [AccountController::class, 'preferences'])->name('preferences');
        Route::post('submissions', [AccountController::class, 'store'])->name('submissions.store');
        Route::put('submissions/{submission}', [AccountController::class, 'update'])->name('submissions.update');
        Route::delete('submissions/{submission}', [AccountController::class, 'destroy'])->name('submissions.destroy');
        Route::post('feedback', [AccountController::class, 'sendFeedback'])->name('feedback.store');
        Route::patch('bookmarks/{bookmark}', [InteractionController::class, 'note'])->name('bookmarks.note');
        Route::delete('bookmarks/{bookmark}', [InteractionController::class, 'removeBookmark'])->name('bookmarks.destroy');
        Route::post('favorites/{category}', [InteractionController::class, 'favorite'])->name('favorites.store');
        Route::post('items/{type}/{id}/bookmark', [InteractionController::class, 'bookmark'])->name('bookmark');
        Route::post('items/{type}/{id}/rating', [InteractionController::class, 'rating'])->name('rating');
        Route::post('items/{type}/{id}/review', [InteractionController::class, 'review'])->name('review');
        Route::delete('reviews/{review}', [InteractionController::class, 'removeReview'])->name('reviews.destroy');
        Route::post('watched/{content}', [InteractionController::class, 'watched'])->name('watched');
    });
});
