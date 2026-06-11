<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('khach_hang', function (Blueprint $table) {
            $table->unsignedBigInteger('ma_khach_hang')->primary();
            $table->string('ten_khach_hang');
            $table->string('so_dien_thoai', 15);
            $table->string('email')->nullable();
            $table->text('dia_chi')->nullable();
            $table->integer('diem_tich_luy')->default(0);

            $table->foreign('ma_khach_hang')
                  ->references('ma_nguoi_dung')
                  ->on('nguoi_dung')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khach_hang');
    }
};