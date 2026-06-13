@extends('layouts.app')

@section('title', 'Villa ' . $location . ' - LuxNest')
@section('main_class', 'lx-main--no-padding')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/page-villa.css') }}">
@endpush

@section('content')

{{-- ========== HERO ========== --}}
<section class="villa-archive-hero" style="background-image: url('https://images.unsplash.com/photo-1618773928121-c32242e63f39?q=80&w=1920&auto=format&fit=crop');">
    <div class="hero-overlay"></div>

    <div class="hero-content">
        <h1>100 VOUCHER GIẢM GIÁ LIÊN TỤC</h1>
        <p class="subtitle">ĐĂNG KÍ NHANH TAY - NHẬN NGAY ƯU ĐÃI</p>
        <p class="desc">DỊCH VỤ CHU ĐÁO, TIỆN ÍCH, HỖ TRỢ KHÁCH HÀNG 24/7</p>
        <a href="#villa-list" class="btn-primary">
            <i class="fa-solid fa-phone"></i> ĐẶT PHÒNG NGAY
        </a>
    </div>

    {{-- Orange bar at bottom of hero --}}
    <div class="villa-archive-bar">
        <div class="container">
            <div class="bar-left">
                <h2>{{ mb_strtoupper($location) }}</h2>
                <div class="breadcrumbs">TRANG CHỦ / MIỀN NAM / {{ mb_strtoupper($location) }}</div>
            </div>
            <div class="bar-right">
                <span class="result-count">{{ count($villas) }} villa</span>
                <form method="GET" action="{{ route('villa.index') }}">
                    <select name="location" class="sort-select" onchange="this.form.submit()">
                        @foreach($branches as $branch)
                            <option value="{{ $branch }}" {{ $branch === $location ? 'selected' : '' }}>
                                {{ $branch }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>
</section>

{{-- ========== VILLA GRID ========== --}}
<section class="villa-archive-grid-section" id="villa-list">
    <div class="container">

        {{-- Location tabs --}}
        <div style="display:flex;gap:10px;margin-bottom:32px;flex-wrap:wrap">
            @foreach($branches as $branch)
            <a href="{{ route('villa.index', ['location' => $branch]) }}"
               style="padding:8px 20px;border-radius:50px;font-weight:700;font-size:.9rem;text-decoration:none;border:2px solid {{ $branch === $location ? '#1a1a1a' : '#e0e0e0' }};background:{{ $branch === $location ? '#1a1a1a' : '#fff' }};color:{{ $branch === $location ? '#fff' : '#555' }};transition:all .2s">
                {{ $branch }}
            </a>
            @endforeach
        </div>

        @if(count($villas) > 0)
        <div class="villa-grid">
            @foreach($villas as $villa)
            <div class="villa-item-horizontal">
                <div class="villa-item-gallery">
                    <a href="tel:{{ preg_replace('/\s+/', '', $settings->hotline ?? '') }}" class="main-image-link">
                        <span class="main-image" style="background-image:url('{{ $villa['image'] }}')"></span>
                    </a>
                </div>

                <div class="villa-item-content">
                    <h3 class="villa-name">{{ $villa['name'] }}</h3>

                    <div class="villa-location">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="#1a1a1a" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                        {{ $villa['location_desc'] }}
                    </div>

                    <div class="villa-amenities">
                        <span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="#1a1a1a" xmlns="http://www.w3.org/2000/svg"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                            {{ $villa['beds'] }}
                        </span>
                        <span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="#1a1a1a" xmlns="http://www.w3.org/2000/svg"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                            {{ $villa['guests'] }}
                        </span>
                    </div>
                </div>

                <div class="villa-item-action">
                    <p class="villa-contact-label">Liên hệ đặt phòng</p>
                    <a href="tel:{{ preg_replace('/\s+/', '', $settings->hotline ?? '') }}" class="villa-call-btn">
                        <i class="fa-solid fa-phone"></i> {{ $settings->hotline }}
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="text-align:center;padding:80px 20px;color:#888">
            <i class="fa-solid fa-house-chimney" style="font-size:3rem;margin-bottom:16px;display:block;color:#ddd"></i>
            <h3 style="font-size:1.2rem;font-weight:600">Hiện chưa có villa khả dụng tại {{ $location }}</h3>
        </div>
        @endif

    </div>
</section>

@endsection
