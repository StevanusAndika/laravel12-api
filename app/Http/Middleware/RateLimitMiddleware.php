<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $key = 'api-rate-limit:' . ($request->user()?->id ?: $request->ip());

        if (RateLimiter::tooManyAttempts($key, 20)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again in ' . RateLimiter::availableIn($key) . ' seconds.'
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}