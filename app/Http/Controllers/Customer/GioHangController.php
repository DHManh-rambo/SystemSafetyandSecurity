<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ChiTietNhap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GioHangController extends Controller
{
    const DIEM_QUY_DOI = 1000;

    public function index()
    {
        $user       = auth()->check() ? Auth::user()->load('khachHang') : null;
        $gioHang    = session('gio_hang', []);
        $diemSuDung = session('diem_su_dung', 0);

        return view('customer.GioHang', compact('user', 'gioHang', 'diemSuDung'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'items'                    => 'required|array|min:1',
            'items.*.ma_chi_tiet_nhap' => 'required|integer|exists:chi_tiet_nhap,ma_chi_tiet_nhap',
            'items.*.so_luong'         => 'required|integer|min:1',
        ]);

        $gioHang = session('gio_hang', []);

        foreach ($request->items as $item) {
            $chiTiet = ChiTietNhap::with('sanPham')
                ->where('ma_chi_tiet_nhap', $item['ma_chi_tiet_nhap'])
                ->where('so_luong_con_lai', '>', 0)
                ->whereHas('phieuNhap', fn($q) => $q->where('trang_thai', 'CONFIRMED'))
                ->first();

            if (!$chiTiet || !$chiTiet->sanPham) {
                continue;
            }

            $key = 'lo_' . $chiTiet->ma_chi_tiet_nhap;

            $soLuongMoi = ($gioHang[$key]['so_luong'] ?? 0) + (int) $item['so_luong'];
            $soLuongMoi = min($soLuongMoi, $chiTiet->so_luong_con_lai);

            $gioHang[$key] = [
                'ma_chi_tiet_nhap' => $chiTiet->ma_chi_tiet_nhap,
                'ma_san_pham'      => $chiTiet->ma_san_pham,
                'ten_san_pham'     => $chiTiet->sanPham->ten_san_pham,
                'hinh_anh'         => $chiTiet->sanPham->hinh_anh,
                'gia_ban'          => (float) $chiTiet->gia_ban,
                'gia_nhap'         => (float) $chiTiet->gia_nhap,
                'so_luong'         => $soLuongMoi,
                'so_luong_con_lai' => $chiTiet->so_luong_con_lai,
            ];
        }

        session(['gio_hang' => $gioHang]);
        session()->forget('diem_su_dung');

        return response()->json([
            'success'     => true,
            'message'     => 'Đã thêm vào giỏ hàng!',
            'so_san_pham' => count($gioHang),
        ]);
    }

    public function buyNow(Request $request)
    {
        $request->validate([
            'items'                    => 'required|array|min:1',
            'items.*.ma_chi_tiet_nhap' => 'required|integer|exists:chi_tiet_nhap,ma_chi_tiet_nhap',
            'items.*.so_luong'         => 'required|integer|min:1',
        ]);

        $muaNgay = [];

        foreach ($request->items as $item) {
            $chiTiet = ChiTietNhap::with('sanPham')
                ->where('ma_chi_tiet_nhap', $item['ma_chi_tiet_nhap'])
                ->where('so_luong_con_lai', '>', 0)
                ->whereHas('phieuNhap', fn($q) => $q->where('trang_thai', 'CONFIRMED'))
                ->first();

            if (!$chiTiet || !$chiTiet->sanPham) {
                continue;
            }

            $key = 'lo_' . $chiTiet->ma_chi_tiet_nhap;

            $muaNgay[$key] = [
                'ma_chi_tiet_nhap' => $chiTiet->ma_chi_tiet_nhap,
                'ma_san_pham'      => $chiTiet->ma_san_pham,
                'ten_san_pham'     => $chiTiet->sanPham->ten_san_pham,
                'hinh_anh'         => $chiTiet->sanPham->hinh_anh,
                'gia_ban' => (float) ($chiTiet->sanPham->gia_ban_hien_tai ?? $chiTiet->gia_ban),
                'gia_nhap'         => (float) $chiTiet->gia_nhap,
                'so_luong'         => min((int) $item['so_luong'], $chiTiet->so_luong_con_lai),
                'so_luong_con_lai' => $chiTiet->so_luong_con_lai,
            ];
        }

        session(['mua_ngay' => $muaNgay]);
        session()->forget('diem_su_dung');

        return response()->json([
            'success' => true,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'key'      => 'required|string',
            'so_luong' => 'required|integer|min:0',
        ]);

        $gioHang = session('gio_hang', []);

        if (!isset($gioHang[$request->key])) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm.'
            ], 404);
        }

        if ((int) $request->so_luong === 0) {
            unset($gioHang[$request->key]);
        } else {
            $max = $gioHang[$request->key]['so_luong_con_lai'];
            $gioHang[$request->key]['so_luong'] = min((int) $request->so_luong, $max);
        }

        session(['gio_hang' => $gioHang]);
        session()->forget('diem_su_dung');

        $tongTien = collect($gioHang)->sum(fn($i) => $i['gia_ban'] * $i['so_luong']);

        return response()->json([
            'success'     => true,
            'so_san_pham' => count($gioHang),
            'tong_tien'   => $tongTien,
        ]);
    }

    public function remove(Request $request)
    {
        $gioHang = session('gio_hang', []);

        unset($gioHang[$request->key]);

        session(['gio_hang' => $gioHang]);
        session()->forget('diem_su_dung');

        $tongTien = collect($gioHang)->sum(fn($i) => $i['gia_ban'] * $i['so_luong']);

        return response()->json([
            'success'     => true,
            'so_san_pham' => count($gioHang),
            'tong_tien'   => $tongTien,
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'required|string'
        ]);

        $gioHang = session('gio_hang', []);

        $checkout = [];
        foreach ($request->selected_items as $key) {
            if (isset($gioHang[$key])) {
                $checkout[$key] = $gioHang[$key];
            }
        }

        if (empty($checkout)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có sản phẩm hợp lệ được chọn.'
            ], 400);
        }

        // Save selected items for checkout
        session(['checkout_items' => $checkout]);

        return response()->json(['success' => true]);
    }

    public function applyPoints(Request $request)
    {
        $request->validate([
            'diem' => 'required|integer|min:0'
        ]);

        $khachHang = Auth::user()->khachHang;

        if (!$khachHang) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin khách hàng.'
            ], 404);
        }

        $diem = min((int) $request->diem, $khachHang->diem_tich_luy);

        session(['diem_su_dung' => $diem]);

        return response()->json([
            'success'      => true,
            'diem_su_dung' => $diem,
            'giam_gia'     => $diem * self::DIEM_QUY_DOI,
        ]);
    }
}