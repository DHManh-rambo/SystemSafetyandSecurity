<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $tongSanPham = DB::table('san_pham')->count();

        $tongKhachHang = DB::table('khach_hang')->count();

        $tongDonHang = DB::table('hoa_don')->count();

        $doanhThuHomNay = DB::table('hoa_don')
            ->where('trang_thai', 'DELIVERED')
            ->whereDate('ngay_dat', $today)
            ->sum('tong_tien');

        $donChoXuLy = DB::table('hoa_don as hd')
            ->leftJoin('khach_hang as kh', 'hd.ma_khach_hang', '=', 'kh.ma_khach_hang')
            ->whereIn('hd.trang_thai', ['PENDING', 'CONFIRMED'])
            ->select(
                'hd.ma_hoa_don',
                'hd.ngay_dat',
                'hd.trang_thai',
                'kh.ten_khach_hang'
            )
            ->orderByDesc('hd.ngay_dat')
            ->limit(5)
            ->get();

        $topSanPham = DB::table('hoa_don as hd')
            ->join('chi_tiet_hoa_don as ct', 'hd.ma_hoa_don', '=', 'ct.ma_hoa_don')
            ->join('san_pham as sp', 'ct.ma_san_pham', '=', 'sp.ma_san_pham')
            ->where('hd.trang_thai', 'DELIVERED')
            ->select(
                'sp.ten_san_pham',
                DB::raw('SUM(ct.so_luong) as tong_ban'),
                DB::raw('SUM(ct.so_luong * ct.gia_ban_snapshot) as doanh_thu')
            )
            ->groupBy('sp.ma_san_pham', 'sp.ten_san_pham')
            ->orderByDesc('tong_ban')
            ->limit(5)
            ->get();

        $sanPhamSapHet = DB::table('san_pham')
            ->select('ten_san_pham', 'so_luong')
            ->where('trang_thai', 'DANG_BAN')
            ->where('so_luong', '<=', 10)
            ->orderBy('so_luong')
            ->limit(5)
            ->get();
        $doanhThu7Ngay = DB::table('hoa_don')
    ->where('trang_thai', 'DELIVERED')
    ->whereDate('ngay_dat', '>=', now()->subDays(6)->toDateString())
    ->selectRaw('DATE(ngay_dat) as ngay, SUM(tong_tien) as doanh_thu')
    ->groupByRaw('DATE(ngay_dat)')
    ->orderBy('ngay')
    ->get();
    $range = request('range', '7days');

$startDate = match ($range) {
    'today' => now()->startOfDay(),
    '30days' => now()->subDays(29)->startOfDay(),
    'month' => now()->startOfMonth(),
    'year' => now()->startOfYear(),
    default => now()->subDays(6)->startOfDay(),
};

$doanhThuTheoNgay = DB::table('hoa_don')
    ->where('trang_thai', 'DELIVERED')
    ->whereDate('ngay_dat', '>=', $startDate)
    ->selectRaw('DATE(ngay_dat) as ngay, SUM(tong_tien) as doanh_thu')
    ->groupByRaw('DATE(ngay_dat)')
    ->orderBy('ngay')
    ->get();
        return view('AdminStaffDashboard', compact(
            'user',
            'tongSanPham',
            'tongKhachHang',
            'tongDonHang',
            'doanhThuHomNay',
            'donChoXuLy',
            'topSanPham',
            'sanPhamSapHet',
            'doanhThu7Ngay',
            'doanhThuTheoNgay',
            'range'
        ));
    }
}