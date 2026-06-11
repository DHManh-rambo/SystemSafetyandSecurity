<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use Illuminate\Support\Facades\Auth;

class ChiTietSanPhamController extends Controller
{
    public function show($id)
    {
        $sanPham = SanPham::where('ma_san_pham', $id)
            ->where('trang_thai', 'DANG_BAN')
            ->with(['chiTietNhaps' => function ($q) {
                $q->where('so_luong_con_lai', '>', 0)
                  ->whereHas('phieuNhap', fn($q2) => $q2->where('trang_thai', 'CONFIRMED'))
                  ->orderBy('gia_ban', 'asc');
            }])
            ->firstOrFail();

        $user = auth()->check() ? Auth::user()->load('khachHang') : null;

        $sanPhamLienQuan = SanPham::where('trang_thai', 'DANG_BAN')
            ->where('loai_san_pham', $sanPham->loai_san_pham)
            ->where('ma_san_pham', '!=', $sanPham->ma_san_pham)
            ->with(['chiTietNhaps' => function ($q) {
                $q->where('so_luong_con_lai', '>', 0)
                  ->whereHas('phieuNhap', fn($q2) => $q2->where('trang_thai', 'CONFIRMED'));
            }])
            ->orderBy('ma_san_pham', 'desc')
            ->limit(6)
            ->get();

        return view('customer.ChiTietSanPham', compact('sanPham', 'user', 'sanPhamLienQuan'));
    }
}