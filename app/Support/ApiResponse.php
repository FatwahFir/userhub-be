<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Build a standardized success response envelope.
     */
    public static function success(
        mixed $data = null,
        array $meta = [],
        ?array $pagination = null,
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $data,
            'error' => null,
            'pagination' => $pagination,
            'meta' => array_merge([
                'request_id' => request()->attributes->get('request_id'),
                'timestamp' => now()->toIso8601String(),
            ], $meta),
        ], $status);
    }

    /**
     * Build a standardized error response envelope.
     */
    public static function error(
        string $code,
        string $message,
        int $status = 400,
        mixed $details = null,
        array $meta = []
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'data' => null,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
            'pagination' => null,
            'meta' => array_merge([
                'request_id' => request()->attributes->get('request_id'),
                'timestamp' => now()->toIso8601String(),
            ], $meta),
        ], $status);
    }
}
