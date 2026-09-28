<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]));

    Route::prefix('auth')->group(function () {
        Route::post('/register', [\App\Http\Controllers\Api\V1\Auth\RegisteredUserController::class, 'store'])
            ->middleware('guest')
            ->name('api.v1.auth.register');

        Route::post('/login', [\App\Http\Controllers\Api\V1\Auth\AuthenticatedSessionController::class, 'store'])
            ->middleware('guest')
            ->name('api.v1.auth.login');

        Route::post('/logout', [\App\Http\Controllers\Api\V1\Auth\AuthenticatedSessionController::class, 'destroy'])
            ->middleware('auth:sanctum')
            ->name('api.v1.auth.logout');
    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/user', [\App\Http\Controllers\Api\V1\Auth\AuthenticatedSessionController::class, 'show'])
            ->name('api.v1.user');

        Route::put('/profile', [\App\Http\Controllers\Api\V1\ProfileController::class, 'update'])
            ->name('api.v1.profile.update');

        Route::middleware('role:admin')->group(function () {
            Route::apiResource('categories', \App\Http\Controllers\Api\V1\CategoryController::class);
        });

        Route::middleware('role:admin')->group(function () {
            Route::get('/users', [\App\Http\Controllers\Api\V1\UserController::class, 'index'])
                ->name('api.v1.users.index');
            Route::get('/users/{user}', [\App\Http\Controllers\Api\V1\UserController::class, 'show'])
                ->name('api.v1.users.show');
            Route::put('/users/{user}', [\App\Http\Controllers\Api\V1\UserController::class, 'update'])
                ->name('api.v1.users.update');
            Route::delete('/users/{user}', [\App\Http\Controllers\Api\V1\UserController::class, 'destroy'])
                ->name('api.v1.users.destroy');
        });
    });
});
