<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hồ sơ Barber - BarberShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #c8a97e; --primary-dark: #b08d5e; --dark: #1a1a1a;
            --dark2: #232323; --dark3: #2d2d2d; --text: #f0ece4; --text-muted: #8a8478;
            --success: #4ade80; --sidebar-w: 260px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:var(--dark); color:var(--text); min-height:100vh; }
        .sidebar {
            position:fixed; top:0; left:0; width:var(--sidebar-w); height:100vh;
            background:linear-gradient(180deg,#111 0%,#1a1a1a 100%);
            border-right:1px solid rgba(200,169,126,.1); z-index:100; display:flex; flex-direction:column;
        }
        .sidebar .brand { padding:24px 20px; text-align:center; border-bottom:1px solid rgba(200,169,126,.1); }
        .sidebar .brand i { color:var(--primary); font-size:1.6rem; }
        .sidebar .brand span { color:var(--primary); font-size:1.3rem; font-weight:700; margin-left:8px; letter-spacing:1px; }
        .sidebar .nav-link {
            color:var(--text-muted); padding:12px 24px; font-size:.9rem; font-weight:500;
            border-left:3px solid transparent; transition:all .25s; display:flex; align-items:center; gap:12px;
            text-decoration:none;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color:var(--primary); background:rgba(200,169,126,.06); border-left-color:var(--primary); }
        .main-content { margin-left:var(--sidebar-w); padding:24px 28px; min-height:100vh; }
        .top-bar {
            background:var(--dark2); border:1px solid rgba(200,169,126,.08); border-radius:14px;
            padding:16px 24px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center;
        }
        .top-bar h5 { color:var(--primary); font-weight:600; margin:0; }
        .user-badge { display:flex; align-items:center; gap:10px; }
        .user-badge .avatar {
            width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,var(--primary),var(--primary-dark));
            color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.9rem;
        }
        .dash-card {
            background:var(--dark2); border:1px solid rgba(200,169,126,.08); border-radius:14px; overflow:hidden;
        }
        .dash-card .card-head { padding:16px 20px; border-bottom:1px solid rgba(200,169,126,.06); font-weight:600; }
        .dash-card .card-body-inner { padding:16px 20px; }
        .btn-gold { background:var(--primary); color:#1a1a1a; border:none; font-weight:600; }
        .btn-gold:hover { background:var(--primary-dark); color:#1a1a1a; }
        .stat-number { font-size:1.5rem; font-weight:800; color:var(--text); }
        .stat-label { font-size:.82rem; color:var(--text-muted); }
        .service-tag {
            display:inline-flex; align-items:center; gap:6px; padding:6px 12px;
            background:var(--dark3); border:1px solid rgba(200,169,126,.1); border-radius:8px;
            font-size:.82rem; color:var(--text-muted); margin:4px;
        }
        .service-tag .price { color:var(--primary); font-weight:600; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="brand"><i class="bi bi-scissors"></i><span>Barber Panel</span></div>
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
                <a href="{{ route('barber.profile') }}" class="nav-link active">
                    <i class="bi bi-person-circle"></i> <span>Hồ sơ</span>
                </a>
            </li>
        </ul>
        <div class="mt-auto">
            <hr style="border-color:rgba(200,169,126,.1);margin:10px 20px;">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent" onclick="return confirm('Bạn có chắc muốn đăng xuất?')">
                    <i class="bi bi-box-arrow-right"></i> <span>Đăng xuất</span>
                </button>
            </form>
        </div>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h5><i class="bi bi-person-circle me-2"></i>Hồ sơ của tôi</h5>
            <div class="user-badge">
                <div>
                    <div style="font-size:.85rem;font-weight:600;">{{ Auth::user()->name }}</div>
                    <div style="font-size:.72rem;color:var(--text-muted);">Barber</div>
                </div>
                <div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2" style="background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);color:var(--success);border-radius:10px;">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
        @endif

        @if(!$barber)
        <div class="dash-card">
            <div class="card-body-inner text-center py-5">
                <i class="bi bi-exclamation-circle" style="font-size:3rem;color:var(--text-muted);"></i>
                <h5 class="mt-2" style="color:var(--warning);">Chưa có hồ sơ Barber</h5>
                <p style="color:var(--text-muted);">Vui lòng liên hệ Admin để được thiết lập hồ sơ.</p>
            </div>
        </div>
        @else
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="dash-card">
                    <div class="card-head"><i class="bi bi-info-circle me-2" style="color:var(--primary);"></i>Thông tin cá nhân</div>
                    <div class="card-body-inner">
                        <form method="POST" action="{{ route('barber.profile.update') }}">
                            @csrf @method('PUT')
                            <div class="mb-3">
                                <label class="form-label small" style="color:var(--text-muted);">Tên hiển thị</label>
                                <input type="text" name="name" class="form-control" value="{{ Auth::user()->name }}" style="background:var(--dark3);color:var(--text);border-color:rgba(200,169,126,.1);">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small" style="color:var(--text-muted);">Email</label>
                                <input type="email" class="form-control" value="{{ Auth::user()->email }}" readonly style="background:var(--dark3);color:var(--text-muted);border-color:rgba(200,169,126,.1);">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small" style="color:var(--text-muted);">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control" value="{{ $barber->phone }}" style="background:var(--dark3);color:var(--text);border-color:rgba(200,169,126,.1);">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small" style="color:var(--text-muted);">Giới thiệu</label>
                                <textarea name="bio" class="form-control" rows="3" style="background:var(--dark3);color:var(--text);border-color:rgba(200,169,126,.1);">{{ $barber->bio }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-gold w-100"><i class="bi bi-save me-1"></i>Lưu thay đổi</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="dash-card text-center p-4">
                            <div class="stat-number">{{ $totalCompleted }}</div>
                            <div class="stat-label">Đã hoàn thành</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dash-card text-center p-4">
                            <div class="stat-number">{{ number_format($totalRevenue, 0, ',', '.') }}đ</div>
                            <div class="stat-label">Tổng doanh thu</div>
                        </div>
                    </div>
                </div>
                <div class="dash-card">
                    <div class="card-head"><i class="bi bi-tags-fill me-2" style="color:var(--primary);"></i>Dịch vụ của tôi</div>
                    <div class="card-body-inner">
                        @if($services->count() > 0)
                            @foreach($services as $svc)
                            <div class="service-tag">
                                <i class="bi bi-tag" style="color:var(--primary);"></i>
                                {{ $svc->name }}
                                <span class="price">{{ number_format($svc->price, 0, ',', '.') }}đ</span>
                            </div>
                            @endforeach
                        @else
                            <p style="color:var(--text-muted);font-size:.82rem;text-align:center;padding:10px;">Chưa có dịch vụ nào được gán.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>