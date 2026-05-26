<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng ký - BarberShop</title>
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
            max-width: 960px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
        }

        /* --- Cột bên trái: Brand --- */
        .auth-brand {
            flex: 0 0 350px;
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
            background: rgba(255,255,255,0.06);
            top: -60px; right: -60px;
        }
        .auth-brand::after {
            content: '';
            position: absolute;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
            bottom: -60px; left: -60px;
        }
        .auth-brand .brand-icon { font-size: 4.5rem; margin-bottom: 18px; }
        .auth-brand h1 { font-size: 1.9rem; font-weight: 700; margin-bottom: 10px; }
        .auth-brand p { font-size: 0.9rem; opacity: 0.9; line-height: 1.6; }
        .auth-brand .steps {
            margin-top: 30px;
            text-align: left;
            width: 100%;
        }
        .auth-brand .step-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            font-size: 0.88rem;
            opacity: 0.9;
        }
        .step-num {
            width: 30px; height: 30px;
            border-radius: 50%;
            background: rgba(255,255,255,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
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
            padding: 45px 50px;
        }
        .form-title {
            font-size: 1.65rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 5px;
        }
        .form-subtitle {
            font-size: 0.88rem;
            color: #6c757d;
            margin-bottom: 28px;
        }

        .form-floating .form-control {
            border-radius: 10px;
            border: 1.5px solid #dee2e6;
            font-size: 0.95rem;
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
        .invalid-feedback { font-size: 0.82rem; }

        /* Strength bar */
        .password-strength { margin-top: 6px; }
        .strength-bar {
            height: 4px;
            border-radius: 4px;
            background: #dee2e6;
            overflow: hidden;
            margin-bottom: 4px;
        }
        .strength-fill {
            height: 100%;
            border-radius: 4px;
            width: 0;
            transition: width 0.3s, background 0.3s;
        }
        .strength-label { font-size: 0.78rem; color: #6c757d; }

        .btn-register {
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
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(233, 69, 96, 0.4);
            color: #fff;
        }
        .btn-register:active { transform: translateY(0); }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #adb5bd;
            font-size: 0.85rem;
            margin: 20px 0;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1;
            height: 1px;
            background: #dee2e6;
        }

        .login-link {
            text-align: center;
            font-size: 0.9rem;
            color: #6c757d;
        }
        .login-link a {
            color: #e94560;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }
        .login-link a:hover { color: #c23152; text-decoration: underline; }

        .alert { border-radius: 10px; font-size: 0.88rem; }

        .row-fields { display: flex; gap: 14px; }
        .row-fields > div { flex: 1; }

        .password-toggle { position: relative; }
        .toggle-btn {
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
        .toggle-btn:hover { color: #e94560; }
        .password-toggle .form-control { padding-right: 45px; }

        @media (max-width: 768px) {
            .auth-brand { display: none; }
            .auth-form-col { padding: 40px 30px; }
            .auth-wrapper { max-width: 480px; }
            .row-fields { flex-direction: column; gap: 0; }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">

        {{-- Cột trái: Brand --}}
        <div class="auth-brand">
            <div class="brand-icon">✂️</div>
            <h1>BarberShop</h1>
            <p>Tạo tài khoản ngay để quản lý tiệm tóc của bạn một cách chuyên nghiệp.</p>

            <div class="steps">
                <div class="step-item">
                    <div class="step-num">1</div>
                    <span>Điền thông tin cơ bản của bạn</span>
                </div>
                <div class="step-item">
                    <div class="step-num">2</div>
                    <span>Tạo mật khẩu an toàn (≥ 8 ký tự)</span>
                </div>
                <div class="step-item">
                    <div class="step-num">3</div>
                    <span>Bắt đầu quản lý ngay lập tức!</span>
                </div>
            </div>
        </div>

        {{-- Cột phải: Form đăng ký --}}
        <div class="auth-form-col">
            <div style="width: 100%; max-width: 420px;">
                <div class="form-title">Tạo tài khoản</div>
                <div class="form-subtitle">Tham gia BarberShop — miễn phí, không giới hạn.</div>

                {{-- Lỗi validation --}}
                @if ($errors->any())
                    <div class="alert alert-danger d-flex align-items-start gap-2 mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                        <div>
                            <strong>Vui lòng kiểm tra lại:</strong>
                            <ul class="mb-0 mt-1 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" id="registerForm" novalidate>
                    @csrf

                    {{-- Họ và tên --}}
                    <div class="form-floating mb-3">
                        <input
                            type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            placeholder="Họ và tên"
                            value="{{ old('name') }}"
                            autocomplete="name"
                            autofocus
                            required
                        >
                        <label for="name"><i class="bi bi-person me-1"></i> Họ và tên <span class="text-danger">*</span></label>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email và Phone cùng hàng --}}
                    <div class="row-fields">
                        <div class="form-floating mb-3">
                            <input
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                placeholder="Email"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                required
                            >
                            <label for="email"><i class="bi bi-envelope me-1"></i> Email <span class="text-danger">*</span></label>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input
                                type="tel"
                                class="form-control @error('phone') is-invalid @enderror"
                                id="phone"
                                name="phone"
                                placeholder="Số điện thoại"
                                value="{{ old('phone') }}"
                                autocomplete="tel"
                            >
                            <label for="phone"><i class="bi bi-telephone me-1"></i> Số điện thoại</label>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Mật khẩu --}}
                    <div class="form-floating mb-1 password-toggle">
                        <input
                            type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            id="password"
                            name="password"
                            placeholder="Mật khẩu"
                            autocomplete="new-password"
                            required
                        >
                        <label for="password"><i class="bi bi-lock me-1"></i> Mật khẩu <span class="text-danger">*</span></label>
                        <button type="button" class="toggle-btn" id="togglePassword" title="Hiện/Ẩn mật khẩu">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password strength indicator --}}
                    <div class="password-strength mb-3" id="strengthContainer" style="display:none;">
                        <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                        <span class="strength-label" id="strengthLabel"></span>
                    </div>

                    {{-- Xác nhận mật khẩu --}}
                    <div class="form-floating mb-4 password-toggle">
                        <input
                            type="password"
                            class="form-control @error('password_confirmation') is-invalid @enderror"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Xác nhận mật khẩu"
                            autocomplete="new-password"
                            required
                        >
                        <label for="password_confirmation"><i class="bi bi-lock-fill me-1"></i> Xác nhận mật khẩu <span class="text-danger">*</span></label>
                        <button type="button" class="toggle-btn" id="toggleConfirm" title="Hiện/Ẩn mật khẩu">
                            <i class="bi bi-eye" id="toggleIconConfirm"></i>
                        </button>
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn-register" id="registerBtn">
                        <i class="bi bi-person-plus me-2"></i>Tạo tài khoản
                    </button>
                </form>

                <div class="divider">hoặc</div>

                <div class="login-link">
                    Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập ngay</a>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // === Toggle hiển thị/ẩn mật khẩu ===
        function makeToggle(btnId, inputId, iconId) {
            const btn   = document.getElementById(btnId);
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);
            if (!btn) return;
            btn.addEventListener('click', function () {
                const isPass = input.type === 'password';
                input.type   = isPass ? 'text' : 'password';
                icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }
        makeToggle('togglePassword', 'password', 'toggleIcon');
        makeToggle('toggleConfirm',  'password_confirmation', 'toggleIconConfirm');

        // === Password strength indicator ===
        const pwInput     = document.getElementById('password');
        const strengthBox = document.getElementById('strengthContainer');
        const fill        = document.getElementById('strengthFill');
        const label       = document.getElementById('strengthLabel');

        pwInput.addEventListener('input', function () {
            const val = this.value;
            if (!val) { strengthBox.style.display = 'none'; return; }
            strengthBox.style.display = 'block';

            let score = 0;
            if (val.length >= 8)                    score++;
            if (/[A-Z]/.test(val))                  score++;
            if (/[0-9]/.test(val))                  score++;
            if (/[^A-Za-z0-9]/.test(val))           score++;

            const levels = [
                { pct: '25%',  color: '#dc3545', text: '⚠️ Yếu' },
                { pct: '50%',  color: '#fd7e14', text: '👌 Trung bình' },
                { pct: '75%',  color: '#ffc107', text: '👍 Khá tốt' },
                { pct: '100%', color: '#198754', text: '✅ Mạnh' },
            ];
            const lvl = levels[Math.min(score - 1, 3)] || levels[0];
            fill.style.width      = lvl.pct;
            fill.style.background = lvl.color;
            label.textContent     = 'Độ mạnh: ' + lvl.text;
        });

        // === Loading state khi submit ===
        document.getElementById('registerForm').addEventListener('submit', function () {
            const btn = document.getElementById('registerBtn');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang tạo tài khoản...';
            btn.disabled  = true;
        });
    </script>
</body>
</html>
