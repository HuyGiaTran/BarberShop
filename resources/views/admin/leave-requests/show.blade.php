@extends('layouts.app')

@section('title', 'Chi tiết đơn nghỉ phép')
@section('page-title', 'Chi tiết đơn nghỉ phép')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-text me-2"></i>Đơn xin nghỉ phép</span>
                @if($leaveRequest->status === 'pending')
                <div>
                    <form method="POST" action="{{ route('admin.leave_requests.approve', $leaveRequest) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i> Duyệt</button>
                    </form>
                    <button class="btn btn-danger btn-sm" onclick="showRejectModal()"><i class="bi bi-x-circle"></i> Từ chối</button>
                </div>
                @endif
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width:200px;">Barber</th>
                        <td>{{ $leaveRequest->barber->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Người bàn giao</th>
                        <td>{{ $leaveRequest->handover_person ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Thời gian</th>
                        <td>
                            @if($leaveRequest->start_time)
                                {{ $leaveRequest->start_time->format('d/m/Y H:i') }} → {{ $leaveRequest->end_time->format('d/m/Y H:i') }}
                            @else
                                {{ $leaveRequest->start_date->format('d/m/Y') }} → {{ $leaveRequest->end_date->format('d/m/Y') }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Lý do</th>
                        <td>{{ $leaveRequest->reason }}</td>
                    </tr>
                    <tr>
                        <th>Trạng thái</th>
                        <td>
                            @if($leaveRequest->status === 'pending') <span class="badge bg-warning">Chờ duyệt</span>
                            @elseif($leaveRequest->status === 'approved') <span class="badge bg-success">Đã duyệt</span>
                            @else <span class="badge bg-danger">Từ chối</span>
                            @endif
                        </td>
                    </tr>
                    @if($leaveRequest->rejection_reason)
                    <tr>
                        <th>Lý do từ chối</th>
                        <td class="text-danger">{{ $leaveRequest->rejection_reason }}</td>
                    </tr>
                    @endif
                </table>

                @if(isset($conflictingAppointments) && $conflictingAppointments->count() > 0)
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    Có <strong>{{ $conflictingAppointments->count() }}</strong> lịch hẹn trùng với thời gian nghỉ.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($leaveRequest->status === 'pending')
<!-- Modal từ chối -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.leave_requests.reject', $leaveRequest) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Từ chối đơn nghỉ phép</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Lý do từ chối</label>
                        <textarea name="rejection_reason" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
function showRejectModal() {
    var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}
</script>
@endif
@endsection