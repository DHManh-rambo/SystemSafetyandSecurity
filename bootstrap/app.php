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