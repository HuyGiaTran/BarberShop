@extends('layouts.app')

@section('title', 'Thêm Barber')
@section('page-title', 'Thêm Barber mới')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Thêm Barber</h5>
    </div>

    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Vui lòng kiểm tra lại dữ liệu!</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('barbers.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label for="user_id" class="form-label">Tài khoản người dùng</label>
                <select name="user_id" id="user_id" class="form-select" required>
                    <option value="">-- Chọn tài khoản --</option>

                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} - {{ $user->email }}
                        </option>
                    @endforeach
                </select>

                @error('user_id')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Tên Barber</label>
                <input type="text"
                       name="name"
                       id="name"
                       class="form-control"
                       value="{{ old('name') }}"
                       placeholder="Nhập tên barber"
                       required>

                @error('name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Số điện thoại</label>
                <input type="text"
                       name="phone"
                       id="phone"
                       class="form-control"
                       value="{{ old('phone') }}"
                       placeholder="Nhập số điện thoại">

                @error('phone')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label for="bio" class="form-label">Giới thiệu</label>
                <textarea name="bio"
                          id="bio"
                          rows="4"
                          class="form-control"
                          placeholder="Nhập giới thiệu, kinh nghiệm của barber">{{ old('bio') }}</textarea>

                @error('bio')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label for="avatar" class="form-label">Ảnh đại diện</label>
                <input type="file"
                       name="avatar"
                       id="avatar"
                       class="form-control"
                       accept="image/*">

                @error('avatar')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox"
                       name="is_active"
                       value="1"
                       id="is_active"
                       class="form-check-input"
                       {{ old('is_active', 1) ? 'checked' : '' }}>

                <label for="is_active" class="form-check-label">
                    Đang hoạt động
                </label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    Lưu Barber
                </button>

                <a href="{{ route('barbers.index') }}" class="btn btn-secondary">
                    Quay lại
                </a>
            </div>
        </form>
    </div>
</div>
@endsection