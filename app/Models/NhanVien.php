<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NhanVien extends Model
{
    use HasFactory;

    protected $table = 'nhan_vien';
    protected $primaryKey = 'ma_nhan_vien';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ma_nhan_vien',
        'ten_nhan_vien',
        'email',
        'so_dien_thoai',
        'chuc_vu',
        'cong_viec',
        'luong',
    ];

    protected $casts = [
        'luong' => 'decimal:2',
    ];

   
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'ma_nhan_vien', 'ma_nguoi_dung');
    }

    
    public function phieuNhaps()
    {
        return $this->hasMany(PhieuNhap::class, 'ma_nhan_vien', 'ma_nhan_vien');
    }

    
    public function hoaDonsGiao()
    {
        return $this->hasMany(HoaDon::class, 'ma_nhan_vien_giao', 'ma_nhan_vien');
    }
}