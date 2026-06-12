# 💈 BarberShop - Website Đặt Lịch Cắt Tóc & Quản Lý Dịch Vụ Barber

Đồ án môn học PHP - Framework Laravel 11.x (hoặc 10.x). Đây là hệ thống quản lý tiệm cắt tóc toàn diện với giao diện dành riêng cho Khách hàng và Quản trị viên (Admin), tích hợp thanh toán online và trợ lý ảo AI.

## 🌟 Các tính năng nổi bật (Mới cập nhật)

- **Tích hợp VNPAY Sandbox**: Hỗ trợ thanh toán đặt cọc và thanh toán hóa đơn trực tuyến tự động thông qua thẻ giả lập của VNPAY. Trạng thái đơn hàng tự động cập nhật ngay lập tức.
- **Trợ lý ảo AI (Chatbot)**: Giao diện Chatbot Glassmorphism hiện đại, hỗ trợ khách hàng tư vấn dịch vụ, hỏi đáp về Barber Shop.
- **Xuất Hóa Đơn PDF**: Tự động tạo và xuất hóa đơn dịch vụ định dạng PDF chuyên nghiệp sử dụng thư viện `barryvdh/laravel-dompdf`.
- **Hệ thống Mã Giảm Giá (Promo Code)**: Áp dụng mã giảm giá (`REVIEW5K`, `BARBERVIP`) khi đặt lịch với các luật tính toán phức tạp (áp dụng theo ngày, theo hạng thành viên).
- **Hệ thống Tích Điểm & Hạng Thành Viên**: Tự động cộng điểm cho khách hàng sau khi sử dụng dịch vụ và nâng hạng thành viên (Bronze, Silver, Gold, Platinum, Diamond) với các ưu đãi riêng.
- **Dashboard Thống Kê Nâng Cao**: Cung cấp biểu đồ trực quan (Chart.js) thống kê doanh thu, tỷ lệ đặt lịch, dịch vụ thịnh hành và giờ cao điểm.
- **Xin nghỉ phép cho Barber**: Các thợ cắt tóc có thể tạo yêu cầu xin nghỉ phép và hệ thống tự động khóa lịch những ngày đó.

---

## 🚀 Hướng dẫn cài đặt và chạy dự án

### 1. Yêu cầu hệ thống

- **PHP** >= 8.2
- **Composer** (quản lý package PHP)
- **MySQL** (qua XAMPP, Laragon, hoặc cài riêng)
- **Git** (clone dự án)

### 2. Clone dự án từ GitHub

Mở Terminal (Command Prompt / PowerShell / Git Bash) và chạy:

```bash
git clone https://github.com/HuyGiaTran/BarberShop.git
cd BarberShop
```

### 3. Cài đặt dependencies

```bash
composer install
```

### 4. Cấu hình môi trường

Tạo file `.env` từ file mẫu:

```bash
cp .env.example .env
# Trên Windows dùng: copy .env.example .env
```

Sau đó mở file `.env` và sửa các thông tin Database, đồng thời cấu hình VNPAY Sandbox:

```env
APP_NAME="Barber Shop"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=barbershop_db
DB_USERNAME=root
DB_PASSWORD=

# VNPAY Sandbox Configuration
VNPAY_TMN_CODE=Mã_TmnCode_Của_Bạn
VNPAY_HASH_SECRET=Mã_HashSecret_Của_Bạn
VNPAY_PAYMENT_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
```

> **Lưu ý VNPAY:** Nếu không có mã, bạn có thể tự đăng ký tài khoản dev miễn phí tại `sandbox.vnpayment.vn/devreg/`.

### 5. Tạo database & Chạy Migration

Tạo database `barbershop_db` bằng phpMyAdmin hoặc MySQL CLI, sau đó chạy:

```bash
php artisan key:generate
php artisan migrate --seed
```
*(Lệnh `--seed` sẽ tạo sẵn dữ liệu mẫu cho Admin, Barber, Dịch vụ và Khách hàng)*

### 6. Chạy server

```bash
php artisan serve
```

Vào **http://127.0.0.1:8000** để xem trang web.

---

## 📂 Cấu trúc dự án nổi bật

- `app/Services/PaymentService.php`: Xử lý thuật toán Checksum và tạo URL cho VNPAY.
- `app/Services/PaymentFlowService.php`: Xử lý luồng tạo thanh toán Cọc và Hóa đơn.
- `app/Http/Controllers/Api/VnpayController.php`: Xử lý Web Callback (IPN/Return) từ VNPAY.
- `app/Http/Controllers/Customer/AppointmentController.php`: Luồng đặt lịch, áp dụng mã giảm giá và tính tiền đặt cọc.
- `app/Http/Controllers/Admin/StatisticController.php`: Xử lý dữ liệu cho biểu đồ thống kê.
- `resources/views/invoices/pdf.blade.php`: Template xuất file PDF cho hóa đơn.
- `resources/views/layouts/public.blade.php`: Giao diện khách hàng tích hợp nút Chatbot AI.

---

## 🔗 Luồng thử nghiệm Thanh toán (Demo VNPAY)

Để chạy demo VNPAY lấy điểm, bạn có thể sử dụng thông tin Thẻ Test sau khi thực hiện thanh toán cọc hoặc thanh toán hóa đơn:
- **Ngân hàng:** `NCB`
- **Số thẻ:** `9704198526191432198`
- **Tên chủ thẻ:** `NGUYEN VAN A`
- **Ngày phát hành:** `07/15`
- **Mã OTP:** `123456` (Hoặc nhập bất kỳ)

---

## 📝 Ghi chú Kỹ thuật
- Dự án áp dụng mô hình thiết kế Service Pattern kết hợp Observer để tách biệt logic nghiệp vụ.
- Xác thực bảo mật: Middleware Admin, Sanctum API Tokens.
- Thư viện Frontend: Bootstrap 5, Bootstrap Icons, Chart.js, SweetAlert2.
- Thư viện Backend: `barryvdh/laravel-dompdf` (PDF), `carbon` (Xử lý thời gian).
