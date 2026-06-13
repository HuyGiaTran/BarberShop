@extends('layouts.app')
@section('title', 'Tính Lương')
@section('page-title', 'Bảng tính lương tháng')
@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-light fw-bold text-secondary d-flex justify-content-between align-items-center">
        <span><i class="bi bi-cash-coin"></i> Chốt lương tháng</span>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.payrolls.calculate') }}" method="POST" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-4">
                <label for="month" class="form-label small fw-bold">Chọn tháng (YYYY-MM)</label>
                <input type="month" class="form-control" id="month" name="month" value="{{ request('month') ?? date('Y-m') }}" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Tiến hành tính toán lương cho tất cả Barber trong tháng này?')">
                    <i class="bi bi-calculator"></i> Bắt đầu tính lương
                </button>
            </div>
            <div class="col-md-4">
                <a href="{{ route('admin.payrolls.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-clockwise"></i> Tải lại danh sách
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-list-columns-reverse"></i> Danh sách Bảng lương đã chốt</h5>
    </div>
    <div class="card-body p-0">
        @if(isset($payrolls) && count($payrolls) > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">ID</th>
                            <th>Tháng</th>
                            <th>Barber</th>
                            <th>Lương cứng</th>
                            <th>Số ca làm</th>
                            <th>Hoa hồng (30%)</th>
                            <th>Tổng nhận</th>
                            <th>Trạng thái</th>
                            <th class="pe-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payrolls as $payroll)
                        <tr>
                            <td class="ps-3 fw-bold">#{{ $payroll->id }}</td>
                            <td><span class="badge bg-secondary">{{ $payroll->month }}</span></td>
                            <td><span class="fw-bold"><i class="bi bi-person-badge"></i> {{ $payroll->barber->name ?? 'N/A' }}</span></td>
                            <td class="text-success">{{ number_format($payroll->base_salary, 0, ',', '.') }}đ</td>
                            <td><span class="badge bg-info text-dark">{{ $payroll->total_appointments }} ca</span></td>
                            <td class="text-success">{{ number_format($payroll->commission, 0, ',', '.') }}đ</td>
                            <td class="text-danger fw-bold fs-6">{{ number_format($payroll->total_amount, 0, ',', '.') }}đ</td>
                            <td>
                                @if($payroll->status == 'paid')
                                    <span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle"></i> Đã thanh toán</span>
                                @else
                                    <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-clock"></i> Chờ thanh toán</span>
                                @endif
                            </td>
                            <td class="pe-3">
                                @if($payroll->status == 'pending')
                                    <form action="{{ route('admin.payrolls.markPaid', $payroll->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Đánh dấu là đã thanh toán cho nhân viên này?')"><i class="bi bi-check-all"></i> Thanh toán</button>
                                    </form>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($payrolls->hasPages())
                <div class="px-3 py-3 border-top bg-white">
                    {{ $payrolls->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-cash-coin display-4 text-muted"></i>
                <p class="text-muted mt-2 mb-0">Chưa có bảng lương nào được chốt trong tháng này.</p>
            </div>
        @endif
    </div>
</div>
@endsection
