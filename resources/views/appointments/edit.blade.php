@extends('layouts.app')
@section('title', 'Sửa lịch hẹn')
@section('page-title', 'Chỉnh sửa lịch hẹn')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning text-dark fw-bold py-3"><i class="bi bi-pencil-square"></i> Cập nhật thông tin lịch hẹn #{{ $appointment->id }}</div>
            <div class="card-body p-4">
                
                @if ($errors->any())
                    <div class="alert alert-danger mb-3">
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.appointments.update', $appointment->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="user_id" class="form-label fw-bold small text-muted">Tài khoản khách hàng đặt lịch</label>
                        <select class="form-select bg-light" id="user_id" name="user_id" required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $appointment->user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="barber_id" class="form-label fw-bold small">Thợ phục vụ (Barber) <span class="text-danger">*</span></label>
                            <select class="form-select @error('barber_id') is-invalid @enderror" id="barber_id" name="barber_id" required>
                                @foreach($barbers as $barber)
                                    <option value="{{ $barber->id }}" {{ old('barber_id', $appointment->barber_id) == $barber->id ? 'selected' : '' }}>
                                        {{ $barber->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('barber_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="service_id" class="form-label fw-bold small">Dịch vụ điều trị <span class="text-danger">*</span></label>
                            <select class="form-select @error('service_id') is-invalid @enderror" id="service_id" name="service_id" required>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" {{ old('service_id', $appointment->service_id) == $service->id ? 'selected' : '' }}>
                                        {{ $service->name }} ({{ number_format($service->price, 0, ',', '.') }}đ)
                                    </option>
                                @endforeach
                            </select>
                            @error('service_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="appointment_date" class="form-label fw-bold small">Ngày hẹn cắt <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('appointment_date') is-invalid @enderror" id="appointment_date" name="appointment_date" value="{{ old('appointment_date', $appointment->appointment_date ? $appointment->appointment_date->format('Y-m-d') : '') }}" required>
                            @error('appointment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="appointment_time" class="form-label fw-bold small">Khung giờ hẹn <span class="text-danger">*</span></label>
                            <select class="form-select @error('appointment_time') is-invalid @enderror" id="appointment_time" name="appointment_time" required>
                                @php
                                    $slots = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00', '19:30'];
                                @endphp
                                @foreach($slots as $slot)
                                    <option value="{{ $slot }}" {{ old('appointment_time', $appointment->appointment_time) == $slot ? 'selected' : '' }}>{{ $slot }}</option>
                                @endforeach
                            </select>
                            @error('appointment_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label fw-bold small">Trạng thái lịch hẹn <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="pending" {{ old('status', $appointment->status) == 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                                <option value="confirmed" {{ old('status', $appointment->status) == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                                <option value="completed" {{ old('status', $appointment->status) == 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                                <option value="cancelled" {{ old('status', $appointment->status) == 'cancelled' ? 'selected' : '' }}>Đã hủy lịch</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label fw-bold small">Ghi chú lịch sử</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $appointment->notes) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary px-4">Hủy thay đổi</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save2"></i> Lưu chỉnh sửa</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection