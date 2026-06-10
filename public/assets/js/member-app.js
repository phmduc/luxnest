/**
 * LuxNest Member Dashboard
 */
(function () {
    'use strict';

    function formatDate(str) {
        if (!str) return '-';
        const d = new Date(str);
        return String(d.getDate()).padStart(2, '0') + '/'
             + String(d.getMonth() + 1).padStart(2, '0') + '/'
             + String(d.getFullYear()).slice(2);
    }

    function formatMoney(n) {
        if (!n) return '0đ';
        return new Intl.NumberFormat('vi-VN').format(n) + 'đ';
    }

    function statusInfo(s) {
        const map = {
            new:         { label: 'Mới',          cls: 'processing' },
            confirmed:   { label: 'Đã xác nhận',  cls: 'awaiting-checkin' },
            in_progress: { label: 'Chờ check-in', cls: 'awaiting-checkin' },
            checked_in:  { label: 'Đã check-in',  cls: 'checked-in' },
            finished:    { label: 'Hoàn tất',      cls: 'completed' },
            cancelled:   { label: 'Đã hủy',        cls: 'cancelled' },
        };
        return map[s] || { label: s || '-', cls: 'new' };
    }

    // ---------------------------------------------------------------
    // Tab Switching
    // ---------------------------------------------------------------

    document.querySelectorAll('.nav-item[data-tab]').forEach(el => {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            const tab = this.dataset.tab;

            document.querySelectorAll('.nav-item[data-tab]').forEach(n => n.classList.remove('active'));
            this.classList.add('active');

            document.querySelectorAll('.dashboard-tab').forEach(t => t.classList.remove('active'));
            const target = document.getElementById('tab-' + tab);
            if (target) target.classList.add('active');
        });
    });

    // ---------------------------------------------------------------
    // My Bookings
    // ---------------------------------------------------------------

    async function loadMyBookings() {
        const month = (document.getElementById('my-booking-month') || {}).value || '';
        const tbody = document.getElementById('my-bookings-body');
        if (!tbody) return;

        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--lux-gray);">Đang tải...</td></tr>';

        const params = new URLSearchParams({ month });
        const res    = await fetch(MEMBER_BASE + '/bookings?' + params.toString(), {
            headers: { 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (!data.success) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:#e11d48;padding:30px;">${data.message || 'Lỗi tải dữ liệu.'}</td></tr>`;
            return;
        }

        const bookings = data.data || [];

        if (!bookings.length) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--lux-gray);">
                <i class="ph ph-calendar-x" style="font-size:2rem;display:block;margin-bottom:10px;"></i>
                Không có booking nào trong tháng này.<br>
                <small>Lưu ý: chỉ hiển thị booking theo email tài khoản của bạn.</small>
            </td></tr>`;
            return;
        }

        tbody.innerHTML = bookings.map(b => {
            const rooms    = b.booking_rooms || [];
            const room0    = rooms[0] || {};
            const roomName = (room0.room_type && room0.room_type.name) || room0.room_unit || 'Phòng';
            const extra    = rooms.length > 1 ? ` (+${rooms.length - 1})` : '';
            const { label, cls } = statusInfo(b.status);

            return `<tr>
                <td style="font-weight:700;color:var(--lux-gray);">#${b.id}</td>
                <td>
                    <strong>${roomName}${extra}</strong>
                    ${b.source_name ? `<span class="table-customer">${b.source_name}</span>` : ''}
                </td>
                <td>
                    <div class="table-dates">
                        <span>${formatDate(b.checkin_date)}</span>
                        <i class="ph ph-arrow-right" style="font-size:0.75rem;color:var(--lux-orange);"></i>
                        <span>${formatDate(b.checkout_date)}</span>
                    </div>
                </td>
                <td style="font-weight:700;">${formatMoney(b.amount)}</td>
                <td><span class="status-badge status-${cls}">${label}</span></td>
            </tr>`;
        }).join('');
    }

    document.getElementById('my-booking-month')?.addEventListener('change', loadMyBookings);

    // Auto-load on page ready
    loadMyBookings();

})();
