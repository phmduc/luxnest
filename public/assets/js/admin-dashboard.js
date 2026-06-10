// Global Tab Switcher
window.switchTab = function(tab) {
    jQuery(`.nav-item[data-tab="${tab}"]`).trigger('click');
};

jQuery(document).ready(function($) {
    // Force hide modal on load (safeguard)
    $('#booking-modal').hide();

    // Auto-open modal if ID is in URL (e.g. from QR scan after login)
    const urlParams = new URLSearchParams(window.location.search);
    const urlId = urlParams.get('booking_id') || urlParams.get('id') || urlParams.get('oid');
    if (urlId) {
        // Wait a small bit for everything to be ready
        setTimeout(() => {
            window.openModal(urlId);
        }, 300);
        
        // Clean URL to prevent re-opening on manual refresh
        const cleanUrl = window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }

    // Tab Switching Logic
    $('.nav-item[data-tab]').on('click', function(e) {
        e.preventDefault();
        const tab = $(this).data('tab');
        
        // Update Nav
        $('.nav-item').removeClass('active');
        $(this).addClass('active');
        
        // Update Content
        $('.dashboard-tab').removeClass('active');
        $(`#tab-${tab}`).addClass('active');

        // Load data if needed
        if (tab === 'bookings') {
            loadBookings();
        }
    });

    let currentBookingPage = 1;

    function loadBookings(page = 1) {
        currentBookingPage = page;
        const search = $('#booking-search').val() || '';
        const $list = $('#bookings-list-body');
        const $pagination = $('#bookings-pagination');
        
        $list.html('<tr><td colspan="6" style="text-align:center;">Đang tải dữ liệu...</td></tr>');

        $.ajax({
            url: luxnest_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'luxnest_admin_get_bookings',
                search: search,
                page: currentBookingPage,
                month: $('#booking-month-filter').val() || ''
            },
            success: function(response) {
                if (response.success) {
                    $list.html(response.data.html);
                    renderPagination(response.data.total_pages, response.data.current_page);
                } else {
                    $list.html('<tr><td colspan="6" style="text-align:center; color:red;">Lỗi tải dữ liệu.</td></tr>');
                }
            }
        });
    }

    function renderPagination(total, current) {
        const $container = $('#bookings-pagination');
        $container.empty();
        
        if (total <= 1) return;

        for (let i = 1; i <= total; i++) {
            const activeClass = i == current ? 'active' : '';
            $container.append(`<button class="page-btn ${activeClass}" data-page="${i}">${i}</button>`);
        }
    }

    $(document).on('click', '.page-btn', function() {
        const page = $(this).data('page');
        loadBookings(page);
    });

    // Global openModal function
    window.openModal = function(id) {
        const $modal = $('#booking-modal');
        const $loader = $('#modal-loader');
        const $data = $('#modal-data');

        $modal.css('display', 'flex');
        $loader.show();
        $data.hide().html('');

        $.ajax({
            url: luxnest_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'luxnest_admin_get_order_details',
                nonce: luxnest_admin_ajax.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    const d = response.data;
                    const html = `
                        <div class="modal-detail-header">
                            <img src="${d.room_img}" class="modal-detail-img">
                            <div class="modal-detail-main">
                                <div class="modal-status-bar">
                                    <span class="status-badge status-${d.status}">${d.status_label}</span>
                                    <span>ID: #${d.id}</span>
                                </div>
                                <h2>${d.room_name}</h2>
                                <p class="recent-date">Ngày đặt: ${d.date}</p>
                            </div>
                        </div>
                        <div class="modal-grid">
                            <div class="detail-group">
                                <h4>Thông tin khách hàng</h4>
                                <div class="detail-item">
                                    <span class="detail-label">Họ tên:</span>
                                    <span class="detail-value">${d.customer}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Email:</span>
                                    <span class="detail-value">${d.email}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Số điện thoại:</span>
                                    <span class="detail-value">${d.phone}</span>
                                </div>
                            </div>
                            <div class="detail-group">
                                <h4>Lịch trình & Ghi chú</h4>
                                <div class="detail-item">
                                    <span class="detail-label">Check-in:</span>
                                    <span class="detail-value">${d.checkin}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Check-out:</span>
                                    <span class="detail-value">${d.checkout}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Ghi chú:</span>
                                    <span class="detail-value">${d.notes}</span>
                                </div>
                            </div>
                        </div>
                        <div class="payment-summary">
                            <div class="payment-row grand-total" style="border-top: none; padding-top: 0; margin-bottom: 20px; border-bottom: 1px dashed var(--lux-gray); padding-bottom: 15px;">
                                <span>Tổng tiền:</span>
                                <span>${d.total}</span>
                            </div>
                            <div class="payment-row">
                                <span>Tiền cọc trước:</span>
                                <span>${d.paid}</span>
                            </div>
                            ${d.has_deposit ? `
                            <div class="payment-row" style="color: var(--lux-orange); font-weight: 700;">
                                <span>Số tiền còn lại cần thanh toán:</span>
                                <span>${d.remaining}</span>
                            </div>
                            ` : ''}
                        </div>

                        ${d.can_checkin ? `
                        <div class="modal-checkin-prompt" style="margin-top: 20px; padding: 20px; background: #fff8f1; border-radius: 16px; border: 1px solid #ffedd5; display: flex; align-items: center; justify-content: space-between; gap: 15px;">
                            <div style="flex: 1;">
                                <h4 style="margin: 0; color: #9a3412; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                                    <i class="ph ph-check-circle" style="font-size: 20px;"></i>
                                    Xác nhận Check-in
                                </h4>
                                <p style="margin: 5px 0 0; font-size: 13px; color: #c2410c; line-height: 1.4;">Khách đã sẵn sàng. Hãy xác nhận để hoàn tất thủ tục nhận phòng.</p>
                            </div>
                            <button data-id="${d.id}" class="btn-checkin-special" style="border:none; background: #ea580c; color: #fff; padding: 12px 24px; border-radius: 12px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.2); transition: all 0.2s ease;">Xác nhận Check-in</button>
                        </div>
                        ` : ''}

                        <div class="modal-actions">
                            <span class="action-label">Cập nhật trạng thái:</span>
                            <div class="status-select-wrapper">
                                <select class="status-select" id="new-order-status">
                                    <option value="on-hold" ${d.status === 'on-hold' ? 'selected' : ''}>Tạm giữ (On-hold)</option>
                                    <option value="partially-paid" ${d.status === 'partially-paid' ? 'selected' : ''}>Đã cọc (Partially Paid)</option>
                                    <option value="processing" ${d.status === 'processing' ? 'selected' : ''}>Đang xử lý (Processing)</option>
                                    <option value="completed" ${d.status === 'completed' ? 'selected' : ''}>Hoàn tất (Completed)</option>
                                    <option value="cancelled" ${d.status === 'cancelled' ? 'selected' : ''}>Đã hủy (Cancelled)</option>
                                </select>
                                <button class="btn-update-status" id="confirm-status-update" data-id="${d.id}">Cập nhật</button>
                            </div>
                        </div>
                    `;
                    $loader.hide();
                    $data.html(html).fadeIn();
                } else {
                    $loader.hide();
                    $data.html('<p class="error">Không thể tải dữ liệu.</p>').show();
                }
            }
        });
    };

    // Update Status Logic
    $(document).on('click', '#confirm-status-update', function() {
        const id = $(this).data('id');
        const status = $('#new-order-status').val();
        const $btn = $(this);

        $btn.prop('disabled', true).text('Đang cập nhật...');

        $.ajax({
            url: luxnest_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'luxnest_admin_update_order_status',
                nonce: luxnest_admin_ajax.nonce,
                id: id,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    alert('Cập nhật trạng thái thành công!');
                    $btn.prop('disabled', false).text('Cập nhật');
                    loadBookings(); // Refresh the list in the background
                } else {
                    alert('Lỗi: ' + response.data.message);
                    $btn.prop('disabled', false).text('Cập nhật');
                }
            }
        });
    });

    // Delegated Check-in Action Logic (Replaces window.processCheckin)
    $(document).on('click', '.btn-checkin-special', function() {
        const orderId = $(this).data('id');
        
        if (typeof Swal === 'undefined') {
            if (!confirm('Bạn có chắc chắn muốn xác nhận check-in cho đơn hàng #' + orderId + '?')) return;
            performCheckinAjax(orderId, $(this));
        } else {
            Swal.fire({
                title: 'Xác nhận Check-in',
                text: 'Bạn có chắc chắn muốn xác nhận check-in cho đơn hàng #' + orderId + '?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ea580c',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Có, xác nhận!',
                cancelButtonText: 'Để sau'
            }).then((result) => {
                if (result.isConfirmed) {
                    performCheckinAjax(orderId, $(this));
                }
            });
        }
    });

    function performCheckinAjax(orderId, $btn) {
        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="ph ph-circle-notch-bold"></i> Đang xử lý...');

        $.ajax({
            url: luxnest_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'luxnest_admin_checkin',
                nonce: luxnest_admin_ajax.nonce,
                id: orderId,
            },
            success: function(response) {
                if (response.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: 'Đã hoàn tất thủ tục check-in.',
                            confirmButtonColor: '#ea580c'
                        }).then(() => {
                            $('#booking-modal').fadeOut();
                            location.reload();
                        });
                    } else {
                        alert('Đã hoàn tất thủ tục check-in.');
                        location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Lỗi', response.data.message, 'error');
                    } else {
                        alert('Lỗi: ' + response.data.message);
                    }
                    $btn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Lỗi', 'Không thể kết nối máy chủ.', 'error');
                } else {
                    alert('Lỗi kết nối máy chủ.');
                }
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    }

    // Close modal
    $('.modal-close').on('click', function() {
        $('#booking-modal').fadeOut();
    });

    $('.modal-overlay').on('click', function(e) {
        if (e.target === this) {
            $('#booking-modal').fadeOut();
        }
    });

    // Booking Search with Debounce
    let searchTimeout;
    $('#booking-search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            loadBookings();
        }, 500);
    });

    $('#booking-month-filter').on('change', function() {
        loadBookings();
    });

    // Click handler for opening modal
    $(document).on('click', '.btn-open-modal', function(e) {
        e.preventDefault();
        openModal($(this).data('id'));
    });

    // Auto-open modal if signaled by PHP
    if (window.luxnest_auto_checkin && window.luxnest_auto_checkin.id) {
        setTimeout(function() {
            switchTab('bookings');
            openModal(window.luxnest_auto_checkin.id);
            // Clear signal
            window.luxnest_auto_checkin = null;
        }, 300);
    }
});
