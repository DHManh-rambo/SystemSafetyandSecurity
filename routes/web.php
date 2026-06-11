<?php

use App\Http\Controllers\NguoiDungController;
use App\Http\Controllers\KhachHangController;
use App\Http\Controllers\NhanVienController;
use App\Http\Controllers\SanPhamController;
use App\Http\Controllers\HoaDonController;
use App\Http\Controllers\DonHangController;
use App\Http\Controllers\PhieuNhapController;
use App\Http\Controllers\BaoCaoController;
use App\Http\Controllers\Shipper\ShipperController;
use App\Http\Controllers\Shipper\NhanDonController;
use App\Http\Controllers\Shipper\ShipperProfileController;
use App\Http\Controllers\Shipper\ThongBaoController;
use App\Http\Controllers\Shipper\ShipperThongBaoController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Customer\ThongBaoController as CustomerThongBaoController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\Customer\ChiTietSanPhamController;
use App\Http\Controllers\Customer\GioHangController;
use App\Http\Controllers\Customer\ThanhToanController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

// ─── Trang chủ ────────────────────────────────────────────────────────────────
Route::get('/', function () {
    if (auth()->check()) {
        $vai_tro = auth()->user()->vai_tro;
        return match($vai_tro) {
            'ADMIN', 'NHAN_VIEN' => redirect()->route('admin.dashboard'),
            'SHIPPER'            => redirect()->route('shipper.dashboard'),
            default              => redirect()->route('customer.dashboard'),
        };
    }
    // Guest vào thẳng dashboard customer (không cần đăng nhập)
    return redirect()->route('customer.dashboard');
});

// ─── Breeze Auth routes ────────────────────────────────────────────────────────
require __DIR__.'/auth.php';

// ─── ADMIN + NHÂN VIÊN Dashboard ──────────────────────────────────────────────
Route::middleware(['auth', 'role:ADMIN,NHAN_VIEN'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');
});

// ─── ADMIN ONLY ───────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:ADMIN'])->group(function () {

    // Quản lý Người dùng
    Route::prefix('nguoi-dung')->name('nguoi-dung.')->group(function () {
        Route::get('/',              [NguoiDungController::class, 'index'])->name('index');
        Route::post('/',             [NguoiDungController::class, 'store'])->name('store');
        Route::put('/{id}',          [NguoiDungController::class, 'update'])->name('update');
        Route::delete('/{id}',       [NguoiDungController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/edit-data',[NguoiDungController::class, 'editData'])->name('edit-data');
    });

    // Quản lý Nhân viên (bao gồm trả tiền - admin only)
    Route::prefix('nhan-vien')->name('nhan-vien.')->controller(NhanVienController::class)->group(function () {
        Route::get('/',                   'index')->name('index');
        Route::put('/{ma_nhan_vien}',     'update')->name('update');
        Route::delete('/{ma_nhan_vien}',  'destroy')->name('destroy');
        Route::post('/{ma}/tra-tien',     'payback')->name('payback');
    });

    // Phiếu nhập
    Route::prefix('phieu-nhap')->name('phieu-nhap.')->controller(PhieuNhapController::class)->group(function () {
        Route::get('/',              'index')->name('index');
        Route::post('/',             'store')->name('store');
        Route::get('/{id}/edit-data','editData')->name('edit-data');
        Route::put('/{id}',          'update')->name('update');
        Route::delete('/{id}',       'destroy')->name('destroy');
        Route::post('/{id}/confirm', 'confirm')->name('confirm');
    });

    // Báo cáo
    Route::prefix('bao-cao')->name('bao-cao.')->group(function () {
        Route::get('/',             [BaoCaoController::class, 'index'])->name('index');
        Route::get('/doanh-thu',    [BaoCaoController::class, 'doanhThu'])->name('doanh-thu');
Route::get('/doanh-thu/export', [BaoCaoController::class, 'exportDoanhThu'])->name('doanh-thu.export');
        Route::get('/loi-nhuan',    [BaoCaoController::class, 'index'])->name('loi-nhuan');
        Route::get('/san-pham',     [BaoCaoController::class, 'baoCaoSanPham'])->name('san-pham');
        Route::get('/ton-kho',      [BaoCaoController::class, 'index'])->name('ton-kho');
        Route::get('/khach-hang',   [BaoCaoController::class, 'index'])->name('khach-hang');
    });
});

// ─── ADMIN + NHÂN VIÊN ────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:ADMIN,NHAN_VIEN'])->group(function () {

    // Quản lý Khách hàng
    Route::prefix('khach-hang')->name('khach-hang.')->group(function () {
        Route::get('/',        [KhachHangController::class, 'index'])->name('index');
        Route::put('/{id}',    [KhachHangController::class, 'update'])->name('update');
        Route::delete('/{id}', [KhachHangController::class, 'destroy'])->name('destroy');
    });

    // Sản phẩm
    Route::prefix('san-pham')->name('san-pham.')->group(function () {
        Route::get('/',              [SanPhamController::class, 'index'])->name('index');
        Route::post('/',             [SanPhamController::class, 'store'])->name('store');
        Route::get('/{id}/edit-data',[SanPhamController::class, 'editData'])->name('editData');
        Route::put('/{id}',          [SanPhamController::class, 'update'])->name('update');
        Route::patch('/{id}/toggle', [SanPhamController::class, 'toggleTrangThai'])->name('toggle');
        Route::get('/{id}/lo-nhap',   [SanPhamController::class, 'loNhap'])->name('lo-nhap');
        Route::post('/bao-hang-hong', [SanPhamController::class, 'baoHangHong'])->name('bao-hang-hong');
    });

    // Hóa đơn
    Route::prefix('hoa-don')->name('hoa-don.')->controller(HoaDonController::class)->group(function () {
        Route::get('/',      'index')->name('index');
        Route::get('/{id}',  'show')->name('show');
        Route::delete('/{id}','destroy')->name('destroy');
    });


Route::prefix('don-hang')->name('don-hang.')->controller(\App\Http\Controllers\DonHangController::class)->group(function () {
    
    Route::get('/', 'index')->name('index');
  
    Route::post('/{id}/confirm', 'confirm')->name('confirm');
   
    Route::post('/{id}/cancel', 'cancel')->name('cancel');
});
Route::prefix('phieu-nhap')->name('phieu-nhap.')->controller(PhieuNhapController::class)->group(function () {
 
    Route::get('/', 'index')->name('index');
 
    Route::post('/', 'store')->name('store');
 
    Route::get('/{id}/edit-data', 'editData')->name('edit-data');
 
    Route::put('/{id}', 'update')->name('update');
 
    Route::delete('/{id}', 'destroy')->name('destroy');
 
    Route::post('/{id}/confirm', 'confirm')->name('confirm');
});
Route::get('/bao-cao',               [BaoCaoController::class, 'index'])->name('bao-cao.index');
Route::get('/bao-cao/doanh-thu',     [BaoCaoController::class, 'doanhThu'])->name('bao-cao.doanh-thu');
Route::get('/bao-cao/loi-nhuan',     [BaoCaoController::class, 'index'])->name('bao-cao.loi-nhuan');
Route::get('/bao-cao/san-pham',              [BaoCaoController::class, 'baoCaoSanPham'])->name('bao-cao.san-pham');
Route::get('/bao-cao/san-pham/export',         [BaoCaoController::class, 'exportBaoCaoSanPham'])->name('bao-cao.san-pham.export');
Route::get('/bao-cao/san-pham/{id}/hang-hong', [BaoCaoController::class, 'chiTietHangHong'])->name('bao-cao.san-pham.hang-hong');
Route::get('/bao-cao/san-pham/{id}/chi-tiet',  [BaoCaoController::class, 'chiTietSanPham'])->name('bao-cao.san-pham.chi-tiet');
Route::get('/bao-cao/ton-kho',       [BaoCaoController::class, 'index'])->name('bao-cao.ton-kho');
Route::get('/bao-cao/khach-hang',    [BaoCaoController::class, 'index'])->name('bao-cao.khach-hang');



});
Route::middleware(['auth', 'role:SHIPPER'])->group(function () {
    Route::get('/shipper/dashboard', [ShipperController::class, 'dashboard'])->name('shipper.dashboard');
    Route::patch('/shipper/don-hang/{id}/cap-nhat', [ShipperController::class, 'updateStatus'])->name('shipper.update-status');
    Route::get('/shipper/don-hang/{id}/chi-tiet', [NhanDonController::class, 'show'])->name('shipper.don-hang.chi-tiet');
    Route::get('/shipper/profile',[ShipperProfileController::class, 'edit'])->name('shipper.profile.edit');
    Route::patch('/shipper/profile',[ShipperProfileController::class, 'update'])->name('shipper.profile.update');
    Route::patch('/shipper/profile/password', [ShipperProfileController::class, 'updatePassword']) ->name('shipper.profile.password');
    Route::post('/shipper/don-hang/{id}/da-den-noi',[ThongBaoController::class, 'guiThongBao'])->name('shipper.don-hang.da-den-noi');
    Route::get('/shipper/thong-bao', [ShipperThongBaoController::class, 'index'])
    ->name('shipper.thong-bao');
 
    Route::get('/shipper/thong-bao/so-chua-doc', [ShipperThongBaoController::class, 'soChuaDoc'])
    ->name('shipper.thong-bao.so-chua-doc');
 
    Route::patch('/shipper/thong-bao/{id}/xoa', [ShipperThongBaoController::class, 'xoa'])
    ->name('shipper.thong-bao.xoa');
 

    Route::post('/shipper/don-hang/{maHoaDon}/check-timeout', [ShipperThongBaoController::class, 'checkTimeout'])
    ->name('shipper.don-hang.check-timeout');
});



//Khach hàng
Route::get('/customer/dashboard', [CustomerController::class, 'dashboard'])
    ->name('customer.dashboard');
Route::get('/customer/hoa-tuoi', [CustomerController::class, 'hoaTuoi'])
    ->name('customer.hoa-tuoi');
Route::get('/customer/san-pham/{id}', [ChiTietSanPhamController::class, 'show'])
    ->name('customer.san-pham.chi-tiet');

// Các chức năng cần đăng nhập mới dùng được
Route::middleware(['auth', 'role:KHACH_HANG'])->group(function () {
    Route::get('/customer/profile',            [CustomerProfileController::class, 'edit'])
        ->name('customer.profile.edit');
    Route::patch('/customer/profile',          [CustomerProfileController::class, 'update'])
        ->name('customer.profile.update');
    Route::patch('/customer/profile/password', [CustomerProfileController::class, 'updatePassword'])
        ->name('customer.profile.password');
        Route::get('/customer/gio-hang', [GioHangController::class, 'index'])
        ->name('customer.gio-hang');

    Route::post('/customer/gio-hang/add', [GioHangController::class, 'add'])
        ->name('customer.gio-hang.add');

    Route::post('/customer/gio-hang/update', [GioHangController::class, 'update'])
        ->name('customer.gio-hang.update');

    Route::post('/customer/gio-hang/remove', [GioHangController::class, 'remove'])
        ->name('customer.gio-hang.remove');

    Route::post('/customer/gio-hang/apply-points', [GioHangController::class, 'applyPoints'])
        ->name('customer.gio-hang.apply-points');
    Route::post('/customer/gio-hang/checkout', [GioHangController::class, 'checkout'])
        ->name('customer.gio-hang.checkout');
    Route::get('/customer/thanh-toan', [ThanhToanController::class, 'index'])->name('customer.thanh-toan');

    Route::post('/customer/thanh-toan', [ThanhToanController::class, 'store'])->name('customer.thanh-toan.store');
    Route::get('/customer/thong-bao',[CustomerThongBaoController::class, 'index'])->name('customer.thong-bao');

    Route::get('/customer/thong-bao/so-chua-doc',[CustomerThongBaoController::class, 'soChuaDoc'])->name('customer.thong-bao.so-chua-doc');

    Route::patch('/customer/thong-bao/{id}/xoa',[CustomerThongBaoController::class, 'xoa'])->name('customer.thong-bao.xoa');
});
//
Route::post('/customer/mua-ngay', [GioHangController::class, 'buyNow'])
    ->name('customer.mua-ngay');
// tin tức chi tiết
Route::get('/customer/tin-tuc', function () {
    return view('customer.TinTuc');
})->name('customer.tin-tuc');
Route::get('/customer/tin-tuc/{slug}', function ($slug) {
    return view('customer.ChiTietTinTuc', compact('slug'));
})->name('customer.tin-tuc.chi-tiet');
// Phụ kiện
Route::get('/customer/phu-kien', [CustomerController::class, 'phuKien'])
    ->name('customer.phu-kien');
    // Quà tặng
Route::get('/customer/qua-tang', [CustomerController::class, 'quaTang'])
    ->name('customer.qua-tang');
// gioi thieu
Route::get('/customer/gioi-thieu', [CustomerController::class, 'gioiThieu'])
    ->name('customer.gioi-thieu');
// Liên hệ
Route::get('/customer/lien-he', [CustomerController::class, 'lienHe'])
    ->name('customer.lien-he');