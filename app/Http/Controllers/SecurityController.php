<?php
// d:\LongWork\BMUD-BTL\SystemSafetyandSecurity\app\Http\Controllers\SecurityController.php

namespace App\Http\Controllers;

use App\Models\SecurityLog;
use App\Models\SecurityReport;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class SecurityController extends Controller
{
    /**
     * Security Dashboard main view (Center of IPS/IDS stats)
     */
    public function dashboard()
    {
        // 1. Thống kê số lượng tổng quan
        $totalLogs = SecurityLog::count();
        $severityCounts = SecurityLog::select('severity', DB::raw('count(*) as count'))
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        $severityCounts = array_merge([
            'LOW' => 0, 'MEDIUM' => 0, 'HIGH' => 0, 'CRITICAL' => 0
        ], $severityCounts);

        $attackTypeCounts = SecurityLog::select('attack_type', DB::raw('count(*) as count'))
            ->groupBy('attack_type')
            ->pluck('count', 'attack_type')
            ->toArray();

        // 2. Lấy dữ liệu biểu đồ tấn công theo ngày (7 ngày gần đây)
        $chartData = SecurityLog::select(DB::raw('DATE(created_at) as date'), 'attack_type', DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date', 'attack_type')
            ->orderBy('date', 'asc')
            ->get();

        // Định dạng dữ liệu cho Chart.js
        $dates = [];
        $attackSeries = [];
        foreach ($chartData as $row) {
            $dates[$row->date] = true;
            $attackSeries[$row->attack_type][$row->date] = $row->count;
        }
        $dates = array_keys($dates);
        sort($dates);

        // 3. Danh sách các IP bị tấn công nhiều nhất
        $topAttackerIps = SecurityLog::select('ip_address', DB::raw('count(*) as count'), DB::raw('max(created_at) as last_attack'))
            ->groupBy('ip_address')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        return view('admin.security_dashboard', compact(
            'totalLogs', 'severityCounts', 'attackTypeCounts', 'dates', 'attackSeries', 'topAttackerIps'
        ));
    }

    /**
     * Xem Logs & Quản lý IP bị Block
     */
    public function logs(Request $request)
    {
        $query = SecurityLog::with('nguoiDung');

        if ($request->filled('attack_type')) {
            $query->where('attack_type', $request->attack_type);
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('ip')) {
            $query->where('ip_address', 'like', '%' . $request->ip . '%');
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(15);

        // Lấy danh sách các IP duy nhất trong logs để kiểm tra trạng thái block trong cache
        $uniqueIps = SecurityLog::select('ip_address')->distinct()->pluck('ip_address')->toArray();
        $blockedIps = [];
        foreach ($uniqueIps as $ip) {
            if (Cache::has("blocked_ip_$ip")) {
                $blockedIps[] = $ip;
            }
        }

        return view('admin.security_logs', compact('logs', 'blockedIps'));
    }

    /**
     * Mở khóa IP bị block
     */
    public function releaseIp(Request $request)
    {
        $request->validate(['ip_address' => 'required|string']);
        $ip = $request->ip_address;

        if (Cache::has("blocked_ip_$ip")) {
            Cache::forget("blocked_ip_$ip");
            // Xóa cache đếm rate limit
            Cache::forget("req_count_$ip");

            return redirect()->back()->with('success', "Đã mở khóa thành công cho địa chỉ IP: $ip");
        }

        return redirect()->back()->with('error', "Địa chỉ IP $ip không nằm trong danh sách chặn.");
    }

    /**
     * Danh sách tài khoản bị khóa / chờ duyệt phê duyệt
     */
    public function pendingUsers()
    {
        $users = NguoiDung::with(['khachHang', 'nhanVien'])
            ->whereIn('trang_thai', ['KHOA', 'CHO_DUYET'])
            ->get();

        return view('admin.security_pending_users', compact('users'));
    }

    /**
     * Phê duyệt kích hoạt lại tài khoản (CHO_DUYET / KHOA -> HOAT_DONG)
     */
    public function approveUser($id)
    {
        $user = NguoiDung::findOrFail($id);
        $user->update(['trang_thai' => 'HOAT_DONG']);

        return redirect()->back()->with('success', "Đã kích hoạt lại thành công cho tài khoản '{$user->ten_dang_nhap}'.");
    }

    /**
     * Khóa cứng tài khoản (HOAT_DONG / CHO_DUYET -> KHOA)
     */
    public function blockUser($id)
    {
        $user = NguoiDung::findOrFail($id);
        $user->update(['trang_thai' => 'KHOA']);

        return redirect()->back()->with('success', "Đã khóa vĩnh viễn tài khoản '{$user->ten_dang_nhap}'.");
    }

    /**
     * Danh sách báo cáo đánh giá an ninh bằng Gemini
     */
    public function reports()
    {
        $reports = SecurityReport::orderBy('created_at', 'desc')->get();
        return view('admin.security_reports', compact('reports'));
    }

    /**
     * Yêu cầu AI Python + Gemini phân tích và viết báo cáo
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Lấy toàn bộ log trong thời gian yêu cầu
        $logs = SecurityLog::whereBetween('created_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59'
        ])->get();

        if ($logs->isEmpty()) {
            return redirect()->back()->with('error', "Không tìm thấy log hoạt động nào trong khoảng thời gian từ $startDate đến $endDate để phân tích.");
        }

        // Gọi Python FastAPI AI Service
        try {
            $response = Http::timeout(90) // Gemini co the can nhieu thoi gian
                ->post('http://127.0.0.1:5000/generate-report', [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'logs' => $logs->toArray(),
                ]);

            if ($response->successful()) {
                $data = $response->json();

                // Lưu báo cáo vào database
                SecurityReport::create([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'summary' => $data['summary'],
                    'total_attacks' => $data['total_attacks'],
                    'critical_events' => $data['critical_events'],
                ]);

                return redirect()->back()->with('success', "Đã tạo báo cáo đánh giá an ninh thành công từ Gemini AI!");
            } else {
                return redirect()->back()->with('error', "Lỗi từ dịch vụ AI: " . $response->body());
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Lỗi kết nối AI Service (port 5000): " . $e->getMessage());
        }
    }
}
