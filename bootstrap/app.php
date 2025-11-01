<?php

use App\Support\ApiResponse;
use App\Support\HttpHelpers;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
        ]);

        $middleware->group('api', [
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\AddRequestId::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (HttpHelpers::shouldReturnJson($request)) {
                return ApiResponse::error(
                    'VALIDATION_ERROR',
                    'The given data was invalid.',
                    422,
                    $exception->errors()
                );
            }

            return null;
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
            if (HttpHelpers::shouldReturnJson($request)) {
                return ApiResponse::error('NOT_FOUND', 'Resource not found.', 404);
            }

            return null;
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (HttpHelpers::shouldReturnJson($request)) {
                return ApiResponse::error('UNAUTHENTICATED', 'Authentication required.', 401);
            }

            return null;
        });

        $exceptions->render(function (\Throwable $exception, Request $request) {
            if (!HttpHelpers::shouldReturnJson($request)) {
                return null;
            }

            $status = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : 500;

            $code = $status >= 500 ? 'INTERNAL_SERVER_ERROR' : 'HTTP_EXCEPTION';
            $message = $status >= 500 ? 'Something went wrong.' : $exception->getMessage();
            $details = null;

            if (config('app.debug')) {
                $details = [
                    'exception' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'file' => sprintf('%s:%d', $exception->getFile(), $exception->getLine()),
                    'trace' => array_map(
                        static function (array $frame): array {
                            return array_filter([
                                'file' => $frame['file'] ?? null,
                                'line' => $frame['line'] ?? null,
                                'class' => $frame['class'] ?? null,
                                'function' => $frame['function'] ?? null,
                            ], static fn ($value) => $value !== null);
                        },
                        array_slice($exception->getTrace(), 0, 5)
                    ),
                ];
            }

            return ApiResponse::error($code, $message, $status, $details);
        });
    })->create();
