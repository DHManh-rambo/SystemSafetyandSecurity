<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
{
    $request->validate([
        'ten_dang_nhap' => ['required', 'string'],
        'password'      => ['required', 'string'],
    ]);

    // Lưu lại giỏ hàng trước khi đăng nhập
    $gioHang = session('gio_hang', []);

    if (! Auth::attempt([
        'ten_dang_nhap' => $request->ten_dang_nhap,
        'password'      => $request->password,
    ], $request->boolean('remember'))) {
        return back()->withErrors([
            'ten_dang_nhap' => 'Tên đăng nhập hoặc mật khẩu không đúng.',
        ])->onlyInput('ten_dang_nhap');
    }

    $request->session()->regenerate();

    // Khôi phục lại giỏ hàng sau khi regenerate session
    session(['gio_hang' => $gioHang]);

    $vai_tro = Auth::user()->vai_tro;

    return match($vai_tro) {
        'ADMIN', 'NHAN_VIEN' => redirect()->route('admin.dashboard'),
        'SHIPPER'            => redirect()->route('shipper.dashboard'),
        default              => redirect()->route('customer.dashboard'),
    };
}

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.dashboard');
    }
}