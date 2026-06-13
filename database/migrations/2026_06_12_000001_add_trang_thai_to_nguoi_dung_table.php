<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->enum('trang_thai', ['HOAT_DONG', 'KHOA', 'CHO_DUYET'])->default('HOAT_DONG')->after('vai_tro');
        });
    }

    public function down(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->dropColumn('trang_thai');
        });
    }
};
