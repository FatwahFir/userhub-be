<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use App\Support\HandlesAvatarUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class RegisterController extends Controller
{
    use HandlesAvatarUploads;

    public function __invoke(RegisterRequest $request)
    {
        $user = DB::transaction(function () use ($request): User {
            $avatarPath = null;

            if ($request->hasFile('avatar')) {
                $avatarPath = $this->storeAvatar($request->file('avatar'));
            }

            $user = User::create([
                'username' => $request->input('username'),
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'phone' => $request->input('phone'),
                'avatar_path' => $avatarPath,
            ]);

            return $user;
        });

        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $exception) {
            return ApiResponse::error('TOKEN_GENERATION_FAILED', 'Unable to generate access token.', 500);
        }

        $user->refresh();

        return ApiResponse::success([
            'token' => $token,
            'user' => new UserResource($user),
        ], status: 201);
    }
}
