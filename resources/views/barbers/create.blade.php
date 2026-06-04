@extends('layouts.app')
@section('title', 'Thêm Barber')
@section('page-title', 'Thêm Barber mới')
@section('content')
<div class="card">
    <div class="card-header">Thêm Barber</div>
    <div class="card-body">
        <p class="text-muted">Form thêm barber - Code bởi thành viên 2</p>
        <a href="{{ route('admin.barbers.index') }}" class="btn btn-secondary">Quay lại</a>
    </div>
</div>
@endsection