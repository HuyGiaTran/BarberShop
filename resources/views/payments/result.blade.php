@extends('layouts.app')

@section('title', 'Kết quả thanh toán')
@section('page-title', 'Kết quả giao dịch VNPAY')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    @if($success)
                        <div class="display-4 text-success mb-3">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    @else
                        <div class="display-4 text-danger mb-3">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                    @endif

                    <h3 class="fw-bold mb-2">
                        {{ $success ? 'Giao dịch đã được xác thực' : 'Giao dịch chưa thành công' }}
                    </h3>
                    <p class="text-muted mb-0">{{ $message }}</p>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100 bg-light">
                            <div class="small text-muted mb-1">Hóa đơn</div>
                            <div class="fw-bold fs-5">#{{ $invoice?->id ?? 'N/A' }}</div>
                            <div class="text-muted small mt-2">
                                Lịch hẹn #{{ $invoice?->appointment_id ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100 bg-light">
                            <div class="small text-muted mb-1">Trạng thái hiện tại</div>
                            <div class="fw-bold fs-5">
                                @switch($invoice?->payment_status)
                                    @case('paid')
                                        Đã thanh toán
                                        @break
                                    @case('refunded')
                                        Đã hoàn tiền
                                        @break
                                    @default
                                        Chưa thanh toán
                                @endswitch
                            </div>
                            <div class="text-muted small mt-2">
                                Mã giao dịch: {{ $transactionNo ?: 'Đang chờ IPN hoặc chưa có' }}
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
                        Hệ thống đã xác thực callback từ VNPAY. Nếu trạng thái hóa đơn chưa đổi ngay, IPN sẽ đồng bộ thanh toán trong bước tiếp theo.
                    @else
                        Giao dịch chưa hoàn tất hoặc bị từ chối. Bạn có thể tạo lại link thanh toán từ trang hóa đơn nếu cần.
                    @endif
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    @auth
                        @if(auth()->user()->isAdmin())
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
