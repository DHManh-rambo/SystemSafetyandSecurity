<?php

namespace App\Http\Controllers;

use App\Models\PhieuNhap;
use App\Models\ChiTietNhap;
use App\Models\NhanVien;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhieuNhapController extends Controller
{
    
    public function index(Request $request)
    {
        $query = PhieuNhap::with(['nhanVien', 'chiTietNhaps.sanPham']);

        
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

      
        if ($request->filled('tu_ngay')) {
            $query->whereDate('ngay_nhap', '>=', $request->tu_ngay);
        }
        if ($request->filled('den_ngay')) {
            $query->whereDate('ngay_nhap', '<=', $request->den_ngay);
        }

        if ($request->filled('tim_ncc')) {
            $query->where('ten_nha_cung_cap', 'LIKE', '%' . $request->tim_ncc . '%');
        }

        $danhSachPhieuNhap = $query->orderBy('ma_phieu_nhap', 'desc')->get();

        
        $danhSachNhanVien = NhanVien::where('chuc_vu', 'VAN_HANH')->get();

        
        $tonKhoTheoLo = DB::table('chi_tiet_nhap as ctn')
            ->join('phieu_nhap as pn', 'ctn.ma_phieu_nhap', '=', 'pn.ma_phieu_nhap')
            ->where('pn.trang_thai', 'CONFIRMED')
            ->groupBy('ctn.ma_san_pham')
            ->select('ctn.ma_san_pham', DB::raw('SUM(ctn.so_luong_con_lai) as tong_con_lai'))
            ->pluck('tong_con_lai', 'ma_san_pham');

        $danhSachSanPham = SanPham::orderBy('ten_san_pham')
                                  ->get()
                                  ->map(function ($sp) use ($tonKhoTheoLo) {
                                      $sp->ton_kho_lo = (int) ($tonKhoTheoLo[$sp->ma_san_pham] ?? 0);
                                      return $sp;
                                  });

        return view('PhieuNhap', compact(
            'danhSachPhieuNhap',
            'danhSachNhanVien',
            'danhSachSanPham'
        ));
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'ngay_nhap'         => 'required|date',
            'ma_nhan_vien'      => 'required|exists:nhan_vien,ma_nhan_vien',
            'ten_nha_cung_cap'  => 'nullable|string|max:100',
            'so_dien_thoai_ncc' => 'nullable|digits:10',
            'email_ncc'         => 'nullable|email|max:100',
            'dia_chi_ncc'       => 'nullable|string',

            'san_pham'          => 'required|array|min:1',
            'san_pham.*'        => 'required|exists:san_pham,ma_san_pham|distinct',
            'so_luong'          => 'required|array|min:1',
            'so_luong.*'        => 'required|integer|min:1',
            'gia_nhap'          => 'required|array|min:1',
            'gia_nhap.*'        => 'required|numeric|min:0',
           
            'gia_ban'           => 'nullable|array',
            'gia_ban.*'         => 'nullable|numeric|min:0',
        ], [
            'ngay_nhap.required'        => 'Vui lòng chọn ngày nhập.',
            'ma_nhan_vien.required'     => 'Vui lòng chọn nhân viên.',
            'so_dien_thoai_ncc.digits'  => 'Số điện thoại NCC phải đủ 10 chữ số.',
            'email_ncc.email'           => 'Email NCC không đúng định dạng (phải có @).',
            'san_pham.required'         => 'Vui lòng thêm ít nhất 1 sản phẩm vào phiếu nhập.',
            'san_pham.*.distinct'       => 'Không được chọn cùng 1 sản phẩm 2 lần trong cùng phiếu.',
            'san_pham.*.exists'         => 'Sản phẩm không hợp lệ.',
            'so_luong.*.min'            => 'Số lượng phải lớn hơn 0.',
            'so_luong.*.integer'        => 'Số lượng phải là số nguyên.',
            'gia_nhap.*.min'            => 'Giá nhập không được âm.',
            'gia_nhap.*.numeric'        => 'Giá nhập phải là số.',
            'gia_ban.*.numeric'         => 'Giá bán phải là số.',
            'gia_ban.*.min'             => 'Giá bán không được âm.',
        ]);

        DB::transaction(function () use ($request) {

            
            $phieuNhap = PhieuNhap::create([
                'ngay_nhap'         => $request->ngay_nhap,
                'ma_nhan_vien'      => $request->ma_nhan_vien,
                'ten_nha_cung_cap'  => $request->ten_nha_cung_cap,
                'so_dien_thoai_ncc' => $request->so_dien_thoai_ncc,
                'email_ncc'         => $request->email_ncc,
                'dia_chi_ncc'       => $request->dia_chi_ncc,
                'trang_thai'        => 'DRAFT',
            ]);

            
            foreach ($request->san_pham as $index => $maSanPham) {

                $giaNhap = $request->gia_nhap[$index];

                
                $giaBanNhap = $request->gia_ban[$index] ?? null;
                $giaBan = ($giaBanNhap !== null && $giaBanNhap !== '')
                    ? (float) $giaBanNhap
                    : (float) $giaNhap * 1.5;

                ChiTietNhap::create([
                    'ma_phieu_nhap'   => $phieuNhap->ma_phieu_nhap,
                    'ma_san_pham'     => $maSanPham,
                    'so_luong'        => $request->so_luong[$index],
                    'gia_nhap'        => $giaNhap,
                    'gia_ban'         => $giaBan,
                   
                    'so_luong_con_lai' => $request->so_luong[$index],
                ]);
            }
        });

        return redirect()->route('phieu-nhap.index')
                         ->with('success', 'Tạo phiếu nhập thành công! Trạng thái: Đang tạo (Draft).');
    }

    
    public function editData($id)
    {
        $phieuNhap = PhieuNhap::with('chiTietNhaps.sanPham')->findOrFail($id);

        // For each chi tiết, compute the "giá bán áp dụng" as the MAX(gia_ban)
        // among confirmed, in-stock lots of the same product. If none found,
        // the frontend will fall back to the phiếu nhập's gia_ban.
        // foreach ($phieuNhap->chiTietNhaps as $ct) {
        //     $giaApDung = DB::table('chi_tiet_nhap as ctn')
        //         ->join('phieu_nhap as pn', 'ctn.ma_phieu_nhap', '=', 'pn.ma_phieu_nhap')
        //         ->where('ctn.ma_san_pham', $ct->ma_san_pham)
        //         ->where('ctn.so_luong_con_lai', '>', 0)
        //         ->where('pn.trang_thai', 'CONFIRMED')
        //         ->max('ctn.gia_ban');

        //     $ct->gia_ban_ap_dung = $giaApDung ?: null;
        // }

        return response()->json($phieuNhap);
    }

    
    public function update(Request $request, $id)
    {
        $phieuNhap = PhieuNhap::findOrFail($id);

        
        if ($phieuNhap->trang_thai !== 'DRAFT') {
            return redirect()->route('phieu-nhap.index')
                             ->with('error', 'Không thể sửa phiếu nhập đã xác nhận!');
        }

        $request->validate([
            'ngay_nhap'         => 'required|date',
            'ma_nhan_vien'      => 'required|exists:nhan_vien,ma_nhan_vien',
            'ten_nha_cung_cap'  => 'nullable|string|max:100',
            'so_dien_thoai_ncc' => 'nullable|digits:10',
            'email_ncc'         => 'nullable|email|max:100',
            'dia_chi_ncc'       => 'nullable|string',
            'san_pham'          => 'required|array|min:1',
            'san_pham.*'        => 'required|exists:san_pham,ma_san_pham|distinct',
            'so_luong'          => 'required|array|min:1',
            'so_luong.*'        => 'required|integer|min:1',
            'gia_nhap'          => 'required|array|min:1',
            'gia_nhap.*'        => 'required|numeric|min:0',
            'gia_ban'           => 'nullable|array',
            'gia_ban.*'         => 'nullable|numeric|min:0',
        ], [
            'so_dien_thoai_ncc.digits' => 'Số điện thoại phải đủ 10 chữ số.',
            'email_ncc.email'          => 'Email không đúng định dạng (phải có @).',
            'san_pham.required'        => 'Vui lòng thêm ít nhất 1 sản phẩm.',
            'san_pham.*.distinct'      => 'Không được chọn cùng 1 sản phẩm 2 lần.',
            'so_luong.*.min'           => 'Số lượng phải lớn hơn 0.',
            'gia_ban.*.numeric'        => 'Giá bán phải là số.',
            'gia_ban.*.min'            => 'Giá bán không được âm.',
        ]);

        DB::transaction(function () use ($request, $phieuNhap) {

           
            $phieuNhap->update([
                'ngay_nhap'         => $request->ngay_nhap,
                'ma_nhan_vien'      => $request->ma_nhan_vien,
                'ten_nha_cung_cap'  => $request->ten_nha_cung_cap,
                'so_dien_thoai_ncc' => $request->so_dien_thoai_ncc,
                'email_ncc'         => $request->email_ncc,
                'dia_chi_ncc'       => $request->dia_chi_ncc,
            ]);

            
            $phieuNhap->chiTietNhaps()->delete();

            foreach ($request->san_pham as $index => $maSanPham) {

                $giaNhap = $request->gia_nhap[$index];

               
                $giaBanNhap = $request->gia_ban[$index] ?? null;
                $giaBan = ($giaBanNhap !== null && $giaBanNhap !== '')
                    ? (float) $giaBanNhap
                    : (float) $giaNhap * 1.5;

                ChiTietNhap::create([
                    'ma_phieu_nhap'    => $phieuNhap->ma_phieu_nhap,
                    'ma_san_pham'      => $maSanPham,
                    'so_luong'         => $request->so_luong[$index],
                    'gia_nhap'         => $giaNhap,
                    'gia_ban'          => $giaBan,
                    'so_luong_con_lai' => $request->so_luong[$index],
                ]);
            }
        });

        return redirect()->route('phieu-nhap.index')
                         ->with('success', 'Cập nhật phiếu nhập thành công!');
    }

    
    public function destroy($id)
    {
        $phieuNhap = PhieuNhap::findOrFail($id);

        if ($phieuNhap->trang_thai !== 'DRAFT') {
            return redirect()->route('phieu-nhap.index')
                             ->with('error', 'Không thể xóa phiếu nhập đã xác nhận!');
        }

        
        $phieuNhap->delete();

        return redirect()->route('phieu-nhap.index')
                         ->with('success', 'Đã xóa phiếu nhập!');
    }

    
    public function confirm($id)
    {
        $phieuNhap = PhieuNhap::with('chiTietNhaps')->findOrFail($id);

        if ($phieuNhap->trang_thai !== 'DRAFT') {
            return redirect()->route('phieu-nhap.index')
                             ->with('error', 'Phiếu nhập này đã được xác nhận rồi!');
        }

        DB::transaction(function () use ($phieuNhap) {

            foreach ($phieuNhap->chiTietNhaps as $chiTiet) {
                
                $sanPham = SanPham::findOrFail($chiTiet->ma_san_pham);
                $sanPham->so_luong += $chiTiet->so_luong;
                $sanPham->save();
            }

            $phieuNhap->trang_thai = 'CONFIRMED';
            $phieuNhap->save();
        });

        return redirect()->route('phieu-nhap.index')
                         ->with('success', 'Xác nhận thành công! Số lượng kho đã được cập nhật.');
    }
}