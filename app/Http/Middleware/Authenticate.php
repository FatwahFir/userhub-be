<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use App\Support\HttpHelpers;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;

class Authenticate extends Middleware
{
    /**
     * Determine if the user is logged in to any of the given guards.
     */
    protected function authenticate($request, array $guards)
    {
        try {
            parent::authenticate($request, $guards);
        } catch (\Throwable $exception) {
            $this->unauthenticated($request, $guards);
        }
    }

    /**
     * Handle unauthenticated requests.
     */
    protected function unauthenticated($request, array $guards)
    {
        if (HttpHelpers::shouldReturnJson($request)) {
            throw new HttpResponseException(
                ApiResponse::error('UNAUTHENTICATED', 'Authentication required.', 401)
            );
        }

        parent::unauthenticated($request, $guards);
    }
}
