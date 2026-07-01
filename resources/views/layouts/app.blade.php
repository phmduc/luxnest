<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LuxNest - Hệ thống đặt phòng')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

    @php
        $defaultOgImage = $settings->og_image ?: $settings->logo ?: asset('promo-banner.png');
        $defaultOgDesc  = $settings->footer_description ?: 'Trải nghiệm lưu trú đẳng cấp tại những điểm đến đẹp nhất Việt Nam.';
        $defaultOgTitle = $settings->site_name . ' - Đặt phòng khách sạn, villa & homestay tốt nhất';
    @endphp

    <!-- SEO -->
    <meta name="description" content="@yield('meta_description', $defaultOgDesc)">

    <!-- Open Graph -->
    <meta property="og:type"        content="website">
    <meta property="og:site_name"   content="{{ $settings->site_name }}">
    <meta property="og:title"       content="@yield('og_title', $defaultOgTitle)">
    <meta property="og:description" content="@yield('og_description', $defaultOgDesc)">
    <meta property="og:url"         content="{{ url()->current() }}">
    <meta property="og:image"       content="@yield('og_image', $defaultOgImage)">
    <meta property="og:locale"      content="vi_VN">

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="@yield('og_title', $defaultOgTitle)">
    <meta name="twitter:description" content="@yield('og_description', $defaultOgDesc)">
    <meta name="twitter:image"       content="@yield('og_image', $defaultOgImage)">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;1,14..32,400&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css">
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>

    <!-- Global CSS -->
    <link rel="stylesheet" href="{{ asset_v('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/css/header.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/css/footer.css') }}">

    @stack('styles')
</head>

<body class="@yield('body_class')">

    <!-- ===================== HEADER ===================== -->
    <header class="lx-header" id="lx-header">
        <div class="lx-header__top">
            <div class="lx-container">
                <!-- Logo -->
                <a href="{{ url('/') }}" class="lx-header__logo">
                    @if($settings->logo)
                        <img src="{{ $settings->logo }}" alt="{{ $settings->site_name }}" class="lx-header__logo-img">
                    @else
                        <span class="lx-header__logo-icon">🏡</span>
                        <span class="lx-header__logo-text">{{ $settings->site_name }}</span>
                    @endif
                </a>

                <!-- Mobile Menu (Collapsible) -->
                <div class="lx-header__menu" id="mobileMenu">
                    <!-- Main Nav -->
                    <nav class="lx-header__nav">
                        <a href="{{ route('rooms.index') }}" class="lx-header__nav-item">Phòng</a>
                        <a href="{{ route('villa.index') }}" class="lx-header__nav-item">Villa</a>
                        <a href="{{ route('car-rental.index') }}" class="lx-header__nav-item">Thuê xe - Tour</a>
                        <a href="{{ route('about.index') }}" class="lx-header__nav-item">Giới thiệu</a>
                        <a href="{{ route('faq.index') }}" class="lx-header__nav-item">Câu hỏi thường gặp</a>
                        <a href="{{ route('partner.index') }}" class="lx-header__nav-item">Hợp tác</a>
                        <a href="{{ route('contact.index') }}" class="lx-header__nav-item">Liên hệ</a>
                        <a href="{{ route('news.index') }}" class="lx-header__nav-item">Tin tức</a>
                    </nav>

                    <!-- Auth & Utils -->
                    <div class="lx-header__utils">
                        <button class="lx-header__util-btn">
                            <i class="fa-solid fa-globe"></i>
                            <span>VI</span>
                        </button>
                        <button class="lx-header__util-btn">
                            <i class="fa-regular fa-bell"></i>
                        </button>
                        @auth
                            <a href="{{ url('/dashboard') }}" class="lx-header__auth-btn lx-header__auth-btn--outline">Bảng điều khiển</a>
                        @else
                            <a href="{{ url('/register') }}" class="lx-header__auth-btn lx-header__auth-btn--outline">Đăng ký</a>
                            <a href="{{ url('/login') }}" class="lx-header__auth-btn lx-header__auth-btn--solid">Đăng nhập</a>
                        @endauth
                    </div>
                </div>

                <!-- Mobile toggle -->
                <button class="lx-header__mobile-toggle" id="mobileToggle">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- ===================== MAIN ===================== -->
    <main class="lx-main @yield('main_class')">
        @yield('content')
    </main>

    <!-- ===================== FOOTER ===================== -->
    <footer class="lx-footer">
        <div class="lx-footer__top">
            <div class="lx-container">
                <div class="lx-footer__grid">
                    <div class="lx-footer__col">
                        <div class="lx-footer__logo">
                            @if($settings->logo)
                                <img src="{{ $settings->logo }}" alt="{{ $settings->site_name }}" style="height:32px;display:block;">
                            @else
                                🏡 {{ $settings->site_name }}
                            @endif
                        </div>
                        <p class="lx-footer__desc">{{ $settings->footer_description ?: 'Trải nghiệm lưu trú đẳng cấp tại những điểm đến đẹp nhất Việt Nam.' }}</p>
                        <div class="lx-footer__socials">
                            @if($settings->facebook_url)
                            <a href="{{ $settings->facebook_url }}" target="_blank" rel="noopener" class="lx-footer__social"><i class="fab fa-facebook-f"></i></a>
                            @endif
                            @if($settings->instagram_url)
                            <a href="{{ $settings->instagram_url }}" target="_blank" rel="noopener" class="lx-footer__social"><i class="fab fa-instagram"></i></a>
                            @endif
                            @if($settings->youtube_url)
                            <a href="{{ $settings->youtube_url }}" target="_blank" rel="noopener" class="lx-footer__social"><i class="fab fa-youtube"></i></a>
                            @endif
                        </div>
                    </div>
                    <div class="lx-footer__col">
                        <h4 class="lx-footer__heading">Khám phá</h4>
                        <ul class="lx-footer__list">
                            <li><a href="#">Khách sạn</a></li>
                            <li><a href="#">Villa & Resort</a></li>
                            <li><a href="#">Căn hộ cho thuê</a></li>
                            <li><a href="#">Chỗ ở trong ngày</a></li>
                        </ul>
                    </div>
                    <div class="lx-footer__col">
                        <h4 class="lx-footer__heading">Hỗ trợ</h4>
                        <ul class="lx-footer__list">
                            <li><a href="#">Trung tâm hỗ trợ</a></li>
                            <li><a href="#">Chính sách hoàn tiền</a></li>
                            <li><a href="#">Điều khoản sử dụng</a></li>
                            <li><a href="#">Chính sách bảo mật</a></li>
                        </ul>
                    </div>
                    <div class="lx-footer__col">
                        <h4 class="lx-footer__heading">Liên hệ</h4>
                        <ul class="lx-footer__list lx-footer__list--contact">
                            @if($settings->address)
                            <li>
                                <i class="fa-solid fa-location-dot"></i>
                                @if($settings->map_link)
                                    <a href="{{ $settings->map_link }}" target="_blank" rel="noopener">{{ $settings->address }}</a>
                                @else
                                    {{ $settings->address }}
                                @endif
                            </li>
                            @endif
                            @if($settings->hotline)
                            <li><i class="fa-solid fa-phone"></i> <a href="tel:{{ preg_replace('/\s+/', '', $settings->hotline) }}">{{ $settings->hotline }}</a></li>
                            @endif
                            @if($settings->email)
                            <li><i class="fa-solid fa-envelope"></i> <a href="mailto:{{ $settings->email }}">{{ $settings->email }}</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="lx-footer__bottom">
            <div class="lx-container">
                <span>© {{ date('Y') }} {{ $settings->site_name }}. All rights reserved.</span>
                <div class="lx-footer__bottom-links">
                    <a href="#">Bảo mật</a>
                    <a href="#">Điều khoản</a>
                    <a href="#">Cookie</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Overlay for mobile menu -->
    <div class="lx-header__overlay" id="mobileOverlay"></div>

    <script>
        // Sticky header on scroll
        const header = document.getElementById('lx-header');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 60) {
                header.classList.add('lx-header--sticky');
            } else {
                header.classList.remove('lx-header--sticky');
            }
        });

        // Mobile toggle
        const toggleBtn = document.getElementById('mobileToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        const overlay = document.getElementById('mobileOverlay');

        function toggleMenu() {
            mobileMenu.classList.toggle('is-open');
            overlay.classList.toggle('is-active');
            // Optional: animate hamburger into X
            toggleBtn.classList.toggle('is-active');
        }

        toggleBtn.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', toggleMenu);
    </script>

    @stack('scripts')

    @include('partials.chat-widget')
</body>

</html>
