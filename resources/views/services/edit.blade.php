@extends('layouts.app')
@section('title', 'Sửa Dịch vụ')
@section('page-title', 'Sửa Dịch vụ')
@section('content')
<div class="card">
    <div class="card-header">Sửa Dịch vụ</div>
    <div class="card-body">
        <p class="text-muted">Form sửa dịch vụ - sẽ được code bởi thành viên 3</p>
        <a href="{{ route('services.index') }}" class="btn btn-secondary">Quay lại</a>
    </div>
</div>
@endsection