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
        .btn-new {
            background: var(--primary);
            color: #000;
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all .25s;
        }
        .btn-new:hover {
            background: var(--primary-dark);
            color: #000;
            transform: translateY(-2px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 24px;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 16px;
            opacity: .5;
        }

        .empty-state p {
            font-size: 1rem;
            margin-bottom: 20px;
        }

        /* Make empty-state button smaller (match header button) */
        .empty-state .btn-new {
            padding: 8px 16px;
            font-size: .95rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* Card */
        .card-container {
            background: var(--dark2);
            border: 1px solid rgba(200, 169, 126, .08);
            border-radius: 14px;
            overflow: hidden;
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid rgba(200, 169, 126, .06);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-body {
            padding: 0;
        }

        .leave-item {
            padding: 20px 24px;
            border-bottom: 1px solid rgba(200, 169, 126, .04);
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 24px;
            align-items: center;
            transition: background .25s;
        }

        .leave-item:last-child {
            border-bottom: none;
        }

        .leave-item:hover {
            background: rgba(200, 169, 126, .03);
        }

        .leave-status-badge {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .8rem;
            text-align: center;
            color: #fff;
        }

        .leave-status-badge.pending {
            background: rgba(251, 191, 36, .2);
            color: var(--warning);
            border: 2px solid var(--warning);
        }

        .leave-status-badge.approved {
            background: rgba(74, 222, 128, .2);
            color: var(--success);
            border: 2px solid var(--success);
        }

        .leave-status-badge.rejected {
            background: rgba(248, 113, 113, .2);
            color: var(--danger);
            border: 2px solid var(--danger);
        }

        .leave-info {
            flex: 1;
        }

        .leave-info-row {
            display: flex;
            gap: 24px;
            margin-bottom: 12px;
        }

        .leave-info-item {
            flex: 1;
        }

        .leave-info-label {
            font-size: .8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .leave-info-value {
            font-size: .95rem;
            color: var(--text);
            font-weight: 500;
        }

        .leave-action {
            display: flex;
            gap: 0;
            flex-direction: column;
            align-items: stretch;
            width: calc(100% + 24px);
            margin-left: -24px;
            margin-right: -24px;
            margin-bottom: -20px;
        }

        .btn-sm-custom {
            padding: 6px 14px;
            font-size: .85rem;
            border-radius: 6px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all .25s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-view {
            background: transparent;
            color: var(--info);
            padding: 10px 24px;
            margin-left: 0;
            justify-content: flex-start;
            border-left: 3px solid transparent;
        }

        .btn-view:hover {
            background: rgba(200, 169, 126, .1);
            border-left-color: var(--primary);
            color: var(--primary);
            margin-left: 0;
        }

        .btn-cancel {
            background: transparent;
            color: var(--danger);
            padding: 10px 24px;
            margin-left: 0;
            justify-content: flex-start;
            border-left: 3px solid transparent;
        }

        .btn-cancel:hover {
            background: rgba(200, 169, 126, .1);
            border-left-color: var(--primary);
            color: var(--primary);
            margin-left: 0;
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
            .leave-item {
                grid-template-columns: auto 1fr;
            }
            .leave-action {
                grid-column: 1 / -1;
                flex-direction: row;
                align-items: center;
                justify-content: flex-start;
                margin-top: 12px;
                padding-top: 12px;
                border-top: 1px solid rgba(200, 169, 126, .04);
            }
            .leave-info-row {
                flex-direction: column;
                gap: 0;
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
            <h4><i class="bi bi-file-earmark-text me-2"></i>Đơn Xin Nghỉ Phép</h4>
            <a href="{{ route('barber.leave_requests.create') }}" class="btn-new">
                <i class="bi bi-plus-lg"></i>Tạo đơn mới
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <strong>Thành công!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <strong>Lỗi!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Leave Requests List -->
        <div class="card-container">
            @if ($leaveRequests->count() > 0)
                <div class="card-body">
                    @foreach ($leaveRequests as $request)
                        <div class="leave-item">
                            <div class="leave-status-badge {{ $request->status }}">
                                @switch($request->status)
                                    @case('pending')
                                        <span>Chờ<br>duyệt</span>
                                        @break
                                    @case('approved')
                                        <span>Đã<br>duyệt</span>
                                        @break
                                    @case('rejected')
                                        <span>Từ<br>chối</span>
                                        @break
                                @endswitch
                            </div>

                            <div class="leave-info">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                    <h6 style="margin: 0; color: var(--primary); font-weight: 700;">
                                        {{ $request->reason }}
                                    </h6>
                                    <span style="font-size: .8rem; color: var(--text-muted);">
                                        {{ $request->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                                <div class="leave-info-row">
                                    <div class="leave-info-item">
                                        <div class="leave-info-label">Từ ngày</div>
                                        <div class="leave-info-value">{{ $request->start_time->format('d/m/Y H:i') }}</div>
                                    </div>
                                    <div class="leave-info-item">
                                        <div class="leave-info-label">Đến ngày</div>
                                        <div class="leave-info-value">{{ $request->end_time->format('d/m/Y H:i') }}</div>
                                    </div>
                                    <div class="leave-info-item">
                                        <div class="leave-info-label">Người bàn giao</div>
                                        <div class="leave-info-value">{{ $request->handover_person }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="leave-action">
                                <a href="{{ route('barber.leave_requests.show', $request) }}" class="btn-sm-custom btn-view">
                                    <i class="bi bi-eye"></i>Chi tiết
                                </a>
                                @if ($request->status === 'pending')
                                    <form method="POST" action="{{ route('barber.leave_requests.cancel', $request) }}" style="margin: 0;" onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-sm-custom btn-cancel">
                                            <i class="bi bi-trash"></i>Hủy
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($leaveRequests->hasPages())
                    <div style="padding: 20px;">
                        {{ $leaveRequests->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @else
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p style="font-size: 1.1rem; margin-bottom: 8px;">Chưa có đơn xin nghỉ phép nào</p>
                    <p style="font-size: .9rem; margin-bottom: 24px;">Tạo đơn mới để gửi yêu cầu nghỉ phép.</p>
                    <a href="{{ route('barber.leave_requests.create') }}" class="btn-new">
                        <i class="bi bi-plus-lg"></i>Tạo đơn mới
                    </a>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
