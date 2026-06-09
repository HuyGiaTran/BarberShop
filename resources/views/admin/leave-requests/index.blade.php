@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-file-earmark-text me-2"></i>Quản Lý Đơn Xin Nghỉ Phép
        </h2>
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

    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Danh sách đơn xin nghỉ phép</h5>
        </div>
        <div class="card-body">
            @if ($leaveRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Barber</th>
                                <th>Thời gian nghỉ</th>
                                <th>Lý do</th>
                                <th>Người bàn giao</th>
                                <th>Ngày xin</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leaveRequests as $request)
                                <tr>
                                    <td>
                                        <strong>{{ $request->barber->name ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $request->barber->user->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        <small>
                                            {{ $request->start_time->format('d/m/Y H:i') }}<br>
                                            đến<br>
                                            {{ $request->end_time->format('d/m/Y H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($request->reason, 50) }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $request->handover_person }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $request->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        @switch($request->status)
                                            @case('pending')
                                                <span class="badge bg-warning">Chờ duyệt</span>
                                                @break
                                            @case('approved')
                                                <span class="badge bg-success">Đã duyệt</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge bg-danger">Từ chối</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.leave_requests.show', $request) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> Chi tiết
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($leaveRequests->hasPages())
                    <div class="mt-3">
                        {{ $leaveRequests->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">Chưa có đơn xin nghỉ phép nào</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
