<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tạo bảng bao_cao_hang_hong (tên file migration bị đặt sai, thực ra là create)
        Schema::create('bao_cao_hang_hong', function (Blueprint $table) {
            $table->id('ma_bao_cao');
            $table->unsignedBigInteger('ma_san_pham')->nullable();
            $table->unsignedBigInteger('ma_chi_tiet_nhap')->nullable();
            $table->integer('so_luong_hong')->default(0);
            $table->text('ly_do')->nullable();
            $table->timestamps();

            $table->foreign('ma_san_pham')
                  ->references('ma_san_pham')
                  ->on('san_pham')
                  ->onDelete('set null');

            $table->foreign('ma_chi_tiet_nhap')
                  ->references('ma_chi_tiet_nhap')
                  ->on('chi_tiet_nhap')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bao_cao_hang_hong');
    }
};