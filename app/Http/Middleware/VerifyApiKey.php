<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey || !hash_equals(config('app.api_secret_key'), $apiKey)) {
            return response(['status' => 401, 'message' => 'Unauthorized.', 'type' => 'fail'], 401);
        }

        return $next($request);
    }
}
