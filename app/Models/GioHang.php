<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GioHang extends Model
{
    protected $table = 'gio_hang';

    protected $primaryKey = 'ma_gio_hang';

    public $timestamps = false;

    protected $fillable = [
        'ma_nguoi_dung',
        'ma_san_pham',
        'so_luong',
    ];
}