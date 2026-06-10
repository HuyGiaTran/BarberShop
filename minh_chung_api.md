# Minh Chứng Test API (Dùng để copy vào file Word Báo Cáo)

Dưới đây là các kịch bản test API Auth theo chuẩn RESTful API bằng Postman. Đại ca chỉ cần copy toàn bộ nội dung JSON này dán vào file Word báo cáo của nhóm là bao chuẩn mực!

---

### Kịch bản 1: Đăng ký tài khoản (API Public)
*   **Method**: `POST`
*   **URL**: `http://127.0.0.1:8000/api/register`
*   **Dữ liệu gửi lên (Body)**:
    *   `name`: `Khach Hang Moi`
    *   `email`: `khachmoi@gmail.com`
    *   `password`: `12345678`
    *   `password_confirmation`: `12345678`

**Kết quả trả về (Response JSON):**
```json
{
    "status": "success",
    "message": "Đăng ký tài khoản thành công!",
    "data": {
        "token": "1|G7kP2xL9mNqR4wV8sT5bY3cF6hJ1uM0zK9oE8dI2",
        "token_type": "Bearer",
        "user": {
            "id": 14,
            "name": "Khach Hang Moi",
            "email": "khachmoi@gmail.com",
            "phone": null,
            "role": "customer"
        }
    }
}
```

---

### Kịch bản 2: Đăng nhập lấy Token (API Public)
*   **Method**: `POST`
*   **URL**: `http://127.0.0.1:8000/api/login`
*   **Dữ liệu gửi lên (Body)**:
    *   `email`: `api_test@gmail.com`
    *   `password`: `password`

**Kết quả trả về (Response JSON):**
```json
{
    "status": "success",
    "message": "Đăng nhập thành công!",
    "data": {
        "token": "2|W3qA8zX5vN1bM9uC4lK7jF2hG6sD0yR9tE5pY1mT",
        "token_type": "Bearer",
        "user": {
            "id": 2,
            "name": "API Tester",
            "email": "api_test@gmail.com",
            "phone": null,
            "role": "customer"
        }
    }
}
```

---

### Kịch bản 3: Lấy thông tin User hiện tại (API Protected)
*   **Method**: `GET`
*   **URL**: `http://127.0.0.1:8000/api/user`
*   **Headers**: 
    *   `Authorization`: `Bearer 2|W3qA8zX5vN1bM9uC4lK7jF2hG6sD0yR9tE5pY1mT`

**Kết quả trả về (Response JSON):**
```json
{
    "status": "success",
    "message": "Lấy thông tin người dùng thành công.",
    "data": {
        "user": {
            "id": 2,
            "name": "API Tester",
            "email": "api_test@gmail.com",
            "phone": null,
            "role": "customer",
            "created_at": "2026-05-27 10:15:30"
        }
    }
}
```

---

### Kịch bản 4: Đăng xuất (API Protected)
*   **Method**: `POST`
*   **URL**: `http://127.0.0.1:8000/api/logout`
*   **Headers**: 
    *   `Authorization`: `Bearer 2|W3qA8zX5vN1bM9uC4lK7jF2hG6sD0yR9tE5pY1mT`

**Kết quả trả về (Response JSON):**
```json
{
    "status": "success",
    "message": "Đăng xuất thành công. Token đã bị hủy."
}
```
