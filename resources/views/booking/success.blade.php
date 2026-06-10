@extends('layouts.app')
@section('title', 'Đặt phòng thành công - LuxNest')

@section('content')
<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:60px 20px;background:#f5f6fa;">
    <div style="background:#fff;border-radius:20px;padding:48px 40px;max-width:520px;width:100%;text-align:center;box-shadow:0 4px 24px rgba(0,0,0,.07);">

        <div style="width:80px;height:80px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:2.2rem;">
            ✅
        </div>

        <h1 style="font-size:1.6rem;font-weight:800;color:#0f172a;margin-bottom:10px;">Đặt phòng thành công!</h1>
        <p style="color:#64748b;font-size:.95rem;line-height:1.7;margin-bottom:28px;">
            Cảm ơn Anh/Chị đã đặt phòng tại LuxNest. Chúng tôi sẽ liên hệ xác nhận qua email hoặc số điện thoại trong vòng <strong>15 phút</strong>.
        </p>

        @if($order)
        <div style="background:#f8fafc;border-radius:12px;padding:20px;text-align:left;margin-bottom:28px;font-size:.9rem;">
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #e2e8f0;">
                <span style="color:#64748b;">Mã đặt phòng</span>
                <strong>#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</strong>
            </div>
            @if($room)
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #e2e8f0;">
                <span style="color:#64748b;">Phòng</span>
                <strong>{{ $room->name }}</strong>
            </div>
            @endif
            @if($order->checkin_date)
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #e2e8f0;">
                <span style="color:#64748b;">Nhận phòng</span>
                <strong>{{ \Carbon\Carbon::parse($order->checkin_date)->format('d/m/Y') }}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #e2e8f0;">
                <span style="color:#64748b;">Trả phòng</span>
                <strong>{{ \Carbon\Carbon::parse($order->checkout_date)->format('d/m/Y') }}</strong>
            </div>
            @endif
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #e2e8f0;">
                <span style="color:#64748b;">Tên khách</span>
                <strong>{{ $order->customer_name }}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0 0;">
                <span style="color:#64748b;">Tổng tiền</span>
                <strong style="color:#ff5b00;font-size:1.05rem;">{{ number_format($order->total_amount, 0, ',', '.') }}₫</strong>
            </div>
        </div>
        @endif

        <div style="display:flex;flex-direction:column;gap:10px;">
            <a href="{{ url('/') }}"
               style="display:block;padding:14px;background:#1a3a6b;color:#fff;border-radius:10px;font-weight:700;font-size:.95rem;text-decoration:none;">
                Về trang chủ
            </a>
            <a href="{{ route('rooms.index') }}"
               style="display:block;padding:14px;background:#f1f5f9;color:#1a3a6b;border-radius:10px;font-weight:600;font-size:.9rem;text-decoration:none;">
                Xem thêm phòng khác
            </a>
        </div>

        <p style="margin-top:20px;font-size:.78rem;color:#94a3b8;">
            <i class="fa-solid fa-headset"></i> Hotline hỗ trợ: <a href="tel:+84123456789" style="color:#1a3a6b;font-weight:600;">0123 456 789</a>
        </p>
    </div>
</div>
@endsection
