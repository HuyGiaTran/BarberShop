<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Barber Dashboard - BarberShop</title>
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
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:var(--dark); color:var(--text); min-height:100vh; }

        /* Sidebar */
        .sidebar {
            position:fixed; top:0; left:0; width:var(--sidebar-w); height:100vh;
            background:linear-gradient(180deg,#111 0%,#1a1a1a 100%);
            border-right:1px solid rgba(200,169,126,.1);
            z-index:100; display:flex; flex-direction:column;
        }
        .sidebar .brand {
            padding:24px 20px; text-align:center;
            border-bottom:1px solid rgba(200,169,126,.1);
        }
        .sidebar .brand i { color:var(--primary); font-size:1.6rem; }
        .sidebar .brand span { color:var(--primary); font-size:1.3rem; font-weight:700; margin-left:8px; letter-spacing:1px; }
        .sidebar .nav { flex:1; padding:16px 0; }
        .sidebar .nav-link {
            color:var(--text-muted); padding:12px 24px; font-size:.9rem; font-weight:500;
            border-left:3px solid transparent; transition:all .25s; display:flex; align-items:center; gap:12px;
            text-decoration:none;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color:var(--primary); background:rgba(200,169,126,.06); border-left-color:var(--primary);
        }
        .sidebar .nav-link i { font-size:1.1rem; width:20px; text-align:center; }

        /* Main */
        .main-content { margin-left:var(--sidebar-w); padding:24px 28px; min-height:100vh; }

        /* Top bar */
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

        /* Stat cards */
        .stat-card {
            background:var(--dark2); border:1px solid rgba(200,169,126,.08); border-radius:14px;
            padding:20px; position:relative; overflow:hidden; transition:transform .2s, box-shadow .2s;
        }
        .stat-card:hover { transform:translateY(-3px); box-shadow:0 8px 25px rgba(0,0,0,.3); }
        .stat-card .stat-icon {
            width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center;
            font-size:1.3rem; margin-bottom:14px;
        }
        .stat-card .stat-number { font-size:1.8rem; font-weight:800; color:var(--text); }
        .stat-card .stat-label { font-size:.82rem; color:var(--text-muted); margin-top:2px; }
        .stat-icon.gold { background:rgba(200,169,126,.15); color:var(--primary); }
        .stat-icon.green { background:rgba(74,222,128,.12); color:var(--success); }
        .stat-icon.blue { background:rgba(96,165,250,.12); color:var(--info); }
        .stat-icon.orange { background:rgba(251,191,36,.12); color:var(--warning); }

        /* Cards */
        .dash-card {
            background:var(--dark2); border:1px solid rgba(200,169,126,.08); border-radius:14px; overflow:hidden;
        }
        .dash-card .card-head {
            padding:16px 20px; border-bottom:1px solid rgba(200,169,126,.06);
            display:flex; justify-content:space-between; align-items:center;
        }
        .dash-card .card-head h6 { margin:0; font-weight:600; color:var(--text); font-size:.95rem; }
        .dash-card .card-body-inner { padding:16px 20px; }

        /* Timeline */
        .timeline-item {
            display:flex; gap:16px; padding:14px 16px; border-radius:10px;
            border-left:3px solid var(--dark4); margin-bottom:8px;
            background:var(--dark3); transition:all .2s;
        }
        .timeline-item:hover { transform:translateX(4px); background:rgba(200,169,126,.04); }
        .timeline-item.status-pending { border-left-color:var(--warning); }
        .timeline-item.status-confirmed { border-left-color:var(--info); }
        .timeline-item.status-completed { border-left-color:var(--success); }
        .timeline-item.status-cancelled { border-left-color:var(--danger); }
        .timeline-time {
            min-width:55px; text-align:center; padding:6px 0;
        }
        .timeline-time .hour { font-size:1.1rem; font-weight:700; color:var(--primary); }
        .timeline-info { flex:1; }
        .timeline-info .client-name { font-weight:600; font-size:.92rem; }
        .timeline-info .service-name { font-size:.82rem; color:var(--text-muted); margin-top:2px; }
        .timeline-actions { display:flex; gap:6px; align-items:center; }
        .timeline-actions .btn { padding:4px 10px; font-size:.75rem; border-radius:6px; }

        /* Status badges */
        .badge-status { font-size:.72rem; padding:4px 10px; border-radius:20px; font-weight:600; }
        .badge-pending { background:rgba(251,191,36,.15); color:var(--warning); }
        .badge-confirmed { background:rgba(96,165,250,.15); color:var(--info); }
        .badge-completed { background:rgba(74,222,128,.15); color:var(--success); }
        .badge-cancelled { background:rgba(248,113,113,.15); color:var(--danger); }

        /* Buttons */
        .btn-gold { background:var(--primary); color:#1a1a1a; border:none; font-weight:600; }
        .btn-gold:hover { background:var(--primary-dark); color:#1a1a1a; }
        .btn-outline-gold { border:1px solid var(--primary); color:var(--primary); background:transparent; }
        .btn-outline-gold:hover { background:var(--primary); color:#1a1a1a; }
        .btn-sm-action { padding:4px 10px; font-size:.75rem; border-radius:6px; font-weight:500; }

        /* Empty state */
        .empty-state { text-align:center; padding:40px 20px; }
        .empty-state i { font-size:3rem; color:var(--dark4); margin-bottom:12px; }
        .empty-state p { color:var(--text-muted); font-size:.9rem; }

        /* Service tag */
        .service-tag {
            display:inline-flex; align-items:center; gap:6px; padding:6px 12px;
            background:var(--dark3); border:1px solid rgba(200,169,126,.1); border-radius:8px;
            font-size:.82rem; color:var(--text-muted); margin:4px;
        }
        .service-tag .price { color:var(--primary); font-weight:600; }

        /* Scrollbar */
        ::-webkit-scrollbar { width:6px; }
        ::-webkit-scrollbar-track { background:var(--dark); }
        ::-webkit-scrollbar-thumb { background:var(--dark4); border-radius:3px; }

        /* Responsive */
        @media(max-width:992px) {
            .sidebar { width:70px; }
            .sidebar .brand span, .sidebar .nav-link span { display:none; }
            .sidebar .nav-link { justify-content:center; padding:14px; }
            .main-content { margin-left:70px; padding:16px; }
        }
        @media(max-width:576px) {
            .sidebar { display:none; }
            .main-content { margin-left:0; }
        }

        /* Animate */
        @keyframes fadeUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
        .fade-up { animation:fadeUp .4s ease forwards; }
        .fade-up-d1 { animation-delay:.05s; opacity:0; }
        .fade-up-d2 { animation-delay:.1s; opacity:0; }
        .fade-up-d3 { animation-delay:.15s; opacity:0; }
        .fade-up-d4 { animation-delay:.2s; opacity:0; }
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
                    <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent"
                        onclick="return confirm('Bạn có chắc muốn đăng xuất?')">
                        <i class="bi bi-box-arrow-right"></i> <span>Đăng xuất</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top bar -->
        <div class="top-bar fade-up">
            <h5><i class="bi bi-grid-1x2-fill me-2"></i>Dashboard</h5>
            <div class="user-badge">
                <div>
                    <div style="font-size:.85rem;font-weight:600;">{{ Auth::user()->name }}</div>
                    <div style="font-size:.72rem;color:var(--text-muted);">Barber</div>
                </div>
                <div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);color:var(--success);border-radius:10px;">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:var(--danger);border-radius:10px;">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(!$barber)
        <div class="dash-card">
            <div class="card-body-inner empty-state">
                <i class="bi bi-exclamation-circle"></i>
                <h5 class="mt-2" style="color:var(--warning);">Chưa có hồ sơ Barber</h5>
                <p>Tài khoản của bạn chưa được liên kết với hồ sơ Barber. Vui lòng liên hệ Admin để được thiết lập.</p>
            </div>
        </div>
        @else
        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card fade-up fade-up-d1">
                    <div class="stat-icon gold"><i class="bi bi-calendar2-check"></i></div>
                    <div class="stat-number">{{ $todayCount }}</div>
                    <div class="stat-label">Lịch hẹn hôm nay</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card fade-up fade-up-d2">
                    <div class="stat-icon orange"><i class="bi bi-hourglass-split"></i></div>
                    <div class="stat-number">{{ $pendingCount }}</div>
                    <div class="stat-label">Đang chờ xử lý</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card fade-up fade-up-d3">
                    <div class="stat-icon green"><i class="bi bi-check2-circle"></i></div>
                    <div class="stat-number">{{ $completedToday }}</div>
                    <div class="stat-label">Hoàn thành hôm nay</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card fade-up fade-up-d4">
                    <div class="stat-icon blue"><i class="bi bi-cash-stack"></i></div>
                    <div class="stat-number">{{ number_format($totalRevenue, 0, ',', '.') }}<span style="font-size:.8rem;color:var(--text-muted);">đ</span></div>
                    <div class="stat-label">Doanh thu tháng này</div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Timeline hôm nay -->
            <div class="col-lg-8">
                <div class="dash-card fade-up" style="animation-delay:.25s;opacity:0;">
                    <div class="card-head">
                        <h6><i class="bi bi-clock-history me-2" style="color:var(--primary);"></i>Lịch hẹn hôm nay — {{ now()->format('d/m/Y') }}</h6>
                        <span class="badge-status badge-pending">{{ $todayCount }} ca</span>
                    </div>
                    <div class="card-body-inner">
                        @if($todayAppointments->count() > 0)
                            @foreach($todayAppointments as $apt)
                            <div class="timeline-item status-{{ $apt->status }}">
                                <div class="timeline-time">
                                    <div class="hour">{{ \Carbon\Carbon::parse($apt->appointment_time)->format('H:i') }}</div>
                                </div>
                                <div class="timeline-info">
                                    <div class="client-name">
                                        <i class="bi bi-person-fill me-1" style="color:var(--primary);font-size:.8rem;"></i>
                                        {{ $apt->user->name ?? 'N/A' }}
                                    </div>
                                    <div class="service-name">
                                        <i class="bi bi-tag-fill me-1"></i>{{ $apt->service->name ?? 'N/A' }}
                                        @if($apt->service && $apt->service->duration_minutes)
                                            · {{ $apt->service->duration_minutes }} phút
                                        @endif
                                        @if($apt->service && $apt->service->price)
                                            · {{ number_format($apt->service->price, 0, ',', '.') }}đ
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    @switch($apt->status)
                                        @case('pending')
                                            <span class="badge-status badge-pending">Chờ xác nhận</span>
                                            <form method="POST" action="{{ route('barber.appointments.updateStatus', $apt) }}" class="d-inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="confirmed">
                                                <button class="btn btn-sm-action btn-outline-gold" title="Xác nhận"><i class="bi bi-check-lg"></i></button>
                                            </form>
                                            <form method="POST" action="{{ route('barber.appointments.updateStatus', $apt) }}" class="d-inline"
                                                onsubmit="return confirm('Hủy lịch hẹn này?')">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button class="btn btn-sm-action btn-outline-danger" style="border-color:var(--danger);color:var(--danger);" title="Hủy"><i class="bi bi-x-lg"></i></button>
                                            </form>
                                            @break
                                        @case('confirmed')
                                            <span class="badge-status badge-confirmed">Đã xác nhận</span>
                                            <form method="POST" action="{{ route('barber.appointments.updateStatus', $apt) }}" class="d-inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="completed">
                                                <button class="btn btn-sm-action btn-gold" title="Hoàn thành"><i class="bi bi-check2-all"></i></button>
                                            </form>
                                            @break
                                        @case('completed')
                                            <span class="badge-status badge-completed">Hoàn thành</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge-status badge-cancelled">Đã hủy</span>
                                            @break
                                    @endswitch
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="empty-state">
                                <i class="bi bi-calendar-x"></i>
                                <p>Không có lịch hẹn nào hôm nay.<br>Hãy tận hưởng thời gian rảnh! ☕</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right column -->
            <div class="col-lg-4">
                <!-- Quick stats -->
                <div class="dash-card mb-3 fade-up" style="animation-delay:.3s;opacity:0;">
                    <div class="card-head">
                        <h6><i class="bi bi-bar-chart-fill me-2" style="color:var(--primary);"></i>Tổng quan</h6>
                    </div>
                    <div class="card-body-inner">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3" style="border-bottom:1px solid rgba(200,169,126,.06);">
                            <span style="color:var(--text-muted);font-size:.85rem;">Tổng đã hoàn thành</span>
                            <span style="font-weight:700;color:var(--success);">{{ $totalCompleted }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3" style="border-bottom:1px solid rgba(200,169,126,.06);">
                            <span style="color:var(--text-muted);font-size:.85rem;">Lịch trong tuần</span>
                            <span style="font-weight:700;color:var(--info);">{{ $weekCount }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span style="color:var(--text-muted);font-size:.85rem;">Trạng thái</span>
                            <form method="POST" action="{{ route('barber.status.update') }}" class="d-flex gap-2">
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
                                <select name="working_status" class="form-select form-select-sm text-center" style="background:var(--dark3);color:{{ $statusColor }};font-weight:600;border-color:rgba(200,169,126,.1);font-size:.8rem;padding-block:2px;box-shadow:none;text-align:center;text-align-last:center;" onchange="this.form.submit()">
                                    <option value="active" {{ $barber->working_status === 'active' ? 'selected' : '' }} style="color:var(--success);">Sẵn sàng</option>
                                    <option value="busy" {{ $barber->working_status === 'busy' ? 'selected' : '' }} style="color:var(--danger);">Bận</option>
                                    <option value="off" {{ $barber->working_status === 'off' ? 'selected' : '' }} style="color:var(--warning);">Nghỉ phép</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Upcoming -->
                <div class="dash-card mb-3 fade-up" style="animation-delay:.35s;opacity:0;">
                    <div class="card-head">
                        <h6><i class="bi bi-calendar-event me-2" style="color:var(--primary);"></i>Sắp tới</h6>
                    </div>
                    <div class="card-body-inner">
                        @if($upcomingAppointments->count() > 0)
                            @foreach($upcomingAppointments->take(5) as $uApt)
                            <div class="d-flex align-items-center gap-3 mb-3 pb-2" style="border-bottom:1px solid rgba(200,169,126,.04);">
                                <div style="min-width:45px;text-align:center;">
                                    <div style="font-size:.7rem;color:var(--text-muted);text-transform:uppercase;">{{ \Carbon\Carbon::parse($uApt->appointment_date)->format('D') }}</div>
                                    <div style="font-size:1.1rem;font-weight:700;color:var(--primary);">{{ \Carbon\Carbon::parse($uApt->appointment_date)->format('d') }}</div>
                                </div>
                                <div style="flex:1;">
                                    <div style="font-size:.85rem;font-weight:600;">{{ $uApt->user->name ?? 'N/A' }}</div>
                                    <div style="font-size:.75rem;color:var(--text-muted);">{{ \Carbon\Carbon::parse($uApt->appointment_time)->format('H:i') }} · {{ $uApt->service->name ?? '' }}</div>
                                </div>
                                <span class="badge-status badge-{{ $uApt->status }}">
                                    {{ $uApt->status === 'pending' ? 'Chờ' : 'OK' }}
                                </span>
                            </div>
                            @endforeach
                        @else
                            <div class="empty-state" style="padding:20px;">
                                <i class="bi bi-calendar-plus" style="font-size:2rem;"></i>
                                <p style="font-size:.82rem;">Chưa có lịch sắp tới</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Services -->
                <div class="dash-card fade-up" style="animation-delay:.4s;opacity:0;">
                    <div class="card-head">
                        <h6><i class="bi bi-tags-fill me-2" style="color:var(--primary);"></i>Dịch vụ của bạn</h6>
                    </div>
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