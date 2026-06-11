<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckKhachHang
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || Auth::user()->vai_tro !== 'KHACH_HANG') {
            abort(403, 'Chỉ Khách Hàng mới có quyền truy cập.');
        }

        return $next($request);
    }
}