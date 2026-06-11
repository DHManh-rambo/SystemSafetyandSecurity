<?php

namespace App\Http\Controllers\Shipper;

use App\Models\HoaDon;
use App\Models\NhanVien;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ShipperController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $maNhanVien = $user->ma_nguoi_dung;

        $shipper = NhanVien::findOrFail($maNhanVien);

        $tienCanTra = HoaDon::where('ma_nhan_vien_giao', $maNhanVien)
            ->where('trang_thai', 'DELIVERED')
            ->where('trang_thai_thanh_toan', 'DA_THANH_TOAN')
            ->where('phuong_thuc_thanh_toan', 'COD')
            ->sum('tong_tien');

        $soDonConNo = HoaDon::where('ma_nhan_vien_giao', $maNhanVien)
            ->where('trang_thai', 'DELIVERED')
            ->where('trang_thai_thanh_toan', 'DA_THANH_TOAN')
            ->where('phuong_thuc_thanh_toan', 'COD')
            ->count();

        $tienPhat = ShipperThongBaoController::getPenalty($maNhanVien);

        $soThongBao = count(Cache::get(ShipperThongBaoController::cacheKey($maNhanVien), []));

        $donHangCanShip = HoaDon::with('khachHang')
            ->where('ma_nhan_vien_giao', $maNhanVien)
            ->whereIn('trang_thai', ['CONFIRMED', 'SHIPPING'])
            ->orderByDesc('ngay_dat')
            ->get();

        $lichSuDonHang = HoaDon::with('khachHang')
            ->where('ma_nhan_vien_giao', $maNhanVien)
            ->where('trang_thai', 'DELIVERED')
            ->orderByDesc('ngay_giao')
            ->paginate(10);

        $tongDoanhThu = HoaDon::where('ma_nhan_vien_giao', $maNhanVien)
            ->where('trang_thai', 'DELIVERED')
            ->sum('tong_tien');

        return view('Shipper.ShipperDashboard', compact(
            'shipper',
            'tienCanTra',
            'soDonConNo',
            'donHangCanShip',
            'lichSuDonHang',
            'tongDoanhThu',
            'tienPhat',
            'soThongBao'
        ));
    }

    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $maNhanVien = $user->ma_nguoi_dung;

        $hoaDon = HoaDon::with(['chiTietHoaDon.sanPham', 'khachHang'])
            ->findOrFail($id);

        if ((int) $hoaDon->ma_nhan_vien_giao !== (int) $maNhanVien) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền cập nhật đơn hàng này.',
            ], 403);
        }

        $map = [
            'CONFIRMED' => 'SHIPPING',
            'SHIPPING'  => 'DELIVERED',
        ];

        if (!isset($map[$hoaDon->trang_thai])) {
            return response()->json([
                'success' => false,
                'message' => 'Trạng thái hiện tại không thể cập nhật.',
            ], 422);
        }

        $newStatus  = $map[$hoaDon->trang_thai];
        $updateData = ['trang_thai' => $newStatus];

        $maHD = '#HD-' . str_pad($id, 4, '0', STR_PAD_LEFT);

        if ($newStatus === 'SHIPPING') {
            $startKey = ShipperThongBaoController::shippingStartKey($id);
            Cache::put($startKey, now()->toIso8601String(), now()->addHours(24));

            $maKhach         = (int) $hoaDon->ma_khach_hang;
            $danhSachSanPham = $hoaDon->chiTietHoaDon
                ->map(fn($item) =>
                    ($item->sanPham->ten_san_pham ?? 'Sản phẩm #' . $item->ma_san_pham)
                    . ' (×' . $item->so_luong . ')'
                )
                ->join(', ');

            $noiDungKhach = "🚚 Đơn hàng {$maHD} gồm {$danhSachSanPham} đã được shipper nhận và đang trên đường giao đến bạn! Vui lòng chú ý điện thoại.";

            $keyKhach   = "thong_bao_khach_{$maKhach}";
            $dsKhach    = Cache::get($keyKhach, []);
            array_unshift($dsKhach, [
                'id'        => uniqid('tb_', true),
                'noi_dung'  => $noiDungKhach,
                'loai'      => 'shipping',
                'thoi_gian' => now()->format('H:i, d/m/Y'),
            ]);
            $dsKhach = array_slice($dsKhach, 0, 20);
            Cache::put($keyKhach, $dsKhach, now()->addDays(7));
        }

        if ($newStatus === 'DELIVERED') {
            $updateData['ngay_giao']             = now();
            $updateData['trang_thai_thanh_toan'] = 'DA_THANH_TOAN';
        }

        $hoaDon->update($updateData);

        return response()->json([
            'success'    => true,
            'new_status' => $newStatus,
            'message'    => $newStatus === 'SHIPPING'
                ? 'Đã bắt đầu giao đơn ' . $maHD . '.'
                : 'Đã hoàn thành đơn ' . $maHD . '!',
        ]);
    }
}