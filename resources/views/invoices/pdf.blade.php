<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Hóa đơn #{{ $invoice->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #c8a97e;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #c8a97e;
            font-size: 24px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 12px;
            color: #666;
        }
        .invoice-details {
            width: 100%;
            margin-bottom: 30px;
        }
        .invoice-details td {
            vertical-align: top;
        }
        .info-box {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }
        .info-box h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 16px;
            color: #444;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table.items th {
            background-color: #c8a97e;
            color: #fff;
            padding: 10px;
            text-align: left;
        }
        table.items td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .total-row td {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #c8a97e;
        }
        .total-amount {
            color: #c8a97e;
            font-size: 18px;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 13px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            color: #fff;
        }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        .badge-info { background-color: #17a2b8; }
        .badge-secondary { background-color: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Gentlemen Barber Shop</h1>
        <p>123 Đường Cắt Tóc, Quận 1, TP. HCM | Hotline: 0365362495</p>
    </div>

    <table class="invoice-details">
        <tr>
            <td style="width: 50%; padding-right: 10px;">
                <div class="info-box">
                    <h3>Khách hàng</h3>
                    <strong>Tên:</strong> {{ $invoice->user->name ?? 'Khách lẻ' }}<br>
                    <strong>Email:</strong> {{ $invoice->user->email ?? 'Không có' }}<br>
                    <strong>SĐT:</strong> {{ $invoice->user->phone ?? 'Không có' }}
                </div>
            </td>
            <td style="width: 50%; padding-left: 10px;">
                <div class="info-box">
                    <h3>Thông tin hóa đơn</h3>
                    <strong>Mã HĐ:</strong> #{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}<br>
                    <strong>Ngày lập:</strong> {{ $invoice->created_at->format('d/m/Y H:i') }}<br>
                    <strong>Thợ cắt:</strong> {{ $invoice->appointment->barber->name ?? 'N/A' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Dịch vụ</th>
                <th class="text-center">Thời gian</th>
                <th class="text-right">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->appointment->service->name ?? 'Dịch vụ Barber' }}</td>
                <td class="text-center">{{ $invoice->appointment->service->duration_minutes ?? 0 }} phút</td>
                <td class="text-right">{{ number_format($invoice->appointment->service->price ?? $invoice->total_amount, 0, ',', '.') }}đ</td>
            </tr>
            @if($invoice->appointment->discount_amount > 0)
            <tr>
                <td colspan="2" class="text-right">Giảm giá (Mã: {{ $invoice->appointment->promo_code }}):</td>
                <td class="text-right" style="color: #dc3545;">-{{ number_format($invoice->appointment->discount_amount, 0, ',', '.') }}đ</td>
            </tr>
            @endif
            <tr class="total-row">
                <td colspan="2" class="text-right">TỔNG CỘNG:</td>
                <td class="text-right total-amount">{{ number_format($invoice->total_amount, 0, ',', '.') }}đ</td>
            </tr>
        </tbody>
    </table>

    <table class="invoice-details" style="margin-top: 20px;">
        <tr>
            <td style="width: 50%;">
                <strong>Phương thức thanh toán:</strong> 
                @if($invoice->payment_method === 'vnpay')
                    <span class="badge badge-info">VNPAY</span>
                @else
                    <span class="badge badge-secondary">Tiền mặt</span>
                @endif
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>Trạng thái:</strong> 
                @if($invoice->payment_status === 'paid')
                    <span class="badge badge-success">Đã thanh toán</span>
                @else
                    <span class="badge badge-danger">Chưa thanh toán</span>
                @endif
            </td>
        </tr>
    </table>

    <div class="footer">
        Cảm ơn quý khách đã sử dụng dịch vụ tại Gentlemen Barber Shop!<br>
        Hẹn gặp lại quý khách lần sau.
    </div>
</body>
</html>
