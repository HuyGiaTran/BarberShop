@extends('layouts.app')

@section('title', 'Chi tiết Barber')
@section('page-title', 'Chi tiết Barber')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Chi tiết Barber</span>

        <a href="{{ route('admin.barbers.index') }}" class="btn btn-secondary btn-sm">
            Quay lại
        </a>
    </div>

    <div class="card-body">
        @if(isset($barber))
            <div class="row">
                <div class="col-md-3 text-center">
                    @if($barber->avatar)
                        <img src="{{ asset('storage/' . $barber->avatar) }}"
                             alt="Avatar"
                             class="rounded-circle mb-3"
                             width="150"
                             height="150"
                             style="object-fit: cover;">
                    @else
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                             style="width:150px; height:150px;">
                            <span class="text-muted">No Avatar</span>
                        </div>
                    @endif
                </div>

                <div class="col-md-9">
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">ID</th>
                            <td>{{ $barber->id }}</td>
                        </tr>

                        <tr>
                            <th>Tài khoản người dùng</th>
                            <td>
                                @if($barber->user)
                                    {{ $barber->user->name }} - {{ $barber->user->email }}
                                @else
                                    <span class="text-muted">Chưa liên kết user</span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th>Tên Barber</th>
                            <td>{{ $barber->name }}</td>
                        </tr>

                        <tr>
                            <th>Số điện thoại</th>
                            <td>{{ $barber->phone ?? 'Chưa có' }}</td>
                        </tr>

                        <tr>
                            <th>Giới thiệu</th>
                            <td>{{ $barber->bio ?? 'Chưa có giới thiệu' }}</td>
                        </tr>

                        <tr>
                            <th>Trạng thái</th>
                            <td>
                                @if($barber->is_active)
                                    <span class="badge bg-success">Hoạt động</span>
                                @else
                                    <span class="badge bg-danger">Tạm ngưng</span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th>Ngày tạo</th>
                            <td>
                                {{ $barber->created_at ? $barber->created_at->format('d/m/Y H:i') : 'Không có' }}
                            </td>
                        </tr>

                        <tr>
                            <th>Ngày cập nhật</th>
                            <td>
                                {{ $barber->updated_at ? $barber->updated_at->format('d/m/Y H:i') : 'Không có' }}
                            </td>
                        </tr>
                    </table>

                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.barbers.edit', $barber->id) }}" class="btn btn-warning">
                            Sửa Barber
                        </a>

                        <form action="{{ route('admin.barbers.destroy', $barber->id) }}"
                              method="POST"
                              onsubmit="return confirm('Bạn có chắc muốn xóa barber này không?')">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger">
                                Xóa Barber
                            </button>
                        </form>

                        <a href="{{ route('admin.barbers.index') }}" class="btn btn-secondary">
                            Quay lại
                        </a>
                    </div>
                </div>
            </div>
        @else
            <p class="text-muted">Không tìm thấy barber.</p>

            <a href="{{ route('admin.barbers.index') }}" class="btn btn-secondary">
                Quay lại
            </a>
        @endif
    </div>
</div>
@endsection