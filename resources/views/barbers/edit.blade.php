@extends('layouts.app')
@section('title', 'Sửa Barber')
@section('page-title', 'Sửa Barber')
@section('content')
<div class="card">
    <div class="card-header">Sửa Barber</div>
    <div class="card-body">
        <p class="text-muted">Form sửa barber - Code bởi thành viên 2</p>
        <a href="{{ route('admin.barbers.index') }}" class="btn btn-secondary">Quay lại</a>
    </div>
</div>
@endsection