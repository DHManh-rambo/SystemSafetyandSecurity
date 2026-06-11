<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HoaDon extends Model
{
    use HasFactory;

    protected $table = 'hoa_don';
    protected $primaryKey = 'ma_hoa_don';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'ma_khach_hang',
        'ngay_dat',
        'tong_tien',
        'trang_thai',
        'trang_thai_thanh_toan',
        'phuong_thuc_thanh_toan',
        'dia_chi_giao',
        'so_dien_thoai',
        'ngay_giao',
        'ma_nhan_vien_giao',
    ];

    protected $casts = [
        'tong_tien' => 'decimal:2',
        'ngay_dat' => 'datetime',
        'ngay_giao' => 'datetime',
    ];

    public function chiTietHoaDon()
    {
        return $this->hasMany(ChiTietHoaDon::class, 'ma_hoa_don', 'ma_hoa_don');
    }

    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class, 'ma_khach_hang', 'ma_khach_hang');
    }

    public function shipper()
    {
        return $this->belongsTo(NhanVien::class, 'ma_nhan_vien_giao', 'ma_nhan_vien');
    }
}