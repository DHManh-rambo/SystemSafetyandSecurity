<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\HoaDon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;  

class ThongBaoController extends Controller
{
    
    private function cacheKey(int $maKhach): string
    {
        return "thong_bao_khach_{$maKhach}";
    }

    
    public function index(Request $request)
    {
        $user        = Auth::user();
        $maKhachHang = (int) $user->ma_nguoi_dung;

        $activeTab = in_array($request->get('tab'), ['thong-bao', 'don-hien-tai', 'lich-su'])
            ? $request->get('tab')
            : 'thong-bao';

        $thongBaos  = Cache::get($this->cacheKey($maKhachHang), []);
        $soThongBao = count($thongBaos);

        $donHienTai = HoaDon::with(['chiTietHoaDon.sanPham', 'shipper'])
            ->where('ma_khach_hang', $maKhachHang)
            ->whereIn('trang_thai', ['PENDING', 'CONFIRMED', 'SHIPPING'])
            ->orderByDesc('ngay_dat')
            ->get();

        $soDonHienTai = $donHienTai->count();

        $lichSuDonHang = HoaDon::with(['chiTietHoaDon.sanPham'])
            ->where('ma_khach_hang', $maKhachHang)
            ->whereIn('trang_thai', ['DELIVERED', 'CANCELLED'])
            ->orderByDesc('ngay_dat')
            ->get();

        return view('Customer.ThongBao', compact(
            'thongBaos',
            'soThongBao',
            'donHienTai',
            'soDonHienTai',
            'lichSuDonHang',
            'activeTab'
        ));
    }

   
    public function soChuaDoc()
    {
        $maKhachHang = (int) Auth::user()->ma_nguoi_dung;
        $count       = count(Cache::get($this->cacheKey($maKhachHang), []));

        return response()->json(['count' => $count]);
    }

   
    public function xoa(Request $request, string $id)
    {
        $maKhachHang = (int) Auth::user()->ma_nguoi_dung;
        $key         = $this->cacheKey($maKhachHang);
        $danhSach    = Cache::get($key, []);

        $danhSach = array_values(
            array_filter($danhSach, fn($tb) => $tb['id'] !== $id)
        );

        if (empty($danhSach)) {
            Cache::forget($key);
        } else {
            Cache::put($key, $danhSach, now()->addDays(7));
        }

        return response()->json(['success' => true]);
    }
}