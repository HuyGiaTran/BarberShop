@extends('layouts.app')
@section('title', 'Sửa Dịch vụ')
@section('page-title', 'Sửa Dịch vụ')
@section('content')
<div class="card">
    <div class="card-header">Chỉnh sửa dịch vụ: {{ $service->name }}</div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Có lỗi xảy ra:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('services.update', $service->id) }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-md-6">
                <label class="form-label">Tên dịch vụ <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                       placeholder="Ví dụ: Cắt tóc nam" value="{{ old('name', $service->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
                <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" 
                       placeholder="Ví dụ: 50000" value="{{ old('price', $service->price) }}" step="1000" min="0" required>
                @error('price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Thời gian phục vụ (phút) <span class="text-danger">*</span></label>
                <input type="number" name="duration_minutes" class="form-control @error('duration_minutes') is-invalid @enderror" 
                       placeholder="Ví dụ: 30" value="{{ old('duration_minutes', $service->duration_minutes) }}" min="1" required>
                @error('duration_minutes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Barber</label>
                <select name="barber_id" class="form-select @error('barber_id') is-invalid @enderror">
                    <option value="">-- Chọn Barber (tùy chọn) --</option>
                    @if(isset($barbers) && count($barbers) > 0)
                        @foreach($barbers as $barber)
                            <option value="{{ $barber->id }}" {{ old('barber_id', $service->barber_id) == $barber->id ? 'selected' : '' }}>
                                {{ $barber->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
                @error('barber_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                          rows="4" placeholder="Nhập mô tả dịch vụ">{{ old('description', $service->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-warning"><i class="bi bi-check-circle"></i> Cập nhật</button>
                <a href="{{ route('services.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Quay lại</a>
            </div>
        </form>
    </div>
</div>
@endsection