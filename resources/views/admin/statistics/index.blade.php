@extends('layouts.app')

@section('title', 'Thống kê kinh doanh - BarberShop')
@section('page-title', 'Biểu đồ thống kê')

@push('styles')
<style>
    .statistics-hero {
        background: linear-gradient(135deg, rgba(26, 26, 46, 0.98), rgba(22, 33, 62, 0.92));
        color: #fff;
        border-radius: 16px;
        overflow: hidden;
        position: relative;
    }

    .statistics-hero::after {
        content: "";
        position: absolute;
        inset: auto -80px -80px auto;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(233, 69, 96, 0.32) 0%, rgba(233, 69, 96, 0) 70%);
        pointer-events: none;
    }

    .statistics-anchor {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        text-decoration: none;
        color: rgba(255, 255, 255, 0.85);
        background: rgba(255, 255, 255, 0.08);
        transition: all 0.2s ease;
    }

    .statistics-anchor:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.16);
    }

    .summary-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        height: 100%;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
    }

    .summary-card .summary-label {
        font-size: 0.88rem;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.85);
    }

    .summary-card .summary-value {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1.2;
        margin-top: 10px;
    }

    .summary-card .summary-note {
        color: rgba(255, 255, 255, 0.84);
        margin-top: 8px;
        margin-bottom: 0;
    }

    .chart-panel {
        position: relative;
        min-height: 320px;
    }

    .chart-panel.chart-panel-compact {
        min-height: 280px;
    }

    .chart-canvas {
        width: 100% !important;
        height: 320px !important;
    }

    .chart-panel.chart-panel-compact .chart-canvas {
        height: 280px !important;
    }

    .chart-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 24px;
        text-align: center;
        border: 1px dashed rgba(108, 117, 125, 0.35);
        border-radius: 14px;
        background: linear-gradient(180deg, rgba(248, 249, 250, 0.9), rgba(255, 255, 255, 0.95));
    }

    .chart-placeholder i {
        font-size: 2rem;
    }

    .chart-placeholder p {
        margin: 0;
        max-width: 320px;
    }

    .chart-card-header p {
        margin-top: 6px;
        margin-bottom: 0;
        color: #6c757d;
        font-size: 0.95rem;
        font-weight: 400;
    }

    @media (max-width: 768px) {
        .summary-card .summary-value {
            font-size: 1.4rem;
        }

        .chart-panel,
        .chart-panel.chart-panel-compact {
            min-height: 260px;
        }

        .chart-canvas,
        .chart-panel.chart-panel-compact .chart-canvas {
            height: 260px !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card statistics-hero mb-4">
        <div class="card-body p-4 p-lg-5 position-relative">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-light text-dark mb-3">Admin Analytics</span>
                    <h2 class="h3 fw-bold mb-3">Theo dõi doanh thu, khung giờ cao điểm và dịch vụ được ưa chuộng nhất</h2>
                    <p class="mb-0 text-white-50">
                        Trang này lấy dữ liệu trực tiếp từ các API thống kê hiện có để giúp admin xem nhanh hiệu quả kinh doanh ngay trong hệ quản trị.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="#revenue-section" class="statistics-anchor">
                            <i class="bi bi-graph-up"></i>
                            <span>Doanh thu</span>
                        </a>
                        <a href="#peak-hours-section" class="statistics-anchor">
                            <i class="bi bi-clock-history"></i>
                            <span>Giờ cao điểm</span>
                        </a>
                        <a href="#services-section" class="statistics-anchor">
                            <i class="bi bi-pie-chart"></i>
                            <span>Dịch vụ hot</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="summary-card stat-card bg-stat1">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div>
                        <div class="summary-label">Doanh thu gần đây</div>
                        <div class="summary-value" data-summary-value="revenue">Đang tải...</div>
                        <p class="summary-note" data-summary-note="revenue">Tổng doanh thu 7 mốc gần nhất</p>
                    </div>
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card stat-card bg-stat2">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div>
                        <div class="summary-label">Khung giờ đông nhất</div>
                        <div class="summary-value" data-summary-value="peakHours">Đang tải...</div>
                        <p class="summary-note" data-summary-note="peakHours">Top khung giờ có nhiều lịch nhất</p>
                    </div>
                    <i class="bi bi-alarm"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card stat-card bg-stat4">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div>
                        <div class="summary-label">Dịch vụ nổi bật</div>
                        <div class="summary-value" data-summary-value="services">Đang tải...</div>
                        <p class="summary-note" data-summary-note="services">Top dịch vụ được đặt nhiều nhất</p>
                    </div>
                    <i class="bi bi-stars"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            @include('admin.statistics.revenue')
        </div>
        <div class="col-lg-7">
            @include('admin.statistics.peak-hours')
        </div>
        <div class="col-lg-5">
            @include('admin.statistics.services')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const endpoints = {
        revenue: @json(url('/api/statistics/revenue')),
        peakHours: @json(url('/api/statistics/peak-hours')),
        services: @json(url('/api/statistics/services')),
    };

    const charts = {};

    const summaryElements = {
        revenue: {
            value: document.querySelector('[data-summary-value="revenue"]'),
            note: document.querySelector('[data-summary-note="revenue"]'),
        },
        peakHours: {
            value: document.querySelector('[data-summary-value="peakHours"]'),
            note: document.querySelector('[data-summary-note="peakHours"]'),
        },
        services: {
            value: document.querySelector('[data-summary-value="services"]'),
            note: document.querySelector('[data-summary-note="services"]'),
        },
    };

    function formatMoney(value) {
        return `${new Intl.NumberFormat('vi-VN').format(Number(value) || 0)}đ`;
    }

    function formatShortDate(value) {
        const parsedDate = new Date(value);

        if (Number.isNaN(parsedDate.getTime())) {
            return value;
        }

        return new Intl.DateTimeFormat('vi-VN', {
            day: '2-digit',
            month: '2-digit',
        }).format(parsedDate);
    }

    function formatTimeLabel(value) {
        return String(value || '').slice(0, 5) || 'N/A';
    }

    function updateSummary(key, value, note) {
        const target = summaryElements[key];

        if (!target) {
            return;
        }

        if (target.value) {
            target.value.textContent = value;
        }

        if (target.note) {
            target.note.textContent = note;
        }
    }

    function setPanelState(key, state, message = '') {
        const panel = document.querySelector(`[data-chart-panel="${key}"]`);

        if (!panel) {
            return;
        }

        const canvas = panel.querySelector('canvas');

        panel.querySelectorAll('[data-chart-state]').forEach((element) => {
            element.classList.add('d-none');
        });

        if (canvas) {
            canvas.classList.toggle('d-none', state !== 'ready');
        }

        if (state === 'ready') {
            return;
        }

        const activeState = panel.querySelector(`[data-chart-state="${state}"]`);

        if (!activeState) {
            return;
        }

        const messageTarget = activeState.querySelector('[data-chart-message]') || activeState;

        if (message) {
            messageTarget.textContent = message;
        }

        activeState.classList.remove('d-none');
    }

    async function getStatisticData(key, endpoint) {
        setPanelState(key, 'loading', 'Đang tải dữ liệu thống kê...');

        const response = await fetch(endpoint, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        let payload = null;

        try {
            payload = await response.json();
        } catch (error) {
            payload = null;
        }

        if (!response.ok) {
            throw new Error(payload?.message || 'Không thể tải dữ liệu thống kê từ máy chủ.');
        }

        if (!payload || payload.success !== true || !Array.isArray(payload.data)) {
            throw new Error(payload?.message || 'Dữ liệu thống kê trả về không hợp lệ.');
        }

        return payload.data;
    }

    function destroyChart(key) {
        if (charts[key]) {
            charts[key].destroy();
            delete charts[key];
        }
    }

    async function renderRevenueChart() {
        const rows = await getStatisticData('revenue', endpoints.revenue);

        if (!rows.length) {
            updateSummary('revenue', '0đ', 'Chưa có hóa đơn đã thanh toán');
            setPanelState('revenue', 'empty', 'Chưa có dữ liệu doanh thu để hiển thị biểu đồ.');
            return;
        }

        const items = [...rows]
            .reverse()
            .map((row) => ({
                label: formatShortDate(row.date),
                totalRevenue: Number(row.total_revenue) || 0,
            }));

        const totalRevenue = items.reduce((sum, item) => sum + item.totalRevenue, 0);

        updateSummary('revenue', formatMoney(totalRevenue), `${items.length} mốc doanh thu gần nhất`);
        destroyChart('revenue');

        charts.revenue = new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: items.map((item) => item.label),
                datasets: [{
                    label: 'Doanh thu',
                    data: items.map((item) => item.totalRevenue),
                    borderColor: 'rgba(13, 110, 253, 1)',
                    backgroundColor: 'rgba(13, 110, 253, 0.14)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgba(13, 110, 253, 1)',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                return `Doanh thu: ${formatMoney(context.parsed.y)}`;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback(value) {
                                return formatMoney(value);
                            },
                        },
                    },
                },
            },
        });

        setPanelState('revenue', 'ready');
    }

    async function renderPeakHoursChart() {
        const rows = await getStatisticData('peakHours', endpoints.peakHours);

        if (!rows.length) {
            updateSummary('peakHours', 'N/A', 'Chưa có lịch hẹn hợp lệ');
            setPanelState('peakHours', 'empty', 'Chưa có dữ liệu khung giờ để hiển thị biểu đồ.');
            return;
        }

        const items = rows.map((row) => ({
            label: formatTimeLabel(row.appointment_time),
            totalBookings: Number(row.total_bookings) || 0,
        }));

        const topSlot = items[0];

        updateSummary('peakHours', topSlot.label, `${topSlot.totalBookings} lịch hẹn trong khung giờ cao nhất`);
        destroyChart('peakHours');

        charts.peakHours = new Chart(document.getElementById('peakHoursChart'), {
            type: 'bar',
            data: {
                labels: items.map((item) => item.label),
                datasets: [{
                    label: 'Số lịch hẹn',
                    data: items.map((item) => item.totalBookings),
                    backgroundColor: [
                        'rgba(233, 69, 96, 0.86)',
                        'rgba(255, 118, 117, 0.86)',
                        'rgba(253, 203, 110, 0.86)',
                        'rgba(116, 185, 255, 0.86)',
                        'rgba(85, 239, 196, 0.86)',
                    ],
                    borderWidth: 0,
                    borderRadius: 12,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                        },
                    },
                    y: {
                        grid: {
                            display: false,
                        },
                    },
                },
            },
        });

        setPanelState('peakHours', 'ready');
    }

    async function renderServicesChart() {
        const rows = await getStatisticData('services', endpoints.services);

        if (!rows.length) {
            updateSummary('services', 'N/A', 'Chưa có dịch vụ phát sinh đặt lịch');
            setPanelState('services', 'empty', 'Chưa có dữ liệu dịch vụ để hiển thị biểu đồ.');
            return;
        }

        const items = rows.map((row) => ({
            label: row.service_name || 'N/A',
            totalBookings: Number(row.total_bookings) || 0,
            price: Number(row.price) || 0,
        }));

        const topService = items[0];

        updateSummary('services', topService.label, `${topService.totalBookings} lượt đặt - ${formatMoney(topService.price)}`);
        destroyChart('services');

        charts.services = new Chart(document.getElementById('servicesChart'), {
            type: 'pie',
            data: {
                labels: items.map((item) => item.label),
                datasets: [{
                    data: items.map((item) => item.totalBookings),
                    backgroundColor: [
                        'rgba(67, 233, 123, 0.88)',
                        'rgba(56, 249, 215, 0.88)',
                        'rgba(102, 126, 234, 0.88)',
                        'rgba(245, 87, 108, 0.88)',
                        'rgba(253, 203, 110, 0.88)',
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 18,
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                const item = items[context.dataIndex];

                                return `${item.label}: ${item.totalBookings} lượt đặt`;
                            },
                            afterLabel(context) {
                                const item = items[context.dataIndex];

                                return `Giá dịch vụ: ${formatMoney(item.price)}`;
                            },
                        },
                    },
                },
            },
        });

        setPanelState('services', 'ready');
    }

    Promise.allSettled([
        renderRevenueChart(),
        renderPeakHoursChart(),
        renderServicesChart(),
    ]).then((results) => {
        results.forEach((result) => {
            if (result.status === 'rejected') {
                console.error(result.reason);
            }
        });
    }).catch((error) => {
        console.error(error);
    });
});
</script>
@endpush
