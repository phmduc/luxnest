@extends('layouts.app')

@section('title', 'Giới thiệu - ' . $settings->site_name)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages.css') }}">
@endpush

@section('content')

<div class="pg-hero">
    <div class="lx-container">
        <h1>Về {{ $settings->site_name }}</h1>
        <p>Không gian nghỉ dưỡng đậm chất Đà Lạt – nơi sự tinh tế trong thiết kế gặp gỡ sự ấm áp trong dịch vụ.</p>
    </div>
</div>

<section class="pg-section pg-section--center">
    <div class="lx-container">
        <h2>Câu chuyện của chúng tôi</h2>
        <p>
            {{ $settings->site_name }} được xây dựng với mong muốn mang đến cho du khách những kỳ nghỉ trọn vẹn tại Đà Lạt –
            từ phòng khách sạn tiện nghi, villa riêng tư cho nhóm bạn và gia đình, đến trải nghiệm tham quan thành phố ngàn hoa
            qua dịch vụ thuê xe và tour trọn gói.
        </p>
        <p>
            Mỗi không gian tại {{ $settings->site_name }} đều được chăm chút về thiết kế và tiện nghi, kết hợp giữa nét hiện đại
            và hơi thở thiên nhiên đặc trưng của Đà Lạt, giúp khách hàng vừa được nghỉ ngơi thoải mái, vừa cảm nhận trọn vẹn
            không khí se lạnh, lãng mạn của thành phố sương mù.
        </p>
    </div>
</section>

<section class="pg-section pg-section--gray">
    <div class="lx-container">
        <h2 style="text-align:center;">Vì sao chọn {{ $settings->site_name }}</h2>
        <div class="pg-grid pg-grid--3">
            <div class="pg-card">
                <div class="pg-card__icon">🏨</div>
                <div class="pg-card__title">Đa dạng chỗ ở</div>
                <p class="pg-card__text">Từ phòng khách sạn, villa đến căn hộ – đáp ứng mọi nhu cầu của khách du lịch, gia đình và nhóm bạn.</p>
            </div>
            <div class="pg-card">
                <div class="pg-card__icon">🤝</div>
                <div class="pg-card__title">Dịch vụ tận tâm</div>
                <p class="pg-card__text">Đội ngũ hỗ trợ 24/7, sẵn sàng tư vấn và đồng hành cùng bạn trong suốt chuyến đi.</p>
            </div>
            <div class="pg-card">
                <div class="pg-card__icon">🚗</div>
                <div class="pg-card__title">Trải nghiệm trọn gói</div>
                <p class="pg-card__text">Kết hợp lưu trú với dịch vụ thuê xe, tour tham quan để hành trình của bạn thêm thuận tiện.</p>
            </div>
        </div>
    </div>
</section>

<section class="pg-section">
    <div class="lx-container">
        <div class="pg-grid">
            <div class="pg-stat">
                <div class="pg-stat__number">19+</div>
                <div class="pg-stat__label">Chỗ ở đa dạng</div>
            </div>
            <div class="pg-stat">
                <div class="pg-stat__number">1000+</div>
                <div class="pg-stat__label">Khách hàng đã phục vụ</div>
            </div>
            <div class="pg-stat">
                <div class="pg-stat__number">4.8/5</div>
                <div class="pg-stat__label">Đánh giá trung bình</div>
            </div>
            <div class="pg-stat">
                <div class="pg-stat__number">24/7</div>
                <div class="pg-stat__label">Hỗ trợ khách hàng</div>
            </div>
        </div>
    </div>
</section>

<section class="pg-section pg-section--gray pg-section--center">
    <div class="lx-container">
        <h2>Sẵn sàng cho chuyến đi tiếp theo?</h2>
        <p>Khám phá các chỗ ở của {{ $settings->site_name }} và đặt phòng ngay hôm nay.</p>
        <a href="{{ route('rooms.index') }}" class="pg-btn">Xem các chỗ ở</a>
    </div>
</section>

@endsection
