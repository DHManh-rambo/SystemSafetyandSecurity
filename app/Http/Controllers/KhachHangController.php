<?php

namespace App\Http\Controllers;

use App\Models\KhachHang;
use App\Models\NguoiDung;
use App\Models\HoaDon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KhachHangController extends Controller
{
    
    public function index(Request $request)
    {
        
        $sort = $request->get('sort', 'desc');
        
      
        if (!in_array($sort, ['asc', 'desc'])) {
            $sort = 'desc';
        }
        
        
        $khachHangs = KhachHang::orderBy('diem_tich_luy', $sort)->get();
        
        return view('KhachHang', compact('khachHangs', 'sort'));
    }

    
    public function update(Request $request, $id)
    {
        
        $khachHang = KhachHang::findOrFail($id);
        
        
        $request->validate([
            'ten_khach_hang' => 'required|string|max:100',
            'so_dien_thoai'  => 'required|string|size:10|regex:/^\d{10}$/',
            'email'          => 'required|email|max:100',
            'dia_chi'        => 'nullable|string',
            'diem_tich_luy'  => 'nullable|integer|min:0',
        ]);
        
        
        $khachHang->update([
            'ten_khach_hang' => $request->ten_khach_hang,
            'so_dien_thoai'  => $request->so_dien_thoai,
            'email'          => $request->email,
            'dia_chi'        => $request->dia_chi,
            'diem_tich_luy'  => $request->diem_tich_luy ?? 0,
        ]);
        
        return redirect()->route('khach-hang.index', ['sort' => $request->sort])
                         ->with('success', 'Cập nhật thông tin khách hàng thành công!');
    }

   
    public function destroy($id, Request $request)
{
    $soHoaDon = HoaDon::where('ma_khach_hang', $id)->count();
    if ($soHoaDon > 0) {
        return redirect()->route('khach-hang.index', ['sort' => $request->sort])
                         ->with('error', 'Không thể xóa! Khách hàng này có ' . $soHoaDon . ' hóa đơn liên quan, vui lòng xóa hóa đơn trước.');
    }

    DB::beginTransaction();
    try {
        $khachHang = KhachHang::find($id);
        if ($khachHang) {
            $khachHang->delete();
        }
        
    
        NguoiDung::destroy($id);
        
        DB::commit();
        return redirect()->route('khach-hang.index', ['sort' => $request->sort])
                         ->with('success', 'Xóa khách hàng thành công!');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->route('khach-hang.index', ['sort' => $request->sort])
                         ->with('error', 'Lỗi khi xóa: ' . $e->getMessage());
    }
}
}