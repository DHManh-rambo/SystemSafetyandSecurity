<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckNhanVien
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            !Auth::check() ||
            !in_array(Auth::user()->vai_tro, ['ADMIN', 'NHAN_VIEN'])
        ) {
            abort(403, 'Chỉ Nhân Viên hoặc Admin mới có quyền truy cập.');
        }

        return $next($request);
    }
}