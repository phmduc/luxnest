<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đặt phòng</title>
    <style type="text/css">
        body { margin: 0; padding: 0; background-color: #f0f0f0; }
        #wrapper { background-color: #f0f0f0; padding: 30px 0; }
        #template_container { box-shadow: 0 1px 4px rgba(0,0,0,.1); }
        #template_header { background-color: #1a3a6b; border-radius: 3px 3px 0 0; }
        #template_header h1 { color: #ffffff; font-family: Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 700; margin: 0; padding: 28px 36px; }
        #template_header p.subtitle { color: #a8c4e8; font-size: 12px; margin: 0; padding: 0 36px 20px; letter-spacing: 1px; text-transform: uppercase; }
        #template_body { background-color: #ffffff; }
        #body_content { padding: 36px; font-family: Helvetica, Arial, sans-serif; font-size: 14px; color: #636363; }
        #body_content h2 { color: #1a3a6b; font-size: 18px; font-weight: 700; margin: 0 0 20px; border-bottom: 3px solid #1a3a6b; padding-bottom: 10px; }
        #body_content table.order-details { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        #body_content table.order-details th,
        #body_content table.order-details td { padding: 10px 12px; text-align: left; border: 1px solid #e4e4e4; font-size: 14px; }
        #body_content table.order-details thead th { background-color: #f8f8f8; color: #1a3a6b; font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
        #body_content table.order-details tfoot tr.total td { background-color: #fff7ed; font-size: 16px; font-weight: 700; color: #ea580c; border-top: 3px solid #e4e4e4; }
        .note-box { background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 14px 16px; border-radius: 0 6px 6px 0; margin-bottom: 24px; font-size: 13px; color: #92400e; line-height: 1.7; }
        #template_footer { background-color: #1a3a6b; border-radius: 0 0 3px 3px; }
        #template_footer p { color: #a8c4e8; font-size: 12px; text-align: center; padding: 20px; margin: 0; line-height: 1.8; font-family: Helvetica, Arial, sans-serif; }
        .btn { display: inline-block; background-color: #1a3a6b; color: #ffffff !important; text-decoration: none; font-size: 14px; font-weight: 700; padding: 12px 28px; border-radius: 5px; }
        @media only screen and (max-width: 620px) {
            #template_container { width: 100% !important; }
            #template_header h1 { font-size: 20px; padding: 20px 20px; }
            #body_content { padding: 20px; }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container">

                    {{-- Header --}}
                    <tr>
                        <td id="template_header">
                            <h1>🏨 LuxNest</h1>
                            <p class="subtitle">Premium Accommodation</p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td id="template_body">
                            <div id="body_content">

                                <p style="font-size:15px;color:#1a3a6b;font-weight:700;">Xin chào {{ $order->customer_name }},</p>
                                <p style="line-height:1.7;">Chúng tôi đã nhận được yêu cầu đặt phòng của Anh/Chị và đang xử lý.
                                Vui lòng giữ lại email này để làm bằng chứng đặt phòng.</p>

                                {{-- Order badge --}}
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:24px;">
                                    <tr>
                                        <td align="center">
                                            <div style="display:inline-block;background:#fff7ed;border:2px dashed #fb923c;border-radius:8px;padding:10px 24px;text-align:center;">
                                                <p style="margin:0;color:#9a3412;font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:600;">Mã đặt phòng</p>
                                                <p style="margin:4px 0 0;color:#ea580c;font-size:22px;font-weight:800;">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                </table>

                                {{-- Order details table --}}
                                <h2>Chi tiết đặt phòng</h2>
                                <table class="order-details" cellspacing="0" cellpadding="0" border="1">
                                    <thead>
                                        <tr>
                                            <th>Thông tin</th>
                                            <th style="text-align:right;">Chi tiết</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($room)
                                        <tr>
                                            <td>Tên phòng</td>
                                            <td style="text-align:right;font-weight:700;color:#0f172a;">{{ $room->name }}</td>
                                        </tr>
                                        @if($room->branch)
                                        <tr>
                                            <td>Khu vực</td>
                                            <td style="text-align:right;color:#0f172a;">{{ $room->branch }}</td>
                                        </tr>
                                        @endif
                                        @if($room->type)
                                        <tr>
                                            <td>Loại phòng</td>
                                            <td style="text-align:right;color:#0f172a;">{{ $room->type }}</td>
                                        </tr>
                                        @endif
                                        @endif
                                        <tr>
                                            <td>Ngày nhận phòng</td>
                                            <td style="text-align:right;color:#0f172a;">
                                                <strong>{{ \Carbon\Carbon::parse($order->checkin_date)->format('d/m/Y') }}</strong>
                                                <span style="color:#64748b;">&nbsp;(từ 14:00)</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Ngày trả phòng</td>
                                            <td style="text-align:right;color:#0f172a;">
                                                <strong>{{ \Carbon\Carbon::parse($order->checkout_date)->format('d/m/Y') }}</strong>
                                                <span style="color:#64748b;">&nbsp;(trước 12:00)</span>
                                            </td>
                                        </tr>
                                        @php
                                            $nights = \Carbon\Carbon::parse($order->checkin_date)->diffInDays(\Carbon\Carbon::parse($order->checkout_date));
                                        @endphp
                                        <tr>
                                            <td>Số đêm</td>
                                            <td style="text-align:right;color:#0f172a;font-weight:600;">{{ $nights }} đêm</td>
                                        </tr>
                                        <tr>
                                            <td>Số khách</td>
                                            <td style="text-align:right;color:#0f172a;">
                                                @php
                                                    preg_match('/Khách: (\d+)/', $order->note ?? '', $m);
                                                    echo ($m[1] ?? 1) . ' người';
                                                @endphp
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="total">
                                            <td><strong>Tổng thanh toán</strong></td>
                                            <td style="text-align:right;"><strong>{{ number_format($order->total_amount, 0, ',', '.') }}₫</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>

                                {{-- Customer info --}}
                                <h2>Thông tin khách hàng</h2>
                                <table class="order-details" cellspacing="0" cellpadding="0" border="1">
                                    <tbody>
                                        <tr>
                                            <td style="width:40%;">Họ và tên</td>
                                            <td style="text-align:right;color:#0f172a;font-weight:600;">{{ $order->customer_name }}</td>
                                        </tr>
                                        <tr>
                                            <td>Email</td>
                                            <td style="text-align:right;color:#0f172a;">{{ $order->customer_email }}</td>
                                        </tr>
                                        <tr>
                                            <td>Điện thoại</td>
                                            <td style="text-align:right;color:#0f172a;">{{ $order->customer_phone }}</td>
                                        </tr>
                                        @if($order->note && str_contains($order->note, 'Yêu cầu đặc biệt:'))
                                        @php
                                            preg_match('/Yêu cầu đặc biệt: (.+)/u', $order->note, $mn);
                                            $specialReq = $mn[1] ?? '';
                                        @endphp
                                        @if($specialReq && $specialReq !== 'Không có')
                                        <tr>
                                            <td>Yêu cầu đặc biệt</td>
                                            <td style="text-align:right;color:#0f172a;">{{ $specialReq }}</td>
                                        </tr>
                                        @endif
                                        @endif
                                    </tbody>
                                </table>

                                {{-- Note --}}
                                <div class="note-box">
                                    <strong>💳 Thanh toán:</strong> Thanh toán tại khách sạn khi nhận phòng.<br>
                                    <strong>📋 Lưu ý:</strong> Vui lòng mang theo CMND/CCCD khi làm thủ tục nhận phòng.<br>
                                    <strong>📞 Hỗ trợ:</strong> Hotline <a href="tel:+84123456789" style="color:#92400e;font-weight:700;">0123 456 789</a>
                                </div>

                                {{-- QR Code Check-in --}}
                                @if($qrImageUrl)
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
                                    <tr>
                                        <td align="center">
                                            <div style="border:2px dashed #FF5B00; border-radius:12px; padding:24px 20px; background:#fffbf7; text-align:center;">
                                                <p style="margin:0 0 6px; font-size:15px; font-weight:700; color:#1a1a1a;">Mã QR Check-in của bạn</p>
                                                <p style="margin:0 0 16px; font-size:13px; color:#64748b; line-height:1.6;">
                                                    Vui lòng xuất trình mã QR này cho lễ tân khi làm thủ tục nhận phòng
                                                </p>
                                                <img src="{{ $qrImageUrl }}" alt="QR Code Check-in"
                                                     width="200" height="200"
                                                     style="display:block; margin:0 auto; border-radius:8px; border:4px solid #fff; box-shadow:0 2px 12px rgba(0,0,0,0.08);">
                                                <p style="margin:14px 0 0; font-size:12px; color:#94a3b8; line-height:1.6;">
                                                    Nhân viên lễ tân sẽ quét mã này để xác nhận thông tin của bạn.<br>
                                                    Mã QR chỉ sử dụng được một lần và chỉ hợp lệ khi check-in.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                @endif

                                {{-- CTA --}}
                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td align="center">
                                            <a href="{{ url('/') }}" class="btn">Về trang chủ LuxNest</a>
                                        </td>
                                    </tr>
                                </table>

                                <p style="margin-top:24px;font-size:13px;color:#94a3b8;text-align:center;line-height:1.7;">
                                    Email này được gửi tự động từ hệ thống LuxNest.<br>
                                    Vui lòng không trả lời trực tiếp email này.
                                </p>

                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td id="template_footer">
                            <p>© {{ date('Y') }} LuxNest — Premium Accommodation<br>
                            📧 {{ config('mail.from.address') }} &nbsp;|&nbsp; 📞 0123 456 789</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
