@extends('layouts.app')

@section('title', 'LuxNest - Đặt phòng khách sạn, villa & homestay tốt nhất')
@section('main_class', 'lx-main--no-padding')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/home.css') }}">
@endpush

@section('content')

{{-- ============================================================
     HERO SECTION
     ============================================================ --}}
<section class="hero">
    <div class="hero__bg">
        <img src="{{ asset('assets/images/hero-bg.jpg') }}" alt="LuxNest Hero" class="hero__img" onerror="this.src='https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1600&q=80'">
        <div class="hero__overlay"></div>
    </div>

    <div class="hero__content">
        <h1 class="hero__title">RONG CHƠI BỐN PHƯƠNG, GIÁ VẪN "YÊU THƯƠNG"</h1>

        {{-- Search Card --}}
        <div class="sw2">
            <div class="sw2__card">
                <div class="sw2__sub-tabs">
                    <button class="sw2__sub-tab sw2__sub-tab--active">Chỗ Ở Qua Đêm</button>
                    <button class="sw2__sub-tab">Chỗ Ở Trong Ngày</button>
                </div>

                <form action="{{ route('rooms.index') }}" method="GET">
                    <div class="sw2__dest">
                        <i class="fa-solid fa-magnifying-glass sw2__dest-icon"></i>
                        <input type="text" name="keyword" class="sw2__dest-input"
                               placeholder="Nhập điểm du lịch hoặc tên khách sạn" autocomplete="off">
                    </div>

                    <div class="sw2__row">
                        <div class="sw2__field" id="sw2FieldCheckin" style="cursor:pointer;">
                            <i class="fa-regular fa-calendar sw2__field-icon"></i>
                            <div class="sw2__field-text">
                                <span class="sw2__field-val" id="sw2Checkin">Nhận phòng</span>
                                <span class="sw2__field-day" id="sw2CheckinDay">Chọn ngày</span>
                            </div>
                        </div>
                        <div class="sw2__row-divider"></div>
                        <div class="sw2__field" id="sw2FieldCheckout" style="cursor:pointer;">
                            <i class="fa-regular fa-calendar sw2__field-icon"></i>
                            <div class="sw2__field-text">
                                <span class="sw2__field-val" id="sw2Checkout">Trả phòng</span>
                                <span class="sw2__field-day" id="sw2CheckoutDay">Chọn ngày</span>
                            </div>
                        </div>
                        <div class="sw2__row-divider"></div>
                        <div class="sw2__field sw2__field--guests" id="sw2GuestField" style="cursor:pointer;">
                            <i class="fa-solid fa-user-group sw2__field-icon"></i>
                            <div class="sw2__field-text">
                                <span class="sw2__field-val" id="sw2GuestVal">2 người lớn</span>
                                <span class="sw2__field-day">0 trẻ em</span>
                            </div>
                            <i class="fa-solid fa-chevron-down sw2__guests-arrow"></i>
                        </div>
                    </div>

                    {{-- Hidden inputs for GoHost params --}}
                    <input type="hidden" name="check_in"  id="sw2HiddenCheckin">
                    <input type="hidden" name="check_out" id="sw2HiddenCheckout">
                    <input type="hidden" name="adults"    id="sw2HiddenAdults"   value="2">
                    <input type="hidden" name="children"  id="sw2HiddenChildren" value="0">

                    <button type="submit" class="sw2__btn">TÌM</button>
                </form>
            </div>
        </div>
    </div>
</section>

@if(false) {{-- ẨN: Điểm đến phổ biến --}}
{{-- ============================================================
     POPULAR DESTINATIONS
     ============================================================ --}}
<section class="lx-section">
    <div class="lx-container">
        <div class="lx-section__header">
            <h2 class="lx-section__title">Điểm đến phổ biến</h2>
            <a href="{{ route('rooms.index') }}" class="lx-section__link">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="destinations-grid">
            @php
            $destinations = [
                ['name' => 'Đà Nẵng',   'count' => '5,534', 'img' => 'https://images.unsplash.com/photo-1560347876-aeef00ee58a1?w=400&q=80', 'tag' => 'Bãi biển'],
                ['name' => 'Nha Trang',  'count' => '4,892', 'img' => 'https://images.unsplash.com/photo-1583417319070-4a69db38a482?w=400&q=80', 'tag' => 'Biển xanh'],
                ['name' => 'Hội An',     'count' => '3,201', 'img' => 'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?w=400&q=80', 'tag' => 'Di sản'],
                ['name' => 'Hà Nội',    'count' => '10,744','img' => 'https://images.unsplash.com/photo-1509030450996-dd1a26dda07a?w=400&q=80', 'tag' => 'Thủ đô'],
                ['name' => 'Phú Quốc',  'count' => '2,785', 'img' => 'https://images.unsplash.com/photo-1589394815804-964ed0be2eb5?w=400&q=80', 'tag' => 'Đảo ngọc'],
                ['name' => 'Đà Lạt',    'count' => '3,560', 'img' => 'https://images.unsplash.com/photo-1586861635167-e5223aadc9fe?w=400&q=80', 'tag' => 'Cao nguyên'],
            ];
            @endphp

            @foreach($destinations as $dest)
            <a href="#" class="dest-card">
                <div class="dest-card__img-wrap">
                    <img src="{{ $dest['img'] }}" alt="{{ $dest['name'] }}" class="dest-card__img" loading="lazy">
                    <span class="dest-card__tag">{{ $dest['tag'] }}</span>
                </div>
                <div class="dest-card__info">
                    <h3 class="dest-card__name">{{ $dest['name'] }}</h3>
                    <p class="dest-card__count">{{ $dest['count'] }} chỗ ở</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============================================================
     FEATURED DEALS
     ============================================================ --}}
<section class="lx-section lx-section--gray">
    <div class="lx-container">
        <div class="lx-section__header">
            <h2 class="lx-section__title">🔥 Ưu đãi hôm nay</h2>
            <a href="{{ route('rooms.index') }}" class="lx-section__link">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="deals-grid">
            @foreach($featuredRooms as $room)
            <a href="{{ route('hotel.show', $room->slug) }}" class="agoda-card">
                <div class="agoda-card__img-wrap">
                    <img src="{{ $room->image }}" alt="{{ $room->name }}" class="agoda-card__img" loading="lazy">
                    <span class="agoda-card__score">9.{{ rand(0,5) }}</span>
                </div>
                <div class="agoda-card__body">
                    <h3 class="agoda-card__name">{{ $room->name }}</h3>
                    <div class="agoda-card__meta">
                        <span class="agoda-card__stars">
                            @for($i = 0; $i < 4; $i++)<i class="fa-solid fa-star"></i>@endfor
                        </span>
                        <span class="agoda-card__dot">•</span>
                        <span class="agoda-card__location">
                            <i class="fa-solid fa-location-dot"></i> {{ $room->branch }}
                        </span>
                    </div>
                    <p class="agoda-card__price-note">Giá mỗi đêm chưa gồm thuế và phí</p>
                    <p class="agoda-card__price">VND {{ number_format($room->price, 0, ',', '.') }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>

@if(false) {{-- ẨN: Khuyến mãi & Hoạt động --}}
{{-- ============================================================
     SECTION: PROMO BANNERS (Khuyến mãi / Activities)
     ============================================================ --}}
<section class="lx-section">
    <div class="lx-container">
        <div class="lx-section__header">
            <h2 class="lx-section__title">Khuyến mãi & Hoạt động</h2>
            <a href="{{ route('rooms.index') }}" class="lx-section__link">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="promo-grid">
            <a href="#" class="promo-card promo-card--purple">
                <div class="promo-card__img-side">
                    <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=300&q=80" alt="Disneyland">
                </div>
                <div class="promo-card__body">
                    <span class="promo-card__badge"><i class="fa-solid fa-earth-asia"></i> Toàn cầu</span>
                    <h3 class="promo-card__title">Vé tham quan & Hoạt động</h3>
                    <p class="promo-card__sub">Giảm đến <strong>15%</strong></p>
                    <span class="promo-card__cta">Đặt ngay →</span>
                </div>
            </a>
            <a href="#" class="promo-card promo-card--orange">
                <div class="promo-card__img-side">
                    <img src="https://images.unsplash.com/photo-1540979388789-6cee28a1cdc9?w=300&q=80" alt="Beach">
                </div>
                <div class="promo-card__body">
                    <span class="promo-card__badge"><i class="fa-solid fa-tag"></i> Flash Sale</span>
                    <h3 class="promo-card__title">Kỳ nghỉ biển mùa hè</h3>
                    <p class="promo-card__sub">Giảm đến <strong>20%</strong></p>
                    <span class="promo-card__cta">Khám phá ngay →</span>
                </div>
            </a>
            <a href="#" class="promo-card promo-card--teal">
                <div class="promo-card__img-side">
                    <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=300&q=80" alt="Food">
                </div>
                <div class="promo-card__body">
                    <span class="promo-card__badge"><i class="fa-solid fa-utensils"></i> Ẩm thực</span>
                    <h3 class="promo-card__title">Tour ẩm thực địa phương</h3>
                    <p class="promo-card__sub">Từ <strong>199,000đ</strong>/người</p>
                    <span class="promo-card__cta">Xem thực đơn →</span>
                </div>
            </a>
        </div>
    </div>
</section>
@endif

{{-- ============================================================
     SECTION: RECOMMENDED HOTELS (Brand-style landscape cards)
     ============================================================ --}}
<section class="lx-section lx-section--gray">
    <div class="lx-container">
        <div class="lx-section__header">
            <h2 class="lx-section__title">Những cơ sở lưu trú bạn sẽ thích</h2>
            <a href="{{ route('rooms.index') }}" class="lx-section__link">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        @php
        $branchMeta = [
            'Hotel'     => ['logo' => '🏨', 'tag' => 'Khách sạn',  'sub' => 'Không gian tinh tế, dịch vụ chuyên nghiệp', 'bg' => '#1a3a6b', 'link' => route('rooms.index', ['keyword' => 'Hotel'])],
            'Villa'     => ['logo' => '🏡', 'tag' => 'Villa',       'sub' => 'Riêng tư, sang trọng giữa thiên nhiên',      'bg' => '#7c3aed', 'link' => route('villa.index', ['location' => 'Đà Lạt'])],
            'Residence' => ['logo' => '🏢', 'tag' => 'Căn hộ',      'sub' => 'Tiện nghi như nhà, đẳng cấp resort',         'bg' => '#0f766e', 'link' => route('rooms.index', ['keyword' => 'Residence'])],
        ];
        @endphp
        <div class="brand-grid">
            @foreach($branchRooms as $branch => $room)
            @php $meta = $branchMeta[$branch] ?? ['logo'=>'🏠','tag'=>$branch,'sub'=>'','bg'=>'#1a3a6b','link'=>'#']; @endphp
            <a href="{{ $meta['link'] }}" class="brand-card">
                <img src="{{ $room->image }}" alt="{{ $branch }}" class="brand-card__img">
                <div class="brand-card__overlay" style="background: linear-gradient(135deg, {{ $meta['bg'] }}cc, {{ $meta['bg'] }}88);">
                    <div class="brand-card__content">
                        <span class="brand-card__tag">{{ $meta['logo'] }} {{ $meta['tag'] }}</span>
                        <h3 class="brand-card__name">{{ $branch }}</h3>
                        <p class="brand-card__sub">{{ $meta['sub'] }}</p>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     SECTION: TRENDING NOW (agoda-card style, 4 items)
     ============================================================ --}}
<section class="lx-section">
    <div class="lx-container">
        <div class="lx-section__header">
            <h2 class="lx-section__title">📈 Đang được quan tâm nhiều</h2>
            <a href="{{ route('rooms.index') }}" class="lx-section__link">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="deals-grid">
            @foreach($trendingRooms as $room)
            <a href="{{ route('hotel.show', $room->slug) }}" class="agoda-card">
                <div class="agoda-card__img-wrap">
                    <img src="{{ $room->image }}" alt="{{ $room->name }}" class="agoda-card__img" loading="lazy">
                    <span class="agoda-card__score">9.{{ rand(2,8) }}</span>
                </div>
                <div class="agoda-card__body">
                    <h3 class="agoda-card__name">{{ $room->name }}</h3>
                    <div class="agoda-card__meta">
                        <span class="agoda-card__stars">
                            @for($i = 0; $i < 5; $i++)<i class="fa-solid fa-star"></i>@endfor
                        </span>
                        <span class="agoda-card__dot">•</span>
                        <span class="agoda-card__location">
                            <i class="fa-solid fa-location-dot"></i> {{ $room->branch }}
                        </span>
                    </div>
                    <p class="agoda-card__price-note">Giá mỗi đêm chưa gồm thuế và phí</p>
                    <p class="agoda-card__price">VND {{ number_format($room->price, 0, ',', '.') }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>

@if(false) {{-- ẨN: Flash Sale cuối tuần --}}
{{-- ============================================================
     SECTION: WEEKEND FLASH DEALS (wide horizontal banner + 3 cards)
     ============================================================ --}}
<section class="lx-section lx-section--gray">
    <div class="lx-container">
        <div class="lx-section__header">
            <h2 class="lx-section__title">⚡ Flash Sale cuối tuần</h2>
            <a href="{{ route('rooms.index') }}" class="lx-section__link">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="flash-layout">
            {{-- Big banner --}}
            <a href="#" class="flash-banner">
                <img src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800&q=80" alt="Flash Sale" class="flash-banner__img">
                <div class="flash-banner__overlay">
                    <span class="flash-banner__badge">⚡ Flash Sale</span>
                    <h3 class="flash-banner__title">The Anam Resort Nha Trang</h3>
                    <p class="flash-banner__sub">Giảm tới 40% — Chỉ còn hôm nay!</p>
                    <div class="flash-banner__price">
                        <span class="flash-banner__old">3,500,000đ</span>
                        <span class="flash-banner__new">2,100,000đ</span>
                        <span class="flash-banner__per">/đêm</span>
                    </div>
                </div>
            </a>
            {{-- Side cards --}}
            <div class="flash-side">
                @php
                $flash = [
                    ['name' => 'Mia Resort Nha Trang', 'location' => 'Nha Trang', 'img' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400&q=80', 'price' => '1,750,000', 'old' => '2,500,000', 'score' => '9.1'],
                    ['name' => 'InterContinental Đà Nẵng', 'location' => 'Đà Nẵng', 'img' => 'https://images.unsplash.com/photo-1455587734955-081b22074882?w=400&q=80', 'price' => '3,900,000', 'old' => '5,200,000', 'score' => '9.4'],
                    ['name' => 'Fusion Suites Đà Nẵng Beach', 'location' => 'Đà Nẵng', 'img' => 'https://images.unsplash.com/photo-1549294413-26f195200c16?w=400&q=80', 'price' => '1,400,000', 'old' => '2,000,000', 'score' => '8.8'],
                ];
                @endphp
                @foreach($flash as $f)
                <a href="#" class="flash-card">
                    <img src="{{ $f['img'] }}" alt="{{ $f['name'] }}" class="flash-card__img">
                    <div class="flash-card__body">
                        <h4 class="flash-card__name">{{ $f['name'] }}</h4>
                        <p class="flash-card__location"><i class="fa-solid fa-location-dot"></i> {{ $f['location'] }}</p>
                        <div class="flash-card__price-row">
                            <span class="flash-card__old">{{ $f['old'] }}đ</span>
                            <span class="flash-card__new">{{ $f['price'] }}đ</span>
                        </div>
                    </div>
                    <span class="flash-card__score">{{ $f['score'] }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- ============================================================
     WHY LUXNEST
     ============================================================ --}}
<section class="lx-section">
    <div class="lx-container">
        <h2 class="lx-section__title lx-section__title--center">Tại sao chọn LuxNest?</h2>
        <div class="features-grid">
            @php
            $features = [
                ['icon' => 'fa-shield-halved',    'color' => '#0ea5e9', 'title' => 'Đặt phòng an toàn',    'desc' => 'Thanh toán bảo mật 100%, hủy phòng linh hoạt theo chính sách.'],
                ['icon' => 'fa-tag',              'color' => '#10b981', 'title' => 'Giá tốt nhất',         'desc' => 'Cam kết giá tốt nhất. Tìm thấy rẻ hơn? Chúng tôi hoàn tiền!'],
                ['icon' => 'fa-headset',          'color' => '#f59e0b', 'title' => 'Hỗ trợ 24/7',         'desc' => 'Đội ngũ hỗ trợ luôn sẵn sàng giải đáp mọi thắc mắc.'],
                ['icon' => 'fa-star',             'color' => '#8b5cf6', 'title' => 'Đánh giá thực',        'desc' => 'Hơn 500,000 đánh giá chân thực từ khách hàng đã lưu trú.'],
            ];
            @endphp

            @foreach($features as $f)
            <div class="feature-card">
                <div class="feature-card__icon" style="background: {{ $f['color'] }}20; color: {{ $f['color'] }}">
                    <i class="fa-solid {{ $f['icon'] }}"></i>
                </div>
                <h4 class="feature-card__title">{{ $f['title'] }}</h4>
                <p class="feature-card__desc">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>



@endsection

@push('scripts')
<script>
    // Category tab switching
    document.querySelectorAll('.sw2__tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.sw2__tab').forEach(t => t.classList.remove('sw2__tab--active'));
            this.classList.add('sw2__tab--active');
        });
    });

    // Sub-tab switching
    document.querySelectorAll('.sw2__sub-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.sw2__sub-tab').forEach(t => t.classList.remove('sw2__sub-tab--active'));
            this.classList.add('sw2__sub-tab--active');
        });
    });

    // Date picker for sw2
    (function(){
        const f1 = document.getElementById('sw2FieldCheckin');
        const f2 = document.getElementById('sw2FieldCheckout');
        const ciIn = document.getElementById('sw2HiddenCheckin');
        const coIn = document.getElementById('sw2HiddenCheckout');
        const vn_days = ['CN','T2','T3','T4','T5','T6','T7'];
        function fmtVN(s){ const d=new Date(s); return d.getDate()+' tháng '+(d.getMonth()+1)+' '+d.getFullYear(); }

        if(typeof Litepicker !== 'undefined'){
            const anchor = document.createElement('input');
            anchor.type = 'text';
            anchor.style.cssText = 'position:absolute;opacity:0;pointer-events:none;width:1px;height:1px;bottom:0;left:0;';
            f1 && f1.style.setProperty('position','relative');
            f1 && f1.appendChild(anchor);

            const picker = new Litepicker({
                element: anchor,
                singleMode: false,
                format: 'YYYY-MM-DD',
                numberOfMonths: window.innerWidth > 768 ? 2 : 1,
                numberOfColumns: window.innerWidth > 768 ? 2 : 1,
                minDate: new Date(),
                autoApply: true,
                setup: p => {
                    p.on('selected', (d1, d2) => {
                        const s1 = d1.format('YYYY-MM-DD'), s2 = d2.format('YYYY-MM-DD');
                        ciIn.value = s1; coIn.value = s2;
                        document.getElementById('sw2Checkin').textContent = fmtVN(s1);
                        document.getElementById('sw2Checkout').textContent = fmtVN(s2);
                        document.getElementById('sw2CheckinDay').textContent  = vn_days[new Date(s1).getDay()];
                        document.getElementById('sw2CheckoutDay').textContent = vn_days[new Date(s2).getDay()];
                    });
                }
            });
            [f1, f2].forEach(f => f && f.addEventListener('click', e => {
                if (e.target.classList.contains('sw2-gc')) return;
                picker.show();
            }));
        }

        // Guest counter — append to body to avoid overflow:hidden clipping
        const gf = document.getElementById('sw2GuestField');
        let sw2Adults=2, sw2Children=0;
        if(gf){
            const drop = document.createElement('div');
            drop.id = 'sw2GuestDrop';
            drop.style.cssText = 'display:none;position:fixed;background:#fff;border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.15);border:1px solid #e2e8f0;padding:16px;z-index:99999;min-width:260px;';
            drop.innerHTML = `
              <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                <div><div style="font-weight:600;font-size:.9rem">Người lớn</div><div style="font-size:.75rem;color:#94a3b8">Từ 13 tuổi</div></div>
                <div style="display:flex;align-items:center;gap:10px;">
                  <button type="button" class="sw2-gc minus" data-type="adults" style="width:30px;height:30px;border-radius:50%;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;font-size:1rem;font-weight:700;color:#1a3a6b;">−</button>
                  <span class="sw2-gc-num" data-type="adults" style="width:20px;text-align:center;font-weight:700;">2</span>
                  <button type="button" class="sw2-gc plus" data-type="adults" style="width:30px;height:30px;border-radius:50%;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;font-size:1rem;font-weight:700;color:#1a3a6b;">+</button>
                </div>
              </div>
              <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;">
                <div><div style="font-weight:600;font-size:.9rem">Trẻ em</div><div style="font-size:.75rem;color:#94a3b8">2–12 tuổi</div></div>
                <div style="display:flex;align-items:center;gap:10px;">
                  <button type="button" class="sw2-gc minus" data-type="children" style="width:30px;height:30px;border-radius:50%;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;font-size:1rem;font-weight:700;color:#1a3a6b;">−</button>
                  <span class="sw2-gc-num" data-type="children" style="width:20px;text-align:center;font-weight:700;">0</span>
                  <button type="button" class="sw2-gc plus" data-type="children" style="width:30px;height:30px;border-radius:50%;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;font-size:1rem;font-weight:700;color:#1a3a6b;">+</button>
                </div>
              </div>`;
            document.body.appendChild(drop);

            function positionDrop(){
                const r = gf.getBoundingClientRect();
                drop.style.top  = (r.bottom + 8) + 'px';
                drop.style.left = Math.max(8, r.left) + 'px';
            }

            function updateSw2Guests(){
                document.getElementById('sw2GuestVal').textContent = sw2Adults + ' người lớn';
                const dayEls = document.querySelectorAll('.sw2__field-day');
                if(dayEls[2]) dayEls[2].textContent = sw2Children + ' trẻ em';
                document.getElementById('sw2HiddenAdults').value   = sw2Adults;
                document.getElementById('sw2HiddenChildren').value = sw2Children;
                drop.querySelectorAll('.sw2-gc.minus').forEach(b => { b.disabled = b.dataset.type==='adults' ? sw2Adults<=1 : sw2Children<=0; });
                drop.querySelectorAll('.sw2-gc-num').forEach(el => { el.textContent = el.dataset.type==='adults' ? sw2Adults : sw2Children; });
            }

            gf.addEventListener('click', e => {
                if(e.target.classList.contains('sw2-gc')) return;
                e.stopPropagation();
                const isOpen = drop.style.display === 'block';
                if(!isOpen){ positionDrop(); drop.style.display = 'block'; }
                else drop.style.display = 'none';
            });
            drop.querySelectorAll('.sw2-gc').forEach(btn => {
                btn.addEventListener('click', e => {
                    e.stopPropagation();
                    const t = btn.dataset.type;
                    if(btn.classList.contains('plus')){ if(t==='adults') sw2Adults++; else sw2Children++; }
                    else { if(t==='adults'&&sw2Adults>1) sw2Adults--; else if(t==='children'&&sw2Children>0) sw2Children--; }
                    updateSw2Guests();
                });
            });
            document.addEventListener('click', e => { if(!gf.contains(e.target) && !drop.contains(e.target)) drop.style.display = 'none'; });
            window.addEventListener('scroll', () => { drop.style.display = 'none'; }, { passive: true });
            updateSw2Guests();
        }
    })();
</script>
@endpush
