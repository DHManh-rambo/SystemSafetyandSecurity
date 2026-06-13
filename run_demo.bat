@echo off
chcp 65001 > nul
title RoseShop - Security AI Demo

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║          🌹  ROSESHOP - SECURITY AI ENGINE - KHỞI ĐỘNG HỆ THỐNG           ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

:: ─── BƯỚC 1: Setup SQLite database cho Laravel ───────────────────────────────
echo [1/4] Tạo SQLite database và migrate...
if not exist "database\database.sqlite" (
    echo "" > database\database.sqlite
    echo     → Đã tạo file database\database.sqlite
) else (
    echo     → File database.sqlite đã tồn tại
)

php artisan config:clear > nul 2>&1
php artisan cache:clear > nul 2>&1
php artisan migrate --force 2>&1
echo.

:: ─── BƯỚC 2: Seed dữ liệu demo ───────────────────────────────────────────────
echo [2/4] Seed dữ liệu demo (admin + khách hàng mẫu)...
php artisan db:seed --class=DemoSeeder --force 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo     ⚠️  Seeder không tồn tại hoặc đã chạy, bỏ qua.
)
echo.

:: ─── BƯỚC 3: Khởi động Python AI Service ─────────────────────────────────────
echo [3/4] Khởi động Python AI Service trên port 5000...
cd ai-service

:: Kiểm tra model đã train chưa
if not exist "models\waf_model.pkl" (
    echo     → Model chưa tồn tại, tiến hành train WAF model...
    python train.py
    echo     ✅ Train model xong!
) else (
    echo     → Model đã tồn tại, bỏ qua train.
)

:: Khởi động FastAPI trong cửa sổ mới
start "AI Service - FastAPI" cmd /k "uvicorn app:app --host 127.0.0.1 --port 5000 --reload"
echo     → AI Service đang khởi động trong cửa sổ mới...
timeout /t 3 /nobreak > nul
cd ..
echo.

:: ─── BƯỚC 4: Khởi động Laravel ───────────────────────────────────────────────
echo [4/4] Khởi động Laravel Web Server trên port 8000...
start "Laravel Server" cmd /k "php artisan serve --host=127.0.0.1 --port=8000"
echo     → Laravel đang khởi động trong cửa sổ mới...
timeout /t 2 /nobreak > nul
echo.

echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║  ✅ HỆ THỐNG ĐÃ KHỞI ĐỘNG!                                                ║
echo ║                                                                              ║
echo ║  🌐 Web App (Laravel):  http://127.0.0.1:8000                               ║
echo ║  🤖 AI Service (API):   http://127.0.0.1:5000/docs                          ║
echo ║  📋 API Docs (Swagger): http://127.0.0.1:5000/docs                          ║
echo ║                                                                              ║
echo ║  Tài khoản demo:                                                             ║
echo ║    Admin:    admin1 / password123                                            ║
echo ║    KH:       khach1 / password123                                            ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.
echo Nhấn Enter để mở trình duyệt demo attack script...
pause > nul

echo.
echo ═══════════════════════════════════════════════════════
echo   🚀 Khởi chạy DEMO ATTACK SCRIPT...
echo ═══════════════════════════════════════════════════════
cd ai-service
python demo_attack.py
cd ..
pause
