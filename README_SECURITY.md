# 🛡️ RoseShop Security System & AI Engine (WAF) - README

Tài liệu này trình bày chi tiết về kiến trúc bảo mật lai (Hybrid Security Architecture) của ứng dụng **RoseShop**, bao gồm các lớp phòng thủ, nguyên lý hoạt động của mô hình trí tuệ nhân tạo (AI WAF), và hướng dẫn chi tiết cách chạy demo kiểm thử.

---

## 📖 MỤC LỤC
1. [Tổng Quan Kiến Trúc Bảo Mật](#1-tổng-quan-kiến-trúc-bảo-mật)
2. [5 Lớp Phòng Thủ Của Hệ Thống](#2-5-lớp-phòng-thủ-của-hệ-thống)
3. [Nguyên Lý Hoạt Động Của Mô Hình AI WAF](#3-nguyên-lý-hoạt-động-của-mô-hình-ai-waf)
4. [Hướng Dẫn Cấu Hình Và Chạy Demo](#4-hướng-dẫn-cấu-hình-và-chạy-demo)
5. [Xem Nhật Ký Bảo Mật & Dashboard Quản Trị](#5-xem-nhật-ký-bảo-mật--dashboard-quản-trị)

---

## 1. TỔNG QUAN KIẾN TRÚC BẢO MẬT
RoseShop áp dụng cơ chế bảo mật **Hybrid (Lai)** kết hợp giữa **Heuristics (Regex)** truyền thống và **Trí tuệ nhân tạo (Machine Learning/LLM)** để bảo vệ ứng dụng ở mức tối đa mà vẫn tối ưu hiệu năng.

```mermaid
graph TD
    Client[Client / Hacker] -->|Request| SecurityShield[Laravel SecurityShield Middleware]
    
    subgraph Laravel Application
        SecurityShield -->|1. Check Blacklist| CacheCheck{IP in Cache?}
        CacheCheck -->|Yes| Abort403[Block 403 Forbidden]
        CacheCheck -->|No| RateLimiter{Rate > 60 req/min?}
        
        RateLimiter -->|Yes| Block1h[Block IP 1 Hour]
        RateLimiter -->|No| FileCheck{Has Upload File?}
        
        FileCheck -->|Yes| ExtensionStego[Check Extension & Steganography]
        ExtensionStego -->|Malicious| CriticalLock[Lock IP 24h + Disable Account]
    end

    FileCheck -->|No / Clean| AIWafCheck{AI Service Online?}
    
    subgraph AI Service (FastAPI Port 5000)
        AIWafCheck -->|Yes| TFIDF[TF-IDF Vectorizer]
        TFIDF -->|Transform| MLModel[ML Classifier Model]
        MLModel -->|Predict| Result[Attack Score & Type]
    end
    
    subgraph Local Fallback
        AIWafCheck -->|No| RegexWAF[Local Heuristics Regex WAF]
    end
    
    Result -->|Score >= 58%| PendingBlock[Lock IP 30m + Log Out + Status: CHO_DUYET]
    RegexWAF -->|Match| PendingBlock
    
    Result -->|Score < 58% / Clean| Pass[Pass to Controller]
    RegexWAF -->|No Match| Pass
```

---

## 2. 5 LỚP PHÒNG THỦ CỦA HỆ THỐNG

### Lớp 1 — IP Blacklist Cache (Chặn tức thời)
Mỗi request đi vào ứng dụng sẽ được kiểm tra IP đầu tiên trong Laravel Cache.
* **Cơ chế:** Nếu IP nằm trong danh sách chặn (`blocked_ip_{ip}`), request sẽ bị hủy ngay lập tức bằng phản hồi `HTTP 403`, hoàn toàn không chạy vào các tác vụ xử lý DB hay Controller để tránh quá tải.

### Lớp 2 — Rate Limiter (Chống tấn công DOS)
Kiểm tra số lượng request gửi lên từ một địa chỉ IP trong khoảng thời gian ngắn.
* **Giới hạn:** Tối đa `60 request / phút`.
* **Hành động:** Khi vượt ngưỡng, IP sẽ bị tạm khóa trong `1 giờ` (lưu vào Cache) và ghi nhận log loại tấn công `DOS` với độ nghiêm trọng `HIGH`.

### Lớp 3 — File Upload WAF & Steganography (Chống tải file độc hại)
Bảo vệ hệ thống khỏi các mã độc Web Shell giấu trong file ảnh tải lên.
* **Blacklist Extension:** Cấm tuyệt đối các file có đuôi `.php`, `.phtml`, `.exe`, `.bat`, `.sh`, `.cmd`, `.js`, `.jar`, `.msi`,...
* **Steganography Detection:** Quét nội dung nhị phân (Binary) của file ảnh tải lên để phát hiện các chữ ký mã script PHP/JS nguy hiểm (như `<?php`, `eval()`, `<script>`, `system()`).
* **Hành động:** Nếu phát hiện file độc hại, IP bị khóa ngay `24 giờ`, tài khoản người dùng tải lên sẽ bị chuyển sang trạng thái `CHO_DUYET` (chờ duyệt), buộc logout lập tức.

### Lớp 4 — Input Payload Inspection (AI WAF)
Kiểm tra tất cả dữ liệu người dùng gửi lên thông qua ô nhập liệu, URL query parameters (`?q=...`), hoặc POST body.
* **Cơ chế:** Dữ liệu đầu vào được gửi tới **FastAPI Python (cổng 5000)** để phân tích.
* **Hành động:** Nếu AI xác nhận là cuộc tấn công (SQL Injection, XSS, Path Traversal) với độ tin cậy **>= 58%**:
  * Tạm khóa IP trong `30 phút`.
  * Chuyển trạng thái tài khoản đang đăng nhập sang `CHO_DUYET`, thu hồi Token Session (Logout).
  * Ghi chi tiết mã độc vào `security_logs` để Admin phân tích.

### Lớp 5 — Local Regex Heuristics (Cơ chế dự phòng Fallback)
Đảm bảo tính sẵn sàng cao (High Availability) cho hệ thống bảo mật.
* **Cơ chế:** Nếu máy chủ dịch vụ AI Python bị tắt hoặc gặp sự cố ngắt kết nối, Laravel Middleware sẽ tự động bắt ngoại lệ (Exception) và chuyển sang quét bằng tập luật **Regex cục bộ**.
* **Đặc điểm:** Không bị gián đoạn bảo mật khi AI offline. Vẫn bắt được các mẫu SQLi, XSS cơ bản.

---

## 3. NGUYÊN LÝ HOẠT ĐỘNG CỦA MÔ HÌNH AI WAF

Dịch vụ AI tại thư mục `ai-service` chạy trên cổng `5000` được cấu hình với hai thành phần AI chính: **Mô hình máy học tĩnh (WAF Model)** và **Trí tuệ nhân tạo tạo sinh (LLM Gemini)**.

### A. Mô Hình Máy Học Phân Loại Payload (WAF Classifier)
Mô hình này hoạt động theo mô hình xử lý ngôn ngữ tự nhiên (NLP) kết hợp phân loại nhị phân.

```
[Dữ liệu thô (Text)] ➔ [TF-IDF Vectorizer] ➔ [Ma trận đặc trưng] ➔ [ML Classifier] ➔ [Kết quả dự đoán (Dự báo + Độ tự tin %)]
```

1. **TF-IDF Vectorizer (`models/vectorizer.pkl`):**
   * Chuyển đổi chuỗi văn bản thô gửi từ client thành một ma trận các con số đặc trưng dựa trên tần suất xuất hiện của các từ/cụm từ (n-grams) trong tập dữ liệu huấn luyện.
2. **Machine Learning Classifier (`models/waf_model.pkl`):**
   * Sử dụng thuật toán phân loại (thường là Logistic Regression hoặc SVM) đã học từ hàng chục nghìn payload tấn công thực tế và các câu truy vấn thông thường.
   * Mô hình sẽ đưa ra dự báo nhị phân: `0` (Hợp lệ) hoặc `1` (Tấn công).
   * Trả về xác suất (Probability/Confidence) để làm cơ sở tính điểm đe dọa (Threat Score).

### B. Mô Hình Phát Hiện Hành Vi Bất Thường (Anomaly Detector)
* Đánh giá tổng hợp điểm hành vi (Heuristic-based Threat Score) dựa trên nhiều chỉ số như: số lần đăng nhập sai liên tiếp, tần suất request thực tế, và dấu hiệu tải file đáng nghi, từ đó đưa ra hành động tương ứng (`BLOCKED_IP`, `LOCKED_ACCOUNT`).

### C. Trí Tuệ Nhân Tạo Tạo Sinh (Gemini API)
* **Báo cáo bảo mật tự động:** Khi Admin bấm nút "Yêu cầu Gemini phân tích" trên trang quản trị, hệ thống sẽ gom tất cả log bảo mật trong khoảng thời gian đã chọn và gửi tới API Gemini.
* AI sẽ đóng vai trò là một chuyên gia Bảo mật để phân tích xu hướng tấn công, đánh giá mức độ nguy hại, tìm ra các IP nguy hiểm nhất và đề xuất phương án vá lỗ hổng chi tiết.

---

## 4. HƯỚNG DẪN CẤU HÌNH VÀ CHẠY DEMO

### Yêu Cầu Chuẩn Bị
* **PHP >= 8.2** & **Composer**
* **Python >= 3.10**
* Database: MySQL (Aiven Cloud hoặc Local)

### Bước 1: Khởi động hệ thống
Mở 2 cửa sổ Terminal độc lập để chạy 2 dịch vụ sau:

1. **Khởi động Laravel Web App:**
   ```bash
   php artisan serve
   # ➔ Chạy tại http://127.0.0.1:8000
   ```
2. **Khởi động AI Python Service:**
   ```bash
   cd ai-service
   py -m uvicorn app:app --host 127.0.0.1 --port 5000
   # ➔ Chạy tại http://127.0.0.1:5000
   ```

---

### Bước 2: Chạy Demo Tấn Công

#### Cách 1: Tấn công Tự Động (Sử dụng Python Script)
Thư mục `ai-service` chứa file `real_attack_sim.py` cấu hình sẵn các gói tấn công mẫu.

1. Kiểm tra cấu hình IP/URL mục tiêu tại dòng 20 trong file `real_attack_sim.py`:
   ```python
   LARAVEL_URL = "http://127.0.0.1:8000" # Đổi thành link Ngrok/Cloudflare nếu test từ máy khác
   ```
2. Chạy script mô phỏng:
   ```bash
   py real_attack_sim.py
   ```
3. Nhập lựa chọn tấn công:
   * `1`: SQL Injection
   * `2`: XSS
   * `3`: Path Traversal
   * `4`: DOS Attack (Tự động gửi dồn dập 65 request để kích hoạt Rate Limiter)

#### Cách 2: Tấn công Thủ Công (Manual PenTest)
Mở trình duyệt của bạn (hoặc thiết bị khác truy cập qua tunnel public) và nhập các chuỗi payload sau vào thanh công cụ tìm kiếm trên giao diện RoseShop:

* **Tấn công SQL Injection:**
  ```text
  ' OR '1'='1
  ```
* **Tấn công Cross-Site Scripting (XSS):**
  ```html
  <script>alert('XSS_Demo')</script>
  ```
* **Tấn công Path Traversal:**
  ```text
  ../../.env
  ```

---

## 5. XEM NHẬT KÝ BẢO MẬT & DASHBOARD QUẢN TRỊ

Khi cuộc tấn công bị chặn, bạn hãy đăng nhập tài khoản Admin (`admin1` / `password123`) để theo dõi kết quả:

1. **Dashboard Bảo Mật:** `http://127.0.0.1:8000/admin/security/dashboard`
   * Xem biểu đồ trực quan về tổng số cuộc tấn công, tỉ lệ các loại hình tấn công (SQLi, XSS, DOS), và top các IP vi phạm nhiều nhất.
2. **Nhật Ký Bảo Mật (Logs):** `http://127.0.0.1:8000/admin/security/logs`
   * Hiển thị bảng chi tiết: IP, Thời gian (GMT+7), Payload bị chặn, Loại tấn công, Điểm đe dọa từ AI WAF, và Hành động hệ thống đã thực thi.
3. **Báo Cáo AI (AI Assessments):** `http://127.0.0.1:8000/admin/security/reports`
   * Chọn mốc thời gian và nhấn nút **Yêu cầu Gemini phân tích**. Chờ ~30-45 giây để AI đọc log và xuất báo cáo PDF/Markdown trực quan khuyến nghị cho quản trị viên.
