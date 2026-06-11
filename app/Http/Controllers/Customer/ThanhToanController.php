<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\HoaDon;
use App\Models\ChiTietHoaDon;
use App\Models\ChiTietNhap;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ThanhToanController extends Controller
{
    const DIEM_QUY_DOI = 1000;
    const TICH_DIEM    = 100000;

    public function index()
    {
        $user = Auth::user()->load('khachHang');

        // Priority: mua_ngay -> checkout_items -> (none allowed)
        if (session()->has('mua_ngay')) {
            $gioHang = session('mua_ngay', []);
        } elseif (session()->has('checkout_items')) {
            $gioHang = session('checkout_items', []);
        } else {
            return redirect()->route('customer.gio-hang')
                ->with('error', 'Giỏ hàng của bạn đang trống!');
        }

        $diemSuDung = session('diem_su_dung', 0);

        return view('customer.ThanhToan', compact('user', 'gioHang', 'diemSuDung'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ten_nguoi_nhan'         => 'required|string|max:100',
            'so_dien_thoai'          => 'required|string|size:10|regex:/^\d{10}$/',
            'quan_huyen'             => 'required|string|max:100',
            'xa_phuong'              => 'required|string|max:100',
            'dia_chi_chi_tiet'       => 'required|string|max:255',
            'phuong_thuc_thanh_toan' => 'required|in:NGAN_HANG,COD',
        ], [
            'ten_nguoi_nhan.required'         => 'Vui lòng nhập tên người nhận.',
            'so_dien_thoai.required'          => 'Vui lòng nhập số điện thoại.',
            'so_dien_thoai.size'              => 'Số điện thoại phải đúng 10 chữ số.',
            'so_dien_thoai.regex'             => 'Số điện thoại chỉ được chứa chữ số.',
            'quan_huyen.required'             => 'Vui lòng chọn quận/huyện.',
            'xa_phuong.required'              => 'Vui lòng chọn xã/phường.',
            'dia_chi_chi_tiet.required'       => 'Vui lòng nhập địa chỉ chi tiết.',
            'phuong_thuc_thanh_toan.required' => 'Vui lòng chọn phương thức thanh toán.',
        ]);

        // Determine which checkout session is present
        $isMuaNgay   = session()->has('mua_ngay');
        $isCheckout  = session()->has('checkout_items');

        if ($isMuaNgay) {
            $gioHang = session('mua_ngay', []);
        } elseif ($isCheckout) {
            $gioHang = session('checkout_items', []);
        } else {
            // Do not allow falling back to full session('gio_hang') per new rules
            return redirect()->route('customer.gio-hang')
                ->with('error', 'Vui lòng chọn sản phẩm để thanh toán.');
        }

        if (empty($gioHang)) {
            return redirect()->route('customer.gio-hang')
                ->with('error', 'Giỏ hàng trống!');
        }

        $user       = Auth::user()->load('khachHang');
        $khachHang  = $user->khachHang;
        $diemSuDung = session('diem_su_dung', 0);

        $tongTienGoc = collect($gioHang)->sum(fn($i) => $i['gia_ban'] * $i['so_luong']);
        $giamGia     = min($diemSuDung * self::DIEM_QUY_DOI, $tongTienGoc);
        $tongTien    = max(0, $tongTienGoc - $giamGia);

        $diaChiGiao = $request->dia_chi_chi_tiet
            . ', ' . $request->xa_phuong
            . ', ' . $request->quan_huyen
            . ', Hà Nội';

        $trangThaiTT = $request->phuong_thuc_thanh_toan === 'NGAN_HANG'
            ? 'DA_THANH_TOAN'
            : 'CHUA_THANH_TOAN';

        try {
            DB::transaction(function () use (
                $request,
                $gioHang,
                $khachHang,
                $tongTien,
                $diemSuDung,
                $trangThaiTT,
                $diaChiGiao
            ) {
                $yeuCauTheoSanPham = collect($gioHang)
                    ->groupBy('ma_san_pham')
                    ->map(fn($items) => $items->sum('so_luong'));

                foreach ($yeuCauTheoSanPham as $maSanPham => $soLuongYeuCau) {
                    $tongTonSanPham = ChiTietNhap::where('ma_san_pham', $maSanPham)
                        ->where('so_luong_con_lai', '>', 0)
                        ->whereHas('phieuNhap', fn($q) => $q->where('trang_thai', 'CONFIRMED'))
                        ->sum('so_luong_con_lai');

                    if ($tongTonSanPham < $soLuongYeuCau) {
                        $tenSanPham = collect($gioHang)
                            ->firstWhere('ma_san_pham', $maSanPham)['ten_san_pham'] ?? 'Sản phẩm';
                        throw new \Exception("Sản phẩm \"{$tenSanPham}\" không đủ hàng theo FIFO. Vui lòng cập nhật giỏ hàng.");
                    }
                }

                $hoaDon = HoaDon::create([
                    'ma_khach_hang'          => $khachHang->ma_khach_hang,
                    'trang_thai'             => 'PENDING',
                    'trang_thai_thanh_toan'  => $trangThaiTT,
                    'phuong_thuc_thanh_toan' => $request->phuong_thuc_thanh_toan,
                    'dia_chi_giao'           => $diaChiGiao,
                    'so_dien_thoai'          => $request->so_dien_thoai,
                    'tong_tien'              => $tongTien,
                    'ngay_dat'               => now(),
                ]);

                foreach ($gioHang as $item) {
                    ChiTietHoaDon::create([
                        'ma_hoa_don'        => $hoaDon->ma_hoa_don,
                        'ma_san_pham'       => $item['ma_san_pham'],
                        'so_luong'          => $item['so_luong'],
                        'gia_ban_snapshot'  => $item['gia_ban'],
                        'gia_nhap_snapshot' => $item['gia_nhap'],
                    ]);
                }

                foreach ($yeuCauTheoSanPham as $maSanPham => $soLuongYeuCau) {
                    $soLuongCanTru = $soLuongYeuCau;
                    $loNhapTheoFIFO = ChiTietNhap::with('phieuNhap')
                        ->where('ma_san_pham', $maSanPham)
                        ->where('so_luong_con_lai', '>', 0)
                        ->whereHas('phieuNhap', fn($q) => $q->where('trang_thai', 'CONFIRMED'))
                        ->get()
                        ->sortBy(function ($lot) {
                            return [
                                $lot->phieuNhap->ngay_nhap ?? now(),
                                $lot->ma_chi_tiet_nhap,
                            ];
                        });

                    foreach ($loNhapTheoFIFO as $lot) {
                        if ($soLuongCanTru <= 0) {
                            break;
                        }

                        $tru = min($lot->so_luong_con_lai, $soLuongCanTru);
                        $lot->decrement('so_luong_con_lai', $tru);
                        $soLuongCanTru -= $tru;
                    }
                }

                foreach ($gioHang as $item) {
                    SanPham::where('ma_san_pham', $item['ma_san_pham'])
                        ->decrement('so_luong', $item['so_luong']);
                }

                if ($diemSuDung > 0 && $khachHang) {
                    $khachHang->decrement('diem_tich_luy', $diemSuDung);
                }

                $diemMoi = (int) floor($tongTien / self::TICH_DIEM);

                if ($diemMoi > 0 && $khachHang) {
                    $khachHang->increment('diem_tich_luy', $diemMoi);
                }
            });
        } catch (\Exception $e) {
            return redirect()->route('customer.thanh-toan')
                ->with('error', $e->getMessage());
        }

        // Clear only relevant sessions after successful order
        if ($isMuaNgay) {
            session()->forget(['mua_ngay', 'diem_su_dung']);
        } elseif ($isCheckout) {
            // remove only paid items from the main cart
            $mainCart = session('gio_hang', []);
            $paidKeys = array_keys(session('checkout_items', []));
            foreach ($paidKeys as $k) {
                if (isset($mainCart[$k])) {
                    unset($mainCart[$k]);
                }
            }
            session(['gio_hang' => $mainCart]);
            session()->forget(['checkout_items', 'diem_su_dung']);
        }

        $ttLabel = $trangThaiTT === 'DA_THANH_TOAN'
            ? 'Chuyển khoản – đơn hàng sẽ được xử lý sau khi xác nhận thanh toán.'
            : 'Thanh toán khi nhận hàng (COD).';

        return redirect()->route('customer.dashboard')
            ->with('success', "🎉 Đặt hàng thành công! Phương thức: {$ttLabel} Chúng tôi sẽ liên hệ sớm nhất.");
    }
}