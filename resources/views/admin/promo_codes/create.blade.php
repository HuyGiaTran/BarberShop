@extends('layouts.app')
@section('title', 'Thêm mã giảm giá')
@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">🎟️ Thêm mã giảm giá</h1>
        <a href="{{ route('admin.promo_codes.index') }}" class="btn btn-secondary">← Quay lại</a>
    </div>
    <div class="card shadow"><div class="card-body">
        <form method="POST" action="{{ route('admin.promo_codes.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Mã giảm giá *</label><input name="code" class="form-control" required maxlength="50"></div>
                <div class="col-md-3"><label class="form-label">Loại</label><select name="discount_type" class="form-control"><option value="percentage">%</option><option value="fixed">VNĐ</option></select></div>
                <div class="col-md-3"><label class="form-label">Giá trị *</label><input name="discount_value" type="number" step="0.01" class="form-control" required></div>
                <div class="col-md-2"><label class="form-label">Kích hoạt</label><select name="is_active" class="form-control"><option value="1">Có</option><option value="0">Không</option></select></div>
                <div class="col-md-3"><label class="form-label">Đơn tối thiểu</label><input name="min_order_amount" type="number" step="0.01" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Giảm tối đa (%)</label><input name="max_discount" type="number" step="0.01" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Số lượt dùng</label><input name="usage_limit" type="number" class="form-control" value="0"></div>
                <div class="col-md-3"><label class="form-label">Ngày bắt đầu</label><input name="starts_at" type="datetime-local" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Ngày hết hạn</label><input name="expires_at" type="datetime-local" class="form-control"></div>
            </div>
            <button class="btn btn-primary mt-3">Tạo mã</button>
        </form>
    </div></div>
</div>
@endsection