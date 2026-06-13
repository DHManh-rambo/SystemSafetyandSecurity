<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {

        // Re-enabled full CSRF protection
        $middleware->validateCsrfTokens(except: [
            //
        ]);

        // Trust all proxies (needed for Ngrok / Cloudflare to get real client IP)
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \App\Http\Middleware\SecurityShield::class,
        ]);

        $middleware->alias([
            'role'       => \App\Http\Middleware\CheckRole::class,
            'admin'      => \App\Http\Middleware\CheckAdmin::class,
            'nhanvien'   => \App\Http\Middleware\CheckNhanVien::class,
            'khachhang'  => \App\Http\Middleware\CheckKhachHang::class,
            'shipper'    => \App\Http\Middleware\CheckShipper::class,
        ]);

    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();