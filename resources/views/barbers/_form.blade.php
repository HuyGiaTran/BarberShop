@if ($errors->any())
    <div class="alert alert-danger">
        <div class="fw-semibold mb-2">Vui lòng kiểm tra lại thông tin:</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Tên barber</label>
        <input
            type="text"
            id="name"
            name="name"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $barber?->name ?? '') }}"
            required
        >
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label">Email đăng nhập</label>
        <input
            type="email"
            id="email"
            name="email"
            class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email', $barber?->user?->email ?? '') }}"
            required
        >
    </div>

    <div class="col-md-6">
        <label for="phone" class="form-label">Số điện thoại</label>
        <input
            type="text"
            id="phone"
            name="phone"
            class="form-control @error('phone') is-invalid @enderror"
            value="{{ old('phone', $barber?->phone ?? '') }}"
            maxlength="20"
        >
    </div>

    <div class="col-md-6">
        <label for="password" class="form-label">
            Mật khẩu
            @isset($barber)
                <span class="text-muted small">(để trống nếu không đổi)</span>
            @endisset
        </label>
        <input
            type="password"
            id="password"
            name="password"
            class="form-control @error('password') is-invalid @enderror"
            {{ isset($barber) ? '' : 'required' }}
        >
    </div>

    <div class="col-12">
        <label for="bio" class="form-label">Giới thiệu</label>
        <textarea
            id="bio"
            name="bio"
            rows="4"
            class="form-control @error('bio') is-invalid @enderror"
        >{{ old('bio', $barber?->bio ?? '') }}</textarea>
    </div>

    <div class="col-md-6">
        <label for="avatar" class="form-label">Ảnh đại diện</label>
        <input
            type="file"
            id="avatar"
            name="avatar"
            accept="image/*"
            class="form-control @error('avatar') is-invalid @enderror"
        >
        <div class="form-text">Hỗ trợ ảnh JPG, PNG, WEBP. Tối đa 2MB.</div>
    </div>

    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check form-switch">
            <input
                type="checkbox"
                class="form-check-input"
                id="is_active"
                name="is_active"
                value="1"
                {{ old('is_active', isset($barber) ? (int) $barber->is_active : 1) ? 'checked' : '' }}
            >
            <label class="form-check-label" for="is_active">Đang hoạt động</label>
        </div>
    </div>

    @if (! empty($barber?->avatar))
        <div class="col-12">
            <label class="form-label d-block">Ảnh hiện tại</label>
            <img
                src="{{ asset('storage/' . $barber->avatar) }}"
                alt="{{ $barber->name }}"
                class="rounded border"
                style="width: 120px; height: 120px; object-fit: cover;"
            >
        </div>
    @endif
</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-barber">
        <i class="bi bi-save me-1"></i>{{ $submitLabel }}
    </button>

    <a href="{{ route('admin.barbers.index') }}" class="btn btn-secondary">
        Quay lại
    </a>
</div>
