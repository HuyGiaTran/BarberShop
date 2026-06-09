@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-file-earmark-text me-2"></i>Chi Tiết Đơn Xin Nghỉ Phép
        </h2>
        <a href="{{ route('admin.leave_requests.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Thông tin đơn -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Thông tin đơn xin</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Người xin:</strong>
                            <p>{{ $leaveRequest->barber->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Email:</strong>
                            <p>{{ $leaveRequest->barber->user->email }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Người nhận đơn:</strong>
                            <p>{{ $leaveRequest->recipient }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Ngày xin:</strong>
                            <p>{{ $leaveRequest->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Thời gian bắt đầu:</strong>
                            <p class="text-danger">{{ $leaveRequest->start_time->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Thời gian kết thúc:</strong>
                            <p class="text-danger">{{ $leaveRequest->end_time->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <strong>Lý do:</strong>
                            <p style="white-space: pre-wrap;">{{ $leaveRequest->reason }}</p>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Người bàn giao công việc:</strong>
                            <p>{{ $leaveRequest->handover_person }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Cam kết:</strong>
                            <p>
                                @if($leaveRequest->commitment)
                                    <span class="badge bg-success">Có</span>
                                @else
                                    <span class="badge bg-danger">Không</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin người làm đơn -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Thông tin người làm đơn</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Họ và tên:</strong>
                            <p>{{ $leaveRequest->applicant_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Ngày sinh:</strong>
                            <p>{{ $leaveRequest->applicant_dob->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <strong>Địa chỉ:</strong>
                            <p>{{ $leaveRequest->applicant_address }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Số điện thoại:</strong>
                            <p>{{ $leaveRequest->applicant_phone }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Địa điểm công tác:</strong>
                            <p>{{ $leaveRequest->applicant_workplace }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Chức vụ:</strong>
                            <p>{{ $leaveRequest->applicant_position }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Status & Actions -->
            <div class="card mb-3 sticky-top" style="top: 20px;">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Trạng thái</h5>
                </div>
                <div class="card-body">
                    @switch($leaveRequest->status)
                        @case('pending')
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-hourglass-split"></i> Đang chờ xét duyệt
                            </div>

                            <form action="{{ route('admin.leave_requests.approve', $leaveRequest) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-circle"></i> Duyệt đơn
                                </button>
                            </form>

                            <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="bi bi-x-circle"></i> Từ chối
                            </button>
                            @break

                        @case('approved')
                            <div class="alert alert-success mb-0">
                                <i class="bi bi-check-circle-fill"></i> Đã duyệt
                            </div>
                            @break

                        @case('rejected')
                            <div class="alert alert-danger mb-3">
                                <i class="bi bi-x-circle-fill"></i> Đã từ chối
                            </div>
                            @if($leaveRequest->rejection_reason)
                                <div class="alert alert-light" role="alert">
                                    <strong>Lý do:</strong>
                                    <p class="mb-0" style="white-space: pre-wrap;">{{ $leaveRequest->rejection_reason }}</p>
                                </div>
                            @endif
                            @break
                    @endswitch
                </div>
            </div>

            <!-- Conflicting Appointments -->
            @if($conflictingAppointments->count() > 0)
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-x"></i> Lịch hẹn trùng lịch
                            <span class="badge bg-danger">{{ $conflictingAppointments->count() }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @foreach($conflictingAppointments as $apt)
                                <div class="list-group-item">
                                    <small>
                                        <strong>{{ $apt->user->name }}</strong><br>
                                        {{ $apt->appointment_date->format('d/m/Y') }} lúc {{ $apt->appointment_time }}<br>
                                        Dịch vụ: {{ $apt->service->name ?? 'N/A' }}<br>
                                        <span class="badge bg-warning">{{ $apt->status }}</span>
                                    </small>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-muted small mt-3 mb-0">
                            * Nếu duyệt, những lịch này sẽ được chuyển sang <strong>{{ $leaveRequest->handover_person }}</strong>
                        </p>
                    </div>
                </div>
            @else
                <div class="card text-center">
                    <div class="card-body">
                        <i class="bi bi-calendar-check" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2 small">Không có lịch hẹn trùng lịch</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Từ chối đơn xin nghỉ phép</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.leave_requests.reject', $leaveRequest) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason" class="form-label">Lý do từ chối (không bắt buộc)</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" placeholder="Nhập lý do từ chối (nếu có)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
