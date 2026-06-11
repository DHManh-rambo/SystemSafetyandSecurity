<?php

namespace App\Models;

use App\Models\GioHang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NguoiDung extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'nguoi_dung';
    protected $primaryKey = 'ma_nguoi_dung';
    public $incrementing = true; 
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'ten_dang_nhap',
        'mat_khau',
        'vai_tro',
    ];

    protected $hidden = [
        'mat_khau',
    ];

    protected $casts = [
        'vai_tro' => 'string',
    ];

    public function getAuthIdentifierName()
    {
        return 'ten_dang_nhap';   
    }
    public function getAuthPassword()
    {
        return $this->mat_khau;
    }

    public function khachHang()
    {
        return $this->hasOne(KhachHang::class, 'ma_khach_hang', 'ma_nguoi_dung');
    }

    public function nhanVien()
    {
        return $this->hasOne(NhanVien::class, 'ma_nhan_vien', 'ma_nguoi_dung');
    }

    public function isKhachHang()
    {
        return $this->vai_tro === 'KHACH_HANG';
    }

    public function isNhanVien()
    {
        return in_array($this->vai_tro, ['NHAN_VIEN', 'SHIPPER']);
    }

    public function isAdmin()
    {
        return $this->vai_tro === 'ADMIN';
    }

    public function setMatKhauAttribute($value)
    {
        $this->attributes['mat_khau'] = bcrypt($value);
    }

public function cartItems(): HasMany
{
    return $this->hasMany(GioHang::class, 'ma_nguoi_dung', 'ma_nguoi_dung');
}
}