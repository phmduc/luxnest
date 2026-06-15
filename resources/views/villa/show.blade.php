@extends('layouts.app')

@section('title', $villa->name . ' - LuxNest')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/hotel-detail.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/page-villa.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
    <style>
    /* ── Policy grid responsive ── */
    .policy-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    @media (max-width: 640px) {
        .policy-grid { grid-template-columns: 1fr; gap: 12px; }
    }
    </style>
@endpush

@section('content')
<main class="lx-main" style="padding-top:20px;">

    {{-- BREADCRUMB --}}
    <div class="hd-breadcrumb lx-container">
        <a href="{{ url('/') }}">Trang chủ</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('villa.index', ['location' => $villa->location]) }}">Villa {{ $villa->location }}</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span>{{ $villa->name }}</span>
    </div>

    {{-- HEADER --}}
    <div class="hd-header lx-container">
        <div class="hd-header__left">
            <h1 class="hd-title">{{ $villa->name }}</h1>
            <p class="hd-address">
                <i class="fa-solid fa-location-dot"></i> {{ $villa->location_desc }}
            </p>
        </div>
        <div class="hd-header__right">
            <button class="hd-action-btn"><i class="fa-regular fa-heart"></i> Lưu</button>
            <button class="hd-action-btn"><i class="fa-solid fa-share-nodes"></i> Chia sẻ</button>
        </div>
    </div>

    {{-- GALLERY --}}
    @if(count($gallery) > 0)
    <div class="hd-gallery lx-container">
        <div class="hd-gallery__main">
            <a href="{{ $gallery[0] }}" data-fancybox="gallery" data-caption="{{ $villa->name }}">
                <img src="{{ $gallery[0] }}" alt="{{ $villa->name }}">
            </a>
        </div>

        @if(count($gallery) > 1)
        <div class="hd-gallery__grid">
            @foreach(array_slice($gallery, 1, 4) as $idx => $img)
                <div class="hd-gallery__item @if($idx == 3) hd-gallery__item--last @endif">
                    <a href="{{ $img }}" data-fancybox="gallery" data-caption="{{ $villa->name }} - Ảnh {{ $idx + 2 }}">
                        <img src="{{ $img }}" alt="Gallery {{ $idx + 2 }}">
                        @if($idx == 3 && count($gallery) > 5)
                            <div class="hd-gallery__more">
                                <span>+{{ count($gallery) - 5 }} Ảnh</span>
                            </div>
                        @endif
                    </a>
                </div>
            @endforeach
        </div>
        @endif

        {{-- Hidden links so remaining images are reachable via lightbox slide --}}
        @if(count($gallery) > 5)
            @foreach(array_slice($gallery, 5) as $idx => $img)
                <a href="{{ $img }}" data-fancybox="gallery" data-caption="{{ $villa->name }} - Ảnh {{ $idx + 6 }}" style="display:none;"></a>
            @endforeach
        @endif
    </div>
    @endif

    {{-- STICKY NAV --}}
    <div class="hd-nav-wrap">
        <div class="hd-nav lx-container">
            <a href="#overview" class="hd-nav__item active">Tổng quan</a>
            <a href="#policies" class="hd-nav__item">Chính sách</a>
        </div>
    </div>

    {{-- MAIN LAYOUT --}}
    <div class="hd-layout lx-container">

        {{-- LEFT COLUMN --}}
        <div class="hd-main">

            {{-- OVERVIEW --}}
            <section id="overview" class="hd-section">
                <div class="hd-overview-grid">
                    <div class="hd-description">
                        <h2 class="hd-section__title">Về villa này</h2>
                        @if($villa->description)
                            <div style="line-height:1.75;color:#444;">
                                {!! nl2br(e($villa->description)) !!}
                            </div>
                        @else
                            <p style="line-height:1.75;color:#444;">
                                {{ $villa->name }} là một villa nghỉ dưỡng tại {{ $villa->location_desc }}, phù hợp cho nhóm gia đình hoặc bạn bè với không gian riêng tư, thoáng đãng.
                                Liên hệ hotline để được tư vấn chi tiết về tiện nghi và lịch trình nhận/trả villa.
                            </p>
                        @endif
                    </div>
                    <div class="hd-highlights">
                        <h3>Thông tin villa</h3>
                        <ul>
                            <li><i class="fa-solid fa-bed"></i> {{ $villa->beds }}</li>
                            <li><i class="fa-solid fa-user-group"></i> {{ $villa->guests }}</li>
                            <li><i class="fa-solid fa-location-dot"></i> {{ $villa->location_desc }}</li>
                        </ul>
                    </div>
                </div>
            </section>

            {{-- POLICIES --}}
            <section id="policies" class="hd-section">
                <h2 class="hd-section__title">Chính sách lưu trú</h2>

                <div class="policy-grid">

                    {{-- Check-in / out --}}
                    <div style="background:#f8fafc;border-radius:14px;padding:20px;">
                        <h4 style="margin:0 0 14px;font-size:1rem;color:#1a1a1a;display:flex;align-items:center;gap:8px;">
                            <i class="fa-regular fa-clock"></i> Giờ nhận & trả villa
                        </h4>
                        <div style="display:flex;flex-direction:column;gap:10px;font-size:.9rem;color:#444;">
                            <div style="display:flex;justify-content:space-between;">
                                <span>Nhận villa (Check-in)</span>
                                <strong>Từ 14:00</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;">
                                <span>Trả villa (Check-out)</span>
                                <strong>Trước 12:00</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;">
                                <span>Nhận villa sớm</span>
                                <strong style="color:#888;">Liên hệ trước</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;">
                                <span>Trả villa muộn</span>
                                <strong style="color:#888;">Liên hệ trước</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Cancellation --}}
                    <div style="background:#f8fafc;border-radius:14px;padding:20px;">
                        <h4 style="margin:0 0 14px;font-size:1rem;color:#1a1a1a;display:flex;align-items:center;gap:8px;">
                            <i class="fa-solid fa-rotate-left"></i> Chính sách hủy
                        </h4>
                        <div style="display:flex;flex-direction:column;gap:10px;font-size:.9rem;color:#444;">
                            <div style="display:flex;align-items:flex-start;gap:8px;">
                                <i class="fa-solid fa-circle-check" style="color:#22c55e;margin-top:2px;flex-shrink:0;"></i>
                                <span>Hủy <strong>miễn phí</strong> trước 48 giờ nhận villa</span>
                            </div>
                            <div style="display:flex;align-items:flex-start;gap:8px;">
                                <i class="fa-solid fa-circle-xmark" style="color:#ef4444;margin-top:2px;flex-shrink:0;"></i>
                                <span>Hủy trong vòng 48 giờ: <strong>mất 100%</strong> tiền đặt cọc</span>
                            </div>
                            <div style="display:flex;align-items:flex-start;gap:8px;">
                                <i class="fa-solid fa-circle-info" style="color:#1a1a1a;margin-top:2px;flex-shrink:0;"></i>
                                <span>Không hoàn tiền khi không đến (no-show)</span>
                            </div>
                        </div>
                    </div>

                    {{-- Payment --}}
                    <div style="background:#f8fafc;border-radius:14px;padding:20px;">
                        <h4 style="margin:0 0 14px;font-size:1rem;color:#1a1a1a;display:flex;align-items:center;gap:8px;">
                            <i class="fa-solid fa-credit-card"></i> Thanh toán
                        </h4>
                        <div style="display:flex;flex-direction:column;gap:10px;font-size:.9rem;color:#444;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <i class="fa-solid fa-check" style="color:#22c55e;"></i>
                                <span>Thanh toán tiền mặt tại chỗ</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <i class="fa-solid fa-check" style="color:#22c55e;"></i>
                                <span>Chuyển khoản ngân hàng</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <i class="fa-solid fa-check" style="color:#22c55e;"></i>
                                <span>VNPay / Momo / ZaloPay</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <i class="fa-solid fa-check" style="color:#22c55e;"></i>
                                <span>Đặt cọc 30% khi đặt villa</span>
                            </div>
                        </div>
                    </div>

                    {{-- Rules --}}
                    <div style="background:#f8fafc;border-radius:14px;padding:20px;">
                        <h4 style="margin:0 0 14px;font-size:1rem;color:#1a1a1a;display:flex;align-items:center;gap:8px;">
                            <i class="fa-solid fa-scroll"></i> Nội quy villa
                        </h4>
                        <div style="display:flex;flex-direction:column;gap:10px;font-size:.9rem;color:#444;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <i class="fa-solid fa-ban" style="color:#ef4444;"></i>
                                <span>Không hút thuốc trong nhà</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <i class="fa-solid fa-ban" style="color:#ef4444;"></i>
                                <span>Không mang thú cưng</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <i class="fa-solid fa-ban" style="color:#ef4444;"></i>
                                <span>Không tổ chức tiệc, sự kiện ồn ào</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <i class="fa-solid fa-check" style="color:#22c55e;"></i>
                                <span>Thân thiện với trẻ em</span>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Contact --}}
                <div style="margin-top:20px;background:#1a1a1a;border:1px solid #1a1a1a;border-radius:14px;padding:18px 22px;display:flex;align-items:center;gap:16px;">
                    <i class="fa-solid fa-headset" style="font-size:1.5rem;color:#fff;flex-shrink:0;"></i>
                    <div>
                        <strong style="font-size:.95rem;color:#fff;">Cần hỗ trợ?</strong>
                        <p style="margin:4px 0 0;font-size:.85rem;color:#cbd5e1;">Liên hệ LuxNest 24/7 qua hotline hoặc chat để được tư vấn đặt villa và giải đáp mọi thắc mắc.</p>
                    </div>
                    <a href="tel:{{ preg_replace('/\s+/', '', $settings->hotline ?? '') }}"
                       style="flex-shrink:0;background:#996d4e;color:#fff;padding:10px 20px;border-radius:10px;font-weight:700;font-size:.85rem;text-decoration:none;white-space:nowrap;">
                        Gọi ngay
                    </a>
                </div>
            </section>

        </div>

        {{-- RIGHT SIDEBAR --}}
        <div class="hd-sidebar">
            <div class="hd-sidebar__box" style="text-align:center; padding:24px;">
                <p style="font-size:.85rem;color:#888;margin-bottom:6px;">Liên hệ đặt villa</p>
                <div style="font-size:1.4rem;font-weight:800;color:#1a1a1a;margin-bottom:16px;">
                    {{ $settings->hotline }}
                </div>
                <a href="tel:{{ preg_replace('/\s+/', '', $settings->hotline ?? '') }}"
                   style="display:block;background:#996d4e;color:#fff;padding:13px;border-radius:10px;font-weight:700;font-size:1rem;text-decoration:none;text-align:center;">
                    <i class="fa-solid fa-phone"></i> Gọi ngay
                </a>
            </div>

            <div class="hd-sidebar__box hd-map-box">
                <div class="hd-map-placeholder">
                    <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?w=400&q=80" alt="Map">
                    <button class="hd-map-btn">Xem bản đồ</button>
                </div>
                <div class="hd-map-info">
                    <p><strong>{{ $villa->location_desc }}</strong></p>
                    <p>Liên hệ để biết vị trí chi tiết</p>
                </div>
            </div>
        </div>

    </div>

    {{-- RELATED VILLAS --}}
    @if($others->count() > 0)
    <section class="villa-related-section lx-container">
        <h2 class="hd-section__title">Villa khác tại {{ $villa->location }}</h2>
        <div class="villa-related-grid">
            @foreach($others as $other)
            <a href="{{ route('villa.show', $other->slug) }}" class="villa-related-card">
                <div class="villa-related-card__image" style="background-image:url('{{ $other->image }}')"></div>
                <div class="villa-related-card__content">
                    <h3>{{ $other->name }}</h3>
                    <p>{{ $other->location_desc }}</p>
                    <span class="villa-related-card__link">Xem chi tiết <i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

</main>

@push('scripts')
<script>
// ── Highlight active nav tab on scroll ───────────────────────
const navItems = document.querySelectorAll('.hd-nav__item[href^="#"]');
const sections = ['overview', 'policies'].map(id => document.getElementById(id)).filter(Boolean);

function updateActiveNav() {
    const scrollY = window.scrollY + 140;
    let current = sections[0];
    sections.forEach(s => { if (s.offsetTop <= scrollY) current = s; });
    navItems.forEach(a => {
        a.classList.toggle('active', a.getAttribute('href') === '#' + current.id);
    });
}

window.addEventListener('scroll', updateActiveNav, { passive: true });

// Smooth scroll with offset for sticky header
navItems.forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        const target = document.querySelector(a.getAttribute('href'));
        if (target) window.scrollTo({ top: target.offsetTop - 120, behavior: 'smooth' });
    });
});
</script>

<!-- Fancybox lightbox for gallery -->
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Fancybox !== 'undefined') {
        Fancybox.bind('[data-fancybox="gallery"]', {
            Thumbs: { type: 'classic' },
        });
    }
});
</script>
@endpush

@endsection
