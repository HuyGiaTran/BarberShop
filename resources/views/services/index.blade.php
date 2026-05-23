@extends('layouts.app')
@section('title', 'Danh sách Dịch vụ')
@section('page-title', 'Danh sách Dịch vụ')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Danh sách Dịch vụ</span>
        <a href="{{ route('services.create') }}" class="btn btn-barber btn-sm"><i class="bi bi-plus-circle"></i> Thêm dịch vụ</a>
    </div>
    <div class="card-body">
        @if(isset($services) && count($services) > 0)
            <table class="table table-hover">
                <thead><tr><th>ID</th><th>Tên</th><th>Giá</th><th>Thời gian</th><th>Thao tác</th></tr></thead>
                <tbody>
                    @foreach($services as $service)
                    <tr>
                        <td>{{ $service->id }}</td>
                        <td>{{ $service->name }}</td>
                        <td>{{ number_format($service->price) }}đ</td>
                        <td>{{ $service->duration_minutes }} phút</td>
                        <td>
                            <a href="{{ route('services.edit', $service->id) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('services.destroy', $service->id) }}" method="POST" style="display:inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Xóa?')"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted">Chưa có dịch vụ nào.</p>
        @endif
    </div>
</div>
@endsection