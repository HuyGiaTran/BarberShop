@extends('layouts.app')

@section('title', 'Xác nhận đặt cọc')
@section('page-title', 'Quản lý Đặt cọc chuyển khoản')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-light fw-bold text-secondary"><i class="bi bi-funnel"></i> Bộ lọc đặt cọc</div>
    <div class="card-body">
        <form action="{{ route('admin.payments.index') }}" method="GET" class="row g-3">
            <div class="col-md-5">
                <label for="q" class="form-label small fw-bold">Từ khóa</label>
                <input
                    type="text"
                    class="form-control"
                    id="q"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Mã lượt hẹn, mã thanh toán, khách hàng..."
                >
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label small fw-bold">Trạng thái</label>
                <select class="form-select" id="status" name="status">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Đã tạo QR</option>
                    <option value="awaiting_confirmation" {{ request('status') === 'awaiting_confirmation' ? 'selected' : '' }}>Chờ admin xác nhận</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Thất bại / từ chối</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Lọc
                </button>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary w-100">Xóa</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-qr-code-scan"></i> Danh sách đặt cọc chuyển khoản</h5>
    </div>
    <div class="card-body p-0">
        @if($payments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Lượt hẹn</th>
                            <th>Khách hàng</th>
                            <th>Barber</th>
                            <th>Số tiền</th>
                            <th>Trạng thái</th>
                            <th>Mã chuyển khoản</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            @php
                                $appointment = $payment->appointment;
                                $statusBadge = match($payment->status) {
                                    'pending' => 'warning text-dark',
                                    'awaiting_confirmation' => 'info text-dark',
                                    'paid' => 'success',
                                    'failed' => 'secondary',
                                    default => 'secondary',
                                };
                                $statusLabel = match($payment->status) {
                                    'pending' => 'Đã tạo QR',
                                    'awaiting_confirmation' => 'Chờ admin xác nhận',
                                    'paid' => 'Đã xác nhận',
                                    'failed' => 'Thất bại / từ chối',
                                    default => $payment->status,
                                };
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold">{{ $payment->booking_reference ?? 'N/A' }}</div>
                                    <small class="text-muted">
                                        {{ $appointment?->appointment_date?->format('d/m/Y') ?? 'N/A' }}
                                        · {{ $appointment?->appointment_time ?? 'N/A' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $payment->user?->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $payment->user?->email ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $appointment?->barber?->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $appointment?->service?->name ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold text-success">{{ number_format((float) $payment->amount, 0, ',', '.') }}đ</div>
                                    <small class="text-muted">Tạo lúc {{ $payment->created_at?->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusBadge }}">{{ $statusLabel }}</span>
                                    @if($payment->paid_at)
                                        <div class="small text-muted mt-1">Xác nhận lúc {{ $payment->paid_at->format('d/m/Y H:i') }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div><code>{{ $payment->gateway_txn_ref }}</code></div>
                                    <small class="text-muted">
                                        {{ $payment->gateway_transaction_no ? 'Mã admin xác nhận: '.$payment->gateway_transaction_no : 'Chưa có mã xác nhận' }}
                                    </small>
                                </td>
                                <td class="pe-3">
                                    @if($payment->status !== 'paid')
                                        <form method="POST" action="{{ route('admin.payments.confirmDeposit', $payment) }}" class="mb-2">
                                            @csrf
                                            @method('PATCH')
                                            <input
                                                type="text"
                                                name="transaction_reference"
                                                class="form-control form-control-sm mb-2"
                                                placeholder="Mã đối soát ngân hàng (nếu có)"
                                            >
                                            <button type="submit" class="btn btn-sm btn-success w-100">
                                                <i class="bi bi-check2-circle"></i> Xác nhận đã nhận cọc
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.payments.rejectDeposit', $payment) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input
                                                type="text"
                                                name="reason"
                                                class="form-control form-control-sm mb-2"
                                                placeholder="Lý do từ chối (nếu có)"
                                            >
                                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                                <i class="bi bi-x-circle"></i> Từ chối / hoàn về chưa cọc
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">Khoản cọc đã hoàn tất</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="px-3 py-3 border-top bg-white">
                    {{ $payments->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-qr-code display-4 text-muted"></i>
                <p class="text-muted mt-2 mb-0">Chưa có khoản đặt cọc nào phù hợp với bộ lọc hiện tại.</p>
            </div>
        @endif
    </div>
</div>
@endsection
