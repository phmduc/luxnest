<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quà tặng từ LuxNest</title>
    <style type="text/css">
        body { margin: 0; padding: 0; background-color: #f0f0f0; }
        #wrapper { background-color: #f0f0f0; padding: 30px 0; }
        #template_container { box-shadow: 0 1px 4px rgba(0,0,0,.1); }
        #template_header { background-color: #1a1a1a; border-radius: 3px 3px 0 0; }
        #template_header h1 { color: #ffffff; font-family: Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 700; margin: 0; padding: 28px 36px; }
        #template_header p.subtitle { color: #c9a17e; font-size: 12px; margin: 0; padding: 0 36px 20px; letter-spacing: 1px; text-transform: uppercase; }
        #template_body { background-color: #ffffff; }
        #body_content { padding: 36px; font-family: Helvetica, Arial, sans-serif; font-size: 14px; color: #636363; }
        #template_footer { background-color: #1a1a1a; border-radius: 0 0 3px 3px; }
        #template_footer p { color: #888; font-size: 12px; text-align: center; padding: 20px; margin: 0; line-height: 1.8; font-family: Helvetica, Arial, sans-serif; }
        .btn { display: inline-block; background-color: #996d4e; color: #ffffff !important; text-decoration: none; font-size: 15px; font-weight: 700; padding: 14px 32px; border-radius: 6px; }
        .voucher-box { background: linear-gradient(135deg, #996d4e, #c9a17e); border-radius: 12px; padding: 30px; text-align: center; margin: 28px 0; }
        .voucher-box p.label { color: rgba(255,255,255,0.85); font-size: 12px; letter-spacing: 2px; text-transform: uppercase; margin: 0 0 10px; font-family: Helvetica, Arial, sans-serif; }
        .voucher-box p.code { color: #ffffff; font-size: 32px; font-weight: 700; letter-spacing: 6px; margin: 0 0 10px; font-family: 'Courier New', monospace; }
        .voucher-box p.discount { color: rgba(255,255,255,0.9); font-size: 14px; margin: 0; font-family: Helvetica, Arial, sans-serif; }
        .divider { border: none; border-top: 1px solid #f0f0f0; margin: 24px 0; }
        @media only screen and (max-width: 620px) {
            #template_container { width: 100% !important; }
            #template_header h1 { font-size: 20px; padding: 20px; }
            #body_content { padding: 20px; }
            .voucher-box p.code { font-size: 24px; letter-spacing: 3px; }
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
                            <p class="subtitle">Premium Accommodation · Đà Lạt</p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td id="template_body">
                            <div id="body_content">

                                <p style="font-size:16px;color:#1a1a1a;font-weight:700;margin:0 0 12px;">
                                    Xin chào {{ $order->customer_name }} 👋
                                </p>

                                <p style="line-height:1.8;margin:0 0 20px;">
                                    LuxNest rất vui được đón tiếp bạn vào kỳ nghỉ vừa rồi.
                                    Chúng tôi hy vọng bạn đã có những khoảnh khắc thật tuyệt vời tại Đà Lạt!
                                </p>

                                <p style="line-height:1.8;margin:0 0 8px;">
                                    Để cảm ơn bạn đã tin tưởng lựa chọn LuxNest, chúng tôi gửi tặng bạn
                                    <strong style="color:#996d4e;">voucher giảm {{ $discountPercent }}%</strong>
                                    cho lần đặt phòng tiếp theo:
                                </p>

                                {{-- Voucher --}}
                                <div class="voucher-box">
                                    <p class="label">Mã voucher của bạn</p>
                                    <p class="code">{{ $voucherCode }}</p>
                                    <p class="discount">Giảm {{ $discountPercent }}% tổng giá trị đặt phòng</p>
                                </div>

                                <p style="line-height:1.8;font-size:13px;color:#888;margin:0 0 20px;">
                                    ⏰ Voucher có hiệu lực trong <strong>30 ngày</strong> kể từ ngày nhận email này.<br>
                                    📌 Nhập mã khi thanh toán trên website hoặc liên hệ trực tiếp với chúng tôi.
                                </p>

                                <hr class="divider">

                                <p style="text-align:center;margin:24px 0;">
                                    <a href="{{ url('/rooms') }}" class="btn">Đặt phòng ngay →</a>
                                </p>

                                <hr class="divider">

                                <p style="line-height:1.8;font-size:13px;color:#aaa;text-align:center;margin:0;">
                                    Nếu bạn không muốn nhận email từ chúng tôi, vui lòng bỏ qua email này.<br>
                                    Hotline: <a href="tel:+84000000000" style="color:#996d4e;">0900 000 000</a>
                                </p>

                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td id="template_footer">
                            <p>
                                © {{ date('Y') }} LuxNest Homestay · Đà Lạt, Việt Nam<br>
                                <a href="{{ url('/') }}" style="color:#c9a17e;">luxnest.vn</a>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
