@extends('layouts.app')
@section('title', 'Chi tiết Dịch vụ')
@section('page-title', 'Chi tiết Dịch vụ')
@section('content')
<div class="card">
    <div class="card-header">Chi tiết Dịch vụ</div>
    <div class="card-body">
        @if(isset($service))
            <p><strong>ID:</strong> {{ $service->id }}</p>
            <p><strong>Tên:</strong> {{ $service->name }}</p>
            <p><strong>Giá:</strong> {{ number_format($service->price) }}đ</p>
            <p><strong>Thời gian:</strong> {{ $service->duration_minutes }} phút</p>
        @else
            <p class="text-muted">Không tìm thấy dịch vụ.</p>
        @endif
        <a href="{{ route('services.index') }}" class="btn btn-secondary">Quay lại</a>
    </div>
</div>
@endsection