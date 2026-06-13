@extends('layouts.app')

@section('title', 'Kết quả thanh toán')
@section('page-title', 'Kết quả giao dịch VNPAY')

@section('content')
@php
    $statusLabel = match($currentStatus ?? null) {
        'paid' => 'Đã thanh toán',
        'failed' => 'Thất bại',
        'refunded' => 'Đã hoàn tiền',
        default => 'Đang chờ xử lý',
    };
    $targetLabel = ($targetType ?? 'invoice') === 'deposit' ? 'Khoản đặt cọc' : 'Hóa đơn';
    $targetSubtitle = ($targetType ?? 'invoice') === 'deposit'
        ? 'Lượt hẹn '.$targetReference
        : 'Lịch hẹn #'.($invoice?->appointment_id ?? 'N/A');
@endphp
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="display-4 {{ $success ? 'text-success' : 'text-danger' }} mb-3">
                        <i class="bi {{ $success ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                    </div>
                    <h3 class="fw-bold mb-2">
                        {{ $success ? 'Giao dịch đã được xác thực' : 'Giao dịch chưa thành công' }}
                    </h3>
                    <p class="text-muted mb-0">{{ $message }}</p>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100 bg-light">
                            <div class="small text-muted mb-1">{{ $targetLabel }}</div>
                            <div class="fw-bold fs-5">{{ $targetReference ?? 'N/A' }}</div>
                            <div class="text-muted small mt-2">{{ $targetSubtitle }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100 bg-light">
                            <div class="small text-muted mb-1">Trạng thái hiện tại</div>
                            <div class="fw-bold fs-5">{{ $statusLabel }}</div>
                            <div class="text-muted small mt-2">
                                Mã giao dịch: {{ $transactionNo ?: ($payment?->gateway_transaction_no ?? $invoice?->transaction_id ?? 'Đang chờ xử lý') }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="small text-muted mb-1">Mã phản hồi VNPAY</div>
                            <div class="fw-bold">{{ $responseCode ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="small text-muted mb-1">Trạng thái giao dịch</div>
                            <div class="fw-bold">{{ $transactionStatus ?: 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <div class="alert {{ $success ? 'alert-success' : 'alert-warning' }} mt-4 mb-0">
                    @if($success)
                        Giao dịch đã được đồng bộ vào hệ thống. Nếu bạn vừa thanh toán đặt cọc, lịch hẹn sẽ tự chuyển sang trạng thái xác nhận.
                    @else
                        Giao dịch chưa hoàn tất hoặc bị từ chối. Bạn có thể thử lại từ màn hình đặt cọc hoặc trang hóa đơn.
                    @endif
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    @auth
                        @if(($targetType ?? 'invoice') === 'deposit')
                            <a href="{{ route('customer.appointments.index') }}" class="btn btn-primary">
                                <i class="bi bi-calendar-check"></i> Về My Schedule
                            </a>
                        @elseif(auth()->user()->isAdmin())
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-primary">
                                <i class="bi bi-receipt"></i> Về danh sách hóa đơn
                            </a>
                        @endif
                    @endauth
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-house"></i> Về trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
