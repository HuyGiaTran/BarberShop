<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lịch hẹn - Barber Dashboard</title>
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
        .dash-card .card-head{padding:16px 20px;border-bottom:1px solid rgba(200,169,126,.06);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px}
        .dash-card .card-head h6{margin:0;font-weight:600;color:var(--text);font-size:.95rem}
        .table-dark-custom{width:100%;border-collapse:collapse}
        .table-dark-custom th{padding:12px 16px;font-size:.78rem;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);border-bottom:1px solid rgba(200,169,126,.08);font-weight:600}
        .table-dark-custom td{padding:14px 16px;font-size:.88rem;border-bottom:1px solid rgba(200,169,126,.04);vertical-align:middle}
        .table-dark-custom tr:hover td{background:rgba(200,169,126,.03)}
        .badge-status{font-size:.72rem;padding:4px 10px;border-radius:20px;font-weight:600}
        .badge-pending{background:rgba(251,191,36,.15);color:var(--warning)}
        .badge-confirmed{background:rgba(96,165,250,.15);color:var(--info)}
        .badge-completed{background:rgba(74,222,128,.15);color:var(--success)}
        .badge-cancelled{background:rgba(248,113,113,.15);color:var(--danger)}
        .btn-gold{background:var(--primary);color:#1a1a1a;border:none;font-weight:600}
        .btn-gold:hover{background:var(--primary-dark);color:#1a1a1a}
        .btn-outline-gold{border:1px solid var(--primary);color:var(--primary);background:transparent}
        .btn-outline-gold:hover{background:var(--primary);color:#1a1a1a}
        .btn-sm-action{padding:4px 10px;font-size:.75rem;border-radius:6px;font-weight:500}
        .filter-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
        .filter-bar .form-control,.filter-bar .form-select{background:var(--dark3);border:1px solid rgba(200,169,126,.12);color:var(--text);font-size:.85rem;border-radius:8px;padding:6px 12px}
        .filter-bar .form-control:focus,.filter-bar .form-select:focus{border-color:var(--primary);box-shadow:0 0 0 2px rgba(200,169,126,.15)}
        .filter-bar .form-select option{background:var(--dark2);color:var(--text)}
        .empty-state{text-align:center;padding:40px 20px}
        .empty-state i{font-size:3rem;color:var(--dark4);margin-bottom:12px}
        .empty-state p{color:var(--text-muted);font-size:.9rem}
        .pagination .page-link{background:var(--dark3);border-color:rgba(200,169,126,.1);color:var(--text-muted);font-size:.85rem}
        .pagination .page-link:hover{background:rgba(200,169,126,.1);color:var(--primary);border-color:rgba(200,169,126,.2)}
        .pagination .page-item.active .page-link{background:var(--primary);border-color:var(--primary);color:#1a1a1a}
        @media(max-width:992px){.sidebar{width:70px}.sidebar .brand span,.sidebar .nav-link span{display:none}.sidebar .nav-link{justify-content:center;padding:14px}.main-content{margin-left:70px;padding:16px}}
        @media(max-width:576px){.sidebar{display:none}.main-content{margin-left:0}}
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand"><i class="bi bi-scissors"></i><span>Barber Panel</span></div>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="{{ route('barber.dashboard') }}" class="nav-link"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="{{ route('barber.appointments') }}" class="nav-link active"><i class="bi bi-calendar2-week"></i><span>Lịch hẹn</span></a></li>
            <li class="nav-item"><a href="{{ route('barber.leave_requests.index') }}" class="nav-link"><i class="bi bi-file-earmark-text"></i><span>Đơn xin nghỉ</span></a></li>
            <li class="nav-item"><a href="{{ route('barber.profile') }}" class="nav-link"><i class="bi bi-person-circle"></i><span>Hồ sơ</span></a></li>
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
            <h5><i class="bi bi-calendar2-week me-2"></i>Quản lý Lịch hẹn</h5>
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

        <div class="dash-card">
            <div class="card-head">
                <h6><i class="bi bi-list-task me-2" style="color:var(--primary);"></i>Danh sách lịch hẹn</h6>
                <form method="GET" action="{{ route('barber.appointments') }}" class="filter-bar">
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}" style="max-width:160px;">
                    <select name="status" class="form-select" style="max-width:160px;">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Chờ xác nhận</option>
                        <option value="confirmed" {{ request('status')=='confirmed'?'selected':'' }}>Đã xác nhận</option>
                        <option value="completed" {{ request('status')=='completed'?'selected':'' }}>Hoàn thành</option>
                        <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }}>Đã hủy</option>
                    </select>
                    <select name="period" class="form-select" style="max-width:140px;">
                        <option value="">Tất cả</option>
                        <option value="today" {{ request('period')=='today'?'selected':'' }}>Hôm nay</option>
                        <option value="week" {{ request('period')=='week'?'selected':'' }}>Tuần này</option>
                        <option value="month" {{ request('period')=='month'?'selected':'' }}>Tháng này</option>
                    </select>
                    <button type="submit" class="btn btn-gold btn-sm"><i class="bi bi-funnel me-1"></i>Lọc</button>
                    <a href="{{ route('barber.appointments') }}" class="btn btn-outline-gold btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
                </form>
            </div>
            <div style="padding:0;">
                @if($appointments->count() > 0)
                <div class="table-responsive">
                    <table class="table-dark-custom">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Giờ</th>
                                <th>Khách hàng</th>
                                <th>Dịch vụ</th>
                                <th>Giá</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($appointments as $apt)
                            <tr>
                                <td style="font-weight:600;">{{ $apt->appointment_date ? $apt->appointment_date->format('d/m/Y') : 'N/A' }}</td>
                                <td><span style="color:var(--primary);font-weight:600;">{{ \Carbon\Carbon::parse($apt->appointment_time)->format('H:i') }}</span></td>
                                <td>
                                    <div style="font-weight:500;">{{ $apt->user->name ?? 'N/A' }}</div>
                                    <div style="font-size:.75rem;color:var(--text-muted);">{{ $apt->user->phone ?? '' }}</div>
                                </td>
                                <td>{{ $apt->service->name ?? 'N/A' }}</td>
                                <td style="color:var(--primary);font-weight:600;">{{ $apt->service ? number_format($apt->service->price, 0, ',', '.') . 'đ' : 'N/A' }}</td>
                                <td>
                                    @switch($apt->status)
                                        @case('pending')<span class="badge-status badge-pending">Chờ xác nhận</span>@break
                                        @case('confirmed')<span class="badge-status badge-confirmed">Đã xác nhận</span>@break
                                        @case('completed')<span class="badge-status badge-completed">Hoàn thành</span>@break
                                        @case('cancelled')<span class="badge-status badge-cancelled">Đã hủy</span>@break
                                        @default<span class="badge-status" style="background:rgba(255,255,255,.08);color:var(--text-muted);">{{ $apt->status }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($apt->status === 'pending')
                                        <form method="POST" action="{{ route('barber.appointments.updateStatus', $apt) }}" class="d-inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="confirmed">
                                            <button class="btn btn-sm-action btn-outline-gold" title="Xác nhận"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('barber.appointments.updateStatus', $apt) }}" class="d-inline" onsubmit="return confirm('Hủy lịch hẹn này?')">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button class="btn btn-sm-action" style="border:1px solid var(--danger);color:var(--danger);" title="Hủy"><i class="bi bi-x-lg"></i></button>
                                        </form>
                                        @elseif($apt->status === 'confirmed')
                                        <form method="POST" action="{{ route('barber.appointments.updateStatus', $apt) }}" class="d-inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="completed">
                                            <button class="btn btn-sm-action btn-gold" title="Hoàn thành"><i class="bi bi-check2-all"></i></button>
                                        </form>
                                        @else
                                        <span style="font-size:.75rem;color:var(--text-muted);">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center p-3">
                    {{ $appointments->withQueryString()->links() }}
                </div>
                @else
                <div class="empty-state">
                    <i class="bi bi-calendar-x"></i>
                    <p>Không tìm thấy lịch hẹn nào phù hợp.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
