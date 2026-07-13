@extends('layouts.app')

@section('title', 'Đặt phòng — ' . $room->name . ' - LuxNest')
@section('main_class', 'lx-main--no-padding')

@push('styles')
<style>
.bk-page {
    background: #f5f6fa;
    min-height: 100vh;
    padding: 80px 0 60px;
}
.bk-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 20px;
}
.bk-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #1a1a1a;
    font-size: .9rem;
    font-weight: 600;
    text-decoration: none;
    margin-bottom: 24px;
}
.bk-back:hover { text-decoration: underline; }
.bk-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 28px;
    align-items: start;
}

/* ── Left: Form ── */
.bk-card {
    background: #fff;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 2px 12px rgba(0,0,0,.05);
    margin-bottom: 20px;
}
.bk-card h2 {
    font-size: 1.2rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 24px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.bk-card h2 .bk-step {
    width: 30px; height: 30px;
    background: #1a1a1a;
    color: #fff;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; font-weight: 700; flex-shrink: 0;
}
.bk-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.bk-field { display: flex; flex-direction: column; gap: 6px; }
.bk-field.full { grid-column: 1 / -1; }
.bk-label {
    font-size: .82rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.bk-label .req { color: #ef4444; }
.bk-input, .bk-textarea {
    padding: 12px 16px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: .95rem;
    font-family: inherit;
    color: #0f172a;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    background: #fff;
    width: 100%;
    box-sizing: border-box;
}
.bk-input:focus, .bk-textarea:focus {
    border-color: #1a1a1a;
    box-shadow: 0 0 0 3px rgba(26,26,26,.08);
}
.bk-textarea { resize: vertical; min-height: 90px; }

/* Cancellation policy */
.bk-policy {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 12px;
    padding: 16px 20px;
    font-size: .88rem;
    color: #1e3a5f;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}
.bk-policy i { font-size: 1.2rem; color: #1a1a1a; flex-shrink: 0; margin-top: 1px; }

/* Submit button */
.bk-submit {
    width: 100%;
    padding: 16px;
    background: #996d4e;
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 1.05rem;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: background .2s, transform .15s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.bk-submit:hover { background: #7a573e; transform: translateY(-1px); }
.bk-secure {
    text-align: center;
    font-size: .78rem;
    color: #94a3b8;
    margin-top: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

/* ── Right: Summary ── */
.bk-summary {
    position: sticky;
    top: 90px;
}
.bk-summary-card {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,.05);
    margin-bottom: 16px;
}
.bk-room-row {
    display: flex;
    gap: 14px;
    margin-bottom: 20px;
}
.bk-room-img {
    width: 90px; height: 70px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
}
.bk-room-meta { flex: 1; }
.bk-room-branch {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .8px;
    color: #1a1a1a;
    margin-bottom: 4px;
}
.bk-room-name {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
    margin-bottom: 4px;
}
.bk-room-price-note {
    font-size: .8rem;
    color: #64748b;
}

.bk-dates {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    background: #f8fafc;
    border-radius: 10px;
    padding: 14px;
    margin-bottom: 16px;
}
.bk-date-col label {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #94a3b8;
    display: block;
    margin-bottom: 4px;
}
.bk-date-col span {
    font-size: .95rem;
    font-weight: 700;
    color: #0f172a;
}
.bk-nights-badge {
    text-align: center;
    font-size: .8rem;
    color: #64748b;
    padding: 8px 0;
    border-top: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 16px;
}
.bk-nights-badge strong { color: #0f172a; }

.bk-price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    font-size: .9rem;
    color: #555;
    border-bottom: 1px solid #f1f5f9;
}
.bk-price-row:last-child { border-bottom: none; }
.bk-price-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 0 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: #0f172a;
}
.bk-deposit-box {
    background: linear-gradient(135deg, #fff5f0, #fff8f5);
    border: 1.5px solid #996d4e;
    border-radius: 12px;
    padding: 16px;
    margin-top: 16px;
    text-align: center;
}
.bk-deposit-label {
    font-size: .78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #996d4e;
    margin-bottom: 6px;
}
.bk-deposit-amount {
    font-size: 1.5rem;
    font-weight: 900;
    color: #996d4e;
    line-height: 1;
}
.bk-deposit-note {
    font-size: .75rem;
    color: #888;
    margin-top: 6px;
}

/* Error */
.bk-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 10px;
    padding: 14px 18px;
    color: #dc2626;
    font-size: .9rem;
    font-weight: 600;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Responsive */
@media (max-width: 900px) {
    .bk-layout { grid-template-columns: 1fr; }
    .bk-summary { position: static; }
    .bk-summary-card:first-child { order: -1; }
}
@media (max-width: 540px) {
    .bk-card { padding: 20px 16px; }
    .bk-grid-2 { grid-template-columns: 1fr; }
    .bk-page { padding-top: 70px; }
}
</style>
@endpush

@section('content')
<div class="bk-page">
    <div class="bk-container">

        <a href="{{ route('hotel.show', $room->slug) }}" class="bk-back">
            <i class="fa-solid fa-chevron-left"></i> Quay lại trang phòng
        </a>

        <form action="{{ route('booking.store') }}" method="POST">
            @csrf
            <input type="hidden" name="slug"     value="{{ $room->slug }}">
            <input type="hidden" name="checkin"  value="{{ $checkin }}">
            <input type="hidden" name="checkout" value="{{ $checkout }}">
            <input type="hidden" name="guests"   value="{{ $guests }}">

            <div class="bk-layout">

                {{-- ===== LEFT: FORM ===== --}}
                <div>

                    {{-- Errors --}}
                    @if($errors->any())
                    <div class="bk-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        {{ $errors->first() }}
                    </div>
                    @endif

                    {{-- Step 1: Customer info --}}
                    <div class="bk-card">
                        <h2><span class="bk-step">1</span> Thông tin của bạn</h2>
                        <div class="bk-grid-2">
                            <div class="bk-field">
                                <label class="bk-label">Họ <span class="req">*</span></label>
                                <input type="text" name="last_name" class="bk-input"
                                       value="{{ old('last_name', optional($user)->name ? explode(' ', $user->name)[0] : '') }}"
                                       placeholder="Nguyễn" required>
                            </div>
                            <div class="bk-field">
                                <label class="bk-label">Tên <span class="req">*</span></label>
                                <input type="text" name="first_name" class="bk-input"
                                       value="{{ old('first_name', optional($user)->name ? (explode(' ', $user->name, 2)[1] ?? '') : '') }}"
                                       placeholder="Văn A" required>
                            </div>
                            <div class="bk-field">
                                <label class="bk-label">Email <span class="req">*</span></label>
                                <input type="email" name="email" class="bk-input"
                                       value="{{ old('email', optional($user)->email) }}"
                                       placeholder="example@email.com" required>
                            </div>
                            <div class="bk-field">
                                <label class="bk-label">Số điện thoại <span class="req">*</span></label>
                                <input type="tel" name="phone" class="bk-input"
                                       value="{{ old('phone') }}"
                                       placeholder="0901 234 567" required>
                            </div>
                            <div class="bk-field full">
                                <label class="bk-label">Yêu cầu đặc biệt <span style="font-weight:400;text-transform:none;font-size:.78rem;color:#94a3b8">(tuỳ chọn)</span></label>
                                <textarea name="special_request" class="bk-textarea"
                                          placeholder="Phòng tầng cao, cần cũi trẻ em, đến muộn...">{{ old('special_request') }}</textarea>
                            </div>

                            {{-- Voucher --}}
                            <div class="bk-field full">
                                <label class="bk-label">Mã voucher <span style="font-weight:400;text-transform:none;font-size:.78rem;color:#94a3b8">(tuỳ chọn)</span></label>
                                <div style="display:flex;gap:8px;align-items:flex-start;">
                                    <input type="text" id="bk-voucher-input" class="bk-input"
                                           placeholder="Nhập mã voucher" style="text-transform:uppercase;flex:1;"
                                           oninput="this.value=this.value.toUpperCase()">
                                    <button type="button" id="bk-voucher-btn"
                                            onclick="applyVoucher()"
                                            style="flex-shrink:0;padding:12px 18px;background:#1a1a1a;color:#fff;border:none;border-radius:10px;font-size:.88rem;font-weight:600;cursor:pointer;white-space:nowrap;">
                                        Áp dụng
                                    </button>
                                </div>
                                <div id="bk-voucher-msg" style="margin-top:7px;font-size:.82rem;display:none;"></div>
                                <input type="hidden" name="voucher_code" id="bk-voucher-code">
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Cancellation --}}
                    <div class="bk-card">
                        <h2><span class="bk-step">2</span> Chính sách huỷ phòng</h2>
                        <div class="bk-policy">
                            <i class="fa-solid fa-circle-check" style="color:#22c55e"></i>
                            <span>
                                <strong>Huỷ miễn phí</strong> trước 48 giờ nhận phòng.
                                Huỷ trong vòng 48 giờ hoặc không đến (no-show) sẽ mất 100% tiền đặt cọc.
                            </span>
                        </div>
                    </div>

                    {{-- Step 3: Payment --}}
                    <div class="bk-card">
                        <h2><span class="bk-step">3</span> Xác nhận & Thanh toán</h2>
                        <p style="color:#64748b;font-size:.9rem;margin-bottom:24px;line-height:1.7;">
                            Bằng cách nhấn <strong>"Xác nhận đặt phòng"</strong>, bạn đồng ý với
                            <a href="#" style="color:#1a1a1a;font-weight:600;">Điều khoản dịch vụ</a>
                            và <a href="#" style="color:#1a1a1a;font-weight:600;">Chính sách bảo mật</a> của LuxNest.
                            Chúng tôi sẽ liên hệ xác nhận qua email/SĐT trong vòng 15 phút.
                        </p>

                        <button type="submit" class="bk-submit" id="bk-submit-btn">
                            <i class="fa-solid fa-lock"></i>
                            Xác nhận đặt phòng — <span id="bk-submit-price">{{ number_format($totalPrice, 0, ',', '.') }}₫</span>
                        </button>
                        <p class="bk-secure">
                            <i class="fa-solid fa-shield-halved"></i>
                            Thông tin của bạn được bảo mật tuyệt đối
                        </p>
                    </div>

                </div>

                {{-- ===== RIGHT: SUMMARY ===== --}}
                <div class="bk-summary">

                    {{-- Room card --}}
                    <div class="bk-summary-card">
                        <div class="bk-room-row">
                            <img src="{{ $room->image }}" alt="{{ $room->name }}" class="bk-room-img">
                            <div class="bk-room-meta">
                                <div class="bk-room-branch">{{ $room->branch }}</div>
                                <div class="bk-room-name">{{ $room->name }}</div>
                                <div class="bk-room-price-note">
                                    {{ number_format($room->price, 0, ',', '.') }}₫ / đêm
                                </div>
                            </div>
                        </div>

                        {{-- Dates --}}
                        <div class="bk-dates">
                            <div class="bk-date-col">
                                <label>Nhận phòng</label>
                                <span>{{ $checkin ? \Carbon\Carbon::parse($checkin)->format('d/m/Y') : '—' }}</span>
                            </div>
                            <div class="bk-date-col" style="text-align:right">
                                <label>Trả phòng</label>
                                <span>{{ $checkout ? \Carbon\Carbon::parse($checkout)->format('d/m/Y') : '—' }}</span>
                            </div>
                        </div>
                        <div class="bk-nights-badge">
                            <strong>{{ $nights }}</strong> đêm · <strong>{{ $guests }}</strong> khách
                        </div>

                        {{-- Price breakdown --}}
                        <div class="bk-price-row">
                            <span>{{ number_format($room->price, 0, ',', '.') }}₫ × {{ $nights }} đêm</span>
                            <span id="bk-base-price-display">{{ number_format($totalPrice, 0, ',', '.') }}₫</span>
                        </div>
                        <div class="bk-price-row" id="bk-discount-row" style="display:none;">
                            <span id="bk-discount-label" style="color:#16a34a;">Voucher giảm giá</span>
                            <span id="bk-discount-display" style="color:#16a34a;font-weight:600;"></span>
                        </div>
                        <div class="bk-price-row">
                            <span>Phí dịch vụ</span>
                            <span style="color:#22c55e;font-weight:600;">Miễn phí</span>
                        </div>
                        <div class="bk-price-total">
                            <span>Tổng cộng</span>
                            <span id="bk-total-display">{{ number_format($totalPrice, 0, ',', '.') }}₫</span>
                        </div>

                        {{-- Deposit --}}
                        <div class="bk-deposit-box">
                            <div class="bk-deposit-label">Tổng thanh toán</div>
                            <div class="bk-deposit-amount" id="bk-deposit-amount-display">{{ number_format($totalPrice, 0, ',', '.') }}₫</div>
                            <div class="bk-deposit-note">Bao gồm thuế và phí dịch vụ</div>
                        </div>
                    </div>

                    {{-- Trust badges --}}
                    <div class="bk-summary-card" style="padding:16px 20px;">
                        <div style="display:flex;flex-direction:column;gap:10px;font-size:.84rem;color:#555;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <i class="fa-solid fa-shield-halved" style="color:#22c55e;font-size:1rem;width:20px;text-align:center;"></i>
                                Đặt phòng an toàn, bảo mật 100%
                            </div>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <i class="fa-solid fa-headset" style="color:#1a1a1a;font-size:1rem;width:20px;text-align:center;"></i>
                                Hỗ trợ 24/7 qua chat & hotline
                            </div>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <i class="fa-solid fa-rotate-left" style="color:#996d4e;font-size:1rem;width:20px;text-align:center;"></i>
                                Huỷ miễn phí trước 48 giờ
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>

    </div>
</div>
{{-- Confirm popup --}}
<div id="bk-confirm" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.6);backdrop-filter:blur(4px);z-index:99998;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#fff;border-radius:20px;padding:36px 32px;max-width:420px;width:100%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="width:64px;height:64px;background:#eff6ff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:1.8rem;">🏨</div>
        <h3 style="font-size:1.25rem;font-weight:800;color:#0f172a;margin:0 0 10px;">Xác nhận đặt phòng</h3>
        <p style="color:#64748b;font-size:.92rem;line-height:1.6;margin:0 0 6px;">
            <strong style="color:#0f172a;">{{ $room->name }}</strong>
        </p>
        @if($checkin && $checkout)
        <p style="color:#64748b;font-size:.9rem;margin:0 0 20px;">
            {{ \Carbon\Carbon::parse($checkin)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($checkout)->format('d/m/Y') }}
            · <strong>{{ $nights }} đêm</strong>
        </p>
        @endif
        <div style="background:#f8fafc;border-radius:12px;padding:14px 18px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;">
            <span style="color:#64748b;font-size:.9rem;">Tổng thanh toán</span>
            <strong style="font-size:1.1rem;color:#996d4e;">{{ number_format($totalPrice, 0, ',', '.') }}₫</strong>
        </div>
        <div style="display:flex;gap:10px;">
            <button type="button" id="bk-confirm-cancel"
                style="flex:1;padding:13px;border-radius:10px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#555;font-weight:600;font-size:.95rem;cursor:pointer;font-family:inherit;">
                Huỷ
            </button>
            <button type="button" id="bk-confirm-ok"
                style="flex:2;padding:13px;border-radius:10px;border:none;background:#996d4e;color:#fff;font-weight:700;font-size:.95rem;cursor:pointer;font-family:inherit;box-shadow:0 4px 14px rgba(153, 109, 78,.3);">
                Xác nhận & Đặt phòng
            </button>
        </div>
    </div>
</div>

{{-- Loading overlay --}}
<div id="bk-loading" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.7);backdrop-filter:blur(4px);z-index:99999;display:none;flex-direction:column;align-items:center;justify-content:center;gap:20px;">
    <div style="width:56px;height:56px;border:4px solid rgba(255,255,255,.2);border-top-color:#fff;border-radius:50%;animation:bk-spin 0.8s linear infinite;"></div>
    <div style="text-align:center;">
        <p style="color:#fff;font-size:1.1rem;font-weight:700;margin:0 0 6px;">Đang xử lý đặt phòng...</p>
        <p style="color:rgba(255,255,255,.7);font-size:.88rem;margin:0;">Vui lòng không đóng hoặc chuyển trang</p>
    </div>
</div>
<style>@keyframes bk-spin{to{transform:rotate(360deg)}}</style>

@push('scripts')
<script>
const bkForm    = document.querySelector('.bk-submit')?.closest('form');
const bkConfirm = document.getElementById('bk-confirm');
const bkLoading = document.getElementById('bk-loading');
let confirmed   = false;

// Click submit → show confirm popup (không submit form)
bkForm?.querySelector('.bk-submit')?.addEventListener('click', function(e) {
    if (!confirmed) {
        e.preventDefault();
        bkConfirm.style.display = 'flex';
    }
});

// Huỷ
document.getElementById('bk-confirm-cancel')?.addEventListener('click', function() {
    bkConfirm.style.display = 'none';
});

// Xác nhận → ẩn popup, show loading, submit form
document.getElementById('bk-confirm-ok')?.addEventListener('click', function() {
    bkConfirm.style.display = 'none';
    bkLoading.style.display = 'flex';
    confirmed = true;
    window.onbeforeunload = null;
    bkForm?.submit();
});

// Đóng confirm khi click ra ngoài
bkConfirm?.addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});

// ── Voucher ──────────────────────────────────────────────
const BASE_PRICE = {{ $totalPrice }};
let appliedDiscount = 0;

function formatVND(n) {
    return new Intl.NumberFormat('vi-VN').format(n) + '₫';
}

function updatePriceDisplay(discount) {
    const finalPrice = Math.max(0, BASE_PRICE - discount);
    document.getElementById('bk-total-display').textContent       = formatVND(finalPrice);
    document.getElementById('bk-deposit-amount-display').textContent = formatVND(finalPrice);
    document.getElementById('bk-submit-price').textContent        = formatVND(finalPrice);
}

async function applyVoucher() {
    const code  = document.getElementById('bk-voucher-input').value.trim().toUpperCase();
    const msgEl = document.getElementById('bk-voucher-msg');
    const btn   = document.getElementById('bk-voucher-btn');

    if (!code) {
        msgEl.style.display = 'block';
        msgEl.style.color   = '#dc2626';
        msgEl.textContent   = 'Vui lòng nhập mã voucher.';
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Đang kiểm tra...';

    try {
        const res = await fetch('{{ route("voucher.validate") }}?code=' + encodeURIComponent(code) + '&amount=' + BASE_PRICE);
        const data = await res.json();

        msgEl.style.display = 'block';

        if (data.success) {
            appliedDiscount = data.discount;

            // Show discount row
            document.getElementById('bk-discount-row').style.display = '';
            document.getElementById('bk-discount-label').textContent = data.label;
            document.getElementById('bk-discount-display').textContent = '−' + formatVND(data.discount);

            // Update all price displays
            updatePriceDisplay(appliedDiscount);

            // Set hidden input
            document.getElementById('bk-voucher-code').value = data.code;

            msgEl.style.color = '#16a34a';
            msgEl.textContent = '✓ ' + data.message;
            btn.textContent   = 'Đã áp dụng';
            btn.style.background = '#16a34a';
        } else {
            msgEl.style.color = '#dc2626';
            msgEl.textContent = data.message;
            btn.disabled = false;
            btn.textContent = 'Áp dụng';

            // Reset if was previously applied
            if (appliedDiscount > 0) {
                appliedDiscount = 0;
                document.getElementById('bk-discount-row').style.display = 'none';
                document.getElementById('bk-voucher-code').value = '';
                updatePriceDisplay(0);
            }
        }
    } catch(e) {
        msgEl.style.display = 'block';
        msgEl.style.color   = '#dc2626';
        msgEl.textContent   = 'Có lỗi xảy ra, vui lòng thử lại.';
        btn.disabled = false;
        btn.textContent = 'Áp dụng';
    }
}

// Allow Enter key in voucher input
document.getElementById('bk-voucher-input')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); applyVoucher(); }
});
</script>
@endpush

@endsection
