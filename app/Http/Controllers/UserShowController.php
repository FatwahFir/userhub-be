<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;

class UserShowController extends Controller
{
    public function __invoke(User $user)
    {
        return ApiResponse::success(new UserResource($user));
    }
}
