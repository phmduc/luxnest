@extends('layouts.app')

@section('title', 'Hợp tác - ' . $settings->site_name)

@push('styles')
<link rel="stylesheet" href="{{ asset_v('assets/css/pages.css') }}">
@endpush

@section('content')

<div class="pg-hero">
    <div class="lx-container">
        <h1>{{ $content['hero_title'] }}</h1>
        <p>{{ $content['hero_subtitle'] }}</p>
    </div>
</div>

<section class="pg-section pg-section--center">
    <div class="lx-container">
        <h2>{{ $content['types_title'] }}</h2>
        <div class="pg-grid pg-grid--3">
            @for ($i = 1; $i <= 3; $i++)
            <div class="pg-card">
                <div class="pg-card__icon">{{ $content["type_{$i}_icon"] }}</div>
                <div class="pg-card__title">{{ $content["type_{$i}_title"] }}</div>
                <p class="pg-card__text">{{ $content["type_{$i}_text"] }}</p>
            </div>
            @endfor
        </div>
    </div>
</section>

<section class="pg-section pg-section--gray">
    <div class="lx-container">
        <h2 style="text-align:center;">{{ $content['benefits_title'] }}</h2>
        <div class="pg-grid">
            @for ($i = 1; $i <= 4; $i++)
            <div class="pg-card">
                <div class="pg-card__icon">{{ $content["benefit_{$i}_icon"] }}</div>
                <div class="pg-card__title">{{ $content["benefit_{$i}_title"] }}</div>
                <p class="pg-card__text">{{ $content["benefit_{$i}_text"] }}</p>
            </div>
            @endfor
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

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        btn.disabled    = true;
        btn.textContent = 'Đang gửi...';
        const body = new URLSearchParams(new FormData(form));
        try {
            const res = await fetch('{{ route("partner.submit") }}', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body,
            });
            const json = await res.json();
            if (json.success) {
                form.style.display    = 'none';
                success.style.display = 'flex';
            } else {
                alert(json.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                btn.disabled    = false;
                btn.textContent = 'Gửi đăng ký';
            }
        } catch {
            alert('Lỗi kết nối, vui lòng thử lại.');
            btn.disabled    = false;
            btn.textContent = 'Gửi đăng ký';
        }
    });
});
</script>
@endpush
