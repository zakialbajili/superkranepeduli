<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SSOUserMobile
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session('is_logged_in_api')) {
            $currentSessionId = Session::getId();
            $sessionUser = session('employee_no');

            $dbSessionId = DB::table('thseusermobile')
                ->where('employee_no', $sessionUser)
                ->value('token');

            if ($dbSessionId && $dbSessionId !== $currentSessionId) {
                session()->forget(['is_logged_in_api', 'employee_no', 'full_name', 'position']);
                return redirect()->route('login')->withErrors([
                    'message' => 'Anda telah logout karena akun Anda login dari perangkat lain.'
                ]);
            }
        }

        return $next($request);
    }
}
