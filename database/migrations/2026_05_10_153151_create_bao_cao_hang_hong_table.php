<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('bao_cao_hang_hong', function (Blueprint $table) {

            
            $table->unsignedBigInteger('ma_chi_tiet_nhap')
                  ->nullable()
                  ->after('ma_san_pham');

            $table->foreign('ma_chi_tiet_nhap')
                  ->references('ma_chi_tiet_nhap')
                  ->on('chi_tiet_nhap')
                  ->onDelete('set null'); 
        });
    }

    public function down(): void
    {
        Schema::table('bao_cao_hang_hong', function (Blueprint $table) {
            $table->dropForeign(['ma_chi_tiet_nhap']);
            $table->dropColumn('ma_chi_tiet_nhap');
        });
    }
};