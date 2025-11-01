<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use App\Support\HandlesAvatarUploads;

class UpdateProfileController extends Controller
{
    use HandlesAvatarUploads;

    public function __invoke(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = collect($request->validated())
            ->except(['avatar'])
            ->filter(fn ($value) => !is_null($value))
            ->toArray();

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $this->storeAvatar($request->file('avatar'), $user->avatar_path);
        }

        $user->fill($data);
        $user->save();

        return ApiResponse::success(new UserResource($user->fresh()));
    }
}
