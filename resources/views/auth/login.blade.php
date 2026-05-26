<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng nhập - BarberShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .auth-wrapper {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 520px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
        }

        /* --- Cột bên trái: Brand / Slogan --- */
        .auth-brand {
            flex: 1;
            background: linear-gradient(145deg, #e94560 0%, #c23152 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 50px 40px;
            color: #fff;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .auth-brand::before {
            content: '';
            position: absolute;
            width: 250px; height: 250px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            top: -60px; right: -60px;
        }
        .auth-brand::after {
            content: '';
            position: absolute;
            width: 180px; height: 180px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            bottom: -50px; left: -50px;
        }
        .auth-brand .brand-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
        }
        .auth-brand h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: 1px;
        }
        .auth-brand p {
            font-size: 0.95rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        .auth-brand .features {
            margin-top: 30px;
            text-align: left;
            width: 100%;
        }
        .auth-brand .features .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .auth-brand .features .feature-item i {
            font-size: 1.1rem;
            background: rgba(255,255,255,0.2);
            width: 34px; height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* --- Cột bên phải: Form --- */
        .auth-form-col {
            flex: 1;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 50px 45px;
        }
        .auth-form-col .form-title {
            font-size: 1.7rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 6px;
        }
        .auth-form-col .form-subtitle {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 30px;
        }

        .form-floating label { color: #6c757d; }
        .form-floating .form-control {
            border-radius: 10px;
            border: 1.5px solid #dee2e6;
            transition: border-color 0.25s, box-shadow 0.25s;
        }
        .form-floating .form-control:focus {
            border-color: #e94560;
            box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.15);
        }
        .form-floating .form-control.is-invalid {
            border-color: #dc3545;
            background-image: none;
        }

        .btn-login {
            background: linear-gradient(135deg, #e94560 0%, #c23152 100%);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            padding: 13px;
            width: 100%;
            transition: all 0.3s;
            letter-spacing: 0.5px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(233, 69, 96, 0.4);
            color: #fff;
        }
        .btn-login:active { transform: translateY(0); }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #adb5bd;
            font-size: 0.85rem;
            margin: 20px 0;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #dee2e6;
        }

        .register-link {
            text-align: center;
            font-size: 0.9rem;
            color: #6c757d;
        }
        .register-link a {
            color: #e94560;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }
        .register-link a:hover { color: #c23152; text-decoration: underline; }

        .alert { border-radius: 10px; font-size: 0.9rem; }

        .password-toggle {
            position: relative;
        }
        .password-toggle .toggle-btn {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
            padding: 4px;
            transition: color 0.2s;
        }
        .password-toggle .toggle-btn:hover { color: #e94560; }
        .password-toggle .form-control { padding-right: 45px; }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.88rem;
        }
        .remember-forgot .form-check-input:checked {
            background-color: #e94560;
            border-color: #e94560;
        }

        @media (max-width: 768px) {
            .auth-brand { display: none; }
            .auth-form-col { padding: 40px 30px; }
            .auth-wrapper { max-width: 450px; }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">

        {{-- Cột trái: Brand --}}
        <div class="auth-brand">
            <div class="brand-icon">✂️</div>
            <h1>BarberShop</h1>
            <p>Hệ thống quản lý tiệm tóc chuyên nghiệp — đặt lịch nhanh, quản lý thông minh.</p>

            <div class="features">
                <div class="feature-item">
                    <i class="bi bi-calendar-check"></i>
                    <span>Đặt lịch hẹn trực tuyến</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-people"></i>
                    <span>Quản lý đội ngũ barber</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Thống kê doanh thu realtime</span>
                </div>
            </div>
        </div>

        {{-- Cột phải: Form đăng nhập --}}
        <div class="auth-form-col">
            <div style="width: 100%; max-width: 360px;">
                <div class="form-title">Đăng nhập</div>
                <div class="form-subtitle">Chào mừng trở lại! Vui lòng đăng nhập để tiếp tục.</div>

                {{-- Alert thành công (vd: sau logout) --}}
                @if (session('success'))
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-3" role="alert">
                        <i class="bi bi-check-circle-fill"></i>
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Alert lỗi chung --}}
                @if ($errors->any())
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div>{{ $errors->first() }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                    @csrf

                    {{-- Email --}}
                    <div class="form-floating mb-3">
                        <input
                            type="email"
                            class="form-control @error('email') is-invalid @enderror"
                            id="email"
                            name="email"
                            placeholder="Email của bạn"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            autofocus
                            required
                        >
                        <label for="email"><i class="bi bi-envelope me-1"></i> Địa chỉ Email</label>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="form-floating mb-3 password-toggle">
                        <input
                            type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            id="password"
                            name="password"
                            placeholder="Mật khẩu"
                            autocomplete="current-password"
                            required
                        >
                        <label for="password"><i class="bi bi-lock me-1"></i> Mật khẩu</label>
                        <button type="button" class="toggle-btn" id="togglePassword" title="Hiển thị/Ẩn mật khẩu">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="remember-forgot mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember"
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label text-muted" for="remember">Ghi nhớ đăng nhập</label>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn-login" id="loginBtn">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
                    </button>
                </form>

                <div class="divider">hoặc</div>

                <div class="register-link">
                    Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle hiển thị/ẩn mật khẩu
        const toggleBtn  = document.getElementById('togglePassword');
        const passwordEl = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        toggleBtn.addEventListener('click', function () {
            const isPassword = passwordEl.getAttribute('type') === 'password';
            passwordEl.setAttribute('type', isPassword ? 'text' : 'password');
            toggleIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
        });

        // Hiệu ứng loading khi submit
        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn = document.getElementById('loginBtn');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang đăng nhập...';
            btn.disabled = true;
        });
    </script>
</body>
</html>