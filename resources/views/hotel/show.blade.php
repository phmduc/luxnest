@extends('layouts.app')

@section('title', $hotel['name'] . ' - LuxNest')

@php
    $ogDesc  = mb_substr(strip_tags($hotel['description'] ?? ''), 0, 160);
    $ogImage = $hotel['images'][0] ?? '';
@endphp
@section('meta_description', $ogDesc)
@section('og_title',       $hotel['name'] . ' - LuxNest')
@section('og_description', $ogDesc)
@section('og_image',       $ogImage)

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/css/hotel-detail.css') }}">
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

    /* ── Mobile sticky booking bar ── */
    .mobile-book-bar {
        display: none;
    }
    @media (max-width: 1024px) {
        .mobile-book-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            padding: 12px 20px;
            z-index: 200;
            box-shadow: 0 -4px 20px rgba(0,0,0,.1);
            gap: 16px;
        }
        .mobile-book-bar__price {
            display: flex;
            flex-direction: column;
        }
        .mobile-book-bar__amount {
            font-size: 1.2rem;
            font-weight: 800;
            color: #1a1a1a;
            line-height: 1.2;
        }
        .mobile-book-bar__note {
            font-size: .72rem;
            color: #888;
        }
        .mobile-book-bar__btn {
            background: #996d4e;
            color: #fff;
            border: none;
            padding: 13px 28px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            white-space: nowrap;
        }
        /* Avoid content hidden behind sticky bar */
        main { padding-bottom: 80px; }
    }
    </style>
@endpush

@section('content')
<main class="lx-main" style="padding-top:20px;">

    {{-- BREADCRUMB --}}
    <div class="hd-breadcrumb lx-container">
        <a href="{{ url('/') }}">Trang chủ</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('rooms.index', ['keyword' => $hotel['branch']]) }}">{{ $hotel['branch'] }}</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span>{{ $hotel['name'] }}</span>
    </div>

    {{-- HEADER --}}
    <div class="hd-header lx-container">
        <div class="hd-header__left">
            <h1 class="hd-title">{{ $hotel['name'] }}
                <span class="hd-stars">
                    @for($i = 0; $i < $hotel['stars']; $i++)
                        <i class="fa-solid fa-star"></i>
                    @endfor
                </span>
            </h1>
            <p class="hd-address">
                <i class="fa-solid fa-location-dot"></i> {{ $hotel['address'] }}
            </p>
        </div>
        <div class="hd-header__right">
            <button class="hd-action-btn" id="btn-save" onclick="toggleSave(this,'{{ $room->slug }}')"><i class="fa-regular fa-heart"></i> Lưu</button>
            <button class="hd-action-btn" onclick="sharePage()"><i class="fa-solid fa-share-nodes"></i> Chia sẻ</button>
        </div>
    </div>

    {{-- GALLERY --}}
    @php
        $hasVideo    = !empty($room->video);
        $thumbImages = $hasVideo ? $hotel['images'] : array_slice($hotel['images'], 1);
    @endphp
    <div class="hd-gallery lx-container">
        <div class="hd-gallery__main">
            @if($hasVideo && $room->isYoutubeVideo())
                <iframe src="{{ $room->youtube_embed_url }}"
                        style="width:100%;height:100%;display:block;border:0;"
                        allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
            @elseif($hasVideo)
                <video src="{{ $room->video }}" autoplay muted loop playsinline controls
                       style="width:100%;height:100%;object-fit:cover;display:block;background:#000;"></video>
            @else
                <a href="{{ $hotel['images'][0] ?? '' }}" data-fancybox="gallery" data-caption="{{ $hotel['name'] }}">
                    <img src="{{ $hotel['images'][0] ?? '' }}" alt="{{ $hotel['name'] }}">
                </a>
            @endif
        </div>
        @if(count($thumbImages) > 0)
        <div class="hd-gallery__grid">
            @foreach(array_slice($thumbImages, 0, 4) as $idx => $img)
                <div class="hd-gallery__item @if($idx == 3) hd-gallery__item--last @endif">
                    <a href="{{ $img }}" data-fancybox="gallery" data-caption="{{ $hotel['name'] }} - Ảnh {{ $idx + 1 }}">
                        <img src="{{ $img }}" alt="Gallery {{ $idx + 1 }}">
                        @if($idx == 3 && count($thumbImages) > 4)
                            <div class="hd-gallery__more">
                                <span>+{{ count($thumbImages) - 4 }} Ảnh</span>
                            </div>
                        @endif
                    </a>
                </div>
            @endforeach
        </div>
        @endif

        {{-- Hidden links so remaining images are reachable via lightbox slide --}}
        @if(count($thumbImages) > 4)
            @foreach(array_slice($thumbImages, 4) as $idx => $img)
                <a href="{{ $img }}" data-fancybox="gallery" data-caption="{{ $hotel['name'] }} - Ảnh {{ $idx + 5 }}" style="display:none;"></a>
            @endforeach
        @endif
    </div>

    {{-- STICKY NAV --}}
    <div class="hd-nav-wrap">
        <div class="hd-nav lx-container">
            <a href="#overview" class="hd-nav__item active">Tổng quan</a>
            <a href="#rooms"    class="hd-nav__item">Phòng</a>
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
                        <h2 class="hd-section__title">Về chỗ nghỉ này</h2>
                        <div style="line-height:1.75;color:#444;">
                            {!! nl2br(e($hotel['description'])) !!}
                        </div>
                    </div>
                    @if(!empty($hotel['highlights']))
                    <div class="hd-highlights">
                        <h3>Tiện nghi nổi bật</h3>
                        <ul>
                            @foreach($hotel['highlights'] as $hl)
                                <li><i class="{{ $hl['icon'] }}"></i> {{ $hl['text'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </section>

            {{-- SEARCH BAR WIDGET --}}
            <section class="hd-section hd-search-bar" id="check-room">
                <div class="hd-search-bar__inner">
                    <div class="hd-search-field" id="hdDateField" style="cursor:pointer;flex:2;">
                        <i class="fa-regular fa-calendar"></i>
                        <div class="hd-search-field__text">
                            <span class="hd-search-label">Ngày nhận - trả phòng</span>
                            <span class="hd-search-val" id="hdDateDisplay">
                                {{ (request('check_in') && request('check_out'))
                                    ? \Carbon\Carbon::parse(request('check_in'))->format('d/m').' → '.\Carbon\Carbon::parse(request('check_out'))->format('d/m/Y')
                                    : 'Chọn ngày' }}
                            </span>
                        </div>
                    </div>
                    <div class="hd-search-field" id="hdGuestField" style="cursor:pointer;position:relative;">
                        <i class="fa-solid fa-user-group"></i>
                        <div class="hd-search-field__text">
                            <span class="hd-search-label">Khách</span>
                            <span class="hd-search-val" id="hdGuestDisplay">{{ max(1,(int)request('adults',1)) }} người lớn</span>
                        </div>
                    </div>
                    <input type="hidden" id="hdCheckIn"  value="{{ request('check_in','') }}">
                    <input type="hidden" id="hdCheckOut" value="{{ request('check_out','') }}">
                    <input type="hidden" id="hdAdults"   value="{{ max(1,(int)request('adults',1)) }}">
                    <button class="hd-search-btn" id="hdCheckBtn" type="button">Kiểm tra phòng</button>
                </div>

                {{-- Availability result --}}
                <div id="availResult" style="display:none;">
                    <div id="availOk" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <i class="fa-solid fa-circle-check" style="color:#22c55e;font-size:1.3rem;flex-shrink:0;"></i>
                            <strong style="color:#166534;">Còn phòng!</strong>
                            <span style="color:#166534;font-size:.9rem;" id="availPrice"></span>
                        </div>
                        <a id="availBookBtn" href="#" class="hd-room__book-btn" style="white-space:nowrap;padding:8px 18px;width:max-content;flex-shrink:0;">Đặt ngay</a>
                    </div>
                    <div id="availNo" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:14px 18px;display:flex;align-items:center;gap:12px;">
                        <i class="fa-solid fa-circle-xmark" style="color:#ef4444;font-size:1.3rem;"></i>
                        <span style="color:#dc2626;font-weight:600;">Hết phòng cho ngày này</span>
                    </div>
                </div>
            </section>

            {{-- ROOMS --}}
            <section id="rooms" class="hd-section">
                <h2 class="hd-section__title">
                    {{ count($hotel['rooms']) > 1 ? 'Các phòng tại ' . $hotel['branch'] : 'Thông tin phòng' }}
                </h2>
                <div class="hd-rooms">
                    @foreach($hotel['rooms'] as $r)
                    <div class="hd-room">
                        <div class="hd-room__left">
                            <div class="hd-room__img">
                                <img src="{{ $r['image'] }}" alt="{{ $r['name'] }}">
                            </div>
                            <div class="hd-room__info">
                                <h3>{{ $r['name'] }}</h3>
                                @if(!empty($r['amenities']))
                                <div class="hd-room__features">
                                    @foreach(array_slice($r['amenities'], 0, 5) as $am)
                                        <span><i class="fa-solid fa-check"></i> {{ $am }}</span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="hd-room__right">
                            <div class="hd-room__price-box">
                                @if($r['old_price'])
                                <span class="hd-room__old-price">{{ number_format($r['old_price'], 0, ',', '.') }} ₫</span>
                                @endif
                                <span class="hd-room__price">{{ number_format($r['price'], 0, ',', '.') }} ₫</span>
                                <span class="hd-room__price-note">Mỗi đêm, chưa gồm thuế & phí</span>
                            </div>
                            @if($r['slug'] === $room->slug)
                            <a href="#check-room" class="hd-room__book-btn book-scroll-btn">
                                Đặt ngay
                            </a>
                            @else
                            <a href="{{ route('hotel.show', $r['slug']) }}" class="hd-room__book-btn" style="background:#1a1a1a;text-align:center;">
                                Xem phòng
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>

            {{-- POLICIES --}}
            <section id="policies" class="hd-section">
                <h2 class="hd-section__title">Chính sách lưu trú</h2>

                <div class="policy-grid">

                    {{-- Check-in / out --}}
                    <div style="background:#f8fafc;border-radius:14px;padding:20px;">
                        <h4 style="margin:0 0 14px;font-size:1rem;color:#1a1a1a;display:flex;align-items:center;gap:8px;">
                            <i class="fa-regular fa-clock"></i> Giờ nhận & trả phòng
                        </h4>
                        <div style="display:flex;flex-direction:column;gap:10px;font-size:.9rem;color:#444;">
                            <div style="display:flex;justify-content:space-between;">
                                <span>Nhận phòng (Check-in)</span>
                                <strong>Từ 14:00</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;">
                                <span>Trả phòng (Check-out)</span>
                                <strong>Trước 12:00</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;">
                                <span>Nhận phòng sớm</span>
                                <strong style="color:#888;">Liên hệ trước</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;">
                                <span>Trả phòng muộn</span>
                                <strong style="color:#888;">Liên hệ trước</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Cancellation --}}
                    <div style="background:#f8fafc;border-radius:14px;padding:20px;">
                        <h4 style="margin:0 0 14px;font-size:1rem;color:#1a1a1a;display:flex;align-items:center;gap:8px;">
                            <i class="fa-solid fa-rotate-left"></i> Chính sách hủy phòng
                        </h4>
                        <div style="display:flex;flex-direction:column;gap:10px;font-size:.9rem;color:#444;">
                            <div style="display:flex;align-items:flex-start;gap:8px;">
                                <i class="fa-solid fa-circle-check" style="color:#22c55e;margin-top:2px;flex-shrink:0;"></i>
                                <span>Hủy <strong>miễn phí</strong> trước 48 giờ nhận phòng</span>
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
                                <span>Đặt cọc 30% khi đặt phòng</span>
                            </div>
                        </div>
                    </div>

                    {{-- Rules --}}
                    <div style="background:#f8fafc;border-radius:14px;padding:20px;">
                        <h4 style="margin:0 0 14px;font-size:1rem;color:#1a1a1a;display:flex;align-items:center;gap:8px;">
                            <i class="fa-solid fa-scroll"></i> Nội quy chỗ ở
                        </h4>
                        <div style="display:flex;flex-direction:column;gap:10px;font-size:.9rem;color:#444;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <i class="fa-solid fa-ban" style="color:#ef4444;"></i>
                                <span>Không hút thuốc trong phòng</span>
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
                        <p style="margin:4px 0 0;font-size:.85rem;color:#cbd5e1;">Liên hệ LuxNest 24/7 qua hotline hoặc chat để được tư vấn đặt phòng và giải đáp mọi thắc mắc.</p>
                    </div>
                    <a href="tel:+84123456789"
                       style="flex-shrink:0;background:#996d4e;color:#fff;padding:10px 20px;border-radius:10px;font-weight:700;font-size:.85rem;text-decoration:none;white-space:nowrap;">
                        Gọi ngay
                    </a>
                </div>
            </section>

        </div>

        {{-- RIGHT SIDEBAR --}}
        <div class="hd-sidebar">
            {{-- Price summary card --}}
            <div class="hd-sidebar__box" style="text-align:center; padding:24px;">
                <p style="font-size:.85rem;color:#888;margin-bottom:6px;">Giá từ</p>
                <div style="font-size:1.8rem;font-weight:800;color:#1a1a1a;">
                    {{ number_format($room->price, 0, ',', '.') }}₫
                </div>
                <p style="font-size:.8rem;color:#888;margin-bottom:20px;">/ đêm, chưa gồm thuế & phí</p>
                <a href="#check-room"
                   style="display:block;background:#996d4e;color:#fff;padding:13px;border-radius:10px;font-weight:700;font-size:1rem;text-decoration:none;text-align:center;">
                    Đặt phòng ngay
                </a>
            </div>

            <div class="hd-sidebar__box hd-map-box">
                <div class="hd-map-placeholder">
                    <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?w=400&q=80" alt="Map">
                    <button class="hd-map-btn">Xem bản đồ</button>
                </div>
                <div class="hd-map-info">
                    <p><strong>{{ $hotel['branch'] }} — LuxNest</strong></p>
                    <p>Liên hệ để biết vị trí chi tiết</p>
                </div>
            </div>
        </div>

    </div>

    {{-- Mobile sticky booking bar --}}
    <div class="mobile-book-bar">
        <div class="mobile-book-bar__price">
            <span class="mobile-book-bar__amount">{{ number_format($room->price, 0, ',', '.') }}₫</span>
            <span class="mobile-book-bar__note">/ đêm, chưa gồm thuế & phí</span>
        </div>
        <a href="#check-room" class="mobile-book-bar__btn">Đặt phòng</a>
    </div>

</main>
@push('scripts')
<script>
// ── Date picker for hotel detail ─────────────────────────────
(function(){
    const dateField  = document.getElementById('hdDateField');
    const ciIn       = document.getElementById('hdCheckIn');
    const coIn       = document.getElementById('hdCheckOut');
    const dateDisp   = document.getElementById('hdDateDisplay');
    function fmtD(s){ const d=new Date(s); return d.getDate().toString().padStart(2,'0')+'/'+(d.getMonth()+1).toString().padStart(2,'0')+'/'+d.getFullYear(); }

    if(typeof Litepicker !== 'undefined'){
        const dummy = document.createElement('input');
        dummy.type = 'text';
        dummy.style.cssText = 'position:absolute;opacity:0;pointer-events:none;width:1px;height:1px;bottom:0;left:0;';
        if(dateField){ dateField.style.position = 'relative'; dateField.appendChild(dummy); }
        const picker = new Litepicker({
            element: dummy, singleMode: false, format: 'YYYY-MM-DD',
            numberOfMonths: window.innerWidth>768?2:1, numberOfColumns: window.innerWidth>768?2:1,
            minDate: new Date(), autoApply: true,
            setup: p => {
                p.on('selected', (d1,d2) => {
                    ciIn.value=d1.format('YYYY-MM-DD'); coIn.value=d2.format('YYYY-MM-DD');
                    dateDisp.textContent = fmtD(d1.format('YYYY-MM-DD'))+' → '+fmtD(d2.format('YYYY-MM-DD'));
                });
            }
        });
        dateField && dateField.addEventListener('click', ()=>picker.show());
    }

    // Guest counter
    const gf = document.getElementById('hdGuestField');
    const adIn = document.getElementById('hdAdults');
    let hdAdults = parseInt(adIn?.value)||1;
    if(gf){
        const drop = document.createElement('div');
        drop.style.cssText='display:none;position:absolute;top:calc(100% + 8px);left:0;background:#fff;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.12);border:1px solid #e2e8f0;padding:14px;z-index:999;min-width:220px;';
        drop.innerHTML=`<div style="display:flex;justify-content:space-between;align-items:center;gap:14px;"><span style="font-weight:600;font-size:.9rem;">Người lớn</span><div style="display:flex;align-items:center;gap:10px;"><button type="button" id="hdMinus" style="width:28px;height:28px;border-radius:50%;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;font-size:1rem;font-weight:700;color:#1a1a1a;">−</button><span id="hdAdultNum" style="width:18px;text-align:center;font-weight:700;">${hdAdults}</span><button type="button" id="hdPlus" style="width:28px;height:28px;border-radius:50%;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;font-size:1rem;font-weight:700;color:#1a1a1a;">+</button></div></div>`;
        gf.style.position='relative'; gf.appendChild(drop);

        function upHdGuests(){ document.getElementById('hdGuestDisplay').textContent=hdAdults+' người lớn'; adIn.value=hdAdults; document.getElementById('hdMinus').disabled=hdAdults<=1; document.getElementById('hdAdultNum').textContent=hdAdults; }
        gf.addEventListener('click', e=>{ if(['hdMinus','hdPlus'].includes(e.target.id)) return; e.stopPropagation(); drop.style.display=drop.style.display==='none'?'block':'none'; });
        document.getElementById('hdMinus')?.addEventListener('click', e=>{ e.stopPropagation(); if(hdAdults>1){hdAdults--;upHdGuests();} });
        document.getElementById('hdPlus')?.addEventListener('click',  e=>{ e.stopPropagation(); hdAdults++;upHdGuests(); });
        document.addEventListener('click', e=>{ if(!gf.contains(e.target)) drop.style.display='none'; });
        upHdGuests();
    }
})();

// ── Availability check ────────────────────────────────────────
document.getElementById('hdCheckBtn')?.addEventListener('click', async function() {
    const ci  = document.getElementById('hdCheckIn')?.value;
    const co  = document.getElementById('hdCheckOut')?.value;
    const adl = document.getElementById('hdAdults')?.value || 1;
    if (!ci || !co) { alert('Vui lòng chọn ngày nhận và trả phòng'); return; }

    this.textContent = 'Đang kiểm tra...';
    this.disabled = true;
    document.getElementById('availResult').style.display = 'none';

    try {
        const res  = await fetch(`{{ route('api.availability') }}?slug={{ $room->slug }}&check_in=${ci}&check_out=${co}&adults=${adl}`);
        const data = await res.json();
        document.getElementById('availResult').style.display = 'block';
        if (data.available) {
            document.getElementById('availOk').style.display = 'flex';
            document.getElementById('availNo').style.display = 'none';
            document.getElementById('availPrice').textContent = new Intl.NumberFormat('vi-VN').format(data.price) + '₫/đêm';
            const bookUrl = `{{ route('booking.show') }}?slug={{ $room->slug }}&checkin=${ci}&checkout=${co}&guests=${adl}`;
            const btn = document.getElementById('availBookBtn');
            btn.href = bookUrl;
            btn.onclick = function(e){ e.preventDefault(); e.stopPropagation(); window.location.href = bookUrl; };
        } else {
            document.getElementById('availOk').style.display = 'none';
            document.getElementById('availNo').style.display = 'flex';
        }
    } catch { alert('Lỗi kết nối, thử lại sau.'); }
    finally { this.textContent = 'Kiểm tra phòng'; this.disabled = false; }
});

// ── Scroll to #check-room — center on screen ─────────────────
document.querySelectorAll('a[href="#check-room"], .book-scroll-btn').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        const target = document.getElementById('check-room');
        if (!target) return;
        const targetRect = target.getBoundingClientRect();
        const targetCenter = window.scrollY + targetRect.top + targetRect.height / 2;
        const scrollTo = targetCenter - window.innerHeight / 2;
        window.scrollTo({ top: scrollTo, behavior: 'smooth' });
    });
});

// ── Highlight active nav tab on scroll ───────────────────────
const navItems = document.querySelectorAll('.hd-nav__item[href^="#"]');
const sections = ['overview', 'rooms', 'policies'].map(id => document.getElementById(id)).filter(Boolean);

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

    // Init save state
    const saveBtn = document.getElementById('btn-save');
    const slug    = saveBtn ? saveBtn.getAttribute('onclick').match(/'([^']+)'/)?.[1] : null;
    if (slug && JSON.parse(localStorage.getItem('lx_saved')||'[]').includes(slug)) {
        saveBtn.innerHTML = '<i class="fa-solid fa-heart" style="color:#ef4444"></i> Đã lưu';
    }
});

function toggleSave(btn, slug) {
    const saved = JSON.parse(localStorage.getItem('lx_saved') || '[]');
    const idx   = saved.indexOf(slug);
    if (idx > -1) {
        saved.splice(idx, 1);
        btn.innerHTML = '<i class="fa-regular fa-heart"></i> Lưu';
        showToast('Đã bỏ lưu');
    } else {
        saved.push(slug);
        btn.innerHTML = '<i class="fa-solid fa-heart" style="color:#ef4444"></i> Đã lưu';
        showToast('Đã lưu!');
    }
    localStorage.setItem('lx_saved', JSON.stringify(saved));
}

async function sharePage() {
    const url   = window.location.href;
    const title = document.title;
    if (navigator.share) {
        try { await navigator.share({ title, url }); } catch {}
    } else {
        await navigator.clipboard.writeText(url);
        showToast('Đã copy link!');
    }
}

function showToast(msg) {
    let t = document.getElementById('lx-toast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'lx-toast';
        t.style.cssText = 'position:fixed;bottom:28px;left:50%;transform:translateX(-50%);background:#1a1a1a;color:#fff;padding:10px 22px;border-radius:99px;font-size:.9rem;font-weight:600;z-index:9999;opacity:0;transition:opacity .25s;pointer-events:none;white-space:nowrap;';
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.style.opacity = '1';
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.style.opacity = '0', 2200);
}
</script>
@endpush

@endsection
