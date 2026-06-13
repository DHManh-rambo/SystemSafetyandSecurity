# d:\LongWork\BMUD-BTL\SystemSafetyandSecurity\ai-service\app.py

import os
import pickle
import re
from typing import List, Dict, Any
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import google.genai as genai

app = FastAPI(title="RoseShop Security AI Engine", version="1.0")

# Cấu hình API Key cho Gemini (lấy động từ biến môi trường hoặc file .env ở thư mục cha)
def load_env_gemini_key() -> str:
    # Thử lấy từ môi trường hệ thống trước
    key = os.environ.get("GEMINI_API_KEY")
    if key:
        return key
    
    # Thử tìm và đọc từ file .env của dự án Laravel (thư mục cha)
    parent_env = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), ".env")
    if os.path.exists(parent_env):
        try:
            with open(parent_env, "r", encoding="utf-8") as f:
                for line in f:
                    if line.strip().startswith("GEMINI_API_KEY="):
                        return line.strip().split("=", 1)[1].strip().strip('"').strip("'")
        except Exception:
            pass
    return ""

DEFAULT_GEMINI_KEY = load_env_gemini_key()
os.environ["GEMINI_API_KEY"] = DEFAULT_GEMINI_KEY

# Tải mô hình WAF khi khởi chạy
vectorizer = None
model = None

def load_models():
    global vectorizer, model
    v_path = "models/vectorizer.pkl"
    m_path = "models/waf_model.pkl"
    if os.path.exists(v_path) and os.path.exists(m_path):
        with open(v_path, "rb") as f:
            vectorizer = pickle.load(f)
        with open(m_path, "rb") as f:
            model = pickle.load(f)
        print("WAF Model (Vectorizer & Classifier) loaded successfully!")
    else:
        print("Warning: Model files not found. Run train.py first!")

@app.on_event("startup")
def startup_event():
    load_models()

# Định nghĩa schemas đầu vào
class PayloadRequest(BaseModel):
    text: str

class AnomalyRequest(BaseModel):
    ip: str
    request_count: int
    failed_logins: int
    has_bad_extensions: bool

class LogReportRequest(BaseModel):
    start_date: str
    end_date: str
    logs: List[Dict[str, Any]]
    api_key: str = None

# 1. Endpoint WAF Classifier
@app.post("/analyze-payload")
def analyze_payload(req: PayloadRequest):
    global vectorizer, model
    if vectorizer is None or model is None:
        # Tải lại nếu chưa load
        load_models()
        if vectorizer is None or model is None:
            raise HTTPException(status_code=500, detail="WAF Model is not trained or loaded.")
            
    text = req.text
    if not text.strip():
        return {"is_attack": False, "confidence": 1.0, "attack_type": "None"}

    # Vector hóa và dự đoán
    vec = vectorizer.transform([text])
    pred = model.predict(vec)[0]
    prob = model.predict_proba(vec)[0][pred]

    # Phân biệt loại hình tấn công dựa trên heuristic
    attack_type = "None"
    if pred == 1:
        text_lower = text.lower()
        if any(keyword in text_lower for keyword in ["select", "union", "insert", "delete", "drop", "update", "or '1'='1", "or 1=1"]):
            attack_type = "SQLi"
        elif any(keyword in text_lower for keyword in ["<script>", "javascript:", "onerror", "onload", "alert(", "confirm("]):
            attack_type = "XSS"
        elif any(keyword in text_lower for keyword in ["../", "..\\", "etc/passwd", "win.ini", ".env"]):
            attack_type = "Path_Traversal"
        else:
            attack_type = "Suspicious_Pattern"

    return {
        "is_attack": bool(pred == 1),
        "confidence": float(prob),
        "attack_type": attack_type
    }

# 2. Endpoint Anomaly & Bot Detection (Smart IPS)
@app.post("/detect-anomaly")
def detect_anomaly(req: AnomalyRequest):
    score = 0
    action = "LOG_ONLY"
    severity = "LOW"

    # Rule-based combined heuristics
    if req.has_bad_extensions:
        score = 100
        action = "LOCKED_ACCOUNT"
        severity = "CRITICAL"
    else:
        # Tính điểm nguy cơ dựa trên hành vi
        # DOS score: 60 request/phút là giới hạn, vượt quá sẽ bắt đầu tăng điểm
        # failed_logins: Thử sai đăng nhập nhiều lần
        score = (req.request_count * 1.0) + (req.failed_logins * 15)
        
        if req.failed_logins >= 5:
            score = max(score, 85)
            action = "LOCKED_ACCOUNT"
            severity = "HIGH"
        elif score >= 80:
            action = "BLOCKED_IP"
            severity = "HIGH"
        elif score >= 40:
            action = "LOG_ONLY"
            severity = "MEDIUM"
            
    return {
        "threat_score": int(score),
        "action_taken": action,
        "severity": severity
    }

# 3. Endpoint Báo cáo đánh giá bằng Gemini
@app.post("/generate-report")
def generate_report(req: LogReportRequest):
    # Sử dụng API Key của người dùng gửi lên hoặc key mặc định
    api_key = req.api_key if req.api_key else DEFAULT_GEMINI_KEY
    if not api_key:
        raise HTTPException(status_code=400, detail="Gemini API Key is missing.")
        
    try:
        client = genai.Client(api_key=api_key)
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to configure Gemini: {str(e)}")

    # Tổng hợp thống kê sơ bộ từ Log gửi lên
    total_attacks = len(req.logs)
    attack_types = {}
    severities = {"LOW": 0, "MEDIUM": 0, "HIGH": 0, "CRITICAL": 0}
    top_ips = {}
    critical_details = []

    for log in req.logs:
        atype = log.get("attack_type", "Unknown")
        attack_types[atype] = attack_types.get(atype, 0) + 1
        
        sev = log.get("severity", "LOW")
        severities[sev] = severities.get(sev, 0) + 1
        
        ip = log.get("ip_address", "Unknown")
        top_ips[ip] = top_ips.get(ip, 0) + 1

        if sev in ["HIGH", "CRITICAL"]:
            critical_details.append(
                f"- IP: {ip} | Loại: {atype} | Payload: {log.get('payload', '')} | Action: {log.get('action_taken', '')} | Lúc: {log.get('created_at', '')}"
            )

    # Lọc top 3 IP tấn công nhiều nhất
    sorted_ips = sorted(top_ips.items(), key=lambda x: x[1], reverse=True)[:3]
    top_ips_str = ", ".join([f"{ip} ({count} lần)" for ip, count in sorted_ips])

    # Tạo prompt gửi cho Gemini
    prompt = f"""
    Bạn là một kỹ sư Security Analyst chuyên nghiệp của hệ thống SIEM/SOC.
    Hãy viết một Báo cáo Đánh giá An ninh Hệ thống (Security Assessment Report) bằng tiếng Việt cho quản trị viên website RoseShop trong khoảng thời gian từ ngày {req.start_date} đến ngày {req.end_date}.
    Thời gian này admin vắng mặt (nghỉ lễ/đi công tác) và hệ thống IPS tự động phòng thủ.

    Dưới đây là số liệu thống kê thu thập từ log bảo mật:
    - Tổng số vụ tấn công bị chặn đứng: {total_attacks}
    - Thống kê mức độ nghiêm trọng: Low ({severities['LOW']}), Medium ({severities['MEDIUM']}), High ({severities['HIGH']}), Critical ({severities['CRITICAL']})
    - Phân loại hình tấn công: {dict(attack_types)}
    - Top IP tấn công nguy hiểm nhất: {top_ips_str}
    
    Danh sách các sự kiện nghiêm trọng (High/Critical):
    {chr(10).join(critical_details[:10]) if critical_details else "Không có sự kiện High/Critical nào."}

    Hãy trình bày báo cáo một cách chuyên nghiệp sử dụng định dạng Markdown rõ ràng, bao gồm các phần:
    1. Tóm tắt tổng quan tình hình an ninh (Executive Summary) - Đánh giá xem hệ thống có an toàn trong thời gian qua không.
    2. Phân tích chi tiết các hình thức tấn công nổi bật (SQLi, XSS, File Upload, DOS) và các chiến dịch tấn công nghi ngờ từ các IP đứng đầu.
    3. Phân tích các trường hợp người dùng bị khóa tài khoản hoặc đưa vào danh sách chờ duyệt (đánh giá hành vi tò mò vs. phá hoại thực sự).
    4. Khuyến nghị hành động cụ thể cho Admin sau khi trở lại làm việc (các IP cần ban vĩnh viễn, các tài khoản cần duyệt mở khóa, nâng cấp cấu hình WAF).
    """

    try:
        response = client.models.generate_content(
            model="gemini-2.5-flash",
            contents=prompt
        )
        report_text = response.text
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to generate report from Gemini: {str(e)}")

    return {
        "summary": report_text,
        "total_attacks": total_attacks,
        "critical_events": severities["HIGH"] + severities["CRITICAL"]
    }
