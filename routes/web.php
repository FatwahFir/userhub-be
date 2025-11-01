<?php

use App\Http\Controllers\Auth\PasswordResetPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('reset-password', [PasswordResetPageController::class, 'show'])
    ->name('password.reset');
