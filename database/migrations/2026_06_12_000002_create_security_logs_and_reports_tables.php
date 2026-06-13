<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->integer('ma_nguoi_dung')->nullable();
            $table->string('route', 255);
            $table->string('request_method', 10);
            $table->text('payload')->nullable();
            $table->string('attack_type', 50); // SQLi, XSS, Path_Traversal, Brute_Force, Malicious_Upload, DOS
            $table->enum('severity', ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL']);
            $table->integer('threat_score');
            $table->string('action_taken', 50); // LOG_ONLY, BLOCKED_IP, LOCKED_ACCOUNT, PENDING_MODERATION
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('ma_nguoi_dung')
                  ->references('ma_nguoi_dung')
                  ->on('nguoi_dung')
                  ->onDelete('set null');
        });

        Schema::create('security_reports', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('summary'); // Báo cáo do AI viết
            $table->integer('total_attacks')->default(0);
            $table->integer('critical_events')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_reports');
        Schema::dropIfExists('security_logs');
    }
};
