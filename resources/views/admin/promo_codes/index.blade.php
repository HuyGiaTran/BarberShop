@extends('layouts.app')
@section('title', 'Mã giảm giá')
@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">🎟️ Mã giảm giá</h1>
        <a href="{{ route('admin.promo_codes.create') }}" class="btn btn-primary">+ Thêm mã</a>
    </div>
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead><tr><th>Mã</th><th>Loại</th><th>Giá trị</th><th>Đơn tối thiểu</th><th>Đã dùng/Limit</th><th>HSD</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                    <tbody>
                        @foreach($promos as $p)
                        <tr>
                            <td class="fw-bold">{{ $p->code }}</td>
                            <td>{{ $p->discount_type == 'percentage' ? '%' : 'VNĐ' }}</td>
                            <td>{{ $p->discount_type == 'percentage' ? $p->discount_value.'%' : number_format($p->discount_value,0,',','.').'đ' }}</td>
                            <td>{{ $p->min_order_amount ? number_format($p->min_order_amount,0,',','.').'đ' : 'Không' }}</td>
                            <td>{{ $p->used_count }}/{{ $p->usage_limit ?: '∞' }}</td>
                            <td>{{ $p->expires_at ? $p->expires_at->format('d/m/Y') : 'Vĩnh viễn' }}</td>
                            <td>{!! $p->is_active ? '<span class="badge bg-success">Hoạt động</span>' : '<span class="badge bg-secondary">Tắt</span>' !!}</td>
                            <td>
                                <a href="{{ route('admin.promo_codes.edit', $p) }}" class="btn btn-sm btn-warning">Sửa</a>
                                <form action="{{ route('admin.promo_codes.destroy', $p) }}" method="POST" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Xóa?')">Xóa</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $promos->links() }}
            </div>
        </div>
    </div>
</div>
@endsection