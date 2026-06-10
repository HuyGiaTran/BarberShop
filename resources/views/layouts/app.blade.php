<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BarberShop - Đặt lịch cắt tóc')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
        }
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            padding-top: 20px;
            z-index: 100;
            transition: all 0.3s;
        }
        .sidebar .brand {
            color: #e94560;
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .sidebar .brand i {
            margin-right: 10px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 12px 25px;
            font-size: 0.95rem;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.1);
            border-left-color: #e94560;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.15);
            border-left-color: #e94560;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }
        .navbar-top {
            background: #fff;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar-top .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .navbar-top .user-info .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e94560;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .page-header {
            margin-bottom: 25px;
        }
        .page-header h2 {
            color: #1a1a2e;
            font-weight: 600;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
            font-weight: 600;
            border-radius: 12px 12px 0 0 !important;
        }
        .stat-card {
            padding: 20px;
            border-radius: 12px;
            color: #fff;
        }
        .stat-card i {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }
        .bg-stat1 { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .bg-stat2 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .bg-stat3 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .bg-stat4 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .btn-barber {
            background: #e94560;
            color: #fff;
            border: none;
        }
        .btn-barber:hover {
            background: #d63851;
            color: #fff;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 0.9rem;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }
            .sidebar .brand span,
            .sidebar .nav-link span {
                display: none;
            }
            .main-content {
                margin-left: 60px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">
            <i class="bi bi-scissors"></i>
            <span>BarberShop</span>
        </div>
        <ul class="nav flex-column">
            @auth
             <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.barbers.index') }}" class="nav-link {{ request()->routeIs('admin.barbers.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Quản lý Barber</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.schedules.index') }}" class="nav-link {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-range"></i>
                    <span>Lịch làm việc</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.services.index') }}" class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                    <i class="bi bi-tag"></i>
                    <span>Quản lý Dịch vụ</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.appointments.index') }}" class="nav-link {{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i>
                    <span>Lịch hẹn</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.invoices.index') }}" class="nav-link {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>
                    <span>Hóa đơn</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.reviews.index') }}" class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                    <i class="bi bi-star"></i>
                    <span>Quản lý Đánh giá</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.leave-requests.index') }}" class="nav-link {{ request()->routeIs('admin.leave-requests.*') ? 'active' : '' }}">
                    <i class="bi bi-envelope-paper"></i>
                    <span>Nghỉ phép</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.payrolls.index') }}" class="nav-link {{ request()->routeIs('admin.payrolls.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-coin"></i>
                    <span>Bảng lương</span>
                </a>
            </li>
            <li class="nav-item mt-4">
                <hr style="border-color: rgba(255,255,255,0.1); margin: 10px 20px;">
            </li>
            <li class="nav-item">
                {{-- Logout dùng POST form để bảo mật (CSRF) --}}
                <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                    @csrf
                    <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent"
                        onclick="return confirm('Bạn có chắc muốn đăng xuất?')">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Đăng xuất</span>
                    </button>
                </form>
            </li>
            @endauth

            @guest
            <li class="nav-item">
                <a href="{{ route('login') }}" class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Đăng nhập</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('register') }}" class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}">
                    <i class="bi bi-person-plus"></i>
                    <span>Đăng ký</span>
                </a>
            </li>
            @endguest
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="navbar-top">
            <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
            <div class="user-info">
                @auth
                <span>Xin chào, <strong>{{ Auth::user()->name }}</strong>
                    @if(Auth::user()->role === 'admin')
                        <span class="badge bg-danger ms-1" style="font-size:0.7rem;">Admin</span>
                    @endif
                </span>
                <div class="avatar" title="{{ Auth::user()->email }}">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                @endauth
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Content -->
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @stack('scripts')
</body>
</html>
