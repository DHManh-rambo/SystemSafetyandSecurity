<?php

namespace App\Http\Controllers\Shipper;

use App\Http\Controllers\Controller;
use App\Models\HoaDon;
use Illuminate\Support\Facades\Auth;

class NhanDonController extends Controller
{
    
    public function show($id)
    {
        $user       = Auth::user();
        $maNhanVien = $user->ma_nguoi_dung;

        $hoaDon = HoaDon::with([
                'khachHang',
                'chiTietHoaDon.sanPham',  
            ])
            ->where('ma_nhan_vien_giao', $maNhanVien)
            ->findOrFail($id);

        return view('Shipper.NhanDon', compact('hoaDon'));
    }
}