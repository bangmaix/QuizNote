<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CreatorMiddleware
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

        if ($request->user()->role !== 'creator') {
            return redirect()->route('student.dashboard')->with('error', 'Anda harus login sebagai Pembuat Soal untuk akses halaman ini');
        }
        
        return $next($request);
    }
}
