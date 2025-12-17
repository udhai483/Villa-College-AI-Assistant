<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Rate limiting for API endpoints
        $middleware->alias([
            'throttle.chat' => \Illuminate\Routing\Middleware\ThrottleRequests::class.':60,1',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
