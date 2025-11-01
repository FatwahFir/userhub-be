<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function __invoke(ForgotPasswordRequest $request)
    {
        Password::sendResetLink(
            $request->only('email')
        );

        return ApiResponse::success([
            'message' => 'If the email exists, a password reset link has been sent.',
        ]);
    }
}
