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
        .container { max-width:760px; margin:30px auto; padding:0 20px; }
        .card { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.05); overflow:hidden; margin-bottom:20px; }
        .card-head { padding:16px 20px; border-bottom:1px solid #eee; font-weight:600; font-size:1rem; background:#fafafa; }
        .card-body { padding:20px; }
        .detail-table { width:100%; }
        .detail-table td { padding:10px 8px; border-bottom:1px solid #f0f0f0; font-size:.9rem; vertical-align:top; }
        .detail-table td:first-child { font-weight:600; color:#555; width:160px; }
        .badge-status, .badge-payment { font-size:.8rem; padding:4px 14px; border-radius:20px; font-weight:600; display:inline-flex; align-items:center; gap:6px; }
        .badge-pending { background:#fef3c7; color:#92400e; }
        .badge-confirmed { background:#dbeafe; color:#1e40af; }
        .badge-completed { background:#d1fae5; color:#065f46; }
        .badge-cancelled { background:#fee2e2; color:#991b1b; }
        .badge-payment-paid { background:#dcfce7; color:#166534; }
        .badge-payment-unpaid { background:#fff7ed; color:#9a3412; }
        .badge-payment-review { background:#e0f2fe; color:#0f766e; }
        .btn-gold { background:var(--primary); color:#fff; border:none; padding:10px 24px; border-radius:8px; font-weight:600; text-decoration:none; font-size:.9rem; display:inline-flex; align-items:center; gap:8px; }
        .btn-gold:hover { background:var(--primary-dark); color:#fff; }
        .btn-outline { border:1px solid #ddd; color:#555; padding:10px 24px; border-radius:8px; text-decoration:none; font-size:.9rem; background:#fff; }
        .btn-outline:hover { background:#f0f0f0; }
        .btn-danger-soft { border:1px solid #fecaca; color:#b91c1c; padding:10px 24px; border-radius:8px; background:#fff5f5; font-size:.9rem; }
        .btn-danger-soft:hover { background:#fee2e2; color:#991b1b; }
        .service-list { margin:0; padding-left:18px; }
        .service-list li { margin-bottom:6px; }
        .combo-chip { display:inline-flex; align-items:center; gap:6px; background:#fff7ed; color:#9a3412; border:1px solid #fdba74; border-radius:999px; padding:6px 14px; font-size:.85rem; font-weight:700; }
        .combo-caption { margin-top:8px; color:#9a3412; font-size:.88rem; }
        .payment-box { background:#fffbeb; border:1px solid #fde68a; border-radius:12px; padding:18px; }
        .payment-note { background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:16px; color:#1e3a8a; }
        .warning-note { background:#fff7ed; border:1px solid #fed7aa; border-radius:12px; padding:16px; color:#9a3412; }
        .qr-card { background:#fff; border:1px dashed #d1d5db; border-radius:12px; padding:20px; text-align:center; }
        .qr-card img { max-width:260px; width:100%; height:auto; border-radius:12px; border:1px solid #eee; padding:10px; background:#fff; }
        .transfer-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:12px; margin-top:16px; }
        .transfer-item { background:#f8fafc; border-radius:10px; padding:12px 14px; }
        .transfer-item .label { font-size:.8rem; color:#64748b; margin-bottom:4px; }
        .transfer-item .value { font-weight:700; color:#111827; word-break:break-word; }
    </style>
</head>
<body>
    <div class="top-bar">
        <h4><i class="bi bi-info-circle"></i>Chi tiết lượt hẹn</h4>
        <div>
            <a href="{{ route('customer.appointments.index') }}"><i class="bi bi-arrow-left"></i> Back to My Schedules</a>
            <span style="margin:0 10px;color:#666;">|</span>
            <a href="{{ route('home') }}"><i class="bi bi-house-door"></i> Home</a>
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

        <div class="card">
            <div class="card-head">
                <i class="bi bi-receipt me-2" style="color:var(--primary);"></i>Thông tin lượt hẹn
            </div>
            <div class="card-body">
                @php
                    $statusClass = match($appointment->status){'pending'=>'badge-pending','confirmed'=>'badge-confirmed','completed'=>'badge-completed','cancelled'=>'badge-cancelled',default=>'badge-pending'};
                    $statusLabel = match($appointment->status){'pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','completed'=>'Hoàn thành','cancelled'=>'Đã hủy',default=>$appointment->status};
                @endphp
                <table class="detail-table">
                    <tr>
                        <td>Mã lượt hẹn</td>
                        <td><strong>{{ $bookingReference }}</strong></td>
                    </tr>
                    <tr>
                        <td>Barber</td>
                        <td>{{ $appointment->barber->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Ngày hẹn</td>
                        <td>{{ $appointment->appointment_date?->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td>Giờ bắt đầu</td>
                        <td>{{ $appointment->appointment_time }}</td>
                    </tr>
                    <tr>
                        <td>Trạng thái lịch</td>
                        <td><span class="badge-status {{ $statusClass }}">{{ $statusLabel }}</span></td>
                    </tr>
                    <tr>
                        <td>Trạng thái cọc</td>
                        <td>
                            <span class="badge-payment {{ $depositState === 'paid' ? 'badge-payment-paid' : ($depositState === 'awaiting_confirmation' ? 'badge-payment-review' : 'badge-payment-unpaid') }}">
                                <i class="bi {{ $depositState === 'paid' ? 'bi-shield-check' : ($depositState === 'awaiting_confirmation' ? 'bi-hourglass-split' : 'bi-wallet2') }}"></i>
                                {{ $depositStateLabel }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>Tiền cọc</td>
                        <td>{{ number_format($appointment->deposit_amount ?: 50000, 0, ',', '.') }}đ / lượt hẹn</td>
                    </tr>
                    @if($appointment->deposit_paid_at)
                        <tr>
                            <td>Thanh toán lúc</td>
                            <td>{{ $appointment->deposit_paid_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endif
                    @if($appointment->deposit_transaction_id)
                        <tr>
                            <td>Mã giao dịch</td>
                            <td><code>{{ $appointment->deposit_transaction_id }}</code></td>
                        </tr>
                    @endif
                    <tr>
                        <td>Tổng giá dịch vụ</td>
                        <td>{{ number_format($bookingTotalPrice, 0, ',', '.') }}đ</td>
                    </tr>
                    <tr>
                        <td>Tổng thời lượng</td>
                        <td>{{ $bookingTotalDuration }} phút</td>
                    </tr>
                    @if($bookingIsCombo)
                        <tr>
                            <td>Combo</td>
                            <td>
                                <span class="combo-chip"><i class="bi bi-stars"></i> {{ $bookingDisplayName }}</span>
                                <div class="combo-caption">Bao gồm: {{ $bookingAppointments->pluck('service.name')->filter()->unique()->implode(', ') }}</div>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td>{{ $bookingIsCombo ? 'Chi tiết dịch vụ' : 'Dịch vụ' }}</td>
                        <td>
                            <ul class="service-list">
                                @foreach($bookingAppointments as $bookingAppointment)
                                    <li>
                                        {{ $bookingAppointment->service->name ?? 'N/A' }}
                                        @if($bookingAppointment->service)
                                            · {{ number_format($bookingAppointment->service->price, 0, ',', '.') }}đ
                                            · {{ $bookingAppointment->service->duration_minutes }} phút
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                    @if($appointment->notes)
                        <tr>
                            <td>Ghi chú</td>
                            <td>{!! nl2br(e($appointment->notes)) !!}</td>
                        </tr>
                    @endif
                </table>

                <div class="mt-4 d-flex flex-wrap gap-2">
                    @if(!$showPaymentPanel && $canDeposit)
                        <a href="{{ route('customer.appointments.deposit', $appointment) }}" class="btn-gold">
                            <i class="bi bi-qr-code-scan"></i> Xem QR đặt cọc
                        </a>
                    @endif

                    @if($canCancel)
                        <form method="POST" action="{{ route('customer.appointments.cancel', $appointment) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger-soft" onclick="return confirm('Bạn chắc chắn muốn hủy lượt hẹn này?')">
                                <i class="bi bi-x-circle"></i> Hủy lịch hẹn
                            </button>
                        </form>
                    @endif

                    @if($appointment->status === 'completed')
                        @php
                            $hasReviewed = \App\Models\Review::where('appointment_id', $appointment->id)->exists();
                        @endphp
                        @if(!$hasReviewed)
                            <button type="button" class="btn-gold" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                <i class="bi bi-star-fill text-warning"></i> Đánh giá nhận mã 5K
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary" disabled>
                                <i class="bi bi-check-circle"></i> Đã đánh giá
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Review Modal -->
        <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-star-fill text-warning me-2"></i>Đánh giá trải nghiệm</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success d-none" id="review-success-alert"></div>
                        <div class="alert alert-danger d-none" id="review-error-alert"></div>

                        <form id="review-form">
                            <input type="hidden" id="review-appointment-id" value="{{ $appointment->id }}">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Không gian cửa hàng</label>
                                <div class="rating-stars" id="space-rating-group">
                                    <i class="bi bi-star cursor-pointer fs-4" data-val="1"></i>
                                    <i class="bi bi-star cursor-pointer fs-4" data-val="2"></i>
                                    <i class="bi bi-star cursor-pointer fs-4" data-val="3"></i>
                                    <i class="bi bi-star cursor-pointer fs-4" data-val="4"></i>
                                    <i class="bi bi-star cursor-pointer fs-4" data-val="5"></i>
                                    <input type="hidden" id="space_rating" name="space_rating" value="0">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Thái độ nhân viên</label>
                                <div class="rating-stars" id="staff-rating-group">
                                    <i class="bi bi-star cursor-pointer fs-4" data-val="1"></i>
                                    <i class="bi bi-star cursor-pointer fs-4" data-val="2"></i>
                                    <i class="bi bi-star cursor-pointer fs-4" data-val="3"></i>
                                    <i class="bi bi-star cursor-pointer fs-4" data-val="4"></i>
                                    <i class="bi bi-star cursor-pointer fs-4" data-val="5"></i>
                                    <input type="hidden" id="staff_rating" name="staff_rating" value="0">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Chất lượng dịch vụ</label>
                                <div class="rating-stars" id="service-rating-group">
                                    <i class="bi bi-star cursor-pointer fs-4" data-val="1"></i>
                                    <i class="bi bi-star cursor-pointer fs-4" data-val="2"></i>
                                    <i class="bi bi-star cursor-pointer fs-4" data-val="3"></i>
                                    <i class="bi bi-star cursor-pointer fs-4" data-val="4"></i>
                                    <i class="bi bi-star cursor-pointer fs-4" data-val="5"></i>
                                    <input type="hidden" id="service_rating" name="service_rating" value="0">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nhận xét (không bắt buộc)</label>
                                <textarea class="form-control" id="review-comment" rows="3" placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
                            </div>

                            <button type="submit" class="btn-gold w-100 justify-content-center" id="submit-review-btn">Gửi đánh giá & Nhận mã</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if($showPaymentPanel)
            <div class="card">
                <div class="card-head">
                    <i class="bi bi-credit-card me-2" style="color:var(--primary);"></i>Thanh toán cọc cho lượt hẹn
                </div>
                <div class="card-body">
                    <div class="payment-box">
                        <p class="mb-2"><strong>Số tiền cọc:</strong> {{ number_format($depositAmount ?? 50000, 0, ',', '.') }}đ</p>
                        <p class="mb-2"><strong>Phạm vi áp dụng:</strong> Khoản cọc này áp dụng cho toàn bộ lượt hẹn <strong>{{ $bookingReference }}</strong>, không phải từng dịch vụ riêng lẻ.</p>
                        <p class="mb-0"><strong>Lưu ý quan trọng:</strong> Sau khi admin xác nhận đã nhận cọc, lịch hẹn sẽ chuyển sang trạng thái xác nhận và không thể hủy online nữa.</p>
                    </div>

                    <div class="payment-note mt-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Quét mã QR bên dưới bằng app ngân hàng để chuyển khoản đúng số tiền cọc. Nội dung chuyển khoản phải giữ nguyên để cửa hàng đối soát nhanh hơn.
                    </div>

                    @if($transferConfigured && $transferQrUrl)
                        <div class="qr-card mt-3">
                            <div class="mb-3 fw-bold">Mã QR chuyển khoản đặt cọc</div>
                            <img src="{{ $transferQrUrl }}" alt="QR chuyển khoản đặt cọc">
                            <div class="small text-muted mt-3">
                                Nếu app ngân hàng không nhận QR, bạn vẫn có thể chuyển tay theo thông tin tài khoản bên dưới.
                            </div>
                        </div>
                    @else
                        <div class="warning-note mt-3">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            Cửa hàng chưa cấu hình tài khoản nhận cọc trong file môi trường, nên chưa thể hiển thị mã QR thật.
                        </div>
                    @endif

                    <div class="transfer-grid">
                        <div class="transfer-item">
                            <div class="label">Ngân hàng</div>
                            <div class="value">{{ $transferBankName ?: 'Chưa cấu hình' }}</div>
                        </div>
                        <div class="transfer-item">
                            <div class="label">Số tài khoản</div>
                            <div class="value">{{ $transferBankAccount ?: 'Chưa cấu hình' }}</div>
                        </div>
                        <div class="transfer-item">
                            <div class="label">Chủ tài khoản</div>
                            <div class="value">{{ $transferAccountName ?: 'Chưa cấu hình' }}</div>
                        </div>
                        <div class="transfer-item">
                            <div class="label">Nội dung chuyển khoản</div>
                            <div class="value">{{ $transferContent }}</div>
                        </div>
                    </div>

                    <div class="mt-3 d-flex gap-2 flex-wrap">
                        <form method="POST" action="{{ route('customer.appointments.processDeposit', $appointment) }}">
                            @csrf
                            <button type="submit" class="btn-gold" {{ !$transferConfigured ? 'disabled' : '' }}>
                                <i class="bi bi-check2-circle"></i> Tôi đã chuyển khoản, nhờ shop xác nhận
                            </button>
                        </form>
                        <a href="{{ route('customer.appointments.show', $appointment) }}" class="btn-outline">Quay lại chi tiết</a>
                    </div>
                </div>
            </div>
        @elseif($depositState === 'awaiting_confirmation')
            <div class="warning-note">
                <i class="bi bi-hourglass-split me-1"></i>
                Khoản cọc của bạn đang chờ cửa hàng xác nhận. Tạm thời bạn chưa thể hủy online hoặc gửi lại yêu cầu thanh toán.
            </div>
        @elseif($hasPaidDeposit)
            <div class="warning-note">
                <i class="bi bi-shield-lock me-1"></i>
                Lượt hẹn này đã thanh toán cọc. Bạn không thể hủy online. Nếu cần đổi lịch hoặc hỗ trợ thêm, vui lòng liên hệ cửa hàng.
            </div>
        @endif
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Logic cho đánh giá sao
            const setupStars = (groupId, inputId) => {
                const group = document.getElementById(groupId);
                if (!group) return;
                const stars = group.querySelectorAll('.bi-star, .bi-star-fill');
                const input = document.getElementById(inputId);

                stars.forEach(star => {
                    star.addEventListener('click', function() {
                        const val = parseInt(this.getAttribute('data-val'));
                        input.value = val;
                        // update UI
                        stars.forEach(s => {
                            const sVal = parseInt(s.getAttribute('data-val'));
                            if (sVal <= val) {
                                s.classList.remove('bi-star');
                                s.classList.add('bi-star-fill', 'text-warning');
                            } else {
                                s.classList.remove('bi-star-fill', 'text-warning');
                                s.classList.add('bi-star');
                            }
                        });
                    });
                });
            };

            setupStars('space-rating-group', 'space_rating');
            setupStars('staff-rating-group', 'staff_rating');
            setupStars('service-rating-group', 'service_rating');

            const reviewForm = document.getElementById('review-form');
            if (reviewForm) {
                reviewForm.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    
                    const btn = document.getElementById('submit-review-btn');
                    const successAlert = document.getElementById('review-success-alert');
                    const errorAlert = document.getElementById('review-error-alert');
                    
                    successAlert.classList.add('d-none');
                    errorAlert.classList.add('d-none');

                    const space = document.getElementById('space_rating').value;
                    const staff = document.getElementById('staff_rating').value;
                    const service = document.getElementById('service_rating').value;
                    
                    if (space == 0 || staff == 0 || service == 0) {
                        errorAlert.textContent = 'Vui lòng đánh giá đủ 3 tiêu chí nhé!';
                        errorAlert.classList.remove('d-none');
                        return;
                    }

                    const payload = {
                        appointment_id: document.getElementById('review-appointment-id').value,
                        space_rating: space,
                        staff_rating: staff,
                        service_rating: service,
                        comment: document.getElementById('review-comment').value
                    };

                    btn.disabled = true;
                    btn.textContent = 'Đang gửi...';

                    try {
                        const response = await fetch('/api/reviews', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();
                        
                        if (response.ok && data.success) {
                            successAlert.textContent = data.message;
                            successAlert.classList.remove('d-none');
                            reviewForm.reset();
                            // Ẩn form sau khi đánh giá xong
                            reviewForm.style.display = 'none';
                            
                            // Load lại trang sau 3 giây để cập nhật UI nút "Đã đánh giá"
                            setTimeout(() => {
                                window.location.reload();
                            }, 3000);
                        } else {
                            errorAlert.textContent = data.message || 'Có lỗi xảy ra, vui lòng thử lại.';
                            errorAlert.classList.remove('d-none');
                            btn.disabled = false;
                            btn.textContent = 'Gửi đánh giá & Nhận mã';
                        }
                    } catch (error) {
                        errorAlert.textContent = 'Lỗi kết nối tới máy chủ.';
                        errorAlert.classList.remove('d-none');
                        btn.disabled = false;
                        btn.textContent = 'Gửi đánh giá & Nhận mã';
                    }
                });
            }
        });
    </script>
</body>
</html>
