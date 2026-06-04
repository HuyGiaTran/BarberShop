@extends('layouts.app')
@section('title', 'Đặt lịch hẹn')
@section('page-title', 'Tạo lịch hẹn mới')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white fw-bold py-3"><i class="bi bi-calendar-plus"></i> Nhập thông tin lịch hẹn mới</div>
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

                <form action="{{ route('admin.appointments.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="user_id" class="form-label fw-bold small">Khách hàng đặt lịch <span class="text-danger">*</span></label>
                        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                            <option value="">-- Click để chọn tài khoản khách hàng --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} (Email: {{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="barber_id" class="form-label fw-bold small">Thợ phục vụ (Barber) <span class="text-danger">*</span></label>
                            <select class="form-select @error('barber_id') is-invalid @enderror" id="barber_id" name="barber_id" required>
                                <option value="">-- Chọn Stylist --</option>
                                @foreach($barbers as $barber)
                                    <option value="{{ $barber->id }}" {{ old('barber_id') == $barber->id ? 'selected' : '' }}>
                                        {{ $barber->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('barber_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="service_id" class="form-label fw-bold small">Gói dịch vụ <span class="text-danger">*</span></label>
                            <select class="form-select @error('service_id') is-invalid @enderror" id="service_id" name="service_id" required>
                                <option value="">-- Chọn dịch vụ tiệm --</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                        {{ $service->name }} ({{ number_format($service->price, 0, ',', '.') }}đ — {{ $service->duration_minutes }} phút)
                                    </option>
                                @endforeach
                            </select>
                            @error('service_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="appointment_date" class="form-label fw-bold small">Ngày hẹn cắt <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('appointment_date') is-invalid @enderror" id="appointment_date" name="appointment_date" value="{{ old('appointment_date', date('Y-m-d')) }}" required>
                            @error('appointment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="appointment_time" class="form-label fw-bold small">Khung giờ hẹn <span class="text-danger">*</span></label>
                            <select class="form-select @error('appointment_time') is-invalid @enderror" id="appointment_time" name="appointment_time" required>
                                <option value="">-- Chọn mốc giờ --</option>
                                @php
                                    $slots = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00', '19:30'];
                                @endphp
                                @foreach($slots as $slot)
                                    <option value="{{ $slot }}" {{ old('appointment_time') == $slot ? 'selected' : '' }}>{{ $slot }}</option>
                                @endforeach
                            </select>
                            @error('appointment_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label fw-bold small">Trạng thái đặt chỗ ban đầu</label>
                        <select class="form-select" id="status" name="status">
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt (Khách đang đăng ký)</option>
                            <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>Duyệt luôn (Xác nhận giữ chỗ)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label fw-bold small">Ghi chú / Yêu cầu riêng</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Ví dụ: Chỉ thợ này cắt mái, cạo râu nhẹ tay, yêu cầu sát khuẩn ghế cắt...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary px-4">Quay lại danh sách</a>
                        <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-circle"></i> Đăng ký lịch hẹn</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection