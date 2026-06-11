<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nguoi_dung', function (Blueprint $table) {
            $table->id('ma_nguoi_dung');
            $table->string('ten_dang_nhap')->unique();
            $table->string('mat_khau');
            $table->enum('vai_tro', ['KHACH_HANG','NHAN_VIEN','ADMIN','SHIPPER']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nguoi_dung');
    }
};