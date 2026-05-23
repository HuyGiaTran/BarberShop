@extends('layouts.app')
@section('title', 'Chi tiết Barber')
@section('page-title', 'Chi tiết Barber')
@section('content')
<div class="card">
    <div class="card-header">Chi tiết Barber</div>
    <div class="card-body">
        @if(isset($barber))
            <p><strong>ID:</strong> {{ $barber->id }}</p>
            <p><strong>Tên:</strong> {{ $barber->name }}</p>
            <p><strong>SĐT:</strong> {{ $barber->phone }}</p>
            <p><strong>Trạng thái:</strong> {{ $barber->is_active ? 'Hoạt động' : 'Tạm ngưng' }}</p>
        @else
            <p class="text-muted">Không tìm thấy barber.</p>
        @endif
        <a href="{{ route('barbers.index') }}" class="btn btn-secondary">Quay lại</a>
    </div>
</div>
@endsection