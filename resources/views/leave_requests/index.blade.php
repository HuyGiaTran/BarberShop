@extends('layouts.app')
@section('title', 'Nghỉ phép')
@section('page-title', 'Quản lý Đơn xin Nghỉ phép')
@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-light fw-bold text-secondary"><i class="bi bi-funnel"></i> Lọc đơn xin nghỉ</div>
    <div class="card-body">
            <form action="{{ route('admin.leave_requests.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="status" class="form-label small fw-bold">Trạng thái</label>
                <select class="form-select" id="status" name="status">
                    <option value="">-- Tất cả --</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Lọc kết quả</button>
                <a href="{{ route('admin.leave_requests.index') }}" class="btn btn-outline-secondary w-100">Xóa bộ lọc</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-envelope-paper"></i> Danh sách đơn nghỉ phép</h5>
    </div>
    <div class="card-body p-0">
        @if(isset($leaveRequests) && count($leaveRequests) > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">ID</th>
                            <th>Barber</th>
                            <th>Thời gian nghỉ</th>
                            <th>Lý do</th>
                            <th>Trạng thái</th>
                            <th class="pe-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaveRequests as $req)
                        <tr>
                            <td class="ps-3 fw-bold">#{{ $req->id }}</td>
                            <td><span class="badge bg-light text-dark border"><i class="bi bi-person-badge"></i> {{ $req->barber->name ?? 'N/A' }}</span></td>
                            <td>
                                <div><span class="small fw-bold">Từ:</span> {{ $req->start_date ? $req->start_date->format('d/m/Y') : ($req->start_time ? $req->start_time->format('d/m/Y') : 'N/A') }}</div>
                                <div><span class="small fw-bold">Đến:</span> {{ $req->end_date ? $req->end_date->format('d/m/Y') : ($req->end_time ? $req->end_time->format('d/m/Y') : 'N/A') }}</div>
                            </td>
                            <td>
                                <span class="d-inline-block text-truncate text-wrap" style="max-width: 250px;">
                                    {{ $req->reason ?? '-' }}
                                </span>
                            </td>
                            <td>
                                @switch($req->status)
                                    @case('pending') <span class="badge bg-warning text-dark px-2 py-1">Chờ duyệt</span> @break
                                    @case('approved') <span class="badge bg-success px-2 py-1">Đã duyệt</span> @break
                                    @case('rejected') <span class="badge bg-danger px-2 py-1">Từ chối</span> @break
                                @endswitch
                            </td>
                            <td class="pe-3">
                                @if($req->status == 'pending')
                                    <form action="{{ route('admin.leave_requests.approve', $req->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">Duyệt</button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}">Từ chối</button>
                                    
                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.leave_requests.reject', $req->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="status" value="rejected">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Từ chối đơn xin nghỉ phép</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label text-wrap">Vui lòng nhập lý do từ chối (bắt buộc):</label>
                                                            <textarea name="rejection_reason" class="form-control text-wrap" rows="3" required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                        <button type="submit" class="btn btn-danger">Xác nhận Từ chối</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($req->status == 'rejected')
                                    <span class="small text-muted d-block text-wrap" style="max-width: 150px;">Lý do TT: {{ $req->rejection_reason }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($leaveRequests->hasPages())
                <div class="px-3 py-3 border-top bg-white">
                    {{ $leaveRequests->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-envelope-x display-4 text-muted"></i>
                <p class="text-muted mt-2 mb-0">Không có đơn xin nghỉ phép nào.</p>
            </div>
        @endif
    </div>
</div>
@endsection
