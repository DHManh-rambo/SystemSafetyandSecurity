<?php

namespace App\Http\Controllers\Shipper;

use App\Http\Controllers\Controller;
use App\Models\NhanVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ShipperProfileController extends Controller
{
    public function edit()
    {
        $user     = Auth::user();
        $shipper  = NhanVien::findOrFail($user->ma_nguoi_dung);

        return view('Shipper.ShipperProfile', compact('shipper'));
    }

    
    public function update(Request $request)
    {
        $user    = Auth::user();
        $shipper = NhanVien::findOrFail($user->ma_nguoi_dung);

        $request->validate([
            'ten_nhan_vien' => 'required|string|max:100',
            'so_dien_thoai' => 'required|string|size:10|regex:/^\d{10}$/',
            'email'         => 'required|email|max:100',
        ]);

        $shipper->update([
            'ten_nhan_vien' => $request->ten_nhan_vien,
            'so_dien_thoai' => $request->so_dien_thoai,
            'email'         => $request->email,
        ]);

        return redirect()
            ->route('shipper.profile.edit')
            ->with('success_info', 'Cập nhật thông tin thành công!');
    }

    
    public function updatePassword(Request $request)
    {
        $request->validate([
            'mat_khau_cu'             => 'required|string',
            'mat_khau_moi'            => 'required|digits_between:6,255|confirmed',
            
        ], [
            'mat_khau_cu.required'              => 'Vui lòng nhập mật khẩu hiện tại.',
            'mat_khau_moi.required'             => 'Vui lòng nhập mật khẩu mới.',
            'mat_khau_moi.min'                  => 'Mật khẩu mới phải có ít nhất 6 chữ số.',
            'mat_khau_moi.digits_between'       => 'Mật khẩu mới phải có ít nhất 6 chữ số.',
            'mat_khau_moi.confirmed'            => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user = Auth::user();

        
        if (! Hash::check($request->mat_khau_cu, $user->getAuthPassword())) {
            return back()
                ->withErrors(['mat_khau_cu' => 'Mật khẩu hiện tại không đúng.'])
                ->withInput()
                ->with('tab', 'password'); 
        }

        $user->mat_khau = $request->mat_khau_moi;
        $user->save();

        return redirect()
            ->route('shipper.profile.edit')
            ->with('success_password', 'Đổi mật khẩu thành công!');
    }
}