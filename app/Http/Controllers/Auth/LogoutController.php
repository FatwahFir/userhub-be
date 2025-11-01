<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class LogoutController extends Controller
{
    public function __invoke(Request $request)
    {
        $token = $request->bearerToken();

        if ($token) {
            try {
                JWTAuth::setToken($token)->invalidate();
            } catch (JWTException $exception) {
                // If blacklisting is disabled we can safely ignore invalidation errors.
            }
        }

        return ApiResponse::success([
            'message' => 'Logged out successfully.',
        ]);
    }
}
