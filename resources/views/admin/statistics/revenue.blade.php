<section id="revenue-section" class="card h-100 summary-card">
    <div class="card-header chart-card-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-graph-up-arrow text-primary"></i>
                <span>Biểu đồ doanh thu</span>
            </div>
            <p>Line Chart thể hiện doanh thu từ các hóa đơn đã thanh toán ở 7 mốc gần nhất.</p>
        </div>
        <span class="badge text-bg-primary">Revenue</span>
    </div>
    <div class="card-body">
        <div class="chart-panel" data-chart-panel="revenue">
            <div class="chart-placeholder" data-chart-state="loading">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                <p data-chart-message>Đang tải dữ liệu thống kê...</p>
            </div>
            <div class="chart-placeholder d-none text-danger" data-chart-state="error">
                <i class="bi bi-exclamation-triangle"></i>
                <p data-chart-message>Không thể tải dữ liệu doanh thu.</p>
            </div>
            <div class="chart-placeholder d-none text-muted" data-chart-state="empty">
                <i class="bi bi-inbox"></i>
                <p data-chart-message>Chưa có dữ liệu doanh thu để hiển thị.</p>
            </div>
            <canvas id="revenueChart" class="chart-canvas d-none"></canvas>
        </div>
    </div>
</section>
