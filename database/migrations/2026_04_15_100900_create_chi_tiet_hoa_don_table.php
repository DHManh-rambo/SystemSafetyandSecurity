<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('chi_tiet_hoa_don', function (Blueprint $table) {

            $table->id('ma_chi_tiet');

            $table->unsignedBigInteger('ma_hoa_don');

            $table->unsignedBigInteger('ma_san_pham');

            $table->unsignedBigInteger('ma_chi_tiet_nhap')->nullable();

            $table->integer('so_luong');

            $table->decimal('gia_nhap_snapshot', 10, 2);

            $table->decimal('gia_ban_snapshot', 10, 2);

            $table->unique([
                'ma_hoa_don',
                'ma_san_pham',
                'ma_chi_tiet_nhap'
            ]);

            $table->foreign('ma_hoa_don')
                ->references('ma_hoa_don')
                ->on('hoa_don')
                ->cascadeOnDelete();

            $table->foreign('ma_san_pham')
                ->references('ma_san_pham')
                ->on('san_pham');

            $table->foreign('ma_chi_tiet_nhap')
                ->references('ma_chi_tiet_nhap')
                ->on('chi_tiet_nhap')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_hoa_don');
    }
};