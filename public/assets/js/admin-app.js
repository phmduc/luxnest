/**
 * LuxNest Admin Dashboard - Vanilla JS
 */
(function () {
    'use strict';

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    function csrf() {
        return document.querySelector('meta[name="csrf-token"]').content;
    }

    async function apiFetch(url, options = {}) {
        const method = options.method || 'GET';
        const headers = {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf(),
        };

        const fetchOpts = { method, headers };

        if (options.body) {
            headers['Content-Type'] = 'application/json';
            fetchOpts.body = typeof options.body === 'string' ? options.body : JSON.stringify(options.body);
        }

        const res = await fetch(url, fetchOpts);
        return res.json();
    }

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
            confirmed:   { label: 'Chờ check-in', cls: 'awaiting-checkin' },
            in_progress: { label: 'Chờ check-in', cls: 'awaiting-checkin' },
            checked_in:  { label: 'Đã check-in',  cls: 'checked-in' },
            finished:    { label: 'Hoàn tất',      cls: 'completed' },
            cancelled:   { label: 'Đã hủy',        cls: 'cancelled' },
        };
        return map[s] || { label: s || '-', cls: 'new' };
    }

    function roleInfo(r) {
        const map = {
            admin:    { label: 'Quản trị viên', color: '#dc2626' },
            employee: { label: 'Nhân viên',      color: '#ea580c' },
            member:   { label: 'Thành viên',     color: '#0984e3' },
        };
        return map[r] || { label: r, color: '#636e72' };
    }

    function renderPagination(containerId, totalPages, currentPage, callback) {
        const el = document.getElementById(containerId);
        if (!el) return;
        el.innerHTML = '';
        if (totalPages <= 1) return;

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.className = 'page-btn' + (i === currentPage ? ' active' : '');
            btn.textContent = i;
            btn.addEventListener('click', () => callback(i));
            el.appendChild(btn);
        }
    }

    // ---------------------------------------------------------------
    // Tab Switching
    // ---------------------------------------------------------------

    let currentTab = 'overview';

    function switchTab(tab) {
        document.querySelectorAll('.nav-item[data-tab]').forEach(el => {
            el.classList.toggle('active', el.dataset.tab === tab);
        });
        document.querySelectorAll('.dashboard-tab').forEach(el => {
            el.classList.toggle('active', el.id === 'tab-' + tab);
        });
        currentTab = tab;

        if (tab === 'bookings')    loadBookings();
        if (tab === 'members')     loadMembers();
        if (tab === 'rooms')       loadRooms();
        if (tab === 'villas')      loadVillas();
        if (tab === 'settings')    loadSettings();
        if (tab === 'news')        loadNews();
        if (tab === 'faqs')        loadFaqs();
        if (tab === 'pagecontent') loadPageContent();
    }

    document.querySelectorAll('.nav-item[data-tab]').forEach(el => {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            switchTab(this.dataset.tab);
        });
    });

    // "View all" shortcut links in overview
    document.querySelectorAll('[data-tab-link]').forEach(el => {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            switchTab(this.dataset.tabLink);
        });
    });

    // ---------------------------------------------------------------
    // Bookings
    // ---------------------------------------------------------------

    let bookingPage = 1;
    let bookingSearchTimer = null;

    async function loadBookings(page = 1) {
        bookingPage = page;
        const search = (document.getElementById('booking-search') || {}).value || '';
        const month  = (document.getElementById('booking-month-filter') || {}).value || '';
        const tbody  = document.getElementById('bookings-list-body');
        if (!tbody) return;

        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:40px;color:var(--lux-gray);">Đang tải...</td></tr>';

        const params = new URLSearchParams({ page, search, month });
        const data   = await apiFetch(ADMIN_BASE + '/bookings?' + params.toString());

        if (!data.success) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:#e11d48;padding:30px;">${data.message || 'Lỗi tải dữ liệu.'}</td></tr>`;
            return;
        }

        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:40px;color:var(--lux-gray);">Không có booking nào trong khoảng thời gian này.</td></tr>';
            return;
        }

        tbody.innerHTML = data.data.map(b => {
            const customer = b.customer && b.customer.name ? b.customer.name : 'Khách vãng lai';
            const rooms    = b.booking_rooms || [];
            const room0    = rooms[0] || {};
            const roomName = (room0.room_type && room0.room_type.name) || room0.room_unit || 'Phòng';
            const roomSuffix = rooms.length > 1 ? ` (+${rooms.length - 1})` : '';
            const { label, cls } = statusInfo(b.status);

            return `<tr>
                <td style="font-weight:700;color:var(--lux-gray);">#${b.id}</td>
                <td>
                    <div class="table-room-info">
                        <div>
                            <strong>${roomName}${roomSuffix}</strong>
                            <span class="table-customer">${customer}</span>
                        </div>
                    </div>
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
                <td><a href="#" class="btn-view" onclick="AdminApp.openBookingModal('${b.id}');return false;">Chi tiết</a></td>
            </tr>`;
        }).join('');

        renderPagination('bookings-pagination', data.total_pages || 1, bookingPage, loadBookings);
    }

    document.getElementById('booking-search')?.addEventListener('input', function () {
        clearTimeout(bookingSearchTimer);
        bookingSearchTimer = setTimeout(() => loadBookings(), 500);
    });

    document.getElementById('booking-month-filter')?.addEventListener('change', () => loadBookings());

    // ---------------------------------------------------------------
    // Booking Detail Modal
    // ---------------------------------------------------------------

    async function openBookingModal(id) {
        const overlay = document.getElementById('booking-modal');
        const loader  = document.getElementById('modal-loader');
        const body    = document.getElementById('modal-data');

        overlay.style.display = 'flex';
        loader.style.display  = 'block';
        body.style.display    = 'none';
        body.innerHTML        = '';

        const res = await apiFetch(ADMIN_BASE + '/bookings/' + id);

        loader.style.display = 'none';

        if (!res.success) {
            body.innerHTML     = `<p style="text-align:center;color:#e11d48;">${res.message || 'Không thể tải dữ liệu.'}</p>`;
            body.style.display = 'block';
            return;
        }

        const b       = res.data;
        const rooms   = b.booking_rooms || [];
        const room0   = rooms[0] || {};
        const roomName = (room0.room_type && room0.room_type.name) || room0.room_unit || 'Phòng';
        const roomId   = room0.id || '';
        const customer = b.customer || {};
        const { label, cls } = statusInfo(b.status);
        const canCheckin = !['cancelled', 'finished', 'checked_in'].includes(b.status);
        const canCancel  = !['cancelled', 'finished', 'checked_in'].includes(b.status);

        body.innerHTML = `
            <div class="modal-detail-header">
                <div class="modal-detail-main" style="flex:1;">
                    <div class="modal-status-bar" style="margin-bottom:14px;">
                        <span class="status-badge status-${cls}">${label}</span>
                        <span style="color:var(--lux-gray);">ID: #${b.id}</span>
                        ${b.source_name ? `<span style="color:var(--lux-gray);">• ${b.source_name}</span>` : ''}
                    </div>
                    <h2 style="margin:0 0 8px;font-size:1.8rem;font-weight:800;">${roomName}</h2>
                    ${rooms.length > 1 ? `<p style="margin:0;color:var(--lux-gray);">+${rooms.length - 1} phòng khác</p>` : ''}
                </div>
            </div>

            <div class="modal-grid">
                <div class="detail-group">
                    <h4>Thông tin khách hàng</h4>
                    <div class="detail-item">
                        <span class="detail-label">Họ tên:</span>
                        <span class="detail-value">${customer.name || '-'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">${customer.email || '-'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Điện thoại:</span>
                        <span class="detail-value">${customer.phone || '-'}</span>
                    </div>
                </div>
                <div class="detail-group">
                    <h4>Lịch trình & Ghi chú</h4>
                    <div class="detail-item">
                        <span class="detail-label">Check-in:</span>
                        <span class="detail-value">${b.checkin_date || '-'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Check-out:</span>
                        <span class="detail-value">${b.checkout_date || '-'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Ghi chú:</span>
                        <span class="detail-value">${b.note || 'Không có'}</span>
                    </div>
                </div>
            </div>

            <div class="payment-summary">
                <div class="payment-row grand-total" style="border-top:none;padding-top:0;margin-bottom:0;">
                    <span>Tổng tiền booking:</span>
                    <span>${formatMoney(b.amount)}</span>
                </div>
            </div>

            ${(canCheckin || canCancel) ? `
            <div style="margin-top:20px;padding:20px;background:#fff8f1;border-radius:16px;border:1px solid #ffedd5;display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap;">
                <div style="flex:1;min-width:200px;">
                    <h4 style="margin:0;color:#9a3412;font-size:16px;display:flex;align-items:center;gap:8px;">
                        <i class="ph ph-gear-six" style="font-size:20px;"></i> Thao tác booking
                    </h4>
                    <p style="margin:5px 0 0;font-size:13px;color:#c2410c;">
                        ${canCheckin ? 'Khách đã sẵn sàng nhận phòng hoặc bạn cần hủy đặt phòng này.' : 'Bạn có thể hủy đặt phòng này nếu cần.'}
                    </p>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    ${canCancel ? `
                    <button data-booking-id="${b.id}" data-room-id="${roomId}"
                            onclick="AdminApp.cancelBooking('${b.id}','${roomId}',this)"
                            style="border:1px solid #fecaca;background:#fff;color:#dc2626;padding:12px 24px;border-radius:12px;font-family:'Outfit',sans-serif;font-weight:700;font-size:1rem;cursor:pointer;transition:all 0.2s;white-space:nowrap;">
                        Hủy đặt phòng
                    </button>
                    ` : ''}
                    ${canCheckin ? `
                    <button data-booking-id="${b.id}" data-room-id="${roomId}"
                            onclick="AdminApp.performCheckin('${b.id}','${roomId}',this)"
                            style="border:none;background:#ea580c;color:#fff;padding:12px 24px;border-radius:12px;font-family:'Outfit',sans-serif;font-weight:700;font-size:1rem;cursor:pointer;box-shadow:0 4px 12px rgba(234,88,12,0.25);transition:all 0.2s;white-space:nowrap;">
                        Xác nhận Check-in
                    </button>
                    ` : ''}
                </div>
            </div>
            ` : ''}
        `;
        body.style.display = 'block';
    }

    async function performCheckin(bookingId, roomId, btn) {
        if (!confirm(`Xác nhận check-in cho booking #${bookingId}?`)) return;

        const orig = btn.textContent;
        btn.disabled   = true;
        btn.textContent = 'Đang xử lý...';

        const res = await apiFetch(ADMIN_BASE + '/bookings/' + bookingId + '/checkin', {
            method: 'POST',
            body: { room_id: roomId },
        });

        if (res.success) {
            alert('Check-in thành công!');
            closeBookingModal();
            if (currentTab === 'bookings') loadBookings(bookingPage);
        } else {
            alert('Lỗi: ' + (res.message || 'Không thể check-in.'));
            btn.disabled   = false;
            btn.textContent = orig;
        }
    }

    async function cancelBooking(bookingId, roomId, btn) {
        if (!confirm(`Bạn có chắc muốn hủy booking #${bookingId}? Hành động này không thể hoàn tác.`)) return;

        const orig = btn.textContent;
        btn.disabled   = true;
        btn.textContent = 'Đang xử lý...';

        const res = await apiFetch(ADMIN_BASE + '/bookings/' + bookingId + '/cancel', {
            method: 'POST',
            body: { room_id: roomId },
        });

        if (res.success) {
            alert('Hủy đặt phòng thành công!');
            closeBookingModal();
            if (currentTab === 'bookings') loadBookings(bookingPage);
        } else {
            alert('Lỗi: ' + (res.message || 'Không thể hủy đặt phòng.'));
            btn.disabled   = false;
            btn.textContent = orig;
        }
    }

    function closeBookingModal() {
        const overlay = document.getElementById('booking-modal');
        if (overlay) overlay.style.display = 'none';
    }

    document.getElementById('booking-modal-close')?.addEventListener('click', closeBookingModal);
    document.getElementById('booking-modal')?.addEventListener('click', function (e) {
        if (e.target === this) closeBookingModal();
    });

    // ---------------------------------------------------------------
    // Members
    // ---------------------------------------------------------------

    let memberPage = 1;
    let memberSearchTimer = null;

    async function loadMembers(page = 1) {
        memberPage = page;
        if (typeof USER_ROLE !== 'undefined' && USER_ROLE !== 'admin') return;

        const search = (document.getElementById('member-search') || {}).value || '';
        const tbody  = document.getElementById('members-list-body');
        if (!tbody) return;

        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--lux-gray);">Đang tải...</td></tr>';

        const params = new URLSearchParams({ page, search });
        const data   = await apiFetch(ADMIN_BASE + '/members?' + params.toString());

        if (!data.success) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:#e11d48;padding:30px;">${data.message || 'Lỗi tải dữ liệu.'}</td></tr>`;
            return;
        }

        const members    = (data.data && data.data.data) ? data.data.data : (data.data || []);
        const pagination = data.data || {};

        if (!members.length) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--lux-gray);">Chưa có thành viên nào.</td></tr>';
            return;
        }

        tbody.innerHTML = members.map(m => {
            const { label, color } = roleInfo(m.role);
            const initial = (m.name || '?').charAt(0).toUpperCase();
            const created = m.created_at ? new Date(m.created_at).toLocaleDateString('vi-VN') : '-';
            const escaped = JSON.stringify(m).replace(/"/g, '&quot;');

            return `<tr>
                <td>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:40px;height:40px;border-radius:50%;background:var(--lux-orange-soft);color:var(--lux-orange);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.1rem;flex-shrink:0;">
                            ${initial}
                        </div>
                        <div>
                            <strong>${m.name}</strong>
                            <span class="table-customer">#${m.id}</span>
                        </div>
                    </div>
                </td>
                <td style="color:var(--lux-gray);">${m.email}</td>
                <td>
                    <span class="status-badge" style="background:${color}18;color:${color};">${label}</span>
                </td>
                <td style="color:var(--lux-gray);font-size:0.9rem;">${created}</td>
                <td>
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <button class="btn-view" onclick='AdminApp.openMemberModal(${escaped})'>Sửa</button>
                        <button class="btn-view" style="color:#dc2626;border-color:#fecdd3;"
                                onclick="AdminApp.deleteMember(${m.id},'${m.name.replace(/'/g, "\\'")}')">Xóa</button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        renderPagination('members-pagination', pagination.last_page || 1, pagination.current_page || 1, loadMembers);
    }

    document.getElementById('member-search')?.addEventListener('input', function () {
        clearTimeout(memberSearchTimer);
        memberSearchTimer = setTimeout(() => loadMembers(), 500);
    });

    function openMemberModal(member) {
        const modal = document.getElementById('member-modal');
        const title = document.getElementById('member-modal-title');
        const hint  = document.getElementById('member-pass-hint');
        if (!modal) return;

        document.getElementById('member-id').value       = '';
        document.getElementById('member-name').value     = '';
        document.getElementById('member-email').value    = '';
        document.getElementById('member-role').value     = 'member';
        document.getElementById('member-password').value = '';
        const errEl = document.getElementById('member-form-error');
        if (errEl) errEl.style.display = 'none';

        if (member && member.id) {
            title.textContent = 'Sửa tài khoản';
            if (hint) hint.textContent = '(để trống nếu không đổi)';
            document.getElementById('member-id').value    = member.id;
            document.getElementById('member-name').value  = member.name  || '';
            document.getElementById('member-email').value = member.email || '';
            document.getElementById('member-role').value  = member.role  || 'member';
        } else {
            title.textContent = 'Thêm tài khoản';
            if (hint) hint.textContent = '(bắt buộc)';
        }

        modal.style.display = 'flex';
    }

    document.getElementById('member-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const id  = document.getElementById('member-id').value;
        const pwd = document.getElementById('member-password').value;

        const payload = {
            name:     document.getElementById('member-name').value,
            email:    document.getElementById('member-email').value,
            role:     document.getElementById('member-role').value,
        };
        if (pwd) payload.password = pwd;

        const url    = id ? (ADMIN_BASE + '/members/' + id) : (ADMIN_BASE + '/members');
        const method = id ? 'PUT' : 'POST';

        const res = await apiFetch(url, { method, body: payload });

        if (res.success) {
            document.getElementById('member-modal').style.display = 'none';
            loadMembers(memberPage);
        } else {
            const errEl = document.getElementById('member-form-error');
            let msg = res.message || 'Lỗi không xác định.';
            if (res.errors) {
                msg = Object.values(res.errors).flat().join(' ');
            }
            if (errEl) {
                errEl.textContent   = msg;
                errEl.style.display = 'block';
            }
        }
    });

    async function deleteMember(id, name) {
        if (!confirm(`Xóa tài khoản "${name}"?\nHành động này không thể hoàn tác.`)) return;

        const res = await apiFetch(ADMIN_BASE + '/members/' + id, { method: 'DELETE' });

        if (res.success) {
            loadMembers(memberPage);
        } else {
            alert('Lỗi: ' + (res.message || 'Không thể xóa.'));
        }
    }

    function closeMemberModal() {
        const modal = document.getElementById('member-modal');
        if (modal) modal.style.display = 'none';
    }

    document.getElementById('member-modal-close')?.addEventListener('click', closeMemberModal);
    document.getElementById('member-modal-cancel')?.addEventListener('click', closeMemberModal);
    document.getElementById('member-modal')?.addEventListener('click', function (e) {
        if (e.target === this) closeMemberModal();
    });

    // ---------------------------------------------------------------
    // Rooms
    // ---------------------------------------------------------------

    let roomPage = 1;
    let roomSearchTimer = null;

    async function loadRooms(page = 1) {
        roomPage = page;
        if (typeof USER_ROLE !== 'undefined' && USER_ROLE !== 'admin') return;

        const search = (document.getElementById('room-search') || {}).value || '';
        const branch = (document.getElementById('room-branch-filter') || {}).value || '';
        const tbody  = document.getElementById('rooms-list-body');
        if (!tbody) return;

        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--lux-gray);">Đang tải...</td></tr>';

        let data;
        try {
            const params = new URLSearchParams({ page, search, branch });
            data = await apiFetch(ADMIN_BASE + '/rooms?' + params.toString());
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:#e11d48;padding:30px;">Lỗi kết nối: ${err.message || err}</td></tr>`;
            return;
        }

        if (!data || !data.success) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:#e11d48;padding:30px;">${(data && data.message) || 'Lỗi tải dữ liệu.'}</td></tr>`;
            return;
        }

        const rooms      = (data.data && data.data.data) ? data.data.data : (data.data || []);
        const pagination = data.data || {};

        console.log('[Rooms] API response:', JSON.stringify(data).slice(0, 300));
        console.log('[Rooms] loaded', rooms.length, 'rooms, branch:', branch);

        if (!rooms.length) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--lux-gray);">Chưa có phòng nào.</td></tr>';
            return;
        }

        const branchColor = { Hotel: '#1a3a6b', Villa: '#7c3aed', Residence: '#0f766e' };

        try { tbody.innerHTML = rooms.map(r => {
            const color    = branchColor[r.branch] || '#666';
            const isActive = r.status === 'active';
            const price    = new Intl.NumberFormat('vi-VN').format(r.price) + 'đ';
            const escaped  = JSON.stringify(r).replace(/"/g, '&quot;');

            const gallery   = Array.isArray(r.gallery) ? r.gallery : [];
            const allImages = [r.image, ...gallery].filter(Boolean).slice(0, 3);

            return `<tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="display:flex;gap:3px;flex-shrink:0;">
                        ${allImages.length
                            ? allImages.map((img, idx) => `<img src="${img}" style="width:${idx===0?'48px':'28px'};height:${idx===0?'38px':'28px'};object-fit:cover;border-radius:${idx===0?'8px':'5px'};flex-shrink:0;">`).join('')
                            : `<div style="width:48px;height:38px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;"><i class="ph ph-image" style="color:#94a3b8;"></i></div>`
                        }
                        </div>
                        <div>
                            <strong style="display:block;">${r.name}</strong>
                            <span style="font-size:.8rem;color:var(--lux-gray);">#${r.id}</span>
                        </div>
                    </div>
                </td>
                <td><span style="background:${color}18;color:${color};padding:4px 10px;border-radius:6px;font-size:.82rem;font-weight:700;">${r.branch}</span></td>
                <td style="color:var(--lux-gray);">${r.type || '—'}</td>
                <td style="font-weight:700;">${price}</td>
                <td style="font-size:.8rem;color:var(--lux-gray);">
                    ${r.gohost_room_type_id
                        ? `<code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;">${r.gohost_room_type_id.slice(0,16)}…</code>`
                        : '<span style="color:#e11d48;">Chưa liên kết</span>'
                    }
                </td>
                <td>
                    <button onclick="AdminApp.toggleRoomStatus(${r.id}, this)"
                            style="border:none;cursor:pointer;padding:5px 12px;border-radius:8px;font-size:.82rem;font-weight:700;font-family:inherit;background:${isActive ? '#dcfce7' : '#fee2e2'};color:${isActive ? '#166534' : '#dc2626'};">
                        ${isActive ? 'Hoạt động' : 'Tạm ẩn'}
                    </button>
                </td>
                <td>
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <button class="btn-view" onclick='AdminApp.openRoomModal(${escaped})'>Sửa</button>
                        <button class="btn-view" style="color:#dc2626;border-color:#fecdd3;"
                                onclick="AdminApp.deleteRoom(${r.id},'${r.name.replace(/'/g, "\\'")}')">Xóa</button>
                    </div>
                </td>
            </tr>`;
        }).join(''); } catch (renderErr) {
            console.error('[Rooms] render error:', renderErr);
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:#e11d48;padding:30px;">Lỗi hiển thị: ${renderErr.message}</td></tr>`;
            return;
        }

        renderPagination('rooms-pagination', pagination.last_page || 1, pagination.current_page || 1, loadRooms);
    }

    document.getElementById('room-search')?.addEventListener('input', function () {
        clearTimeout(roomSearchTimer);
        roomSearchTimer = setTimeout(() => loadRooms(), 500);
    });
    document.getElementById('room-branch-filter')?.addEventListener('change', () => loadRooms());

    // Auto-generate slug from name
    document.getElementById('room-name')?.addEventListener('input', function () {
        const slugEl = document.getElementById('room-slug');
        if (!slugEl || slugEl.dataset.manual) return;
        slugEl.value = this.value
            .toLowerCase()
            .normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/đ/g, 'd').replace(/[^a-z0-9\s-]/g, '')
            .trim().replace(/\s+/g, '-');
    });
    document.getElementById('room-slug')?.addEventListener('input', function () {
        this.dataset.manual = this.value ? '1' : '';
    });

    // ── Gallery: 5 slots ──────────────────────────────────────────

    // In-memory state: array of 5 URLs (null = empty)
    let gallerySlots = [null, null, null, null, null];
    let activeSlot   = 0; // which slot the file picker targets

    function getSlotEl(idx) {
        return document.querySelectorAll('.img-slot')[idx];
    }

    function renderSlot(idx) {
        const slot    = getSlotEl(idx);
        if (!slot) return;
        const url     = gallerySlots[idx];
        const imgEl   = slot.querySelector('.slot-img');
        const empty   = slot.querySelector('.slot-empty');
        const overlay = slot.querySelector('.slot-overlay');

        if (url) {
            imgEl.src             = url;
            imgEl.style.display   = 'block';
            empty.style.display   = 'none';
            overlay.style.display = 'none'; // shown on hover via CSS
            slot.style.borderStyle = 'solid';
            slot.style.borderColor = '#e2e8f0';
        } else {
            imgEl.style.display   = 'none';
            empty.style.display   = 'flex';
            overlay.style.display = 'none';
            slot.style.borderStyle = 'dashed';
            slot.style.borderColor = '#e2e8f0';
        }
    }

    function resetGallery() {
        gallerySlots = [null, null, null, null, null];
        for (let i = 0; i < 5; i++) renderSlot(i);
        const hiddenImg = document.getElementById('room-image');
        if (hiddenImg) hiddenImg.value = '';
    }

    function loadGalleryFromRoom(room) {
        gallerySlots = [null, null, null, null, null];
        if (room.image)   gallerySlots[0] = room.image;
        const extra = Array.isArray(room.gallery) ? room.gallery : [];
        extra.forEach((url, i) => { if (i + 1 < 5) gallerySlots[i + 1] = url; });
        for (let i = 0; i < 5; i++) renderSlot(i);
    }

    function collectGallery() {
        // returns { image, gallery }
        return {
            image:   gallerySlots[0] || null,
            gallery: gallerySlots.slice(1).filter(Boolean),
        };
    }

    function openSlotPicker(idx) {
        activeSlot = idx;
        const fi = document.getElementById('room-file-input');
        if (fi) { fi.value = ''; fi.click(); }
    }

    document.getElementById('room-file-input')?.addEventListener('change', function () {
        if (this.files[0]) uploadToSlot(this.files[0], activeSlot);
    });

    function handleSlotDrop(e, idx) {
        e.preventDefault();
        const slot = getSlotEl(idx);
        if (slot) slot.style.borderColor = '#e2e8f0';
        const file = e.dataTransfer?.files?.[0];
        if (file && file.type.startsWith('image/')) uploadToSlot(file, idx);
    }

    async function uploadToSlot(file, idx) {
        const slot      = getSlotEl(idx);
        const uploading = slot?.querySelector('.slot-uploading');
        const bar       = uploading?.querySelector('.slot-bar');

        // Show uploading state
        if (uploading) uploading.style.display = 'flex';
        if (bar)       bar.style.width = '0%';

        let pct = 0;
        const ticker = setInterval(() => {
            pct = Math.min(pct + 12, 88);
            if (bar) bar.style.width = pct + '%';
        }, 100);

        const form = new FormData();
        form.append('image', file);

        try {
            const res = await fetch(ADMIN_BASE + '/rooms/upload-image', {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': csrf() },
                body:    form,
            }).then(r => r.json());

            clearInterval(ticker);
            if (bar) bar.style.width = '100%';

            if (res.success) {
                gallerySlots[idx] = res.url;
                // sync hidden input for slot 0
                if (idx === 0) {
                    const hi = document.getElementById('room-image');
                    if (hi) hi.value = res.url;
                }
                renderSlot(idx);
            } else {
                alert('Upload thất bại: ' + (res.message || 'Lỗi'));
            }
        } catch {
            clearInterval(ticker);
            alert('Lỗi kết nối khi tải ảnh.');
        } finally {
            setTimeout(() => {
                if (uploading) uploading.style.display = 'none';
                if (bar)       bar.style.width = '0%';
            }, 500);
        }
    }

    function clearSlot(idx) {
        gallerySlots[idx] = null;
        if (idx === 0) {
            const hi = document.getElementById('room-image');
            if (hi) hi.value = '';
        }
        renderSlot(idx);
    }

    // Legacy no-op kept for HTML compatibility
    function handleRoomDrop() {}
    function clearRoomPreview() { clearSlot(0); }

    function openRoomModal(room) {
        const modal = document.getElementById('room-modal');
        const title = document.getElementById('room-modal-title');
        if (!modal) return;

        // Reset fields
        ['room-id','room-name','room-slug','room-type','room-price','room-regular-price',
         'room-image','room-amenities','room-gohost-id','room-description'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        const slugEl = document.getElementById('room-slug');
        if (slugEl) delete slugEl.dataset.manual;
        document.getElementById('room-branch').value = 'Hotel';
        document.getElementById('room-status').value = 'active';
        resetGallery();
        const fileInput = document.getElementById('room-file-input');
        if (fileInput) fileInput.value = '';
        const errEl = document.getElementById('room-form-error');
        if (errEl) errEl.style.display = 'none';

        if (room && room.id) {
            title.textContent = 'Sửa phòng';
            document.getElementById('room-id').value            = room.id;
            document.getElementById('room-name').value          = room.name           || '';
            document.getElementById('room-slug').value          = room.slug           || '';
            document.getElementById('room-slug').dataset.manual = '1';
            document.getElementById('room-branch').value        = room.branch         || 'Hotel';
            document.getElementById('room-type').value          = room.type           || '';
            document.getElementById('room-price').value         = room.price          || '';
            document.getElementById('room-regular-price').value = room.regular_price  || '';
            document.getElementById('room-amenities').value     = Array.isArray(room.amenities)
                                                                   ? room.amenities.join(', ')
                                                                   : (room.amenities   || '');
            document.getElementById('room-gohost-id').value     = room.gohost_room_type_id || '';
            document.getElementById('room-status').value        = room.status         || 'active';
            document.getElementById('room-description').value   = room.description    || '';
            loadGalleryFromRoom(room);
        } else {
            title.textContent = 'Thêm phòng mới';
        }

        modal.style.display = 'flex';
    }

    document.getElementById('room-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const id  = document.getElementById('room-id').value;
        const btn = document.getElementById('room-submit-btn');
        const origText = btn.textContent;
        btn.disabled    = true;
        btn.textContent = 'Đang lưu...';

        const amenitiesRaw = document.getElementById('room-amenities').value;
        const amenities    = amenitiesRaw
            ? amenitiesRaw.split(',').map(s => s.trim()).filter(Boolean)
            : [];

        const { image, gallery } = collectGallery();

        const payload = {
            name:                document.getElementById('room-name').value,
            slug:                document.getElementById('room-slug').value,
            branch:              document.getElementById('room-branch').value,
            type:                document.getElementById('room-type').value || null,
            price:               parseInt(document.getElementById('room-price').value) || 0,
            regular_price:       parseInt(document.getElementById('room-regular-price').value) || null,
            image,
            gallery,
            amenities,
            gohost_room_type_id: document.getElementById('room-gohost-id').value || null,
            status:              document.getElementById('room-status').value,
            description:         document.getElementById('room-description').value || null,
        };

        const url    = id ? (ADMIN_BASE + '/rooms/' + id) : (ADMIN_BASE + '/rooms');
        const method = id ? 'PUT' : 'POST';
        const res    = await apiFetch(url, { method, body: payload });

        btn.disabled    = false;
        btn.textContent = origText;

        if (res.success) {
            document.getElementById('room-modal').style.display = 'none';
            loadRooms(roomPage);
        } else {
            const errEl = document.getElementById('room-form-error');
            let msg = res.message || 'Lỗi không xác định.';
            if (res.errors) msg = Object.values(res.errors).flat().join(' ');
            if (errEl) { errEl.textContent = msg; errEl.style.display = 'block'; }
        }
    });

    async function deleteRoom(id, name) {
        if (!confirm(`Xóa phòng "${name}"?\nHành động này không thể hoàn tác.`)) return;

        const res = await apiFetch(ADMIN_BASE + '/rooms/' + id, { method: 'DELETE' });
        if (res.success) {
            loadRooms(roomPage);
        } else {
            alert('Lỗi: ' + (res.message || 'Không thể xóa.'));
        }
    }

    async function toggleRoomStatus(id, btn) {
        const res = await apiFetch(ADMIN_BASE + '/rooms/' + id + '/status', { method: 'PATCH' });
        if (res.success) {
            const isActive = res.status === 'active';
            btn.textContent = isActive ? 'Hoạt động' : 'Tạm ẩn';
            btn.style.background = isActive ? '#dcfce7' : '#fee2e2';
            btn.style.color      = isActive ? '#166534' : '#dc2626';
        }
    }

    function closeRoomModal() {
        const modal = document.getElementById('room-modal');
        if (modal) modal.style.display = 'none';
    }

    document.getElementById('room-modal-close')?.addEventListener('click', closeRoomModal);
    document.getElementById('room-modal-cancel')?.addEventListener('click', closeRoomModal);
    document.getElementById('room-modal')?.addEventListener('click', function (e) {
        if (e.target === this) closeRoomModal();
    });

    // ---------------------------------------------------------------
    // Villa Listings
    // ---------------------------------------------------------------

    let villaPage = 1;
    let villaSearchTimer = null;

    async function loadVillas(page = 1) {
        villaPage = page;
        if (typeof USER_ROLE !== 'undefined' && USER_ROLE !== 'admin') return;

        const search = (document.getElementById('villa-search') || {}).value || '';
        const tbody  = document.getElementById('villas-list-body');
        if (!tbody) return;

        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--lux-gray);">Đang tải...</td></tr>';

        let data;
        try {
            const params = new URLSearchParams({ page, search });
            data = await apiFetch(ADMIN_BASE + '/villas?' + params.toString());
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:#e11d48;padding:30px;">Lỗi kết nối: ${err.message || err}</td></tr>`;
            return;
        }

        if (!data || !data.success) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:#e11d48;padding:30px;">${(data && data.message) || 'Lỗi tải dữ liệu.'}</td></tr>`;
            return;
        }

        const villas     = (data.data && data.data.data) ? data.data.data : (data.data || []);
        const pagination = data.data || {};

        if (!villas.length) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--lux-gray);">Chưa có villa nào.</td></tr>';
            return;
        }

        try { tbody.innerHTML = villas.map(v => {
            const isActive = v.status === 'active';
            const escaped  = JSON.stringify(v).replace(/"/g, '&quot;');

            const gallery   = Array.isArray(v.gallery) ? v.gallery : [];
            const allImages = [v.image, ...gallery].filter(Boolean).slice(0, 3);

            return `<tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="display:flex;gap:3px;flex-shrink:0;">
                        ${allImages.length
                            ? allImages.map((img, idx) => `<img src="${img}" style="width:${idx===0?'48px':'28px'};height:${idx===0?'38px':'28px'};object-fit:cover;border-radius:${idx===0?'8px':'5px'};flex-shrink:0;">`).join('')
                            : `<div style="width:48px;height:38px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;"><i class="ph ph-image" style="color:#94a3b8;"></i></div>`
                        }
                        </div>
                        <div>
                            <strong style="display:block;">${v.name}</strong>
                            <span style="font-size:.8rem;color:var(--lux-gray);">${v.location_desc || ''}</span>
                        </div>
                    </div>
                </td>
                <td>${v.location || '—'}</td>
                <td style="color:var(--lux-gray);">${v.beds} / ${v.guests}</td>
                <td>
                    <button onclick="AdminApp.toggleVillaStatus(${v.id}, this)"
                            style="border:none;cursor:pointer;padding:5px 12px;border-radius:8px;font-size:.82rem;font-weight:700;font-family:inherit;background:${isActive ? '#dcfce7' : '#fee2e2'};color:${isActive ? '#166534' : '#dc2626'};">
                        ${isActive ? 'Hoạt động' : 'Tạm ẩn'}
                    </button>
                </td>
                <td>
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <button class="btn-view" onclick='AdminApp.openVillaModal(${escaped})'>Sửa</button>
                        <button class="btn-view" style="color:#dc2626;border-color:#fecdd3;"
                                onclick="AdminApp.deleteVilla(${v.id},'${v.name.replace(/'/g, "\\'")}')">Xóa</button>
                    </div>
                </td>
            </tr>`;
        }).join(''); } catch (renderErr) {
            console.error('[Villas] render error:', renderErr);
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:#e11d48;padding:30px;">Lỗi hiển thị: ${renderErr.message}</td></tr>`;
            return;
        }

        renderPagination('villas-pagination', pagination.last_page || 1, pagination.current_page || 1, loadVillas);
    }

    document.getElementById('villa-search')?.addEventListener('input', function () {
        clearTimeout(villaSearchTimer);
        villaSearchTimer = setTimeout(() => loadVillas(), 500);
    });

    // Auto-generate slug from name
    document.getElementById('villa-name')?.addEventListener('input', function () {
        const slugEl = document.getElementById('villa-slug');
        if (!slugEl || slugEl.dataset.manual) return;
        slugEl.value = this.value
            .toLowerCase()
            .normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/đ/g, 'd').replace(/[^a-z0-9\s-]/g, '')
            .trim().replace(/\s+/g, '-');
    });
    document.getElementById('villa-slug')?.addEventListener('input', function () {
        this.dataset.manual = this.value ? '1' : '';
    });

    // ── Gallery: 10 slots ─────────────────────────────────────────

    const VILLA_GALLERY_SIZE = 10;
    let villaGallerySlots = new Array(VILLA_GALLERY_SIZE).fill(null);
    let activeVillaSlot   = 0;

    function getVillaSlotEl(idx) {
        return document.querySelectorAll('#villa-gallery-grid .villa-img-slot')[idx];
    }

    function renderVillaSlot(idx) {
        const slot    = getVillaSlotEl(idx);
        if (!slot) return;
        const url     = villaGallerySlots[idx];
        const imgEl   = slot.querySelector('.slot-img');
        const empty   = slot.querySelector('.slot-empty');
        const overlay = slot.querySelector('.slot-overlay');

        if (url) {
            imgEl.src             = url;
            imgEl.style.display   = 'block';
            empty.style.display   = 'none';
            overlay.style.display = 'none';
            slot.style.borderStyle = 'solid';
            slot.style.borderColor = '#e2e8f0';
        } else {
            imgEl.style.display   = 'none';
            empty.style.display   = 'flex';
            overlay.style.display = 'none';
            slot.style.borderStyle = 'dashed';
            slot.style.borderColor = '#e2e8f0';
        }
    }

    function resetVillaGallery() {
        villaGallerySlots = new Array(VILLA_GALLERY_SIZE).fill(null);
        for (let i = 0; i < VILLA_GALLERY_SIZE; i++) renderVillaSlot(i);
        const hiddenImg = document.getElementById('villa-image');
        if (hiddenImg) hiddenImg.value = '';
    }

    function loadGalleryFromVilla(villa) {
        villaGallerySlots = new Array(VILLA_GALLERY_SIZE).fill(null);
        if (villa.image) villaGallerySlots[0] = villa.image;
        const extra = Array.isArray(villa.gallery) ? villa.gallery : [];
        extra.forEach((url, i) => { if (i + 1 < VILLA_GALLERY_SIZE) villaGallerySlots[i + 1] = url; });
        for (let i = 0; i < VILLA_GALLERY_SIZE; i++) renderVillaSlot(i);
    }

    function collectVillaGallery() {
        return {
            image:   villaGallerySlots[0] || null,
            gallery: villaGallerySlots.slice(1).filter(Boolean),
        };
    }

    function openVillaSlotPicker(idx) {
        activeVillaSlot = idx;
        const fi = document.getElementById('villa-file-input');
        if (fi) { fi.value = ''; fi.click(); }
    }

    document.getElementById('villa-file-input')?.addEventListener('change', function () {
        if (this.files[0]) uploadVillaToSlot(this.files[0], activeVillaSlot);
    });

    function handleVillaSlotDrop(e, idx) {
        e.preventDefault();
        const slot = getVillaSlotEl(idx);
        if (slot) slot.style.borderColor = '#e2e8f0';
        const file = e.dataTransfer?.files?.[0];
        if (file && file.type.startsWith('image/')) uploadVillaToSlot(file, idx);
    }

    async function uploadVillaToSlot(file, idx) {
        const slot      = getVillaSlotEl(idx);
        const uploading = slot?.querySelector('.slot-uploading');
        const bar       = uploading?.querySelector('.slot-bar');

        if (uploading) uploading.style.display = 'flex';
        if (bar)       bar.style.width = '0%';

        let pct = 0;
        const ticker = setInterval(() => {
            pct = Math.min(pct + 12, 88);
            if (bar) bar.style.width = pct + '%';
        }, 100);

        const form = new FormData();
        form.append('image', file);

        try {
            const res = await fetch(ADMIN_BASE + '/villas/upload-image', {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': csrf() },
                body:    form,
            }).then(r => r.json());

            clearInterval(ticker);
            if (bar) bar.style.width = '100%';

            if (res.success) {
                villaGallerySlots[idx] = res.url;
                if (idx === 0) {
                    const hi = document.getElementById('villa-image');
                    if (hi) hi.value = res.url;
                }
                renderVillaSlot(idx);
            } else {
                alert('Upload thất bại: ' + (res.message || 'Lỗi'));
            }
        } catch {
            clearInterval(ticker);
            alert('Lỗi kết nối khi tải ảnh.');
        } finally {
            setTimeout(() => {
                if (uploading) uploading.style.display = 'none';
                if (bar)       bar.style.width = '0%';
            }, 500);
        }
    }

    function clearVillaSlot(idx) {
        villaGallerySlots[idx] = null;
        if (idx === 0) {
            const hi = document.getElementById('villa-image');
            if (hi) hi.value = '';
        }
        renderVillaSlot(idx);
    }

    function openVillaModal(villa) {
        const modal = document.getElementById('villa-modal');
        const title = document.getElementById('villa-modal-title');
        if (!modal) return;

        ['villa-id','villa-name','villa-slug','villa-location','villa-location-desc',
         'villa-beds','villa-guests','villa-image','villa-description'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        const slugEl = document.getElementById('villa-slug');
        if (slugEl) delete slugEl.dataset.manual;
        document.getElementById('villa-location').value = 'Đà Lạt';
        document.getElementById('villa-status').value   = 'active';
        resetVillaGallery();
        const fileInput = document.getElementById('villa-file-input');
        if (fileInput) fileInput.value = '';
        const errEl = document.getElementById('villa-form-error');
        if (errEl) errEl.style.display = 'none';

        if (villa && villa.id) {
            title.textContent = 'Sửa villa';
            document.getElementById('villa-id').value                = villa.id;
            document.getElementById('villa-name').value              = villa.name          || '';
            document.getElementById('villa-slug').value               = villa.slug          || '';
            document.getElementById('villa-slug').dataset.manual      = '1';
            document.getElementById('villa-location').value           = villa.location      || 'Đà Lạt';
            document.getElementById('villa-location-desc').value      = villa.location_desc || '';
            document.getElementById('villa-beds').value                = villa.beds          || '';
            document.getElementById('villa-guests').value              = villa.guests        || '';
            document.getElementById('villa-status').value              = villa.status        || 'active';
            document.getElementById('villa-description').value         = villa.description   || '';
            loadGalleryFromVilla(villa);
        } else {
            title.textContent = 'Thêm villa mới';
        }

        modal.style.display = 'flex';
    }

    document.getElementById('villa-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const id  = document.getElementById('villa-id').value;
        const btn = document.getElementById('villa-submit-btn');
        const origText = btn.textContent;
        btn.disabled    = true;
        btn.textContent = 'Đang lưu...';

        const { image, gallery } = collectVillaGallery();

        const payload = {
            name:          document.getElementById('villa-name').value,
            slug:          document.getElementById('villa-slug').value,
            location:      document.getElementById('villa-location').value,
            location_desc: document.getElementById('villa-location-desc').value,
            beds:          document.getElementById('villa-beds').value,
            guests:        document.getElementById('villa-guests').value,
            image,
            gallery,
            status:        document.getElementById('villa-status').value,
            description:   document.getElementById('villa-description').value || null,
        };

        const url    = id ? (ADMIN_BASE + '/villas/' + id) : (ADMIN_BASE + '/villas');
        const method = id ? 'PUT' : 'POST';
        const res    = await apiFetch(url, { method, body: payload });

        btn.disabled    = false;
        btn.textContent = origText;

        if (res.success) {
            document.getElementById('villa-modal').style.display = 'none';
            loadVillas(villaPage);
        } else {
            const errEl = document.getElementById('villa-form-error');
            let msg = res.message || 'Lỗi không xác định.';
            if (res.errors) msg = Object.values(res.errors).flat().join(' ');
            if (errEl) { errEl.textContent = msg; errEl.style.display = 'block'; }
        }
    });

    async function deleteVilla(id, name) {
        if (!confirm(`Xóa villa "${name}"?\nHành động này không thể hoàn tác.`)) return;

        const res = await apiFetch(ADMIN_BASE + '/villas/' + id, { method: 'DELETE' });
        if (res.success) {
            loadVillas(villaPage);
        } else {
            alert('Lỗi: ' + (res.message || 'Không thể xóa.'));
        }
    }

    async function toggleVillaStatus(id, btn) {
        const res = await apiFetch(ADMIN_BASE + '/villas/' + id + '/status', { method: 'PATCH' });
        if (res.success) {
            const isActive = res.status === 'active';
            btn.textContent = isActive ? 'Hoạt động' : 'Tạm ẩn';
            btn.style.background = isActive ? '#dcfce7' : '#fee2e2';
            btn.style.color      = isActive ? '#166534' : '#dc2626';
        }
    }

    function closeVillaModal() {
        const modal = document.getElementById('villa-modal');
        if (modal) modal.style.display = 'none';
    }

    document.getElementById('villa-modal-close')?.addEventListener('click', closeVillaModal);
    document.getElementById('villa-modal-cancel')?.addEventListener('click', closeVillaModal);
    document.getElementById('villa-modal')?.addEventListener('click', function (e) {
        if (e.target === this) closeVillaModal();
    });

    // ---------------------------------------------------------------
    // Business Settings
    // ---------------------------------------------------------------

    function renderSettingsLogo(url) {
        const empty   = document.getElementById('settings-logo-empty');
        const preview = document.getElementById('settings-logo-preview');
        if (!empty || !preview) return;

        if (url) {
            preview.src           = url;
            preview.style.display = 'block';
            empty.style.display   = 'none';
        } else {
            preview.style.display = 'none';
            empty.style.display   = 'flex';
        }
    }

    async function loadSettings() {
        const res = await apiFetch(ADMIN_BASE + '/settings');
        if (!res.success) return;

        const s = res.data;
        document.getElementById('settings-site-name').value   = s.site_name || '';
        document.getElementById('settings-hotline').value     = s.hotline || '';
        document.getElementById('settings-email').value       = s.email || '';
        document.getElementById('settings-address').value     = s.address || '';
        document.getElementById('settings-map-link').value    = s.map_link || '';
        document.getElementById('settings-facebook').value    = s.facebook_url || '';
        document.getElementById('settings-instagram').value   = s.instagram_url || '';
        document.getElementById('settings-youtube').value     = s.youtube_url || '';
        document.getElementById('settings-footer-desc').value = s.footer_description || '';
        document.getElementById('settings-logo').value        = s.logo || '';
        renderSettingsLogo(s.logo || '');
    }

    function openSettingsLogoPicker() {
        const fi = document.getElementById('settings-logo-input');
        if (fi) { fi.value = ''; fi.click(); }
    }

    function clearSettingsLogo() {
        document.getElementById('settings-logo').value = '';
        renderSettingsLogo('');
    }

    document.getElementById('settings-logo-input')?.addEventListener('change', async function () {
        const file = this.files[0];
        if (!file) return;

        const uploading = document.getElementById('settings-logo-uploading');
        const bar       = document.getElementById('settings-logo-bar');

        if (uploading) uploading.style.display = 'flex';
        if (bar)       bar.style.width = '0%';

        let pct = 0;
        const ticker = setInterval(() => {
            pct = Math.min(pct + 12, 88);
            if (bar) bar.style.width = pct + '%';
        }, 100);

        const form = new FormData();
        form.append('image', file);

        try {
            const res = await fetch(ADMIN_BASE + '/settings/upload-logo', {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': csrf() },
                body:    form,
            }).then(r => r.json());

            clearInterval(ticker);
            if (bar) bar.style.width = '100%';

            if (res.success) {
                document.getElementById('settings-logo').value = res.url;
                renderSettingsLogo(res.url);
            } else {
                alert('Upload thất bại: ' + (res.message || 'Lỗi'));
            }
        } catch {
            clearInterval(ticker);
            alert('Lỗi kết nối khi tải ảnh.');
        } finally {
            setTimeout(() => {
                if (uploading) uploading.style.display = 'none';
                if (bar)       bar.style.width = '0%';
            }, 500);
        }
    });

    document.getElementById('settings-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const errEl = document.getElementById('settings-form-error');
        const okEl  = document.getElementById('settings-form-success');
        errEl.style.display = 'none';
        okEl.style.display  = 'none';

        const payload = {
            site_name:          document.getElementById('settings-site-name').value.trim(),
            logo:               document.getElementById('settings-logo').value.trim() || null,
            hotline:            document.getElementById('settings-hotline').value.trim() || null,
            email:              document.getElementById('settings-email').value.trim() || null,
            address:            document.getElementById('settings-address').value.trim() || null,
            map_link:           document.getElementById('settings-map-link').value.trim() || null,
            facebook_url:       document.getElementById('settings-facebook').value.trim() || null,
            instagram_url:      document.getElementById('settings-instagram').value.trim() || null,
            youtube_url:        document.getElementById('settings-youtube').value.trim() || null,
            footer_description: document.getElementById('settings-footer-desc').value.trim() || null,
        };

        const btn  = document.getElementById('settings-submit-btn');
        const orig = btn.innerHTML;
        btn.disabled  = true;
        btn.innerHTML = 'Đang lưu...';

        const res = await apiFetch(ADMIN_BASE + '/settings', { method: 'POST', body: payload });

        btn.disabled  = false;
        btn.innerHTML = orig;

        if (res.success) {
            okEl.textContent   = 'Đã lưu thông tin doanh nghiệp!';
            okEl.style.display = 'block';
        } else if (res.errors) {
            errEl.innerHTML = Object.values(res.errors).flat().join('<br>');
            errEl.style.display = 'block';
        } else {
            errEl.textContent   = res.message || 'Có lỗi xảy ra, vui lòng thử lại.';
            errEl.style.display = 'block';
        }
    });

    // ---------------------------------------------------------------
    // News
    // ---------------------------------------------------------------

    let newsPage = 1;
    let newsSearchTimer = null;

    async function loadNews(page = 1) {
        newsPage = page;
        const search = (document.getElementById('news-search') || {}).value || '';
        const tbody  = document.getElementById('news-list-body');
        if (!tbody) return;

        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--lux-gray);">Đang tải...</td></tr>';

        const params = new URLSearchParams({ page, search });
        const data   = await apiFetch(ADMIN_BASE + '/news?' + params.toString());

        if (!data.success) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:#e11d48;padding:30px;">${data.message || 'Lỗi tải dữ liệu.'}</td></tr>`;
            return;
        }

        const items      = (data.data && data.data.data) ? data.data.data : (data.data || []);
        const pagination = data.data || {};

        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--lux-gray);">Chưa có bài viết nào.</td></tr>';
            return;
        }

        tbody.innerHTML = items.map(n => {
            const escaped   = JSON.stringify(n).replace(/"/g, '&quot;');
            const isActive  = n.status === 'active';
            const published = n.published_at ? formatDate(n.published_at) : '-';

            return `<tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        ${n.image
                            ? `<img src="${n.image}" style="width:48px;height:38px;object-fit:cover;border-radius:8px;flex-shrink:0;">`
                            : `<div style="width:48px;height:38px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="ph ph-image" style="color:#94a3b8;"></i></div>`
                        }
                        <div>
                            <strong style="display:block;">${n.title}</strong>
                            <span style="font-size:.8rem;color:var(--lux-gray);">#${n.id}</span>
                        </div>
                    </div>
                </td>
                <td style="color:var(--lux-gray);">${n.tag || '—'}</td>
                <td style="color:var(--lux-gray);">${published}</td>
                <td>
                    <span class="status-badge" style="background:${isActive ? '#dcfce7' : '#fee2e2'};color:${isActive ? '#166534' : '#dc2626'};">
                        ${isActive ? 'Hiển thị' : 'Bản nháp'}
                    </span>
                </td>
                <td>
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <button class="btn-view" onclick='AdminApp.openNewsModal(${escaped})'>Sửa</button>
                        <button class="btn-view" style="color:#dc2626;border-color:#fecdd3;"
                                onclick="AdminApp.deleteNews(${n.id},'${n.title.replace(/'/g, "\\'")}')">Xóa</button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        renderPagination('news-pagination', pagination.last_page || 1, pagination.current_page || 1, loadNews);
    }

    document.getElementById('news-search')?.addEventListener('input', function () {
        clearTimeout(newsSearchTimer);
        newsSearchTimer = setTimeout(() => loadNews(), 500);
    });

    // ── News image (single slot) ──────────────────────────────────

    function renderNewsImage(url) {
        const empty   = document.getElementById('news-image-empty');
        const preview = document.getElementById('news-image-preview');
        const slot    = document.getElementById('news-image-slot');
        if (!empty || !preview) return;

        if (url) {
            preview.src            = url;
            preview.style.display  = 'block';
            empty.style.display    = 'none';
            if (slot) { slot.style.borderStyle = 'solid'; slot.style.borderColor = '#e2e8f0'; }
        } else {
            preview.style.display  = 'none';
            empty.style.display    = 'flex';
            if (slot) { slot.style.borderStyle = 'dashed'; slot.style.borderColor = 'var(--border-strong)'; }
        }
    }

    function openNewsImagePicker() {
        const fi = document.getElementById('news-file-input');
        if (fi) { fi.value = ''; fi.click(); }
    }

    function clearNewsImage() {
        document.getElementById('news-image').value = '';
        renderNewsImage('');
    }

    document.getElementById('news-file-input')?.addEventListener('change', function () {
        if (this.files[0]) uploadNewsImageFile(this.files[0]);
    });

    function handleNewsImageDrop(e) {
        e.preventDefault();
        const slot = document.getElementById('news-image-slot');
        if (slot) slot.style.borderColor = 'var(--border-strong)';
        const file = e.dataTransfer?.files?.[0];
        if (file && file.type.startsWith('image/')) uploadNewsImageFile(file);
    }

    async function uploadNewsImageFile(file) {
        const uploading = document.getElementById('news-image-uploading');
        const bar       = document.getElementById('news-image-bar');

        if (uploading) uploading.style.display = 'flex';
        if (bar)       bar.style.width = '0%';

        let pct = 0;
        const ticker = setInterval(() => {
            pct = Math.min(pct + 12, 88);
            if (bar) bar.style.width = pct + '%';
        }, 100);

        const form = new FormData();
        form.append('image', file);

        try {
            const res = await fetch(ADMIN_BASE + '/news/upload-image', {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': csrf() },
                body:    form,
            }).then(r => r.json());

            clearInterval(ticker);
            if (bar) bar.style.width = '100%';

            if (res.success) {
                document.getElementById('news-image').value = res.url;
                renderNewsImage(res.url);
            } else {
                alert('Upload thất bại: ' + (res.message || 'Lỗi'));
            }
        } catch {
            clearInterval(ticker);
            alert('Lỗi kết nối khi tải ảnh.');
        } finally {
            setTimeout(() => {
                if (uploading) uploading.style.display = 'none';
                if (bar)       bar.style.width = '0%';
            }, 500);
        }
    }

    function openNewsModal(article) {
        const modal = document.getElementById('news-modal');
        const title = document.getElementById('news-modal-title');
        if (!modal) return;

        ['news-id','news-title','news-tag','news-published-at','news-excerpt','news-content','news-image'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        document.getElementById('news-status').value = 'active';
        renderNewsImage('');
        const fileInput = document.getElementById('news-file-input');
        if (fileInput) fileInput.value = '';
        const errEl = document.getElementById('news-form-error');
        if (errEl) errEl.style.display = 'none';

        if (article && article.id) {
            title.textContent = 'Sửa bài viết';
            document.getElementById('news-id').value           = article.id;
            document.getElementById('news-title').value        = article.title || '';
            document.getElementById('news-tag').value          = article.tag || '';
            document.getElementById('news-published-at').value = article.published_at ? String(article.published_at).slice(0, 10) : '';
            document.getElementById('news-excerpt').value      = article.excerpt || '';
            document.getElementById('news-content').value      = article.content || '';
            document.getElementById('news-status').value       = article.status || 'active';
            document.getElementById('news-image').value        = article.image || '';
            renderNewsImage(article.image || '');
        } else {
            title.textContent = 'Thêm bài viết';
        }

        modal.style.display = 'flex';
    }

    document.getElementById('news-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const id  = document.getElementById('news-id').value;
        const btn = document.getElementById('news-submit-btn');
        const origText = btn.textContent;
        btn.disabled    = true;
        btn.textContent = 'Đang lưu...';

        const payload = {
            title:        document.getElementById('news-title').value,
            tag:          document.getElementById('news-tag').value || null,
            published_at: document.getElementById('news-published-at').value || null,
            excerpt:      document.getElementById('news-excerpt').value || null,
            content:      document.getElementById('news-content').value || null,
            image:        document.getElementById('news-image').value || null,
            status:       document.getElementById('news-status').value,
        };

        const url    = id ? (ADMIN_BASE + '/news/' + id) : (ADMIN_BASE + '/news');
        const method = id ? 'PUT' : 'POST';
        const res    = await apiFetch(url, { method, body: payload });

        btn.disabled    = false;
        btn.textContent = origText;

        if (res.success) {
            document.getElementById('news-modal').style.display = 'none';
            loadNews(newsPage);
        } else {
            const errEl = document.getElementById('news-form-error');
            let msg = res.message || 'Lỗi không xác định.';
            if (res.errors) msg = Object.values(res.errors).flat().join(' ');
            if (errEl) { errEl.textContent = msg; errEl.style.display = 'block'; }
        }
    });

    async function deleteNews(id, title) {
        if (!confirm(`Xóa bài viết "${title}"?\nHành động này không thể hoàn tác.`)) return;

        const res = await apiFetch(ADMIN_BASE + '/news/' + id, { method: 'DELETE' });
        if (res.success) {
            loadNews(newsPage);
        } else {
            alert('Lỗi: ' + (res.message || 'Không thể xóa.'));
        }
    }

    function closeNewsModal() {
        const modal = document.getElementById('news-modal');
        if (modal) modal.style.display = 'none';
    }

    document.getElementById('news-modal-close')?.addEventListener('click', closeNewsModal);
    document.getElementById('news-modal-cancel')?.addEventListener('click', closeNewsModal);
    document.getElementById('news-modal')?.addEventListener('click', function (e) {
        if (e.target === this) closeNewsModal();
    });

    // ---------------------------------------------------------------
    // FAQs
    // ---------------------------------------------------------------

    async function loadFaqs() {
        const tbody = document.getElementById('faqs-list-body');
        if (!tbody) return;

        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:40px;color:var(--lux-gray);">Đang tải...</td></tr>';

        const data = await apiFetch(ADMIN_BASE + '/faqs');

        if (!data.success) {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:#e11d48;padding:30px;">${data.message || 'Lỗi tải dữ liệu.'}</td></tr>`;
            return;
        }

        const items = data.data || [];

        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:40px;color:var(--lux-gray);">Chưa có câu hỏi nào.</td></tr>';
            return;
        }

        tbody.innerHTML = items.map(f => {
            const escaped = JSON.stringify(f).replace(/"/g, '&quot;');
            return `<tr>
                <td style="color:var(--lux-gray);">${f.group_name}</td>
                <td><strong>${f.question}</strong></td>
                <td style="color:var(--lux-gray);">${f.sort_order}</td>
                <td>
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <button class="btn-view" onclick='AdminApp.openFaqModal(${escaped})'>Sửa</button>
                        <button class="btn-view" style="color:#dc2626;border-color:#fecdd3;"
                                onclick="AdminApp.deleteFaq(${f.id},'${f.question.replace(/'/g, "\\'")}')">Xóa</button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    function openFaqModal(faq) {
        const modal = document.getElementById('faq-modal');
        const title = document.getElementById('faq-modal-title');
        if (!modal) return;

        ['faq-id','faq-group','faq-question','faq-answer','faq-sort-order'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        const errEl = document.getElementById('faq-form-error');
        if (errEl) errEl.style.display = 'none';

        if (faq && faq.id) {
            title.textContent = 'Sửa câu hỏi';
            document.getElementById('faq-id').value         = faq.id;
            document.getElementById('faq-group').value      = faq.group_name || '';
            document.getElementById('faq-question').value   = faq.question || '';
            document.getElementById('faq-answer').value     = faq.answer || '';
            document.getElementById('faq-sort-order').value = (faq.sort_order ?? '');
        } else {
            title.textContent = 'Thêm câu hỏi';
        }

        modal.style.display = 'flex';
    }

    document.getElementById('faq-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const id  = document.getElementById('faq-id').value;
        const btn = document.getElementById('faq-submit-btn');
        const origText = btn.textContent;
        btn.disabled    = true;
        btn.textContent = 'Đang lưu...';

        const sortOrderRaw = document.getElementById('faq-sort-order').value;

        const payload = {
            group_name: document.getElementById('faq-group').value,
            question:   document.getElementById('faq-question').value,
            answer:     document.getElementById('faq-answer').value,
            sort_order: sortOrderRaw !== '' ? parseInt(sortOrderRaw) : null,
        };

        const url    = id ? (ADMIN_BASE + '/faqs/' + id) : (ADMIN_BASE + '/faqs');
        const method = id ? 'PUT' : 'POST';
        const res    = await apiFetch(url, { method, body: payload });

        btn.disabled    = false;
        btn.textContent = origText;

        if (res.success) {
            document.getElementById('faq-modal').style.display = 'none';
            loadFaqs();
        } else {
            const errEl = document.getElementById('faq-form-error');
            let msg = res.message || 'Lỗi không xác định.';
            if (res.errors) msg = Object.values(res.errors).flat().join(' ');
            if (errEl) { errEl.textContent = msg; errEl.style.display = 'block'; }
        }
    });

    async function deleteFaq(id, question) {
        if (!confirm(`Xóa câu hỏi "${question}"?\nHành động này không thể hoàn tác.`)) return;

        const res = await apiFetch(ADMIN_BASE + '/faqs/' + id, { method: 'DELETE' });
        if (res.success) {
            loadFaqs();
        } else {
            alert('Lỗi: ' + (res.message || 'Không thể xóa.'));
        }
    }

    function closeFaqModal() {
        const modal = document.getElementById('faq-modal');
        if (modal) modal.style.display = 'none';
    }

    document.getElementById('faq-modal-close')?.addEventListener('click', closeFaqModal);
    document.getElementById('faq-modal-cancel')?.addEventListener('click', closeFaqModal);
    document.getElementById('faq-modal')?.addEventListener('click', function (e) {
        if (e.target === this) closeFaqModal();
    });

    // ---------------------------------------------------------------
    // Page Content (Giới thiệu / Hợp tác)
    // ---------------------------------------------------------------

    const ABOUT_KEYS = (() => {
        const keys = ['hero_title', 'hero_subtitle', 'story_title', 'story_paragraph_1', 'story_paragraph_2', 'why_title'];
        for (let i = 1; i <= 3; i++) keys.push(`why_card_${i}_icon`, `why_card_${i}_title`, `why_card_${i}_text`);
        for (let i = 1; i <= 4; i++) keys.push(`stat_${i}_number`, `stat_${i}_label`);
        keys.push('cta_title', 'cta_text', 'cta_button');
        return keys;
    })();

    const PARTNER_KEYS = (() => {
        const keys = ['hero_title', 'hero_subtitle', 'types_title'];
        for (let i = 1; i <= 3; i++) keys.push(`type_${i}_icon`, `type_${i}_title`, `type_${i}_text`);
        keys.push('benefits_title');
        for (let i = 1; i <= 4; i++) keys.push(`benefit_${i}_icon`, `benefit_${i}_title`, `benefit_${i}_text`);
        return keys;
    })();

    async function loadPageContentSlug(slug, keys) {
        const res = await apiFetch(ADMIN_BASE + '/page-contents/' + slug);
        if (!res.success) return;

        keys.forEach(key => {
            const el = document.getElementById(slug + '-' + key);
            if (el) el.value = res.data[key] || '';
        });
    }

    function loadPageContent() {
        loadPageContentSlug('about', ABOUT_KEYS);
        loadPageContentSlug('partner', PARTNER_KEYS);
    }

    async function submitPageContent(slug, keys) {
        const errEl = document.getElementById(slug + '-content-error');
        const okEl  = document.getElementById(slug + '-content-success');
        if (errEl) errEl.style.display = 'none';
        if (okEl)  okEl.style.display  = 'none';

        const payload = {};
        keys.forEach(key => {
            const el = document.getElementById(slug + '-' + key);
            payload[key] = el ? el.value : '';
        });

        const form = document.getElementById(slug + '-content-form');
        const btn  = form.querySelector('button[type="submit"]');
        const orig = btn.innerHTML;
        btn.disabled  = true;
        btn.innerHTML = 'Đang lưu...';

        const res = await apiFetch(ADMIN_BASE + '/page-contents/' + slug, { method: 'POST', body: payload });

        btn.disabled  = false;
        btn.innerHTML = orig;

        if (res.success) {
            if (okEl) { okEl.textContent = 'Đã lưu nội dung!'; okEl.style.display = 'block'; }
        } else if (res.errors) {
            if (errEl) { errEl.innerHTML = Object.values(res.errors).flat().join('<br>'); errEl.style.display = 'block'; }
        } else {
            if (errEl) { errEl.textContent = res.message || 'Có lỗi xảy ra, vui lòng thử lại.'; errEl.style.display = 'block'; }
        }
    }

    document.getElementById('about-content-form')?.addEventListener('submit', function (e) {
        e.preventDefault();
        submitPageContent('about', ABOUT_KEYS);
    });

    document.getElementById('partner-content-form')?.addEventListener('submit', function (e) {
        e.preventDefault();
        submitPageContent('partner', PARTNER_KEYS);
    });

    // ---------------------------------------------------------------
    // Public API
    // ---------------------------------------------------------------

    window.AdminApp = {
        openBookingModal,
        performCheckin,
        cancelBooking,
        openMemberModal,
        deleteMember,
        openRoomModal,
        deleteRoom,
        toggleRoomStatus,
        loadRooms,
        switchTab,
        // Gallery slots
        openSlotPicker,
        handleSlotDrop,
        clearSlot,
        handleRoomDrop,
        clearRoomImage: clearRoomPreview,
        // Villas
        loadVillas,
        openVillaModal,
        deleteVilla,
        toggleVillaStatus,
        openVillaSlotPicker,
        handleVillaSlotDrop,
        clearVillaSlot,
        // Settings
        loadSettings,
        openSettingsLogoPicker,
        clearSettingsLogo,
        // News
        openNewsModal,
        deleteNews,
        openNewsImagePicker,
        handleNewsImageDrop,
        clearNewsImage,
        // FAQs
        openFaqModal,
        deleteFaq,
    };

})();
