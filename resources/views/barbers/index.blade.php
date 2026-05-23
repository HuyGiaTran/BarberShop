@extends('layouts.app')

@section('title', 'Quản lý Barber')
@section('page-title', 'Danh sách Barber')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Danh sách Barber</span>
        <a href="{{ route('barbers.create') }}" class="btn btn-barber btn-sm">
            <i class="bi bi-plus-circle"></i> Thêm Barber
        </a>
    </div>
    <div class="card-body">
        @if(isset($barbers) && count($barbers) > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên</th>
                            <th>Số điện thoại</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($barbers as $barber)
                        <tr>
                            <td>{{ $barber->id }}</td>
                            <td>{{ $barber->name }}</td>
                            <td>{{ $barber->phone }}</td>
                            <td>
                                @if($barber->is_active)
                                    <span class="badge bg-success">Hoạt động</span>
                                @else
                                    <span class="badge bg-danger">Tạm ngưng</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('barbers.show', $barber->id) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('barbers.edit', $barber->id) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('barbers.destroy', $barber->id) }}" method="POST" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Xóa barber này?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted">Chưa có barber nào. <a href="{{ route('barbers.create') }}">Thêm barber</a></p>
        @endif
    </div>
</div>
@endsection