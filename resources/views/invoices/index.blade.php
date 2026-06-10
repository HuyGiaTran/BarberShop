@extends('layouts.app')

@section('title', 'Hóa đơn')
@section('page-title', 'Quản lý Hóa đơn')

@section('content')
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-light fw-bold text-secondary">
        <i class="bi bi-funnel"></i> Bộ lọc hóa đơn
    </div>
    <div class="card-body">
        <form action="{{ route('admin.invoices.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="q" class="form-label small fw-bold">Từ khóa</label>
                <input
                    type="text"
                    class="form-control"
                    id="q"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Mã hóa đơn, khách hàng, barber, dịch vụ..."
                >
            </div>
            <div class="col-md-3">
                <label for="payment_method" class="form-label small fw-bold">Phương thức</label>
                <select class="form-select" id="payment_method" name="payment_method">
                    <option value="">-- Tất cả phương thức --</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Tiền mặt</option>
                    <option value="vnpay" {{ request('payment_method') === 'vnpay' ? 'selected' : '' }}>VNPAY</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="payment_status" class="form-label small fw-bold">Trạng thái thanh toán</label>
                <select class="form-select" id="payment_status" name="payment_status">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                    <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Đã hoàn tiền</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Lọc
                </button>
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary w-100">Xóa</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold text-dark">
            <i class="bi bi-receipt-cutoff"></i> Danh sách hóa đơn
        </h5>
        <span class="badge bg-light text-dark border">{{ $invoices->total() }} hóa đơn</span>
    </div>
    <div class="card-body p-0">
        @if($invoices->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Mã HĐ</th>
                            <th>Khách hàng</th>
                            <th>Lịch hẹn</th>
                            <th>Dịch vụ</th>
                            <th>Số tiền</th>
                            <th>Thanh toán</th>
                            <th class="pe-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            @php
                                $appointment = $invoice->appointment;
                                $service = $appointment?->service;
                                $barber = $appointment?->barber;
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold">#{{ $invoice->id }}</div>
                                    <small class="text-muted">
                                        Lịch #{{ $invoice->appointment_id ?? 'N/A' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $invoice->user->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $invoice->user->email ?? '' }}</small>
                                </td>
                                <td>
                                    @if($appointment)
                                        <div class="fw-bold">
                                            {{ $appointment->appointment_date?->format('d/m/Y') ?? 'N/A' }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $appointment->appointment_time }} · {{ $barber?->name ?? 'N/A' }}
                                        </small>
                                    @else
                                        <span class="text-muted">Không còn lịch hẹn</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $service?->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $service?->duration_minutes ?? 0 }} phút</small>
                                </td>
                                <td>
                                    <div class="fw-bold text-success">
                                        {{ number_format((float) $invoice->total_amount, 0, ',', '.') }}đ
                                    </div>
                                    <small class="text-muted">{{ $invoice->created_at?->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    <div class="mb-1">
                                        @if($invoice->payment_method === 'vnpay')
                                            <span class="badge bg-info text-dark">VNPAY</span>
                                        @else
                                            <span class="badge bg-secondary">Tiền mặt</span>
                                        @endif
                                    </div>
                                    <div class="mb-1">
                                        @switch($invoice->payment_status)
                                            @case('paid')
                                                <span class="badge bg-success">Đã thanh toán</span>
                                                @break
                                            @case('refunded')
                                                <span class="badge bg-warning text-dark">Đã hoàn tiền</span>
                                                @break
                                            @default
                                                <span class="badge bg-danger">Chưa thanh toán</span>
                                        @endswitch
                                    </div>
                                    <small class="text-muted">
                                        {{ $invoice->transaction_id ? 'Mã GD: '.$invoice->transaction_id : 'Chưa có mã giao dịch' }}
                                    </small>
                                </td>
                                <td class="pe-3">
                                    <div class="d-flex flex-wrap gap-2">
                                        @if($invoice->payment_status !== 'paid')
                                            <form action="{{ route('admin.invoices.markCashPaid', $invoice) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-success"
                                                    onclick="return confirm('Xác nhận đã thu tiền mặt cho hóa đơn này?')"
                                                >
                                                    <i class="bi bi-cash-coin"></i> Đã thu tiền mặt
                                                </button>
                                            </form>

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary btn-create-vnpay"
                                                data-invoice-id="{{ $invoice->id }}"
                                            >
                                                <i class="bi bi-credit-card-2-front"></i> Link VNPAY
                                            </button>
                                        @else
                                            <span class="text-muted small">Đã hoàn tất thanh toán</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="px-3 py-3 border-top bg-white">
                    {{ $invoices->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-receipt display-4 text-muted"></i>
                <p class="text-muted mt-2 mb-0">Chưa có hóa đơn nào phù hợp với bộ lọc hiện tại.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-create-vnpay').forEach((button) => {
    button.addEventListener('click', async () => {
        const invoiceId = button.dataset.invoiceId;
        const originalHtml = button.innerHTML;

        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang tạo link...';

        try {
            const response = await fetch('/api/vnpay/create-payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ invoice_id: invoiceId }),
            });

            const result = await response.json();

            if (!response.ok || !result.success || !result.data?.payment_url) {
                throw new Error(result.message || 'Không tạo được link thanh toán.');
            }

            window.open(result.data.payment_url, '_blank', 'noopener');
        } catch (error) {
            alert(error.message || 'Có lỗi xảy ra khi tạo link VNPAY.');
        } finally {
            button.disabled = false;
            button.innerHTML = originalHtml;
        }
    });
});
</script>
@endpush
