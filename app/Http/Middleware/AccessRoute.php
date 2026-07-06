<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessRoute
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->user()->can(\Illuminate\Support\Facades\Route::currentRouteName())) {
            if ($request->ajax()) {
                return response()->json(["message" => 'Anda Tidak Diijinkan. Hubungi Administrator!!', 'status' => 'error']);
            }
            toastr('Anda Tidak Diijinkan. Hubungi Administrator!', 'error');
            return back();
        }
        return $next($request);
    }
}