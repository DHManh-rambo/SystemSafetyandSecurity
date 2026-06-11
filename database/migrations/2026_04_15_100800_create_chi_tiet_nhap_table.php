<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('chi_tiet_nhap', function (Blueprint $table) {

            $table->id('ma_chi_tiet_nhap');

            $table->unsignedBigInteger('ma_phieu_nhap');

            $table->unsignedBigInteger('ma_san_pham');

            $table->integer('so_luong');

            $table->decimal('gia_nhap', 10, 2);

            $table->decimal('gia_ban', 10, 2);

            $table->integer('so_luong_con_lai');

            $table->timestamps();

            $table->unique([
                'ma_phieu_nhap',
                'ma_san_pham'
            ]);

            $table->foreign('ma_phieu_nhap')
                ->references('ma_phieu_nhap')
                ->on('phieu_nhap')
                ->cascadeOnDelete();

            $table->foreign('ma_san_pham')
                ->references('ma_san_pham')
                ->on('san_pham');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_nhap');
    }
};