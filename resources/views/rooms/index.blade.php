@extends('layouts.app')

@section('title', 'Khách sạn tại ' . $keyword . ' - LuxNest')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/rooms-archive.css') }}">
<style>
/* ── Search strip ── */
.ra-search-strip {
    background: #1a1a1a;
    padding: 12px 0;
    position: sticky;
    top: 64px;
    z-index: 100;
    box-shadow: 0 2px 8px rgba(0,0,0,.25);
}
.ra-search-strip .container {
    max-width: 1260px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    gap: 8px;
    align-items: center;
}
.ra-search-field {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #fff;
    border-radius: 8px;
    padding: 8px 14px;
    flex: 1;
    cursor: pointer;
    min-width: 0;
}
.ra-search-field i { color: #888; font-size: .9rem; flex-shrink: 0; }
.ra-search-field-label { font-size: .7rem; color: #888; }
.ra-search-field-val { font-size: .9rem; font-weight: 600; color: #1a1a1a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ra-search-btn {
    background: #996d4e;
    color: #fff;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 700;
    font-size: .9rem;
    cursor: pointer;
    white-space: nowrap;
    flex-shrink: 0;
}
.ra-search-btn:hover { background: #e54f00; }

/* ── Breadcrumb ── */
.ra-breadcrumb {
    background: #fff;
    border-bottom: 1px solid #eee;
    padding: 10px 0;
}
.ra-breadcrumb .container {
    max-width: 1260px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: .85rem;
    color: #666;
    flex-wrap: wrap;
}
.ra-breadcrumb a { color: #3b71fe; text-decoration: none; }
.ra-breadcrumb a:hover { text-decoration: underline; }
.ra-breadcrumb i { font-size: .7rem; color: #ccc; }

/* ── Pagination ── */
.ra-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
    margin-top: 32px;
    flex-wrap: wrap;
}
.ra-page-btn {
    width: 38px; height: 38px;
    border-radius: 8px;
    border: 1.5px solid #e0e0e0;
    background: #fff;
    color: #333;
    font-weight: 600;
    font-size: .9rem;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all .2s;
}
.ra-page-btn:hover { border-color: #3b71fe; color: #3b71fe; }
.ra-page-btn--active { background: #3b71fe; border-color: #3b71fe; color: #fff; }
.ra-page-btn--arrow { color: #888; }

/* ── Rating badge colour ── */
.score-excellent { background: #1b5e20 !important; }
.score-great     { background: #2e7d32 !important; }
.score-good      { background: #0056b3 !important; }
.score-ok        { background: #e65100 !important; }

/* ── Mobile toggle bar ── */
.ra-mobile-toggle-bar { display: none; }
.ra-drawer-header { display: none; }
.ra-drawer-overlay { display: none; }

@media (max-width: 768px) {
    /* Toggle bar */
    .ra-mobile-toggle-bar {
        display: block;
        position: sticky;
        top: 56px;
        z-index: 100;
        background: #1a1a1a;
        padding: 10px 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,.25);
    }
    .ra-mobile-toggle-btn {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(255,255,255,.15);
        border: 1.5px solid rgba(255,255,255,.25);
        border-radius: 10px;
        padding: 11px 16px;
        color: #fff;
        font-size: .9rem;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        text-align: left;
    }

    /* Drawer */
    #raSearchDrawer {
        position: fixed !important;
        top: 0; bottom: 0; left: -100%;
        width: min(340px, 90vw);
        height: 100% !important;
        max-height: 100vh;
        overflow-y: auto;
        z-index: 10000;
    }
    /* Litepicker phải cao hơn drawer */
    .litepicker { z-index: 20000 !important; }
    #raSearchDrawer {
        transition: left .3s cubic-bezier(.4,0,.2,1);
        padding-bottom: 32px;
    }
    #raSearchDrawer.ra-drawer-open { left: 0; }

    .ra-drawer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid rgba(255,255,255,.15);
        margin-bottom: 8px;
        color: #fff;
    }

    .ra-drawer-overlay {
        display: block;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.5);
        z-index: 9999;
        opacity: 0;
        pointer-events: none;
        transition: opacity .3s;
    }
    .ra-drawer-overlay.active { opacity: 1; pointer-events: all; }

    /* Hide original sticky positioning on mobile */
    .ra-search-strip { position: static !important; top: auto !important; }
}

/* ── Mobile: search strip ── */
@media (max-width: 768px) {
    .ra-search-strip { top: 56px; padding: 10px 0; }
    .ra-search-strip .container {
        flex-wrap: wrap;
        gap: 6px;
    }
    /* Destination takes full row */
    .ra-search-field--dest { flex: 0 0 100%; }
    /* Date fields share one row */
    .ra-search-field--date { flex: 1 1 calc(50% - 3px); min-width: 0; }
    /* Guests takes full row */
    .ra-search-field--guests { flex: 0 0 100%; }
    /* Button full width */
    .ra-search-btn { flex: 0 0 100%; text-align: center; padding: 11px; }
    .ra-search-field { padding: 7px 10px; }
    .ra-search-field-label { font-size: .65rem; }
    .ra-search-field-val,
    .ra-search-field input { font-size: .85rem !important; }
    .ra-search-field input { width: 100% !important; }
}

/* ── Mobile rating row (hidden on desktop) ── */
.mobile-rating-row { display: none; }

/* ── Mobile: cards ── */
@media (max-width: 768px) {
    .room-item-horizontal {
        flex-direction: row !important;
        border-radius: 10px !important;
        margin-bottom: 10px !important;
        min-height: 120px;
        background: #fff;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
    }
    .item-gallery {
        width: 42% !important;
        min-width: 42% !important;
        max-width: 160px !important;
        padding: 0 !important;
        gap: 0 !important;
        background: transparent !important;
        flex-shrink: 0;
    }
    .main-image-link {
        border-radius: 0 !important;
        box-shadow: none !important;
        height: 100%;
        display: block;
    }
    .main-image {
        height: 100% !important;
        min-height: 120px !important;
        border-radius: 0 !important;
    }
    .thumb-images,
    .wishlist-btn-overlay,
    .mobile-price-block,
    .archive-rating-badge,
    .sale-ribbon { display: none !important; }

    .item-content {
        flex: 1 !important;
        padding: 10px 28px 10px 12px !important;
        border-right: none !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        position: relative;
        overflow: hidden;
    }
    .item-content::after {
        display: block !important;
        content: '' !important;
        position: absolute;
        right: 12px; top: 50%;
        transform: translateY(-50%) rotate(45deg);
        width: 7px; height: 7px;
        border-top: 2px solid #bbb;
        border-right: 2px solid #bbb;
    }
    .title-row { display: block !important; margin-bottom: 2px; }
    .rating-summary, .room-tag, .amenities-chips,
    .cancellation-policy, .promo-banner { display: none !important; }
    .room-meta-info { margin-bottom: 3px !important; }

    .room-name {
        font-size: .9rem !important;
        font-weight: 700 !important;
        color: #1a56db !important;
        margin-bottom: 0 !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.3 !important;
    }

    /* Rating row: badge + text trên 1 dòng, review count dòng dưới */
    .mobile-rating-row {
        display: flex !important;
        flex-direction: column;
        gap: 1px;
        margin: 5px 0 3px;
    }
    .mobile-rating-row .m-top-line {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .m-score-badge {
        background: #1a56db;
        color: #fff;
        font-size: .8rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 4px;
        flex-shrink: 0;
    }
    .m-score-badge.score-excellent { background: #1b5e20; }
    .m-score-badge.score-great     { background: #2e7d32; }
    .m-score-badge.score-good      { background: #1a56db; }
    .m-score-badge.score-ok        { background: #e65100; }
    .m-rating-text {
        font-size: .82rem;
        font-weight: 700;
        color: #1a56db;
    }
    .m-review-count {
        font-size: .78rem;
        color: #888;
    }

    /* Location */
    .room-location {
        font-size: .8rem !important;
        color: #555 !important;
        font-weight: 500 !important;
        display: flex !important;
        align-items: center;
        gap: 4px;
        margin-bottom: 5px;
    }
    .room-location span { display: none !important; }
    .room-location i { color: #1a56db !important; }

    /* Mobile price */
    .mobile-price-in-content { display: block !important; }
    .m-price-amount {
        font-size: 1rem;
        font-weight: 800;
        color: #e53935;
        line-height: 1.2;
    }
    .m-price-amount .currency { font-size: .82rem; font-weight: 700; }
    .m-price-note {
        font-size: .7rem;
        color: #888;
        margin-top: 2px;
    }

    /* Hide action panel */
    .item-action { display: none !important; }
}

/* Hide mobile-only elements on desktop */
.mobile-price-in-content { display: none; }
</style>
@endpush

@section('content')

{{-- MOBILE: Toggle button --}}
<div class="ra-mobile-toggle-bar">
    <button type="button" id="raSearchToggle" class="ra-mobile-toggle-btn">
        <i class="fa-solid fa-magnifying-glass"></i>
        <span>
            @if($checkIn && $checkOut)
                {{ \Carbon\Carbon::parse($checkIn)->format('d/m') }} → {{ \Carbon\Carbon::parse($checkOut)->format('d/m') }}
                · {{ $adults + $children }} khách
            @elseif($keyword)
                {{ $keyword }}
            @else
                Tìm phòng
            @endif
        </span>
        <i class="fa-solid fa-sliders" style="margin-left:auto;font-size:.9rem;opacity:.6;"></i>
    </button>
</div>

{{-- MOBILE: Drawer overlay --}}
<div id="raDrawerOverlay" class="ra-drawer-overlay"></div>

{{-- SEARCH STRIP (desktop) / DRAWER (mobile) --}}
<div class="ra-search-strip" id="raSearchDrawer">
    <div class="ra-drawer-header">
        <span style="font-weight:700;font-size:1rem;">Tìm kiếm phòng</span>
        <button type="button" id="raDrawerClose" style="background:none;border:none;cursor:pointer;font-size:1.3rem;color:#fff;line-height:1;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <div class="container">
        <form action="{{ route('rooms.index') }}" method="GET" style="display:contents">
            <div class="ra-search-field ra-search-field--dest">
                <i class="fa-solid fa-magnifying-glass"></i>
                <div style="flex:1;min-width:0">
                    <div class="ra-search-field-label">Điểm đến</div>
                    <input type="text" name="keyword" value="{{ $keyword }}"
                           style="border:none;outline:none;font-size:.9rem;font-weight:600;color:#1a1a1a;width:100%;background:transparent"
                           placeholder="Nhập điểm đến...">
                </div>
            </div>
            <div class="ra-search-field ra-search-field--date" id="raDateField" style="cursor:pointer;">
                <i class="fa-regular fa-calendar"></i>
                <div>
                    <div class="ra-search-field-label">Nhận - Trả phòng</div>
                    <div class="ra-search-field-val" id="raDateDisplay">
                        {{ ($checkIn && $checkOut) ? \Carbon\Carbon::parse($checkIn)->format('d/m').' → '.\Carbon\Carbon::parse($checkOut)->format('d/m/Y') : 'Chọn ngày' }}
                    </div>
                </div>
            </div>
            <div class="ra-search-field ra-search-field--guests" id="raGuestField" style="cursor:pointer;position:relative;">
                <i class="fa-solid fa-user-group"></i>
                <div>
                    <div class="ra-search-field-label">Khách</div>
                    <div class="ra-search-field-val" id="raGuestDisplay">{{ ($adults + $children) }} Khách</div>
                </div>
                {{-- Guest mini-dropdown --}}
                <div id="raGuestDrop" style="display:none;position:absolute;top:calc(100% + 8px);left:0;background:#fff;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.12);border:1px solid #e2e8f0;padding:14px;z-index:999;min-width:240px;">
                    @foreach([['adults','Người lớn',$adults],['children','Trẻ em',$children]] as [$type,$label,$val])
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                        <span style="font-size:.88rem;font-weight:600;color:#0f172a;">{{ $label }}</span>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <button type="button" class="ra-gc-btn minus" data-type="{{ $type }}" style="width:28px;height:28px;border-radius:50%;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;font-size:1rem;font-weight:700;color:#1a1a1a;">−</button>
                            <span class="ra-gc-num" data-type="{{ $type }}" style="width:18px;text-align:center;font-weight:700;">{{ $val }}</span>
                            <button type="button" class="ra-gc-btn plus" data-type="{{ $type }}" style="width:28px;height:28px;border-radius:50%;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;font-size:1rem;font-weight:700;color:#1a1a1a;">+</button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <input type="hidden" name="check_in"  id="raCheckIn"  value="{{ $checkIn }}">
            <input type="hidden" name="check_out" id="raCheckOut" value="{{ $checkOut }}">
            <input type="hidden" name="adults"    id="raAdults"   value="{{ $adults }}">
            <input type="hidden" name="children"  id="raChildren" value="{{ $children }}">
            <button type="submit" class="ra-search-btn"><i class="fa-solid fa-magnifying-glass"></i> TÌM</button>
        </form>
    </div>
</div>

{{-- BREADCRUMB --}}
<div class="ra-breadcrumb">
    <div class="container">
        <a href="{{ url('/') }}">Trang chủ</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="#">Việt Nam</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span>{{ $keyword }}</span>
    </div>
</div>

{{-- MAIN ARCHIVE --}}
<section class="rooms-archive-section">
    <div class="container">
        <div class="archive-layout">

            {{-- ============ SIDEBAR ============ --}}
            <aside class="archive-sidebar">

                {{-- Map --}}
                <div class="sidebar-block map-block">
                    <div class="map-preview">
                        <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?w=600&q=80" alt="Bản đồ LuxNest">
                        <div class="map-overlay">
                            <button class="explore-map-btn" type="button">
                                <i class="fa-solid fa-map-location-dot"></i> Khám phá bản đồ
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Filter Form --}}
                <form method="GET" action="{{ route('rooms.index') }}" id="sidebar-filter-form">
                    <input type="hidden" name="keyword"  value="{{ $keyword }}">
                    <input type="hidden" name="check_in"  value="{{ $checkIn }}">
                    <input type="hidden" name="check_out" value="{{ $checkOut }}">
                    <input type="hidden" name="adults"    value="{{ $adults }}">
                    <input type="hidden" name="children"  value="{{ $children }}">
                    <input type="hidden" name="sort"     value="{{ $sort }}">
                    <input type="hidden" name="min_price" id="min_price_input" value="{{ request('min_price', 0) }}">
                    <input type="hidden" name="max_price" id="max_price_input" value="{{ request('max_price', 10000000) }}">

                    {{-- Price --}}
                    <div class="sidebar-block">
                        <div class="block-header">
                            <h4>Giá mỗi đêm</h4>
                            <button type="button" class="reset-link" onclick="resetPrice()">Đặt lại</button>
                        </div>
                        <div class="price-slider-container">
                            <div class="dual-range-slider" id="priceSlider">
                                <div class="slider-track"></div>
                                <input type="range" id="priceMin" min="0" max="2000000" value="{{ request('min_price', 0) }}" step="50000">
                                <input type="range" id="priceMax" min="0" max="2000000" value="{{ request('max_price', 2000000) }}" step="50000">
                            </div>
                            <div class="price-inputs">
                                <div class="price-input-group">
                                    <input type="text" id="minPriceInput" value="{{ number_format((int)request('min_price',0), 0, ',', '.') }}" readonly>
                                    <span>VND</span>
                                </div>
                                <div class="price-input-group">
                                    <input type="text" id="maxPriceInput" value="{{ number_format((int)request('max_price',2000000), 0, ',', '.') }}" readonly>
                                    <span>VND</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Chi nhánh --}}
                    <div class="sidebar-block">
                        <div class="block-header">
                            <h4>Loại chỗ ở</h4>
                            <button type="button" class="reset-link" onclick="resetCheckboxes('branch')">Đặt lại</button>
                        </div>
                        @foreach(['Hotel' => 'Khách sạn', 'Villa' => 'Villa', 'Residence' => 'Căn hộ'] as $val => $label)
                        <label class="filter-checkbox">
                            <input type="checkbox" name="branch[]" value="{{ $val }}"
                                   {{ in_array($val, (array)request('branch',[])) ? 'checked' : '' }}
                                   onchange="document.getElementById('sidebar-filter-form').submit()">
                            <span class="checkmark"></span>
                            <span>{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>

                    {{-- Amenities --}}
                    <div class="sidebar-block">
                        <div class="block-header">
                            <h4>Tiện nghi</h4>
                            <button type="button" class="reset-link" onclick="resetCheckboxes('amenity')">Đặt lại</button>
                        </div>
                        @foreach(['Wi-Fi', 'Điều hòa', 'TV', 'Nước nóng'] as $am)
                        <label class="filter-checkbox">
                            <input type="checkbox" name="amenity[]" value="{{ $am }}"
                                   {{ in_array($am, (array)request('amenity',[])) ? 'checked' : '' }}
                                   onchange="document.getElementById('sidebar-filter-form').submit()">
                            <span class="checkmark"></span>
                            <span>{{ $am }}</span>
                        </label>
                        @endforeach
                    </div>


                </form>

            </aside>

            {{-- ============ MAIN CONTENT ============ --}}
            <div class="archive-main">

                {{-- Header --}}
                <div class="main-header">
                    <div class="location-summary">
                        <h2>{{ $displayKeyword }}: {{ $hotels->total() }} chỗ ở</h2>
                        <p class="result-count">
                            <i class="fa-regular fa-calendar"></i>&nbsp;
                            {{ $checkIn ?: date('d/m/Y', strtotime('+1 day')) }}
                            &nbsp;→&nbsp;
                            {{ $checkOut ?: date('d/m/Y', strtotime('+2 days')) }}
                            &nbsp;·&nbsp; {{ $adults }} người lớn
                        </p>
                    </div>
                    <div class="header-controls">
                        <form action="{{ route('rooms.index') }}" method="GET" style="display:contents">
                            <input type="hidden" name="keyword" value="{{ $keyword }}">
                            <input type="hidden" name="adults"   value="{{ $adults }}">
                            <input type="hidden" name="children" value="{{ $children }}">
                            <input type="hidden" name="check_in"  value="{{ $checkIn }}">
                            <input type="hidden" name="check_out" value="{{ $checkOut }}">
                            <div class="control-group">
                                <label style="font-weight:600;white-space:nowrap">Sắp xếp theo:</label>
                                <select name="sort" class="custom-select" onchange="this.form.submit()">
                                    <option value="recommended" {{ $sort==='recommended'?'selected':'' }}>Đề xuất</option>
                                    <option value="price_asc"   {{ $sort==='price_asc'?'selected':'' }}>Giá thấp → cao</option>
                                    <option value="price_desc"  {{ $sort==='price_desc'?'selected':'' }}>Giá cao → thấp</option>
                                    <option value="rating"      {{ $sort==='rating'?'selected':'' }}>Điểm cao nhất</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Hotel Cards --}}
                @foreach($hotels as $hotel)
                @php
                    $scoreClass = $hotel['rating'] >= 9.5 ? 'score-excellent'
                                : ($hotel['rating'] >= 9.0 ? 'score-great'
                                : ($hotel['rating'] >= 8.0 ? 'score-good' : 'score-ok'));
                    $visibleAmenities = array_slice($hotel['amenities'], 0, 4);
                    $extraAmenities   = array_slice($hotel['amenities'], 4);
                @endphp
                <div class="room-item-horizontal">

                    {{-- Gallery --}}
                    <div class="item-gallery">
                        <span class="archive-rating-badge {{ $scoreClass }}">{{ number_format($hotel['rating'], 1) }}</span>

                        <a href="{{ route('hotel.show', $hotel['slug']) }}" class="main-image-link">
                            <div class="main-image" style="background-image:url('{{ $hotel['images'][0] }}')">
                                @if($hotel['discount'])
                                <span class="sale-ribbon">-{{ $hotel['discount'] }}%</span>
                                @endif
                            </div>
                        </a>

                        <button class="wishlist-btn-overlay" data-id="{{ $hotel['id'] }}" aria-label="Yêu thích">
                            <svg class="heart-icon heart-outline" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                            <svg class="heart-icon heart-filled" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" style="position:absolute"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        </button>

                        @if(count($hotel['images']) > 1)
                        <div class="thumb-images">
                            @foreach(array_slice($hotel['images'], 1, 3) as $idx => $thumb)
                            <div class="thumb @if($idx === 2 && count($hotel['images']) > 4) more-thumb @endif"
                                 style="background-image:url('{{ $thumb }}')">
                                @if($idx === 2 && count($hotel['images']) > 4)
                                <span>+{{ count($hotel['images']) - 4 }}</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif

                        {{-- Mobile price block --}}
                        <div class="mobile-price-block">
                            <p class="mobile-price-helper">Giá mỗi đêm</p>
                            <div class="mobile-price-amount">
                                <span class="amount">{{ number_format($hotel['price'], 0, ',', '.') }}</span>
                                <span class="currency">₫</span>
                            </div>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="item-content">
                        <div>
                            <div class="title-row">
                                <a href="{{ route('hotel.show', $hotel['slug']) }}" class="room-title-link">
                                    <h2 class="room-name">{{ $hotel['name'] }}</h2>
                                </a>
                                <div class="rating-summary">
                                    <div class="rating-text">
                                        <strong>{{ $hotel['rating_text'] }}</strong>
                                        <span class="review-count">{{ number_format($hotel['reviews_count'], 0, ',', '.') }} đánh giá</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Mobile: rating badge + text inline --}}
                            <div class="mobile-rating-row">
                                <div class="m-top-line">
                                    <span class="m-score-badge {{ $scoreClass }}">{{ number_format($hotel['rating'], 1) }}</span>
                                    <span class="m-rating-text">{{ $hotel['rating_text'] }}</span>
                                </div>
                                <span class="m-review-count">{{ number_format($hotel['reviews_count'], 0, ',', '.') }} nhận xét</span>
                            </div>

                            <div class="room-meta-info">
                                <div class="room-tag">
                                    <i class="fa-solid fa-hotel"></i>
                                    {{ $hotel['type'] }}
                                    <span class="mini-stars">
                                        @for($s = 0; $s < $hotel['stars']; $s++)<i class="fa-solid fa-star" style="color:#f59e0b"></i>@endfor
                                    </span>
                                </div>
                                <div class="room-location">
                                    <i class="fa-solid fa-location-dot" style="color:#3b71fe"></i>
                                    {{ $hotel['location'] }}
                                    <span style="color:#888;font-weight:400;font-size:.8rem">· {{ $hotel['distance'] }}</span>
                                </div>
                            </div>

                            <div class="amenities-chips">
                                @foreach($visibleAmenities as $am)
                                <span><i class="fa-solid fa-check" style="color:#03a65a;font-size:.7rem"></i> {{ $am }}</span>
                                @endforeach
                                @if(count($extraAmenities))
                                <span class="more-chips"
                                      data-tooltip="{{ implode(' · ', $extraAmenities) }}">+{{ count($extraAmenities) }} nữa</span>
                                @endif
                            </div>

                            <p class="cancellation-policy">
                                @if(str_contains($hotel['cancellation'], 'Hủy miễn phí'))
                                <i class="fa-solid fa-circle-check"></i>
                                @else
                                <i class="fa-solid fa-circle-xmark" style="color:#ef4444"></i>
                                @endif
                                {{ $hotel['cancellation'] }}
                            </p>
                        </div>

                        @if($hotel['promo'])
                        <div class="promo-banner">
                            <i class="fa-solid fa-tag"></i> {{ $hotel['promo'] }}
                        </div>
                        @endif

                        {{-- Mobile: price shown inside content panel --}}
                        <div class="mobile-price-in-content">
                            <div class="m-price-amount">
                                {{ number_format($hotel['price'], 0, ',', '.') }} <span class="currency">đ</span>
                            </div>
                            <p class="m-price-note">Giá trung bình mỗi đêm</p>
                        </div>
                    </div>

                    {{-- Action --}}
                    <div class="item-action">
                        @if($hotel['badge'])
                        <div class="special-badge">
                            <i class="fa-solid fa-award"></i> {{ $hotel['badge'] }}
                        </div>
                        @else
                        <div></div>
                        @endif

                        <div class="action-price">
                            @if($hotel['old_price'])
                            <p class="original-price">{{ number_format($hotel['old_price'], 0, ',', '.') }} ₫</p>
                            @endif
                            <div class="discounted-price">
                                <span class="amount">{{ number_format($hotel['price'], 0, ',', '.') }}</span>
                                <span class="currency">₫</span>
                            </div>
                            <p class="price-notice">Giá mỗi đêm, chưa gồm thuế & phí</p>
                        </div>

                        <a href="{{ route('hotel.show', $hotel['slug']) }}" class="select-room-btn">
                            Xem phòng trống
                        </a>
                    </div>

                </div>
                @endforeach

                {{-- Pagination --}}
                @if($hotels->hasPages())
                <div class="ra-pagination">
                    {{-- Prev --}}
                    @if($hotels->onFirstPage())
                        <span class="ra-page-btn ra-page-btn--arrow" style="opacity:.35;cursor:default"><i class="fa-solid fa-chevron-left"></i></span>
                    @else
                        <a href="{{ $hotels->previousPageUrl() }}" class="ra-page-btn ra-page-btn--arrow"><i class="fa-solid fa-chevron-left"></i></a>
                    @endif

                    {{-- Pages --}}
                    @foreach($hotels->getUrlRange(1, $hotels->lastPage()) as $page => $url)
                        @if($page == $hotels->currentPage())
                            <span class="ra-page-btn ra-page-btn--active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="ra-page-btn">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if($hotels->hasMorePages())
                        <a href="{{ $hotels->nextPageUrl() }}" class="ra-page-btn ra-page-btn--arrow"><i class="fa-solid fa-chevron-right"></i></a>
                    @else
                        <span class="ra-page-btn ra-page-btn--arrow" style="opacity:.35;cursor:default"><i class="fa-solid fa-chevron-right"></i></span>
                    @endif
                </div>
                @endif

            </div>{{-- /archive-main --}}
        </div>{{-- /archive-layout --}}
    </div>
</section>

@endsection

@push('scripts')
<script>
// ── Mobile drawer toggle ─────────────────────────────────────
(function(){
    const toggle  = document.getElementById('raSearchToggle');
    const drawer  = document.getElementById('raSearchDrawer');
    const overlay = document.getElementById('raDrawerOverlay');
    const close   = document.getElementById('raDrawerClose');

    function openDrawer(){
        drawer.classList.add('ra-drawer-open');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeDrawer(){
        drawer.classList.remove('ra-drawer-open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    toggle?.addEventListener('click', openDrawer);
    close?.addEventListener('click', closeDrawer);
    overlay?.addEventListener('click', closeDrawer);
})();

// ── Date picker for rooms search strip ──────────────────────
(function(){
    const dateField   = document.getElementById('raDateField');
    const ciInput     = document.getElementById('raCheckIn');
    const coInput     = document.getElementById('raCheckOut');
    const dateDisplay = document.getElementById('raDateDisplay');

    function fmtD(s){ const d=new Date(s); return d.getDate().toString().padStart(2,'0')+'/'+(d.getMonth()+1).toString().padStart(2,'0')+'/'+d.getFullYear(); }

    if(typeof Litepicker !== 'undefined' && dateField){
        const dummy = document.createElement('input');
        dummy.type = 'text';
        dummy.style.cssText = 'position:absolute;opacity:0;pointer-events:none;width:1px;height:1px;bottom:0;left:0;';
        dateField.style.position = 'relative';
        dateField.appendChild(dummy);
        const picker = new Litepicker({
            element: dummy,
            singleMode: false,
            format: 'YYYY-MM-DD',
            numberOfMonths: window.innerWidth > 768 ? 2 : 1,
            numberOfColumns: window.innerWidth > 768 ? 2 : 1,
            minDate: new Date(),
            autoApply: true,
            parentEl: document.body, // render ra body, tránh bị clip bởi overflow
            setup: p => {
                p.on('selected', (d1, d2) => {
                    ciInput.value = d1.format('YYYY-MM-DD');
                    coInput.value = d2.format('YYYY-MM-DD');
                    dateDisplay.textContent = fmtD(d1.format('YYYY-MM-DD'))+' → '+fmtD(d2.format('YYYY-MM-DD'));
                });
            }
        });
        dateField.addEventListener('click', () => picker.show());
    }

    // Guest dropdown
    const guestField = document.getElementById('raGuestField');
    const guestDrop  = document.getElementById('raGuestDrop');
    const guestDisp  = document.getElementById('raGuestDisplay');
    const adultsIn   = document.getElementById('raAdults');
    const childrenIn = document.getElementById('raChildren');
    let counts = { adults: parseInt(adultsIn.value)||1, children: parseInt(childrenIn.value)||0 };

    function updateGuests(){
        const t = counts.adults + counts.children;
        guestDisp.textContent = t + ' Khách';
        adultsIn.value   = counts.adults;
        childrenIn.value = counts.children;
        document.querySelectorAll('.ra-gc-btn.minus').forEach(b => {
            b.disabled = counts[b.dataset.type] <= (b.dataset.type==='adults'?1:0);
        });
        document.querySelectorAll('.ra-gc-num').forEach(el => el.textContent = counts[el.dataset.type]);
    }

    if(guestField && guestDrop){
        // Move to body to escape overflow:hidden on search strip
        document.body.appendChild(guestDrop);
        guestDrop.style.position = 'fixed';
        guestDrop.style.zIndex   = '99999';

        function posRaDrop(){
            const r = guestField.getBoundingClientRect();
            guestDrop.style.top  = (r.bottom + 6) + 'px';
            guestDrop.style.left = Math.max(8, r.left) + 'px';
        }

        guestField.addEventListener('click', e => {
            if(e.target.classList.contains('ra-gc-btn')) return;
            e.stopPropagation();
            const open = guestDrop.style.display === 'block';
            if(!open){ posRaDrop(); guestDrop.style.display = 'block'; }
            else guestDrop.style.display = 'none';
        });
        document.querySelectorAll('.ra-gc-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                e.stopPropagation();
                const t = btn.dataset.type, min = t==='adults'?1:0;
                if(btn.classList.contains('plus')) counts[t]++;
                else if(counts[t]>min) counts[t]--;
                updateGuests();
            });
        });
        document.addEventListener('click', e => {
            if(!guestField.contains(e.target) && !guestDrop.contains(e.target)) guestDrop.style.display = 'none';
        });
        window.addEventListener('scroll', () => { guestDrop.style.display = 'none'; }, { passive: true });
        updateGuests();
    }
})();

// Wishlist toggle
document.querySelectorAll('.wishlist-btn-overlay').forEach(btn => {
    btn.addEventListener('click', e => {
        e.preventDefault();
        btn.classList.toggle('active');
    });
});

// Dual range price slider → sync với hidden inputs
const minInput       = document.getElementById('priceMin');
const maxInput       = document.getElementById('priceMax');
const minText        = document.getElementById('minPriceInput');
const maxText        = document.getElementById('maxPriceInput');
const minPriceHidden = document.getElementById('min_price_input');
const maxPriceHidden = document.getElementById('max_price_input');

function fmt(v) { return Number(v).toLocaleString('vi-VN'); }

function syncSlider() {
    let lo = parseInt(minInput.value), hi = parseInt(maxInput.value);
    if (lo > hi - 50000) { lo = hi - 50000; minInput.value = lo; }
    minText.value = fmt(lo);
    maxText.value = fmt(hi);
    if (minPriceHidden) minPriceHidden.value = lo;
    if (maxPriceHidden) maxPriceHidden.value = hi;
}

function resetPrice() {
    minInput.value = 0;
    maxInput.value = 2000000;
    syncSlider();
}

function resetCheckboxes(name) {
    document.querySelectorAll(`input[name="${name}[]"]`).forEach(cb => cb.checked = false);
    document.getElementById('sidebar-filter-form').submit();
}

if (minInput) {
    // 'input' = cập nhật hiển thị khi kéo
    minInput.addEventListener('input', syncSlider);
    maxInput.addEventListener('input', syncSlider);
    // 'change' = submit khi thả tay
    minInput.addEventListener('change', () => document.getElementById('sidebar-filter-form').submit());
    maxInput.addEventListener('change', () => document.getElementById('sidebar-filter-form').submit());
    syncSlider();
}
</script>
@endpush
