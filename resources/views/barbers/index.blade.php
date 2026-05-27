@extends('layouts.app')

@section('title', 'Quản lý Barber')
@section('page-title', 'Danh sách Barber')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Danh sách Barber</span>

        <a href="{{ route('barbers.create') }}" class="btn btn-barber btn-sm">
            <i class="bi bi-plus-circle"></i> Thêm Barber
        </a>
    </div>

    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('barbers.index') }}" method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-10">
                    <input type="text"
                           name="keyword"
                           class="form-control"
                           placeholder="Tìm kiếm theo tên, số điện thoại, giới thiệu..."
                           value="{{ request('keyword') }}">
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Tìm
                    </button>

                    <a href="{{ route('barbers.index') }}" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        @if(isset($barbers) && $barbers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên</th>
                            <th>Số điện thoại</th>
                            <th>Giới thiệu</th>
                            <th>Trạng thái</th>
                            <th width="160">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($barbers as $barber)
                            <tr>
                                <td>{{ $barber->id }}</td>

                                <td>{{ $barber->name }}</td>

                                <td>{{ $barber->phone ?? 'Chưa có' }}</td>

                                <td>
                                    {{ \Illuminate\Support\Str::limit($barber->bio ?? 'Chưa có giới thiệu', 40) }}
                                </td>

                                <td>
                                    @if($barber->is_active)
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-danger">Tạm ngưng</span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('barbers.show', $barber->id) }}"
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="{{ route('barbers.edit', $barber->id) }}"
                                       class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form action="{{ route('barbers.destroy', $barber->id) }}"
                                          method="POST"
                                          style="display:inline">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Bạn có chắc muốn xóa barber này không?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $barbers->links() }}
            </div>
        @else
            <div class="alert alert-warning mb-0">
                Chưa có barber nào.
                <a href="{{ route('barbers.create') }}">Thêm barber</a>
            </div>
        @endif
    </div>
</div>
@endsection