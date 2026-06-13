<section id="services-section" class="card h-100 summary-card">
    <div class="card-header chart-card-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-pie-chart text-success"></i>
                <span>Dịch vụ hot</span>
            </div>
            <p>Pie Chart hiển thị top 5 dịch vụ được đặt nhiều nhất để admin dễ theo dõi xu hướng.</p>
        </div>
        <span class="badge text-bg-success">Hot Services</span>
    </div>
    <div class="card-body">
        <div class="chart-panel chart-panel-compact" data-chart-panel="services">
            <div class="chart-placeholder" data-chart-state="loading">
                <div class="spinner-border text-success" role="status" aria-hidden="true"></div>
                <p data-chart-message>Đang tải dữ liệu thống kê...</p>
            </div>
            <div class="chart-placeholder d-none text-danger" data-chart-state="error">
                <i class="bi bi-exclamation-triangle"></i>
                <p data-chart-message>Không thể tải dữ liệu dịch vụ hot.</p>
            </div>
            <div class="chart-placeholder d-none text-muted" data-chart-state="empty">
                <i class="bi bi-inbox"></i>
                <p data-chart-message>Chưa có dữ liệu dịch vụ hot để hiển thị.</p>
            </div>
            <canvas id="servicesChart" class="chart-canvas d-none"></canvas>
        </div>
    </div>
</section>
