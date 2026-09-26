<?php

use Illuminate\Support\Facades\Route;

// Admin-only routes — no non-admin may enter /admin/*
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin', 'ip-restrict'])
    ->group(function () {

        Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/categories/trashed', [\App\Http\Controllers\Admin\CategoryController::class, 'trashed'])->name('categories.trashed');
        Route::post('/categories/{id}/restore', [\App\Http\Controllers\Admin\CategoryController::class, 'restore'])->name('categories.restore')->withTrashed();
        Route::delete('/categories/{id}/force-delete', [\App\Http\Controllers\Admin\CategoryController::class, 'forceDelete'])->name('categories.force-delete')->withTrashed();
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);

        Route::patch('/contents/{content}/feature', [\App\Http\Controllers\Admin\ContentController::class, 'toggleFeatured'])->name('contents.feature');
        Route::patch('/contents/{content}/status', [\App\Http\Controllers\Admin\ContentController::class, 'updateStatus'])->name('contents.status');
        Route::resource('contents', \App\Http\Controllers\Admin\ContentController::class);

        Route::get('/submissions', [\App\Http\Controllers\Admin\SubmissionController::class, 'index'])->name('submissions.index');
        Route::get('/submissions/{content}', [\App\Http\Controllers\Admin\SubmissionController::class, 'show'])->name('submissions.show');
        Route::patch('/submissions/{content}/approve', [\App\Http\Controllers\Admin\SubmissionController::class, 'approve'])->name('submissions.approve');
        Route::patch('/submissions/{content}/reject', [\App\Http\Controllers\Admin\SubmissionController::class, 'reject'])->name('submissions.reject');

        Route::post('/characters/{character}/contents', [\App\Http\Controllers\Admin\CharacterController::class, 'attachContent'])->name('characters.contents.attach');
        Route::delete('/characters/{character}/contents/{content}', [\App\Http\Controllers\Admin\CharacterController::class, 'detachContent'])->name('characters.contents.detach');
        Route::resource('characters', \App\Http\Controllers\Admin\CharacterController::class);

        Route::resource('merchandise', \App\Http\Controllers\Admin\MerchandiseController::class)->parameters(['merchandise' => 'merchandise']);

        Route::resource('events', \App\Http\Controllers\Admin\EventController::class);

        Route::get('/reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/{review}', [\App\Http\Controllers\Admin\ReviewController::class, 'show'])->name('reviews.show');
        Route::patch('/reviews/{review}/approve', [\App\Http\Controllers\Admin\ReviewController::class, 'approve'])->name('reviews.approve');
        Route::patch('/reviews/{review}/reject', [\App\Http\Controllers\Admin\ReviewController::class, 'reject'])->name('reviews.reject');
        Route::delete('/reviews/{review}', [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

        Route::get('/ratings', [\App\Http\Controllers\Admin\RatingController::class, 'index'])->name('ratings.index');
        Route::delete('/ratings/{rating}', [\App\Http\Controllers\Admin\RatingController::class, 'destroy'])->name('ratings.destroy');

        Route::get('/feedback', [\App\Http\Controllers\Admin\FeedbackController::class, 'index'])->name('feedback.index');
        Route::get('/feedback/{feedback}', [\App\Http\Controllers\Admin\FeedbackController::class, 'show'])->name('feedback.show');
        Route::patch('/feedback/{feedback}/status', [\App\Http\Controllers\Admin\FeedbackController::class, 'updateStatus'])->name('feedback.status');
        Route::delete('/feedback/{feedback}', [\App\Http\Controllers\Admin\FeedbackController::class, 'destroy'])->name('feedback.destroy');

        Route::get('/analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');

        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

        Route::resource('contacts', \App\Http\Controllers\Admin\ContactController::class)->only(['index', 'show', 'destroy']);

        Route::get('/media', [\App\Http\Controllers\Admin\MediaController::class, 'index'])->name('media.index');
        Route::post('/media', [\App\Http\Controllers\Admin\MediaController::class, 'store'])->name('media.store');
        Route::delete('/media/{media}', [\App\Http\Controllers\Admin\MediaController::class, 'destroy'])->name('media.destroy');

        // Chunked upload for large files (movies)
        Route::post('/media/chunk/init', [\App\Http\Controllers\Admin\MediaController::class, 'initChunkedUpload'])->name('media.chunk.init');
        Route::post('/media/chunk/upload', [\App\Http\Controllers\Admin\MediaController::class, 'uploadChunk'])->name('media.chunk.upload');
        Route::post('/media/chunk/complete', [\App\Http\Controllers\Admin\MediaController::class, 'completeChunkedUpload'])->name('media.chunk.complete');
        Route::post('/media/chunk/cancel', [\App\Http\Controllers\Admin\MediaController::class, 'cancelChunkedUpload'])->name('media.chunk.cancel');

        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'store'])->name('settings.store');
        Route::delete('/settings/{setting}', [\App\Http\Controllers\Admin\SettingController::class, 'destroy'])->name('settings.destroy');

        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class)->except(['show']);

        Route::resource('tags', \App\Http\Controllers\Admin\TagController::class)->except(['show']);

        Route::get('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/activity-logs/export', [\App\Http\Controllers\Admin\ActivityLogController::class, 'export'])->name('activity-logs.export');
        Route::get('/activity-logs/{activity_log}', [\App\Http\Controllers\Admin\ActivityLogController::class, 'show'])->name('activity-logs.show');
        Route::delete('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'destroy'])->name('activity-logs.destroy');

        Route::get('/ip-restrictions', [\App\Http\Controllers\Admin\IpRestrictionController::class, 'index'])->name('ip-restrictions.index');
        Route::put('/ip-restrictions', [\App\Http\Controllers\Admin\IpRestrictionController::class, 'update'])->name('ip-restrictions.update');

        Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::delete('/notifications/{notification}', [\App\Http\Controllers\Admin\NotificationController::class, 'destroy'])->name('notifications.destroy');

        Route::get('/subscribers', [\App\Http\Controllers\Admin\SubscriberController::class, 'index'])->name('subscribers.index');
        Route::get('/subscribers/export', [\App\Http\Controllers\Admin\SubscriberController::class, 'export'])->name('subscribers.export');
        Route::get('/subscribers/{subscriber}', [\App\Http\Controllers\Admin\SubscriberController::class, 'show'])->name('subscribers.show');
        Route::get('/subscribers/{subscriber}/edit', [\App\Http\Controllers\Admin\SubscriberController::class, 'edit'])->name('subscribers.edit');
        Route::put('/subscribers/{subscriber}', [\App\Http\Controllers\Admin\SubscriberController::class, 'update'])->name('subscribers.update');
        Route::patch('/subscribers/{subscriber}/status', [\App\Http\Controllers\Admin\SubscriberController::class, 'updateStatus'])->name('subscribers.update-status');
        Route::post('/subscribers/bulk', [\App\Http\Controllers\Admin\SubscriberController::class, 'bulkAction'])->name('subscribers.bulk');
        Route::delete('/subscribers/{subscriber}', [\App\Http\Controllers\Admin\SubscriberController::class, 'destroy'])->name('subscribers.destroy');

        Route::get('/chatbot/faqs', [\App\Http\Controllers\Admin\ChatbotFaqController::class, 'index'])->name('chatbot.faqs.index');
        Route::get('/chatbot/faqs/create', [\App\Http\Controllers\Admin\ChatbotFaqController::class, 'create'])->name('chatbot.faqs.create');
        Route::post('/chatbot/faqs', [\App\Http\Controllers\Admin\ChatbotFaqController::class, 'store'])->name('chatbot.faqs.store');
        Route::get('/chatbot/faqs/{chatbotFaq}', [\App\Http\Controllers\Admin\ChatbotFaqController::class, 'show'])->name('chatbot.faqs.show');
        Route::get('/chatbot/faqs/{chatbotFaq}/edit', [\App\Http\Controllers\Admin\ChatbotFaqController::class, 'edit'])->name('chatbot.faqs.edit');
        Route::put('/chatbot/faqs/{chatbotFaq}', [\App\Http\Controllers\Admin\ChatbotFaqController::class, 'update'])->name('chatbot.faqs.update');
        Route::delete('/chatbot/faqs/{chatbotFaq}', [\App\Http\Controllers\Admin\ChatbotFaqController::class, 'destroy'])->name('chatbot.faqs.destroy');

        Route::get('/chatbot', [\App\Http\Controllers\Admin\ChatbotController::class, 'index'])->name('chatbot.index');
        Route::get('/chatbot/export', [\App\Http\Controllers\Admin\ChatbotController::class, 'export'])->name('chatbot.export');
        Route::delete('/chatbot/{query}', [\App\Http\Controllers\Admin\ChatbotController::class, 'destroy'])->name('chatbot.destroy');

        Route::get('/sessions', [\App\Http\Controllers\Admin\SessionController::class, 'index'])->name('sessions.index');
        Route::delete('/sessions/{id}', [\App\Http\Controllers\Admin\SessionController::class, 'destroy'])->name('sessions.destroy');

        Route::get('/maintenance', [\App\Http\Controllers\Admin\MaintenanceController::class, 'index'])->name('maintenance.index');
        Route::post('/maintenance/toggle', [\App\Http\Controllers\Admin\MaintenanceController::class, 'toggle'])->name('maintenance.toggle');
        Route::put('/maintenance/message', [\App\Http\Controllers\Admin\MaintenanceController::class, 'updateMessage'])->name('maintenance.message');
        Route::put('/maintenance/bypass-routes', [\App\Http\Controllers\Admin\MaintenanceController::class, 'updateBypassRoutes'])->name('maintenance.bypass-routes');

        Route::get('/health', [\App\Http\Controllers\Admin\HealthController::class, 'index'])->name('health.index');

        Route::get('/logs', [\App\Http\Controllers\Admin\LogViewerController::class, 'index'])->name('logs.index');
        Route::delete('/logs', [\App\Http\Controllers\Admin\LogViewerController::class, 'clear'])->name('logs.clear');
        Route::get('/logs/download', [\App\Http\Controllers\Admin\LogViewerController::class, 'download'])->name('logs.download');

        Route::get('/backup', [\App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backup.index');
        Route::post('/backup', [\App\Http\Controllers\Admin\BackupController::class, 'create'])->name('backup.create');
        Route::get('/backup/{filename}', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backup.download');
        Route::delete('/backup/{filename}', [\App\Http\Controllers\Admin\BackupController::class, 'destroy'])->name('backup.destroy');

        // Newsletters
        Route::resource('newsletters', \App\Http\Controllers\Admin\NewsletterController::class);
        Route::post('/newsletters/{newsletter}/send', [\App\Http\Controllers\Admin\NewsletterController::class, 'send'])->name('newsletters.send');
        Route::get('/newsletters/{newsletter}/preview', [\App\Http\Controllers\Admin\NewsletterController::class, 'preview'])->name('newsletters.preview');
    });
