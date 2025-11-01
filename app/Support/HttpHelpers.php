<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HttpHelpers
{
    /**
     * Determine whether the given request should receive a JSON response.
     */
    public static function shouldReturnJson(Request $request): bool
    {
        if ($request->expectsJson()) {
            return true;
        }

        $accept = $request->headers->get('Accept', '');
        if (Str::contains($accept, ['/json', '+json'])) {
            return true;
        }

        if ($request->is('api/*')) {
            return true;
        }

        $route = $request->route();
        if ($route) {
            $name = $route->getName();
            if (is_string($name) && str_starts_with($name, 'api.')) {
                return true;
            }

            $prefix = data_get($route->getAction(), 'prefix');
            if (is_string($prefix) && Str::startsWith(trim($prefix, '/'), 'api')) {
                return true;
            }
        }

        return false;
    }
}
