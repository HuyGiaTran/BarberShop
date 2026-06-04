@extends('layouts.app')
@section('title', 'Chi tiết lịch hẹn')
@section('page-title', 'Chi tiết lịch hẹn')
@section('content')
<div class="card">
    <div class="card-header">Chi tiết lịch hẹn</div>
    <div class="card-body">
        @if(isset($appointment))
            <p><strong>ID:</strong> {{ $appointment->id }}</p>
            <p><strong>Khách hàng:</strong> {{ $appointment->user->name ?? 'N/A' }}</p>
            <p><strong>Barber:</strong> {{ $appointment->barber->name ?? 'N/A' }}</p>
            <p><strong>Dịch vụ:</strong> {{ $appointment->service->name ?? 'N/A' }}</p>
            <p><strong>Ngày:</strong> {{ $appointment->appointment_date }}</p>
            <p><strong>Giờ:</strong> {{ $appointment->appointment_time }}</p>
            <p><strong>Trạng thái:</strong> {{ $appointment->status }}</p>
        @else
            <p class="text-muted">Không tìm thấy lịch hẹn.</p>
        @endif
        <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">Quay lại</a>
    </div>
</div>
@endsection