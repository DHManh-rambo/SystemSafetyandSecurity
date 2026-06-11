<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hoa_don', function (Blueprint $table) {

            $table->id('ma_hoa_don');

            $table->unsignedBigInteger('ma_khach_hang');

            $table->dateTime('ngay_dat')->nullable();

            $table->decimal('tong_tien', 10, 2)->default(0);

            $table->enum('trang_thai', [
                'PENDING',
                'CONFIRMED',
                'SHIPPING',
                'DELIVERED',
                'CANCELLED'
            ])->default('PENDING');

            $table->enum('trang_thai_thanh_toan', [
                'CHUA_THANH_TOAN',
                'DA_THANH_TOAN',
                'DA_NOP'
            ])->default('CHUA_THANH_TOAN');

            $table->enum('phuong_thuc_thanh_toan', [
                'COD',
                'NGAN_HANG'
            ])->nullable();

            $table->text('dia_chi_giao')->nullable();

            $table->string('so_dien_thoai', 15)->nullable();

            $table->dateTime('ngay_giao')->nullable();

            $table->unsignedBigInteger('ma_nhan_vien_giao')->nullable();

            $table->foreign('ma_khach_hang')
                ->references('ma_khach_hang')
                ->on('khach_hang');

            $table->foreign('ma_nhan_vien_giao')
                ->references('ma_nhan_vien')
                ->on('nhan_vien')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoa_don');
    }
};