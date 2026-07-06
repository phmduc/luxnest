@extends('layouts.app')

@section('title', 'Liên hệ - ' . $settings->site_name)

@push('styles')
<link rel="stylesheet" href="{{ asset_v('assets/css/pages.css') }}">
@endpush

@section('content')

<div class="pg-hero">
    <div class="lx-container">
        <h1>Liên hệ với chúng tôi</h1>
        <p>{{ $settings->site_name }} luôn sẵn sàng hỗ trợ bạn. Hãy gửi thông tin hoặc liên hệ trực tiếp qua các kênh dưới đây.</p>
    </div>
</div>

<section class="pg-section">
    <div class="lx-container">
        <div class="pg-contact-grid">

            {{-- Contact info --}}
            <div class="pg-contact-info">
                @if($settings->hotline)
                <div class="pg-contact-row">
                    <i class="fa-solid fa-phone"></i>
                    <div>
                        <strong>Hotline</strong>
                        <a href="tel:{{ preg_replace('/\s+/', '', $settings->hotline) }}">{{ $settings->hotline }}</a>
                    </div>
                </div>
                @endif

                @if($settings->email)
                <div class="pg-contact-row">
                    <i class="fa-solid fa-envelope"></i>
                    <div>
                        <strong>Email</strong>
                        <a href="mailto:{{ $settings->email }}">{{ $settings->email }}</a>
                    </div>
                </div>
                @endif

                @if($settings->address)
                <div class="pg-contact-row">
                    <i class="fa-solid fa-location-dot"></i>
                    <div>
                        <strong>Địa chỉ</strong>
                        @if($settings->map_link)
                            <a href="{{ $settings->map_link }}" target="_blank" rel="noopener">{{ $settings->address }}</a>
                        @else
                            <span>{{ $settings->address }}</span>
                        @endif
                    </div>
                </div>
                @endif

                <div class="pg-contact-row">
                    <i class="fa-solid fa-clock"></i>
                    <div>
                        <strong>Thời gian hỗ trợ</strong>
                        <span>24/7 - Tất cả các ngày trong tuần</span>
                    </div>
                </div>

                @if($settings->facebook_url || $settings->instagram_url || $settings->youtube_url)
                <div class="pg-contact-row">
                    <i class="fa-solid fa-share-nodes"></i>
                    <div>
                        <strong>Mạng xã hội</strong>
                        <span>
                            @if($settings->facebook_url)<a href="{{ $settings->facebook_url }}" target="_blank" rel="noopener">Facebook</a>@endif
                            @if($settings->instagram_url) &nbsp;·&nbsp; <a href="{{ $settings->instagram_url }}" target="_blank" rel="noopener">Instagram</a>@endif
                            @if($settings->youtube_url) &nbsp;·&nbsp; <a href="{{ $settings->youtube_url }}" target="_blank" rel="noopener">YouTube</a>@endif
                        </span>
                    </div>
                </div>
                @endif
            </div>

            {{-- Contact form --}}
            <div class="pg-form-wrap">
                <h3>📝 Gửi yêu cầu liên hệ</h3>

                <form class="pg-form" id="contact-form">
                    @csrf
                    <div class="pg-field">
                        <label>Họ và tên</label>
                        <input type="text" name="contact_name" placeholder="Nguyễn Văn A" required>
                    </div>
                    <div class="pg-field">
                        <label>Email</label>
                        <input type="email" name="contact_email" placeholder="email@example.com" required>
                    </div>
                    <div class="pg-field">
                        <label>Số điện thoại</label>
                        <input type="tel" name="contact_phone" placeholder="0901 234 567" required>
                    </div>
                    <div class="pg-field">
                        <label>Nội dung</label>
                        <textarea name="contact_message" rows="4" placeholder="Bạn cần hỗ trợ về vấn đề gì?" required></textarea>
                    </div>

                    <button type="submit" class="pg-btn" id="contact-submit-btn">Gửi liên hệ</button>
                </form>

                <div class="pg-success" id="contact-success" style="display:none;">
                    <span>✅</span>
                    <div>
                        <strong>Đã gửi yêu cầu liên hệ!</strong>
                        <p>{{ $settings->site_name }} sẽ phản hồi bạn trong thời gian sớm nhất.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form    = document.getElementById('contact-form');
    const success = document.getElementById('contact-success');
    const btn     = document.getElementById('contact-submit-btn');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        btn.disabled    = true;
        btn.textContent = 'Đang gửi...';
        const body = new URLSearchParams(new FormData(form));
        try {
            const res = await fetch('{{ route("contact.submit") }}', {
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
                btn.textContent = 'Gửi liên hệ';
            }
        } catch {
            alert('Lỗi kết nối, vui lòng thử lại.');
            btn.disabled    = false;
            btn.textContent = 'Gửi liên hệ';
        }
    });
});
</script>
@endpush
