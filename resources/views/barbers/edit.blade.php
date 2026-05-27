@extends('layouts.app')

@section('title', 'Sửa Barber')
@section('page-title', 'Sửa Barber')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Sửa thông tin Barber</h5>
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

        <form action="{{ route('barbers.update', $barber->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="user_id" class="form-label">Tài khoản người dùng</label>
                <select name="user_id" id="user_id" class="form-select" required>
                    <option value="">-- Chọn tài khoản --</option>

                    @foreach ($users as $user)
                        <option value="{{ $user->id }}"
                            {{ old('user_id', $barber->user_id) == $user->id ? 'selected' : '' }}>
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
                       value="{{ old('name', $barber->name) }}"
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
                       value="{{ old('phone', $barber->phone) }}"
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
                          placeholder="Nhập giới thiệu, kinh nghiệm của barber">{{ old('bio', $barber->bio) }}</textarea>

                @error('bio')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Avatar hiện tại</label><br>

                @if ($barber->avatar)
                    <img src="{{ asset('storage/' . $barber->avatar) }}"
                         alt="Avatar"
                         width="100"
                         height="100"
                         class="rounded mb-2"
                         style="object-fit: cover;">
                @else
                    <p class="text-muted mb-2">Chưa có avatar</p>
                @endif

                <input type="file"
                       name="avatar"
                       id="avatar"
                       class="form-control"
                       accept="image/*">

                <small class="text-muted">
                    Nếu không chọn ảnh mới, hệ thống sẽ giữ ảnh cũ.
                </small>

                @error('avatar')
                    <small class="text-danger d-block">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox"
                       name="is_active"
                       value="1"
                       id="is_active"
                       class="form-check-input"
                       {{ old('is_active', $barber->is_active) ? 'checked' : '' }}>

                <label for="is_active" class="form-check-label">
                    Đang hoạt động
                </label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    Cập nhật Barber
                </button>

                <a href="{{ route('barbers.index') }}" class="btn btn-secondary">
                    Quay lại
                </a>
            </div>
        </form>
    </div>
</div>
@endsection