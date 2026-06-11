<?php

namespace App\Http\Controllers\Shipper;

use App\Http\Controllers\Controller;
use App\Models\HoaDon;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ShipperThongBaoController extends Controller
{
  
    public static function cacheKey(int $maNhanVien): string
    {
        return "thong_bao_shipper_{$maNhanVien}";
    }

   
    public static function shippingStartKey(int $maHoaDon): string
    {
        return "shipping_start_{$maHoaDon}";
    }

   
    public static function penaltyDoneKey(int $maHoaDon): string
    {
        return "shipping_penalty_done_{$maHoaDon}";
    }

    
    public static function push(int $maNhanVien, string $noiDung, string $loai = 'info'): void
    {
        $key      = self::cacheKey($maNhanVien);
        $danhSach = Cache::get($key, []);

        array_unshift($danhSach, [
            'id'        => uniqid('stb_', true),
            'noi_dung'  => $noiDung,
            'loai'      => $loai,   
            'thoi_gian' => now()->format('H:i, d/m/Y'),
        ]);

        $danhSach = array_slice($danhSach, 0, 30);
        Cache::put($key, $danhSach, now()->addDays(14));
    }

    
    public function index()
    {
        $maNhanVien = (int) Auth::user()->ma_nguoi_dung;
        $thongBaos  = Cache::get(self::cacheKey($maNhanVien), []);
        $soThongBao = count($thongBaos);

        return view('Shipper.ShipperThongBao', compact('thongBaos', 'soThongBao'));
    }

  
    public function soChuaDoc()
    {
        $maNhanVien = (int) Auth::user()->ma_nguoi_dung;
        $count      = count(Cache::get(self::cacheKey($maNhanVien), []));
        return response()->json(['count' => $count]);
    }

    
    public function xoa(string $id)
    {
        $maNhanVien = (int) Auth::user()->ma_nguoi_dung;
        $key        = self::cacheKey($maNhanVien);
        $danhSach   = Cache::get($key, []);

        $danhSach = array_values(
            array_filter($danhSach, fn($tb) => $tb['id'] !== $id)
        );

        empty($danhSach) ? Cache::forget($key) : Cache::put($key, $danhSach, now()->addDays(14));

        return response()->json(['success' => true]);
    }

 
    public function checkTimeout(int $maHoaDon)
    {
        $maNhanVien  = (int) Auth::user()->ma_nguoi_dung;
        $penaltyKey  = self::penaltyDoneKey($maHoaDon);

        if (Cache::has($penaltyKey)) {
            return response()->json(['status' => 'already_handled']);
        }

        $startKey   = self::shippingStartKey($maHoaDon);
        $startTime  = Cache::get($startKey);

        if (!$startTime) {
            return response()->json(['status' => 'no_start_time']);
        }

        $elapsed = now()->diffInSeconds(\Carbon\Carbon::parse($startTime));

        if ($elapsed < 10800) {
            return response()->json([
                'status'          => 'ok',
                'elapsed_seconds' => $elapsed,
                'remaining'       => 10800 - $elapsed,
            ]);
        }

        $hoaDon = HoaDon::with(['khachHang', 'chiTietHoaDon.sanPham'])
            ->where('ma_nhan_vien_giao', $maNhanVien)
            ->find($maHoaDon);

        if (!$hoaDon || $hoaDon->trang_thai === 'DELIVERED') {
            Cache::put($penaltyKey, true, now()->addDays(30));
            return response()->json(['status' => 'delivered_ok']);
        }

        Cache::put($penaltyKey, true, now()->addDays(30));

        $maHD = '#HD-' . str_pad($maHoaDon, 4, '0', STR_PAD_LEFT);

        $maKhach = $hoaDon->ma_khach_hang;
        if ($maKhach) {
            $khachHang = \App\Models\KhachHang::find($maKhach);
            if ($khachHang) {
                $khachHang->increment('diem_tich_luy', 15);
            }
        }

        $this->pushKhachXinLoi($maKhach, $maHD);

        $penaltyCacheKey = "shipper_penalty_{$maNhanVien}";
        $currentPenalty  = (int) Cache::get($penaltyCacheKey, 0);
        Cache::put($penaltyCacheKey, $currentPenalty + 15000, now()->addDays(60));

        $noiDungShipper = "⚠️ Đơn hàng {$maHD} quá 3 tiếng chưa giao thành công. "
            . "Bạn đã bị trừ thêm 15.000 đ vào khoản cần nộp vì giao trễ.";
        self::push($maNhanVien, $noiDungShipper, 'danger');

        return response()->json([
            'status'  => 'penalty_applied',
            'message' => 'Đã xử lý phạt. Khách được cộng 15 điểm, bạn bị cộng thêm 15.000 đ.',
        ]);
    }

   
    private function pushKhachXinLoi(int $maKhach, string $maHD): void
    {
        $key      = "thong_bao_khach_{$maKhach}";
        $danhSach = Cache::get($key, []);

        array_unshift($danhSach, [
            'id'        => uniqid('tb_', true),
            'noi_dung'  => "😔 Xin lỗi vì sự bất tiện! Đơn hàng {$maHD} của bạn chưa được giao đúng hẹn. "
                         . "Chúng tôi đã cộng thêm 15 điểm tích lũy vào tài khoản của bạn để bù đắp. Cảm ơn bạn đã thông cảm! 💐",
            'loai'      => 'apology',
            'thoi_gian' => now()->format('H:i, d/m/Y'),
        ]);

        $danhSach = array_slice($danhSach, 0, 20);
        Cache::put($key, $danhSach, now()->addDays(7));
    }

    
    public static function getPenalty(int $maNhanVien): int
    {
        return (int) Cache::get("shipper_penalty_{$maNhanVien}", 0);
    }

    
    public static function clearPenalty(int $maNhanVien): void
    {
        Cache::forget("shipper_penalty_{$maNhanVien}");
    }
}