<?php
// d:\LongWork\BMUD-BTL\SystemSafetyandSecurity\app\Http\Middleware\SecurityShield.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Models\SecurityLog;
use Symfony\Component\HttpFoundation\Response;

class SecurityShield
{
    // Cấu hình URL của Python FastAPI AI Service
    private string $aiServiceUrl = 'http://127.0.0.1:5000';

    // Đuôi file cấm tuyệt đối
    private array $bannedExtensions = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'phar', 
        'exe', 'bat', 'sh', 'cmd', 'js', 'jar', 'vbs', 'scr', 'msi', 'com', 'pif'
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        // 1. Kiểm tra IP Blacklist trong Cache
        if (Cache::has("blocked_ip_$ip")) {
            abort(403, 'Địa chỉ IP của bạn tạm thời bị khóa do phát hiện các hành vi nghi ngờ tấn công hoặc phá hoại hệ thống.');
        }

        // 2. Rate Limiting (DOS Prevention) - Giới hạn 60 request/phút
        $rateLimitKey = "req_count_$ip";
        $requestCount = Cache::get($rateLimitKey, 0);

        if ($requestCount === 0) {
            Cache::put($rateLimitKey, 1, 60);
        } else {
            Cache::increment($rateLimitKey);
        }

        if ($requestCount > 60) {
            // Block IP trong 1 giờ
            Cache::put("blocked_ip_$ip", true, 3600);

            // Ghi nhận log DOS
            SecurityLog::create([
                'ip_address' => $ip,
                'ma_nguoi_dung' => Auth::user()?->ma_nguoi_dung,
                'route' => $request->path(),
                'request_method' => $request->method(),
                'payload' => "Tần suất gửi request: $requestCount req/min",
                'attack_type' => 'DOS',
                'severity' => 'HIGH',
                'threat_score' => 85,
                'action_taken' => 'BLOCKED_IP',
            ]);

            abort(429, 'Hệ thống phát hiện tần suất yêu cầu bất thường từ IP của bạn. IP đã bị tạm khóa trong 1 giờ.');
        }

        // 3. Kiểm tra File Tải lên (Malicious File Upload & Steganography Detection)
        if ($request->hasFile(null) || count($request->allFiles()) > 0) {
            foreach ($request->allFiles() as $key => $file) {
                if ($file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $ext = strtolower($file->getClientOriginalExtension());

                    // Kiểm tra 3.1: Đuôi file trong danh sách đen
                    if (in_array($ext, $this->bannedExtensions)) {
                        $this->triggerCriticalLock($request, $ip, 'Malicious_Upload', "Cố gắng tải lên file nguy hại: $originalName", 100);
                    }

                    // Kiểm tra 3.2: Quét nội dung tệp tin tìm mã độc nhị phân (Steganography / Web Shell inside Image)
                    try {
                        $content = file_get_contents($file->getRealPath(), false, null, 0, 8192); // Đọc 8KB đầu tiên
                        if ($content !== false) {
                            $contentLower = strtolower($content);
                            // Quét chữ ký script đặc trưng của PHP/JS
                            if (preg_match('/<\?php/i', $contentLower) || 
                                preg_match('/<\?=/i', $contentLower) || 
                                preg_match('/<script/i', $contentLower) || 
                                preg_match('/eval\s*\(/i', $contentLower) || 
                                preg_match('/shell_exec\s*\(/i', $contentLower) || 
                                preg_match('/system\s*\(/i', $contentLower)) {
                                
                                $this->triggerCriticalLock(
                                    $request, 
                                    $ip, 
                                    'Malicious_Upload', 
                                    "Phát hiện mã độc nhúng trong tệp tin: $originalName (Polyglot/Steganography)", 
                                    100
                                );
                            }
                        }
                    } catch (\Exception $e) {
                        // Bỏ qua nếu lỗi đọc file
                    }
                }
            }
        }

        // 4. Kiểm tra Payload dữ liệu bằng AI WAF Service (SQLi / XSS / Path Traversal)
        $inputs = $request->except(['_token', 'password', 'password_confirmation', 'mat_khau_cu', 'mat_khau_moi', 'mat_khau_moi_confirmation']);
        if (!empty($inputs)) {
            $payloadText = json_encode($inputs, JSON_UNESCAPED_UNICODE);

            if (strlen($payloadText) > 4) { // Chỉ kiểm tra khi có dữ liệu đầu vào thực sự
                $aiResult = $this->queryAiWaf($payloadText);

                if ($aiResult && $aiResult['is_attack']) {
                    $attackType = $aiResult['attack_type'];
                    $confidence = $aiResult['confidence'];
                    $score = intval($confidence * 100);

                    // Quyết định hành động:
                    // Nếu độ tin cậy trung bình-cao (>= 58% từ mô hình AI), tiến hành khóa tài khoản chờ duyệt & block IP
                    if ($score >= 58) {
                        $action = 'PENDING_MODERATION';
                        Cache::put("blocked_ip_$ip", true, 1800); // Khóa IP 30 phút

                        // Ghi log bảo mật
                        SecurityLog::create([
                            'ip_address' => $ip,
                            'ma_nguoi_dung' => Auth::user()?->ma_nguoi_dung,
                            'route' => $request->path(),
                            'request_method' => $request->method(),
                            'payload' => $payloadText,
                            'attack_type' => $attackType,
                            'severity' => 'HIGH',
                            'threat_score' => $score,
                            'action_taken' => $action,
                        ]);

                        // Khóa tài khoản của người dùng nếu đã đăng nhập và chuyển sang hàng chờ duyệt
                        $hasUser = Auth::check();
                        if ($hasUser) {
                            $user = Auth::user();
                            $user->update(['trang_thai' => 'CHO_DUYET']);
                            Auth::logout();
                            $request->session()->invalidate();
                            $request->session()->regenerateToken();
                        }

                        $message = "Yêu cầu của bạn bị từ chối do AI WAF phát hiện hành vi nghi ngờ tấn công $attackType (Độ tin cậy: " . ($score) . "%).";
                        if ($hasUser) {
                            $message .= " Tài khoản của bạn đã được chuyển sang trạng thái chờ phê duyệt.";
                        }
                        abort(403, $message);
                    } else {
                        // Ghi log mức độ cảnh báo nhẹ
                        SecurityLog::create([
                            'ip_address' => $ip,
                            'ma_nguoi_dung' => Auth::user()?->ma_nguoi_dung,
                            'route' => $request->path(),
                            'request_method' => $request->method(),
                            'payload' => $payloadText,
                            'attack_type' => $attackType,
                            'severity' => 'MEDIUM',
                            'threat_score' => $score,
                            'action_taken' => 'LOG_ONLY',
                        ]);
                    }
                }
            }
        }

        return $next($request);
    }

    /**
     * Khóa tài khoản lập tức và chặn IP (dành cho Critical Events)
     */
    private function triggerCriticalLock(Request $request, string $ip, string $attackType, string $payload, int $score): void
    {
        // Khóa IP 24 giờ
        Cache::put("blocked_ip_$ip", true, 86400);

        // Ghi log bảo mật
        SecurityLog::create([
            'ip_address' => $ip,
            'ma_nguoi_dung' => Auth::user()?->ma_nguoi_dung,
            'route' => $request->path(),
            'request_method' => $request->method(),
            'payload' => $payload,
            'attack_type' => $attackType,
            'severity' => 'CRITICAL',
            'threat_score' => $score,
            'action_taken' => 'LOCKED_ACCOUNT',
        ]);

        $hasUser = Auth::check();
        if ($hasUser) {
            $user = Auth::user();
            $user->update(['trang_thai' => 'CHO_DUYET']);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $message = "Phát hiện hành vi nguy hại nghiêm trọng! Địa chỉ IP đã bị khóa trong 24 giờ.";
        if ($hasUser) {
            $message .= " Tài khoản của bạn đã bị vô hiệu hóa để chờ quản trị viên xác minh.";
        }
        abort(403, $message);
    }

    /**
     * Gọi API WAF tới FastAPI Python, có dự phòng (Fallback) bằng Regex
     */
    private function queryAiWaf(string $text): ?array
    {
        try {
            $response = Http::timeout(2) // Giới hạn timeout 2 giây để tránh treo web
                            ->post($this->aiServiceUrl . '/analyze-payload', [
                                'text' => $text
                            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // Service Python đang offline -> Dùng cơ chế dự phòng Heuristics/Regex cục bộ
            return $this->fallbackRegexWaf($text);
        }

        return $this->fallbackRegexWaf($text);
    }

    /**
     * Phòng thủ dự phòng bằng Heuristics Regex nếu AI Service bị ngắt kết nối
     */
    private function fallbackRegexWaf(string $text): array
    {
        $textLower = strtolower($text);

        // 1. Check SQLi signatures
        if (preg_match('/union\s+select|select\s+.*from|insert\s+into|delete\s+from|drop\s+table|or\s+\d+=\d+|or\s+\'\w+\'=\'\w+\'/i', $textLower)) {
            return [
                'is_attack' => true,
                'confidence' => 0.95,
                'attack_type' => 'SQLi'
            ];
        }

        // 2. Check XSS signatures
        if (preg_match('/<script|javascript:|onerror\s*=|onload\s*=|alert\s*\(|confirm\s*\(|<img\s+src/i', $textLower)) {
            return [
                'is_attack' => true,
                'confidence' => 0.95,
                'attack_type' => 'XSS'
            ];
        }

        // 3. Check Path Traversal
        if (preg_match('/\.\.\/|\.\.\\\\|etc\/passwd|win\.ini|\.env/i', $textLower)) {
            return [
                'is_attack' => true,
                'confidence' => 0.90,
                'attack_type' => 'Path_Traversal'
            ];
        }

        return [
            'is_attack' => false,
            'confidence' => 1.0,
            'attack_type' => 'None'
        ];
    }
}
