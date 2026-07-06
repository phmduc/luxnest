@extends('layouts.app')

@section('title', 'Thuê Xe - LuxNest')

@push('styles')
<style>
.cr-page { background: #f8fafc; padding: 50px 0; }

.cr-wrap { max-width: 900px; margin: 0 auto; padding: 0 20px; }

.cr-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 32px;
    flex-wrap: wrap;
}
.cr-header h1 { font-size: 28px; font-weight: 800; color: #0f172a; margin-bottom: 6px; }
.cr-header p  { color: #64748b; font-size: 15px; margin: 0; }

.cr-hotline-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #996d4e;
    color: #fff;
    border-radius: 10px;
    font-weight: 700;
    font-size: 15px;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.2s, transform 0.2s;
}
.cr-hotline-btn:hover { background: #e64f00; color: #fff; transform: translateY(-2px); }

/* Table */
.cr-table-wrap {
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    margin-bottom: 10px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
.cr-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.cr-table thead tr { background: #0f172a; color: #fff; }
.cr-table th { padding: 14px 18px; text-align: left; font-weight: 600; font-size: 13px; letter-spacing: 0.3px; }
.cr-table td { padding: 13px 18px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
.cr-table tbody tr:last-child td { border-bottom: none; }
.cr-row-even { background: #f8fafc; }
.cr-table tbody tr:hover { background: #fff7f3; }
.cr-price-cell { font-weight: 700; color: #996d4e; }
.cr-note { color: #64748b; font-size: 13px; }

.cr-table-note { font-size: 13px; color: #94a3b8; margin-bottom: 40px; font-style: italic; }

/* Form */
.cr-form-wrap {
    background: #fff;
    border-radius: 14px;
    padding: 36px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
.cr-form-wrap h2 { font-size: 20px; font-weight: 700; color: #0f172a; margin-bottom: 28px; }
.cr-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 24px; }
.cr-field { display: flex; flex-direction: column; gap: 6px; }
.cr-field-full { grid-column: 1 / -1; }
.cr-field label { font-size: 13px; font-weight: 600; color: #374151; }
.cr-field input,
.cr-field select,
.cr-field textarea {
    padding: 10px 14px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    color: #0f172a;
    background: #fff;
    transition: border-color 0.2s;
    outline: none;
    font-family: inherit;
}
.cr-field input:focus,
.cr-field select:focus,
.cr-field textarea:focus { border-color: #996d4e; box-shadow: 0 0 0 3px rgba(153, 109, 78,0.08); }
.cr-field textarea { resize: vertical; }

.cr-form-footer { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
.cr-submit-btn {
    padding: 13px 32px;
    background: #996d4e;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
    font-family: inherit;
}
.cr-submit-btn:hover { background: #e64f00; transform: translateY(-2px); }
.cr-submit-btn:disabled { background: #94a3b8; cursor: not-allowed; transform: none; }

.cr-history-link { font-size: 14px; color: #64748b; text-decoration: none; }
.cr-history-link:hover { color: #996d4e; }

.cr-success {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    padding: 18px 22px;
    margin-top: 24px;
    font-size: 14px;
}
.cr-success span { font-size: 24px; }
.cr-success strong { display: block; color: #15803d; margin-bottom: 4px; }
.cr-success p { color: #166534; margin: 0; }
.cr-success a { color: #996d4e; font-weight: 700; }

/* Success Popup */
.success-popup-overlay {
    position: fixed; inset: 0;
    background: rgba(15,23,42,0.85);
    backdrop-filter: blur(8px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: crFadeIn 0.4s ease forwards;
}
.success-popup-card {
    background: #fff;
    max-width: 450px;
    width: 100%;
    padding: 40px;
    border-radius: 24px;
    text-align: center;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    transform: scale(0.9);
    animation: crPopIn 0.5s cubic-bezier(0.175,0.885,0.32,1.275) forwards;
}
@keyframes crPopIn  { to { transform: scale(1); } }
@keyframes crFadeIn { from { opacity: 0; } to { opacity: 1; } }
.success-popup-icon { font-size: 60px; margin-bottom: 20px; }
.success-popup-card h2 { font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 12px; line-height: 1.3; }
.success-popup-card p  { font-size: 16px; color: #64748b; margin-bottom: 30px; line-height: 1.6; }
.success-popup-card button {
    width: 100%; padding: 16px; border-radius: 12px; border: none;
    background: #996d4e; color: #fff; font-weight: 700; font-size: 16px;
    cursor: pointer; transition: all 0.2s; font-family: inherit;
}
.success-popup-card button:hover { background: #e64f00; transform: translateY(-2px); }

@media (max-width: 640px) {
    .cr-form-grid { grid-template-columns: 1fr; }
    .cr-form-wrap { padding: 24px 18px; }
    .cr-table { font-size: 13px; }
    .cr-table th, .cr-table td { padding: 10px 12px; }
    .cr-header { flex-direction: column; align-items: flex-start; }
    .cr-hotline-btn { width: 100%; justify-content: center; }
}
</style>
@endpush

@section('content')
<div class="cr-page">
    <div class="cr-wrap">

        {{-- Header --}}
        <div class="cr-header">
            <div>
                <h1>🚗 Dịch Vụ Cho Thuê Xe</h1>
                <p>Đặt xe kèm lái xe riêng 24/7 – phục vụ tận nơi tại LuxNest</p>
            </div>
            <a href="tel:+84123456789" class="cr-hotline-btn">
                📞 Hotline: 0123 456 789
            </a>
        </div>

        {{-- Table --}}
        <div class="cr-table-wrap">
            <table class="cr-table">
                <thead>
                    <tr>
                        <th>Loại xe</th>
                        <th>Mẫu xe</th>
                        <th>Giá từ (VNĐ/ngày)</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cars as $i => $car)
                    <tr class="{{ $i % 2 === 0 ? 'cr-row-even' : '' }}">
                        <td><strong>{{ $car['type'] }}</strong></td>
                        <td>{{ $car['model'] }}</td>
                        <td class="cr-price-cell">{{ $car['price'] }}đ</td>
                        <td class="cr-note">{{ $car['note'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="cr-table-note">* Giá trên chưa bao gồm phí xăng dầu và đường cao tốc. Liên hệ để nhận báo giá cụ thể.</p>

        {{-- Inquiry Form --}}
        <div class="cr-form-wrap">
            <h2>📋 Để lại thông tin – Chúng tôi sẽ liên hệ lại trong 15 phút</h2>

            <form class="cr-form" id="cr-inquiry-form">
                @csrf
                <div class="cr-form-grid">
                    <div class="cr-field">
                        <label>Họ và tên</label>
                        <input type="text" name="cr_name" placeholder="Nguyễn Văn A" required>
                    </div>
                    <div class="cr-field">
                        <label>Email</label>
                        <input type="email" name="cr_email" placeholder="email@example.com" required>
                    </div>
                    <div class="cr-field">
                        <label>Số điện thoại liên hệ</label>
                        <input type="tel" name="cr_phone" placeholder="0901 234 567" required>
                    </div>
                    <div class="cr-field">
                        <label>Loại xe cần thuê</label>
                        <select name="cr_car_type">
                            <option value="">-- Chọn loại xe --</option>
                            @foreach($cars as $car)
                            <option value="{{ $car['type'] }}">{{ $car['type'] }} – {{ $car['model'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="cr-field">
                        <label>Ngày nhận xe</label>
                        <input type="text" name="cr_pickup" placeholder="DD/MM/YYYY">
                    </div>
                    <div class="cr-field">
                        <label>Ngày trả xe</label>
                        <input type="text" name="cr_return" placeholder="DD/MM/YYYY">
                    </div>
                    <div class="cr-field cr-field-full">
                        <label>Ghi chú thêm</label>
                        <textarea name="cr_note" rows="3" placeholder="Điểm đón, yêu cầu đặc biệt..."></textarea>
                    </div>
                </div>

                <div class="cr-form-footer">
                    <button type="submit" class="cr-submit-btn" id="cr-submit-btn">
                        Gửi yêu cầu thuê xe
                    </button>
                    <a href="{{ url('/') }}" class="cr-history-link">Về trang chủ →</a>
                </div>
            </form>

            <div class="cr-success" id="cr-success" style="display:none;">
                <span>✅</span>
                <div>
                    <strong>Yêu cầu đã được gửi!</strong>
                    <p>Đội ngũ LuxNest sẽ liên hệ với bạn trong vòng 15 phút. Hoặc gọi ngay <a href="tel:+84123456789">0123 456 789</a>.</p>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Success Popup --}}
<div id="booking-success-popup" class="success-popup-overlay" style="display:none;">
    <div class="success-popup-card">
        <div class="success-popup-icon">🎉</div>
        <h2>Chúc mừng bạn đặt phòng thành công!</h2>
        <p>Để chuyến đi trọn vẹn hơn, hãy tham khảo dịch vụ thuê xe của chúng tôi bên dưới nhé.</p>
        <button onclick="closeSuccessPopup()">Xem dịch vụ thuê xe</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form    = document.getElementById('cr-inquiry-form');
    const success = document.getElementById('cr-success');
    const btn     = document.getElementById('cr-submit-btn');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        btn.disabled    = true;
        btn.textContent = 'Đang gửi...';
        const body = new URLSearchParams(new FormData(form));
        try {
            const res = await fetch('{{ route("car-rental.submit") }}', {
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
                btn.textContent = 'Gửi yêu cầu thuê xe';
            }
        } catch {
            alert('Lỗi kết nối, vui lòng thử lại.');
            btn.disabled    = false;
            btn.textContent = 'Gửi yêu cầu thuê xe';
        }
    });

    // Show popup if redirected from booking success
    if (new URLSearchParams(window.location.search).has('booking_success')) {
        document.getElementById('booking-success-popup').style.display = 'flex';
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

function closeSuccessPopup() {
    const popup = document.getElementById('booking-success-popup');
    popup.style.animation = 'crFadeIn 0.3s ease reverse forwards';
    setTimeout(() => popup.style.display = 'none', 300);
}
</script>
@endpush
