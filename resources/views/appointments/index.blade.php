@extends('layouts.app')
@section('title', 'Lịch hẹn')
@section('page-title', 'Danh sách Lịch hẹn')
@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-light fw-bold text-secondary"><i class="bi bi-funnel"></i> Bộ lọc lịch hẹn</div>
    <div class="card-body">
        <form action="{{ route('admin.appointments.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="date" class="form-label small fw-bold">Ngày hẹn</label>
                <input type="date" class="form-control" id="date" name="date" value="{{ request('date') }}">
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label small fw-bold">Trạng thái</label>
                <select class="form-select" id="status" name="status">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Lọc kết quả</button>
                <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary w-100">Xóa bộ lọc</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-calendar3"></i> Sổ ghi lịch hẹn</h5>
        <a href="{{ route('admin.appointments.create') }}" class="btn btn-success btn-sm"><i class="bi bi-plus-circle"></i> Đặt lịch mới</a>
    </div>
    <div class="card-body p-0">
        @if(isset($appointments) && count($appointments) > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">ID</th>
                            <th>Khách hàng</th>
                            <th>Barber</th>
                            <th>Dịch vụ</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                            <th>Duyệt nhanh</th>
                            <th class="pe-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appointments as $apt)
                        <tr>
                            <td class="ps-3 fw-bold">#{{ $apt->id }}</td>
                            <td>
                                <div class="fw-bold">{{ $apt->user->name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $apt->user->email ?? '' }}</small>
                            </td>
                            <td><span class="badge bg-light text-dark border"><i class="bi bi-person-badge"></i> {{ $apt->barber->name ?? 'N/A' }}</span></td>
                            <td>
                                <div class="small fw-bold text-dark">{{ $apt->service->name ?? 'N/A' }}</div>
                                <small class="text-success">{{ number_format($apt->service->price ?? 0, 0, ',', '.') }}đ</small>
                            </td>
                            <td>
                                <div class="fw-bold"><i class="bi bi-calendar-event"></i> {{ $apt->appointment_date ? $apt->appointment_date->format('d/m/Y') : 'N/A' }}</div>
                                <span class="badge bg-dark mt-1"><i class="bi bi-clock"></i> {{ $apt->appointment_time }}</span>
                            </td>
                            <td>
                                @switch($apt->status)
                                    @case('pending') <span class="badge bg-warning text-dark px-2 py-1">Chờ duyệt</span> @break
                                    @case('confirmed') <span class="badge bg-primary px-2 py-1">Đã xác nhận</span> @break
                                    @case('completed') <span class="badge bg-success px-2 py-1">Hoàn thành</span> @break
                                    @case('cancelled') <span class="badge bg-danger px-2 py-1">Đã hủy</span> @break
                                    @default <span class="badge bg-secondary px-2 py-1">{{ $apt->status }}</span>
                                @endswitch
                            </td>
                            <td>
                                @if($apt->status == 'pending')
                                    <form action="{{ route('admin.appointments.updateStatus', $apt->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-1">Xác nhận</button>
                                    </form>
                                    <form action="{{ route('admin.appointments.updateStatus', $apt->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="return confirm('Hủy lịch này?')">Hủy</button>
                                    </form>
                                @elseif($apt->status == 'confirmed')
                                    <form action="{{ route('admin.appointments.updateStatus', $apt->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn btn-sm btn-outline-success py-0 px-1">Xong</button>
                                    </form>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="pe-3">
                                <div class="btn-group">
                                    <a href="{{ route('admin.appointments.edit', $apt->id) }}" class="btn btn-sm btn-light border text-warning" title="Chỉnh sửa"><i class="bi bi-pencil-square"></i></a>
                                    <form action="{{ route('admin.appointments.destroy', $apt->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Xóa vĩnh viễn lịch hẹn này khỏi danh sách?')" title="Xóa bỏ"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($appointments->hasPages())
                <div class="px-3 py-3 border-top bg-white">
                    {{ $appointments->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-calendar-x display-4 text-muted"></i>
                <p class="text-muted mt-2 mb-0">Không tìm thấy dữ liệu lịch hẹn nào phù hợp.</p>
            </div>
        @endif
    </div>
</div>
@endsection
