<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TinTuc extends Model
{
    protected $table = 'tin_tuc';

    protected $primaryKey = 'ma_tin_tuc';

    public $timestamps = false;

    protected $fillable = [
        'tieu_de',
        'hinh_anh',
        'tom_tat',
        'noi_dung',
        'trang_thai',
        'ngay_dang'
    ];
}