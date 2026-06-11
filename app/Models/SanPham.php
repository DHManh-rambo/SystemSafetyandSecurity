<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SanPham extends Model
{
    use HasFactory;

    protected $table = 'san_pham';
    protected $primaryKey = 'ma_san_pham';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false; 

    protected $fillable = [
        'ten_san_pham',
        'so_luong',
        'loai_san_pham',
        'mo_ta',
        'hinh_anh',
        'trang_thai',
        'gia_ban_hien_tai',
    ];

    protected $casts = [
    'so_luong' => 'integer',
    'gia_ban_hien_tai' => 'float',
    'loai_san_pham' => 'string',
    'trang_thai' => 'string',
];


    public function chiTietHoaDons()
    {
        return $this->hasMany(ChiTietHoaDon::class, 'ma_san_pham', 'ma_san_pham');
    }

    public function chiTietNhaps()
    {
        return $this->hasMany(ChiTietNhap::class, 'ma_san_pham', 'ma_san_pham');
    }

    public function getAnhAttribute(): string
    {
        return asset($this->hinh_anh ?? 'img/default.jpg');
    }


    public function scopeDangBan($query)
    {
        return $query->where('trang_thai', 'DANG_BAN');
    }

    public function scopeNgungBan($query)
    {
        return $query->where('trang_thai', 'NGUNG_BAN');
    }

    public function scopeTheoLoai($query, string $loai)
    {
        return $query->where('loai_san_pham', $loai);
    }
}