@extends('layouts.admin')
@section('title', 'LuxNest Admin Dashboard')

@section('content')
<div class="manager-dashboard-container">

    {{-- ═══════════════════ SIDEBAR ═══════════════════ --}}
    <aside class="manager-sidebar">

        {{-- Logo --}}
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon"><i class="ph ph-buildings"></i></div>
            <span class="sidebar-logo-text">LuxNest</span>
        </div>

        {{-- Navigation --}}
        <nav class="sidebar-nav">
            <span class="nav-section-label">Menu</span>

            <a href="#" class="nav-item active" data-tab="overview">
                <i class="ph ph-squares-four"></i> Tổng Quan
            </a>
            <a href="#" class="nav-item" data-tab="bookings">
                <i class="ph ph-calendar-check"></i> Quản Lý Booking
            </a>
            @if(auth()->user()->isAdmin())
            <a href="#" class="nav-item" data-tab="rooms">
                <i class="ph ph-door"></i> Quản Lý Phòng
            </a>
            <a href="#" class="nav-item" data-tab="members">
                <i class="ph ph-users"></i> Thành Viên
            </a>
            <a href="#" class="nav-item" data-tab="settings">
                <i class="ph ph-storefront"></i> Thông Tin Doanh Nghiệp
            </a>
            @endif

            <div class="sidebar-spacer"></div>

            <span class="nav-section-label">Hệ Thống</span>
            <a href="{{ url('/') }}" class="nav-item">
                <i class="ph ph-house"></i> Trang Chủ
            </a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="nav-item">
                    <i class="ph ph-sign-out"></i> Đăng Xuất
                </button>
            </form>
        </nav>

        {{-- User profile --}}
        <div class="sidebar-user">
            <div class="sidebar-user-inner">
                <div class="sidebar-avatar">
                    {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                    <div class="sidebar-user-role">{{ auth()->user()->role }}</div>
                </div>
            </div>
        </div>

    </aside>

    {{-- Backdrop for mobile sidebar --}}
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    {{-- ═══════════════════ MAIN ═══════════════════ --}}
    <main class="manager-content">

        {{-- Top bar --}}
        <div class="admin-topbar">
            <div class="topbar-left">
                <button type="button" class="sidebar-toggle" id="sidebar-toggle" aria-label="Mở menu">
                    <i class="ph ph-list"></i>
                </button>
                <span class="topbar-title" id="topbar-title">Bảng Điều Khiển</span>
            </div>
            <div class="topbar-right">
                <span class="topbar-date" id="topbar-date"></span>
                <div class="topbar-avatar">
                    {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                </div>
            </div>
        </div>

        {{-- Page content --}}
        <div class="page-content">

            {{-- ═══ TAB: OVERVIEW ═══ --}}
            <section id="tab-overview" class="dashboard-tab active">

                <header class="content-header">
                    <h2>Bảng Điều Khiển</h2>
                    <p>Chào mừng trở lại, <strong>{{ auth()->user()->name }}</strong>.</p>
                </header>

                @if($goHostError)
                <div class="alert alert-error">
                    <i class="ph ph-warning-circle"></i>
                    <span>{{ $goHostError }}</span>
                </div>
                @endif

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="ph ph-calendar-dots"></i></div>
                        <div class="stat-info">
                            <span class="stat-label">Booking Tháng Này</span>
                            <h3 class="stat-value">{{ $totalBookings }}</h3>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon orange"><i class="ph ph-clock-countdown"></i></div>
                        <div class="stat-info">
                            <span class="stat-label">Chờ Check-in</span>
                            <h3 class="stat-value">{{ $pendingCheckin }}</h3>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon green"><i class="ph ph-bank"></i></div>
                        <div class="stat-info">
                            <span class="stat-label">Doanh Thu Tháng Này</span>
                            <h3 class="stat-value">{{ number_format($revenue, 0, ',', '.') }}đ</h3>
                        </div>
                    </div>
                </div>

                <div class="recent-sections-grid">
                    <div class="recent-card">
                        <div class="card-header">
                            <h3>Booking Gần Đây</h3>
                            <a href="#" class="view-all" data-tab-link="bookings">Xem tất cả</a>
                        </div>
                        <div class="card-content">
                            @if(!empty($recentBookings))
                            <ul class="recent-list">
                                @foreach($recentBookings as $booking)
                                @php
                                    $customer = $booking['customer']['name'] ?? 'Khách vãng lai';
                                    $rooms    = $booking['booking_rooms'] ?? [];
                                    $roomName = $rooms[0]['room_type']['name'] ?? ($rooms[0]['room_unit'] ?? 'Phòng');
                                    $amount   = number_format($booking['amount'] ?? 0, 0, ',', '.');
                                    $checkin  = isset($booking['checkin_date'])
                                        ? \Carbon\Carbon::parse($booking['checkin_date'])->format('d/m H:i')
                                        : '-';
                                @endphp
                                <li>
                                    <div class="recent-info">
                                        <span class="recent-id">{{ $roomName }} (#{{ $booking['id'] }})</span>
                                        <span class="recent-date">{{ $checkin }} &middot; {{ $customer }}</span>
                                    </div>
                                    <div class="recent-meta">
                                        <span class="recent-total">{{ $amount }}đ</span>
                                        <a href="#" class="btn-view-mini"
                                           onclick="AdminApp.openBookingModal('{{ $booking['id'] }}'); return false;">
                                            Chi tiết
                                        </a>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                            @else
                            <p style="color:var(--text-muted); text-align:center; padding:32px 0; font-size:0.9rem;">
                                Chưa có booking nào trong tháng này.
                            </p>
                            @endif
                        </div>
                    </div>
                </div>

            </section>

            {{-- ═══ TAB: BOOKINGS ═══ --}}
            <section id="tab-bookings" class="dashboard-tab">

                <div class="section-toolbar">
                    <h2>Lịch Trình Booking</h2>
                    <div class="toolbar-actions">
                        <div class="filter-wrapper">
                            <input type="month" id="booking-month-filter" value="{{ date('Y-m') }}" class="month-input">
                        </div>
                        <div class="search-wrapper">
                            <input type="text" id="booking-search" placeholder="Tìm khách, mã, phòng...">
                            <i class="ph ph-magnifying-glass"></i>
                        </div>
                    </div>
                </div>

                <div class="data-table-container">
                    <table class="manager-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Phòng &amp; Khách hàng</th>
                                <th>Lịch trình</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="bookings-list-body">
                            <tr>
                                <td colspan="6" style="text-align:center; color:var(--lux-gray); padding:40px;">
                                    Chọn tab để tải dữ liệu...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="bookings-pagination" class="pagination-container"></div>

            </section>

            {{-- ═══ TAB: ROOMS (admin only) ═══ --}}
            @if(auth()->user()->isAdmin())
            <section id="tab-rooms" class="dashboard-tab">

                <div class="section-toolbar">
                    <h2>Quản Lý Phòng</h2>
                    <div class="toolbar-actions">
                        <select id="room-branch-filter" class="filter-select">
                            <option value="">Tất cả chi nhánh</option>
                            <option value="Hotel">Hotel</option>
                            <option value="Villa">Villa</option>
                            <option value="Residence">Residence</option>
                        </select>
                        <div class="search-wrapper">
                            <input type="text" id="room-search" placeholder="Tìm theo tên phòng...">
                            <i class="ph ph-magnifying-glass"></i>
                        </div>
                        <button onclick="AdminApp.openRoomModal()" class="btn-primary">
                            <i class="ph ph-plus"></i> Thêm phòng
                        </button>
                    </div>
                </div>

                <div class="data-table-container">
                    <table class="manager-table">
                        <thead>
                            <tr>
                                <th>Phòng</th>
                                <th>Chi nhánh</th>
                                <th>Loại</th>
                                <th>Giá / đêm</th>
                                <th>GoHost ID</th>
                                <th>Trạng thái</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="rooms-list-body">
                            <tr>
                                <td colspan="7" style="text-align:center; color:var(--lux-gray); padding:40px;">
                                    Đang tải...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="rooms-pagination" class="pagination-container"></div>

            </section>
            @endif

            {{-- ═══ TAB: MEMBERS (admin only) ═══ --}}
            @if(auth()->user()->isAdmin())
            <section id="tab-members" class="dashboard-tab">

                <div class="section-toolbar">
                    <h2>Quản Lý Thành Viên</h2>
                    <div class="toolbar-actions">
                        <div class="search-wrapper">
                            <input type="text" id="member-search" placeholder="Tìm tên hoặc email...">
                            <i class="ph ph-magnifying-glass"></i>
                        </div>
                        <button onclick="AdminApp.openMemberModal()" class="btn-primary">
                            <i class="ph ph-plus"></i> Thêm tài khoản
                        </button>
                    </div>
                </div>

                <div class="data-table-container">
                    <table class="manager-table">
                        <thead>
                            <tr>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th>Ngày tạo</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="members-list-body">
                            <tr>
                                <td colspan="5" style="text-align:center; color:var(--lux-gray); padding:40px;">
                                    Đang tải...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="members-pagination" class="pagination-container"></div>

            </section>
            @endif

            {{-- ═══ TAB: SETTINGS / BUSINESS INFO (admin only) ═══ --}}
            @if(auth()->user()->isAdmin())
            <section id="tab-settings" class="dashboard-tab">

                <div class="section-toolbar">
                    <h2>Thông Tin Doanh Nghiệp</h2>
                </div>

                <form id="settings-form" style="max-width:760px;">
                    @csrf

                    <div class="mf-grid-2">
                        <div class="mf-group" style="grid-column:1/-1;">
                            <label class="mf-label">Logo</label>
                            <input type="file" id="settings-logo-input" accept="image/*" style="display:none">
                            <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                                <div id="settings-logo-slot"
                                     style="width:120px; height:80px; border:2px dashed var(--border-strong); border-radius:11px; overflow:hidden; position:relative; cursor:pointer; background:#FAFAFA; display:flex; align-items:center; justify-content:center; flex-shrink:0;"
                                     onclick="AdminApp.openSettingsLogoPicker()">
                                    <div class="slot-empty" id="settings-logo-empty">
                                        <i class="ph ph-image" style="font-size:1.6rem; color:#CBD5E1;"></i>
                                    </div>
                                    <img id="settings-logo-preview" style="display:none; width:100%; height:100%; object-fit:contain;">
                                    <div id="settings-logo-uploading" style="display:none; position:absolute; inset:0; background:rgba(255,255,255,.85); flex-direction:column; align-items:center; justify-content:center; gap:6px;">
                                        <div style="width:70%; height:3px; background:var(--border); border-radius:2px; overflow:hidden;">
                                            <div id="settings-logo-bar" style="height:100%; background:var(--orange); width:0%; transition:width .3s;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <button type="button" class="btn-view" onclick="AdminApp.openSettingsLogoPicker()">
                                        <i class="ph ph-upload-simple"></i> Tải logo lên
                                    </button>
                                    <button type="button" class="btn-view" style="margin-left:8px; color:#dc2626;" onclick="AdminApp.clearSettingsLogo()">
                                        <i class="ph ph-trash"></i> Xóa
                                    </button>
                                    <p style="margin:7px 0 0; font-size:0.74rem; color:var(--text-muted);">JPG/PNG/WebP/SVG, tối đa 2MB.</p>
                                </div>
                            </div>
                            <input type="hidden" id="settings-logo">
                        </div>

                        <div class="mf-group" style="grid-column:1/-1;">
                            <label class="mf-label">Tên doanh nghiệp</label>
                            <input type="text" id="settings-site-name" class="mf-input" placeholder="LuxNest">
                        </div>

                        <div class="mf-group">
                            <label class="mf-label">Hotline</label>
                            <input type="text" id="settings-hotline" class="mf-input" placeholder="+84 912 345 678">
                        </div>
                        <div class="mf-group">
                            <label class="mf-label">Email</label>
                            <input type="email" id="settings-email" class="mf-input" placeholder="hello@luxnest.com">
                        </div>

                        <div class="mf-group" style="grid-column:1/-1;">
                            <label class="mf-label">Địa chỉ</label>
                            <input type="text" id="settings-address" class="mf-input" placeholder="78 Nguyễn Thị Minh Khai, Nha Trang">
                        </div>
                        <div class="mf-group" style="grid-column:1/-1;">
                            <label class="mf-label">Link Google Maps</label>
                            <input type="url" id="settings-map-link" class="mf-input" placeholder="https://maps.google.com/...">
                        </div>

                        <div class="mf-group">
                            <label class="mf-label">Facebook</label>
                            <input type="url" id="settings-facebook" class="mf-input" placeholder="https://facebook.com/...">
                        </div>
                        <div class="mf-group">
                            <label class="mf-label">Instagram</label>
                            <input type="url" id="settings-instagram" class="mf-input" placeholder="https://instagram.com/...">
                        </div>
                        <div class="mf-group">
                            <label class="mf-label">YouTube</label>
                            <input type="url" id="settings-youtube" class="mf-input" placeholder="https://youtube.com/...">
                        </div>

                        <div class="mf-group" style="grid-column:1/-1;">
                            <label class="mf-label">Mô tả footer</label>
                            <textarea id="settings-footer-desc" class="mf-input" rows="3" placeholder="Trải nghiệm lưu trú đẳng cấp tại những điểm đến đẹp nhất Việt Nam."></textarea>
                        </div>
                    </div>

                    <div id="settings-form-error"
                         style="display:none; margin-top:14px; padding:11px 14px; background:#FEE2E2; border-radius:9px; color:#991B1B; font-weight:600; font-size:0.85rem;"></div>
                    <div id="settings-form-success"
                         style="display:none; margin-top:14px; padding:11px 14px; background:#DCFCE7; border-radius:9px; color:#166534; font-weight:600; font-size:0.85rem;"></div>

                    <div style="margin-top:24px; display:flex; gap:10px; justify-content:flex-end;">
                        <button type="submit" id="settings-submit-btn" class="btn-primary" style="padding:10px 22px;">
                            <i class="ph ph-floppy-disk"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>

            </section>
            @endif

        </div>{{-- /page-content --}}
    </main>
</div>

{{-- ═══════════════════ QR CHECK-IN MODAL ═══════════════════ --}}
@if($qrCheckinOrder)
<div id="qr-checkin-modal" class="modal-overlay" style="display:flex;">
    <div class="modal-content" style="max-width:480px; text-align:center;">
        <button class="modal-close" id="qr-checkin-close"><i class="ph ph-x"></i></button>

        <div style="width:60px; height:60px; background:var(--orange-soft); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 18px; font-size:1.8rem; color:var(--orange);">
            <i class="ph ph-qr-code"></i>
        </div>

        <h2 style="font-size:1.4rem; font-weight:800; color:var(--text); margin:0 0 6px;">Xác nhận Check-in</h2>
        <p style="color:var(--text-muted); font-size:0.88rem; margin:0 0 22px;">Kiểm tra thông tin khách hàng trước khi xác nhận</p>

        <div style="background:var(--bg); border-radius:14px; padding:18px 20px; text-align:left; margin-bottom:22px;">
            <div style="display:flex; flex-direction:column; gap:10px;">
                <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:10px; border-bottom:1px solid var(--border);">
                    <span style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted);">Đơn hàng</span>
                    <span style="font-weight:800; color:var(--orange); font-size:1rem;">#{{ str_pad($qrCheckinOrder['id'], 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--text-muted); font-size:0.88rem;">Khách hàng</span>
                    <strong style="font-size:0.88rem;">{{ $qrCheckinOrder['customer_name'] }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--text-muted); font-size:0.88rem;">Phòng</span>
                    <strong style="font-size:0.88rem;">{{ $qrCheckinOrder['room_name'] }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--text-muted); font-size:0.88rem;">Check-in</span>
                    <strong style="font-size:0.88rem;">{{ \Carbon\Carbon::parse($qrCheckinOrder['checkin_date'])->format('d/m/Y') }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--text-muted); font-size:0.88rem;">Check-out</span>
                    <strong style="font-size:0.88rem;">{{ \Carbon\Carbon::parse($qrCheckinOrder['checkout_date'])->format('d/m/Y') }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; padding-top:10px; border-top:1px solid var(--border);">
                    <span style="color:var(--text-muted); font-size:0.88rem;">Tổng tiền</span>
                    <strong style="color:var(--orange); font-size:1rem;">{{ number_format($qrCheckinOrder['total_amount'], 0, ',', '.') }}₫</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--text-muted); font-size:0.88rem;">SĐT</span>
                    <span style="font-size:0.88rem;">{{ $qrCheckinOrder['customer_phone'] }}</span>
                </div>
            </div>
        </div>

        <div id="qr-checkin-result" style="display:none; padding:12px 16px; border-radius:10px; margin-bottom:16px; font-weight:600; font-size:0.88rem;"></div>

        <div style="display:flex; gap:10px;">
            <button type="button" id="qr-checkin-close-btn" class="btn-view" style="flex:1; justify-content:center; padding:12px;">Đóng</button>
            <button type="button" id="qr-checkin-confirm"
                    class="btn-primary"
                    style="flex:2; justify-content:center; padding:12px; font-size:0.95rem;"
                    data-order-id="{{ $qrCheckinOrder['id'] }}"
                    data-auth="{{ $qrCheckinOrder['auth'] }}">
                <i class="ph ph-check-circle"></i> Xác nhận Check-in
            </button>
        </div>
    </div>
</div>
@endif

@if($qrCheckinError)
<div id="qr-error-toast"
     style="position:fixed; bottom:32px; left:50%; transform:translateX(-50%); background:#FEE2E2; border:1px solid #FECACA; color:#991B1B; padding:14px 22px; border-radius:12px; font-weight:600; font-size:0.9rem; z-index:9999; display:flex; align-items:center; gap:10px; box-shadow:0 4px 20px rgba(0,0,0,0.1);">
    <i class="ph ph-warning-circle" style="font-size:1.2rem;"></i>
    {{ $qrCheckinError }}
    <button onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; color:#991B1B; font-size:1.1rem; margin-left:8px;">×</button>
</div>
@endif

{{-- ═══════════════════ BOOKING DETAIL MODAL ═══════════════════ --}}
<div id="booking-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <button class="modal-close" id="booking-modal-close"><i class="ph ph-x"></i></button>
        <div id="modal-loader" class="modal-loader">
            <div class="spinner"></div>
            <p style="color:var(--text-muted); font-size:0.9rem;">Đang tải chi tiết...</p>
        </div>
        <div id="modal-data" class="modal-body" style="display:none;"></div>
    </div>
</div>

{{-- ═══════════════════ MEMBER MODAL (admin only) ═══════════════════ --}}
@if(auth()->user()->isAdmin())
<div id="member-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:520px;">
        <button class="modal-close" id="member-modal-close"><i class="ph ph-x"></i></button>
        <h2 id="member-modal-title" style="font-size:1.4rem; font-weight:800; margin-bottom:24px; color:var(--text);">
            Thêm tài khoản
        </h2>

        <form id="member-form">
            @csrf
            <input type="hidden" id="member-id">

            <div class="mf-grid-2">
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">Họ và tên</label>
                    <div class="auth-input-wrap">
                        <i class="ph ph-user"></i>
                        <input type="text" id="member-name" class="mf-input" placeholder="Nguyễn Văn A">
                    </div>
                </div>
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">Email</label>
                    <div class="auth-input-wrap">
                        <i class="ph ph-envelope"></i>
                        <input type="email" id="member-email" class="mf-input" placeholder="email@example.com">
                    </div>
                </div>
                <div class="mf-group">
                    <label class="mf-label">Vai trò</label>
                    <select id="member-role" class="mf-select">
                        <option value="member">Thành viên</option>
                        <option value="employee">Nhân viên</option>
                        <option value="admin">Quản trị viên</option>
                    </select>
                </div>
                <div class="mf-group">
                    <label class="mf-label">
                        Mật khẩu
                        <span id="member-pass-hint" style="font-size:0.75rem; color:var(--text-muted); font-weight:400; text-transform:none; letter-spacing:0;">(bắt buộc)</span>
                    </label>
                    <div class="auth-input-wrap">
                        <i class="ph ph-lock"></i>
                        <input type="password" id="member-password" class="mf-input" placeholder="Tối thiểu 8 ký tự">
                    </div>
                </div>
            </div>

            <div id="member-form-error"
                 style="display:none; margin-top:14px; padding:11px 14px; background:#FEE2E2; border-radius:9px; color:#991B1B; font-weight:600; font-size:0.85rem;"></div>

            <div style="margin-top:24px; display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" id="member-modal-cancel" class="btn-view">Hủy</button>
                <button type="submit" class="btn-primary" style="padding:10px 22px;">Lưu tài khoản</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ═══════════════════ ROOM MODAL (admin only) ═══════════════════ --}}
@if(auth()->user()->isAdmin())
<div id="room-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:660px; max-height:90vh; overflow-y:auto;">
        <button class="modal-close" id="room-modal-close"><i class="ph ph-x"></i></button>
        <h2 id="room-modal-title" style="font-size:1.4rem; font-weight:800; margin-bottom:24px; color:var(--text);">
            Thêm phòng
        </h2>

        <form id="room-form">
            @csrf
            <input type="hidden" id="room-id">

            <div class="mf-grid-2">
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">Tên phòng</label>
                    <input type="text" id="room-name" class="mf-input" placeholder="VD: Deluxe Valley View">
                </div>
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">Slug <span style="font-weight:400; font-size:0.75rem; text-transform:none; letter-spacing:0;">(URL, không dấu)</span></label>
                    <input type="text" id="room-slug" class="mf-input" placeholder="deluxe-valley-view">
                </div>
                <div class="mf-group">
                    <label class="mf-label">Chi nhánh</label>
                    <select id="room-branch" class="mf-select">
                        <option value="Hotel">Hotel</option>
                        <option value="Villa">Villa</option>
                        <option value="Residence">Residence</option>
                    </select>
                </div>
                <div class="mf-group">
                    <label class="mf-label">Loại phòng</label>
                    <input type="text" id="room-type" class="mf-input" placeholder="VD: Deluxe, Suite, Standard...">
                </div>
                <div class="mf-group">
                    <label class="mf-label">Giá / đêm (VNĐ)</label>
                    <input type="number" id="room-price" class="mf-input" placeholder="800000" min="0" step="10000">
                </div>
                <div class="mf-group">
                    <label class="mf-label">Giá gốc (để trống nếu không giảm)</label>
                    <input type="number" id="room-regular-price" class="mf-input" placeholder="1000000" min="0" step="10000">
                </div>

                {{-- Gallery --}}
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">
                        Ảnh phòng
                        <span style="font-weight:400; font-size:0.75rem; text-transform:none; letter-spacing:0;"> — tối đa 5 tấm, tấm đầu là ảnh đại diện</span>
                    </label>

                    <input type="file" id="room-file-input" accept="image/*" style="display:none">

                    <div id="room-gallery-grid" style="display:grid; grid-template-columns:1fr repeat(4,72px); gap:8px; align-items:start;">

                        {{-- Slot 0: main --}}
                        <div class="img-slot img-slot--main" data-slot="0"
                             style="border:2px dashed var(--border-strong); border-radius:11px; overflow:hidden; aspect-ratio:4/3; position:relative; cursor:pointer; background:#FAFAFA; display:flex; flex-direction:column; align-items:center; justify-content:center; transition:border-color .2s;"
                             onclick="AdminApp.openSlotPicker(0)"
                             ondragover="event.preventDefault(); this.style.borderColor='var(--orange)'"
                             ondragleave="this.style.borderColor='var(--border-strong)'"
                             ondrop="AdminApp.handleSlotDrop(event,0)">
                            <div class="slot-empty">
                                <i class="ph ph-image" style="font-size:1.8rem; color:#CBD5E1;"></i>
                                <span style="font-size:0.72rem; color:#94A3B8; margin-top:5px; display:block;">Ảnh chính</span>
                            </div>
                            <img class="slot-img" style="display:none; width:100%; height:100%; object-fit:cover;">
                            <div class="slot-overlay" style="display:none; position:absolute; inset:0; background:rgba(0,0,0,.4); align-items:center; justify-content:center; gap:8px;">
                                <button type="button" onclick="event.stopPropagation(); AdminApp.openSlotPicker(0)"
                                        style="background:#fff; border:none; border-radius:7px; padding:5px 9px; cursor:pointer; font-size:0.78rem; font-weight:600;">
                                    <i class="ph ph-pencil"></i>
                                </button>
                                <button type="button" onclick="event.stopPropagation(); AdminApp.clearSlot(0)"
                                        style="background:#EF4444; color:#fff; border:none; border-radius:7px; padding:5px 9px; cursor:pointer; font-size:0.78rem;">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                            <div class="slot-uploading" style="display:none; position:absolute; inset:0; background:rgba(255,255,255,.85); flex-direction:column; align-items:center; justify-content:center; gap:7px;">
                                <div style="width:60%; height:4px; background:var(--border); border-radius:3px; overflow:hidden;">
                                    <div class="slot-bar" style="height:100%; background:var(--orange); width:0%; transition:width .3s;"></div>
                                </div>
                                <span style="font-size:0.72rem; color:var(--text-muted);">Đang tải...</span>
                            </div>
                        </div>

                        {{-- Slots 1–4 --}}
                        @for($s = 1; $s <= 4; $s++)
                        <div class="img-slot" data-slot="{{ $s }}"
                             style="border:2px dashed var(--border-strong); border-radius:9px; overflow:hidden; aspect-ratio:1; position:relative; cursor:pointer; background:#FAFAFA; display:flex; flex-direction:column; align-items:center; justify-content:center; transition:border-color .2s;"
                             onclick="AdminApp.openSlotPicker({{ $s }})"
                             ondragover="event.preventDefault(); this.style.borderColor='var(--orange)'"
                             ondragleave="this.style.borderColor='var(--border-strong)'"
                             ondrop="AdminApp.handleSlotDrop(event,{{ $s }})">
                            <div class="slot-empty">
                                <i class="ph ph-plus" style="font-size:1.2rem; color:#CBD5E1;"></i>
                            </div>
                            <img class="slot-img" style="display:none; width:100%; height:100%; object-fit:cover;">
                            <div class="slot-overlay" style="display:none; position:absolute; inset:0; background:rgba(0,0,0,.4); align-items:center; justify-content:center; gap:5px;">
                                <button type="button" onclick="event.stopPropagation(); AdminApp.openSlotPicker({{ $s }})"
                                        style="background:#fff; border:none; border-radius:6px; padding:4px 7px; cursor:pointer; font-size:0.72rem; font-weight:600;">
                                    <i class="ph ph-pencil"></i>
                                </button>
                                <button type="button" onclick="event.stopPropagation(); AdminApp.clearSlot({{ $s }})"
                                        style="background:#EF4444; color:#fff; border:none; border-radius:6px; padding:4px 7px; cursor:pointer; font-size:0.72rem;">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                            <div class="slot-uploading" style="display:none; position:absolute; inset:0; background:rgba(255,255,255,.85); flex-direction:column; align-items:center; justify-content:center; gap:6px;">
                                <div style="width:70%; height:3px; background:var(--border); border-radius:2px; overflow:hidden;">
                                    <div class="slot-bar" style="height:100%; background:var(--orange); width:0%; transition:width .3s;"></div>
                                </div>
                            </div>
                        </div>
                        @endfor

                    </div>
                    <p style="margin:7px 0 0; font-size:0.74rem; color:var(--text-muted);">
                        <i class="ph ph-info"></i> Click hoặc kéo thả ảnh vào ô. JPG/PNG/WebP, tối đa 4MB mỗi tấm.
                    </p>
                    <input type="hidden" id="room-image">
                </div>

                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">Tiện nghi <span style="font-weight:400; font-size:0.75rem; text-transform:none; letter-spacing:0;">(phân cách bằng dấu phẩy)</span></label>
                    <input type="text" id="room-amenities" class="mf-input" placeholder="Wi-Fi, Điều hòa, TV, Bể bơi...">
                </div>
                <div class="mf-group">
                    <label class="mf-label">GoHost Room Type ID</label>
                    <input type="text" id="room-gohost-id" class="mf-input" placeholder="UUID từ GoHost PMS">
                </div>
                <div class="mf-group">
                    <label class="mf-label">Trạng thái</label>
                    <select id="room-status" class="mf-select">
                        <option value="active">Đang hoạt động</option>
                        <option value="inactive">Tạm ẩn</option>
                    </select>
                </div>
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">Mô tả <span style="font-weight:400; font-size:0.75rem; text-transform:none; letter-spacing:0;">(tuỳ chọn)</span></label>
                    <textarea id="room-description" class="mf-input" rows="3" placeholder="Mô tả ngắn về phòng..."></textarea>
                </div>
            </div>

            <div id="room-form-error"
                 style="display:none; margin-top:14px; padding:11px 14px; background:#FEE2E2; border-radius:9px; color:#991B1B; font-weight:600; font-size:0.85rem;"></div>

            <div style="margin-top:24px; display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" id="room-modal-cancel" class="btn-view">Hủy</button>
                <button type="submit" id="room-submit-btn" class="btn-primary" style="padding:10px 22px;">Lưu phòng</button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script src="{{ asset('assets/js/admin-app.js') }}"></script>
<script>
(function () {
    // ── QR Check-in modal ──────────────────────────────────────
    var qrModal   = document.getElementById('qr-checkin-modal');
    var confirmBtn = document.getElementById('qr-checkin-confirm');
    var resultEl  = document.getElementById('qr-checkin-result');

    function closeQrModal() {
        if (qrModal) qrModal.style.display = 'none';
        // Xóa QR params khỏi URL mà không reload
        var url = new URL(window.location.href);
        url.searchParams.delete('checkin');
        url.searchParams.delete('oid');
        url.searchParams.delete('auth');
        history.replaceState(null, '', url.toString());
    }

    document.getElementById('qr-checkin-close')?.addEventListener('click', closeQrModal);
    document.getElementById('qr-checkin-close-btn')?.addEventListener('click', closeQrModal);
    qrModal?.addEventListener('click', function (e) { if (e.target === this) closeQrModal(); });

    confirmBtn?.addEventListener('click', async function () {
        var orderId = this.dataset.orderId;
        var auth    = this.dataset.auth;

        this.disabled    = true;
        this.textContent = 'Đang xử lý...';

        try {
            var res = await fetch('{{ url("/admin/api/qr-checkin") }}', {
                method:  'POST',
                headers: {
                    'Content-Type':  'application/json',
                    'Accept':        'application/json',
                    'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ order_id: orderId, auth: auth }),
            }).then(r => r.json());

            resultEl.style.display = 'block';
            if (res.success) {
                resultEl.style.background = '#D1FAE5';
                resultEl.style.color      = '#065F46';
                resultEl.style.border     = '1px solid #6EE7B7';
                resultEl.innerHTML = '<i class="ph ph-check-circle"></i> ' + (res.message || 'Check-in thành công!');
                this.style.display = 'none';
                document.getElementById('qr-checkin-close-btn').textContent = 'Đóng';
            } else {
                resultEl.style.background = '#FEE2E2';
                resultEl.style.color      = '#991B1B';
                resultEl.style.border     = '1px solid #FECACA';
                resultEl.innerHTML = '<i class="ph ph-warning-circle"></i> ' + (res.message || 'Có lỗi xảy ra.');
                this.disabled    = false;
                this.innerHTML   = '<i class="ph ph-check-circle"></i> Xác nhận Check-in';
            }
        } catch (err) {
            resultEl.style.display    = 'block';
            resultEl.style.background = '#FEE2E2';
            resultEl.style.color      = '#991B1B';
            resultEl.textContent      = 'Lỗi kết nối. Vui lòng thử lại.';
            this.disabled  = false;
            this.innerHTML = '<i class="ph ph-check-circle"></i> Xác nhận Check-in';
        }
    });

    // Tự đóng toast lỗi sau 6s
    var toast = document.getElementById('qr-error-toast');
    if (toast) setTimeout(function () { toast.remove(); }, 6000);
})();
</script>
@if(auth()->user()->isAdmin())
<script>
if (typeof AdminApp !== 'undefined') AdminApp.loadRooms();
</script>
@endif
<script>
(function () {
    // Topbar date
    var d = new Date();
    var el = document.getElementById('topbar-date');
    if (el) el.textContent = d.toLocaleDateString('vi-VN', { weekday: 'long', day: 'numeric', month: 'long' });

    // Sync topbar title on tab switch
    var titles = {
        overview: 'Bảng Điều Khiển',
        bookings: 'Quản Lý Booking',
        rooms:    'Quản Lý Phòng',
        members:  'Quản Lý Thành Viên',
        settings: 'Thông Tin Doanh Nghiệp'
    };
    document.querySelectorAll('.nav-item[data-tab]').forEach(function (el) {
        el.addEventListener('click', function () {
            var t = document.getElementById('topbar-title');
            if (t) t.textContent = titles[this.dataset.tab] || '';
        });
    });

    // Mobile sidebar toggle
    var sidebar  = document.querySelector('.manager-sidebar');
    var toggle   = document.getElementById('sidebar-toggle');
    var overlay  = document.getElementById('sidebar-overlay');

    function closeSidebar() {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('show');
    }

    toggle?.addEventListener('click', function () {
        sidebar?.classList.toggle('open');
        overlay?.classList.toggle('show');
    });

    overlay?.addEventListener('click', closeSidebar);

    // Đóng sidebar khi chọn 1 mục menu (mobile)
    document.querySelectorAll('.nav-item[data-tab]').forEach(function (el) {
        el.addEventListener('click', closeSidebar);
    });
})();
</script>
@endpush
@endsection
