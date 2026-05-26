@extends('layouts.app')

@section('title', 'Dashboard - BarberShop')
@section('page-title', 'Tổng quan')

@section('content')
<div class="container-fluid">
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card bg-stat1 d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-number">{{ $totalAppointments ?? 0 }}</div>
                    <div>Tổng lịch hẹn</div>
                </div>
                <i class="bi bi-calendar-check"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-stat2 d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-number">{{ $pendingAppointments ?? 0 }}</div>
                    <div>Đang chờ</div>
                </div>
                <i class="bi bi-hourglass-split"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-stat3 d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-number">{{ $totalBarbers ?? 0 }}</div>
                    <div>Barber</div>
                </div>
                <i class="bi bi-people"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-stat4 d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-number">{{ $totalServices ?? 0 }}</div>
                    <div>Dịch vụ</div>
                </div>
                <i class="bi bi-tag"></i>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-lightning me-2"></i>Thao tác nhanh
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ route('appointments.create') }}" class="btn btn-barber">
                            <i class="bi bi-plus-circle me-2"></i>Tạo lịch hẹn mới
                        </a>
                        <a href="{{ route('barbers.create') }}" class="btn btn-outline-primary">
                            <i class="bi bi-person-plus me-2"></i>Thêm Barber
                        </a>
                        <a href="{{ route('services.create') }}" class="btn btn-outline-success">
                            <i class="bi bi-plus-tag me-2"></i>Thêm dịch vụ
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-history me-2"></i>Lịch hẹn gần đây
                </div>
                <div class="card-body">
                    @if(isset($recentAppointments) && count($recentAppointments) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Khách hàng</th>
                                        <th>Barber</th>
                                        <th>Dịch vụ</th>
                                        <th>Ngày giờ</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentAppointments as $appointment)
                                    <tr>
                                        <td>{{ $appointment->user->name ?? 'N/A' }}</td>
                                        <td>{{ $appointment->barber->name ?? 'N/A' }}</td>
                                        <td>{{ $appointment->service->name ?? 'N/A' }}</td>
                                        
                                        <td>
                                            {{ $appointment->appointment_date ? $appointment->appointment_date->format('d-m-Y') : 'N/A' }} 
                                            {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}
                                        </td>

                                        <td>
                                            @switch($appointment->status)
                                                @case('pending')
                                                    <span class="badge bg-warning text-dark">Chờ xác nhận</span>
                                                    @break
                                                @case('confirmed')
                                                    <span class="badge bg-primary">Đã xác nhận</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge bg-success">Hoàn thành</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">Đã hủy</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $appointment->status }}</span>
                                            @endswitch
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="mt-2 text-muted">Chưa có lịch hẹn nào.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection