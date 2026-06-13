<section id="peak-hours-section" class="card h-100 summary-card">
    <div class="card-header chart-card-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-danger"></i>
                <span>Giờ cao điểm</span>
            </div>
            <p>Bar Chart xếp hạng 5 khung giờ có số lịch hẹn nhiều nhất, bỏ qua các lịch đã hủy.</p>
        </div>
        <span class="badge text-bg-danger">Peak Hours</span>
    </div>
    <div class="card-body">
        <div class="chart-panel chart-panel-compact" data-chart-panel="peakHours">
            <div class="chart-placeholder" data-chart-state="loading">
                <div class="spinner-border text-danger" role="status" aria-hidden="true"></div>
                <p data-chart-message>Đang tải dữ liệu thống kê...</p>
            </div>
            <div class="chart-placeholder d-none text-danger" data-chart-state="error">
                <i class="bi bi-exclamation-triangle"></i>
                <p data-chart-message>Không thể tải dữ liệu giờ cao điểm.</p>
            </div>
            <div class="chart-placeholder d-none text-muted" data-chart-state="empty">
                <i class="bi bi-inbox"></i>
                <p data-chart-message>Chưa có dữ liệu giờ cao điểm để hiển thị.</p>
            </div>
            <canvas id="peakHoursChart" class="chart-canvas d-none"></canvas>
        </div>
    </div>
</section>
