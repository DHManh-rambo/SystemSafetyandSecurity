<?php

namespace App\Http\Controllers\Shipper;

use App\Http\Controllers\Controller;
use App\Models\HoaDon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ThongBaoController extends Controller
{
    /**
     * Key cache cho danh sách thông báo của 1 khách hàng.
     */
    private function cacheKey(int $maKhach): string
    {
        return "thong_bao_khach_{$maKhach}";
    }

    // ─────────────────────────────────────────────────────────────────
    // SHIPPER: Nhấn "Đã đến điểm giao" → ghi thông báo vào cache
    // ─────────────────────────────────────────────────────────────────
    public function guiThongBao(Request $request, $id)
    {
        $user       = Auth::user();
        $maNhanVien = $user->ma_nguoi_dung;

        $hoaDon = HoaDon::with(['chiTietHoaDon.sanPham', 'khachHang'])
            ->where('ma_nhan_vien_giao', $maNhanVien)
            ->findOrFail($id);

        if ($hoaDon->trang_thai !== 'SHIPPING') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể gửi thông báo khi đơn hàng đang được vận chuyển.',
            ], 422);
        }

        // Tổng hợp tên sản phẩm
        $danhSachSanPham = $hoaDon->chiTietHoaDon
            ->map(fn($item) =>
                ($item->sanPham->ten_san_pham ?? 'Sản phẩm #' . $item->ma_san_pham)
                . ' (×' . $item->so_luong . ')'
            )
            ->join(', ');

        $maHD    = '#HD-' . str_pad($hoaDon->ma_hoa_don, 4, '0', STR_PAD_LEFT);
        $diaChi  = $hoaDon->dia_chi_giao;
        $noiDung = "Đơn hàng {$maHD} gồm: {$danhSachSanPham} đã đến địa điểm giao {$diaChi}. Bạn hãy xuống nhận hàng nhé! 🌸";

        // Đọc danh sách thông báo hiện có, thêm mới vào đầu
        $maKhach   = (int) $hoaDon->ma_khach_hang;
        $key       = $this->cacheKey($maKhach);
        $danhSach  = Cache::get($key, []);

        // Tạo id đơn giản = timestamp_ms để dùng khi xóa
        $tbId = uniqid('tb_', true);
        array_unshift($danhSach, [
            'id'       => $tbId,
            'ma_hd'    => $hoaDon->ma_hoa_don,
            'noi_dung' => $noiDung,
            'thoi_gian'=> now()->format('H:i, d/m/Y'),
        ]);

        // Giữ tối đa 20 thông báo, TTL 7 ngày
        $danhSach = array_slice($danhSach, 0, 20);
        Cache::put($key, $danhSach, now()->addDays(7));

        return response()->json([
            'success'  => true,
            'message'  => 'Đã gửi thông báo đến khách hàng!',
            'noi_dung' => $noiDung,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // KHÁCH HÀNG: Xem danh sách thông báo
    // ─────────────────────────────────────────────────────────────────
    public function index()
    {
        $maKhach   = (int) Auth::user()->ma_nguoi_dung;
        $thongBaos = Cache::get($this->cacheKey($maKhach), []);

        return view('Customer.ThongBao', compact('thongBaos'));
    }

    // ─────────────────────────────────────────────────────────────────
    // KHÁCH HÀNG: Trả về số thông báo chưa đọc (dùng cho chấm đỏ chuông)
    // ─────────────────────────────────────────────────────────────────
    public function soChuaDoc()
    {
        $maKhach = (int) Auth::user()->ma_nguoi_dung;
        $count   = count(Cache::get($this->cacheKey($maKhach), []));
        return response()->json(['count' => $count]);
    }

    // ─────────────────────────────────────────────────────────────────
    // KHÁCH HÀNG: Ấn X → xóa 1 thông báo khỏi cache
    // ─────────────────────────────────────────────────────────────────
    public function xoa(Request $request, string $id)
    {
        $maKhach  = (int) Auth::user()->ma_nguoi_dung;
        $key      = $this->cacheKey($maKhach);
        $danhSach = Cache::get($key, []);

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