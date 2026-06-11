<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietNhap extends Model
{
    use HasFactory;

    protected $table = 'chi_tiet_nhap';
    protected $primaryKey = 'ma_chi_tiet_nhap';
    public $incrementing = true;
    protected $keyType = 'int';

    
    public $timestamps = true;

    protected $fillable = [
        'ma_phieu_nhap',
        'ma_san_pham',
        'so_luong',
        'gia_nhap',
        'gia_ban',          
        'so_luong_con_lai', 
    ];

    protected $casts = [
        'so_luong' => 'integer',
        'gia_nhap' => 'decimal:2',
        'gia_ban' => 'decimal:2',
        'so_luong_con_lai' => 'integer',
    ];

    public function phieuNhap()
    {
        return $this->belongsTo(PhieuNhap::class, 'ma_phieu_nhap', 'ma_phieu_nhap');
    }

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'ma_san_pham', 'ma_san_pham');
    }
}