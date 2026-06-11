<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đơn Xin Nghỉ Phép - BarberShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #c8a97e;
            --primary-dark: #b08d5e;
            --accent: #e8d5b7;
            --dark: #1a1a1a;
            --dark2: #232323;
            --dark3: #2d2d2d;
            --dark4: #3a3a3a;
            --text: #f0ece4;
            --text-muted: #8a8478;
            --success: #4ade80;
            --warning: #fbbf24;
            --danger: #f87171;
            --info: #60a5fa;
            --sidebar-w: 260px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--dark); color: var(--text); min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: linear-gradient(180deg, #111 0%, #1a1a1a 100%);
            border-right: 1px solid rgba(200, 169, 126, .1);
            z-index: 100;
            display: flex;
            flex-direction: column;
        }
        .sidebar .brand {
            padding: 24px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(200, 169, 126, .1);
        }
        .sidebar .brand i {
            color: var(--primary);
            font-size: 1.6rem;
        }
        .sidebar .brand span {
            color: var(--primary);
            font-size: 1.3rem;
            font-weight: 700;
            margin-left: 8px;
            letter-spacing: 1px;
        }
        .sidebar .nav {
            flex: 1;
            padding:0;
        }
        .sidebar .nav-link {
            color: var(--text-muted);
            padding: 12px 24px;
            font-size: .9rem;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all .25s;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: var(--primary); 
            background: rgba(200, 169, 126, .06); 
            border-left-color: var(--primary);
        }
        .sidebar .nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        /* Main */
        .main-content {
            margin-left: var(--sidebar-w);
            padding: 24px 28px;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: var(--dark2);
            border: 1px solid rgba(200, 169, 126, .08);
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h4 {
            color: var(--primary);
            font-weight: 700;
            margin: 0;
        }
        .btn-back {
            color: var(--text);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: color .25s;
        }
        .btn-back:hover {
            color: var(--primary);
        }

        /* Form Styles */
        .form-container {
            background: var(--dark2);
            border: 1px solid rgba(200, 169, 126, .08);
            border-radius: 14px;
            padding: 32px;
            margin-bottom: 24px;
        }

        .form-section {
            margin-bottom: 32px;
        }
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid rgba(200, 169, 126, .15);
        }

        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text);
            font-weight: 500;
            font-size: .95rem;
        }
        .form-group label .required {
            color: var(--danger);
        }
        .form-control,
        .form-select {
            background: var(--dark3) !important;
            border: 1px solid rgba(200, 169, 126, .1) !important;
            color: var(--text) !important;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: .95rem;
            transition: border-color .25s;
        }
        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 0.2rem rgba(200, 169, 126, .15) !important;
        }
        .form-control::placeholder {
            color: var(--text-muted);
        }
        .form-text {
            font-size: .85rem;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .invalid-feedback {
            display: block;
            color: var(--danger);
            font-size: .85rem;
            margin-top: 6px;
        }

        /* Checkbox */
        .form-check {
            margin: 20px 0;
        }
        .form-check-input {
            width: 20px;
            height: 20px;
            margin-top: 3px;
            background: var(--dark3) !important;
            border: 1px solid rgba(200, 169, 126, .2) !important;
            cursor: pointer;
        }
        .form-check-input:checked {
            background: var(--primary) !important;
            border-color: var(--primary) !important;
        }
        .form-check-label {
            margin-left: 8px;
            cursor: pointer;
            color: var(--text);
        }

        /* Buttons */
        .btn {
            padding: 10px 24px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            transition: all .25s;
            font-size: .95rem;
            cursor: pointer;
        }
        .btn-primary {
            background: var(--primary);
            color: #000;
        }
        .btn-primary:hover {
            background: var(--primary-dark);
            color: #000;
        }
        .btn-secondary {
            background: var(--dark3);
            color: var(--text);
            border: 1px solid rgba(200, 169, 126, .2);
        }
        .btn-secondary:hover {
            background: var(--dark4);
            border-color: var(--primary);
        }

        .button-group {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid rgba(200, 169, 126, .08);
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { width: 70px; }
            .sidebar .brand span, .sidebar .nav-link span { display: none; }
            .sidebar .nav-link { justify-content: center; padding: 14px; }
            .main-content { margin-left: 70px; padding: 16px; }
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
            .sidebar {
                transform: translateX(-100%);
            }
            .form-grid-2 {
                grid-template-columns: 1fr;
            }
            .form-container {
                padding: 20px;
            }
        }
        @media (max-width: 576px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">
            <i class="bi bi-scissors"></i>
            <span>Barber Panel</span>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ route('barber.dashboard') }}" class="nav-link {{ request()->routeIs('barber.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i> <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('barber.appointments') }}" class="nav-link {{ request()->routeIs('barber.appointments') ? 'active' : '' }}">
                    <i class="bi bi-calendar2-week"></i> <span>Lịch hẹn</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('barber.leave_requests.index') }}" class="nav-link {{ request()->routeIs('barber.leave_requests.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i> <span>Đơn xin nghỉ</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('barber.profile') }}" class="nav-link {{ request()->routeIs('barber.profile') ? 'active' : '' }}">
                    <i class="bi bi-person-circle"></i> <span>Hồ sơ</span>
                </a>
            </li>
        </ul>
        
        <div class="mt-auto">
            <hr style="border-color:rgba(200,169,126,.1);margin:10px 20px;">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent"
                    onclick="return confirm('Bạn có chắc muốn đăng xuất?')">
                    <i class="bi bi-box-arrow-right"></i> <span>Đăng xuất</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <h4><i class="bi bi-file-earmark-text me-2"></i>Tạo Đơn Xin Nghỉ Phép</h4>
            <a href="{{ route('barber.leave_requests.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i>Quay lại
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <strong>Lỗi!</strong> Vui lòng kiểm tra lại biểu mẫu.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Form -->
        <div class="form-container">
            <form action="{{ route('barber.leave_requests.store') }}" method="POST" novalidate>
                @csrf

                @if ($errors->any())
                    <div class="alert alert-dismissible fade show d-flex align-items-start gap-2" role="alert" style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:var(--danger);border-radius:10px;margin-bottom:24px;">
                        <div style="flex-shrink:0;margin-top:2px;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <div style="flex:1;">
                            <strong>Vui lòng sửa các lỗi sau:</strong>
                            <ul style="margin:8px 0 0 0;padding-left:20px;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Section 1: Thông tin người nhận -->
                <div class="form-section">
                    <h5 class="form-section-title"><i class="bi bi-person me-2"></i>Thông tin người nhận</h5>
                    <div class="form-group">
                        <label for="recipient">Người nhận đơn <span class="required">*</span></label>
                        <input
                            type="text"
                            class="form-control @error('recipient') is-invalid @enderror"
                            id="recipient"
                            name="recipient"
                            placeholder="VD: Ban Giám Đốc"
                            value="{{ old('recipient', 'Ban Giám Đốc') }}"
                            required>
                        @error('recipient')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Section 2: Thông tin người làm đơn -->
                <div class="form-section">
                    <h5 class="form-section-title"><i class="bi bi-file-text me-2"></i>Thông tin người làm đơn</h5>
                    <div class="form-group">
                        <label for="applicant_name">Họ và tên <span class="required">*</span></label>
                        <input
                            type="text"
                            class="form-control @error('applicant_name') is-invalid @enderror"
                            id="applicant_name"
                            name="applicant_name"
                            placeholder="Nhập họ và tên"
                            value="{{ old('applicant_name', $barber->name ?? '') }}"
                            required>
                        @error('applicant_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="applicant_dob">Ngày sinh <span class="required">*</span></label>
                            <input
                                type="date"
                                class="form-control @error('applicant_dob') is-invalid @enderror"
                                id="applicant_dob"
                                name="applicant_dob"
                                value="{{ old('applicant_dob') }}"
                                required>
                            @error('applicant_dob')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="applicant_phone">Số điện thoại <span class="required">*</span></label>
                            <input
                                type="tel"
                                class="form-control @error('applicant_phone') is-invalid @enderror"
                                id="applicant_phone"
                                name="applicant_phone"
                                placeholder="0123456789"
                                value="{{ old('applicant_phone', $barber->phone ?? '') }}"
                                pattern="[0-9]{10}"
                                required>
                            @error('applicant_phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text">Nhập đúng 10 chữa số</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="applicant_address">Địa chỉ <span class="required">*</span></label>
                        <input
                            type="text"
                            class="form-control @error('applicant_address') is-invalid @enderror"
                            id="applicant_address"
                            name="applicant_address"
                            placeholder="Nhập địa chỉ"
                            value="{{ old('applicant_address') }}"
                            required>
                        @error('applicant_address')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="applicant_workplace">Địa điểm công tác <span class="required">*</span></label>
                            <input
                                type="text"
                                class="form-control @error('applicant_workplace') is-invalid @enderror"
                                id="applicant_workplace"
                                name="applicant_workplace"
                                placeholder="VD: Chi nhánh Hà Nội"
                                value="{{ old('applicant_workplace') }}"
                                required>
                            @error('applicant_workplace')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="applicant_position">Chức vụ <span class="required">*</span></label>
                            <input
                                type="text"
                                class="form-control @error('applicant_position') is-invalid @enderror"
                                id="applicant_position"
                                name="applicant_position"
                                placeholder="VD: Barber"
                                value="{{ old('applicant_position', 'Barber') }}"
                                required>
                            @error('applicant_position')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 3: Thời gian nghỉ -->
                <div class="form-section">
                    <h5 class="form-section-title"><i class="bi bi-calendar-range me-2"></i>Thời gian nghỉ phép</h5>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="start_time">Ngày giờ bắt đầu <span class="required">*</span></label>
                            <input
                                type="datetime-local"
                                class="form-control @error('start_time') is-invalid @enderror"
                                id="start_time"
                                name="start_time"
                                value="{{ old('start_time') }}"
                                required>
                            @error('start_time')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="end_time">Ngày giờ kết thúc <span class="required">*</span></label>
                            <input
                                type="datetime-local"
                                class="form-control @error('end_time') is-invalid @enderror"
                                id="end_time"
                                name="end_time"
                                value="{{ old('end_time') }}"
                                required>
                            @error('end_time')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text">Phải sau thời gian bắt đầu</small>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Lý do nghỉ -->
                <div class="form-section">
                    <h5 class="form-section-title"><i class="bi bi-chat-left-text me-2"></i>Lý do nghỉ phép</h5>
                    <div class="form-group">
                        <label for="reason">Lý do <span class="required">*</span></label>
                        <textarea
                            class="form-control @error('reason') is-invalid @enderror"
                            id="reason"
                            name="reason"
                            rows="4"
                            placeholder="Nhập lý do xin nghỉ phép"
                            required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Section 5: Phương án bàn giao -->
                <div class="form-section">
                    <h5 class="form-section-title"><i class="bi bi-arrow-left-right me-2"></i>Phương án bàn giao công việc</h5>
                    <div class="form-group">
                        <label for="handover_person">Tên người đảm nhiệm <span class="required">*</span></label>
                        <select
                            class="form-control @error('handover_person') is-invalid @enderror"
                            id="handover_person"
                            name="handover_person"
                            required>
                            <option value="">-- Chọn nhân viên --</option>
                            @foreach($availableBarbers as $barberOption)
                                <option value="{{ $barberOption->name }}" {{ old('handover_person') == $barberOption->name ? 'selected' : '' }}>
                                    {{ $barberOption->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('handover_person')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Section 6: Cam kết -->
                <div class="form-section">
                    <h5 class="form-section-title"><i class="bi bi-check-circle me-2"></i>Cam kết</h5>
                    <div class="form-check">
                        <input type="hidden" name="commitment" value="0">
                        <input
                            class="form-check-input @error('commitment') is-invalid @enderror"
                            type="checkbox"
                            id="commitment"
                            name="commitment"
                            value="1"
                            {{ old('commitment') ? 'checked' : '' }}>
                        <label class="form-check-label" for="commitment">
                            Tôi cam kết thực hiện đúng các nội dung đã nêu trong đơn này <span class="required">*</span>
                        </label>
                        @error('commitment')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Button Group -->
                <div class="button-group">
                    <a href="{{ route('barber.leave_requests.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg me-1"></i>Hủy
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Gửi đơn
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add Bootstrap validation class on form submission
        (() => {
            'use strict';
            const forms = document.querySelectorAll('form[novalidate]');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                        
                        // Find first invalid field and scroll to it
                        const firstInvalid = form.querySelector(':invalid');
                        if (firstInvalid) {
                            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            setTimeout(() => firstInvalid.focus(), 500);
                        }
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // Custom validation messages
        const phoneInput = document.getElementById('applicant_phone');
        phoneInput?.addEventListener('invalid', (e) => {
            if (e.target.validity.valueMissing) {
                e.target.setCustomValidity('Vui lòng nhập số điện thoại.');
            } else if (e.target.validity.patternMismatch) {
                e.target.setCustomValidity('Số điện thoại phải có đúng 10 chữ số.');
            }
        });
        phoneInput?.addEventListener('input', (e) => e.target.setCustomValidity('')); // Xoá lỗi khi nhập lại

        const endTimeInput = document.getElementById('end_time');
        endTimeInput?.addEventListener('invalid', (e) => {
            if (e.target.validity.valueMissing) {
                e.target.setCustomValidity('Vui lòng chọn ngày giờ kết thúc.');
            }
        });
        endTimeInput?.addEventListener('input', (e) => e.target.setCustomValidity('')); // Xoá lỗi khi nhập lại

        const commitmentInput = document.getElementById('commitment');
        commitmentInput?.addEventListener('invalid', (e) => {
            if (e.target.validity.valueMissing) {
                e.target.setCustomValidity('Vui lòng xác nhận cam kết.');
            }
        });
        commitmentInput?.addEventListener('change', (e) => e.target.setCustomValidity('')); // Xoá lỗi khi tick lại
    </script>
</body>
</html>
