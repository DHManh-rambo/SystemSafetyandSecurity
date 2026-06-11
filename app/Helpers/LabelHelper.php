<?php

namespace App\Helpers;

class LabelHelper
{
    /** Map mã loại sản phẩm → nhãn hiển thị */
    private static array $loaiSanPhamMap = [
        'HOA_TUOI'       => 'Hoa tươi',
        'HOA_PHU'        => 'Hoa phụ',
        'CHAU_HOA_TUOI'  => 'Chậu hoa tươi',
        'CHAU_HOA_KHO'   => 'Chậu hoa khô',
        'HOA_KHO'        => 'Hoa khô',
        'PHU_KIEN'       => 'Phụ kiện',
        'QUA_TANG'       => 'Quà tặng',
    ];

    public static function loaiSanPham(?string $ma): string
    {
        return self::$loaiSanPhamMap[$ma] ?? ($ma ?? '-');
    }
}
