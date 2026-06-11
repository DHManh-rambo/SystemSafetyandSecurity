<?php

namespace App\Http\Controllers;

use App\Models\SanPham;
use App\Models\ChiTietNhap;
use App\Models\BaoCaoHangHong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SanPhamController extends Controller
{
    private $danhSachLoai = [
        'HOA_TUOI'         => 'Hoa Tươi',
        'HOA_GIA'          => 'Hoa Giả',
        'SAN_PHAM_PREMIUM' => 'Sản Phẩm Premium',
        'CHAU_HOA_GIA'     => 'Chậu Hoa Giả',
        'CHAU_HOA_TUOI'    => 'Chậu Hoa Tươi',
        'CAY_CANH'         => 'Cây Cảnh',
        'HOA_SAP'          => 'Hoa Sáp',
        'HOA_GIAY_NHUN'    => 'Hoa Giấy Nhún',
        'TERRARIUM'        => 'Terrarium',
        'PHU_KIEN'         => 'Phụ Kiện',
        'QUA_TANG'         => 'Quà Tặng',
    ];

   
    // DANH SÁCH SẢN PHẨM

    public function index(Request $request)
    {
        $query = SanPham::query();

        if ($request->filled('loai_san_pham')) {
            $query->where('loai_san_pham', $request->loai_san_pham);
        }

        $danhSachSanPham = $query
            ->with(['chiTietNhaps' => function ($q) {
                $q->where('so_luong_con_lai', '>', 0)
                  ->whereHas('phieuNhap', fn($q2) => $q2->where('trang_thai', 'CONFIRMED'))
                  ->orderBy('gia_ban', 'asc');
            }])
            ->orderBy('ma_san_pham', 'desc')
            ->get();

        $danhSachLoai = $this->danhSachLoai;

        return view('SanPham', compact('danhSachSanPham', 'danhSachLoai'));
    }

    
    // THÊM SẢN PHẨM
    

    public function store(Request $request)
    {
        $request->validate([
            'ten_san_pham'  => 'required|string|max:100',
            'loai_san_pham' => 'required|in:' . implode(',', array_keys($this->danhSachLoai)),
            'gia_ban_hien_tai' => 'nullable|numeric|min:0',
            'mo_ta'         => 'nullable|string',
            'hinh_anh'      => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ], [
            'ten_san_pham.required'  => 'Vui lòng nhập tên sản phẩm.',
            'loai_san_pham.required' => 'Vui lòng chọn loại sản phẩm.',
            'loai_san_pham.in'       => 'Loại sản phẩm không hợp lệ.',
            'hinh_anh.image'         => 'File tải lên phải là hình ảnh.',
            'hinh_anh.mimes'         => 'Chỉ chấp nhận JPG, PNG, GIF, WEBP.',
            'hinh_anh.max'           => 'Ảnh không được vượt quá 2MB.',
        ]);

        $duongDanAnh = null;
        if ($request->hasFile('hinh_anh')) {
            $file    = $request->file('hinh_anh');
            $tenFile = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('img'), $tenFile);
            $duongDanAnh = 'img/' . $tenFile;
        }

SanPham::create([
    'ten_san_pham'      => $request->ten_san_pham,
    'so_luong'          => 0,
    'loai_san_pham'     => $request->loai_san_pham,
    'gia_ban_hien_tai'  => $request->gia_ban_hien_tai,
    'mo_ta'             => $request->mo_ta,
    'hinh_anh'          => $duongDanAnh,
    'trang_thai'        => 'NGUNG_BAN',
]);

        return redirect()->route('san-pham.index')
            ->with('success', 'Thêm sản phẩm thành công! Trạng thái mặc định là Ngừng bán. Hãy nhập hàng rồi bật lại để bán.');
    }

    
    // LẤY DỮ LIỆU ĐỂ SỬA 
    

    
    public function editData($id)
{
    $sanPham = SanPham::with(['chiTietNhaps' => function ($q) {
        $q->where('so_luong_con_lai', '>', 0)
          ->whereHas('phieuNhap', fn($q2) => $q2->where('trang_thai', 'CONFIRMED'));
    }])->findOrFail($id);

    $sanPham->ton_kho_thuc_te = $sanPham->chiTietNhaps->sum('so_luong_con_lai');

    return response()->json($sanPham);
}

    
    // CẬP NHẬT SẢN PHẨM
    

    public function update(Request $request, $id)
    {
        $sanPham = SanPham::findOrFail($id);

        $request->validate([
            'ten_san_pham' => 'required|string|max:100',
            'mo_ta'        => 'nullable|string',
            'hinh_anh'     => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ], [
            'ten_san_pham.required' => 'Vui lòng nhập tên sản phẩm.',
            'hinh_anh.image'        => 'File tải lên phải là hình ảnh.',
            'hinh_anh.mimes'        => 'Chỉ chấp nhận JPG, PNG, GIF, WEBP.',
            'hinh_anh.max'          => 'Ảnh không được vượt quá 2MB.',
        ]);

        $duongDanAnh = $sanPham->hinh_anh;
        if ($request->hasFile('hinh_anh')) {
            if ($sanPham->hinh_anh && file_exists(public_path($sanPham->hinh_anh))) {
                unlink(public_path($sanPham->hinh_anh));
            }
            $file    = $request->file('hinh_anh');
            $tenFile = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('img'), $tenFile);
            $duongDanAnh = 'img/' . $tenFile;
        }

        $sanPham->update([
            'ten_san_pham' => $request->ten_san_pham,
            'gia_ban_hien_tai' => $request->gia_ban_hien_tai,
            'mo_ta'        => $request->mo_ta,
            'hinh_anh'     => $duongDanAnh,
        ]);

        return redirect()->route('san-pham.index')
            ->with('success', 'Cập nhật sản phẩm "' . $sanPham->ten_san_pham . '" thành công!');
    }

   
    // ẨN / HIỆN SẢN PHẨM
    

    public function toggleTrangThai($id)
    {
        $sanPham = SanPham::findOrFail($id);

        if ($sanPham->trang_thai === 'DANG_BAN') {
            $sanPham->trang_thai = 'NGUNG_BAN';
            $thongBao = 'Đã ẩn sản phẩm "' . $sanPham->ten_san_pham . '"! Khách hàng sẽ không thể mua.';
        } else {
            $sanPham->trang_thai = 'DANG_BAN';
            $thongBao = 'Đã hiện lại sản phẩm "' . $sanPham->ten_san_pham . '"! Khách hàng có thể mua.';
        }

        $sanPham->save();

        return redirect()->route('san-pham.index')->with('success', $thongBao);
    }

    

    public function loNhap($id)
    {
        SanPham::findOrFail($id); 

        $loNhaps = ChiTietNhap::with('phieuNhap')
            ->where('ma_san_pham', $id)
            ->where('so_luong_con_lai', '>', 0)
            ->whereHas('phieuNhap', fn($q) => $q->where('trang_thai', 'CONFIRMED'))
            ->orderByDesc('ma_chi_tiet_nhap')
            ->get()
            ->map(function ($ct) {
                $ngay = optional($ct->phieuNhap?->ngay_nhap)->format('d/m/Y') ?? '?';
                $maPN = $ct->ma_phieu_nhap;
                return [
                    'ma_chi_tiet_nhap' => $ct->ma_chi_tiet_nhap,
                    'so_luong_con_lai' => $ct->so_luong_con_lai,
                    
                    'label' => "Phiếu #{$maPN} — {$ngay} — Còn {$ct->so_luong_con_lai} sp",
                ];
            });

        return response()->json(['lo_nhaps' => $loNhaps]);
    }

   

    public function baoHangHong(Request $request)
    {
        $request->validate([
    'ten_san_pham'      => 'required|string|max:100',
    'gia_ban_hien_tai'  => 'nullable|numeric|min:0',
    'mo_ta'             => 'nullable|string',
    'hinh_anh'          => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
], [
    'ten_san_pham.required'     => 'Vui lòng nhập tên sản phẩm.',
    'gia_ban_hien_tai.numeric'  => 'Giá bán hiện tại phải là số.',
    'gia_ban_hien_tai.min'      => 'Giá bán hiện tại không được âm.',
    'hinh_anh.image'            => 'File tải lên phải là hình ảnh.',
    'hinh_anh.mimes'            => 'Chỉ chấp nhận JPG, PNG, GIF, WEBP.',
    'hinh_anh.max'              => 'Ảnh không được vượt quá 2MB.',
]);

        
        $chiTietNhap = ChiTietNhap::with('phieuNhap')
            ->where('ma_chi_tiet_nhap', $request->ma_chi_tiet_nhap)
            ->where('ma_san_pham', $request->ma_san_pham)
            ->whereHas('phieuNhap', fn($q) => $q->where('trang_thai', 'CONFIRMED'))
            ->firstOrFail();

        
        if ($request->so_luong_hong > $chiTietNhap->so_luong_con_lai) {
            return redirect()->back()
                ->withErrors([
                    'so_luong_hong' => "Số lượng hỏng ({$request->so_luong_hong}) vượt quá tồn kho của lô này ({$chiTietNhap->so_luong_con_lai} sp).",
                ])
                ->withInput();
        }

        $sanPham = SanPham::findOrFail($request->ma_san_pham);

        DB::transaction(function () use ($request, $chiTietNhap, $sanPham) {

            
            $chiTietNhap->decrement('so_luong_con_lai', $request->so_luong_hong);

           
            $sanPham->decrement('so_luong', $request->so_luong_hong);

           
            if ($sanPham->fresh()->so_luong <= 0) {
                $sanPham->update(['trang_thai' => 'NGUNG_BAN']);
            }

            
            BaoCaoHangHong::create([
                'ma_san_pham'       => $request->ma_san_pham,
                'ma_chi_tiet_nhap'  => $chiTietNhap->ma_chi_tiet_nhap,
                'ma_nhan_vien'      => Auth::user()?->nhanVien?->ma_nhan_vien
                                       ?? $request->ma_nhan_vien
                                       ?? 7, 
                'so_luong_hong'     => $request->so_luong_hong,
                'ly_do'             => $request->ly_do,
                'ghi_chu'           => $request->ghi_chu,
                'thoi_gian_bao_cao' => now(),
            ]);
        });

        $slHong   = $request->so_luong_hong;
        $tenSp    = $sanPham->ten_san_pham;
        $slConLai = $chiTietNhap->fresh()->so_luong_con_lai;
        $maPN     = $chiTietNhap->ma_phieu_nhap;

        return redirect()->route('san-pham.index')
            ->with('success', "Đã báo cáo {$slHong} sp hỏng của \"{$tenSp}\" tại lô Phiếu #{$maPN}. Tồn kho lô còn lại: {$slConLai} sp.");
    }
}