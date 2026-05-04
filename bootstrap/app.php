<?php

declare(strict_types=1);

use App\Exceptions\Domain\CartOperationException;
use App\Exceptions\Domain\CheckoutOperationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (CartOperationException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'errors' => [
                        $exception->field => [$exception->getMessage()],
                    ],
                ], 422);
            }

            return back()->withInput()->withErrors([
                $exception->field => $exception->getMessage(),
            ]);
        });

        $exceptions->render(function (CheckoutOperationException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'errors' => [
                        $exception->field => [$exception->getMessage()],
                    ],
                ], 422);
            }

            return back()->withInput()->withErrors([
                $exception->field => $exception->getMessage(),
            ]);
        });
    })->create();
