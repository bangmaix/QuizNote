<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            abort(403, 'Unauthorized - Please login first');
        }

        if ($request->user()->role !== 'student') {
            return redirect()->route('creator.dashboard')->with('error', 'Anda harus login sebagai Peserta untuk akses halaman ini');
        }
        
        return $next($request);
    }
}
