<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barber Dashboard - BarberShop</title>
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
            background: linear-gradient(180deg, #2d3436 0%, #636e72 100%);
            padding-top: 20px;
            z-index: 100;
        }
        .sidebar .brand {
            color: #fdcb6e;
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 12px 25px;
            font-size: 0.95rem;
            border-left: 3px solid transparent;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.1);
            border-left-color: #fdcb6e;
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
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
            font-weight: 600;
            border-radius: 12px 12px 0 0 !important;
        }
        .timeline-item {
            border-left: 3px solid #fdcb6e;
            padding: 15px 20px;
            margin-bottom: 10px;
            background: #fff;
            border-radius: 0 8px 8px 0;
            transition: all 0.2s;
        }
        .timeline-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .timeline-item.pending { border-left-color: #fdcb6e; }
        .timeline-item.confirmed { border-left-color: #00b894; }
        .timeline-item.cancelled { border-left-color: #d63031; }
        .timeline-item.completed { border-left-color: #0984e3; }
        .status-badge {
            font-size: 0.75rem;
            padding: 3px 10px;
            border-radius: 20px;
        }
        .btn-barber {
            background: #fdcb6e;
            color: #2d3436;
            border: none;
        }
        .btn-barber:hover {
            background: #f9ca24;
            color: #2d3436;
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
                <a href="{{ route('barber.dashboard') }}" class="nav-link active">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item mt-4">
                <hr style="border-color: rgba(255,255,255,0.1); margin: 10px 20px;">
            </li>
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent"
                        onclick="return confirm('Bạn có chắc muốn đăng xuất?')">
                        <i class="bi bi-box-arrow-right"></i> Đăng xuất
                    </button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="navbar-top">
            <h5 class="mb-0"><i class="bi bi-calendar-week me-2"></i>Dashboard Barber</h5>
            <div class="d-flex align-items-center gap-2">
                <span>Xin chào, <strong>{{ Auth::user()->name }}</strong></span>
                <span class="badge bg-warning text-dark">Barber</span>
            </div>
        </div>

        @if (session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
        </div>
        @endif

        <div class="alert alert-light border d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <strong>{{ $barber->name }}</strong>
                <span class="text-muted">| {{ $todayCount }} lịch hôm nay, {{ $completedCount }} đã hoàn thành</span>
            </div>
            <span class="badge {{ $barber->is_active ? 'bg-success' : 'bg-secondary' }}">
                {{ $barber->is_active ? 'Sẵn sàng nhận lịch' : 'Đang bận' }}
            </span>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-calendar-check me-2"></i>Hôm nay
                    </div>
                    <div class="card-body text-center py-4">
                        <div style="font-size: 2.5rem; font-weight: bold;">{{ now()->format('d/m/Y') }}</div>
                        <div class="text-muted">{{ now()->format('l') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-people me-2"></i>Lịch hẹn hôm nay
                    </div>
                    <div class="card-body text-center py-4">
                        <div style="font-size: 2.5rem; font-weight: bold;">{{ $todayCount }}</div>
                        <div class="text-muted">khách hàng</div>
                        <div class="small text-muted mt-2">{{ $completedCount }} lịch đã hoàn thành</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-clock me-2"></i>Trạng thái
                    </div>
                    <div class="card-body text-center py-4">
                        <form method="POST" action="{{ route('barber.toggleStatus') }}">
                            @csrf
                            <div class="form-check form-switch d-inline-flex align-items-center gap-2" style="font-size: 1.2rem;">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    id="busyMode"
                                    {{ $barber->is_active ? 'checked' : '' }}
                                    onchange="this.form.submit()"
                                >
                                <label class="form-check-label" for="busyMode">
                                    {{ $barber->is_active ? 'Sẵn sàng nhận lịch' : 'Đang bận' }}
                                </label>
                            </div>
                            <div class="small text-muted mt-3">
                                Gạt công tắc để bật hoặc tắt trạng thái nhận lịch.
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-list-task me-2"></i>Timeline ca cắt hôm nay
            </div>
            <div class="card-body">
                @php
                    $statusClasses = [
                        'pending' => 'warning text-dark',
                        'confirmed' => 'success',
                        'completed' => 'primary',
                        'cancelled' => 'danger',
                    ];

                    $statusLabels = [
                        'pending' => 'Chờ xác nhận',
                        'confirmed' => 'Đã xác nhận',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
                    ];
                @endphp

                @forelse ($todayAppointments as $appointment)
                    @php
                        $status = $appointment->status;
                        $badgeClass = $statusClasses[$status] ?? 'secondary';
                        $label = $statusLabels[$status] ?? ucfirst($status);
                    @endphp

                    <div class="timeline-item {{ $status }}">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <div class="fw-bold fs-5">
                                    {{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('H:i') }}
                                </div>
                                <div class="mt-1">
                                    <strong>{{ $appointment->user->name }}</strong>
                                    <span class="text-muted">- {{ $appointment->service->name }}</span>
                                </div>
                                @if ($appointment->notes)
                                <div class="small text-muted mt-2">{{ $appointment->notes }}</div>
                                @endif
                            </div>

                            <span class="badge bg-{{ $badgeClass }} status-badge">{{ $label }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-5 mb-0">Chưa có lịch hẹn nào trong ngày hôm nay.</p>
                @endforelse
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
