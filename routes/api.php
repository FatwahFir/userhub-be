<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RefreshTokenController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\UpdateProfileController;
use App\Http\Controllers\UserIndexController;
use App\Http\Controllers\UserShowController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')
    ->prefix('v1')
    ->group(function (): void {
        Route::prefix('auth')->group(function (): void {
            Route::post('register', RegisterController::class);
            Route::post('login', LoginController::class);
            Route::post('forgot-password', ForgotPasswordController::class);
            Route::post('reset-password', ResetPasswordController::class);

            Route::middleware('auth:api')->group(function (): void {
                Route::post('logout', LogoutController::class);
                Route::post('refresh', RefreshTokenController::class);
            });
        });

        Route::middleware('auth:api')->group(function (): void {
            Route::get('me', MeController::class);
            Route::put('me', UpdateProfileController::class);

            Route::get('users', UserIndexController::class);
            Route::get('users/{user}', UserShowController::class);
        });
    });
