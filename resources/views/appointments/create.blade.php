@extends('layouts.app')
@section('title', 'Đặt lịch hẹn')
@section('page-title', 'Đặt lịch hẹn mới')
@section('content')
<div class="card">
    <div class="card-header">Đặt lịch hẹn</div>
    <div class="card-body">
        <p class="text-muted">Form đặt lịch - sẽ được code bởi thành viên 4</p>
        <a href="{{ route('appointments.index') }}" class="btn btn-secondary">Quay lại</a>
    </div>
</div>
@endsection