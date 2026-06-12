@extends('layouts.app')

@section('title', 'Hợp tác - ' . $settings->site_name)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages.css') }}">
@endpush

@section('content')

<div class="pg-hero">
    <div class="lx-container">
        <h1>Hợp tác cùng {{ $settings->site_name }}</h1>
        <p>Bạn đang sở hữu khách sạn, villa, dịch vụ thuê xe hoặc tour du lịch tại Đà Lạt? Cùng đồng hành với {{ $settings->site_name }} để tiếp cận thêm nhiều khách hàng.</p>
    </div>
</div>

<section class="pg-section pg-section--center">
    <div class="lx-container">
        <h2>Hình thức hợp tác</h2>
        <div class="pg-grid pg-grid--3">
            <div class="pg-card">
                <div class="pg-card__icon">🏠</div>
                <div class="pg-card__title">Chủ chỗ ở</div>
                <p class="pg-card__text">Đưa khách sạn, villa, homestay của bạn lên hệ thống LuxNest để tiếp cận lượng lớn khách du lịch.</p>
            </div>
            <div class="pg-card">
                <div class="pg-card__icon">🚐</div>
                <div class="pg-card__title">Đối tác vận chuyển</div>
                <p class="pg-card__text">Cung cấp dịch vụ thuê xe, đưa đón sân bay cho khách hàng đặt phòng qua LuxNest.</p>
            </div>
            <div class="pg-card">
                <div class="pg-card__icon">🗺️</div>
                <div class="pg-card__title">Đối tác tour & trải nghiệm</div>
                <p class="pg-card__text">Giới thiệu các tour tham quan, hoạt động trải nghiệm tại Đà Lạt đến khách hàng của LuxNest.</p>
            </div>
        </div>
    </div>
</section>

<section class="pg-section pg-section--gray">
    <div class="lx-container">
        <h2 style="text-align:center;">Quyền lợi khi hợp tác</h2>
        <div class="pg-grid">
            <div class="pg-card">
                <div class="pg-card__icon">📈</div>
                <div class="pg-card__title">Tăng lượng khách</div>
                <p class="pg-card__text">Tiếp cận tệp khách hàng đang tìm chỗ ở, thuê xe và tour tại Đà Lạt thông qua LuxNest.</p>
            </div>
            <div class="pg-card">
                <div class="pg-card__icon">⚙️</div>
                <div class="pg-card__title">Vận hành đơn giản</div>
                <p class="pg-card__text">Quản lý đặt phòng, đơn hàng tập trung, không cần đầu tư hệ thống riêng.</p>
            </div>
            <div class="pg-card">
                <div class="pg-card__icon">🤝</div>
                <div class="pg-card__title">Hỗ trợ tận tâm</div>
                <p class="pg-card__text">Đội ngũ LuxNest đồng hành, hỗ trợ đối tác trong suốt quá trình hợp tác.</p>
            </div>
            <div class="pg-card">
                <div class="pg-card__icon">💰</div>
                <div class="pg-card__title">Chính sách minh bạch</div>
                <p class="pg-card__text">Tỷ lệ hoa hồng và chính sách thanh toán rõ ràng, công bằng cho đối tác.</p>
            </div>
        </div>
    </div>
</section>

<section class="pg-section">
    <div class="lx-container" style="max-width: 760px;">
        <div class="pg-form-wrap">
            <h3>📋 Đăng ký hợp tác</h3>

            <form class="pg-form" id="partner-form">
                @csrf
                <div class="pg-field">
                    <label>Tên đơn vị / cá nhân</label>
                    <input type="text" name="partner_name" placeholder="VD: Villa Sunrise Đà Lạt" required>
                </div>
                <div class="pg-field">
                    <label>Loại hợp tác</label>
                    <select name="partner_type">
                        <option value="">-- Chọn hình thức --</option>
                        <option value="cho-o">Chủ chỗ ở (khách sạn / villa / homestay)</option>
                        <option value="van-chuyen">Đối tác vận chuyển / thuê xe</option>
                        <option value="tour">Đối tác tour & trải nghiệm</option>
                        <option value="khac">Khác</option>
                    </select>
                </div>
                <div class="pg-field">
                    <label>Số điện thoại</label>
                    <input type="tel" name="partner_phone" placeholder="0901 234 567" required>
                </div>
                <div class="pg-field">
                    <label>Email</label>
                    <input type="email" name="partner_email" placeholder="email@example.com" required>
                </div>
                <div class="pg-field">
                    <label>Thông tin thêm</label>
                    <textarea name="partner_note" rows="4" placeholder="Giới thiệu ngắn về dịch vụ / chỗ ở bạn muốn hợp tác..."></textarea>
                </div>

                <button type="submit" class="pg-btn" id="partner-submit-btn">Gửi đăng ký</button>
            </form>

            <div class="pg-success" id="partner-success" style="display:none;">
                <span>✅</span>
                <div>
                    <strong>Đã gửi đăng ký hợp tác!</strong>
                    <p>Đội ngũ {{ $settings->site_name }} sẽ liên hệ lại với bạn trong thời gian sớm nhất.</p>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form    = document.getElementById('partner-form');
    const success = document.getElementById('partner-success');
    const btn     = document.getElementById('partner-submit-btn');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        btn.disabled    = true;
        btn.textContent = 'Đang gửi...';
        setTimeout(function () {
            form.style.display    = 'none';
            success.style.display = 'flex';
        }, 800);
    });
});
</script>
@endpush
