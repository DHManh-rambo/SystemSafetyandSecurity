<?php
// d:\LongWork\BMUD-BTL\SystemSafetyandSecurity\tests\Feature\SecurityShieldTest.php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SecurityShieldTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear blocked IPs and request counts before each test
        Cache::flush();
    }

    /**
     * Test SQL Injection detection (Mocking AI WAF API)
     */
    public function test_sql_injection_is_blocked_by_waf(): void
    {
        // Mock Python FastAPI response for SQLi payload
        Http::fake([
            '127.0.0.1:5000/analyze-payload' => Http::response([
                'is_attack' => true,
                'confidence' => 0.95,
                'attack_type' => 'SQLi'
            ], 200)
        ]);

        $response = $this->get('/customer/hoa-tuoi?search=union+select');

        // Middleware should abort with 403 Forbidden
        $response->assertStatus(403);
        $this->assertStringContainsString('AI WAF phát hiện hành vi nghi ngờ tấn công SQLi', $response->getContent());
    }

    /**
     * Test Cross-Site Scripting (XSS) detection (Mocking AI WAF API)
     */
    public function test_xss_is_blocked_by_waf(): void
    {
        // Mock Python FastAPI response for XSS payload
        Http::fake([
            '127.0.0.1:5000/analyze-payload' => Http::response([
                'is_attack' => true,
                'confidence' => 0.90,
                'attack_type' => 'XSS'
            ], 200)
        ]);

        $response = $this->get('/customer/hoa-tuoi?search=<script>alert(1)</script>');

        $response->assertStatus(403);
        $this->assertStringContainsString('AI WAF phát hiện hành vi nghi ngờ tấn công XSS', $response->getContent());
    }

    /**
     * Test Rate Limiting (DOS defense)
     */
    public function test_rate_limiting_blocks_dos_attack(): void
    {
        // Mock Python WAF to return safe for standard requests
        Http::fake([
            '127.0.0.1:5000/*' => Http::response([
                'is_attack' => false,
                'confidence' => 1.0,
                'attack_type' => 'None'
            ], 200)
        ]);

        // Send 60 requests (within limit)
        for ($i = 0; $i < 60; $i++) {
            $response = $this->get('/customer/dashboard');
            $response->assertStatus(200);
        }

        // The 61st request should trigger the Rate Limiter (DOS block)
        $response = $this->get('/customer/dashboard');
        $response->assertStatus(429); // Too Many Requests
    }

    /**
     * Test Upload of executable file (.exe) is blocked instantly
     */
    public function test_malicious_file_upload_banned_instantly(): void
    {
        $file = \Illuminate\Http\UploadedFile::fake()->create('shell.exe', 100);

        $response = $this->post('/san-pham', [
            'hinh_anh' => $file,
            'ten_san_pham' => 'Hoa Test',
            'loai_san_pham' => 'HOA_TUOI'
        ]);

        $response->assertStatus(403);
        $this->assertStringContainsString('Phát hiện hành vi nguy hại nghiêm trọng', $response->getContent());
    }
}
