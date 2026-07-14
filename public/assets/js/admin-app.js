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
        if (tab === 'pagecontent')    loadPageContent();
        if (tab === 'remarketing')    loadRemarketing();
        if (tab === 'voucher')        loadVouchers();
        if (tab === 'emailmarketing') { loadCampaigns(); loadVoucherOptions(); }
        if (tab === 'gallery')        loadGalleryPhotos();
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

    // ── Gallery: 10 slots ─────────────────────────────────────────

    const ROOM_GALLERY_SIZE = 10;
    // In-memory state: array of 10 URLs (null = empty)
    let gallerySlots = new Array(ROOM_GALLERY_SIZE).fill(null);
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
        gallerySlots = new Array(ROOM_GALLERY_SIZE).fill(null);
        for (let i = 0; i < ROOM_GALLERY_SIZE; i++) renderSlot(i);
        const hiddenImg = document.getElementById('room-image');
        if (hiddenImg) hiddenImg.value = '';
    }

    function loadGalleryFromRoom(room) {
        gallerySlots = new Array(ROOM_GALLERY_SIZE).fill(null);
        if (room.image)   gallerySlots[0] = room.image;
        const extra = Array.isArray(room.gallery) ? room.gallery : [];
        extra.forEach((url, i) => { if (i + 1 < ROOM_GALLERY_SIZE) gallerySlots[i + 1] = url; });
        for (let i = 0; i < ROOM_GALLERY_SIZE; i++) renderSlot(i);
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

    // ── Video upload ──────────────────────────────────────────────

    function renderRoomVideo() {
        const url     = document.getElementById('room-video')?.value || '';
        const empty   = document.getElementById('room-video-empty');
        const preview = document.getElementById('room-video-preview');
        if (!empty || !preview) return;

        if (url) {
            preview.src           = url;
            preview.style.display = 'block';
            empty.style.display   = 'none';
        } else {
            preview.removeAttribute('src');
            preview.style.display = 'none';
            empty.style.display   = 'flex';
        }
    }

    function resetRoomVideo() {
        const hi = document.getElementById('room-video');
        if (hi) hi.value = '';
        const yt = document.getElementById('room-video-youtube-url');
        if (yt) yt.value = '';
        renderRoomVideo();
    }

    function setRoomVideoModeUI(mode) {
        const uploadBlock  = document.getElementById('room-video-upload-block');
        const youtubeBlock = document.getElementById('room-video-youtube-block');
        if (uploadBlock)  uploadBlock.style.display  = mode === 'youtube' ? 'none' : 'block';
        if (youtubeBlock) youtubeBlock.style.display = mode === 'youtube' ? 'block' : 'none';
        const radio = document.getElementById('room-video-mode-' + mode);
        if (radio) radio.checked = true;
    }

    function switchRoomVideoMode(mode) {
        setRoomVideoModeUI(mode);
        resetRoomVideo();
    }

    function loadRoomVideo(room) {
        const hi  = document.getElementById('room-video');
        const url = room.video || '';
        if (hi) hi.value = url;

        const isYoutube = /youtube\.com|youtu\.be/i.test(url);
        if (isYoutube) {
            setRoomVideoModeUI('youtube');
            const yt = document.getElementById('room-video-youtube-url');
            if (yt) yt.value = url;
        } else {
            setRoomVideoModeUI('upload');
            renderRoomVideo();
        }
    }

    function openRoomVideoPicker() {
        const fi = document.getElementById('room-video-input');
        if (fi) { fi.value = ''; fi.click(); }
    }

    function clearRoomVideo() {
        resetRoomVideo();
    }

    document.getElementById('room-video-youtube-url')?.addEventListener('input', function () {
        const hi = document.getElementById('room-video');
        if (hi) hi.value = this.value.trim();
    });

    document.getElementById('room-video-input')?.addEventListener('change', async function () {
        const file = this.files[0];
        if (!file) return;

        const uploading = document.getElementById('room-video-uploading');
        const bar       = document.getElementById('room-video-bar');
        if (uploading) uploading.style.display = 'flex';
        if (bar)       bar.style.width = '0%';

        let pct = 0;
        const ticker = setInterval(() => {
            pct = Math.min(pct + 8, 88);
            if (bar) bar.style.width = pct + '%';
        }, 150);

        const form = new FormData();
        form.append('video', file);

        try {
            const res = await fetch(ADMIN_BASE + '/rooms/upload-video', {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': csrf() },
                body:    form,
            }).then(r => r.json());

            clearInterval(ticker);
            if (bar) bar.style.width = '100%';

            if (res.success) {
                document.getElementById('room-video').value = res.url;
                renderRoomVideo();
            } else {
                alert('Upload video thất bại: ' + (res.message || 'Lỗi'));
            }
        } catch {
            clearInterval(ticker);
            alert('Lỗi kết nối khi tải video.');
        } finally {
            setTimeout(() => {
                if (uploading) uploading.style.display = 'none';
                if (bar)       bar.style.width = '0%';
            }, 500);
        }
    });

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
        resetRoomVideo();
        setRoomVideoModeUI('upload');
        const fileInput = document.getElementById('room-file-input');
        if (fileInput) fileInput.value = '';
        const videoInput = document.getElementById('room-video-input');
        if (videoInput) videoInput.value = '';
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
            loadRoomVideo(room);
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
            video:               document.getElementById('room-video').value || null,
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

    // ── Video upload ──────────────────────────────────────────────

    function renderVillaVideo() {
        const url     = document.getElementById('villa-video')?.value || '';
        const empty   = document.getElementById('villa-video-empty');
        const preview = document.getElementById('villa-video-preview');
        if (!empty || !preview) return;

        if (url) {
            preview.src           = url;
            preview.style.display = 'block';
            empty.style.display   = 'none';
        } else {
            preview.removeAttribute('src');
            preview.style.display = 'none';
            empty.style.display   = 'flex';
        }
    }

    function resetVillaVideo() {
        const hi = document.getElementById('villa-video');
        if (hi) hi.value = '';
        const yt = document.getElementById('villa-video-youtube-url');
        if (yt) yt.value = '';
        renderVillaVideo();
    }

    function setVillaVideoModeUI(mode) {
        const uploadBlock  = document.getElementById('villa-video-upload-block');
        const youtubeBlock = document.getElementById('villa-video-youtube-block');
        if (uploadBlock)  uploadBlock.style.display  = mode === 'youtube' ? 'none' : 'block';
        if (youtubeBlock) youtubeBlock.style.display = mode === 'youtube' ? 'block' : 'none';
        const radio = document.getElementById('villa-video-mode-' + mode);
        if (radio) radio.checked = true;
    }

    function switchVillaVideoMode(mode) {
        setVillaVideoModeUI(mode);
        resetVillaVideo();
    }

    function loadVillaVideo(villa) {
        const hi  = document.getElementById('villa-video');
        const url = villa.video || '';
        if (hi) hi.value = url;

        const isYoutube = /youtube\.com|youtu\.be/i.test(url);
        if (isYoutube) {
            setVillaVideoModeUI('youtube');
            const yt = document.getElementById('villa-video-youtube-url');
            if (yt) yt.value = url;
        } else {
            setVillaVideoModeUI('upload');
            renderVillaVideo();
        }
    }

    function openVillaVideoPicker() {
        const fi = document.getElementById('villa-video-input');
        if (fi) { fi.value = ''; fi.click(); }
    }

    function clearVillaVideo() {
        resetVillaVideo();
    }

    document.getElementById('villa-video-youtube-url')?.addEventListener('input', function () {
        const hi = document.getElementById('villa-video');
        if (hi) hi.value = this.value.trim();
    });

    document.getElementById('villa-video-input')?.addEventListener('change', async function () {
        const file = this.files[0];
        if (!file) return;

        const uploading = document.getElementById('villa-video-uploading');
        const bar       = document.getElementById('villa-video-bar');
        if (uploading) uploading.style.display = 'flex';
        if (bar)       bar.style.width = '0%';

        let pct = 0;
        const ticker = setInterval(() => {
            pct = Math.min(pct + 8, 88);
            if (bar) bar.style.width = pct + '%';
        }, 150);

        const form = new FormData();
        form.append('video', file);

        try {
            const res = await fetch(ADMIN_BASE + '/villas/upload-video', {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': csrf() },
                body:    form,
            }).then(r => r.json());

            clearInterval(ticker);
            if (bar) bar.style.width = '100%';

            if (res.success) {
                document.getElementById('villa-video').value = res.url;
                renderVillaVideo();
            } else {
                alert('Upload video thất bại: ' + (res.message || 'Lỗi'));
            }
        } catch {
            clearInterval(ticker);
            alert('Lỗi kết nối khi tải video.');
        } finally {
            setTimeout(() => {
                if (uploading) uploading.style.display = 'none';
                if (bar)       bar.style.width = '0%';
            }, 500);
        }
    });

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
        resetVillaVideo();
        setVillaVideoModeUI('upload');
        const fileInput = document.getElementById('villa-file-input');
        if (fileInput) fileInput.value = '';
        const videoInput = document.getElementById('villa-video-input');
        if (videoInput) videoInput.value = '';
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
            loadVillaVideo(villa);
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
            video:         document.getElementById('villa-video').value || null,
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
        document.getElementById('settings-og-image').value    = s.og_image || '';
        renderSettingsOg(s.og_image || '');
    }

    function renderSettingsOg(url) {
        const empty   = document.getElementById('settings-og-empty');
        const preview = document.getElementById('settings-og-preview');
        if (!empty || !preview) return;
        if (url) {
            preview.src = url; preview.style.display = 'block'; empty.style.display = 'none';
        } else {
            preview.style.display = 'none'; empty.style.display = 'flex';
        }
    }

    function openSettingsOgPicker() {
        const fi = document.getElementById('settings-og-input');
        if (fi) { fi.value = ''; fi.click(); }
    }

    function clearSettingsOg() {
        document.getElementById('settings-og-image').value = '';
        renderSettingsOg('');
    }

    document.getElementById('settings-og-input')?.addEventListener('change', async function () {
        const file = this.files[0];
        if (!file) return;
        const uploading = document.getElementById('settings-og-uploading');
        const bar       = document.getElementById('settings-og-bar');
        if (uploading) uploading.style.display = 'flex';
        if (bar) bar.style.width = '0%';
        let pct = 0;
        const ticker = setInterval(() => { pct = Math.min(pct + 10, 85); if (bar) bar.style.width = pct + '%'; }, 120);
        try {
            const fd = new FormData();
            fd.append('image', file);
            fd.append('_token', csrf());
            const r = await fetch(ADMIN_BASE + '/settings/upload-og-image', { method: 'POST', body: fd });
            const res = await r.json();
            clearInterval(ticker);
            if (bar) bar.style.width = '100%';
            if (res.success) {
                document.getElementById('settings-og-image').value = res.url;
                renderSettingsOg(res.url);
            } else { alert('Lỗi: ' + (res.message || 'Tải ảnh thất bại.')); }
        } catch { clearInterval(ticker); alert('Lỗi kết nối khi tải ảnh.'); }
        finally { setTimeout(() => { if (uploading) uploading.style.display = 'none'; if (bar) bar.style.width = '0%'; }, 500); }
    });

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
            og_image:           document.getElementById('settings-og-image').value.trim() || null,
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

        ['news-id','news-title','news-slug','news-tag','news-published-at','news-excerpt','news-content','news-image'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        const newsSlugEl = document.getElementById('news-slug');
        if (newsSlugEl) delete newsSlugEl.dataset.manual;
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
            document.getElementById('news-slug').value         = article.slug || '';
            if (article.slug) document.getElementById('news-slug').dataset.manual = '1';
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
            slug:         document.getElementById('news-slug').value || null,
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

    document.getElementById('news-title')?.addEventListener('input', function () {
        const slugEl = document.getElementById('news-slug');
        if (!slugEl || slugEl.dataset.manual) return;
        slugEl.value = this.value
            .toLowerCase()
            .normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/đ/g, 'd').replace(/[^a-z0-9\s-]/g, '')
            .trim().replace(/\s+/g, '-');
    });
    document.getElementById('news-slug')?.addEventListener('input', function () {
        this.dataset.manual = this.value ? '1' : '';
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
    // Gallery Photos (admin only)
    // ---------------------------------------------------------------

    let galleryPhotoUploading = false;
    let galleryPhotoImagePath = '';

    async function loadGalleryPhotos() {
        if (typeof USER_ROLE !== 'undefined' && USER_ROLE !== 'admin') return;
        const grid = document.getElementById('gallery-admin-grid');
        if (!grid) return;
        grid.innerHTML = '<div class="table-empty-state"><i class="ph ph-spinner"></i><span>Đang tải...</span></div>';

        const res = await apiFetch(ADMIN_BASE + '/gallery-photos');
        if (!res?.success) {
            grid.innerHTML = '<div class="table-empty-state"><i class="ph ph-warning"></i><span>Không tải được ảnh.</span></div>';
            return;
        }
        const photos = res.data;
        if (!photos.length) {
            grid.innerHTML = '<div class="table-empty-state"><i class="ph ph-images"></i><span>Chưa có ảnh nào. Nhấn "Thêm ảnh" để bắt đầu.</span></div>';
            return;
        }

        grid.innerHTML = '';
        photos.forEach(photo => {
            const imgUrl  = photo.image.startsWith('http') ? photo.image : `/storage/${photo.image}`;
            const inactive = !photo.is_active ? 'gallery-admin-card--inactive' : '';
            const card = document.createElement('div');
            card.className = `gallery-admin-card ${inactive}`;
            card.innerHTML = `
                <img class="gallery-admin-card__thumb" src="${imgUrl}" alt="${photo.caption || ''}">
                <div class="gallery-admin-card__body">
                    <div class="gallery-admin-card__caption">${photo.caption || '<em style="opacity:.5">Không có chú thích</em>'}</div>
                    <div class="gallery-admin-card__meta">Thứ tự: ${photo.sort_order} · ${photo.is_active ? '<span style="color:#16a34a">Hiện</span>' : '<span style="color:#dc2626">Ẩn</span>'}</div>
                    <div class="gallery-admin-card__actions">
                        <button onclick="AdminApp.openGalleryPhotoModal(${JSON.stringify(photo).replace(/"/g, '&quot;')})">✏ Sửa</button>
                        <button onclick="AdminApp.toggleGalleryPhotoStatus(${photo.id}, this)">${photo.is_active ? '👁 Ẩn' : '👁 Hiện'}</button>
                        <button class="del" onclick="AdminApp.deleteGalleryPhoto(${photo.id})">🗑</button>
                    </div>
                </div>`;
            grid.appendChild(card);
        });

        const addCard = document.createElement('div');
        addCard.className = 'gallery-admin-card--add';
        addCard.innerHTML = '<i class="ph ph-plus" style="font-size:1.6rem;"></i><span>Thêm ảnh</span>';
        addCard.onclick = () => openGalleryPhotoModal();
        grid.appendChild(addCard);
    }

    function openGalleryPhotoModal(photo = null) {
        if (typeof USER_ROLE !== 'undefined' && USER_ROLE !== 'admin') return;
        document.getElementById('gallery-photo-modal-title').textContent = photo ? 'Chỉnh sửa ảnh' : 'Thêm ảnh gallery';
        document.getElementById('gallery-photo-id').value = photo?.id ?? '';
        document.getElementById('gallery-photo-caption').value = photo?.caption ?? '';
        document.getElementById('gallery-photo-sort').value = photo?.sort_order ?? 0;
        document.getElementById('gallery-photo-active').checked = photo ? !!photo.is_active : true;
        document.getElementById('gallery-photo-form-error').style.display = 'none';
        document.getElementById('gallery-photo-file-input').value = '';
        galleryPhotoImagePath = photo?.image ?? '';

        const preview     = document.getElementById('gallery-photo-preview');
        const placeholder = document.getElementById('gallery-photo-placeholder');
        const clearBtn    = document.getElementById('gallery-photo-clear');
        if (photo?.image) {
            const url = photo.image.startsWith('http') ? photo.image : `/storage/${photo.image}`;
            preview.src = url;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
            clearBtn.style.display = 'block';
        } else {
            preview.src = '';
            preview.style.display = 'none';
            placeholder.style.display = 'flex';
            clearBtn.style.display = 'none';
        }

        document.getElementById('gallery-photo-modal').style.display = 'flex';
    }

    function closeGalleryPhotoModal() {
        document.getElementById('gallery-photo-modal').style.display = 'none';
    }

    function triggerGalleryPhotoUpload() {
        document.getElementById('gallery-photo-file-input').click();
    }

    async function handleGalleryPhotoFile(file) {
        if (!file || galleryPhotoUploading) return;
        galleryPhotoUploading = true;

        const placeholder = document.getElementById('gallery-photo-placeholder');
        const preview     = document.getElementById('gallery-photo-preview');
        const clearBtn    = document.getElementById('gallery-photo-clear');
        placeholder.innerHTML = '<i class="ph ph-spinner" style="font-size:1.5rem;color:var(--orange);"></i><span style="font-size:.8rem;color:var(--text-muted);">Đang tải lên...</span>';

        const fd = new FormData();
        fd.append('image', file);
        fd.append('_token', csrf());

        try {
            const res = await fetch(ADMIN_BASE + '/gallery-photos/upload-image', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: fd,
            }).then(r => r.json());

            if (res.success) {
                galleryPhotoImagePath = res.path;
                preview.src = res.url;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
                clearBtn.style.display = 'block';
            } else {
                placeholder.innerHTML = '<i class="ph ph-warning" style="color:#dc2626;"></i><span style="font-size:.78rem;color:#dc2626;">Tải lên thất bại</span>';
            }
        } catch {
            placeholder.innerHTML = '<i class="ph ph-warning" style="color:#dc2626;"></i><span style="font-size:.78rem;color:#dc2626;">Lỗi kết nối</span>';
        } finally {
            galleryPhotoUploading = false;
        }
    }

    function handleGalleryPhotoDrop(e) {
        e.preventDefault();
        const file = e.dataTransfer?.files?.[0];
        if (file && file.type.startsWith('image/')) handleGalleryPhotoFile(file);
    }

    function clearGalleryPhoto() {
        galleryPhotoImagePath = '';
        const preview     = document.getElementById('gallery-photo-preview');
        const placeholder = document.getElementById('gallery-photo-placeholder');
        const clearBtn    = document.getElementById('gallery-photo-clear');
        if (preview)  { preview.style.display = 'none'; preview.src = ''; }
        if (clearBtn) clearBtn.style.display = 'none';
        if (placeholder) {
            placeholder.style.display = 'flex';
            placeholder.innerHTML = `
                <i class="ph ph-image" style="font-size:2rem;color:var(--text-muted);"></i>
                <span style="font-size:.82rem;color:var(--text-muted);">Click hoặc kéo ảnh vào đây</span>
                <span style="font-size:.75rem;color:var(--text-muted);opacity:.7;">JPG / PNG / WebP · tối đa 4 MB</span>`;
        }
        const fi = document.getElementById('gallery-photo-file-input');
        if (fi) fi.value = '';
    }

    async function deleteGalleryPhoto(id) {
        if (!confirm('Xóa ảnh này?')) return;
        const res = await apiFetch(`${ADMIN_BASE}/gallery-photos/${id}`, 'DELETE');
        if (res?.success) loadGalleryPhotos();
    }

    async function toggleGalleryPhotoStatus(id, btn) {
        const res = await apiFetch(`${ADMIN_BASE}/gallery-photos/${id}/status`, 'PATCH');
        if (res?.success) {
            btn.textContent = res.is_active ? '👁 Ẩn' : '👁 Hiện';
            loadGalleryPhotos();
        }
    }

    document.getElementById('gallery-photo-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const errEl = document.getElementById('gallery-photo-form-error');
        errEl.style.display = 'none';

        if (!galleryPhotoImagePath) {
            errEl.textContent = 'Vui lòng chọn ảnh.';
            errEl.style.display = 'block';
            return;
        }

        const id = document.getElementById('gallery-photo-id').value;
        const payload = {
            image:      galleryPhotoImagePath,
            caption:    document.getElementById('gallery-photo-caption').value.trim() || null,
            sort_order: parseInt(document.getElementById('gallery-photo-sort').value) || 0,
            is_active:  document.getElementById('gallery-photo-active').checked,
        };

        const url    = id ? `${ADMIN_BASE}/gallery-photos/${id}` : `${ADMIN_BASE}/gallery-photos`;
        const method = id ? 'PUT' : 'POST';
        const res    = await apiFetch(url, method, payload);

        if (res?.success) {
            closeGalleryPhotoModal();
            loadGalleryPhotos();
        } else {
            errEl.textContent = res?.message || 'Có lỗi xảy ra.';
            errEl.style.display = 'block';
        }
    });

    document.getElementById('gallery-photo-modal-close')?.addEventListener('click', closeGalleryPhotoModal);
    document.getElementById('gallery-photo-modal-cancel')?.addEventListener('click', closeGalleryPhotoModal);
    document.getElementById('gallery-photo-modal')?.addEventListener('click', function (e) {
        if (e.target === this) closeGalleryPhotoModal();
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
        openRoomVideoPicker,
        clearRoomVideo,
        switchRoomVideoMode,
        // Villas
        loadVillas,
        openVillaModal,
        deleteVilla,
        toggleVillaStatus,
        openVillaSlotPicker,
        handleVillaSlotDrop,
        clearVillaSlot,
        openVillaVideoPicker,
        clearVillaVideo,
        switchVillaVideoMode,
        // Settings
        loadSettings,
        openSettingsLogoPicker,
        clearSettingsLogo,
        openSettingsOgPicker,
        clearSettingsOg,
        // News
        openNewsModal,
        deleteNews,
        openNewsImagePicker,
        handleNewsImageDrop,
        clearNewsImage,
        // FAQs
        openFaqModal,
        deleteFaq,
        // Remarketing (legacy)
        sendRemarketingNow,
        saveRemarketingSchedule,
        clearRemarketingSchedule,
        // Vouchers
        loadVouchers,
        openVoucherModal,
        deleteVoucher,
        toggleVoucherStatus,
        onVoucherTypeChange,
        // Email Campaigns
        loadCampaigns,
        openCampaignModal,
        deleteCampaign,
        sendCampaignNow,
        previewEligible,
        onCampaignVoucherModeChange,
        onCampaignStatusChange,
        updateCondRangePreview,
        // Recipient mode
        setCampaignRecipientMode,
        filterCampaignMembers,
        updateMemberSelectedCount,
        onManualEmailsChange,
        // Gallery Photos
        loadGalleryPhotos,
        openGalleryPhotoModal,
        deleteGalleryPhoto,
        toggleGalleryPhotoStatus,
        triggerGalleryPhotoUpload,
        handleGalleryPhotoDrop,
        handleGalleryPhotoFile,
        clearGalleryPhoto,
    };

    // ---------------------------------------------------------------
    // Remarketing
    // ---------------------------------------------------------------

    async function loadRemarketing() {
        const res = await apiFetch(ADMIN_BASE + '/remarketing');
        if (!res.success) return;
        const d = res.data;

        document.getElementById('rm-subject').value  = d.subject || '';
        document.getElementById('rm-greeting').value = d.greeting || '';
        document.getElementById('rm-body').value     = d.body || '';
        document.getElementById('rm-discount').value = d.discount ?? 10;
        document.getElementById('rm-auto').checked   = !!d.auto;
        document.getElementById('rm-eligible').textContent = d.eligible ?? 0;

        const badge = document.getElementById('rm-scheduled-badge');
        const txt   = document.getElementById('rm-scheduled-text');
        if (d.send_at) {
            const dt = new Date(d.send_at);
            txt.textContent = 'Đã xếp lịch: ' + dt.toLocaleString('vi-VN');
            badge.style.display = 'block';
            document.getElementById('rm-send-at').value = d.send_at.replace(' ', 'T').slice(0, 16);
        } else {
            badge.style.display = 'none';
            document.getElementById('rm-send-at').value = '';
        }
    }

    document.getElementById('rm-auto')?.addEventListener('change', async function () {
        const checked = this.checked;
        // Auto-save the toggle immediately
        const res = await apiFetch(ADMIN_BASE + '/remarketing/settings', {
            method: 'POST',
            body: JSON.stringify({
                subject:  document.getElementById('rm-subject').value,
                greeting: document.getElementById('rm-greeting').value,
                body:     document.getElementById('rm-body').value,
                discount: parseInt(document.getElementById('rm-discount').value) || 10,
                auto:     checked,
                send_at:  document.getElementById('rm-send-at').value || null,
            }),
        });
        if (!res.success) this.checked = !checked; // revert on error
    });

    document.getElementById('remarketing-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const msgEl = document.getElementById('remarketing-form-msg');
        msgEl.style.display = 'none';

        const res = await apiFetch(ADMIN_BASE + '/remarketing/settings', {
            method: 'POST',
            body: JSON.stringify({
                subject:  document.getElementById('rm-subject').value,
                greeting: document.getElementById('rm-greeting').value,
                body:     document.getElementById('rm-body').value,
                discount: parseInt(document.getElementById('rm-discount').value) || 10,
                auto:     document.getElementById('rm-auto').checked,
                send_at:  document.getElementById('rm-send-at').value || null,
            }),
        });

        msgEl.style.display = 'block';
        if (res.success) {
            msgEl.style.background = '#DCFCE7';
            msgEl.style.color      = '#166534';
            msgEl.textContent      = res.message || 'Đã lưu.';
        } else {
            msgEl.style.background = '#FEE2E2';
            msgEl.style.color      = '#991B1B';
            msgEl.textContent      = res.message || 'Lỗi khi lưu.';
        }
        setTimeout(() => { msgEl.style.display = 'none'; }, 3000);
    });

    async function saveRemarketingSchedule() {
        const sendAt = document.getElementById('rm-send-at').value;
        if (!sendAt) { alert('Vui lòng chọn ngày & giờ gửi.'); return; }

        const res = await apiFetch(ADMIN_BASE + '/remarketing/settings', {
            method: 'POST',
            body: JSON.stringify({
                subject:  document.getElementById('rm-subject').value,
                greeting: document.getElementById('rm-greeting').value,
                body:     document.getElementById('rm-body').value,
                discount: parseInt(document.getElementById('rm-discount').value) || 10,
                auto:     document.getElementById('rm-auto').checked,
                send_at:  sendAt,
            }),
        });

        const badge = document.getElementById('rm-scheduled-badge');
        const txt   = document.getElementById('rm-scheduled-text');
        if (res.success) {
            const dt = new Date(sendAt);
            txt.textContent = 'Đã xếp lịch: ' + dt.toLocaleString('vi-VN');
            badge.style.display = 'block';
        } else {
            alert(res.message || 'Lỗi khi xếp lịch.');
        }
    }

    async function clearRemarketingSchedule() {
        const res = await apiFetch(ADMIN_BASE + '/remarketing/settings', {
            method: 'POST',
            body: JSON.stringify({
                subject:  document.getElementById('rm-subject').value,
                greeting: document.getElementById('rm-greeting').value,
                body:     document.getElementById('rm-body').value,
                discount: parseInt(document.getElementById('rm-discount').value) || 10,
                auto:     document.getElementById('rm-auto').checked,
                send_at:  null,
            }),
        });
        if (res.success) {
            document.getElementById('rm-scheduled-badge').style.display = 'none';
            document.getElementById('rm-send-at').value = '';
        }
    }

    async function sendRemarketingNow() {
        const btn   = document.getElementById('rm-send-btn');
        const msgEl = document.getElementById('rm-send-msg');
        if (!confirm('Gửi email remarketing đến tất cả khách đủ điều kiện ngay bây giờ?')) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-circle-notch"></i> Đang gửi...';
        msgEl.style.display = 'none';

        const res = await apiFetch(ADMIN_BASE + '/remarketing/send-now', { method: 'POST', body: '{}' });

        btn.disabled = false;
        btn.innerHTML = '<i class="ph ph-paper-plane-tilt"></i> Gửi ngay';
        msgEl.style.display = 'block';

        if (res.success) {
            msgEl.style.background = '#DCFCE7';
            msgEl.style.color      = '#166534';
            msgEl.textContent      = res.message;
            // Reload eligible count
            const r2 = await apiFetch(ADMIN_BASE + '/remarketing');
            if (r2.success) document.getElementById('rm-eligible').textContent = r2.data.eligible ?? 0;
        } else {
            msgEl.style.background = '#FEE2E2';
            msgEl.style.color      = '#991B1B';
            msgEl.textContent      = res.message || 'Gửi thất bại.';
        }
    }

    // ---------------------------------------------------------------
    // Vouchers
    // ---------------------------------------------------------------

    let voucherPage = 1;
    let voucherSearchTimer = null;

    async function loadVouchers(page = 1) {
        voucherPage = page;
        const search = (document.getElementById('voucher-search')?.value || '').trim();
        const res    = await apiFetch(ADMIN_BASE + '/vouchers?page=' + page + '&search=' + encodeURIComponent(search));
        if (!res.success) return;

        const tbody = document.getElementById('voucher-list-body');
        const items = res.data.data || [];

        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:32px;">Chưa có voucher nào.</td></tr>';
            document.getElementById('voucher-pagination').innerHTML = '';
            return;
        }

        tbody.innerHTML = items.map(v => {
            const discount    = v.discount_type === 'percent'
                ? `<span class="discount-value">${v.discount_value}%</span>`
                : `<span class="discount-value">${Number(v.discount_value).toLocaleString('vi-VN')}đ</span>`;
            const maxUses     = v.max_uses ? v.max_uses : '∞';
            const usedRatio   = v.max_uses ? `<span style="color:${v.used_count >= v.max_uses ? '#dc2626' : 'inherit'}">${v.used_count}</span> / ${maxUses}` : `${v.used_count} / ∞`;
            const expires     = v.expires_at ? new Date(v.expires_at).toLocaleDateString('vi-VN') : '—';
            const expired     = v.expires_at && new Date(v.expires_at) < new Date();
            const statusClass = expired ? 'status-voucher-expired' : (v.is_active ? 'status-voucher-active' : 'status-voucher-inactive');
            const statusTxt   = expired ? 'Hết hạn' : (v.is_active ? 'Hoạt động' : 'Tắt');
            const statusIcon  = expired ? 'ph-clock' : (v.is_active ? 'ph-check-circle' : 'ph-x-circle');
            return `<tr>
                <td><span class="voucher-code-chip">${v.code}</span></td>
                <td><span style="font-weight:500;">${v.name}</span>${v.notes ? `<span class="table-customer">${v.notes}</span>` : ''}</td>
                <td>${discount}${v.min_order_amount ? `<span class="table-customer">tối thiểu ${Number(v.min_order_amount).toLocaleString('vi-VN')}đ</span>` : ''}</td>
                <td>${usedRatio}</td>
                <td style="white-space:nowrap;">${expires}${expired ? '<span class="table-customer" style="color:#dc2626;">Đã hết hạn</span>' : ''}</td>
                <td>
                    <button class="status-toggle-btn ${statusClass}"
                            onclick="AdminApp.toggleVoucherStatus(${v.id}, this)">
                        <i class="ph ${statusIcon}"></i>${statusTxt}
                    </button>
                </td>
                <td>
                    <div style="display:flex;gap:6px;justify-content:flex-end;">
                        <button class="btn-view" onclick="AdminApp.openVoucherModal(${JSON.stringify(v).replace(/"/g,'&quot;')})"><i class="ph ph-pencil-simple"></i> Sửa</button>
                        <button class="btn-view btn-danger" onclick="AdminApp.deleteVoucher(${v.id},'${v.code}')"><i class="ph ph-trash"></i></button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        renderPagination('voucher-pagination', res.data.current_page, res.data.last_page, loadVouchers);
    }

    document.getElementById('voucher-search')?.addEventListener('input', function () {
        clearTimeout(voucherSearchTimer);
        voucherSearchTimer = setTimeout(() => loadVouchers(1), 350);
    });

    function openVoucherModal(voucher = null) {
        const isEdit = !!voucher;
        document.getElementById('voucher-modal-title').textContent = isEdit ? 'Sửa voucher' : 'Tạo voucher';
        document.getElementById('voucher-id').value       = voucher?.id || '';
        document.getElementById('voucher-code').value     = voucher?.code || '';
        document.getElementById('voucher-name').value     = voucher?.name || '';
        document.getElementById('voucher-type').value     = voucher?.discount_type || 'percent';
        document.getElementById('voucher-value').value    = voucher?.discount_value || 10;
        document.getElementById('voucher-min-order').value = voucher?.min_order_amount || '';
        document.getElementById('voucher-max-uses').value  = voucher?.max_uses || '';
        document.getElementById('voucher-expires').value   = voucher?.expires_at ? voucher.expires_at.slice(0,10) : '';
        document.getElementById('voucher-active').checked  = voucher ? !!voucher.is_active : true;
        document.getElementById('voucher-notes').value     = voucher?.notes || '';
        document.getElementById('voucher-form-error').style.display = 'none';
        onVoucherTypeChange();
        document.getElementById('voucher-modal').style.display = 'flex';
    }

    function onVoucherTypeChange() {
        const type  = document.getElementById('voucher-type')?.value;
        const label = document.getElementById('voucher-value-label');
        if (label) label.textContent = type === 'percent' ? 'Giá trị giảm (%)' : 'Số tiền giảm (VNĐ)';
    }

    document.getElementById('voucher-modal-close')?.addEventListener('click',  () => { document.getElementById('voucher-modal').style.display = 'none'; });
    document.getElementById('voucher-modal-cancel')?.addEventListener('click', () => { document.getElementById('voucher-modal').style.display = 'none'; });

    document.getElementById('voucher-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const errEl = document.getElementById('voucher-form-error');
        errEl.style.display = 'none';

        const id   = document.getElementById('voucher-id').value;
        const url  = id ? ADMIN_BASE + '/vouchers/' + id : ADMIN_BASE + '/vouchers';
        const method = id ? 'PUT' : 'POST';

        const payload = {
            code:             document.getElementById('voucher-code').value.toUpperCase().trim(),
            name:             document.getElementById('voucher-name').value.trim(),
            discount_type:    document.getElementById('voucher-type').value,
            discount_value:   parseInt(document.getElementById('voucher-value').value) || 0,
            min_order_amount: parseInt(document.getElementById('voucher-min-order').value) || null,
            max_uses:         parseInt(document.getElementById('voucher-max-uses').value) || null,
            expires_at:       document.getElementById('voucher-expires').value || null,
            is_active:        document.getElementById('voucher-active').checked,
            notes:            document.getElementById('voucher-notes').value.trim() || null,
        };

        const res = await apiFetch(url, { method, body: JSON.stringify(payload) });

        if (res.success) {
            document.getElementById('voucher-modal').style.display = 'none';
            loadVouchers(voucherPage);
            loadVoucherOptions();
        } else {
            errEl.textContent    = res.message || Object.values(res.errors || {}).flat().join(' ');
            errEl.style.display  = 'block';
        }
    });

    async function deleteVoucher(id, code) {
        if (!confirm(`Xóa voucher "${code}"? Hành động này không thể hoàn tác.`)) return;
        const res = await apiFetch(ADMIN_BASE + '/vouchers/' + id, { method: 'DELETE' });
        if (res.success) loadVouchers(voucherPage);
        else alert(res.message || 'Lỗi khi xóa.');
    }

    async function toggleVoucherStatus(id, btn) {
        const res = await apiFetch(ADMIN_BASE + '/vouchers/' + id + '/status', { method: 'PATCH', body: '{}' });
        if (res.success) {
            const active = res.is_active;
            btn.className = 'status-toggle-btn ' + (active ? 'status-voucher-active' : 'status-voucher-inactive');
            btn.innerHTML = `<i class="ph ${active ? 'ph-check-circle' : 'ph-x-circle'}"></i>${active ? 'Hoạt động' : 'Tắt'}`;
        }
    }

    // ---------------------------------------------------------------
    // Email Campaigns
    // ---------------------------------------------------------------

    let voucherOptions = [];

    async function loadVoucherOptions() {
        const res = await apiFetch(ADMIN_BASE + '/vouchers?page=1&search=');
        if (!res.success) return;
        voucherOptions = (res.data.data || []).filter(v => v.is_active);
        const sel = document.getElementById('campaign-voucher-id');
        if (!sel) return;
        const cur = sel.value;
        sel.innerHTML = '<option value="">-- Chọn voucher --</option>' +
            voucherOptions.map(v => {
                const disc = v.discount_type === 'percent' ? v.discount_value + '%' : Number(v.discount_value).toLocaleString('vi-VN') + ' VNĐ';
                return `<option value="${v.id}">${v.code} — ${v.name} (${disc})</option>`;
            }).join('');
        if (cur) sel.value = cur;
    }

    async function loadCampaigns() {
        const res = await apiFetch(ADMIN_BASE + '/campaigns');
        if (!res.success) return;

        const tbody = document.getElementById('campaign-list-body');
        const items = res.data || [];

        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:32px;">Chưa có chiến dịch nào. Tạo chiến dịch đầu tiên!</td></tr>';
            return;
        }

        const statusMap = {
            draft:     ['status-draft',      'Nháp',            'ph-pencil'],
            scheduled: ['status-scheduled',  'Đã xếp lịch',     'ph-calendar-check'],
            sending:   ['status-sending',    'Đang gửi',        'ph-paper-plane-tilt'],
            sent:      ['status-sent',       'Đã gửi',          'ph-check-circle'],
            recurring: ['status-recurring',  'Lặp lại',         'ph-arrows-clockwise'],
        };
        const intervalLabel = { daily: 'Mỗi ngày', weekly: 'Mỗi tuần' };

        tbody.innerHTML = items.map(c => {
            const cond    = c.conditions || {};
            const minDays = cond.checkout_min_days ?? 30;
            const maxDays = cond.checkout_max_days ?? 60;
            const condParts = [`Trả phòng ${minDays}–${maxDays} ngày trước`];
            if (cond.min_bookings > 1) condParts.push(`≥${cond.min_bookings} booking`);
            if (cond.min_spent > 0)    condParts.push(`≥${Number(cond.min_spent).toLocaleString('vi-VN')}đ`);

            const vMode = c.voucher_mode === 'auto'
                ? `<span class="voucher-code-chip" style="background:#FFF3E8;color:var(--orange);border-color:#fed7aa;">Tự tạo ${c.auto_discount_percent}%</span>`
                : c.voucher_mode === 'fixed'
                    ? `<span class="voucher-code-chip">${c.voucher ? c.voucher.code : 'Voucher'}</span>`
                    : `<span style="color:var(--text-muted);font-size:0.8rem;">—</span>`;

            const [sClass, sTxt, sIcon] = statusMap[c.status] || statusMap.draft;
            const fmt = d => d ? new Date(d).toLocaleString('vi-VN', {day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'}) : null;
            const sendDate = c.status === 'recurring'
                ? (intervalLabel[c.repeat_interval] || 'Định kỳ') + (c.sent_at ? `<br><span style="font-size:0.72rem;color:var(--text-muted);">Lần cuối: ${fmt(c.sent_at)}</span>` : '')
                : (fmt(c.send_at) || fmt(c.sent_at) || '—');

            const eligibleBadge = c.eligible_count != null
                ? `<span class="eligible-badge">${c.eligible_count}</span>`
                : `<span style="color:var(--text-muted);">—</span>`;

            const sentInfo = c.sent_count > 0
                ? `<span class="table-customer"><i class="ph ph-check-circle" style="color:#16a34a;"></i> ${c.sent_count} đã gửi</span>`
                : '';
            const actionBtn = c.status === 'sending'
                ? `<span style="font-size:0.78rem;color:var(--text-muted);">Đang gửi...</span>`
                : `<button class="btn-send-now" onclick="AdminApp.sendCampaignNow(${c.id},'${c.name.replace(/'/g,"\\'")}',this)"><i class="ph ph-paper-plane-tilt"></i> Gửi ngay</button>${sentInfo}`;

            return `<tr>
                <td>
                    <span style="font-weight:600;">${c.name}</span>
                    ${c.subject ? `<span class="table-customer">${c.subject}</span>` : ''}
                </td>
                <td style="font-size:0.78rem;color:var(--text-muted);">${
                    c.recipient_mode === 'manual'  ? `<span style="color:#1d4ed8;"><i class="ph ph-pencil-line"></i> Nhập email</span><br>${(c.recipient_data||[]).length} địa chỉ` :
                    c.recipient_mode === 'members' ? `<span style="color:#7c3aed;"><i class="ph ph-users-three"></i> Chọn member</span><br>${(c.recipient_data||[]).length} member` :
                    condParts.join('<br>')
                }</td>
                <td>${vMode}</td>
                <td style="text-align:center;">${eligibleBadge}</td>
                <td><span class="status-badge ${sClass}"><i class="ph ${sIcon}"></i>${sTxt}</span></td>
                <td style="font-size:0.78rem;white-space:nowrap;">${sendDate}</td>
                <td>
                    <div style="display:flex;gap:6px;justify-content:flex-end;align-items:center;flex-wrap:wrap;">
                        ${actionBtn}
                        <button class="btn-view" onclick="AdminApp.openCampaignModal(${JSON.stringify(c).replace(/"/g,'&quot;')})"><i class="ph ph-pencil-simple"></i> Sửa</button>
                        <button class="btn-view btn-danger" onclick="AdminApp.deleteCampaign(${c.id},'${c.name.replace(/'/g,"\\'")}')"><i class="ph ph-trash"></i></button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    // ---- Recipient mode ----
    let campaignMembersCache = null;

    function setCampaignRecipientMode(mode) {
        document.getElementById('campaign-recipient-mode').value = mode;
        // Update tab buttons
        document.querySelectorAll('.rcpt-mode-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.mode === mode);
        });
        // Show/hide sub-sections
        document.getElementById('rcpt-eligible-section').style.display = mode === 'eligible' ? 'block' : 'none';
        document.getElementById('rcpt-manual-section').style.display   = mode === 'manual'   ? 'block' : 'none';
        document.getElementById('rcpt-members-section').style.display  = mode === 'members'  ? 'block' : 'none';

        if (mode === 'members' && !campaignMembersCache) {
            loadMembersForCampaign();
        }
    }

    async function loadMembersForCampaign(selectedIds = []) {
        const listEl = document.getElementById('campaign-member-list');
        if (!listEl) return;

        if (!campaignMembersCache) {
            listEl.innerHTML = '<div style="padding:20px;text-align:center;color:var(--text-muted);font-size:0.82rem;">Đang tải...</div>';
            const res = await apiFetch(ADMIN_BASE + '/members?per_page=200');
            if (!res.success) { listEl.innerHTML = '<div style="padding:20px;text-align:center;color:#dc2626;">Lỗi tải danh sách.</div>'; return; }
            campaignMembersCache = res.data.data || [];
        }

        renderMemberList(campaignMembersCache, selectedIds);
    }

    function renderMemberList(members, selectedIds = [], filter = '') {
        const listEl = document.getElementById('campaign-member-list');
        if (!listEl) return;

        const filt = filter.toLowerCase();
        const filtered = filt
            ? members.filter(m => (m.name||'').toLowerCase().includes(filt) || (m.email||'').toLowerCase().includes(filt))
            : members;

        if (!filtered.length) {
            listEl.innerHTML = '<div style="padding:20px;text-align:center;color:var(--text-muted);font-size:0.82rem;">Không tìm thấy member.</div>';
            updateMemberSelectedCount();
            return;
        }

        listEl.innerHTML = filtered.map(m => {
            const checked = selectedIds.includes(m.id) ? 'checked' : '';
            return `<label class="member-check-row">
                <input type="checkbox" name="campaign_member_ids[]" value="${m.id}" ${checked} onchange="AdminApp.updateMemberSelectedCount()">
                <div>
                    <div class="member-check-name">${m.name || '—'}</div>
                    <div class="member-check-email">${m.email}</div>
                </div>
            </label>`;
        }).join('');

        updateMemberSelectedCount();
    }

    function filterCampaignMembers() {
        const filter = document.getElementById('campaign-member-filter')?.value || '';
        const selected = getSelectedMemberIds();
        renderMemberList(campaignMembersCache || [], selected, filter);
    }

    function getSelectedMemberIds() {
        return [...document.querySelectorAll('#campaign-member-list input[type=checkbox]:checked')]
            .map(cb => parseInt(cb.value));
    }

    function updateMemberSelectedCount() {
        const count = getSelectedMemberIds().length;
        const el = document.getElementById('member-selected-count');
        if (el) el.textContent = count > 0
            ? `${count} member được chọn`
            : '0 member được chọn';
    }

    function onManualEmailsChange() {
        const raw   = document.getElementById('campaign-manual-emails')?.value || '';
        const emails = parseManualEmails(raw);
        const el     = document.getElementById('manual-email-count');
        if (el) el.textContent = emails.length > 0
            ? `${emails.length} email hợp lệ`
            : '0 email hợp lệ';
    }

    function parseManualEmails(raw) {
        return raw.split(/[\n,;]+/)
            .map(e => e.trim().toLowerCase())
            .filter(e => e && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e));
    }
    // ---- End recipient mode ----

    function openCampaignModal(campaign = null) {
        const isEdit = !!campaign;
        campaignMembersCache = null; // reset cache so members reload fresh
        document.getElementById('campaign-modal-title').textContent = isEdit ? 'Sửa chiến dịch' : 'Tạo chiến dịch email';
        document.getElementById('campaign-id').value      = campaign?.id || '';
        document.getElementById('campaign-name').value    = campaign?.name || '';
        document.getElementById('campaign-subject').value = campaign?.subject || '';
        document.getElementById('campaign-greeting').value = campaign?.greeting || '';
        document.getElementById('campaign-body').value    = campaign?.body || '';
        document.getElementById('campaign-voucher-mode').value = campaign?.voucher_mode || 'auto';
        document.getElementById('campaign-discount').value = campaign?.auto_discount_percent ?? 10;
        const st = campaign?.status;
        document.getElementById('campaign-status').value = (st === 'scheduled' || st === 'recurring') ? st : 'draft';
        document.getElementById('campaign-repeat-interval').value = campaign?.repeat_interval || 'daily';

        const cond = campaign?.conditions || {};
        document.getElementById('cond-min-days').value      = cond.checkout_min_days ?? 30;
        document.getElementById('cond-max-days').value      = cond.checkout_max_days ?? 60;
        document.getElementById('cond-min-bookings').value  = cond.min_bookings ?? 1;
        document.getElementById('cond-min-spent').value     = cond.min_spent || '';
        updateCondRangePreview();

        if (campaign?.send_at) {
            document.getElementById('campaign-send-at').value = campaign.send_at.replace(' ', 'T').slice(0, 16);
        } else {
            document.getElementById('campaign-send-at').value = '';
        }

        loadVoucherOptions().then(() => {
            if (campaign?.voucher_id) {
                document.getElementById('campaign-voucher-id').value = campaign.voucher_id;
            }
        });

        // Recipient mode
        const rMode = campaign?.recipient_mode || 'eligible';
        const rData = campaign?.recipient_data || [];
        document.getElementById('campaign-manual-emails').value = '';
        document.getElementById('campaign-member-filter').value = '';

        if (rMode === 'manual') {
            document.getElementById('campaign-manual-emails').value = (rData || []).join('\n');
            onManualEmailsChange();
        } else if (rMode === 'members') {
            loadMembersForCampaign(rData || []);
        }

        setCampaignRecipientMode(rMode);

        document.getElementById('campaign-eligible-preview').style.display = 'none';
        document.getElementById('campaign-form-error').style.display = 'none';
        onCampaignVoucherModeChange();
        onCampaignStatusChange();
        document.getElementById('campaign-modal').style.display = 'flex';
    }

    function updateCondRangePreview() {
        const minDays = parseInt(document.getElementById('cond-min-days')?.value) || 0;
        const maxDays = parseInt(document.getElementById('cond-max-days')?.value) || 0;
        const el = document.getElementById('cond-range-preview');
        if (el) el.textContent = minDays + ' – ' + maxDays + ' ngày trước';
    }

    function onCampaignVoucherModeChange() {
        const mode = document.getElementById('campaign-voucher-mode')?.value;
        document.getElementById('campaign-voucher-auto-opts').style.display  = mode === 'auto'  ? 'block' : 'none';
        document.getElementById('campaign-voucher-fixed-opts').style.display = mode === 'fixed' ? 'block' : 'none';
    }

    function onCampaignStatusChange() {
        const status = document.getElementById('campaign-status')?.value;
        document.getElementById('campaign-send-at-wrap').style.display  = status === 'scheduled' ? 'block' : 'none';
        document.getElementById('campaign-repeat-wrap').style.display   = status === 'recurring' ? 'block' : 'none';
    }

    async function previewEligible() {
        const id = document.getElementById('campaign-id').value;
        if (!id) {
            // No saved campaign yet — just show local count estimate
            alert('Lưu chiến dịch trước để xem danh sách người nhận.');
            return;
        }
        const res = await apiFetch(ADMIN_BASE + '/campaigns/' + id + '/eligible');
        if (!res.success) return;

        const el = document.getElementById('campaign-eligible-preview');
        document.getElementById('campaign-eligible-count').textContent = res.count;
        el.style.display = 'block';

        if (res.count > 0 && res.preview?.length) {
            const names = res.preview.map(p => `${p.name || p.email} (${p.checkout})`).join(', ');
            el.title = names;
        }
    }

    document.getElementById('campaign-modal-close')?.addEventListener('click',  () => { document.getElementById('campaign-modal').style.display = 'none'; });
    document.getElementById('campaign-modal-cancel')?.addEventListener('click', () => { document.getElementById('campaign-modal').style.display = 'none'; });

    document.getElementById('campaign-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const errEl = document.getElementById('campaign-form-error');
        errEl.style.display = 'none';

        const id     = document.getElementById('campaign-id').value;
        const url    = id ? ADMIN_BASE + '/campaigns/' + id : ADMIN_BASE + '/campaigns';
        const method = id ? 'PUT' : 'POST';

        const rMode = document.getElementById('campaign-recipient-mode').value || 'eligible';
        let rData = null;
        if (rMode === 'manual') {
            rData = parseManualEmails(document.getElementById('campaign-manual-emails').value || '');
        } else if (rMode === 'members') {
            rData = getSelectedMemberIds();
        }

        const payload = {
            name:                  document.getElementById('campaign-name').value.trim(),
            subject:               document.getElementById('campaign-subject').value.trim() || null,
            greeting:              document.getElementById('campaign-greeting').value.trim() || null,
            body:                  document.getElementById('campaign-body').value.trim() || null,
            voucher_mode:          document.getElementById('campaign-voucher-mode').value,
            voucher_id:            document.getElementById('campaign-voucher-id').value || null,
            auto_discount_percent: parseInt(document.getElementById('campaign-discount').value) || 10,
            conditions: {
                checkout_max_days: parseInt(document.getElementById('cond-max-days').value) || 60,
                checkout_min_days: parseInt(document.getElementById('cond-min-days').value) || 0,
                min_bookings:      parseInt(document.getElementById('cond-min-bookings').value) || 1,
                min_spent:         parseInt(document.getElementById('cond-min-spent').value) || 0,
                order_statuses:    ['confirmed', 'completed', 'pending'],
            },
            recipient_mode:    rMode,
            recipient_data:    rData,
            status:            document.getElementById('campaign-status').value,
            send_at:           document.getElementById('campaign-send-at').value || null,
            repeat_interval:   document.getElementById('campaign-repeat-interval').value || null,
        };

        const res = await apiFetch(url, { method, body: JSON.stringify(payload) });

        if (res.success) {
            document.getElementById('campaign-id').value = res.data?.id || id || '';
            document.getElementById('campaign-modal').style.display = 'none';
            loadCampaigns();
        } else {
            errEl.textContent   = res.message || Object.values(res.errors || {}).flat().join(' ');
            errEl.style.display = 'block';
        }
    });

    async function deleteCampaign(id, name) {
        if (!confirm(`Xóa chiến dịch "${name}"? Hành động này không thể hoàn tác.`)) return;
        const res = await apiFetch(ADMIN_BASE + '/campaigns/' + id, { method: 'DELETE' });
        if (res.success) loadCampaigns();
        else alert(res.message || 'Lỗi khi xóa.');
    }

    async function sendCampaignNow(id, name, btn) {
        if (!confirm(`Gửi ngay chiến dịch "${name}" đến tất cả khách đủ điều kiện?`)) return;
        btn.disabled     = true;
        btn.textContent  = 'Đang gửi...';

        const res = await apiFetch(ADMIN_BASE + '/campaigns/' + id + '/send-now', { method: 'POST', body: '{}' });
        btn.disabled    = false;
        btn.textContent = 'Gửi ngay';

        alert(res.message || (res.success ? 'Hoàn thành!' : 'Có lỗi xảy ra.'));
        if (res.success) loadCampaigns();
    }

})();
