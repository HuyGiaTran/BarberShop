@extends('layouts.app')
@section('title', 'Chi tiết Dịch vụ')
@section('page-title', 'Chi tiết Dịch vụ')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Chi tiết Dịch vụ</span>
        <div>
            <a href="{{ route('admin.services.edit', $service->id) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i> Sửa</a>
            <a href="{{ route('admin.services.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Quay lại</a>
        </div>
    </div>
    <div class="card-body">
        @if(isset($service))
            <div class="row">
                <div class="col-md-8">
                    <table class="table">
                        <tr>
                            <td width="30%"><strong>ID:</strong></td>
                            <td>#{{ $service->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tên dịch vụ:</strong></td>
                            <td>{{ $service->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Giá:</strong></td>
                            <td><span class="badge bg-success fs-6">{{ number_format($service->price) }}đ</span></td>
                        </tr>
                        <tr>
                            <td><strong>Barber:</strong></td>
                            <td>{{ $service->barber ? $service->barber->name : 'Không có' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Thời gian:</strong></td>
                            <td>{{ $service->duration_minutes }} phút</td>
                        </tr>
                        <tr>
                            <td><strong>Mô tả:</strong></td>
                            <td>{{ $service->description ?? 'Không có mô tả' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Ngày tạo:</strong></td>
                            <td>{{ $service->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Cập nhật lần cuối:</strong></td>
                            <td>{{ $service->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Không tìm thấy dịch vụ.
            </div>
        @endif
    </div>
</div>
@endsection