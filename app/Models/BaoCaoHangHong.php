<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaoCaoHangHong extends Model
{
    use HasFactory;

    protected $table = 'bao_cao_hang_hong';

    protected $primaryKey = 'ma_bao_cao';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'ma_san_pham',
        'ma_chi_tiet_nhap',   
        'ma_nhan_vien',
        'so_luong_hong',
        'ly_do',
        'ghi_chu',
        'thoi_gian_bao_cao',
    ];

    

    public function sanPham()
    {
        return $this->belongsTo(
            SanPham::class,
            'ma_san_pham',
            'ma_san_pham'
        );
    }

    public function nhanVien()
    {
        return $this->belongsTo(
            NhanVien::class,
            'ma_nhan_vien',
            'ma_nhan_vien'
        );
    }

    
    public function chiTietNhap()
    {
        return $this->belongsTo(
            ChiTietNhap::class,
            'ma_chi_tiet_nhap',
            'ma_chi_tiet_nhap'
        );
    }
}