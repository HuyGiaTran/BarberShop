# KẾ HOẠCH PHÁT TRIỂN HỆ THỐNG QUẢN LÝ BARBER SHOP

## 📋 MỤC LỤC

1. [Hiện trạng dự án](#1-hiện-trạng-dự-án)
2. [Cấu trúc thư mục khi hoàn thành](#2-cấu-trúc-thư-mục-khi-hoàn-thành)
3. [Kiến trúc Database](#3-kiến-trúc-database)
4. [Phân công công việc 5 thành viên](#4-phân-công-công-việc-5-thành-viên)
5. [Chi tiết chức năng 3 vai trò](#5-chi-tiết-chức-năng-3-vai-trò)
6. [Lộ trình ưu tiên phát triển](#6-lộ-trình-ưu-tiên-phát-triển)

---

## 1. HIỆN TRẠNG DỰ ÁN

### Đã hoàn thành:

| STT | Tính năng                                                               |  Trạng thái  |
| --- | ------------------------------------------------------------------------- | :-------------: |
| 1   | Giao diện public template (Hero, About, Services, Price List, Contact)   | ✅ Hoàn thành |
| 2   | Trang chủ public load Barber & Service từ database                      | ✅ Hoàn thành |
| 3   | Đăng nhập / Đăng ký (redirect về trang chủ cho customer)          | ✅ Hoàn thành |
| 4   | Sidebar public (Đăng nhập/Đăng ký cho guest, Đăng xuất cho user) | ✅ Hoàn thành |
| 5   | Admin Dashboard (thống kê tổng quan)                                   | ✅ Hoàn thành |
| 6   | CRUD Barbers (Admin)                                                      | ✅ Hoàn thành |
| 7   | CRUD Services (Admin)                                                     | ✅ Hoàn thành |
| 8   | CRUD Appointments (Admin)                                                 | ✅ Hoàn thành |
| 9   | API Services (cho frontend tích hợp)                                    | ✅ Hoàn thành |
| 10  | Booking form public (disabled cho guest, active cho logged-in user)       | ✅ Hoàn thành |

### Tài khoản mẫu đã có:

| Vai trò | Email               | Password     | Ghi chú                        |
| -------- | ------------------- | ------------ | ------------------------------- |
| Admin    | `admin@gmail.com` | `admin123` | Quyền quản trị cao nhất     |
| Barber 1 | `tho1@gmail.com`  | `password` | Anh Tuan                        |
| Barber 2 | `tho2@gmail.com`  | `password` | Hai Phong                       |
| Barber 3 | `tho3@gmail.com`  | `password` | Duy Thai                        |
| Customer | Đăng ký mới     | Tự đặt    | Role mặc định khi đăng ký |

---

## 2. CẤU TRÚC THƯ MỤC KHI HOÀN THÀNH

```
laravel-shop/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── HomeController.php              # Trang chủ public
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php          # Đăng nhập
│   │   │   │   └── RegisterController.php       # Đăng ký
│   │   │   ├── Admin/                           # 👑 ADMIN (Member 1 Lead)
│   │   │   │   ├── DashboardController.php       # Dashboard tổng quan
│   │   │   │   ├── BarberController.php          # CRUD Barber
│   │   │   │   ├── ServiceController.php         # CRUD Service
│   │   │   │   ├── AppointmentController.php     # Quản lý lịch hẹn
│   │   │   │   ├── PayrollController.php         # Tính lương & hoa hồng (Member 5)
│   │   │   │   ├── ReviewController.php          # Quản lý đánh giá (Member 2)
│   │   │   │   └── StatisticController.php       # Thống kê doanh thu (Member 5)
│   │   │   ├── Barber/                           # ✂️ BARBER (Member 4)
│   │   │   │   ├── DashboardController.php       # Barber dashboard (Timeline)
│   │   │   │   └── LeaveRequestController.php    # Đơn xin nghỉ
│   │   │   ├── Customer/                         # 👤 CUSTOMER (Member 2 + 3)
│   │   │   │   ├── AppointmentController.php     # Đặt lịch
│   │   │   │   ├── ReviewController.php          # Đánh giá
│   │   │   │   └── LoyaltyController.php         # Điểm thưởng
│   │   │   └── Api/                              # API (Member 1 + 3)
│   │   │       ├── ServiceApiController.php      # API Services (đã có)
│   │   │       ├── ChatbotController.php         # API Chatbot AI (Member 1)
│   │   │       ├── ReviewApiController.php       # API đánh giá (Member 2)
│   │   │       ├── VnpayController.php           # API VNPAY (Member 3)
│   │   │       └── StatisticApiController.php    # API thống kê (Member 5)
│   │   ├── Middleware/
│   │   │   ├── CheckAdminRole.php                # Kiểm tra role = admin
│   │   │   └── CheckBarberRole.php               # Kiểm tra role = barber
│   ├── Models/
│   │   ├── User.php                              # Người dùng (customer/admin/barber)
│   │   ├── Barber.php                            # Thợ cắt tóc
│   │   ├── Service.php                           # Dịch vụ
│   │   ├── Appointment.php                       # Lịch hẹn
│   │   ├── Review.php                            # Đánh giá (MỚI)
│   │   ├── LoyaltyPoint.php                      # Điểm thưởng (MỚI)
│   │   ├── LeaveRequest.php                      # Đơn xin nghỉ (MỚI)
│   │   ├── Commission.php                        # Hoa hồng (MỚI)
│   │   └── Payroll.php                           # Bảng lương (MỚI)
│
├── resources/views/
│   ├── layouts/
│   │   ├── app.blade.php              # Admin layout (sidebar gradient tối)
│   │   ├── public.blade.php           # Public layout (sidebar template)
│   │   └── barber.blade.php           # Barber layout (MỚI - sidebar đơn giản)
│   ├── home/
│   │   └── index.blade.php            # Trang chủ public
│   ├── auth/
│   │   ├── login.blade.php            # Đăng nhập
│   │   └── register.blade.php         # Đăng ký
│   ├── admin/
│   │   ├── dashboard.blade.php        # Admin dashboard
│   │   ├── barbers/                   # CRUD barbers
│   │   ├── services/                  # CRUD services
│   │   ├── appointments/              # Quản lý lịch hẹn
│   │   ├── reviews/                   # Quản lý đánh giá
│   │   ├── payrolls/                  # Lương thưởng
│   │   └── statistics/                # Thống kê biểu đồ
│   ├── barber/                        # MỚI - Giao diện Barber
│   │   ├── dashboard.blade.php        # Timeline ca cắt
│   │   └── leaves/
│   │       └── create.blade.php       # Xin nghỉ
│   └── customer/                      # MỚI - Giao diện Customer
│       ├── appointments/
│       │   └── create.blade.php       # Đặt lịch nâng cao (chọn thợ, giờ)
│       └── reviews/
│           └── create.blade.php       # Đánh giá sau khi cắt
│
├── routes/
│   ├── web.php                        # Web routes
│   └── api.php                        # API routes
│
└── database/
    └── migrations/
        ├── ...create_users_table.php
        ├── ...create_barbers_table.php
        ├── ...create_services_table.php
        ├── ...create_appointments_table.php
        ├── ...create_reviews_table.php           # MỚI
        ├── ...create_loyalty_points_table.php    # MỚI
        ├── ...create_leave_requests_table.php    # MỚI
        ├── ...create_commissions_table.php       # MỚI
        └── ...create_payrolls_table.php          # MỚI
```

---

## 3. KIẾN TRÚC DATABASE

### Các bảng hiện tại:

```sql
-- users: id, name, email, phone, password, role [customer|admin|barber], remember_token
-- barbers: id, user_id, name, phone, bio, avatar, is_active
-- services: id, name, description, price, duration_minutes, barber_id
-- appointments: id, user_id, barber_id, service_id, appointment_date, appointment_time, status [pending|confirmed|completed|cancelled], notes
```

### Các bảng mới cần tạo:

```sql
-- 1. reviews (Member 2)
-- id, user_id, barber_id, service_id, appointment_id, rating (1-5), comment, created_at
CREATE TABLE reviews (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    barber_id BIGINT NOT NULL,
    service_id BIGINT,
    appointment_id BIGINT UNIQUE,  -- 1 lịch chỉ được đánh giá 1 lần
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (barber_id) REFERENCES barbers(id),
    FOREIGN KEY (service_id) REFERENCES services(id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id)
);

-- 2. loyalty_points (Member 2)
-- id, user_id, points, tier [bronze|silver|gold|diamond], total_spent
CREATE TABLE loyalty_points (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNIQUE NOT NULL,
    points INT DEFAULT 0,
    tier ENUM('bronze', 'silver', 'gold', 'diamond') DEFAULT 'bronze',
    total_spent DECIMAL(12,2) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 3. loyalty_transactions (Member 2)
-- Lịch sử tích điểm / tiêu điểm
CREATE TABLE loyalty_transactions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    points INT NOT NULL,         -- số dương: tích, số âm: tiêu
    type ENUM('earn', 'redeem', 'bonus'),
    reference_type VARCHAR(50),  -- 'appointment', 'promotion'
    reference_id BIGINT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 4. leave_requests (Member 4)
-- id, barber_id, date, reason, status [pending|approved|rejected], admin_id
CREATE TABLE leave_requests (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    barber_id BIGINT NOT NULL,
    leave_date DATE NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_id BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (barber_id) REFERENCES barbers(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- 5. commissions (Member 5)
-- Định nghĩa % hoa hồng cho từng barber theo từng dịch vụ
CREATE TABLE commissions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    barber_id BIGINT NOT NULL,
    service_id BIGINT NOT NULL,
    commission_percent DECIMAL(5,2) NOT NULL DEFAULT 30.00,  -- % hoa hồng
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (barber_id) REFERENCES barbers(id),
    FOREIGN KEY (service_id) REFERENCES services(id)
);

-- 6. payrolls (Member 5)
-- id, barber_id, month, year, base_salary, total_commission, bonus, total_salary, status [pending|paid], paid_at
CREATE TABLE payrolls (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    barber_id BIGINT NOT NULL,
    month TINYINT NOT NULL,
    year SMALLINT NOT NULL,
    base_salary DECIMAL(12,2) DEFAULT 0,        -- Lương cứng
    total_commission DECIMAL(12,2) DEFAULT 0,    -- Tổng hoa hồng
    bonus DECIMAL(12,2) DEFAULT 0,               -- Thưởng
    total_salary DECIMAL(12,2) GENERATED ALWAYS AS (base_salary + total_commission + bonus) STORED,
    status ENUM('pending', 'paid') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    admin_id BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (barber_id) REFERENCES barbers(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- 7. barber_schedules (Member 3 + 4)
-- Lưu khung giờ làm việc/tạm nghỉ của barber
CREATE TABLE barber_schedules (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    barber_id BIGINT NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,       -- 09:00
    end_time TIME NOT NULL,         -- 18:00
    is_available BOOLEAN DEFAULT TRUE, -- FALSE nếu xin nghỉ / bận
    reason VARCHAR(255),
    FOREIGN KEY (barber_id) REFERENCES barbers(id)
);

-- 8. appointments MỞ RỘNG (thêm cột)
ALTER TABLE appointments ADD COLUMN deposit_amount DECIMAL(12,2) DEFAULT 0;       -- Số tiền cọc
ALTER TABLE appointments ADD COLUMN vnpay_transaction_code VARCHAR(100) NULL;     -- Mã giao dịch VNPAY
ALTER TABLE appointments ADD COLUMN deposit_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid';
ALTER TABLE appointments ADD COLUMN number_of_people INT DEFAULT 1;
```

---

## 4. PHÂN CÔNG CÔNG VIỆC 5 THÀNH VIÊN

---

### 👑 MEMBER 1 - NHÓM TRƯỞNG (Lead Backend & AI Core)

**Vai trò**: Điều phối Backend tổng thể, Chatbot AI, phân quyền, API cốt lõi.

#### Công việc cụ thể:

| STT | Công việc                                                                                                  | File / Thành phần                              | Mức ưu tiên |
| :-: | ------------------------------------------------------------------------------------------------------------ | ------------------------------------------------ | :------------: |
|  1  | Tạo Middleware CheckAdminRole + CheckBarberRole                                                             | `app/Http/Middleware/`                         |     🔴 Cao     |
|  2  | Đăng ký middleware trong `bootstrap/app.php`                                                            | `bootstrap/app.php`                            |     🔴 Cao     |
|  3  | Cập nhật LoginController - redirect theo role (admin→dashboard, barber→barber.dashboard, customer→home) | `LoginController.php`                          |     🔴 Cao     |
|  4  | Cập nhật RegisterController - customer redirect về home                                                   | `RegisterController.php`                       |     🔴 Cao     |
|  5  | Tái cấu trúc routes/web.php - tách Admin route (prefix /admin) + Barber route (prefix /barber)           | `routes/web.php`                               |     🔴 Cao     |
|  6  | Viết routes/api.php cho các API endpoints                                                                  | `routes/api.php`                               |    🟡 Vừa    |
|  7  | Chatbot AI (Gemini/OpenAI) - xử lý câu hỏi về giá, kiểu tóc, giới thiệu thợ                       | `Api/ChatbotController.php` + view chat widget |    🟡 Vừa    |
|  8  | Gallery ảnh mẫu tóc                                                                                      | `resources/views/home/gallery.blade.php`       |    🟢 Thấp    |
|    |                                                                                                              |                                                  |                |

**Chi tiết kỹ thuật Chatbot AI**:

- Nhúng khung chat nổi (floating chat widget) ở góc dưới phải màn hình public
- Backend gọi API Gemini hoặc OpenAI để trả lời tự động
- Ngữ cảnh: giá dịch vụ, giới thiệu barber, kiểu tóc phù hợp
- Lưu lịch sử chat trong session

---

### 🙋 MEMBER 2 - CRM & REVIEWS (Khách hàng thân thiết & Đánh giá)

**Vai trò**: Thu thập phản hồi và hệ thống tích điểm khách hàng.

#### Công việc cụ thể:

| STT | Công việc                                                                            | File / Thành phần                                                                                             | Mức ưu tiên |
| :-: | -------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------- | :------------: |
|  1  | Tạo migration + model Review                                                          | `database/migrations/create_reviews_table.php`, `app/Models/Review.php`                                     |     🔴 Cao     |
|  2  | Tạo migration + model LoyaltyPoint, LoyaltyTransaction                                | `create_loyalty_points_table.php`, `create_loyalty_transactions_table.php`, `app/Models/LoyaltyPoint.php` |     🔴 Cao     |
|  3  | Api ReviewController - API gửi & lấy đánh giá                                     | `app/Http/Controllers/Api/ReviewApiController.php`                                                            |     🔴 Cao     |
|  4  | Customer ReviewController - form đánh giá sau khi cắt xong                         | `app/Http/Controllers/Customer/ReviewController.php` + `views/customer/reviews/create.blade.php`            |     🔴 Cao     |
|  5  | Hiển thị số sao trung bình của Barber ngoài trang chủ                           | Cập nhật `views/home/index.blade.php`                                                                       |    🟡 Vừa    |
|  6  | Tự động tích điểm khi Appointment hoàn thành (10.000đ = 1 điểm)             | `app/Models/LoyaltyPoint.php` (Observer/Event)                                                                |    🟡 Vừa    |
|  7  | Tự động thăng hạng thành viên (Bạc < Vàng < Kim Cương) & áp mã giảm giá | Logic trong `Customer/LoyaltyController.php`                                                                  |    🟡 Vừa    |
|  8  | Hiển thị thông tin điểm thưởng & hạng thành viên cho customer                | `views/customer/loyalty/index.blade.php`                                                                      |    🟢 Thấp    |
|  9  | Admin ReviewController - quản lý đánh giá (duyệt/xóa)                           | `app/Http/Controllers/Admin/ReviewController.php`                                                             |    🟢 Thấp    |
|    |                                                                                        |                                                                                                                 |                |

**Công thức tích điểm**:

```
- Mỗi 10.000đ hóa đơn = 1 điểm Loyalty Point
- Bạc (0 điểm): 0% giảm giá
- Vàng (100 điểm): 5% giảm giá
- Kim Cương (300 điểm): 10% giảm giá
```

---

### 🙋 MEMBER 3 - BOOKING NÂNG CAO & THANH TOÁN (Fintech)

**Vai trò**: Đặt lịch thông minh - chọn thợ/chọn giờ, thanh toán VNPAY, xuất hóa đơn.

#### Công việc cụ thể:

| STT | Công việc                                                                                                                                       | File / Thành phần                                                      | Mức ưu tiên |
| :-: | ------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------ | :------------: |
|  1  | Tạo migration + model BarberSchedule                                                                                                             | `create_barber_schedules_table.php`, `app/Models/BarberSchedule.php` |     🔴 Cao     |
|  2  | Tạo Customer AppointmentController - đặt lịch nâng cao                                                                                       | `app/Http/Controllers/Customer/AppointmentController.php`              |     🔴 Cao     |
|  3  | Chức năng chọn Barber → hiển thị khung giờ trống (dựa vào BarberSchedule & Appointment hiện có)                                       | Logic lọc giờ trống trong `Customer/AppointmentController.php`      |     🔴 Cao     |
|  4  | Tạo form đặt lịch nâng cao (chọn ngày → chọn thợ → chọn giờ → chọn dịch vụ)                                                      | `views/customer/appointments/create.blade.php`                         |     🔴 Cao     |
|  5  | Tích hợp VNPAY Sandbox - tạo URL thanh toán cọc 50.000đ                                                                                     | `Api/VnpayController.php`                                              |    🟡 Vừa    |
|  6  | Xử lý callback IPN từ VNPAY - tự động xác nhận lịch hẹn                                                                                 | `Api/VnpayController.php` (handleIPN)                                  |    🟡 Vừa    |
|  7  | Xuất hóa đơn PDF (barryvdh/laravel-dompdf)                                                                                                    | `Admin/PayrollController.php` hoặc tạo `InvoiceController.php`     |    🟡 Vừa    |
|  8  | Thêm cột deposit_amount, vnpay_transaction_code, deposit_status vào bảng appointments (Tạo Migration mới luôn tránh conflict phần admin) | `database/migrations/`                                                 |    🟡 Vừa    |
|  9  | Giao diện Admin duyệt lịch hẹn + xem trạng thái thanh toán cọc                                                                            | Cập nhật `views/admin/appointments/`                                 |    🟢 Thấp    |
|    |                                                                                                                                                   |                                                                          |                |

**Luồng đặt lịch nâng cao**:

```
1. Khách chọn Ngày → 
2. Hệ thống hiển thị danh sách Barber còn trống (dựa lịch nghỉ + schedule) →
3. Khách chọn Barber → 
4. Hệ thống hiển thị các khung giờ trống (dựa vào appointments đã có) →
5. Khách chọn giờ + dịch vụ →
6. Submit → Chuyển sang VNPAY để đặt cọc 50.000đ →
7. Thanh toán thành công → Status = confirmed
```

---

### 🙋 MEMBER 4 - BARBER BACKOFFICE (Giao diện riêng của Thợ)

**Vai trò**: Xây dựng workspace riêng cho Barber.

#### Công việc cụ thể:

| STT | Công việc                                                            | File / Thành phần                                                                                   | Mức ưu tiên |
| :-: | ---------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------- | :------------: |
|  1  | Tạo migration + model LeaveRequest                                    | `create_leave_requests_table.php`, `app/Models/LeaveRequest.php`                                  |     🔴 Cao     |
|  2  | Tạo Barber Dashboard layout riêng                                    | `resources/views/layouts/barber.blade.php`                                                          |     🔴 Cao     |
|  3  | Barber Dashboard - Timeline ca cắt trong ngày                        | `app/Http/Controllers/Barber/DashboardController.php` + `views/barber/dashboard.blade.php`        |     🔴 Cao     |
|  4  | Danh sách lịch hẹn của thợ (lọc theo barber_id)                  | `views/barber/appointments/index.blade.php`                                                         |     🔴 Cao     |
|  5  | Cập nhật trạng thái ca cắt: Confirmed → In Progress → Completed | Patch route + cập nhật `AppointmentController` (thêm status 'in_progress')                       |     🔴 Cao     |
|  6  | Nút gạt "Đang bận" (Busy Mode) - tạm khóa nhận lịch mới       | Cập nhật Barber model +`Barber/DashboardController.php`                                           |    🟡 Vừa    |
|  7  | Tạo đơn xin nghỉ phép (Leave Request)                             | `app/Http/Controllers/Barber/LeaveRequestController.php` + `views/barber/leaves/create.blade.php` |    🟡 Vừa    |
|  8  | Khi Admin duyệt nghỉ → tự động khóa khung giờ của Barber đó | Logic trong LeaveRequest (Observer hoặc Admin action)                                                |    🟡 Vừa    |
|  9  | Thêm role 'barber' vào User model nếu chưa có (đã có sẵn)     | Kiểm tra trong `User.php`                                                                          |  ✅ Có sẵn  |
|    |                                                                        |                                                                                                       |                |

**Nội dung Barber Dashboard** (dạng Timeline):

```
📅 Hôm nay: 04/06/2026

⏰ 09:00 - 10:00 🟢 Nguyễn Văn A - Haircut (Confirmed)
⏰ 10:30 - 11:30 🟡 Trần Thị B - Washing + Shaves (In Progress)
⏰ 14:00 - 15:00 🔴 Lê Văn C - Hair Color (Cancelled)
⏰ 15:30 - 16:30 🔵 Trống - (Có thể nhận khách vãng lai)

[🟢 Đã xác nhận] [🟡 Đang cắt] [🔴 Đã hủy] [🔵 Trống]
```

---

### 🙋 MEMBER 5 - ADMIN ANALYTICS (Chấm công, Hoa hồng & Thống kê)

**Vai trò**: Xử lý số liệu tài chính, lương thưởng và biểu đồ thống kê.

#### Công việc cụ thể:

| STT | Công việc                                                      | File / Thành phần                                             | Mức ưu tiên |
| :-: | ---------------------------------------------------------------- | --------------------------------------------------------------- | :------------: |
|  1  | Tạo migration + model Commission                                | `create_commissions_table.php`, `app/Models/Commission.php` |     🔴 Cao     |
|  2  | Tạo migration + model Payroll                                   | `create_payrolls_table.php`, `app/Models/Payroll.php`       |     🔴 Cao     |
|  3  | Admin cấu hình % hoa hồng cho từng Barber theo từng Service | `views/admin/commissions/`                                    |    🟡 Vừa    |
|  4  | Thuật toán tự động tính hoa hồng & lương cuối tháng   | `app/Http/Controllers/Admin/PayrollController.php`            |    🟡 Vừa    |
|  5  | Giao diện Admin duyệt bảng lương (pending → paid)          | `views/admin/payrolls/`                                       |    🟡 Vừa    |
|  6  | API Statistic - trả về dữ liệu JSON cho biểu đồ           | `app/Http/Controllers/Api/StatisticApiController.php`         |    🟡 Vừa    |
|  7  | Biểu đồ Line Chart - Doanh thu theo ngày/tuần/tháng        | `views/admin/statistics/revenue.blade.php` (Chart.js)         |    🟡 Vừa    |
|  8  | Biểu đồ Bar Chart - Khung giờ cao điểm trong tuần         | `views/admin/statistics/peak-hours.blade.php` (Chart.js)      |    🟢 Thấp    |
|  9  | Biểu đồ Pie Chart - Dịch vụ được ưa chuộng nhất       | `views/admin/statistics/services.blade.php` (Chart.js)        |    🟢 Thấp    |
|    |                                                                  |                                                                 |                |

**Công thức tính lương Barber**:

```
Tổng lương = Lương cứng + Tổng hoa hồng + Thưởng

Trong đó:
- Lương cứng: Cấu hình riêng cho từng Barber (VD: 2.000.000đ/tháng)
- Hoa hồng = Σ (Giá dịch vụ × % Hoa hồng của dịch vụ đó)
  + VD: Haircut giá 100.000đ, hoa hồng 30% → 30.000đ/ca
- Thưởng: Admin có thể cộng thêm (VD: thưởng doanh thu, thưởng thâm niên)
```

---

## 5. CHI TIẾT CHỨC NĂNG 3 VAI TRÒ

---

### 👤 VAI TRÒ CUSTOMER (Khách hàng)

**Mô tả**: Khách hàng đến tiệm cắt tóc, có thể đặt lịch online, đánh giá dịch vụ, tích điểm.

| Tính năng                           | Mô tả                                                                             | Giao diện                        | Phụ trách |
| ------------------------------------- | ----------------------------------------------------------------------------------- | --------------------------------- | :---------: |
| 🏠**Trang chủ**                | Xem Hero, About, Services, Price List, Contact (template gốc)                      | Public layout                     | ✅ Đã có |
| 👥**Xem danh sách Barber**     | Xem thông tin Barber, số sao trung bình, review từ khách khác                 | Section #section_2 + Phần Review |  Member 2  |
| 💇**Xem dịch vụ & giá**      | Xem danh sách dịch vụ + bảng giá                                               | Section #section_3 + #section_4   | ✅ Đã có |
| 📅**Đặt lịch hẹn**          | Chọn ngày → chọn Barber → chọn khung giờ trống → chọn dịch vụ → submit | Booking form nâng cao            |  Member 3  |
| 💳**Thanh toán cọc VNPAY**    | Sau khi đặt lịch, chuyển sang VNPAY đặt cọc 50.000đ                         | Trang VNPAY                       |  Member 3  |
| 📄**Xuất hóa đơn PDF**      | Tải về hóa đơn sau khi hoàn thành dịch vụ                                  | Trang xác nhận                  |  Member 3  |
| ⭐**Đánh giá & Review**      | Chấm sao (1-5) + comment cho Barber sau khi cắt xong                              | Form đánh giá                  |  Member 2  |
| 🏆**Tích điểm thành viên** | Tích lũy điểm từ hóa đơn (10.000đ = 1 điểm), đổi ưu đãi             | Trang điểm thưởng             |  Member 2  |
| 💬**Chatbot tư vấn**          | Hỏi đáp tự động về giá, kiểu tóc, thợ giỏi                              | Floating chat widget              |  Member 1  |
| 🔑**Đăng nhập / Đăng ký** | Đăng nhập, đăng ký tài khoản                                                | Form auth                         | ✅ Đã có |
| 🚪**Đăng xuất**              | Đăng xuất tài khoản                                                            | Sidebar public                    | ✅ Đã có |

**Luồng hoạt động chính của Customer**:

```
Trang chủ → Xem Barber/Services
    ↓
Đăng ký/Đăng nhập (nếu chưa)
    ↓
Đặt lịch (Chọn ngày → Chọn thợ → Chọn giờ → Chọn dịch vụ)
    ↓
Thanh toán cọc VNPAY (50.000đ)
    ↓
Đến tiệm cắt tóc đúng giờ
    ↓
Barber cập nhật status → Completed
    ↓
Đánh giá & Review (tích điểm tự động)
```

---

### ✂️ VAI TRÒ BARBER (Thợ cắt tóc)

**Mô tả**: Thợ cắt tóc có dashboard riêng để quản lý ca làm việc, xem khách đặt, xin nghỉ.

| Tính năng                                 | Mô tả                                                                       | Giao diện                     | Phụ trách |
| ------------------------------------------- | ----------------------------------------------------------------------------- | ------------------------------ | :---------: |
| 📊**Dashboard Timeline**              | Xem danh sách khách hôm nay dạng dòng thời gian                         | Barber layout                  |  Member 4  |
| 👥**Danh sách lịch hẹn**           | Xem tất cả lịch hẹn của mình (có filter: hôm nay/tuần này/tất cả) | Barber layout                  |  Member 4  |
| 🔄**Cập nhật trạng thái ca cắt** | Nút chuyển: Confirmed → In Progress → Completed                           | Dashboard + Appointment detail |  Member 4  |
| 🔴**Nút "Đang bận" (Busy Mode)**   | Gạt để tạm khóa nhận lịch mới (khi có khách vãng lai)              | Dashboard header               |  Member 4  |
| 📝**Xem thông tin khách**           | Xem tên, số điện thoại, dịch vụ đã đặt, ghi chú                   | Chi tiết lịch hẹn           |  Member 4  |
| 📋**Xin nghỉ phép**                 | Tạo đơn xin nghỉ gửi Admin duyệt                                        | Form xin nghỉ                 |  Member 4  |
| 🔑**Đăng nhập / Đăng xuất**     | Đăng nhập bằng tài khoản barber                                         | Auth form                      | ✅ Đã có |

**Lưu ý**: Barber **KHÔNG** có quyền:

- ❌ Quản lý Barber khác
- ❌ Quản lý Dịch vụ
- ❌ Xem doanh thu / thống kê
- ❌ Xem lịch hẹn của Barber khác

---

### 👑 VAI TRÒ ADMIN (Quản trị viên)

**Mô tả**: Chủ tiệm có toàn quyền quản lý Barber, Dịch vụ, Lịch hẹn, tài chính và xem thống kê.

| Tính năng                               | Mô tả                                                             | Giao diện              | Phụ trách |
| ----------------------------------------- | ------------------------------------------------------------------- | ----------------------- | :----------: |
| 📊**Dashboard tổng quan**          | Thống kê: tổng lịch hẹn, lịch chờ, số Barber, số dịch vụ | Admin layout            | ✅ Đã có |
| 👥**Quản lý Barber (CRUD)**       | Thêm/Sửa/Xóa thông tin Barber                                   | Admin layout            | ✅ Đã có |
| 💇**Quản lý Dịch vụ (CRUD)**    | Thêm/Sửa/Xóa dịch vụ & giá                                    | Admin layout            | ✅ Đã có |
| 📅**Quản lý Lịch hẹn**          | Xem tất cả lịch hẹn, duyệt/hủy, filter theo trạng thái      | Admin layout            | ✅ Đã có |
| ✅**Duyệt đơn xin nghỉ Barber** | Xem danh sách, duyệt/từ chối đơn nghỉ phép                  | Admin layout            | Member 4 + 5 |
| 💰**Cấu hình % hoa hồng**        | Set % hoa hồng cho từng Barber theo từng dịch vụ               | Admin layout            |   Member 5   |
| 📄**Bảng lương Barber**          | Tự động tính lương cuối tháng, duyệt trả lương          | Admin layout            |   Member 5   |
| ⭐**Quản lý đánh giá**         | Xem, duyệt, xóa đánh giá của khách                           | Admin layout            |   Member 2   |
| 📈**Biểu đồ doanh thu**          | Line Chart doanh thu theo ngày/tuần/tháng                        | Admin layout (Chart.js) |   Member 5   |
| 📊**Biểu đồ giờ cao điểm**    | Bar Chart khung giờ đông khách nhất                            | Admin layout (Chart.js) |   Member 5   |
| 🥧**Biểu đồ dịch vụ hot**      | Pie Chart dịch vụ được làm nhiều nhất                       | Admin layout (Chart.js) |   Member 5   |
| 🏠**Về trang chủ**                | Nút quay về trang public                                          | Admin layout header     | ✅ Đã có |
| 🚪**Đăng xuất**                  | Đăng xuất khỏi Admin                                            | Admin layout            | ✅ Đã có |

**Sidebar Admin (dự kiến sau khi hoàn thành)**:

```
✂️ BarberShop
├── 📊 Dashboard
├── 👥 Quản lý Barber
├── 🏷️ Quản lý Dịch vụ
├── 📅 Lịch hẹn
├── 💰 Hoa hồng & Lương
│   ├── Cấu hình hoa hồng
│   └── Bảng lương
├── ⭐ Đánh giá
├── 📈 Thống kê
│   ├── Doanh thu
│   ├── Giờ cao điểm
│   └── Dịch vụ
├── Đơn xin nghỉ
└── 🚪 Đăng xuất
```

---

## 6. LỘ TRÌNH ƯU TIÊN PHÁT TRIỂN

### Giai đoạn 1: NỀN TẢNG

| Tuần 1 | Công việc                                                                     |   Người   |
| ------- | ------------------------------------------------------------------------------- | :----------: |
| Day 1-2 | Tạo Middleware, cập nhật routes, phân quyền admin/barber/customer          |   Member 1   |
| Day 1-2 | Cập nhật LoginController redirect theo role                                   |   Member 1   |
| Day 3-4 | Barber Dashboard layout + Timeline dashboard                                    |   Member 4   |
| Day 3-4 | Tạo migrations: reviews, loyalty_points, leave_requests, commissions, payrolls | Member 2 + 5 |
| Day 5-7 | Customer đặt lịch nâng cao (chọn thợ, chọn giờ trống)                  |   Member 3   |

### Giai đoạn 2: TÍNH NĂNG CHÍNH

| Tuần 2-3 | Công việc                                       | Người |
| --------- | ------------------------------------------------- | :------: |
| Tuần 2   | Chatbot AI + Gallery mẫu tóc                    | Member 1 |
| Tuần 2   | Đánh giá & Review + Tích điểm thành viên  | Member 2 |
| Tuần 2-3 | Tích hợp VNPAY + Xuất hóa đơn PDF           | Member 3 |
| Tuần 2   | Xin nghỉ phép + Cập nhật trạng thái ca cắt | Member 4 |
| Tuần 2-3 | Hoa hồng & lương + Biểu đồ Chart.js         | Member 5 |

### Giai đoạn 3: HOÀN THIỆN & BÁO CÁO

|  |  |  |
| - | - | :-: |

---

## FILE NÀY CẦN ĐƯỢC CẬP NHẬT THEO TIẾN ĐỘ THỰC TẾ
