@extends('layouts.app')
@section('title', 'Sửa lịch hẹn')
@section('page-title', 'Sửa lịch hẹn')
@section('content')
<div class="card">
    <div class="card-header">Sửa lịch hẹn</div>
    <div class="card-body">
        <p class="text-muted">Form sửa lịch hẹn - sẽ được code bởi thành viên 4</p>
        <a href="{{ route('appointments.index') }}" class="btn btn-secondary">Quay lại</a>
    </div>
</div>
@endsection