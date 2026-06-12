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
        :root { --primary: #bc9c22; --primary-dark: #a0801a; --dark: #1a1a2e; }
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
        .container { max-width:960px; margin:30px auto; padding:0 20px; }
        .schedule-card {
            background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.05);
            padding:20px; margin-bottom:16px; transition:transform .2s;
            border-left: 4px solid #ddd;
        }
        .schedule-card:hover { transform:translateY(-2px); box-shadow:0 4px 15px rgba(0,0,0,.1); }
        .schedule-card.pending { border-left-color: #fbbf24; }
        .schedule-card.confirmed { border-left-color: #3b82f6; }
        .schedule-card.completed { border-left-color: #10b981; }
        .schedule-card.cancelled { border-left-color: #ef4444; opacity:.8; }
        .schedule-header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:12px; }
        .schedule-date { font-weight:700; font-size:1.05rem; color:var(--dark); }
        .schedule-date i { color:var(--primary); margin-right:6px; }
        .schedule-subtitle { font-size:.82rem; color:#6b7280; margin-top:4px; }
        .schedule-body { display:flex; gap:20px; flex-wrap:wrap; }
        .schedule-info { flex:1; min-width:240px; }
        .schedule-info p { margin:6px 0; font-size:.92rem; color:#555; }
        .schedule-info p strong { color:#222; }
        .schedule-action { display:flex; align-items:flex-start; gap:8px; flex-wrap:wrap; justify-content:flex-end; }
        .service-list { margin:0; padding-left:18px; color:#555; }
        .service-list li { margin-bottom:4px; }
        .combo-chip { display:inline-flex; align-items:center; gap:6px; background:#fff7ed; color:#9a3412; border:1px solid #fdba74; border-radius:999px; padding:4px 12px; font-size:.8rem; font-weight:700; }
        .combo-meta { margin:8px 0 6px; color:#9a3412; font-size:.85rem; }
        .btn-gold { background:var(--primary); color:#fff; border:none; padding:8px 18px; border-radius:8px; font-weight:600; text-decoration:none; font-size:.85rem; }
        .btn-gold:hover { background:var(--primary-dark); color:#fff; }
        .btn-outline { border:1px solid #ddd; color:#555; padding:8px 18px; border-radius:8px; text-decoration:none; font-size:.85rem; background:#fff; }
        .btn-outline:hover { background:#f0f0f0; color:#333; }
        .btn-danger-soft { border:1px solid #fecaca; color:#b91c1c; padding:8px 18px; border-radius:8px; background:#fff5f5; font-size:.85rem; }
        .btn-danger-soft:hover { background:#fee2e2; color:#991b1b; }
        .badge-status, .badge-payment { font-size:.75rem; padding:4px 12px; border-radius:20px; font-weight:600; display:inline-flex; align-items:center; gap:6px; }
        .badge-pending { background:#fef3c7; color:#92400e; }
        .badge-confirmed { background:#dbeafe; color:#1e40af; }
        .badge-completed { background:#d1fae5; color:#065f46; }
        .badge-cancelled { background:#fee2e2; color:#991b1b; }
        .badge-payment-paid { background:#dcfce7; color:#166534; }
        .badge-payment-unpaid { background:#fff7ed; color:#9a3412; }
        .badge-payment-review { background:#e0f2fe; color:#0f766e; }
        .booking-note { font-size:.82rem; color:#6b7280; background:#f8fafc; padding:8px 12px; border-radius:8px; margin-top:12px; }
        .empty-state { text-align:center; padding:60px 20px; color:#999; }
        .empty-state i { font-size:4rem; margin-bottom:16px; }
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
            <div class="alert alert-success d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            </div>
        @endif

        @if($bookings->count() > 0)
            @foreach($bookings as $booking)
                @php
                    $primary = $booking['primary'];
                    $badgeClass = match($primary->status) {
                        'pending' => 'badge-pending',
                        'confirmed' => 'badge-confirmed',
                        'completed' => 'badge-completed',
                        'cancelled' => 'badge-cancelled',
                        default => 'badge-pending',
                    };
                    $label = match($primary->status) {
                        'pending' => 'Chờ xác nhận',
                        'confirmed' => 'Đã xác nhận',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
                        default => $primary->status,
                    };
                @endphp

                <div class="schedule-card {{ $primary->status }}">
                    <div class="schedule-header">
                        <div>
                            <div class="schedule-date">
                                <i class="bi bi-calendar3"></i>
                                {{ $primary->appointment_date?->format('d/m/Y') }}
                                <span style="font-weight:400;font-size:.9rem;color:#888;margin-left:8px;">
                                    <i class="bi bi-clock"></i> {{ $primary->appointment_time }}
                                </span>
                            </div>
                            <div class="schedule-subtitle">
                                Mã lượt hẹn: <strong>{{ $booking['reference'] }}</strong>
                                · Barber: <strong>{{ $primary->barber->name ?? 'N/A' }}</strong>
                            </div>
                            @if($booking['is_combo'])
                                <div class="mt-2">
                                    <span class="combo-chip"><i class="bi bi-stars"></i> {{ $booking['display_service_name'] }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="d-flex flex-column align-items-end gap-2">
                            <span class="badge-status {{ $badgeClass }}">{{ $label }}</span>
                            <span class="badge-payment {{ $booking['deposit_state'] === 'paid' ? 'badge-payment-paid' : ($booking['deposit_state'] === 'awaiting_confirmation' ? 'badge-payment-review' : 'badge-payment-unpaid') }}">
                                <i class="bi {{ $booking['deposit_state'] === 'paid' ? 'bi-shield-check' : ($booking['deposit_state'] === 'awaiting_confirmation' ? 'bi-hourglass-split' : 'bi-wallet2') }}"></i>
                                {{ $booking['deposit_state'] === 'paid' ? 'Đã thanh toán cọc' : ($booking['deposit_state'] === 'awaiting_confirmation' ? 'Chờ admin xác nhận cọc' : 'Chưa thanh toán cọc') }}
                            </span>
                        </div>
                    </div>

                    <div class="schedule-body">
                        <div class="schedule-info">
                            <p><strong>{{ $booking['is_combo'] ? 'Combo áp dụng:' : 'Dịch vụ:' }}</strong></p>
                            @if($booking['is_combo'])
                                <p class="combo-meta">Bao gồm: {{ $booking['service_names']->implode(', ') }}</p>
                            @endif
                            <ul class="service-list">
                                @foreach($booking['appointments'] as $appointmentItem)
                                    <li>
                                        {{ $appointmentItem->service->name ?? 'N/A' }}
                                        @if($appointmentItem->service)
                                            · {{ $appointmentItem->service->duration_minutes }} phút
                                            · {{ number_format($appointmentItem->service->price, 0, ',', '.') }}đ
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                            <p><strong>Tổng thời lượng:</strong> {{ $booking['total_duration'] }} phút</p>
                            <p><strong>Tổng giá dịch vụ:</strong> {{ number_format($booking['total_price'], 0, ',', '.') }}đ</p>
                            <p><strong>Tiền cọc áp dụng:</strong> {{ number_format($booking['deposit_amount'], 0, ',', '.') }}đ / lượt hẹn</p>
                        </div>

                        <div class="schedule-action">
                            @if($booking['can_deposit'])
                                <a href="{{ route('customer.appointments.deposit', $primary) }}" class="btn btn-gold">
                                    <i class="bi bi-qr-code-scan"></i> Xem QR đặt cọc
                                </a>
                            @endif

                            <a href="{{ route('customer.appointments.show', $primary) }}" class="btn btn-outline">
                                <i class="bi bi-eye"></i> Xem chi tiết
                            </a>

                            @if($booking['can_cancel'])
                                <form method="POST" action="{{ route('customer.appointments.cancel', $primary) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger-soft" onclick="return confirm('Bạn chắc chắn muốn hủy lượt hẹn này?')">
                                        <i class="bi bi-x-circle"></i> Hủy lịch hẹn
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    @if($booking['deposit_state'] === 'awaiting_confirmation')
                        <div class="booking-note">
                            Bạn đã gửi yêu cầu xác nhận chuyển khoản cọc. Cửa hàng sẽ kiểm tra giao dịch và xác nhận sớm nhất.
                        </div>
                    @elseif($booking['has_paid_deposit'])
                        <div class="booking-note">
                            Lượt hẹn này đã thanh toán cọc nên không thể hủy online. Nếu cần thay đổi, vui lòng liên hệ cửa hàng.
                        </div>
                    @elseif(!$booking['can_cancel'] && $primary->status === 'completed')
                        <div class="booking-note">
                            Lịch hẹn đã hoàn thành nên không thể hủy.
                        </div>
                    @endif
                </div>
            @endforeach

            @if($bookings->hasPages())
                <div class="pagination">
                    {{ $bookings->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="bi bi-calendar-x"></i>
                <h5>Chưa có lịch hẹn nào</h5>
                <p>Bạn chưa đặt lịch nào. Hãy thử book một chỗ nhé.</p>
                <a href="{{ route('home') }}#booking-section" class="btn btn-gold mt-3">
                    <i class="bi bi-plus-circle"></i> Book a seat now
                </a>
            </div>
        @endif
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
