@extends('layouts.app')

@section('title', 'Cấu hình hoa hồng')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">💰 Cấu hình hoa hồng Barber</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thêm / Sửa cấu hình hoa hồng</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.commissions.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Barber</label>
                        <select name="barber_id" class="form-control" required>
                            <option value="">-- Chọn Barber --</option>
                            @foreach($barbers as $barber)
                                <option value="{{ $barber->id }}">{{ $barber->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Dịch vụ</label>
                        <select name="service_id" class="form-control" required>
                            <option value="">-- Chọn dịch vụ --</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">% Hoa hồng</label>
                        <input type="number" name="commission_percent" class="form-control" value="30" min="0" max="100" step="0.01" required>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách hoa hồng hiện tại</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Barber</th>
                            <th>Dịch vụ</th>
                            <th>% Hoa hồng</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($barbers as $barber)
                            @foreach($barber->commissions as $commission)
                            <tr>
                                <td>{{ $barber->name }}</td>
                                <td>{{ $commission->service->name ?? 'N/A' }}</td>
                                <td>{{ number_format($commission->commission_percent, 1) }}%</td>
                                <td>
                                    <form action="{{ route('admin.commissions.destroy', $commission) }}" method="POST" style="display:inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xóa?')">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="4" class="text-center">Chưa có cấu hình hoa hồng nào.</td></tr>
                        @endforelse
                        @php
                            $hasCommissions = $barbers->contains(fn($b) => $b->commissions->isNotEmpty());
                        @endphp
                        @if(!$hasCommissions)
                        @endif
                    </tbody>
                </table>
                @if(!$hasCommissions)
                <p class="text-muted text-center">Chưa có cấu hình hoa hồng. Hãy thêm cấu hình mới ở form trên.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection