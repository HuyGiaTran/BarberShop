@extends('layouts.app')
@section('title', 'Danh sách Dịch vụ')
@section('page-title', 'Danh sách Dịch vụ')
@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Bộ lọc dịch vụ</span>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.services.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tìm kiếm tên/mô tả</label>
                <input type="text" name="search" class="form-control" placeholder="Nhập tên hoặc mô tả" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Giá tối thiểu (VNĐ)</label>
                <input type="number" name="min_price" class="form-control" placeholder="Từ" value="{{ request('min_price') }}" min="0" step="1000">
            </div>
            <div class="col-md-3">
                <label class="form-label">Giá tối đa (VNĐ)</label>
                <input type="number" name="max_price" class="form-control" placeholder="Đến" value="{{ request('max_price') }}" min="0" step="1000">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-barber flex-grow-1"><i class="bi bi-funnel"></i> Lọc</button>
                <a href="{{ route('admin.services.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Danh sách Dịch vụ ({{ count($services) }} dịch vụ)</span>
        <a href="{{ route('admin.services.create') }}" class="btn btn-barber btn-sm"><i class="bi bi-plus-circle"></i> Thêm dịch vụ</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(isset($services) && count($services) > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Tên</th>
                            <th>Barber</th>
                            <th>Mô tả</th>
                            <th>Giá</th>
                            <th>Thời gian</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($services as $service)
                        <tr>
                            <td>#{{ $service->id }}</td>
                            <td><strong>{{ $service->name }}</strong></td>
                            <td>{!! $service->barber ? $service->barber->name : '<span class="badge bg-secondary">Không có</span>' !!}</td>
                            <td>{{ Str::limit($service->description, 50) }}</td>
                            <td><span class="badge bg-success">{{ number_format($service->price) }}đ</span></td>
                            <td>{{ $service->duration_minutes }} phút</td>
                            <td>
                                <a href="{{ route('admin.services.show', $service->id) }}" class="btn btn-sm btn-info" title="Xem"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('admin.services.edit', $service->id) }}" class="btn btn-sm btn-warning" title="Sửa"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('admin.services.destroy', $service->id) }}" method="POST" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa dịch vụ này?')" title="Xóa"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle"></i> Không có dịch vụ nào phù hợp với bộ lọc.
            </div>
        @endif
    </div>
</div>
@endsection