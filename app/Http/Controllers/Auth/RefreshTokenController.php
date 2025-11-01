<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class RefreshTokenController extends Controller
{
    public function __invoke()
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();
        } catch (JWTException $exception) {
            return ApiResponse::error('TOKEN_REFRESH_FAILED', 'Unable to refresh token.', 401);
        }

        $user = JWTAuth::setToken($newToken)->toUser();

        return ApiResponse::success([
            'token' => $newToken,
            'user' => new UserResource($user),
        ]);
    }
}
