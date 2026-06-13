@extends('layouts.app')
@section('title', 'Sửa mã giảm giá')
@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">🎟️ Sửa mã: {{ $promoCode->code }}</h1>
        <a href="{{ route('admin.promo_codes.index') }}" class="btn btn-secondary">← Quay lại</a>
    </div>
    <div class="card shadow"><div class="card-body">
        <form method="POST" action="{{ route('admin.promo_codes.update', $promoCode) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Mã giảm giá *</label><input name="code" class="form-control" value="{{ $promoCode->code }}" required maxlength="50"></div>
                <div class="col-md-3"><label class="form-label">Loại</label><select name="discount_type" class="form-control"><option value="percentage" {{ $promoCode->discount_type=='percentage'?'selected':'' }}>%</option><option value="fixed" {{ $promoCode->discount_type=='fixed'?'selected':'' }}>VNĐ</option></select></div>
                <div class="col-md-3"><label class="form-label">Giá trị *</label><input name="discount_value" type="number" step="0.01" class="form-control" value="{{ $promoCode->discount_value }}" required></div>
                <div class="col-md-2"><label class="form-label">Kích hoạt</label><select name="is_active" class="form-control"><option value="1" {{ $promoCode->is_active?'selected':'' }}>Có</option><option value="0" {{ !$promoCode->is_active?'selected':'' }}>Không</option></select></div>
                <div class="col-md-3"><label class="form-label">Đơn tối thiểu</label><input name="min_order_amount" type="number" step="0.01" class="form-control" value="{{ $promoCode->min_order_amount }}"></div>
                <div class="col-md-3"><label class="form-label">Giảm tối đa (%)</label><input name="max_discount" type="number" step="0.01" class="form-control" value="{{ $promoCode->max_discount }}"></div>
                <div class="col-md-3"><label class="form-label">Số lượt dùng</label><input name="usage_limit" type="number" class="form-control" value="{{ $promoCode->usage_limit }}"></div>
                <div class="col-md-3"><label class="form-label">Ngày bắt đầu</label><input name="starts_at" type="datetime-local" class="form-control" value="{{ $promoCode->starts_at ? $promoCode->starts_at->format('Y-m-d\TH:i') : '' }}"></div>
                <div class="col-md-3"><label class="form-label">Ngày hết hạn</label><input name="expires_at" type="datetime-local" class="form-control" value="{{ $promoCode->expires_at ? $promoCode->expires_at->format('Y-m-d\TH:i') : '' }}"></div>
            </div>
            <button class="btn btn-primary mt-3">Cập nhật</button>
        </form>
    </div></div>
</div>
@endsection