<?php

use Illuminate\Support\Facades\DB;

Route::get('/sanpham', function () {
    $data = DB::table('san_pham')->get();

    return response()->json($data);
});
Route::get('/hoadon', function () {
    $data = DB::table('hoa_don')->get();

    return response()->json($data);
});