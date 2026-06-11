<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->check() ? Auth::user()->load('khachHang') : null;

       $keyword = trim($request->get('q', ''));

$sanPhams = SanPham::where('trang_thai', 'DANG_BAN')
    ->with(['chiTietNhaps' => function ($q) {
        $q->where('so_luong_con_lai', '>', 0)
            ->whereHas('phieuNhap', fn($q2) => $q2->where('trang_thai', 'CONFIRMED'))
            ->orderBy('gia_ban', 'asc');
    }])
    ->when($keyword, function ($query) use ($keyword) {
        $query->where(function ($sub) use ($keyword) {
            $sub->where('ten_san_pham', 'like', "%{$keyword}%")
                ->orWhere('loai_san_pham', 'like', "%{$keyword}%");

            // nếu có cột mo_ta mới để dòng này
            // ->orWhere('mo_ta', 'like', "%{$keyword}%");
        });
    })
    ->orderBy('ma_san_pham', 'desc')
    ->get();
        $hoaTuoiBanChay = SanPham::where('trang_thai', 'DANG_BAN')
            ->whereNotIn('loai_san_pham', ['PHU_KIEN', 'QUA_TANG'])
            ->with(['chiTietNhaps' => function ($q) {
                $q->where('so_luong_con_lai', '>', 0)
                    ->whereHas('phieuNhap', fn($q2) => $q2->where('trang_thai', 'CONFIRMED'))
                    ->orderBy('gia_ban', 'asc');
            }])
            ->limit(8)
            ->get();

        $phuKienNoiBat = SanPham::where('trang_thai', 'DANG_BAN')
            ->where('loai_san_pham', 'PHU_KIEN')
            ->with(['chiTietNhaps' => function ($q) {
                $q->where('so_luong_con_lai', '>', 0)
                    ->whereHas('phieuNhap', fn($q2) => $q2->where('trang_thai', 'CONFIRMED'))
                    ->orderBy('gia_ban', 'asc');
            }])
            ->limit(6)
            ->get();

        $quaTangNoiBat = SanPham::where('trang_thai', 'DANG_BAN')
            ->where('loai_san_pham', 'QUA_TANG')
            ->with(['chiTietNhaps' => function ($q) {
                $q->where('so_luong_con_lai', '>', 0)
                    ->whereHas('phieuNhap', fn($q2) => $q2->where('trang_thai', 'CONFIRMED'))
                    ->orderBy('gia_ban', 'asc');
            }])
            ->limit(6)
            ->get();

        return view('customer.Dashboard', compact(
            'user',
            'sanPhams',
            'hoaTuoiBanChay',
            'phuKienNoiBat',
            'quaTangNoiBat'
        ));
    }

    public function hoaTuoi(Request $request)
    {
        $user = auth()->check() ? Auth::user()->load('khachHang') : null;

        $excludedTypes = ['PHU_KIEN', 'QUA_TANG'];

        $query = SanPham::where('trang_thai', 'DANG_BAN')
            ->whereNotIn('loai_san_pham', $excludedTypes)
            ->with(['chiTietNhaps' => function ($q) {
                $q->where('so_luong_con_lai', '>', 0)
                    ->whereHas('phieuNhap', fn($q2) => $q2->where('trang_thai', 'CONFIRMED'))
                    ->orderBy('gia_ban', 'asc');
            }]);

        if ($request->filled('loai')) {
            $query->whereIn('loai_san_pham', $request->loai);
        }

        $this->applyPriceFilter($query, $request);

        $hoaTuois = $query->orderBy('ma_san_pham', 'desc')->get();

        $categories = SanPham::where('trang_thai', 'DANG_BAN')
            ->whereNotIn('loai_san_pham', $excludedTypes)
            ->select('loai_san_pham')
            ->distinct()
            ->pluck('loai_san_pham');

        return view('customer.HoaTuoi', compact(
            'user',
            'hoaTuois',
            'categories'
        ));
    }

    public function phuKien(Request $request)
    {
        $user = auth()->check() ? Auth::user()->load('khachHang') : null;

        $query = SanPham::where('trang_thai', 'DANG_BAN')
            ->where('loai_san_pham', 'PHU_KIEN')
            ->with(['chiTietNhaps' => function ($q) {
                $q->where('so_luong_con_lai', '>', 0)
                    ->whereHas('phieuNhap', fn($q2) => $q2->where('trang_thai', 'CONFIRMED'))
                    ->orderBy('gia_ban', 'asc');
            }]);

        $this->applyPriceFilter($query, $request);

        $phuKiens = $query->orderBy('ma_san_pham', 'desc')->get();

        return view('customer.PhuKien', compact(
            'user',
            'phuKiens'
        ));
    }

    public function quaTang(Request $request)
    {
        $user = auth()->check() ? Auth::user()->load('khachHang') : null;

        $query = SanPham::where('trang_thai', 'DANG_BAN')
            ->where('loai_san_pham', 'QUA_TANG')
            ->with(['chiTietNhaps' => function ($q) {
                $q->where('so_luong_con_lai', '>', 0)
                    ->whereHas('phieuNhap', fn($q2) => $q2->where('trang_thai', 'CONFIRMED'))
                    ->orderBy('gia_ban', 'asc');
            }]);

        $this->applyPriceFilter($query, $request);

        $quaTangs = $query->orderBy('ma_san_pham', 'desc')->get();

        return view('customer.QuaTang', compact(
            'user',
            'quaTangs'
        ));
    }
    public function gioiThieu()
{
    $user = auth()->check() ? Auth::user()->load('khachHang') : null;

    return view('customer.GioiThieu', compact('user'));
}
public function lienHe()
{
    $user = auth()->check() ? Auth::user()->load('khachHang') : null;

    return view('customer.LienHe', compact('user'));
}

    private function applyPriceFilter($query, Request $request): void
{
    if (!$request->filled('price')) {
        return;
    }

    $query->where(function ($sub) use ($request) {

        foreach ($request->price as $price) {

            if ($price === 'duoi_20000') {
                $sub->orWhere('gia_ban_hien_tai', '<', 20000);
            }

            if ($price === '20000_50000') {
                $sub->orWhereBetween('gia_ban_hien_tai', [20000, 50000]);
            }

            if ($price === '50000_100000') {
                $sub->orWhereBetween('gia_ban_hien_tai', [50000, 100000]);
            }

            if ($price === 'tren_100000') {
                $sub->orWhere('gia_ban_hien_tai', '>', 100000);
            }
        }
    });
}
}