<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerProfileController extends Controller
{
    public function edit()
    {
        $user      = Auth::user();
        $khachHang = KhachHang::findOrFail($user->ma_nguoi_dung);

        return view('customer.CustomerProfile', compact('user', 'khachHang'));
    }

    public function update(Request $request)
    {
        $user      = Auth::user();
        $khachHang = KhachHang::findOrFail($user->ma_nguoi_dung);

        $request->validate([
            'ten_khach_hang'   => 'required|string|max:100',
            'so_dien_thoai'    => 'required|string|size:10|regex:/^\d{10}$/',
            'email'            => 'required|email|max:100',
            'quan_huyen'       => 'required|string|max:100',
            'xa_phuong'        => 'required|string|max:100',
            'dia_chi_chi_tiet' => 'required|string|max:255',
        ], [
            'ten_khach_hang.required'   => 'Vui lòng nhập họ tên.',
            'so_dien_thoai.required'    => 'Vui lòng nhập số điện thoại.',
            'so_dien_thoai.size'        => 'Số điện thoại phải đúng 10 chữ số.',
            'so_dien_thoai.regex'       => 'Số điện thoại chỉ được chứa chữ số.',
            'email.required'            => 'Vui lòng nhập email.',
            'email.email'               => 'Email không đúng định dạng.',
            'quan_huyen.required'       => 'Vui lòng chọn quận/huyện.',
            'xa_phuong.required'        => 'Vui lòng chọn xã/phường.',
            'dia_chi_chi_tiet.required' => 'Vui lòng nhập địa chỉ chi tiết.',
        ]);

        $diaChiDayDu = $request->dia_chi_chi_tiet
            . ', ' . $request->xa_phuong
            . ', ' . $request->quan_huyen
            . ', Hà Nội';

        $khachHang->update([
            'ten_khach_hang' => $request->ten_khach_hang,
            'so_dien_thoai'  => $request->so_dien_thoai,
            'email'          => $request->email,
            'dia_chi'        => $diaChiDayDu,
        ]);

        return redirect()
            ->route('customer.profile.edit')
            ->with('success_info', 'Cập nhật thông tin thành công!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'mat_khau_cu'  => 'required|string',
            'mat_khau_moi' => 'required|min:6|confirmed',
        ], [
            'mat_khau_cu.required'   => 'Vui lòng nhập mật khẩu hiện tại.',
            'mat_khau_moi.required'  => 'Vui lòng nhập mật khẩu mới.',
            'mat_khau_moi.min'       => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'mat_khau_moi.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user = Auth::user();

        $matKhauDung = Hash::check($request->mat_khau_cu, $user->mat_khau)
                    || $request->mat_khau_cu === $user->mat_khau;

        if (! $matKhauDung) {
            return back()
                ->withErrors(['mat_khau_cu' => 'Mật khẩu hiện tại không đúng.'])
                ->withInput()
                ->with('tab', 'password');
        }

        $user->mat_khau = $request->mat_khau_moi;
        $user->save();

        return redirect()
            ->route('customer.profile.edit')
            ->with('success_password', 'Đổi mật khẩu thành công!');
    }
}