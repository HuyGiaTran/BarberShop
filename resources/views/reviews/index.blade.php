@extends('layouts.app')
@section('title', 'Đánh giá')
@section('page-title', 'Quản lý Đánh giá')
@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-light fw-bold text-secondary"><i class="bi bi-funnel"></i> Bộ lọc đánh giá</div>
    <div class="card-body">
        <form action="{{ route('admin.reviews.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="rating" class="form-label small fw-bold">Số sao</label>
                <select class="form-select" id="rating" name="rating">
                    <option value="">-- Tất cả số sao --</option>
                    <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Sao</option>
                    <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Sao</option>
                    <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Sao</option>
                    <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Sao</option>
                    <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Sao</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="barber_id" class="form-label small fw-bold">Barber</label>
                <select class="form-select" id="barber_id" name="barber_id">
                    <option value="">-- Tất cả Barber --</option>
                    @foreach($barbers as $barber)
                        <option value="{{ $barber->id }}" {{ request('barber_id') == $barber->id ? 'selected' : '' }}>{{ $barber->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Lọc kết quả</button>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary w-100">Xóa bộ lọc</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-star"></i> Danh sách đánh giá</h5>
    </div>
    <div class="card-body p-0">
        @if(isset($reviews) && count($reviews) > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">ID</th>
                            <th>Khách hàng</th>
                            <th>Barber</th>
                            <th>Lịch hẹn</th>
                            <th>Đánh giá</th>
                            <th>Nội dung</th>
                            <th>Ngày tạo</th>
                            <th class="pe-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviews as $review)
                        <tr>
                            <td class="ps-3 fw-bold">#{{ $review->id }}</td>
                            <td>
                                <div class="fw-bold">{{ $review->user->name ?? 'N/A' }}</div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><i class="bi bi-person-badge"></i> {{ $review->barber->name ?? 'N/A' }}</span></td>
                            <td>
                                @if($review->appointment)
                                    <a href="{{ route('admin.appointments.show', $review->appointment_id) }}" class="small fw-bold text-primary">#{{ $review->appointment_id }}</a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                    @endfor
                                </div>
                            </td>
                            <td>
                                <span class="d-inline-block text-truncate text-wrap" style="max-width: 250px;">
                                    {{ $review->comment ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <div class="small text-muted">{{ $review->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="pe-3">
                                <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?')" title="Xóa bỏ"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($reviews->hasPages())
                <div class="px-3 py-3 border-top bg-white">
                    {{ $reviews->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-star display-4 text-muted"></i>
                <p class="text-muted mt-2 mb-0">Không tìm thấy dữ liệu đánh giá nào.</p>
            </div>
        @endif
    </div>
</div>
@endsection
