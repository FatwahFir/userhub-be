<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request)
    {
        $credentials = [
            'password' => $request->input('password'),
        ];

        $identifier = $request->input('username');
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials[$field] = $identifier;

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return ApiResponse::error('INVALID_CREDENTIALS', 'Invalid credentials.', 401);
            }
        } catch (JWTException $exception) {
            return ApiResponse::error('TOKEN_GENERATION_FAILED', 'Unable to generate access token.', 500);
        }

        $user = Auth::user();

        return ApiResponse::success([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }
}
