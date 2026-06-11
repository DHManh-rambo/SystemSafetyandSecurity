<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || Auth::user()->vai_tro !== 'ADMIN') {
            abort(403, 'Chỉ Admin mới có quyền truy cập.');
        }

        return $next($request);
    }
}