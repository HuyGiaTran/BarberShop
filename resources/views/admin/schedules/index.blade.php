@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý Lịch Làm Việc Của Thợ</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách Thợ & Lịch trình</h6>
        </div>
        <div class="card-body">
            <div class="accordion" id="accordionSchedules">
                @foreach($barbers as $index => $barber)
                    <div class="accordion-item mb-3 border">
                        <h2 class="accordion-header" id="heading{{ $barber->id }}">
                            <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }} bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $barber->id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $barber->id }}">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $barber->avatar ? Storage::url($barber->avatar) : asset('assets/img/default-avatar.png') }}" class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                    <h6 class="mb-0 fw-bold">{{ $barber->user->name }}</h6>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse{{ $barber->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $barber->id }}" data-bs-parent="#accordionSchedules">
                            <div class="accordion-body">
                                <form action="{{ route('admin.schedules.update', $barber->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Thứ</th>
                                                    <th>Trạng Thái</th>
                                                    <th>Giờ Bắt Đầu</th>
                                                    <th>Giờ Kết Thúc</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $days = [
                                                        1 => 'Thứ Hai',
                                                        2 => 'Thứ Ba',
                                                        3 => 'Thứ Tư',
                                                        4 => 'Thứ Năm',
                                                        5 => 'Thứ Sáu',
                                                        6 => 'Thứ Bảy',
                                                        0 => 'Chủ Nhật'
                                                    ];
                                                @endphp
                                                @foreach($days as $dayValue => $dayName)
                                                    @php
                                                        $schedule = $barber->schedules->firstWhere('day_of_week', $dayValue);
                                                        $isOff = $schedule ? $schedule->is_off : false;
                                                        $startTime = $schedule ? Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '08:00';
                                                        $endTime = $schedule ? Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '19:30';
                                                    @endphp
                                                    <tr>
                                                        <td class="align-middle fw-bold">{{ $dayName }}</td>
                                                        <td class="align-middle">
                                                            <input type="hidden" name="schedules[{{ $dayValue }}][day_of_week]" value="{{ $dayValue }}">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input off-switch" type="checkbox" role="switch" name="schedules[{{ $dayValue }}][is_off]" value="1" id="off_{{ $barber->id }}_{{ $dayValue }}" {{ $isOff ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="off_{{ $barber->id }}_{{ $dayValue }}">Nghỉ Làm</label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="time" class="form-control time-input" name="schedules[{{ $dayValue }}][start_time]" value="{{ $startTime }}" {{ $isOff ? 'disabled' : '' }} required>
                                                        </td>
                                                        <td>
                                                            <input type="time" class="form-control time-input" name="schedules[{{ $dayValue }}][end_time]" value="{{ $endTime }}" {{ $isOff ? 'disabled' : '' }} required>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Lưu Lịch Trình</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const switches = document.querySelectorAll('.off-switch');
        switches.forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                const tr = this.closest('tr');
                const timeInputs = tr.querySelectorAll('.time-input');
                timeInputs.forEach(input => {
                    input.disabled = this.checked;
                });
            });
        });
    });
</script>
@endpush
@endsection
