<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hồ sơ cá nhân - Barber Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--primary:#c8a97e;--primary-dark:#b08d5e;--dark:#1a1a1a;--dark2:#232323;--dark3:#2d2d2d;--dark4:#3a3a3a;--text:#f0ece4;--text-muted:#8a8478;--success:#4ade80;--warning:#fbbf24;--danger:#f87171;--info:#60a5fa;--sidebar-w:260px}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:var(--dark);color:var(--text);min-height:100vh}
        .sidebar{position:fixed;top:0;left:0;width:var(--sidebar-w);height:100vh;background:linear-gradient(180deg,#111 0%,#1a1a1a 100%);border-right:1px solid rgba(200,169,126,.1);z-index:100;display:flex;flex-direction:column}
        .sidebar .brand{padding:24px 20px;text-align:center;border-bottom:1px solid rgba(200,169,126,.1)}
        .sidebar .brand i{color:var(--primary);font-size:1.6rem}
        .sidebar .brand span{color:var(--primary);font-size:1.3rem;font-weight:700;margin-left:8px;letter-spacing:1px}
        .sidebar .nav-link{color:var(--text-muted);padding:12px 24px;font-size:.9rem;font-weight:500;border-left:3px solid transparent;transition:all .25s;display:flex;align-items:center;gap:12px;text-decoration:none}
        .sidebar .nav-link:hover,.sidebar .nav-link.active{color:var(--primary);background:rgba(200,169,126,.06);border-left-color:var(--primary)}
        .sidebar .nav-link i{font-size:1.1rem;width:20px;text-align:center}
        .main-content{margin-left:var(--sidebar-w);padding:24px 28px;min-height:100vh}
        .top-bar{background:var(--dark2);border:1px solid rgba(200,169,126,.08);border-radius:14px;padding:16px 24px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center}
        .top-bar h5{color:var(--primary);font-weight:600;margin:0}
        .user-badge{display:flex;align-items:center;gap:10px}
        .user-badge .avatar{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem}
        .dash-card{background:var(--dark2);border:1px solid rgba(200,169,126,.08);border-radius:14px;overflow:hidden}
        .dash-card .card-head{padding:16px 20px;border-bottom:1px solid rgba(200,169,126,.06);display:flex;justify-content:space-between;align-items:center}
        .dash-card .card-head h6{margin:0;font-weight:600;color:var(--text);font-size:.95rem}
        .dash-card .card-body-inner{padding:20px}
        .btn-gold{background:var(--primary);color:#1a1a1a;border:none;font-weight:600}
        .btn-gold:hover{background:var(--primary-dark);color:#1a1a1a}
        .form-control-dark,.form-control-dark:focus{background:var(--dark3);border:1px solid rgba(200,169,126,.12);color:var(--text);border-radius:8px}
        .form-control-dark:focus{border-color:var(--primary);box-shadow:0 0 0 2px rgba(200,169,126,.15)}
        .form-control-dark::placeholder{color:var(--text-muted)}
        .form-label-gold{color:var(--primary);font-weight:500;font-size:.85rem;margin-bottom:6px}
        .profile-avatar{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:2.5rem;margin:0 auto 16px}
        .profile-stat{text-align:center;padding:16px}
        .profile-stat .num{font-size:1.5rem;font-weight:800;color:var(--text)}
        .profile-stat .lbl{font-size:.78rem;color:var(--text-muted);margin-top:2px}
        .service-tag{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:var(--dark3);border:1px solid rgba(200,169,126,.1);border-radius:8px;font-size:.85rem;color:var(--text-muted);margin:4px}
        .service-tag .price{color:var(--primary);font-weight:600}
        @media(max-width:992px){.sidebar{width:70px}.sidebar .brand span,.sidebar .nav-link span{display:none}.sidebar .nav-link{justify-content:center;padding:14px}.main-content{margin-left:70px;padding:16px}}
        @media(max-width:576px){.sidebar{display:none}.main-content{margin-left:0}}
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="brand"><i class="bi bi-scissors"></i><span>Barber Panel</span></div>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="{{ route('barber.dashboard') }}" class="nav-link"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="{{ route('barber.appointments') }}" class="nav-link"><i class="bi bi-calendar2-week"></i><span>Lịch hẹn</span></a></li>
            <li class="nav-item"><a href="{{ route('barber.profile') }}" class="nav-link active"><i class="bi bi-person-circle"></i><span>Hồ sơ</span></a></li>
            <li class="nav-item" style="margin-top:auto;"><hr style="border-color:rgba(200,169,126,.1);margin:10px 20px;"></li>
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent" onclick="return confirm('Bạn có chắc muốn đăng xuất?')">
                        <i class="bi bi-box-arrow-right"></i><span>Đăng xuất</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h5><i class="bi bi-person-circle me-2"></i>Hồ sơ cá nhân</h5>
            <div class="user-badge">
                <div>
                    <div style="font-size:.85rem;font-weight:600;">{{ Auth::user()->name }}</div>
                    <div style="font-size:.72rem;color:var(--text-muted);">Barber</div>
                </div>
                <div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);color:var(--success);border-radius:10px;">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:var(--danger);border-radius:10px;">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(!$barber)
        <div class="dash-card">
            <div class="card-body-inner" style="text-align:center;padding:60px;">
                <i class="bi bi-exclamation-circle" style="font-size:3rem;color:var(--warning);"></i>
                <h5 class="mt-3" style="color:var(--warning);">Chưa có hồ sơ Barber</h5>
                <p style="color:var(--text-muted);">Vui lòng liên hệ Admin để được thiết lập hồ sơ.</p>
            </div>
        </div>
        @else
        <div class="row g-4">
            <!-- Profile card -->
            <div class="col-lg-4">
                <div class="dash-card">
                    <div class="card-body-inner" style="text-align:center;padding:30px;">
                        <div class="profile-avatar">{{ strtoupper(substr($barber->name, 0, 1)) }}</div>
                        <h5 style="font-weight:700;">{{ $barber->name }}</h5>
                        <p style="color:var(--text-muted);font-size:.85rem;">{{ Auth::user()->email }}</p>
                        @if($barber->phone)
                        <p style="color:var(--text-muted);font-size:.85rem;"><i class="bi bi-telephone me-1"></i>{{ $barber->phone }}</p>
                        @endif
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <form method="POST" action="{{ route('barber.status.update') }}">
                                @csrf
                                @method('PATCH')
                                @php
                                    $statusColor = match($barber->working_status) {
                                        'active' => 'var(--success)',
                                        'busy' => 'var(--danger)',
                                        'off' => 'var(--warning)',
                                        default => 'var(--text)'
                                    };
                                @endphp
                                <select name="working_status" class="form-select form-select-sm text-center" style="background:var(--dark3);color:{{ $statusColor }};font-weight:600;border-color:rgba(200,169,126,.2);font-size:.8rem;padding-block:4px;border-radius:20px;box-shadow:none;min-width:140px;text-align:center;text-align-last:center;" onchange="this.form.submit()">
                                    <option value="active" {{ $barber->working_status === 'active' ? 'selected' : '' }} style="color:var(--success);">Sẵn sàng</option>
                                    <option value="busy" {{ $barber->working_status === 'busy' ? 'selected' : '' }} style="color:var(--danger);">Bận</option>
                                    <option value="off" {{ $barber->working_status === 'off' ? 'selected' : '' }} style="color:var(--warning);">Nghỉ phép</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    <div style="border-top:1px solid rgba(200,169,126,.06);">
                        <div class="row g-0">
                            <div class="col-6 profile-stat" style="border-right:1px solid rgba(200,169,126,.06);">
                                <div class="num" style="color:var(--success);">{{ $totalCompleted }}</div>
                                <div class="lbl">Đã hoàn thành</div>
                            </div>
                            <div class="col-6 profile-stat">
                                <div class="num" style="color:var(--primary);">{{ number_format($totalRevenue, 0, ',', '.') }}đ</div>
                                <div class="lbl">Tổng doanh thu</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Services -->
                <div class="dash-card mt-3">
                    <div class="card-head">
                        <h6><i class="bi bi-tags-fill me-2" style="color:var(--primary);"></i>Dịch vụ phụ trách</h6>
                    </div>
                    <div class="card-body-inner">
                        @if($services->count() > 0)
                            @foreach($services as $svc)
                            <div class="service-tag">
                                <i class="bi bi-tag" style="color:var(--primary);"></i>
                                {{ $svc->name }}
                                <span class="price">{{ number_format($svc->price, 0, ',', '.') }}đ</span>
                                <span style="font-size:.75rem;color:var(--text-muted);">· {{ $svc->duration_minutes }}p</span>
                            </div>
                            @endforeach
                        @else
                            <p style="color:var(--text-muted);font-size:.82rem;text-align:center;">Chưa có dịch vụ nào.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Edit form -->
            <div class="col-lg-8">
                <div class="dash-card">
                    <div class="card-head">
                        <h6><i class="bi bi-pencil-square me-2" style="color:var(--primary);"></i>Chỉnh sửa hồ sơ</h6>
                    </div>
                    <div class="card-body-inner">
                        <form method="POST" action="{{ route('barber.profile.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label-gold">Họ tên</label>
                                <input type="text" name="name" class="form-control form-control-dark" value="{{ old('name', $barber->name) }}" placeholder="Nhập họ tên">
                                @error('name')<small style="color:var(--danger);">{{ $message }}</small>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label-gold">Email</label>
                                <input type="email" class="form-control form-control-dark" value="{{ Auth::user()->email }}" disabled style="opacity:.6;">
                                <small style="color:var(--text-muted);font-size:.75rem;">Email không thể thay đổi</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label-gold">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control form-control-dark" value="{{ old('phone', $barber->phone) }}" placeholder="Nhập số điện thoại">
                                @error('phone')<small style="color:var(--danger);">{{ $message }}</small>@enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label-gold">Giới thiệu bản thân</label>
                                <textarea name="bio" class="form-control form-control-dark" rows="4" placeholder="Mô tả ngắn về bản thân và kinh nghiệm...">{{ old('bio', $barber->bio) }}</textarea>
                                @error('bio')<small style="color:var(--danger);">{{ $message }}</small>@enderror
                            </div>

                            <button type="submit" class="btn btn-gold px-4">
                                <i class="bi bi-check-lg me-1"></i>Lưu thay đổi
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Bio preview -->
                @if($barber->bio)
                <div class="dash-card mt-3">
                    <div class="card-head">
                        <h6><i class="bi bi-quote me-2" style="color:var(--primary);"></i>Giới thiệu</h6>
                    </div>
                    <div class="card-body-inner">
                        <p style="color:var(--text-muted);font-size:.9rem;line-height:1.7;font-style:italic;">
                            "{{ $barber->bio }}"
                        </p>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
