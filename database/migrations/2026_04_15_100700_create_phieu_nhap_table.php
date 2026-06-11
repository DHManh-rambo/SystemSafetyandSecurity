<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('phieu_nhap', function (Blueprint $table) {

            $table->id('ma_phieu_nhap');

            $table->dateTime('ngay_nhap')->nullable();

            $table->unsignedBigInteger('ma_nhan_vien');

            $table->string('ten_nha_cung_cap', 100)->nullable();

            $table->string('so_dien_thoai_ncc', 15)->nullable();

            $table->string('email_ncc', 100)->nullable();

            $table->text('dia_chi_ncc')->nullable();

            $table->enum('trang_thai', [
                'DRAFT',
                'CONFIRMED'
            ])->default('DRAFT');

            $table->timestamps();

            $table->foreign('ma_nhan_vien')
                ->references('ma_nhan_vien')
                ->on('nhan_vien');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phieu_nhap');
    }
};