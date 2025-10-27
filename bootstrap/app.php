<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AccessMiddleware;
use App\Http\Middleware\ErrorHandlerMiddleware;
use App\Http\Middleware\AuthenticateMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware
        $middleware->append(ErrorHandlerMiddleware::class);

        // Route middleware (use in routes with ->middleware('access:admin'))
        $middleware->alias([
            'auth.jwt' => AuthenticateMiddleware::class,
            'access' => AccessMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
