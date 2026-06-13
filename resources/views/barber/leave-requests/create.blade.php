<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tạo Đơn Xin Nghỉ - BarberShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --primary: #c8a97e; --primary-dark: #b08d5e; --dark: #1a1a1a; --dark2: #232323; --dark3: #2d2d2d; --text: #f0ece4; --text-muted: #8a8478; --sidebar-w: 260px; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:var(--dark); color:var(--text); min-height:100vh; }
        .sidebar { position:fixed; top:0; left:0; width:var(--sidebar-w); height:100vh; background:linear-gradient(180deg,#111 0%,#1a1a1a 100%); border-right:1px solid rgba(200,169,126,.1); z-index:100; display:flex; flex-direction:column; }
        .sidebar .brand { padding:24px 20px; text-align:center; border-bottom:1px solid rgba(200,169,126,.1); }
        .sidebar .brand i { color:var(--primary); font-size:1.6rem; }
        .sidebar .brand span { color:var(--primary); font-size:1.3rem; font-weight:700; margin-left:8px; letter-spacing:1px; }
        .sidebar .nav-link { color:var(--text-muted); padding:12px 24px; font-size:.9rem; font-weight:500; border-left:3px solid transparent; text-decoration:none; display:flex; align-items:center; gap:12px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color:var(--primary); background:rgba(200,169,126,.06); border-left-color:var(--primary); }
        .main-content { margin-left:var(--sidebar-w); padding:24px 28px; min-height:100vh; }
        .header { background:var(--dark2); border:1px solid rgba(200,169,126,.08); border-radius:14px; padding:24px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; }
        .header h4 { color:var(--primary); font-weight:700; margin:0; }
        .card { background:var(--dark2); border:1px solid rgba(200,169,126,.08); border-radius:14px; overflow:hidden; margin-bottom:24px; }
        .card-header { padding:16px 20px; border-bottom:1px solid rgba(200,169,126,.06); font-weight:600; color:var(--primary); }
        .card-body { padding:24px; }
        .form-label { color:var(--text); font-weight:500; font-size:.9rem; margin-bottom:6px; }
        .form-control, .form-select { background:var(--dark3); color:var(--text); border:1px solid rgba(200,169,126,.1); border-radius:8px; padding:10px 14px; font-size:.9rem; }
        .form-control:focus, .form-select:focus { background:var(--dark3); color:var(--text); border-color:var(--primary); }
        .btn-gold { background:var(--primary); color:#1a1a1a; border:none; font-weight:600; padding:10px 24px; border-radius:8px; }
        .btn-gold:hover { background:var(--primary-dark); color:#1a1a1a; }
        .btn-outline-gold { border:1px solid var(--primary); color:var(--primary); background:transparent; border-radius:8px; padding:10px 24px; }
        .leave-type-option { display:flex; align-items:center; gap:12px; padding:16px; background:var(--dark3); border:2px solid transparent; border-radius:10px; cursor:pointer; margin-bottom:8px; }
        .leave-type-option:hover { border-color:rgba(200,169,126,.3); }
        .leave-type-option.active { border-color:var(--primary); background:rgba(200,169,126,.08); }
        .leave-type-option input { display:none; }
        .leave-type-option .icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; background:rgba(200,169,126,.1); color:var(--primary); }
        .leave-type-option .title { font-weight:600; font-size:.95rem; }
        .leave-type-option .desc { font-size:.8rem; color:var(--text-muted); }
        .slot-checkbox { display:flex; align-items:center; gap:10px; padding:12px 16px; background:var(--dark3); border:1px solid rgba(200,169,126,.1); border-radius:8px; cursor:pointer; margin-bottom:6px; }
        .slot-checkbox:hover { border-color:rgba(200,169,126,.3); }
        .slot-checkbox.checked { border-color:var(--primary); background:rgba(200,169,126,.08); }
        .slot-checkbox input { display:none; }
        .slot-checkbox .slot-time { font-weight:600; font-size:.85rem; }
        .slot-checkbox .slot-label { font-size:.8rem; color:var(--text-muted); }
        .required { color:#f87171; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="brand"><i class="bi bi-scissors"></i><span>Barber Panel</span></div>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="{{ route('barber.dashboard') }}" class="nav-link"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a></li>
            <li class="nav-item"><a href="{{ route('barber.appointments') }}" class="nav-link"><i class="bi bi-calendar2-week"></i> Lịch hẹn</a></li>
            <li class="nav-item"><a href="{{ route('barber.leave_requests.index') }}" class="nav-link active"><i class="bi bi-file-earmark-text"></i> Đơn xin nghỉ</a></li>
            <li class="nav-item"><a href="{{ route('barber.profile') }}" class="nav-link"><i class="bi bi-person-circle"></i> Hồ sơ</a></li>
        </ul>
        <div class="mt-auto">
            <hr style="border-color:rgba(200,169,126,.1);margin:10px 20px;">
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent" onclick="return confirm('Đăng xuất?')"><i class="bi bi-box-arrow-right"></i> Đăng xuất</button>
            </form>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <h4><i class="bi bi-file-earmark-text me-2"></i>Tạo Đơn Xin Nghỉ Phép</h4>
            <a href="{{ route('barber.leave_requests.index') }}" class="btn btn-outline-gold"><i class="bi bi-arrow-left"></i> Quay lại</a>
        </div>

        @if ($errors->any())
        <div class="alert alert-danger" style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#f87171;border-radius:10px;">
            <ul class="mb-0 small">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
        @endif

        <form action="{{ route('barber.leave_requests.store') }}" method="POST" id="leaveForm">
            @csrf

            <!-- Bước 1: Loại nghỉ -->
            <div class="card">
                <div class="card-header"><i class="bi bi-question-circle me-2"></i>1. Loại nghỉ phép</div>
                <div class="card-body">
                    <div class="leave-type-option {{ old('leave_type', 'full_day') === 'full_day' ? 'active' : '' }}" onclick="selectType('full_day')">
                        <input type="radio" name="leave_type" value="full_day" id="typeFullDay" {{ old('leave_type', 'full_day') === 'full_day' ? 'checked' : '' }}>
                        <div class="icon"><i class="bi bi-calendar-range"></i></div>
                        <div><div class="title">Nghỉ cả ngày / Nhiều ngày</div><div class="desc">08:00 - 22:00, chọn nhiều ngày liên tiếp</div></div>
                    </div>
                    <div class="leave-type-option {{ old('leave_type') === 'shift' ? 'active' : '' }}" onclick="selectType('shift')">
                        <input type="radio" name="leave_type" value="shift" id="typeShift" {{ old('leave_type') === 'shift' ? 'checked' : '' }}>
                        <div class="icon"><i class="bi bi-clock"></i></div>
                        <div><div class="title">Nghỉ theo ca</div><div class="desc">Chọn 1 hoặc nhiều ca trong ngày</div></div>
                    </div>
                </div>
            </div>

            <!-- Bước 2: Ngày + Ca -->
            <div class="card">
                <div class="card-header"><i class="bi bi-calendar3 me-2"></i>2. Chọn ngày nghỉ</div>
                <div class="card-body">
                    <div id="fullDaySection">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày bắt đầu <span class="required">*</span></label>
                                <input type="date" name="start_date" id="fullDayStart" class="form-control" value="{{ old('start_date') }}" min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày kết thúc <span class="required">*</span></label>
                                <input type="date" name="end_date" id="fullDayEnd" class="form-control" value="{{ old('end_date') }}" min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="small text-muted">Giờ làm việc: <strong>08:00 - 22:00</strong></div>
                    </div>
                    <div id="shiftSection" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label">Chọn ngày <span class="required">*</span></label>
                            <input type="date" name="leave_date" id="shiftDate" class="form-control" value="{{ old('leave_date') }}" min="{{ date('Y-m-d') }}">
                        </div>
                        <label class="form-label">Chọn ca (có thể chọn nhiều) <span class="required">*</span></label>
                        <div class="slot-checkbox" onclick="toggleSlot(this)">
                            <input type="checkbox" name="slots[]" value="morning">
                            <div><div class="slot-time">08:00 - 13:00</div><div class="slot-label">Ca sáng</div></div>
                        </div>
                        <div class="slot-checkbox" onclick="toggleSlot(this)">
                            <input type="checkbox" name="slots[]" value="afternoon">
                            <div><div class="slot-time">13:00 - 18:00</div><div class="slot-label">Ca chiều</div></div>
                        </div>
                        <div class="slot-checkbox" onclick="toggleSlot(this)">
                            <input type="checkbox" name="slots[]" value="evening">
                            <div><div class="slot-time">18:00 - 22:00</div><div class="slot-label">Ca tối</div></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bước 3: Thông tin -->
            <div class="card">
                <div class="card-header"><i class="bi bi-info-circle me-2"></i>3. Thông tin bổ sung</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Họ tên <span class="required">*</span></label>
                            <input type="text" name="applicant_name" class="form-control" value="{{ old('applicant_name', Auth::user()->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày sinh <span class="required">*</span></label>
                            <input type="date" name="applicant_dob" class="form-control" value="{{ old('applicant_dob') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số điện thoại <span class="required">*</span></label>
                            <input type="text" name="applicant_phone" class="form-control" value="{{ old('applicant_phone', $barber->phone ?? '') }}" placeholder="0912345678" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Địa chỉ <span class="required">*</span></label>
                            <input type="text" name="applicant_address" class="form-control" value="{{ old('applicant_address') }}" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Lý do <span class="required">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required>{{ old('reason') }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Người bàn giao <span class="required">*</span></label>
                            <select name="handover_person" class="form-select" required>
                                <option value="">-- Chọn --</option>
                                @foreach($availableBarbers as $b)
                                <option value="{{ $b->name }}" {{ old('handover_person') == $b->name ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input type="hidden" name="commitment" value="0">
                        <input class="form-check-input" type="checkbox" id="commitment" name="commitment" value="1" {{ old('commitment') ? 'checked' : '' }} required>
                        <label class="form-check-label" for="commitment">Tôi cam kết đúng nội dung đã nêu <span class="required">*</span></label>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <a href="{{ route('barber.leave_requests.index') }}" class="btn btn-outline-gold me-2">Hủy</a>
                <button type="submit" class="btn btn-gold"><i class="bi bi-send me-1"></i>Gửi đơn</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gọi ngay khi trang load để disable đúng input
        document.addEventListener('DOMContentLoaded', function() {
            var checked = document.querySelector('input[name="leave_type"]:checked');
            if (checked) selectType(checked.value);
        });

        function selectType(type) {
            document.querySelectorAll('.leave-type-option').forEach(el => el.classList.remove('active'));
            document.querySelector(`.leave-type-option input[value="${type}"]`).closest('.leave-type-option').classList.add('active');
            document.querySelector(`input[name="leave_type"][value="${type}"]`).checked = true;
            
            var isFullDay = type === 'full_day';
            document.getElementById('fullDaySection').style.display = isFullDay ? 'block' : 'none';
            document.getElementById('shiftSection').style.display = isFullDay ? 'none' : 'block';
            
            // Disable/Enable to prevent empty values from being submitted
            document.getElementById('fullDayStart').disabled = !isFullDay;
            document.getElementById('fullDayEnd').disabled = !isFullDay;
        }

        function toggleSlot(el) {
            el.classList.toggle('checked');
            el.querySelector('input').checked = !el.querySelector('input').checked;
        }
    </script>
</body>
</html>