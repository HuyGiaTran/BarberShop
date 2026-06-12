@extends('layouts.app')

@section('title', 'Lịch làm việc')
@section('page-title', 'Quản lý Lịch Làm Việc')

@section('content')
<div class="container-fluid px-0">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <!-- Điều hướng tuần -->
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <a href="{{ route('admin.schedules.index', ['week' => $weekOffset - 1]) }}" class="btn btn-outline-secondary">
                <i class="bi bi-chevron-left"></i> Tuần trước
            </a>
            <div class="text-center">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-week me-2"></i>
                    Tuần {{ $weekStart->format('d/m') }} - {{ $weekEnd->format('d/m/Y') }}
                </h5>
                @if($weekOffset == 0)
                    <span class="badge bg-primary mt-1">Tuần hiện tại</span>
                @endif
            </div>
            <a href="{{ route('admin.schedules.index', ['week' => $weekOffset + 1]) }}" class="btn btn-outline-secondary">
                Tuần sau <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>

    <!-- Danh sách Barber + Lịch -->
    @foreach($barbers as $barber)
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center gap-3 py-3">
            <div class="d-flex align-items-center justify-content-center rounded-circle" style="width:45px;height:45px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-weight:700;">
                {{ strtoupper(substr($barber->name, 0, 1)) }}
            </div>
            <div>
                <h6 class="mb-0 fw-bold">{{ $barber->name }}</h6>
                <small class="text-muted">
                    @if($barber->is_active) <span class="badge bg-success">Đang hoạt động</span>
                    @else <span class="badge bg-secondary">Tạm nghỉ</span>
                    @endif
                </small>
            </div>
            <div class="ms-auto">
                <button class="btn btn-sm btn-outline-primary" onclick="showScheduleForm({{ $barber->id }})">
                    <i class="bi bi-gear"></i> Cập nhật lịch
                </button>
            </div>
        </div>

        <!-- Bảng hiển thị lịch -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width:130px;">Thứ/Ngày</th>
                            <th>Ca sáng<br><small class="text-muted">08:00 - 13:00</small></th>
                            <th>Ca chiều<br><small class="text-muted">13:00 - 18:00</small></th>
                            <th>Ca tối<br><small class="text-muted">18:00 - 22:00</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($days as $day)
                            @php $slots = $schedulesByBarber[$barber->id][$day['day_of_week']] ?? []; @endphp
                            <tr class="text-center align-middle">
                                <td class="fw-bold">
                                    {{ $day['label'] }}<br>
                                    <small class="text-muted">{{ $day['date']->format('d/m') }}</small>
                                </td>
                                @foreach(['morning' => ['start' => '08:00', 'end' => '13:00'], 'afternoon' => ['start' => '13:00', 'end' => '18:00'], 'evening' => ['start' => '18:00', 'end' => '22:00']] as $sn => $snTimes)
                                <td>
                                    @php
                                        $blocked = false;
                                        $reason = '';
                                        foreach ($slots as $s) {
                                            if ($s['status'] === 'blocked') {
                                                // Kiểm tra overlap giữa slot và schedule range
                                                $sStart = (int)explode(':', $snTimes['start'])[0];
                                                $sEnd = (int)explode(':', $snTimes['end'])[0];
                                                $bStart = (int)explode(':', $s['start'])[0];
                                                $bEnd = (int)explode(':', $s['end'])[0];
                                                if ($sStart < $bEnd && $sEnd > $bStart) {
                                                    $blocked = true;
                                                    $reason = $s['reason'];
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp
                                    @if($blocked)
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2">
                                            <i class="bi bi-x-circle me-1"></i> {{ $reason }}
                                        </span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                                            <i class="bi bi-check-circle me-1"></i> Làm
                                        </span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Form cập nhật lịch (ẩn, hiện khi bấm nút) -->
        <div class="card-body border-top" id="scheduleForm{{ $barber->id }}" style="display:none;">
            <form action="{{ route('admin.schedules.update', $barber->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Thứ</th>
                                <th>Trạng thái</th>
                                <th>Giờ bắt đầu</th>
                                <th>Giờ kết thúc</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $dayNames = [1 => 'Thứ Hai', 2 => 'Thứ Ba', 3 => 'Thứ Tư', 4 => 'Thứ Năm', 5 => 'Thứ Sáu', 6 => 'Thứ Bảy', 0 => 'Chủ Nhật'];
                            @endphp
                            @foreach($dayNames as $dayVal => $dayName)
                                @php
                                    $schedule = $barber->schedules->firstWhere('day_of_week', $dayVal);
                                    $isOff = $schedule ? ($schedule->is_off || !$schedule->is_available) : false;
                                    $startTime = $schedule ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '08:00';
                                    $endTime = $schedule ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '18:00';
                                @endphp
                                <tr>
                                    <td class="align-middle fw-bold">{{ $dayName }}</td>
                                    <td class="align-middle">
                                        <input type="hidden" name="schedules[{{ $dayVal }}][day_of_week]" value="{{ $dayVal }}">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input off-switch" type="checkbox" role="switch" name="schedules[{{ $dayVal }}][is_off]" value="1" id="off_{{ $barber->id }}_{{ $dayVal }}" {{ $isOff ? 'checked' : '' }}>
                                            <label class="form-check-label" for="off_{{ $barber->id }}_{{ $dayVal }}">Nghỉ làm</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="time" class="form-control time-input form-control-sm" name="schedules[{{ $dayVal }}][start_time]" value="{{ $startTime }}" {{ $isOff ? 'disabled' : '' }} required>
                                    </td>
                                    <td>
                                        <input type="time" class="form-control time-input form-control-sm" name="schedules[{{ $dayVal }}][end_time]" value="{{ $endTime }}" {{ $isOff ? 'disabled' : '' }} required>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-outline-secondary me-2" onclick="hideScheduleForm({{ $barber->id }})">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Lưu lịch</button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
    function showScheduleForm(id) {
        document.getElementById('scheduleForm' + id).style.display = 'block';
    }
    function hideScheduleForm(id) {
        document.getElementById('scheduleForm' + id).style.display = 'none';
    }
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.off-switch').forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                const tr = this.closest('tr');
                const timeInputs = tr.querySelectorAll('.time-input');
                timeInputs.forEach(input => { input.disabled = this.checked; });
            });
        });
    });
</script>
@endpush
