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
    Schema::table('san_pham', function (Blueprint $table) {
        $table->decimal('gia_ban_hien_tai', 12, 2)
              ->nullable()
              ->after('so_luong');
    });
}

public function down(): void
{
    Schema::table('san_pham', function (Blueprint $table) {
        $table->dropColumn('gia_ban_hien_tai');
    });
}
};
