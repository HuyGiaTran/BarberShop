<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Appointment #{{ $appointment->id }} - BarberShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --primary: #bc9c22; --primary-dark: #a0801a; --dark: #1a1a2e; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:#f4f6f9; color:#333; min-height:100vh; }
        .top-bar { background:linear-gradient(135deg,#1a1a2e,#16213e); color:#fff; padding:20px 30px; display:flex; justify-content:space-between; align-items:center; }
        .top-bar h4 { margin:0; font-weight:700; }
        .top-bar h4 i { color:var(--primary); margin-right:10px; }
        .top-bar a { color:#fff; text-decoration:none; font-size:.9rem; }
        .top-bar a:hover { color:var(--primary); }
        .container { max-width:700px; margin:30px auto; padding:0 20px; }
        .card { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.05); overflow:hidden; margin-bottom:20px; }
        .card-head { padding:16px 20px; border-bottom:1px solid #eee; font-weight:600; font-size:1rem; background:#fafafa; }
        .card-body { padding:20px; }
        .detail-table { width:100%; }
        .detail-table td { padding:10px 8px; border-bottom:1px solid #f0f0f0; font-size:.9rem; }
        .detail-table td:first-child { font-weight:600; color:#555; width:130px; }
        .badge-status { font-size:.8rem; padding:4px 14px; border-radius:20px; font-weight:600; display:inline-block; }
        .badge-pending { background:#fef3c7; color:#92400e; }
        .badge-confirmed { background:#dbeafe; color:#1e40af; }
        .badge-completed { background:#d1fae5; color:#065f46; }
        .badge-cancelled { background:#fee2e2; color:#991b1b; }
        .btn-gold { background:var(--primary); color:#fff; border:none; padding:10px 24px; border-radius:8px; font-weight:600; text-decoration:none; font-size:.9rem; display:inline-flex; align-items:center; gap:8px; }
        .btn-gold:hover { background:var(--primary-dark); color:#fff; }
        .btn-outline { border:1px solid #ddd; color:#555; padding:10px 24px; border-radius:8px; text-decoration:none; font-size:.9rem; }
        .btn-outline:hover { background:#f0f0f0; }
        .simulation-box { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:16px; margin-top:16px; }
        .simulation-box small { color:#065f46; }
        .note-transfer { font-size:.85rem; color:#6b7280; background:#f0fdf4; padding:8px 14px; border-radius:6px; margin-top:6px; }
    </style>
</head>
<body>
    <div class="top-bar">
        <h4><i class="bi bi-info-circle"></i>Appointment #{{ $appointment->id }}</h4>
        <div>
            <a href="{{ route('customer.appointments.index') }}"><i class="bi bi-arrow-left"></i> Back to My Schedules</a>
            <span style="margin:0 10px;color:#666;">|</span>
            <a href="{{ route('home') }}"><i class="bi bi-house-door"></i> Home</a>
        </div>
    </div>

    <div class="container">
        @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-head"><i class="bi bi-receipt me-2" style="color:var(--primary);"></i>Appointment Details</div>
            <div class="card-body">
                <table class="detail-table">
                    <tr><td>ID</td><td>#{{ $appointment->id }}</td></tr>
                    <tr><td>Service</td><td><strong>{{ $appointment->service->name ?? 'N/A' }}</strong><br><small class="text-muted">{{ number_format($appointment->service->price ?? 0, 0, ',', '.') }}đ - {{ $appointment->service->duration_minutes ?? 0 }} min</small></td></tr>
                    <tr><td>Barber</td><td>{{ $appointment->barber->name ?? 'N/A' }}</td></tr>
                    <tr><td>Date</td><td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}</td></tr>
                    <tr><td>Time</td><td>{{ $appointment->appointment_time }}</td></tr>
                    <tr><td>Status</td><td>
                        @php
                            $bc = match($appointment->status){'pending'=>'badge-pending','confirmed'=>'badge-confirmed','completed'=>'badge-completed','cancelled'=>'badge-cancelled',default=>''};
                            $lb = match($appointment->status){'pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled',default=>$appointment->status};
                        @endphp
                        <span class="badge-status {{ $bc }}">{{ $lb }}</span>
                    </td></tr>
                    @if($appointment->notes)
                    <tr><td>Notes</td><td>{!! nl2br(e($appointment->notes)) !!}</td></tr>
                    @endif
                </table>

                @if(str_contains($appointment->notes ?? '', 'Chuyển từ'))
                <div class="note-transfer">
                    <i class="bi bi-arrow-left-right me-1"></i>
                    This appointment was transferred from another barber due to a leave request.
                </div>
                @endif
            </div>
        </div>

        @if($appointment->status === 'pending' || isset($depositAmount))
        <div class="card">
            <div class="card-head"><i class="bi bi-credit-card me-2" style="color:var(--primary);"></i>Deposit (VNPAY Simulation)</div>
            <div class="card-body">
                <p class="mb-3">A deposit of <strong>{{ number_format($depositAmount ?? 50000, 0, ',', '.') }}đ</strong> is required to confirm your appointment.</p>

                <div class="simulation-box">
                    <i class="bi bi-info-circle me-1"></i>
                    <small>This is a simulation of VNPAY payment gateway. In production, you would be redirected to VNPAY to complete the payment.</small>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <form method="POST" action="{{ route('customer.appointments.processDeposit', $appointment) }}">
                        @csrf
                        <button type="submit" class="btn btn-gold"><i class="bi bi-check-circle"></i> Simulate Payment 50,000đ</button>
                    </form>
                    <a href="{{ route('customer.appointments.show', $appointment) }}" class="btn btn-outline">Cancel</a>
                </div>
            </div>
        </div>
        @endif
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>