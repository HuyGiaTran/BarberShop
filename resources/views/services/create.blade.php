@extends('layouts.app')
@section('title', 'Thêm Dịch vụ')
@section('page-title', 'Thêm Dịch vụ')
@section('content')
<div class="card">
    <div class="card-header">Thêm Dịch vụ</div>
    <div class="card-body">
        <p class="text-muted">Form thêm dịch vụ - sẽ được code bởi thành viên 3</p>
        <a href="{{ route('services.index') }}" class="btn btn-secondary">Quay lại</a>
    </div>
</div>
@endsection