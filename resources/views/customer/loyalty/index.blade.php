@extends('layouts.public')

@section('title', 'Điểm thưởng thành viên')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">🏆 Chương trình khách hàng thân thiết</h4>
                </div>
                <div class="card-body">
                    <!-- Thông tin điểm & hạng -->
                    <div class="row text-center mb-4">
                        <div class="col-md-6">
                            <div class="p-4 border rounded bg-light">
                                <h5>Điểm hiện tại</h5>
                                <p class="display-4 text-primary fw-bold">{{ number_format($summary['points']) }}</p>
                                <p class="text-muted">1.000đ = 1 điểm</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 border rounded bg-light">
                                <h5>Hạng thành viên</h5>
                                @php
                                    $tierColors = [
                                        'bronze' => ['bg' => '#CD7F32', 'text' => 'Đồng (Bronze)'],
                                        'silver' => ['bg' => '#C0C0C0', 'text' => 'Bạc (Silver)'],
                                        'gold' => ['bg' => '#FFD700', 'text' => 'Vàng (Gold)'],
                                        'platinum' => ['bg' => '#E5E4E2', 'text' => 'Bạch kim (Platinum)'],
                                    ];
                                    $tierColor = $tierColors[$summary['tier']] ?? ['bg' => '#CD7F32', 'text' => 'Đồng'];
                                @endphp
                                <div class="d-inline-block rounded-circle p-4 mb-2" style="background: {{ $tierColor['bg'] }}; width: 100px; height: 100px; line-height: 68px; font-size: 24px;">
                                    👑
                                </div>
                                <p class="fs-5 fw-bold">{{ $tierColor['text'] }}</p>
                                @if($summary['discount'] > 0)
                                <div class="alert alert-success py-2 px-3 mt-2">
                                    🎉 <strong>Giảm {{ $summary['discount'] }}%</strong> cho lần đặt lịch tiếp theo!
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($summary['benefits'])
                    <div class="alert alert-info text-center">
                        <i class="bi bi-star-fill"></i> <strong>Ưu đãi của bạn:</strong> {{ $summary['benefits'] }}
                    </div>
                    @endif

                    <!-- Thanh tiến trình lên hạng tiếp theo -->
                    @if($summary['next_tier'])
                    <div class="mb-4">
                        <h5>Tiến trình lên hạng <strong>{{ $summary['next_tier_label'] }}</strong></h5>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: {{ $summary['progress_percentage'] }}%;" 
                                 aria-valuenow="{{ $summary['progress_percentage'] }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ $summary['progress_percentage'] }}%
                            </div>
                        </div>
                        <small class="text-muted">Cần thêm <strong>{{ number_format($summary['points_to_next_tier']) }}</strong> điểm nữa để lên {{ $summary['next_tier_label'] }}</small>
                    </div>
                    @else
                    <div class="alert alert-success">
                        🎉 Bạn đã đạt hạng cao nhất! Cảm ơn bạn đã đồng hành cùng chúng tôi.
                    </div>
                    @endif

                    <!-- Bảng hạng -->
                    <div class="mb-4">
                        <h5>Bảng hạng thành viên</h5>
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Hạng</th>
                                    <th>Điểm tối thiểu</th>
                                    <th>Mô tả</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="{{ $summary['tier'] == 'bronze' ? 'table-primary' : '' }}">
                                    <td>🥉 Đồng</td>
                                    <td>0 điểm</td>
                                    <td>Hạng mặc định khi đăng ký</td>
                                </tr>
                                <tr class="{{ $summary['tier'] == 'silver' ? 'table-primary' : '' }}">
                                    <td>🥈 Bạc</td>
                                    <td>200 điểm</td>
                                    <td>Giảm 5% cho lần cắt tiếp theo</td>
                                </tr>
                                <tr class="{{ $summary['tier'] == 'gold' ? 'table-primary' : '' }}">
                                    <td>🥇 Vàng</td>
                                    <td>400 điểm</td>
                                    <td>Giảm 10% + ưu tiên đặt lịch</td>
                                </tr>
                                <tr class="{{ $summary['tier'] == 'platinum' ? 'table-primary' : '' }}">
                                    <td>💎 Bạch kim</td>
                                    <td>700 điểm</td>
                                    <td>Giảm 15% + ưu tiên + quà tặng sinh nhật</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Lịch sử giao dịch điểm -->
                    <div>
                        <h5>Lịch sử tích điểm gần đây</h5>
                        @if(count($summary['recent_logs']) > 0)
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Ngày</th>
                                    <th>Điểm</th>
                                    <th>Số dư</th>
                                    <th>Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($summary['recent_logs'] as $log)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($log['created_at'])->format('d/m/Y H:i') }}</td>
                                    <td class="text-success fw-bold">+{{ number_format($log['points']) }}</td>
                                    <td>{{ number_format($log['balance_after']) }}</td>
                                    <td>{{ $log['note'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <p class="text-muted">Chưa có giao dịch nào. Hãy đặt lịch để tích điểm nhé!</p>
                        @endif
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('customer.appointments.index') }}" class="btn btn-primary">📅 Xem lịch hẹn của tôi</a>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">🏠 Về trang chủ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection