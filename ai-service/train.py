# d:\LongWork\BMUD-BTL\SystemSafetyandSecurity\ai-service\train.py

import os
import pickle
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.linear_model import LogisticRegression
from sklearn.model_selection import train_test_split

def train_model():
    print("WAF Model Training: Preparing datasets...")
    
    # 1. Dataset payloads
    benign_payloads = [
        "hoa hong do", "binh hoa thuy tinh", "gio hoa chuc mung", "qua tang sinh nhat",
        "thanh toan hoa don qua ngan hang", "giao hang nhanh", "so dien thoai 0987654321",
        "hanoi, vietnam", "hoa tuoi da lat", "hoa cuc vang", "dat hang", "customer_profile",
        "Nguyen Van A", "nguyenvana@gmail.com", "Quan Cau Giay, Ha Noi", "Them san pham moi",
        "Mo ta san pham hoa tuoi nhap khau", "Gia ban hien tai 150000", "Chuyen khoan COD",
        "So luong con lai 50", "Thanh toan thanh cong!", "Dich vu khach hang truc tuyen",
        "shipper giao hang", "bao cao doanh thu", "nhap hang dot 2", "Tin tuc khuyen mai tet 2026"
    ]
    
    sqli_payloads = [
        "1' OR '1'='1", "1' OR 1=1 --", "admin' --", "admin' #", "' OR ''='",
        "1' OR 1=1 LIMIT 1", "1 UNION SELECT null, null, null --",
        "SELECT * FROM users", "UNION SELECT username, password FROM users",
        "'; DROP TABLE users; --", "OR 1=1", "1' AND 1=2 --",
        "admin' AND 1=1 --", "1') OR ('1'='1", "SELECT ma_nguoi_dung, mat_khau FROM nguoi_dung"
    ]
    
    xss_payloads = [
        "<script>alert(1)</script>", "<script src='http://evil.com/xss.js'></script>",
        "<img src=x onerror=alert('XSS')>", "<body onload=alert('XSS')>",
        "javascript:alert(1)", "javascript:alert(document.cookie)",
        "<svg/onload=alert(1)>", "onload=alert(1)", "onerror=confirm(1)",
        "<iframe src='javascript:alert(1)'>", "<a href='javascript:alert(1)'>Click me</a>",
        "\"onfocus=\"alert(1)", "'onmouseover='alert(1)"
    ]
    
    path_traversal_payloads = [
        "../../../../etc/passwd", "../../../etc/hosts", "..\\..\\..\\windows\\win.ini",
        "../../.env", "..\\..\\.env", "boot.ini", "/etc/passwd", "C:\\windows\\system32\\drivers\\etc\\hosts",
        "../../../../etc/shadow", "..\\..\\..\\..\\..\\..\\..\\etc\\passwd"
    ]

    # Create DataFrame
    data = []
    # Benign = 0
    for p in benign_payloads:
        data.append({"payload": p, "label": 0})
    # Malicious = 1
    for p in sqli_payloads:
        data.append({"payload": p, "label": 1})
    for p in xss_payloads:
        data.append({"payload": p, "label": 1})
    for p in path_traversal_payloads:
        data.append({"payload": p, "label": 1})
        
    df = pd.DataFrame(data)
    
    # 2. TF-IDF
    print("TF-IDF: Extracting features...")
    vectorizer = TfidfVectorizer(analyzer='char', ngram_range=(2, 4))
    X = vectorizer.fit_transform(df['payload'])
    y = df['label']
    
    # 3. Train Classifier
    print("Logistic Regression: Training WAF model...")
    model = LogisticRegression(class_weight='balanced')
    model.fit(X, y)
    
    # Eval
    score = model.score(X, y)
    print(f"Training completed! Training Accuracy: {score * 100:.2f}%")
    
    # 4. Save
    os.makedirs("models", exist_ok=True)
    with open("models/vectorizer.pkl", "wb") as f:
        pickle.dump(vectorizer, f)
    with open("models/waf_model.pkl", "wb") as f:
        pickle.dump(model, f)
        
    print("Model files saved to models/")

if __name__ == "__main__":
    train_model()
