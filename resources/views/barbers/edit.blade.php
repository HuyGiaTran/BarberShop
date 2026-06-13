@extends('layouts.app')
@section('title', 'Sửa Barber')
@section('page-title', 'Sửa Barber')
@section('content')
<div class="card">
    <div class="card-header">Sửa Barber</div>
    <div class="card-body">
        <form action="{{ route('admin.barbers.update', $barber) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('barbers._form', [
                'barber' => $barber,
                'submitLabel' => 'Cập nhật barber',
            ])
        </form>
    </div>
</div>
@endsection
