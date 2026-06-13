@extends('layouts.app')
@section('title', 'Thêm Barber')
@section('page-title', 'Thêm Barber mới')
@section('content')
<div class="card">
    <div class="card-header">Thêm Barber</div>
    <div class="card-body">
        <form action="{{ route('admin.barbers.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @include('barbers._form', [
                'barber' => null,
                'submitLabel' => 'Thêm barber',
            ])
        </form>
    </div>
</div>
@endsection
