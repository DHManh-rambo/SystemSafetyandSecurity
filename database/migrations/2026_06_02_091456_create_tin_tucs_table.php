<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tin_tuc', function (Blueprint $table) {
    $table->id('ma_tin_tuc');
    $table->string('tieu_de');
    $table->string('hinh_anh')->nullable();
    $table->text('tom_tat')->nullable();
    $table->longText('noi_dung')->nullable();
    $table->enum('trang_thai', ['HIEN_THI', 'AN'])->default('HIEN_THI');
    $table->timestamp('ngay_dang')->useCurrent();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tin_tucs');
    }
};
