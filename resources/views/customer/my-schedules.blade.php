<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Schedules - BarberShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --primary: #bc9c22; --primary-dark: #a0801a; --dark: #1a1a2e; --sidebar-w: 260px; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:#f4f6f9; color:#333; min-height:100vh; }
        .top-bar {
            background: linear-gradient(135deg, #1a1a2e, #16213e); color:#fff;
            padding: 20px 30px; display:flex; justify-content:space-between; align-items:center;
        }
        .top-bar h4 { margin:0; font-weight:700; }
        .top-bar h4 i { color:var(--primary); margin-right:10px; }
        .top-bar a { color:#fff; text-decoration:none; font-size:.9rem; }
        .top-bar a:hover { color:var(--primary); }
        .container { max-width:900px; margin:30px auto; padding:0 20px; }
        .schedule-card {
            background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.05);
            padding:20px; margin-bottom:16px; transition:transform .2s;
            border-left: 4px solid #ddd;
        }
        .schedule-card:hover { transform:translateY(-2px); box-shadow:0 4px 15px rgba(0,0,0,.1); }
        .schedule-card.pending { border-left-color: #fbbf24; }
        .schedule-card.confirmed { border-left-color: #3b82f6; }
        .schedule-card.completed { border-left-color: #10b981; }
        .schedule-card.cancelled { border-left-color: #ef4444; opacity:.7; }
        .schedule-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
        .schedule-date { font-weight:700; font-size:1.1rem; color:var(--dark); }
        .schedule-date i { color:var(--primary); margin-right:6px; }
        .schedule-body { display:flex; gap:20px; flex-wrap:wrap; }
        .schedule-info { flex:1; min-width:200px; }
        .schedule-info p { margin:4px 0; font-size:.9rem; color:#555; }
        .schedule-info p strong { color:#222; }
        .schedule-action { display:flex; align-items:center; gap:8px; }
        .btn-gold { background:var(--primary); color:#fff; border:none; padding:8px 18px; border-radius:8px; font-weight:600; text-decoration:none; font-size:.85rem; }
        .btn-gold:hover { background:var(--primary-dark); color:#fff; }
        .btn-outline { border:1px solid #ddd; color:#555; padding:8px 18px; border-radius:8px; text-decoration:none; font-size:.85rem; }
        .btn-outline:hover { background:#f0f0f0; }
        .badge-status { font-size:.75rem; padding:4px 12px; border-radius:20px; font-weight:600; }
        .badge-pending { background:#fef3c7; color:#92400e; }
        .badge-confirmed { background:#dbeafe; color:#1e40af; }
        .badge-completed { background:#d1fae5; color:#065f46; }
        .badge-cancelled { background:#fee2e2; color:#991b1b; }
        .empty-state { text-align:center; padding:60px 20px; color:#999; }
        .empty-state i { font-size:4rem; margin-bottom:16px; }
        .note-transfer { font-size:.8rem; color:#6b7280; background:#f0fdf4; padding:6px 12px; border-radius:6px; display:inline-block; margin-top:4px; }
        .pagination { display:flex; justify-content:center; gap:5px; margin-top:20px; }
        .pagination a, .pagination span { padding:8px 14px; border:1px solid #ddd; border-radius:6px; text-decoration:none; color:#555; }
        .pagination a:hover { background:#f0f0f0; }
        .pagination .active { background:var(--primary); color:#fff; border-color:var(--primary); }
    </style>
</head>
<body>
    <div class="top-bar">
        <h4><i class="bi bi-calendar-check"></i>My Schedules</h4>
        <div>
            <span style="margin-right:15px;">{{ Auth::user()->name }}</span>
            <a href="{{ route('home') }}"><i class="bi bi-house-door"></i> Back to Home</a>
            <span style="margin:0 10px;color:#666;">|</span>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" style="background:none;border:none;color:#fff;cursor:pointer;font-size:.9rem;">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <div class="container">
        @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2">{{ session('success') }}</div>
        @endif

        @if($appointments->count() > 0)
            @foreach($appointments as $apt)
            <div class="schedule-card {{ $apt->status }}">
                <div class="schedule-header">
                    <div class="schedule-date">
                        <i class="bi bi-calendar3"></i>
                        {{ \Carbon\Carbon::parse($apt->appointment_date)->format('d/m/Y') }}
                        <span style="font-weight:400;font-size:.9rem;color:#888;margin-left:8px;">
                            <i class="bi bi-clock"></i> {{ $apt->appointment_time }}
                        </span>
                    </div>
                    @php
                        $badgeClass = match($apt->status) {
                            'pending' => 'badge-pending',
                            'confirmed' => 'badge-confirmed',
                            'completed' => 'badge-completed',
                            'cancelled' => 'badge-cancelled',
                            default => ''
                        };
                        $label = match($apt->status) {
                            'pending' => 'Pending',
                            'confirmed' => 'Confirmed',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                            default => $apt->status
                        };
                    @endphp
                    <span class="badge-status {{ $badgeClass }}">{{ $label }}</span>
                </div>
                <div class="schedule-body">
                    <div class="schedule-info">
                        <p><strong>Service:</strong> {{ $apt->service->name ?? 'N/A' }}</p>
                        <p><strong>Barber:</strong> {{ $apt->barber->name ?? 'N/A' }}</p>
                        @if(str_contains($apt->notes ?? '', 'Chuyển từ'))
                        <p class="note-transfer"><i class="bi bi-arrow-left-right me-1"></i>{{ $apt->notes }}</p>
                        @endif
                        @if($apt->service && $apt->service->price)
                        <p><strong>Price:</strong> {{ number_format($apt->service->price, 0, ',', '.') }}đ</p>
                        @endif
                    </div>
                    <div class="schedule-action">
                        @if($apt->status === 'pending')
                        <a href="{{ route('customer.appointments.deposit', $apt) }}" class="btn btn-gold">
                            <i class="bi bi-credit-card"></i> Deposit 50k
                        </a>
                        @endif
                        <a href="{{ route('customer.appointments.show', $apt) }}" class="btn btn-outline">
                            <i class="bi bi-eye"></i> Details
                        </a>
                    </div>
                </div>
            </div>
            @endforeach

            @if($appointments->hasPages())
            <div class="pagination">
                {{ $appointments->links() }}
            </div>
            @endif
        @else
            <div class="empty-state">
                <i class="bi bi-calendar-x"></i>
                <h5>No appointments yet</h5>
                <p>You haven't booked any appointments. Let's make one!</p>
                <a href="{{ route('home') }}#booking-section" class="btn btn-gold mt-3">
                    <i class="bi bi-plus-circle"></i> Book a seat now
                </a>
            </div>
        @endif
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>