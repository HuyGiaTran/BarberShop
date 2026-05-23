# 💈 BarberShop - Website Đặt Lịch Cắt Tóc

Đồ án môn học PHP - Framework Laravel


## 🚀 Hướng dẫn cài đặt và chạy dự án

### 1. Yêu cầu hệ thống

- **PHP** >= 8.1 (khuyến nghị 8.2+)
- **Composer** (quản lý package PHP)
- **MySQL** (qua XAMPP, Laragon, hoặc cài riêng)
- **Node.js** & **NPM** (cho frontend assets - không bắt buộc nếu chỉ chạy web)
- **Git** (clone dự án)

### 2. Clone dự án từ GitHub

Mở Terminal (Command Prompt / PowerShell / Git Bash) và chạy:

```bash
cd C:\xampp\htdocs  # Nếu dùng XAMPP trên Windows
# hoặc
cd /Applications/XAMPP/htdocs  # Nếu dùng XAMPP trên macOS
# hoặc thư mục bất kỳ bạn muốn đặt project

git clone https://github.com/HuyGiaTran/BarberShop.git
cd BarberShop
```

### 3. Cài đặt dependencies

```bash
composer install
```

> Lệnh này sẽ tải tất cả thư viện cần thiết (Laravel framework, Sanctum, ...)

### 4. Cấu hình môi trường

Tạo file `.env` từ file mẫu:

```bash
cp .env.example .env
# Trên Windows dùng: copy .env.example .env
```

Sau đó mở file `.env` và sửa các dòng sau:

```env
APP_NAME=BarberShop
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=barbershop_db
DB_USERNAME=root
DB_PASSWORD=
```

> ℹ️ **Lưu ý:** Nếu MySQL của bạn có mật khẩu, hãy nhập vào `DB_PASSWORD`.

### 5. Tạo database

**Cách 1: Dùng phpMyAdmin**
1. Mở trình duyệt, vào `http://localhost/phpmyadmin`
2. Click **New** (Mới)
3. Nhập tên database: `barbershop_db`
4. Chọn **utf8mb4_unicode_ci** trong phần Collation
5. Click **Create**

**Cách 2: Dùng Terminal (nếu có MySQL CLI)**
```bash
mysql -u root -p -e "CREATE DATABASE barbershop_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 6. Tạo key cho ứng dụng

```bash
php artisan key:generate
```

### 7. Chạy migration (tạo các bảng trong database)

```bash
php artisan migrate
```

Kết quả thành công sẽ hiện:
```
INFO  Running migrations.
  0001_01_01_000000_create_users_table ..................... DONE
  0001_01_01_000001_create_cache_table ..................... DONE
  0001_01_01_000002_create_jobs_table ...................... DONE
  2026_05_23_161632_create_personal_access_tokens_table ... DONE
  2026_05_23_165001_create_barbers_table .................. DONE
  2026_05_23_165007_create_services_table ................. DONE
  2026_05_23_165012_create_appointments_table ............. DONE
```

### 8. (Tùy chọn) Thêm dữ liệu mẫu

Nếu có seeder (dữ liệu mẫu), chạy:

```bash
php artisan db:seed
```

Hoặc chạy cả migrate + seed:

```bash
php artisan migrate:fresh --seed
```

### 9. Chạy server

```bash
php artisan serve --port=8080
```

### 10. Mở trình duyệt

Vào **http://127.0.0.1:8080** để xem trang web.

---

## 📂 Cấu trúc thư mục chính

```
laravel-shop/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php          # Trang tổng quan
│   │   │   ├── Admin/
│   │   │   │   ├── BarberController.php         # CRUD Barber (Web)
│   │   │   │   ├── ServiceController.php        # CRUD Dịch vụ (Web)
│   │   │   │   └── AppointmentController.php    # CRUD Lịch hẹn (Web)
│   │   │   ├── Api/
│   │   │   │   ├── ServiceApiController.php     # Services API
│   │   │   │   ├── BarberApiController.php      # Barbers API
│   │   │   │   ├── AppointmentApiController.php # Appointments API
│   │   │   │   ├── UserApiController.php        # Users API
│   │   │   │   └── AuthApiController.php        # Auth API (Sanctum)
│   │   │   └── Auth/
│   │   │       ├── LoginController.php          # Đăng nhập
│   │   │       └── RegisterController.php       # Đăng ký
│   └── Models/
│       ├── User.php
│       ├── Barber.php
│       ├── Service.php
│       └── Appointment.php
├── database/
│   └── migrations/                              # Các file tạo bảng
├── resources/views/
│   ├── layouts/app.blade.php                    # Layout chung
│   ├── dashboard.blade.php                      # Trang tổng quan
│   ├── barbers/                                 # Views cho Barber
│   ├── services/                                # Views cho Dịch vụ
│   ├── appointments/                            # Views cho Lịch hẹn
│   └── auth/                                    # Views cho Đăng nhập
└── routes/
    ├── web.php                                  # Routes Web
    └── api.php                                  # Routes API
```

---

## 🔗 Các route chính

### Web (Trang quản trị)

| Route | Chức năng | Method |
|-------|-----------|--------|
| `/` hoặc `/dashboard` | Trang tổng quan | GET |
| `/barbers` | Danh sách barber | GET |
| `/barbers/create` | Thêm barber | GET |
| `/barbers/{id}/edit` | Sửa barber | GET |
| `/services` | Danh sách dịch vụ | GET |
| `/services/create` | Thêm dịch vụ | GET |
| `/appointments` | Danh sách lịch hẹn | GET |
| `/appointments/create` | Đặt lịch mới | GET |
| `/login` | Đăng nhập | GET/POST |
| `/register` | Đăng ký | GET/POST |

### API (RESTful)

| Route | Chức năng | Auth |
|-------|-----------|------|
| `POST /api/login` | Đăng nhập | Không |
| `POST /api/register` | Đăng ký | Không |
| `GET /api/services` | Danh sách dịch vụ | Không |
| `GET /api/barbers` | Danh sách barber | Không |
| `POST /api/logout` | Đăng xuất | Sanctum |
| `GET /api/user` | Thông tin user | Sanctum |
| `GET/POST/PUT/DELETE /api/services/*` | CRUD Services | Sanctum |
| `GET/POST/PUT/DELETE /api/barbers/*` | CRUD Barbers | Sanctum |
| `GET/POST/PUT/DELETE /api/appointments/*` | CRUD Appointments | Sanctum |
| `GET/PUT /api/users/*` | Users | Sanctum |

> Test API bằng **Postman**: import các route trên, với các route cần auth thì thêm Header `Authorization: Bearer {token}` (lấy token từ API login/register).



## 📝 Ghi chú

- Dự án sử dụng **Laravel Framework 13.x**
- Xác thực API dùng **Laravel Sanctum**
- Frontend dùng **Bootstrap 5** + **Bootstrap Icons**
- CSDL: **MySQL** (XAMPP)
