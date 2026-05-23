@extends('layouts.app')
@section('title', 'Lịch hẹn')
@section('page-title', 'Danh sách Lịch hẹn')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Lịch hẹn</span>
        <a href="{{ route('appointments.create') }}" class="btn btn-barber btn-sm"><i class="bi bi-plus-circle"></i> Đặt lịch mới</a>
    </div>
    <div class="card-body">
        @if(isset($appointments) && count($appointments) > 0)
            <table class="table table-hover">
                <thead>
                    <tr><th>ID</th><th>Khách hàng</th><th>Barber</th><th>Dịch vụ</th><th>Ngày</th><th>Giờ</th><th>Trạng thái</th><th>Thao tác</th></tr>
                </thead>
                <tbody>
                    @foreach($appointments as $apt)
                    <tr>
                        <td>{{ $apt->id }}</td>
                        <td>{{ $apt->user->name ?? 'N/A' }}</td>
                        <td>{{ $apt->barber->name ?? 'N/A' }}</td>
                        <td>{{ $apt->service->name ?? 'N/A' }}</td>
                        <td>{{ $apt->appointment_date }}</td>
                        <td>{{ $apt->appointment_time }}</td>
                        <td>
                            @switch($apt->status)
                                @case('pending') <span class="badge bg-warning text-dark">Chờ</span> @break
                                @case('confirmed') <span class="badge bg-primary">Xác nhận</span> @break
                                @case('completed') <span class="badge bg-success">Xong</span> @break
                                @case('cancelled') <span class="badge bg-danger">Hủy</span> @break
                                @default <span class="badge bg-secondary">{{ $apt->status }}</span>
                            @endswitch
                        </td>
                        <td>
                            <a href="{{ route('appointments.edit', $apt->id) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('appointments.destroy', $apt->id) }}" method="POST" style="display:inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Hủy lịch hẹn?')"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted">Chưa có lịch hẹn nào.</p>
        @endif
    </div>
</div>
@endsection