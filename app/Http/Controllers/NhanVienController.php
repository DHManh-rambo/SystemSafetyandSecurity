<?php

namespace App\Http\Controllers;

use App\Models\NhanVien;
use App\Models\NguoiDung;
use App\Models\HoaDon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NhanVienController extends Controller
{
    public function index(Request $request)
    {
        $query = NhanVien::query();

        if ($request->filled('chuc_vu')) {
            $query->where('chuc_vu', $request->chuc_vu);
        }

        $sort = $request->get('sort', '');
        if ($sort === 'luong_desc') {
            $query->orderBy('luong', 'desc');
        } elseif ($sort === 'luong_asc') {
            $query->orderBy('luong', 'asc');
        }

        $nhanViens = $query->paginate(10)->appends($request->query());

        $chucVus = [
            'CSKH'     => 'CSKH',
            'VAN_HANH' => 'Vận hành',
            'THIET_KE' => 'Thiết kế',
            'ONLINE'   => 'Online',
            'SHIPPER'  => 'Shipper',
            'KHAC'     => 'Khác',
        ];

        $shipperDebts = HoaDon::query()
            ->select('ma_nhan_vien_giao', DB::raw('SUM(tong_tien) as tong_can_tra'))
            ->where('trang_thai', 'DELIVERED')
            ->where('trang_thai_thanh_toan', 'DA_THANH_TOAN')
            ->where('phuong_thuc_thanh_toan', 'COD')
            ->groupBy('ma_nhan_vien_giao')
            ->pluck('tong_can_tra', 'ma_nhan_vien_giao');

        return view('NhanVien', compact('nhanViens', 'chucVus', 'shipperDebts'));
    }

    public function update(Request $request, $ma_nhan_vien)
    {
        $request->validate([
            'ten_nhan_vien' => 'required|string|max:100',
            'email'         => 'nullable|email|max:100',
            'so_dien_thoai' => 'nullable|string|max:15',
            'chuc_vu'       => 'required|in:CSKH,VAN_HANH,THIET_KE,ONLINE,SHIPPER,KHAC',
            'cong_viec'     => 'nullable|string',
            'luong'         => 'nullable|numeric|min:0',
        ]);

        $nhanVien = NhanVien::findOrFail($ma_nhan_vien);
        $nhanVien->update($request->only([
            'ten_nhan_vien', 'email', 'so_dien_thoai', 'chuc_vu', 'cong_viec', 'luong'
        ]));

        return redirect()->route('nhan-vien.index')
                         ->with('success', 'Cập nhật nhân viên thành công!');
    }

    public function destroy($ma_nhan_vien)
    {
        $nhanVien    = NhanVien::findOrFail($ma_nhan_vien);
        $maNguoiDung = $nhanVien->ma_nhan_vien;

        DB::transaction(function () use ($nhanVien, $maNguoiDung) {
            $nhanVien->delete();
            NguoiDung::where('ma_nguoi_dung', $maNguoiDung)->delete();
        });

        return redirect()->route('nhan-vien.index')
                         ->with('success', 'Xóa nhân viên thành công!');
    }

    public function payback($ma_nhan_vien)
    {
        $nhanVien = NhanVien::findOrFail($ma_nhan_vien);

        if ($nhanVien->chuc_vu !== 'SHIPPER') {
            return response()->json([
                'success' => false,
                'message' => 'Nhân viên này không phải Shipper.',
            ], 422);
        }

        $soTienDaTra = HoaDon::where('ma_nhan_vien_giao', $ma_nhan_vien)
            ->where('trang_thai', 'DELIVERED')
            ->where('trang_thai_thanh_toan', 'DA_THANH_TOAN')
            ->where('phuong_thuc_thanh_toan', 'COD')
            ->sum('tong_tien');

        if ($soTienDaTra == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Shipper ' . $nhanVien->ten_nhan_vien . ' không có khoản nào cần nộp.',
            ], 422);
        }

        $updated = HoaDon::where('ma_nhan_vien_giao', $ma_nhan_vien)
            ->where('trang_thai', 'DELIVERED')
            ->where('trang_thai_thanh_toan', 'DA_THANH_TOAN')
            ->where('phuong_thuc_thanh_toan', 'COD')
            ->update(['trang_thai_thanh_toan' => 'DA_NOP']);

        return response()->json([
            'success'      => true,
            'so_tien'      => $soTienDaTra,
            'so_don'       => $updated,
            'message'      => 'Đã xác nhận ' . $nhanVien->ten_nhan_vien . ' nộp '
                              . number_format($soTienDaTra, 0, ',', '.') . ' đ ('
                              . $updated . ' đơn).',
        ]);
    }
}