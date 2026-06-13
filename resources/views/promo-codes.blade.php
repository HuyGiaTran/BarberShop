@extends('layouts.public')
@section('title', 'Mã giảm giá')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">🎟️ Mã giảm giá đang hoạt động</h4>
                </div>
                <div class="card-body">
                    @if($promos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mã</th>
                                        <th>Giảm</th>
                                        <th>Đơn tối thiểu</th>
                                        <th>Hạn sử dụng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($promos as $p)
                                    <tr>
                                        <td class="fw-bold text-uppercase d-flex align-items-center gap-2">
                                            {{ $p->code }}
                                            <button class="btn btn-sm btn-outline-primary copy-btn" data-code="{{ $p->code }}" title="Sao chép mã">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </td>
                                        <td>
                                            @if($p->discount_type == 'percentage')
                                                {{ $p->discount_value }}% 
                                                @if($p->max_discount)
                                                    (tối đa {{ number_format($p->max_discount, 0, ',', '.') }}đ)
                                                @endif
                                            @else
                                                {{ number_format($p->discount_value, 0, ',', '.') }}đ
                                            @endif
                                        </td>
                                        <td>{{ $p->min_order_amount ? number_format($p->min_order_amount, 0, ',', '.') . 'đ' : 'Không' }}</td>
                                        <td>{{ $p->expires_at ? $p->expires_at->format('d/m/Y') : 'Vĩnh viễn' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Nhập mã giảm giá khi đặt lịch để được giảm giá ngay!
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-ticket display-4 text-muted"></i>
                            <p class="mt-3 text-muted">Hiện tại chưa có mã giảm giá nào đang hoạt động.</p>
                        </div>
                    @endif

                    <div class="mt-3">
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">🏠 Về trang chủ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.copy-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const code = this.dataset.code;
            navigator.clipboard.writeText(code).then(function() {
                const icon = btn.querySelector('i');
                const originalClass = icon.className;
                icon.className = 'bi bi-check-lg';
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-success');
                setTimeout(function() {
                    icon.className = originalClass;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-primary');
                }, 1500);
            });
        });
    });
});
</script>
@endsection
