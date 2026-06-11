<?php

namespace App\Http\Controllers;

use App\Models\HoaDon;
use App\Models\SanPham;
use App\Models\KhachHang;
use App\Models\BaoCaoHangHong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BaoCaoController extends Controller

{
    private const LOAI_TUOI = ['HOA_TUOI', 'CHAU_HOA_TUOI'];

    private const MA_NHAN_VIEN_MAC_DINH = 7;

    public function index(Request $request)
    {
        
        $tab = $request->get('tab', 'san-pham');
        if (!in_array($tab, ['san-pham', 'doanh-thu'], true)) {
            $tab = 'san-pham';
        }

        $tongDoanhThu = HoaDon::where(function ($q) {
            $q->where('trang_thai', 'DELIVERED')
              ->orWhere('trang_thai_thanh_toan', 'DA_THANH_TOAN');
        })->sum('tong_tien');

        $tongLoiNhuan = DB::table('chi_tiet_hoa_don as ct')
            ->join('hoa_don as hd', 'ct.ma_hoa_don', '=', 'hd.ma_hoa_don')
            ->where('hd.trang_thai', 'DELIVERED')
            ->selectRaw('SUM((ct.gia_ban_snapshot - ct.gia_nhap_snapshot) * ct.so_luong) as loi_nhuan')
            ->value('loi_nhuan') ?? 0;

        $tongHoaDon = HoaDon::count();

        $tongSanPhamBan = DB::table('chi_tiet_hoa_don as ct')
            ->join('hoa_don as hd', 'ct.ma_hoa_don', '=', 'hd.ma_hoa_don')
            ->where('hd.trang_thai', 'DELIVERED')
            ->sum('ct.so_luong') ?? 0;

        $donHangHomNay = HoaDon::whereDate('ngay_dat', Carbon::today())->count();

        $doanhThuHomNay = HoaDon::whereDate('ngay_dat', Carbon::today())
            ->where('trang_thai', 'DELIVERED')
            ->sum('tong_tien');

        $sanPhamSapHetSo = SanPham::where('so_luong', '<', 5)
            ->where('trang_thai', 'DANG_BAN')
            ->count();

        $tongHangHongHomNay = BaoCaoHangHong::whereDate('thoi_gian_bao_cao', Carbon::today())
            ->sum('so_luong_hong');

        $doanhThu7Ngay = $this->layDoanhThuTheoNgay(
             Carbon::today()->subDays(6)->startOfDay(),
            Carbon::today()->endOfDay()
        );

        $trangThaiDonHang = HoaDon::selectRaw('trang_thai, COUNT(*) as so_luong')
            ->groupBy('trang_thai')
            ->pluck('so_luong', 'trang_thai')
            ->toArray();

        $topSanPhamDashboard = $this->layTopSanPham(5);

        $data = compact(
            'tab',
            'tongDoanhThu', 'tongLoiNhuan', 'tongHoaDon', 'tongSanPhamBan',
            'donHangHomNay', 'doanhThuHomNay', 'sanPhamSapHetSo', 'tongHangHongHomNay',
            'doanhThu7Ngay', 'trangThaiDonHang', 'topSanPhamDashboard'
        );

        if ($tab === 'doanh-thu') {
            $data = array_merge($data, $this->dataDoanhThu($request));
        } elseif ($tab === 'ton-kho') {
            $data = array_merge($data, $this->dataTonKho($request));
        } elseif ($tab === 'khach-hang') {
            $data = array_merge($data, $this->dataKhachHang($request));
        } elseif ($tab === 'hang-hong') {
            $data = array_merge($data, $this->dataHangHong($request));
        }

        return view('BaoCao', $data);

    }

    public function doanhThu(Request $request)
{
    $data = $this->dataDoanhThu($request);

    $data['tab'] = 'doanh-thu';

    return view('BaoCao.DoanhThu', $data);
}

public function chiTietSanPham($id, Request $request)
{
    $ngayBatDau = $request->filled('ngay_bat_dau')
        ? Carbon::parse($request->ngay_bat_dau)->startOfDay()
        : Carbon::now()->startOfMonth()->startOfDay();

    $ngayKetThuc = $request->filled('ngay_ket_thuc')
        ? Carbon::parse($request->ngay_ket_thuc)->endOfDay()
        : Carbon::now()->endOfDay();

    $sanPham = SanPham::findOrFail($id);

    // Lịch sử bán trong khoảng lọc
    $lichSuGiaBan = DB::table('chi_tiet_hoa_don as ct')
        ->join('hoa_don as hd', 'ct.ma_hoa_don', '=', 'hd.ma_hoa_don')
        ->where('ct.ma_san_pham', $id)
        ->where('hd.trang_thai', 'DELIVERED')
        ->whereBetween('hd.ngay_dat', [$ngayBatDau, $ngayKetThuc])
        ->select(
            'hd.ngay_dat',
            'ct.so_luong',
            'ct.gia_ban_snapshot'
        )
        ->orderByDesc('hd.ngay_dat')
        ->get();

    // Lịch sử nhập trong khoảng lọc
    $lichSuNhap = DB::table('chi_tiet_nhap as ctn')
        ->join('phieu_nhap as pn', 'ctn.ma_phieu_nhap', '=', 'pn.ma_phieu_nhap')
        ->where('ctn.ma_san_pham', $id)
        ->where('pn.trang_thai', 'CONFIRMED')
        ->whereBetween('pn.ngay_nhap', [$ngayBatDau, $ngayKetThuc])
        ->select(
            'pn.ngay_nhap',
            'ctn.so_luong',
            'ctn.gia_nhap',
            'ctn.gia_ban'
        )
        ->orderByDesc('pn.ngay_nhap')
        ->get();

    // Tính tổng bán trong khoảng lọc
    $tongDaBan = $lichSuGiaBan->sum('so_luong');

    // Tính tổng doanh thu trong khoảng lọc
    $tongDoanhThu = $lichSuGiaBan->sum(function ($row) {
        return $row->so_luong * $row->gia_ban_snapshot;
    });

    // Tính tổng hỏng trong khoảng lọc
    $tongHangHong = BaoCaoHangHong::where('ma_san_pham', $id)
        ->whereBetween('thoi_gian_bao_cao', [$ngayBatDau, $ngayKetThuc])
        ->sum('so_luong_hong');

    // Tính tồn kho tại thời điểm den_ngay: cumulative từ đầu đến den_ngay
    $tongNhapDenNgay = DB::table('chi_tiet_nhap as ctn')
        ->join('phieu_nhap as pn', 'ctn.ma_phieu_nhap', '=', 'pn.ma_phieu_nhap')
        ->where('ctn.ma_san_pham', $id)
        ->where('pn.trang_thai', 'CONFIRMED')
        ->where('pn.ngay_nhap', '<=', $ngayKetThuc)
        ->sum('ctn.so_luong');

    $tongBanDenNgay = DB::table('chi_tiet_hoa_don as cthd')
        ->join('hoa_don as hd', 'cthd.ma_hoa_don', '=', 'hd.ma_hoa_don')
        ->where('cthd.ma_san_pham', $id)
        ->where('hd.trang_thai', 'DELIVERED')
        ->where('hd.ngay_dat', '<=', $ngayKetThuc)
        ->sum('cthd.so_luong');

    $tongHongDenNgay = BaoCaoHangHong::where('ma_san_pham', $id)
        ->where('thoi_gian_bao_cao', '<=', $ngayKetThuc)
        ->sum('so_luong_hong');

    $tonKhoHienTai = $tongNhapDenNgay - $tongBanDenNgay - $tongHongDenNgay;

    return response()->json([
        'san_pham' => [
            'ma_san_pham' => $sanPham->ma_san_pham,
            'ten_san_pham' => $sanPham->ten_san_pham,
            'loai_san_pham' => $sanPham->loai_san_pham,
            'so_luong' => $tonKhoHienTai,
            'trang_thai' => $sanPham->trang_thai,
            'hinh_anh' => $sanPham->hinh_anh,
        ],
        'tong_quan' => [
            'tong_nhap_den_ngay' => (int) $tongNhapDenNgay,
            'tong_da_ban_den_ngay' => (int) $tongBanDenNgay,
            'tong_hang_hong_den_ngay' => (int) $tongHongDenNgay,
            'ton_kho_hien_tai' => (int) $tonKhoHienTai,
            'tong_da_ban' => $tongDaBan,
            'tong_doanh_thu' => $tongDoanhThu,
            'tong_hang_hong' => $tongHangHong,
        ],
        'lich_su_gia_ban' => $lichSuGiaBan,
        'lich_su_nhap' => $lichSuNhap,
    ]);
}
public function exportDoanhThu(Request $request)
{
    $data = $this->dataDoanhThu($request);

    $filename = 'bao_cao_doanh_thu_' . now()->format('Ymd_His') . '.csv';

    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ];

    return response()->streamDownload(function () use ($data) {
        $file = fopen('php://output', 'w');

        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($file, [
            'Thời gian',
            'Số đơn hàng',
            'Doanh thu',
            'Giá vốn',
            'Lợi nhuận',
        ]);

        foreach ($data['chiTietDoanhThu'] as $row) {
            fputcsv($file, [
                $row->thoi_gian,
                $row->so_don_hang,
                $row->doanh_thu,
                $row->gia_von,
                $row->loi_nhuan,
            ]);
        }

        fclose($file);
    }, $filename, $headers);
}
    /**
     * Trang báo cáo sản phẩm độc lập — route GET /bao-cao/san-pham
     */
    public function baoCaoSanPham(Request $request)
    {
        $data = $this->getBaoCaoSanPhamData($request);

        return view('BaoCao.SanPham', $data);
    }

    /**
     * Xuất Excel báo cáo sản phẩm — route GET /bao-cao/san-pham/export
     */
    public function exportBaoCaoSanPham(Request $request)
    {
        $data = $this->getBaoCaoSanPhamData($request);
        extract($data);

        $filename = 'bao_cao_san_pham_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ];

        $callback = function () use (
            $baoCaoSanPham,
            $tongNhapThem,
            $tongDaBan,
            $tongHangHong,
            $tongTonKhoHienTai,
            $giaNhapTrungBinhChung,
            $tyLeHongChung,
            $tongDoanhThuSanPham
        ) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM UTF-8 để Excel đọc đúng tiếng Việt

            fputcsv($file, [
                '#',
                'Sản phẩm',
                'Loại sản phẩm',
                'Nhập thêm',
                'Đã bán',
                'Hàng hỏng',
                'Tồn kho hiện tại',
                'Giá nhập TB (VNĐ)',
                'Giá bán hiện tại (VNĐ)',
                'Tỷ lệ hỏng (%)',
                'Doanh thu (VNĐ)',
                'Trạng thái',
            ]);

            foreach ($baoCaoSanPham as $i => $row) {
                fputcsv($file, [
                    $i + 1,
                    $row['ten_san_pham'],
                    \App\Helpers\LabelHelper::loaiSanPham($row['loai_san_pham']),
                    $row['nhap_them'],
                    $row['da_ban'],
                    $row['hang_hong'],
                    $row['ton_kho_hien_tai'],
                    $row['gia_nhap_tb'] !== null ? number_format($row['gia_nhap_tb'], 0, ',', '.') : '-',
                    $row['gia_ban_hien_tai'] > 0 ? number_format($row['gia_ban_hien_tai'], 0, ',', '.') : '-',
                    number_format($row['ty_le_hong'], 2, ',', '.') . '%',
                    number_format($row['doanh_thu'], 0, ',', '.'),
                    $row['trang_thai'] === 'DANG_BAN' ? 'Còn hàng' : 'Ngừng bán',
                ]);
            }

            fputcsv($file, [
                'TỔNG CỘNG',
                '',
                '',
                $tongNhapThem,
                $tongDaBan,
                $tongHangHong,
                $tongTonKhoHienTai,
                $giaNhapTrungBinhChung !== null ? number_format($giaNhapTrungBinhChung, 0, ',', '.') : '-',
                '-',
                number_format($tyLeHongChung, 2, ',', '.') . '%',
                number_format($tongDoanhThuSanPham, 0, ',', '.'),
                '',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    /**
     * API JSON — chi tiết hàng hỏng theo sản phẩm — route GET /bao-cao/san-pham/{id}/hang-hong
     * Query param tuỳ chọn: ngay_bat_dau, ngay_ket_thuc
     */
    public function chiTietHangHong(Request $request, $id)
    {
        $sanPham = SanPham::findOrFail($id);

        $query = BaoCaoHangHong::with(['nhanVien', 'chiTietNhap.phieuNhap'])
            ->where('ma_san_pham', $id);

        if ($request->filled('ngay_bat_dau')) {
            $query->where('thoi_gian_bao_cao', '>=',
                Carbon::parse($request->ngay_bat_dau)->startOfDay());
        }
        if ($request->filled('ngay_ket_thuc')) {
            $query->where('thoi_gian_bao_cao', '<=',
                Carbon::parse($request->ngay_ket_thuc)->endOfDay());
        }

        $chiTiet = $query->orderByDesc('thoi_gian_bao_cao')
            ->get()
            ->map(fn($item) => [
                'ngay_ghi_nhan'      => optional($item->thoi_gian_bao_cao)->format('d/m/Y H:i'),
                'so_luong_hong'      => $item->so_luong_hong,
                'ly_do_hong'         => $item->ly_do ?? '-',
                'ghi_chu'            => $item->ghi_chu ?? '-',
                'nhan_vien_ghi_nhan' => $item->nhanVien?->ten_nhan_vien ?? 'Không rõ',
                // Thông tin lô nhập bị hỏng
                'ma_phieu_nhap'      => $item->chiTietNhap?->ma_phieu_nhap ?? null,
                'ngay_nhap_lo'       => optional($item->chiTietNhap?->phieuNhap?->ngay_nhap)->format('d/m/Y') ?? '-',
                'sl_lo_con_lai'      => $item->chiTietNhap?->so_luong_con_lai ?? '-',
            ]);

        return response()->json([
            'ten_san_pham'       => $sanPham->ten_san_pham,
            'tong_so_luong_hong' => $chiTiet->sum('so_luong_hong'),
            'data'               => $chiTiet,
        ]);
    }

    /**
     * Truy vấn và tính toán dữ liệu báo cáo sản phẩm dùng chung cho view và export.
     */
    private function getBaoCaoSanPhamData(Request $request): array
    {
        // 1. Bộ lọc thời gian
        $ngayBatDau = $request->filled('ngay_bat_dau')
            ? Carbon::parse($request->ngay_bat_dau)->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $ngayKetThuc = $request->filled('ngay_ket_thuc')
            ? Carbon::parse($request->ngay_ket_thuc)->endOfDay()
            : Carbon::now()->endOfDay();

        $keyword = trim($request->get('keyword', ''));
        $loaiSanPham = $request->get('loai_san_pham');
        $trangThai = $request->get('trang_thai');

        // 2. Danh sách sản phẩm
       $sanPhams = SanPham::query()
    ->select(
        'ma_san_pham',
        'ten_san_pham',
        'loai_san_pham',
        'trang_thai',
        'hinh_anh',
        'so_luong',
        'gia_ban_hien_tai'
    )
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('ten_san_pham', 'like', '%' . $keyword . '%');
            })
            ->when($loaiSanPham, function ($query) use ($loaiSanPham) {
                $query->where('loai_san_pham', $loaiSanPham);
            })
            ->when($trangThai, function ($query) use ($trangThai) {
                $query->where('trang_thai', $trangThai);
            })
            ->orderBy('ten_san_pham')
            ->get();

        // 3. Nhập thêm trong khoảng ngày
        $nhapThemData = DB::table('chi_tiet_nhap as ctn')
            ->join('phieu_nhap as pn', 'ctn.ma_phieu_nhap', '=', 'pn.ma_phieu_nhap')
            ->where('pn.trang_thai', 'CONFIRMED')
            ->whereBetween('pn.ngay_nhap', [$ngayBatDau, $ngayKetThuc])
            ->groupBy('ctn.ma_san_pham')
            ->select(
                'ctn.ma_san_pham',
                DB::raw('SUM(ctn.so_luong) as tong_nhap'),
                DB::raw('SUM(ctn.so_luong * ctn.gia_nhap) as tong_tien_nhap')
            )
            ->get()
            ->keyBy('ma_san_pham');

        // 4. Đã bán + doanh thu trong khoảng ngày
        $daBanData = DB::table('chi_tiet_hoa_don as cthd')
            ->join('hoa_don as hd', 'cthd.ma_hoa_don', '=', 'hd.ma_hoa_don')
            ->where('hd.trang_thai', 'DELIVERED')
            ->whereBetween('hd.ngay_dat', [$ngayBatDau, $ngayKetThuc])
            ->groupBy('cthd.ma_san_pham')
            ->select(
                'cthd.ma_san_pham',
                DB::raw('SUM(cthd.so_luong) as tong_da_ban'),
                DB::raw('SUM(cthd.so_luong * cthd.gia_ban_snapshot) as doanh_thu')
            )
            ->get()
            ->keyBy('ma_san_pham');

        // 5. Hàng hỏng trong khoảng ngày
        $hangHongData = BaoCaoHangHong::query()
            ->whereBetween('thoi_gian_bao_cao', [$ngayBatDau, $ngayKetThuc])
            ->groupBy('ma_san_pham')
            ->select(
                'ma_san_pham',
                DB::raw('SUM(so_luong_hong) as tong_hong')
            )
            ->get()
            ->keyBy('ma_san_pham');

        // 5b. Subqueries up-to-date (tính đến ngày kết thúc) để tính TỒN KHO tại thời điểm den_ngay
        $tongNhapDenNgay = DB::table('chi_tiet_nhap as ctn')
            ->join('phieu_nhap as pn', 'ctn.ma_phieu_nhap', '=', 'pn.ma_phieu_nhap')
            ->where('pn.trang_thai', 'CONFIRMED')
            ->where('pn.ngay_nhap', '<=', $ngayKetThuc)
            ->groupBy('ctn.ma_san_pham')
            ->select('ctn.ma_san_pham', DB::raw('SUM(ctn.so_luong) as tong_nhap_den_ngay'))
            ->get()
            ->keyBy('ma_san_pham');

        $tongBanDenNgay = DB::table('chi_tiet_hoa_don as cthd')
            ->join('hoa_don as hd', 'cthd.ma_hoa_don', '=', 'hd.ma_hoa_don')
            ->where('hd.trang_thai', 'DELIVERED')
            ->where('hd.ngay_dat', '<=', $ngayKetThuc)
            ->groupBy('cthd.ma_san_pham')
            ->select('cthd.ma_san_pham', DB::raw('SUM(cthd.so_luong) as tong_ban_den_ngay'))
            ->get()
            ->keyBy('ma_san_pham');

        $tongHongDenNgay = BaoCaoHangHong::query()
            ->where('thoi_gian_bao_cao', '<=', $ngayKetThuc)
            ->groupBy('ma_san_pham')
            ->select('ma_san_pham', DB::raw('SUM(so_luong_hong) as tong_hong_den_ngay'))
            ->get()
            ->keyBy('ma_san_pham');

// 6. Giá bán hiện tại: giá bán cao nhất của lô nhập còn hàng đã xác nhận
$giaHienTaiData = collect();


$giaNhapMoiNhatData = DB::table('chi_tiet_nhap as ctn')
    ->join('phieu_nhap as pn', 'ctn.ma_phieu_nhap', '=', 'pn.ma_phieu_nhap')
    ->where('pn.trang_thai', 'CONFIRMED')
    ->select(
        'ctn.ma_san_pham',
        'ctn.gia_nhap',
        'pn.ngay_nhap',
        'ctn.ma_chi_tiet_nhap'
    )
    ->orderByDesc('pn.ngay_nhap')
    ->orderByDesc('ctn.ma_chi_tiet_nhap')
    ->get()
    ->unique('ma_san_pham')
    ->keyBy('ma_san_pham');

foreach ($sanPhams as $sp) {
    $giaHienTaiData[$sp->ma_san_pham] = (object) [
        'gia_ban' => $sp->gia_ban_hien_tai !== null
            ? (float) $sp->gia_ban_hien_tai
            : null,

        'gia_nhap' => isset($giaNhapMoiNhatData[$sp->ma_san_pham])
            ? (float) $giaNhapMoiNhatData[$sp->ma_san_pham]->gia_nhap
            : null,
    ];
}

        // 7. Ghép dữ liệu thành bảng báo cáo chính
        $baoCaoSanPham = $sanPhams->map(function ($sp) use (
            $nhapThemData,
            $daBanData,
            $hangHongData,
            $tongNhapDenNgay,
            $tongBanDenNgay,
            $tongHongDenNgay,
            $giaHienTaiData,
        ) {
            $ma = $sp->ma_san_pham;

            $nhapThem = (int) ($nhapThemData[$ma]->tong_nhap ?? 0);
            $tienNhap = (float) ($nhapThemData[$ma]->tong_tien_nhap ?? 0);

            $daBan = (int) ($daBanData[$ma]->tong_da_ban ?? 0);
            $doanhThu = (float) ($daBanData[$ma]->doanh_thu ?? 0);

            $hangHong = (int) ($hangHongData[$ma]->tong_hong ?? 0);

          $giaNhapTB = $nhapThem > 0
    ? round($tienNhap / $nhapThem, 2)
    : (
        isset($giaHienTaiData[$ma])
            ? (float) $giaHienTaiData[$ma]->gia_nhap
            : null
    );

            $tyLeHong = $nhapThem > 0
                ? round(($hangHong / $nhapThem) * 100, 2)
                : 0;

            // Tính tồn kho tại thời điểm den_ngay: tổng nhập đến den_ngay - tổng bán đến den_ngay - tổng hỏng đến den_ngay
            $tongNhapDen = (int) ($tongNhapDenNgay[$ma]->tong_nhap_den_ngay ?? 0);
            $tongBanDen = (int) ($tongBanDenNgay[$ma]->tong_ban_den_ngay ?? 0);
            $tongHongDen = (int) ($tongHongDenNgay[$ma]->tong_hong_den_ngay ?? 0);

            $tonBaoCao = $tongNhapDen - $tongBanDen - $tongHongDen;

            return [
                'ma_san_pham'      => $ma,
                'ten_san_pham'     => $sp->ten_san_pham,
                'loai_san_pham'    => $sp->loai_san_pham,
                'trang_thai'       => $sp->trang_thai,
                'hinh_anh'         => $sp->hinh_anh,

                'nhap_them'        => $nhapThem,
                'da_ban'           => $daBan,
                'hang_hong'        => $hangHong,
                'ton_kho_hien_tai' => (int) $tonBaoCao,

                'gia_nhap_tb'      => $giaNhapTB,
                // 'gia_ban_hien_tai' => (float) ($giaBanHienTai[$ma] ?? 0),
               'gia_ban_hien_tai' => isset($giaHienTaiData[$ma])
    ? (float) $giaHienTaiData[$ma]->gia_ban
    : 0,

                'ty_le_hong'       => $tyLeHong,
                'doanh_thu'        => $doanhThu,
            ];
        });

        // 8. Dòng tổng cộng
        $tongNhapThem = $baoCaoSanPham->sum('nhap_them');
        $tongDaBan = $baoCaoSanPham->sum('da_ban');
        $tongHangHong = $baoCaoSanPham->sum('hang_hong');
        $tongTonKhoHienTai = $baoCaoSanPham->sum('ton_kho_hien_tai');
        $tongDoanhThuSanPham = $baoCaoSanPham->sum('doanh_thu');

        $tongTriGiaNhap = $baoCaoSanPham->sum(function ($row) {
            return $row['nhap_them'] * ($row['gia_nhap_tb'] ?? 0);
        });

        $giaNhapTrungBinhChung = $tongNhapThem > 0
            ? round($tongTriGiaNhap / $tongNhapThem, 2)
            : null;

        $tyLeHongChung = $tongNhapThem > 0
            ? round(($tongHangHong / $tongNhapThem) * 100, 2)
            : 0;

        // 9. Top 5 sản phẩm bán chạy
        $topSanPhamBanChay = DB::table('chi_tiet_hoa_don as ct')
            ->join('hoa_don as hd', 'ct.ma_hoa_don', '=', 'hd.ma_hoa_don')
            ->join('san_pham as sp', 'ct.ma_san_pham', '=', 'sp.ma_san_pham')
            ->where('hd.trang_thai', 'DELIVERED')
            ->whereBetween('hd.ngay_dat', [$ngayBatDau, $ngayKetThuc])
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('sp.ten_san_pham', 'like', '%' . $keyword . '%');
            })
            ->when($loaiSanPham, function ($query) use ($loaiSanPham) {
                $query->where('sp.loai_san_pham', $loaiSanPham);
            })
            ->when($trangThai, function ($query) use ($trangThai) {
                $query->where('sp.trang_thai', $trangThai);
            })
            ->select(
                'sp.ten_san_pham',
                DB::raw('SUM(ct.so_luong) as tong_da_ban')
            )
            ->groupBy('sp.ma_san_pham', 'sp.ten_san_pham')
            ->orderByDesc('tong_da_ban')
            ->limit(5)
            ->get();

        return [
            'baoCaoSanPham'         => $baoCaoSanPham,
            'topSanPhamBanChay'     => $topSanPhamBanChay,

            'tongNhapThem'          => $tongNhapThem,
            'tongDaBan'             => $tongDaBan,
            'tongHangHong'          => $tongHangHong,
            'tongTonKhoHienTai'     => $tongTonKhoHienTai,
            'tongDoanhThuSanPham'   => $tongDoanhThuSanPham,

            'giaNhapTrungBinhChung' => $giaNhapTrungBinhChung,
            'tyLeHongChung'         => $tyLeHongChung,

            'ngayBatDau'            => $ngayBatDau,
            'ngayKetThuc'           => $ngayKetThuc,
            'keyword'               => $keyword,
            'loaiSanPham'           => $loaiSanPham,
            'trangThai'             => $trangThai,
        ];
    }

    // baoHangHong đã chuyển sang SanPhamController

    private function dataHangHong(Request $request): array
    {
        $hhTongSoLuong   = BaoCaoHangHong::sum('so_luong_hong');
        $hhTongLanBaoCao = BaoCaoHangHong::count();

        // Base select dùng chung: join thêm chi_tiet_nhap + phieu_nhap để lấy thông tin lô
        $baseSelect = [
            'bc.ma_bao_cao',
            'sp.ten_san_pham',
            'bc.so_luong_hong',
            'bc.thoi_gian_bao_cao',
            'bc.ly_do',
            'bc.ghi_chu',
            'nv.ten_nhan_vien',
            'pn.ma_phieu_nhap',
            'pn.ngay_nhap',
            'ctn.so_luong as sl_lo_ban_dau',
            'ctn.so_luong_con_lai as sl_lo_con_lai',
        ];

        // Danh sách hàng tươi hỏng (kèm thông tin lô nhập)
        $hhDanhSachTuoi = DB::table('bao_cao_hang_hong as bc')
            ->join('san_pham as sp',        'bc.ma_san_pham',      '=', 'sp.ma_san_pham')
            ->join('nhan_vien as nv',        'bc.ma_nhan_vien',     '=', 'nv.ma_nhan_vien')
            ->leftJoin('chi_tiet_nhap as ctn', 'bc.ma_chi_tiet_nhap', '=', 'ctn.ma_chi_tiet_nhap')
            ->leftJoin('phieu_nhap as pn',   'ctn.ma_phieu_nhap',   '=', 'pn.ma_phieu_nhap')
            ->whereIn('sp.loai_san_pham', self::LOAI_TUOI)
            ->select($baseSelect)
            ->orderBy('bc.thoi_gian_bao_cao', 'desc')
            ->get();

        $hhTongTuoi = $hhDanhSachTuoi->sum('so_luong_hong');

        // Danh sách hàng không tươi hỏng (kèm thông tin lô nhập)
        $hhDanhSachKhacTuoi = DB::table('bao_cao_hang_hong as bc')
            ->join('san_pham as sp',          'bc.ma_san_pham',      '=', 'sp.ma_san_pham')
            ->join('nhan_vien as nv',          'bc.ma_nhan_vien',     '=', 'nv.ma_nhan_vien')
            ->leftJoin('chi_tiet_nhap as ctn', 'bc.ma_chi_tiet_nhap', '=', 'ctn.ma_chi_tiet_nhap')
            ->leftJoin('phieu_nhap as pn',     'ctn.ma_phieu_nhap',   '=', 'pn.ma_phieu_nhap')
            ->whereNotIn('sp.loai_san_pham', self::LOAI_TUOI)
            ->select($baseSelect)
            ->orderBy('bc.thoi_gian_bao_cao', 'desc')
            ->get();

        $hhTongKhacTuoi = $hhDanhSachKhacTuoi->sum('so_luong_hong');

        // Top 5 sản phẩm hỏng nhiều nhất
        $hhTopHong = DB::table('bao_cao_hang_hong as bc')
            ->join('san_pham as sp', 'bc.ma_san_pham', '=', 'sp.ma_san_pham')
            ->select('sp.ten_san_pham', DB::raw('SUM(bc.so_luong_hong) as tong_hong'))
            ->groupBy('bc.ma_san_pham', 'sp.ten_san_pham')
            ->orderBy('tong_hong', 'desc')
            ->limit(5)
            ->get();

        return compact(
            'hhTongSoLuong', 'hhTongLanBaoCao',
            'hhDanhSachTuoi', 'hhTongTuoi',
            'hhDanhSachKhacTuoi', 'hhTongKhacTuoi',
            'hhTopHong'
        );
    }

    
   private function dataDoanhThu(Request $request): array
{
    $tuNgay = $request->input('tu_ngay', Carbon::now()->startOfMonth()->format('Y-m-d'));
    $denNgay = $request->input('den_ngay', Carbon::now()->format('Y-m-d'));
    $kieuHienThi = $request->input('kieu_hien_thi', 'thang');

    $start = Carbon::parse($tuNgay)->startOfDay();
    $end = Carbon::parse($denNgay)->endOfDay();

    $query = DB::table('hoa_don as hd')
        ->join('chi_tiet_hoa_don as ct', 'hd.ma_hoa_don', '=', 'ct.ma_hoa_don')
        ->join('san_pham as sp', 'ct.ma_san_pham', '=', 'sp.ma_san_pham')
        ->where('hd.trang_thai', 'DELIVERED')
        ->whereBetween('hd.ngay_dat', [$start, $end]);

    $tongDonHang = DB::table('hoa_don')
        ->where('trang_thai', 'DELIVERED')
        ->whereBetween('ngay_dat', [$start, $end])
        ->count();
    $tongSanPhamBan = (clone $query)
    ->sum('ct.so_luong');

    $tongDoanhThu = (clone $query)
        ->selectRaw('SUM(ct.so_luong * ct.gia_ban_snapshot) as tong')
        ->value('tong') ?? 0;

    $tongVon = (clone $query)
        ->selectRaw('SUM(ct.so_luong * ct.gia_nhap_snapshot) as tong')
        ->value('tong') ?? 0;

    $tongLoiNhuan = $tongDoanhThu - $tongVon;

    $giaTriDonTrungBinh = $tongDonHang > 0
        ? round($tongDoanhThu / $tongDonHang)
        : 0;

        if ($kieuHienThi === 'ngay') {

            $groupFormat = "DATE(hd.ngay_dat)";

        } elseif ($kieuHienThi === 'quy') {

            $groupFormat = "CONCAT('Quý ', QUARTER(hd.ngay_dat), '/', YEAR(hd.ngay_dat))";

        } elseif ($kieuHienThi === 'nam') {

            $groupFormat = "YEAR(hd.ngay_dat)";

        } else {

            $groupFormat = "DATE_FORMAT(hd.ngay_dat, '%m/%Y')";
        }
    $chiTietDoanhThu = (clone $query)
        ->selectRaw("
            $groupFormat as thoi_gian,
            COUNT(DISTINCT hd.ma_hoa_don) as so_don_hang,
SUM(ct.so_luong) as tong_san_pham_ban,
SUM(ct.so_luong * ct.gia_ban_snapshot) as doanh_thu,
            SUM(ct.so_luong * ct.gia_nhap_snapshot) as gia_von,
            SUM((ct.gia_ban_snapshot - ct.gia_nhap_snapshot) * ct.so_luong) as loi_nhuan
        ")
        ->groupBy(DB::raw($groupFormat))
        ->orderBy('thoi_gian')
        ->get();
    $duLieuTheoMoc = $chiTietDoanhThu->keyBy('thoi_gian');

$mocThoiGian = collect();

$current = $start->copy();

while ($current <= $end) {
    if ($kieuHienThi === 'ngay') {
        $key = $current->format('Y-m-d');
        $current->addDay();
    } elseif ($kieuHienThi === 'quy') {
        $key = 'Quý ' . $current->quarter . '/' . $current->year;
        $current->addQuarter();
    } elseif ($kieuHienThi === 'nam') {
        $key = $current->format('Y');
        $current->addYear();
    } else {
        $key = $current->format('m/Y');
        $current->addMonth();
    }

    $mocThoiGian->push($key);
}

$chiTietDoanhThu = $mocThoiGian->unique()->map(function ($moc) use ($duLieuTheoMoc) {
    $row = $duLieuTheoMoc->get($moc);

    return (object) [
        'thoi_gian' => $moc,
'so_don_hang' => (int) ($row->so_don_hang ?? 0),
'tong_san_pham_ban' => (int) ($row->tong_san_pham_ban ?? 0),
'doanh_thu' => (float) ($row->doanh_thu ?? 0),
        'gia_von' => $row->gia_von ?? 0,
        'loi_nhuan' => $row->loi_nhuan ?? 0,
    ];
});



    $topSanPham = (clone $query)
        ->selectRaw("
            sp.ten_san_pham,
            sp.hinh_anh,
            SUM(ct.so_luong) as tong_san_pham_ban,
            SUM(ct.so_luong * ct.gia_ban_snapshot) as doanh_thu
        ")
        ->groupBy('sp.ma_san_pham', 'sp.ten_san_pham', 'sp.hinh_anh')
        ->orderByDesc('doanh_thu')
        ->limit(5)
        ->get();

    return [
     
        'kieuHienThi' => $kieuHienThi,
        'tongDonHang' => $tongDonHang,
        'tongSanPhamBan' => $tongSanPhamBan,
        'tongDoanhThu' => $tongDoanhThu,
        'tongVon' => $tongVon,
        'tongLoiNhuan' => $tongLoiNhuan,
        'giaTriDonTrungBinh' => $giaTriDonTrungBinh,
        'chiTietDoanhThu' => $chiTietDoanhThu,
        'topSanPham' => $topSanPham,
    ];
}

    private function dataSanPham(Request $request): array
    // {
    //     $tuNgay  = $request->filled('tu_ngay')
    //         ? Carbon::parse($request->tu_ngay)->startOfDay()
    //         : Carbon::now()->startOfMonth();
    //     $denNgay = $request->filled('den_ngay')
    //         ? Carbon::parse($request->den_ngay)->endOfDay()
    //         : Carbon::now()->endOfDay();
    //     $keyword = trim($request->get('keyword', ''));
    //     $spTop   = (int) $request->get('top', 10);

    //     $sanPhams = SanPham::query()
    //         ->select('ma_san_pham', 'ten_san_pham', 'loai_san_pham', 'trang_thai')
    //         ->when($keyword, function ($query, $keyword) {
    //             $query->where('ten_san_pham', 'like', '%' . $keyword . '%');
    //         })
    //         ->orderBy('ten_san_pham')
    //         ->get();

    //     $nhapTruocKy = DB::table('chi_tiet_nhap as ctn')
    //         ->join('phieu_nhap as pn', 'ctn.ma_phieu_nhap', '=', 'pn.ma_phieu_nhap')
    //         ->where('pn.trang_thai', 'CONFIRMED')
    //         ->where('pn.ngay_nhap', '<', $tuNgay)
    //         ->groupBy('ctn.ma_san_pham')
    //         ->select('ctn.ma_san_pham', DB::raw('SUM(ctn.so_luong) as tong_nhap'))
    //         ->pluck('tong_nhap', 'ma_san_pham');

    //     $daBanTruocKy = DB::table('chi_tiet_hoa_don as cthd')
    //         ->join('hoa_don as hd', 'cthd.ma_hoa_don', '=', 'hd.ma_hoa_don')
    //         ->where('hd.trang_thai', 'DELIVERED')
    //         ->where('hd.ngay_dat', '<', $tuNgay)
    //         ->groupBy('cthd.ma_san_pham')
    //         ->select('cthd.ma_san_pham', DB::raw('SUM(cthd.so_luong) as tong_ban'))
    //         ->pluck('tong_ban', 'ma_san_pham');

    //     $hongTruocKy = BaoCaoHangHong::query()
    //         ->where('thoi_gian_bao_cao', '<', $tuNgay)
    //         ->groupBy('ma_san_pham')
    //         ->select('ma_san_pham', DB::raw('SUM(so_luong_hong) as tong_hong'))
    //         ->pluck('tong_hong', 'ma_san_pham');

    //     $nhapTrongKy = DB::table('chi_tiet_nhap as ctn')
    //         ->join('phieu_nhap as pn', 'ctn.ma_phieu_nhap', '=', 'pn.ma_phieu_nhap')
    //         ->where('pn.trang_thai', 'CONFIRMED')
    //         ->whereBetween('pn.ngay_nhap', [$tuNgay, $denNgay])
    //         ->groupBy('ctn.ma_san_pham')
    //         ->select(
    //             'ctn.ma_san_pham',
    //             DB::raw('SUM(ctn.so_luong) as tong_nhap'),
    //             DB::raw('SUM(ctn.so_luong * ctn.gia_nhap) as tong_tien_nhap')
    //         )
    //         ->get()
    //         ->keyBy('ma_san_pham');

    //     $daBanTrongKy = DB::table('chi_tiet_hoa_don as cthd')
    //         ->join('hoa_don as hd', 'cthd.ma_hoa_don', '=', 'hd.ma_hoa_don')
    //         ->where('hd.trang_thai', 'DELIVERED')
    //         ->whereBetween('hd.ngay_dat', [$tuNgay, $denNgay])
    //         ->groupBy('cthd.ma_san_pham')
    //         ->select('cthd.ma_san_pham', DB::raw('SUM(cthd.so_luong) as tong_ban'))
    //         ->pluck('tong_ban', 'ma_san_pham');

    //     $hongTrongKy = BaoCaoHangHong::query()
    //         ->whereBetween('thoi_gian_bao_cao', [$tuNgay, $denNgay])
    //         ->groupBy('ma_san_pham')
    //         ->select('ma_san_pham', DB::raw('SUM(so_luong_hong) as tong_hong'))
    //         ->pluck('tong_hong', 'ma_san_pham');

    //     $giaBanHienTai = DB::table('chi_tiet_nhap as ctn')
    //         ->join('phieu_nhap as pn', 'ctn.ma_phieu_nhap', '=', 'pn.ma_phieu_nhap')
    //         ->where('pn.trang_thai', 'CONFIRMED')
    //         ->orderByDesc('pn.ngay_nhap')
    //         ->orderByDesc('ctn.ma_chi_tiet_nhap')
    //         ->get(['ctn.ma_san_pham', 'ctn.gia_ban'])
    //         ->unique('ma_san_pham')
    //         ->pluck('gia_ban', 'ma_san_pham');

    //     $baoCaoSanPham = $sanPhams->map(function ($sanPham) use (
    //         $nhapTruocKy,
    //         $daBanTruocKy,
    //         $hongTruocKy,
    //         $nhapTrongKy,
    //         $daBanTrongKy,
    //         $hongTrongKy,
    //         $giaBanHienTai
    //     ) {
    //         $ma = $sanPham->ma_san_pham;
    //         $tongNhapTruocKy = (int) ($nhapTruocKy[$ma] ?? 0);
    //         $tongDaBanTruocKy = (int) ($daBanTruocKy[$ma] ?? 0);
    //         $tongHongTruocKy = (int) ($hongTruocKy[$ma] ?? 0);
    //         $tongNhapTrongKy = (int) ($nhapTrongKy[$ma]->tong_nhap ?? 0);
    //         $tongTienNhapTrongKy = (float) ($nhapTrongKy[$ma]->tong_tien_nhap ?? 0);
    //         $tongDaBanTrongKy = (int) ($daBanTrongKy[$ma] ?? 0);
    //         $tongHongTrongKy = (int) ($hongTrongKy[$ma] ?? 0);

    //         $tonDauKy = $tongNhapTruocKy - $tongDaBanTruocKy - $tongHongTruocKy;
    //         $tonCuoiKy = $tonDauKy + $tongNhapTrongKy - $tongDaBanTrongKy - $tongHongTrongKy;
    //         $giaNhapTrungBinh = $tongNhapTrongKy > 0
    //             ? round($tongTienNhapTrongKy / $tongNhapTrongKy, 2)
    //             : 0;
    //         $giaBan = isset($giaBanHienTai[$ma]) ? (float) $giaBanHienTai[$ma] : 0;
    //         $tyLeHong = $tongNhapTrongKy > 0
    //             ? round(($tongHongTrongKy / $tongNhapTrongKy) * 100, 2)
    //             : 0;

    //         return [
    //             'ma_san_pham' => $ma,
    //             'ten_san_pham' => $sanPham->ten_san_pham,
    //             'loai_san_pham' => $sanPham->loai_san_pham,
    //             'trang_thai' => $sanPham->trang_thai,
    //             'ton_dau_ky' => $tonDauKy,
    //             'nhap_trong_ky' => $tongNhapTrongKy,
    //             'da_ban_trong_ky' => $tongDaBanTrongKy,
    //             'hang_hong_trong_ky' => $tongHongTrongKy,
    //             'ton_cuoi_ky' => $tonCuoiKy,
    //             'gia_nhap_tb_trong_ky' => $giaNhapTrungBinh,
    //             'gia_ban_hien_tai' => $giaBan,
    //             'ty_le_hong' => $tyLeHong,
    //         ];
    //     });

    //     $tongTonDauKy = $baoCaoSanPham->sum('ton_dau_ky');
    //     $tongNhapTrongKy = $baoCaoSanPham->sum('nhap_trong_ky');
    //     $tongDaBanTrongKy = $baoCaoSanPham->sum('da_ban_trong_ky');
    //     $tongHangHongTrongKy = $baoCaoSanPham->sum('hang_hong_trong_ky');
    //     $tongTonCuoiKy = $baoCaoSanPham->sum('ton_cuoi_ky');

    //     $tongTriGiaNhapTrongKy = $baoCaoSanPham->sum(function ($row) {
    //         return $row['nhap_trong_ky'] * $row['gia_nhap_tb_trong_ky'];
    //     });

    //     $giaNhapTrungBinhChung = $tongNhapTrongKy > 0
    //         ? round($tongTriGiaNhapTrongKy / $tongNhapTrongKy, 2)
    //         : 0;

    //     $tyLeHongChung = $tongNhapTrongKy > 0
    //         ? round(($tongHangHongTrongKy / $tongNhapTrongKy) * 100, 2)
    //         : 0;

    //     $spDanhSach = DB::table('chi_tiet_hoa_don as ct')
    //         ->join('hoa_don as hd', 'ct.ma_hoa_don', '=', 'hd.ma_hoa_don')
    //         ->join('san_pham as sp', 'ct.ma_san_pham', '=', 'sp.ma_san_pham')
    //         ->where('hd.trang_thai', 'DELIVERED')
    //         ->whereBetween('hd.ngay_dat', [$tuNgay, $denNgay])
    //         ->when($keyword, function ($query, $keyword) {
    //             $query->where('sp.ten_san_pham', 'like', '%' . $keyword . '%');
    //         })
    //         ->select('sp.ten_san_pham', DB::raw('SUM(ct.so_luong) as tong_ban'))
    //         ->groupBy('sp.ma_san_pham', 'sp.ten_san_pham')
    //         ->orderByDesc('tong_ban')
    //         ->limit($spTop)
    //         ->get();

    //     $topSanPhamBanChay = DB::table('chi_tiet_hoa_don as ct')
    //         ->join('hoa_don as hd', 'ct.ma_hoa_don', '=', 'hd.ma_hoa_don')
    //         ->join('san_pham as sp', 'ct.ma_san_pham', '=', 'sp.ma_san_pham')
    //         ->where('hd.trang_thai', 'DELIVERED')
    //         ->whereBetween('hd.ngay_dat', [$tuNgay, $denNgay])
    //         ->when($keyword, function ($query, $keyword) {
    //             $query->where('sp.ten_san_pham', 'like', '%' . $keyword . '%');
    //         })
    //         ->select('sp.ten_san_pham', DB::raw('SUM(ct.so_luong) as tong_da_ban'))
    //         ->groupBy('sp.ma_san_pham', 'sp.ten_san_pham')
    //         ->orderByDesc('tong_da_ban')
    //         ->limit(5)
    //         ->get();

    //     $spTongBan = $spDanhSach->sum('tong_ban');

    //     return compact(
    //         'tuNgay', 'denNgay', 'keyword', 'spTop', 'spDanhSach', 'spTongBan',
    //         'baoCaoSanPham', 'topSanPhamBanChay',
    //         'tongTonDauKy', 'tongNhapTrongKy', 'tongDaBanTrongKy',
    //         'tongHangHongTrongKy', 'tongTonCuoiKy',
    //         'giaNhapTrungBinhChung', 'tyLeHongChung'
    //     );
    // }
    {
        $tuNgay  = $request->filled('tu_ngay')
            ? Carbon::parse($request->tu_ngay)->startOfDay()
            : Carbon::now()->startOfMonth();
        $denNgay = $request->filled('den_ngay')
            ? Carbon::parse($request->den_ngay)->endOfDay()
            : Carbon::now()->endOfDay();
        $keyword = trim($request->get('keyword', ''));
        $spTop   = (int) $request->get('top', 10);

        $sanPhams = SanPham::query()
            ->select('ma_san_pham', 'ten_san_pham', 'loai_san_pham', 'trang_thai')
            ->when($keyword, function ($query, $keyword) {
                $query->where('ten_san_pham', 'like', '%' . $keyword . '%');
            })
            ->orderBy('ten_san_pham')
            ->get();

        $nhapTruocKy = DB::table('chi_tiet_nhap as ctn')
            ->join('phieu_nhap as pn', 'ctn.ma_phieu_nhap', '=', 'pn.ma_phieu_nhap')
            ->where('pn.trang_thai', 'CONFIRMED')
            ->where('pn.ngay_nhap', '<', $tuNgay)
            ->groupBy('ctn.ma_san_pham')
            ->select('ctn.ma_san_pham', DB::raw('SUM(ctn.so_luong) as tong_nhap'))
            ->pluck('tong_nhap', 'ma_san_pham');

        $daBanTruocKy = DB::table('chi_tiet_hoa_don as cthd')
            ->join('hoa_don as hd', 'cthd.ma_hoa_don', '=', 'hd.ma_hoa_don')
            ->where('hd.trang_thai', 'DELIVERED')
            ->where('hd.ngay_dat', '<', $tuNgay)
            ->groupBy('cthd.ma_san_pham')
            ->select('cthd.ma_san_pham', DB::raw('SUM(cthd.so_luong) as tong_ban'))
            ->pluck('tong_ban', 'ma_san_pham');

        $hongTruocKy = BaoCaoHangHong::query()
            ->where('thoi_gian_bao_cao', '<', $tuNgay)
            ->groupBy('ma_san_pham')
            ->select('ma_san_pham', DB::raw('SUM(so_luong_hong) as tong_hong'))
            ->pluck('tong_hong', 'ma_san_pham');

        $nhapTrongKy = DB::table('chi_tiet_nhap as ctn')
            ->join('phieu_nhap as pn', 'ctn.ma_phieu_nhap', '=', 'pn.ma_phieu_nhap')
            ->where('pn.trang_thai', 'CONFIRMED')
            ->whereBetween('pn.ngay_nhap', [$tuNgay, $denNgay])
            ->groupBy('ctn.ma_san_pham')
            ->select(
                'ctn.ma_san_pham',
                DB::raw('SUM(ctn.so_luong) as tong_nhap'),
                DB::raw('SUM(ctn.so_luong * ctn.gia_nhap) as tong_tien_nhap')
            )
            ->get()
            ->keyBy('ma_san_pham');

        $daBanTrongKy = DB::table('chi_tiet_hoa_don as cthd')
            ->join('hoa_don as hd', 'cthd.ma_hoa_don', '=', 'hd.ma_hoa_don')
            ->where('hd.trang_thai', 'DELIVERED')
            ->whereBetween('hd.ngay_dat', [$tuNgay, $denNgay])
            ->groupBy('cthd.ma_san_pham')
            ->select('cthd.ma_san_pham', DB::raw('SUM(cthd.so_luong) as tong_ban'))
            ->pluck('tong_ban', 'ma_san_pham');

        $hongTrongKy = BaoCaoHangHong::query()
            ->whereBetween('thoi_gian_bao_cao', [$tuNgay, $denNgay])
            ->groupBy('ma_san_pham')
            ->select('ma_san_pham', DB::raw('SUM(so_luong_hong) as tong_hong'))
            ->pluck('tong_hong', 'ma_san_pham');

        $giaBanHienTai = DB::table('chi_tiet_nhap as ctn')
            ->join('phieu_nhap as pn', 'ctn.ma_phieu_nhap', '=', 'pn.ma_phieu_nhap')
            ->where('pn.trang_thai', 'CONFIRMED')
            ->orderByDesc('pn.ngay_nhap')
            ->orderByDesc('ctn.ma_chi_tiet_nhap')
            ->get(['ctn.ma_san_pham', 'ctn.gia_ban'])
            ->unique('ma_san_pham')
            ->pluck('gia_ban', 'ma_san_pham');

        $baoCaoSanPham = $sanPhams->map(function ($sanPham) use (
            $nhapTruocKy, $daBanTruocKy, $hongTruocKy,
            $nhapTrongKy, $daBanTrongKy, $hongTrongKy, $giaBanHienTai
        ) {
            $ma = $sanPham->ma_san_pham;
            $tongNhapTruocKy     = (int)   ($nhapTruocKy[$ma] ?? 0);
            $tongDaBanTruocKy    = (int)   ($daBanTruocKy[$ma] ?? 0);
            $tongHongTruocKy     = (int)   ($hongTruocKy[$ma] ?? 0);
            $tongNhapTrongKy     = (int)   ($nhapTrongKy[$ma]->tong_nhap ?? 0);
            $tongTienNhapTrongKy = (float) ($nhapTrongKy[$ma]->tong_tien_nhap ?? 0);
            $tongDaBanTrongKy    = (int)   ($daBanTrongKy[$ma] ?? 0);
            $tongHongTrongKy     = (int)   ($hongTrongKy[$ma] ?? 0);

            $tonDauKy         = $tongNhapTruocKy - $tongDaBanTruocKy - $tongHongTruocKy;
            $tonCuoiKy        = $tonDauKy + $tongNhapTrongKy - $tongDaBanTrongKy - $tongHongTrongKy;
            $giaNhapTrungBinh = $tongNhapTrongKy > 0
                ? round($tongTienNhapTrongKy / $tongNhapTrongKy, 2)
                : 0;
            $giaBan   = isset($giaBanHienTai[$ma]) ? (float) $giaBanHienTai[$ma] : 0;
            $tyLeHong = $tongNhapTrongKy > 0
                ? round(($tongHongTrongKy / $tongNhapTrongKy) * 100, 2)
                : 0;

            return [
                'ma_san_pham'          => $ma,
                'ten_san_pham'         => $sanPham->ten_san_pham,
                'loai_san_pham'        => $sanPham->loai_san_pham,
                'trang_thai'           => $sanPham->trang_thai,
                'ton_dau_ky'           => $tonDauKy,
                'nhap_trong_ky'        => $tongNhapTrongKy,
                'da_ban_trong_ky'      => $tongDaBanTrongKy,
                'hang_hong_trong_ky'   => $tongHongTrongKy,
                'ton_cuoi_ky'          => $tonCuoiKy,
                'gia_nhap_tb_trong_ky' => $giaNhapTrungBinh,
                'gia_ban_hien_tai'     => $giaBan,
                'ty_le_hong'           => $tyLeHong,
            ];
        });

        $tongTonDauKy        = $baoCaoSanPham->sum('ton_dau_ky');
        $tongNhapTrongKy     = $baoCaoSanPham->sum('nhap_trong_ky');
        $tongDaBanTrongKy    = $baoCaoSanPham->sum('da_ban_trong_ky');
        $tongHangHongTrongKy = $baoCaoSanPham->sum('hang_hong_trong_ky');
        $tongTonCuoiKy       = $baoCaoSanPham->sum('ton_cuoi_ky');

        $tongTriGiaNhapTrongKy = $baoCaoSanPham->sum(fn ($row) =>
            $row['nhap_trong_ky'] * $row['gia_nhap_tb_trong_ky']
        );

        $giaNhapTrungBinhChung = $tongNhapTrongKy > 0
            ? round($tongTriGiaNhapTrongKy / $tongNhapTrongKy, 2)
            : 0;

        $tyLeHongChung = $tongNhapTrongKy > 0
            ? round(($tongHangHongTrongKy / $tongNhapTrongKy) * 100, 2)
            : 0;

        $topSanPhamBanChay = DB::table('chi_tiet_hoa_don as ct')
            ->join('hoa_don as hd', 'ct.ma_hoa_don', '=', 'hd.ma_hoa_don')
            ->join('san_pham as sp', 'ct.ma_san_pham', '=', 'sp.ma_san_pham')
            ->where('hd.trang_thai', 'DELIVERED')
            ->whereBetween('hd.ngay_dat', [$tuNgay, $denNgay])
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('sp.ten_san_pham', 'like', '%' . $keyword . '%');
            })
            ->select('sp.ten_san_pham', DB::raw('SUM(ct.so_luong) as tong_da_ban'))
            ->groupBy('sp.ma_san_pham', 'sp.ten_san_pham')
            ->orderByDesc('tong_da_ban')
            ->limit(5)
            ->get();

        return compact(
            'tuNgay', 'denNgay', 'keyword', 'spTop',
            'baoCaoSanPham', 'topSanPhamBanChay',
            'tongTonDauKy', 'tongNhapTrongKy', 'tongDaBanTrongKy',
            'tongHangHongTrongKy', 'tongTonCuoiKy',
            'giaNhapTrungBinhChung', 'tyLeHongChung'
        );
    }

    private function dataTonKho(Request $request): array
    {
        $tkNguong = (int) $request->get('nguong', 5);

        $tkSapHet = SanPham::where('so_luong', '<', $tkNguong)
            ->where('trang_thai', 'DANG_BAN')
            ->orderBy('so_luong')
            ->get();

        $tkLoHang = DB::table('chi_tiet_nhap as ctn')
            ->join('san_pham as sp', 'ctn.ma_san_pham', '=', 'sp.ma_san_pham')
            ->join('phieu_nhap as pn', 'ctn.ma_phieu_nhap', '=', 'pn.ma_phieu_nhap')
            ->where('pn.trang_thai', 'CONFIRMED')
            ->where('ctn.so_luong_con_lai', '>', 0)
            ->select('sp.ten_san_pham', 'ctn.gia_nhap', 'ctn.gia_ban', 'ctn.so_luong', 'ctn.so_luong_con_lai', 'pn.ngay_nhap')
            ->orderBy('pn.ngay_nhap', 'desc')
            ->get();

        $tkNhapXuat = DB::table('san_pham as sp')
            ->leftJoin('chi_tiet_nhap as ctn', 'sp.ma_san_pham', '=', 'ctn.ma_san_pham')
            ->leftJoin('phieu_nhap as pn', function ($join) {
                $join->on('ctn.ma_phieu_nhap', '=', 'pn.ma_phieu_nhap')
                     ->where('pn.trang_thai', 'CONFIRMED');
            })
            ->leftJoin('chi_tiet_hoa_don as cthd', 'sp.ma_san_pham', '=', 'cthd.ma_san_pham')
            ->leftJoin('hoa_don as hd', function ($join) {
                $join->on('cthd.ma_hoa_don', '=', 'hd.ma_hoa_don')
                     ->where('hd.trang_thai', 'DELIVERED');
            })
            ->select(
                'sp.ma_san_pham', 'sp.ten_san_pham', 'sp.so_luong as ton_hien_tai',
                DB::raw('SUM(DISTINCT ctn.so_luong) as tong_nhap'),
                DB::raw('SUM(cthd.so_luong) as tong_ban')
            )
            ->groupBy('sp.ma_san_pham', 'sp.ten_san_pham', 'sp.so_luong')
            ->orderBy('sp.ten_san_pham')
            ->get();

        return compact('tkNguong', 'tkSapHet', 'tkLoHang', 'tkNhapXuat');
    }

    private function dataKhachHang(Request $request): array
    {
        $khTop = (int) $request->get('top', 10);

        $khDanhSach = DB::table('khach_hang as kh')
            ->join('hoa_don as hd', 'kh.ma_khach_hang', '=', 'hd.ma_khach_hang')
            ->where('hd.trang_thai', 'DELIVERED')
            ->select(
                'kh.ma_khach_hang', 'kh.ten_khach_hang', 'kh.so_dien_thoai', 'kh.email',
                DB::raw('COUNT(hd.ma_hoa_don) as so_don_hang'),
                DB::raw('SUM(hd.tong_tien) as tong_tien_mua')
            )
            ->groupBy('kh.ma_khach_hang', 'kh.ten_khach_hang', 'kh.so_dien_thoai', 'kh.email')
            ->orderBy('tong_tien_mua', 'desc')
            ->limit($khTop)
            ->get();

        $khTongKhachHang = KhachHang::count();

        return compact('khTop', 'khDanhSach', 'khTongKhachHang');
    }

    private function layDoanhThuTheoNgay($tuNgay, $denNgay): array
    {
        $rawData = HoaDon::whereBetween('ngay_dat', [$tuNgay, $denNgay])
            ->where(function ($q) {
                $q->where('trang_thai', 'DELIVERED')
                  ->orWhere('trang_thai_thanh_toan', 'DA_THANH_TOAN');
            })
            ->selectRaw('DATE(ngay_dat) as ngay, SUM(tong_tien) as doanh_thu')
            ->groupBy('ngay')
            ->orderBy('ngay')
            ->get()
            ->keyBy('ngay');

        $labels  = [];
        $data    = [];
        $current = Carbon::parse($tuNgay)->copy();
        $end     = Carbon::parse($denNgay)->copy();

        while ($current->lte($end)) {
            $key      = $current->format('Y-m-d');
            $labels[] = $current->format('d/m');
            $data[]   = isset($rawData[$key]) ? (float) $rawData[$key]->doanh_thu : 0;
            $current->addDay();
        }

        return compact('labels', 'data');
    }

    private function layTopSanPham(int $top = 10)
    {
        return DB::table('chi_tiet_hoa_don as ct')
            ->join('hoa_don as hd', 'ct.ma_hoa_don', '=', 'hd.ma_hoa_don')
            ->join('san_pham as sp', 'ct.ma_san_pham', '=', 'sp.ma_san_pham')
            ->where('hd.trang_thai', 'DELIVERED')
            ->select(
                'sp.ten_san_pham',
                DB::raw('SUM(ct.so_luong) as tong_ban'),
                DB::raw('SUM(ct.so_luong * ct.gia_ban_snapshot) as tong_doanh_thu')
            )
            ->groupBy('sp.ma_san_pham', 'sp.ten_san_pham')
            ->orderBy('tong_ban', 'desc')
            ->limit($top)
            ->get();
    }

}