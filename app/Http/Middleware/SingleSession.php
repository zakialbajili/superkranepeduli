<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SingleSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $currentSessionId = Session::getId();
            $dbSessionId = Auth::user()->token;

            if ($dbSessionId && $dbSessionId !== $currentSessionId) {
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'message' => 'You have been logged out because your account was logged in from another device.'
                ]);
            }
        }

        return $next($request);
    }
}
