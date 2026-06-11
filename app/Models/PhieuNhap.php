<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhieuNhap extends Model
{
    use HasFactory;

    protected $table = 'phieu_nhap';
    protected $primaryKey = 'ma_phieu_nhap';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'ngay_nhap',
        'ma_nhan_vien',
        'ten_nha_cung_cap',
        'so_dien_thoai_ncc',
        'email_ncc',
        'dia_chi_ncc',
        'trang_thai',
    ];

    protected $casts = [
        'ngay_nhap' => 'datetime',
    ];

    public function nhanVien()
    {
        return $this->belongsTo(NhanVien::class, 'ma_nhan_vien', 'ma_nhan_vien');
    }

    public function chiTietNhaps()
    {
        return $this->hasMany(ChiTietNhap::class, 'ma_phieu_nhap', 'ma_phieu_nhap');
    }
}