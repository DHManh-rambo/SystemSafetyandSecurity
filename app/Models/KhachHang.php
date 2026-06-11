<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KhachHang extends Model
{
    use HasFactory;

    protected $table = 'khach_hang';
    protected $primaryKey = 'ma_khach_hang';
    public $incrementing = false; 
    protected $keyType = 'int';
    public $timestamps = false; 

    protected $fillable = [
        'ma_khach_hang',
        'ten_khach_hang',
        'so_dien_thoai',
        'email',
        'dia_chi',
        'diem_tich_luy',
    ];

    protected $casts = [
        'diem_tich_luy' => 'integer',
    ];

   
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'ma_khach_hang', 'ma_nguoi_dung');
    }

    public function hoaDons()
    {
        return $this->hasMany(HoaDon::class, 'ma_khach_hang', 'ma_khach_hang');
    }
}