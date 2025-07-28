<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'jwt.verify' => \Tymon\JWTAuth\Http\Middleware\Authenticate::class,
            'jwt.refresh' => \Tymon\JWTAuth\Http\Middleware\RefreshToken::class,
            'auth.api' => \App\Http\Middleware\AuthenticateAPI::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
            'rate_limit' => \App\Http\Middleware\RateLimitMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['status' => 'error', 'message' => 'Token expired'], 401);
        });
        
        $exceptions->renderable(function (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['status' => 'error', 'message' => 'Token invalid'], 401);
        });
        
        $exceptions->renderable(function (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['status' => 'error', 'message' => 'Token absent'], 401);
        });
    })
    ->create();