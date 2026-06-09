<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chi Tiết Đơn Xin Nghỉ Phép - BarberShop</title>
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
            padding: 16px 0;
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
            margin-left: -24px;
            margin-right: -20px;
            padding-left: 24px;
            padding-right: 20px;
        }
        .sidebar .nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        .sidebar .nav-item {
            display: flex;
        }
        .sidebar .nav {
            display: flex;
            flex-direction: column;
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

        /* Container */
        .container-box {
            background: var(--dark2);
            border: 1px solid rgba(200, 169, 126, .08);
            border-radius: 14px;
            padding: 32px;
        }

        /* Section */
        .section {
            margin-bottom: 32px;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid rgba(200, 169, 126, .15);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title i {
            font-size: 1.3rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: .8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .info-value {
            font-size: 1rem;
            color: var(--text);
            font-weight: 500;
            word-break: break-word;
        }

        .info-value.highlight {
            color: var(--primary);
            font-weight: 700;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: .9rem;
        }

        .status-badge.pending {
            background: rgba(251, 191, 36, .15);
            color: var(--warning);
            border: 1px solid var(--warning);
        }

        .status-badge.approved {
            background: rgba(74, 222, 128, .15);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .status-badge.rejected {
            background: rgba(248, 113, 113, .15);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .text-area-display {
            background: var(--dark3);
            border: 1px solid rgba(200, 169, 126, .1);
            border-radius: 8px;
            padding: 16px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .button-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid rgba(200, 169, 126, .08);
        }

        .btn {
            padding: 10px 24px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            transition: all .25s;
            font-size: .95rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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

        .btn-danger {
            background: rgba(248, 113, 113, .15);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .btn-danger:hover {
            background: rgba(248, 113, 113, .25);
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
            .container-box {
                padding: 20px;
            }
            .info-grid {
                grid-template-columns: 1fr;
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
            <li class="nav-item mt-auto" style="margin-top:auto!important;">
                <hr style="border-color:rgba(200,169,126,.1);margin:10px 20px;">
            </li>
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link w-100 text-start" style="border:none;background:none;">
                        <i class="bi bi-box-arrow-left"></i> <span>Đăng xuất</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <h4><i class="bi bi-file-earmark-text me-2"></i>Chi Tiết Đơn Xin Nghỉ Phép</h4>
            <a href="{{ route('barber.leave_requests.index') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i>Quay lại
            </a>
        </div>

        <!-- Content -->
        <div class="container-box">
        <!-- Status Section -->
            <div style="text-align: center; margin-bottom: 32px;">
                <div class="status-badge {{ $leaveRequest->status }}">
                    @switch($leaveRequest->status)
                        @case('pending')
                            <i class="bi bi-hourglass-split me-1"></i>Chờ xét duyệt
                            @break
                        @case('approved')
                            <i class="bi bi-check-circle-fill me-1"></i>Đã duyệt
                            @break
                        @case('rejected')
                            <i class="bi bi-x-circle-fill me-1"></i>Từ chối
                            @break
                    @endswitch
                </div>
                @if ($leaveRequest->status === 'rejected' && $leaveRequest->rejection_reason)
                    <div class="alert alert-danger mt-3 mb-0">
                        <strong>Lý do từ chối:</strong>
                        <p style="margin-top: 10px; white-space: pre-wrap;" class="mb-0">{{ $leaveRequest->rejection_reason }}</p>
                    </div>
                @endif
            </div>

            <!-- Thông tin người nhận -->
            <div class="section">
                <h5 class="section-title">
                    <i class="bi bi-person"></i>Thông tin người nhận
                </h5>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Người nhận</div>
                        <div class="info-value highlight">{{ $leaveRequest->recipient }}</div>
                    </div>
                </div>
            </div>

            <!-- Thông tin người làm đơn -->
            <div class="section">
                <h5 class="section-title">
                    <i class="bi bi-file-text"></i>Thông tin người làm đơn
                </h5>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Họ và tên</div>
                        <div class="info-value">{{ $leaveRequest->applicant_name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Ngày sinh</div>
                        <div class="info-value">{{ $leaveRequest->applicant_dob->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Số điện thoại</div>
                        <div class="info-value">{{ $leaveRequest->applicant_phone }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Địa chỉ</div>
                        <div class="info-value">{{ $leaveRequest->applicant_address }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Địa điểm công tác</div>
                        <div class="info-value">{{ $leaveRequest->applicant_workplace }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Chức vụ</div>
                        <div class="info-value">{{ $leaveRequest->applicant_position }}</div>
                    </div>
                </div>
            </div>

            <!-- Thời gian nghỉ -->
            <div class="section">
                <h5 class="section-title">
                    <i class="bi bi-calendar-range"></i>Thời gian nghỉ phép
                </h5>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Thời gian bắt đầu</div>
                        <div class="info-value highlight">{{ $leaveRequest->start_time->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Thời gian kết thúc</div>
                        <div class="info-value highlight">{{ $leaveRequest->end_time->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Thời gian xin</div>
                        <div class="info-value">{{ $leaveRequest->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- Lý do nghỉ -->
            <div class="section">
                <h5 class="section-title">
                    <i class="bi bi-chat-left-text"></i>Lý do nghỉ phép
                </h5>
                <div class="text-area-display">{{ $leaveRequest->reason }}</div>
            </div>

            <!-- Phương án bàn giao -->
            <div class="section">
                <h5 class="section-title">
                    <i class="bi bi-arrow-left-right"></i>Phương án bàn giao công việc
                </h5>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Người đảm nhiệm</div>
                        <div class="info-value highlight">{{ $leaveRequest->handover_person }}</div>
                    </div>
                </div>
            </div>

            <!-- Cam kết -->
            <div class="section">
                <h5 class="section-title">
                    <i class="bi bi-check-circle"></i>Cam kết
                </h5>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Trạng thái cam kết</div>
                        <div class="info-value">
                            @if ($leaveRequest->commitment)
                                <span style="color: var(--success);">
                                    <i class="bi bi-check-circle-fill me-1"></i>Đã cam kết
                                </span>
                            @else
                                <span style="color: var(--text-muted);">
                                    <i class="bi bi-x-circle-fill me-1"></i>Chưa cam kết
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Button Group -->
            <div class="button-group">
                <a href="{{ route('barber.leave_requests.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i>Quay lại danh sách
                </a>
                @if ($leaveRequest->status === 'pending')
                    <form method="POST" action="{{ route('barber.leave_requests.cancel', $leaveRequest) }}" style="margin: 0;" onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn này?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i>Hủy đơn
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
