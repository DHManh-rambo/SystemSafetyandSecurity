<?php

use App\Http\Controllers\NguoiDungController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KhachHangController;
use App\Http\Controllers\NhanVienController;
use App\Http\Controllers\SanPhamController;
use App\Http\Controllers\HoaDonController;
use App\Http\Controllers\DonHangController;
use App\Http\Controllers\PhieuNhapController;
use App\Http\Controllers\BaoCaoController;
use App\Http\Controllers\Shipper\ShipperController;
Route::get('/', function () {
    return view('welcome');
});

Route::prefix('nguoi-dung')->name('nguoi-dung.')->group(function () {
    Route::get('/', [NguoiDungController::class, 'index'])->name('index');
    Route::post('/', [NguoiDungController::class, 'store'])->name('store');
    Route::put('/{id}', [NguoiDungController::class, 'update'])->name('update');
    Route::delete('/{id}', [NguoiDungController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/edit-data', [NguoiDungController::class, 'editData'])->name('edit-data');
});

Route::prefix('khach-hang')->name('khach-hang.')->group(function () {
    Route::get('/', [KhachHangController::class, 'index'])->name('index');
    Route::put('/{id}', [KhachHangController::class, 'update'])->name('update');
    Route::delete('/{id}', [KhachHangController::class, 'destroy'])->name('destroy');
});
Route::prefix('nhan-vien')->name('nhan-vien.')->controller(NhanVienController::class)->group(function () {

        Route::get('/', 'index')->name('index');

        Route::put('/{ma_nhan_vien}', 'update')->name('update');

        Route::delete('/{ma_nhan_vien}', 'destroy')->name('destroy');

        Route::post('/{ma}/tra-tien', 'payback')->name('payback');
    });
Route::get('/san-pham', [SanPhamController::class, 'index'])
    ->name('san-pham.index');
 

Route::post('/san-pham', [SanPhamController::class, 'store'])
    ->name('san-pham.store');
 

Route::get('/san-pham/{id}/edit-data', [SanPhamController::class, 'editData'])
    ->name('san-pham.editData');
 

Route::put('/san-pham/{id}', [SanPhamController::class, 'update'])
    ->name('san-pham.update');

Route::patch('/san-pham/{id}/toggle', [SanPhamController::class, 'toggleTrangThai'])
    ->name('san-pham.toggle');

Route::prefix('hoa-don')->name('hoa-don.')->controller(HoaDonController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{id}', 'show')->name('show');
    Route::delete('/{id}', 'destroy')->name('destroy');
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
Route::get('/bao-cao/doanh-thu/export', [BaoCaoController::class, 'exportDoanhThu'])
    ->name('bao-cao.doanh-thu.export');
Route::get('/bao-cao/loi-nhuan',     [BaoCaoController::class, 'loiNhuan'])->name('bao-cao.loi-nhuan');
Route::get('/bao-cao/san-pham',         [BaoCaoController::class, 'baoCaoSanPham'])->name('bao-cao.san-pham');
Route::get('/bao-cao/san-pham/export',  [BaoCaoController::class, 'exportBaoCaoSanPham'])->name('bao-cao.san-pham.export');
Route::get('/bao-cao/san-pham/{id}/chi-tiet', [BaoCaoController::class, 'chiTietSanPham'])
    ->name('bao-cao.san-pham.chi-tiet');
Route::get('/bao-cao/san-pham/{id}/hang-hong', [BaoCaoController::class, 'chiTietHangHong'])->name('bao-cao.san-pham.hang-hong');
Route::get('/bao-cao/ton-kho',       [BaoCaoController::class, 'tonKho'])->name('bao-cao.ton-kho');
Route::get('/bao-cao/khach-hang',    [BaoCaoController::class, 'khachHang'])->name('bao-cao.khach-hang');
Route::post('/bao-cao/hang-hong',    [BaoCaoController::class, 'baoHangHong'])->name('bao-cao.bao-hang-hong');


Route::get('/shipper/dashboard', [ShipperController::class, 'dashboard'])->name('shipper.dashboard');
Route::patch('/shipper/don-hang/{id}/cap-nhat', [ShipperController::class, 'updateStatus'])->name('shipper.update-status');