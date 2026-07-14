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
            <a href="#" class="nav-item" data-tab="villas">
                <i class="ph ph-house-line"></i> Quản Lý Villa
            </a>
            <a href="#" class="nav-item" data-tab="members">
                <i class="ph ph-users"></i> Thành Viên
            </a>
            <a href="#" class="nav-item" data-tab="news">
                <i class="ph ph-newspaper"></i> Tin Tức
            </a>
            <a href="#" class="nav-item" data-tab="faqs">
                <i class="ph ph-question"></i> Câu Hỏi Thường Gặp
            </a>
            <a href="#" class="nav-item" data-tab="pagecontent">
                <i class="ph ph-file-text"></i> Nội Dung Trang
            </a>
            <a href="#" class="nav-item" data-tab="voucher">
                <i class="ph ph-ticket"></i> Voucher
            </a>
            <a href="#" class="nav-item" data-tab="emailmarketing">
                <i class="ph ph-envelope-simple-open"></i> Email Marketing
            </a>
            <a href="#" class="nav-item" data-tab="gallery">
                <i class="ph ph-images"></i> Gallery
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

            {{-- ═══ TAB: VILLAS (admin only) ═══ --}}
            @if(auth()->user()->isAdmin())
            <section id="tab-villas" class="dashboard-tab">

                <div class="section-toolbar">
                    <h2>Quản Lý Villa</h2>
                    <div class="toolbar-actions">
                        <div class="search-wrapper">
                            <input type="text" id="villa-search" placeholder="Tìm theo tên hoặc địa chỉ...">
                            <i class="ph ph-magnifying-glass"></i>
                        </div>
                        <button onclick="AdminApp.openVillaModal()" class="btn-primary">
                            <i class="ph ph-plus"></i> Thêm villa
                        </button>
                    </div>
                </div>

                <div class="data-table-container">
                    <table class="manager-table">
                        <thead>
                            <tr>
                                <th>Villa</th>
                                <th>Khu vực</th>
                                <th>Phòng / Khách</th>
                                <th>Trạng thái</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="villas-list-body">
                            <tr>
                                <td colspan="5" style="text-align:center; color:var(--lux-gray); padding:40px;">
                                    Đang tải...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="villas-pagination" class="pagination-container"></div>

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

            {{-- ═══ TAB: VOUCHER (admin only) ═══ --}}
            @if(auth()->user()->isAdmin())
            <section id="tab-voucher" class="dashboard-tab">
                <div class="section-toolbar">
                    <h2>Quản Lý Voucher</h2>
                    <button class="btn-primary" onclick="AdminApp.openVoucherModal()">
                        <i class="ph ph-plus"></i> Tạo voucher
                    </button>
                </div>

                <div style="display:flex;gap:10px;margin-bottom:16px;">
                    <input type="text" id="voucher-search" class="mf-input" placeholder="Tìm theo mã hoặc tên..." style="max-width:320px;"
                           oninput="AdminApp.loadVouchers(1)">
                </div>

                <div class="data-table-container">
                    <table class="manager-table">
                        <thead>
                            <tr>
                                <th>Mã voucher</th>
                                <th>Tên</th>
                                <th>Giảm giá</th>
                                <th>Đã dùng / Tối đa</th>
                                <th>Hết hạn</th>
                                <th>Trạng thái</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="voucher-list-body">
                            <tr><td colspan="7" class="table-empty-state">Đang tải...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="voucher-pagination" class="pagination-container"></div>
            </section>
            @endif

            {{-- ═══ TAB: EMAIL MARKETING / CAMPAIGNS (admin only) ═══ --}}
            @if(auth()->user()->isAdmin())
            <section id="tab-emailmarketing" class="dashboard-tab">
                <div class="section-toolbar">
                    <h2>Email Marketing</h2>
                    <button class="btn-primary" onclick="AdminApp.openCampaignModal()">
                        <i class="ph ph-plus"></i> Tạo chiến dịch
                    </button>
                </div>

                <div class="data-table-container">
                    <table class="manager-table">
                        <thead>
                            <tr>
                                <th>Tên chiến dịch</th>
                                <th>Điều kiện</th>
                                <th>Voucher</th>
                                <th style="text-align:center;">Đủ điều kiện</th>
                                <th>Trạng thái</th>
                                <th>Ngày gửi</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="campaign-list-body">
                            <tr><td colspan="7" class="table-empty-state">Đang tải...</td></tr>
                        </tbody>
                    </table>
                </div>
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
                            <label class="mf-label">Ảnh OG (share link)
                                <span style="font-weight:400;font-size:0.75rem;text-transform:none;letter-spacing:0;">— khuyến nghị 1200×630px, dùng khi share link lên Zalo/Facebook</span>
                            </label>
                            <input type="file" id="settings-og-input" accept="image/jpeg,image/png,image/webp" style="display:none">
                            <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                                <div id="settings-og-slot"
                                     style="width:200px; height:105px; border:2px dashed var(--border-strong); border-radius:11px; overflow:hidden; position:relative; cursor:pointer; background:#FAFAFA; display:flex; align-items:center; justify-content:center; flex-shrink:0;"
                                     onclick="AdminApp.openSettingsOgPicker()">
                                    <div class="slot-empty" id="settings-og-empty">
                                        <i class="ph ph-image" style="font-size:1.6rem; color:#CBD5E1;"></i>
                                        <span style="font-size:0.7rem;color:#94a3b8;margin-top:4px;display:block;">1200×630px</span>
                                    </div>
                                    <img id="settings-og-preview" style="display:none; width:100%; height:100%; object-fit:cover;">
                                    <div id="settings-og-uploading" style="display:none; position:absolute; inset:0; background:rgba(255,255,255,.85); flex-direction:column; align-items:center; justify-content:center; gap:6px;">
                                        <div style="width:70%; height:3px; background:var(--border); border-radius:2px; overflow:hidden;">
                                            <div id="settings-og-bar" style="height:100%; background:var(--orange); width:0%; transition:width .3s;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <button type="button" class="btn-view" onclick="AdminApp.openSettingsOgPicker()">
                                        <i class="ph ph-upload-simple"></i> Tải ảnh lên
                                    </button>
                                    <button type="button" class="btn-view" style="margin-left:8px; color:#dc2626;" onclick="AdminApp.clearSettingsOg()">
                                        <i class="ph ph-trash"></i> Xóa
                                    </button>
                                    <p style="margin:7px 0 0; font-size:0.74rem; color:var(--text-muted);">JPG/PNG/WebP, tối đa 4MB. Tỉ lệ 1.91:1 (1200×630).</p>
                                </div>
                            </div>
                            <input type="hidden" id="settings-og-image">
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

            {{-- ═══ TAB: NEWS (admin only) ═══ --}}
            @if(auth()->user()->isAdmin())
            <section id="tab-news" class="dashboard-tab">

                <div class="section-toolbar">
                    <h2>Quản Lý Tin Tức</h2>
                    <div class="toolbar-actions">
                        <div class="search-wrapper">
                            <input type="text" id="news-search" placeholder="Tìm theo tiêu đề...">
                            <i class="ph ph-magnifying-glass"></i>
                        </div>
                        <button onclick="AdminApp.openNewsModal()" class="btn-primary">
                            <i class="ph ph-plus"></i> Thêm bài viết
                        </button>
                    </div>
                </div>

                <div class="data-table-container">
                    <table class="manager-table">
                        <thead>
                            <tr>
                                <th>Bài viết</th>
                                <th>Tag</th>
                                <th>Ngày đăng</th>
                                <th>Trạng thái</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="news-list-body">
                            <tr>
                                <td colspan="5" style="text-align:center; color:var(--lux-gray); padding:40px;">
                                    Đang tải...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="news-pagination" class="pagination-container"></div>

            </section>
            @endif

            {{-- ═══ TAB: FAQ (admin only) ═══ --}}
            @if(auth()->user()->isAdmin())
            <section id="tab-faqs" class="dashboard-tab">

                <div class="section-toolbar">
                    <h2>Quản Lý Câu Hỏi Thường Gặp</h2>
                    <div class="toolbar-actions">
                        <button onclick="AdminApp.openFaqModal()" class="btn-primary">
                            <i class="ph ph-plus"></i> Thêm câu hỏi
                        </button>
                    </div>
                </div>

                <div class="data-table-container">
                    <table class="manager-table">
                        <thead>
                            <tr>
                                <th>Nhóm</th>
                                <th>Câu hỏi</th>
                                <th>Thứ tự</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="faqs-list-body">
                            <tr>
                                <td colspan="4" style="text-align:center; color:var(--lux-gray); padding:40px;">
                                    Đang tải...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </section>
            @endif

            {{-- ═══ TAB: PAGE CONTENT - Giới thiệu / Hợp tác (admin only) ═══ --}}
            @if(auth()->user()->isAdmin())
            <section id="tab-pagecontent" class="dashboard-tab">

                <div class="section-toolbar">
                    <h2>Nội Dung Trang</h2>
                </div>

                {{-- ── Trang Giới thiệu ── --}}
                <div style="margin-bottom:40px;">
                    <h3 style="font-size:1.05rem; font-weight:800; color:var(--text); margin-bottom:14px;">
                        <i class="ph ph-info"></i> Trang Giới thiệu
                    </h3>

                    <form id="about-content-form" style="max-width:920px;">
                        @csrf
                        <div class="mf-grid-2">
                            <div class="mf-group" style="grid-column:1/-1;">
                                <label class="mf-label">Tiêu đề Hero</label>
                                <input type="text" id="about-hero_title" class="mf-input">
                            </div>
                            <div class="mf-group" style="grid-column:1/-1;">
                                <label class="mf-label">Mô tả Hero</label>
                                <textarea id="about-hero_subtitle" class="mf-input" rows="2"></textarea>
                            </div>

                            <div class="mf-group" style="grid-column:1/-1;">
                                <label class="mf-label">Tiêu đề mục "Câu chuyện"</label>
                                <input type="text" id="about-story_title" class="mf-input">
                            </div>
                            <div class="mf-group" style="grid-column:1/-1;">
                                <label class="mf-label">Đoạn văn 1</label>
                                <textarea id="about-story_paragraph_1" class="mf-input" rows="3"></textarea>
                            </div>
                            <div class="mf-group" style="grid-column:1/-1;">
                                <label class="mf-label">Đoạn văn 2</label>
                                <textarea id="about-story_paragraph_2" class="mf-input" rows="3"></textarea>
                            </div>

                            <div class="mf-group" style="grid-column:1/-1;">
                                <label class="mf-label">Tiêu đề mục "Vì sao chọn..."</label>
                                <input type="text" id="about-why_title" class="mf-input">
                            </div>

                            @for($i = 1; $i <= 3; $i++)
                            <div class="mf-group">
                                <label class="mf-label">Icon thẻ {{ $i }}</label>
                                <input type="text" id="about-why_card_{{ $i }}_icon" class="mf-input">
                            </div>
                            <div class="mf-group">
                                <label class="mf-label">Tiêu đề thẻ {{ $i }}</label>
                                <input type="text" id="about-why_card_{{ $i }}_title" class="mf-input">
                            </div>
                            <div class="mf-group" style="grid-column:1/-1;">
                                <label class="mf-label">Nội dung thẻ {{ $i }}</label>
                                <textarea id="about-why_card_{{ $i }}_text" class="mf-input" rows="2"></textarea>
                            </div>
                            @endfor

                            @for($i = 1; $i <= 4; $i++)
                            <div class="mf-group">
                                <label class="mf-label">Số liệu {{ $i }}</label>
                                <input type="text" id="about-stat_{{ $i }}_number" class="mf-input">
                            </div>
                            <div class="mf-group">
                                <label class="mf-label">Nhãn số liệu {{ $i }}</label>
                                <input type="text" id="about-stat_{{ $i }}_label" class="mf-input">
                            </div>
                            @endfor

                            <div class="mf-group" style="grid-column:1/-1;">
                                <label class="mf-label">Tiêu đề CTA</label>
                                <input type="text" id="about-cta_title" class="mf-input">
                            </div>
                            <div class="mf-group" style="grid-column:1/-1;">
                                <label class="mf-label">Mô tả CTA</label>
                                <textarea id="about-cta_text" class="mf-input" rows="2"></textarea>
                            </div>
                            <div class="mf-group">
                                <label class="mf-label">Chữ trên nút CTA</label>
                                <input type="text" id="about-cta_button" class="mf-input">
                            </div>
                        </div>

                        <div id="about-content-error"
                             style="display:none; margin-top:14px; padding:11px 14px; background:#FEE2E2; border-radius:9px; color:#991B1B; font-weight:600; font-size:0.85rem;"></div>
                        <div id="about-content-success"
                             style="display:none; margin-top:14px; padding:11px 14px; background:#DCFCE7; border-radius:9px; color:#166534; font-weight:600; font-size:0.85rem;"></div>

                        <div style="margin-top:24px; display:flex; gap:10px; justify-content:flex-end;">
                            <button type="submit" class="btn-primary" style="padding:10px 22px;">
                                <i class="ph ph-floppy-disk"></i> Lưu nội dung Giới thiệu
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ── Trang Hợp tác ── --}}
                <div>
                    <h3 style="font-size:1.05rem; font-weight:800; color:var(--text); margin-bottom:14px;">
                        <i class="ph ph-handshake"></i> Trang Hợp tác
                    </h3>

                    <form id="partner-content-form" style="max-width:920px;">
                        @csrf
                        <div class="mf-grid-2">
                            <div class="mf-group" style="grid-column:1/-1;">
                                <label class="mf-label">Tiêu đề Hero</label>
                                <input type="text" id="partner-hero_title" class="mf-input">
                            </div>
                            <div class="mf-group" style="grid-column:1/-1;">
                                <label class="mf-label">Mô tả Hero</label>
                                <textarea id="partner-hero_subtitle" class="mf-input" rows="2"></textarea>
                            </div>

                            <div class="mf-group" style="grid-column:1/-1;">
                                <label class="mf-label">Tiêu đề mục "Hình thức hợp tác"</label>
                                <input type="text" id="partner-types_title" class="mf-input">
                            </div>

                            @for($i = 1; $i <= 3; $i++)
                            <div class="mf-group">
                                <label class="mf-label">Icon hình thức {{ $i }}</label>
                                <input type="text" id="partner-type_{{ $i }}_icon" class="mf-input">
                            </div>
                            <div class="mf-group">
                                <label class="mf-label">Tiêu đề hình thức {{ $i }}</label>
                                <input type="text" id="partner-type_{{ $i }}_title" class="mf-input">
                            </div>
                            <div class="mf-group" style="grid-column:1/-1;">
                                <label class="mf-label">Nội dung hình thức {{ $i }}</label>
                                <textarea id="partner-type_{{ $i }}_text" class="mf-input" rows="2"></textarea>
                            </div>
                            @endfor

                            <div class="mf-group" style="grid-column:1/-1;">
                                <label class="mf-label">Tiêu đề mục "Quyền lợi khi hợp tác"</label>
                                <input type="text" id="partner-benefits_title" class="mf-input">
                            </div>

                            @for($i = 1; $i <= 4; $i++)
                            <div class="mf-group">
                                <label class="mf-label">Icon quyền lợi {{ $i }}</label>
                                <input type="text" id="partner-benefit_{{ $i }}_icon" class="mf-input">
                            </div>
                            <div class="mf-group">
                                <label class="mf-label">Tiêu đề quyền lợi {{ $i }}</label>
                                <input type="text" id="partner-benefit_{{ $i }}_title" class="mf-input">
                            </div>
                            <div class="mf-group" style="grid-column:1/-1;">
                                <label class="mf-label">Nội dung quyền lợi {{ $i }}</label>
                                <textarea id="partner-benefit_{{ $i }}_text" class="mf-input" rows="2"></textarea>
                            </div>
                            @endfor
                        </div>

                        <div id="partner-content-error"
                             style="display:none; margin-top:14px; padding:11px 14px; background:#FEE2E2; border-radius:9px; color:#991B1B; font-weight:600; font-size:0.85rem;"></div>
                        <div id="partner-content-success"
                             style="display:none; margin-top:14px; padding:11px 14px; background:#DCFCE7; border-radius:9px; color:#166534; font-weight:600; font-size:0.85rem;"></div>

                        <div style="margin-top:24px; display:flex; gap:10px; justify-content:flex-end;">
                            <button type="submit" class="btn-primary" style="padding:10px 22px;">
                                <i class="ph ph-floppy-disk"></i> Lưu nội dung Hợp tác
                            </button>
                        </div>
                    </form>
                </div>

            </section>

            {{-- ═══════════════════ GALLERY TAB ═══════════════════ --}}
            <section id="tab-gallery" class="dashboard-tab">

                <div class="section-toolbar">
                    <h2>Gallery Ảnh</h2>
                    <div class="toolbar-actions">
                        <button class="btn-primary" onclick="AdminApp.openGalleryPhotoModal()">
                            <i class="ph ph-plus"></i> Thêm ảnh
                        </button>
                    </div>
                </div>

                <p style="font-size:.83rem;color:var(--text-muted);margin-bottom:18px;">
                    Ảnh sẽ hiển thị theo thứ tự <strong>Thứ tự sắp xếp</strong> (số nhỏ lên trước). Drag-and-drop thứ tự chưa hỗ trợ — chỉnh số thứ tự khi sửa ảnh.
                </p>

                <div id="gallery-admin-grid" class="gallery-admin-grid">
                    <div class="table-empty-state"><i class="ph ph-images"></i><span>Đang tải...</span></div>
                </div>

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
                        <span style="font-weight:400; font-size:0.75rem; text-transform:none; letter-spacing:0;"> — tối đa 10 tấm, tấm đầu là ảnh đại diện</span>
                    </label>

                    <input type="file" id="room-file-input" accept="image/*" style="display:none">

                    <div id="room-gallery-grid" style="display:grid; grid-template-columns:repeat(5,72px); gap:8px; align-items:start;">

                        {{-- Slot 0: main --}}
                        <div class="img-slot img-slot--main" data-slot="0"
                             style="grid-column:1/-1; height:160px; border:2px dashed var(--border-strong); border-radius:11px; overflow:hidden; position:relative; cursor:pointer; background:#FAFAFA; display:flex; flex-direction:column; align-items:center; justify-content:center; transition:border-color .2s;"
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

                        {{-- Slots 1–9 --}}
                        @for($s = 1; $s <= 9; $s++)
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

                {{-- Video --}}
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">
                        Video phòng
                        <span style="font-weight:400; font-size:0.75rem; text-transform:none; letter-spacing:0;"> — tuỳ chọn, hiển thị đầu tiên ở trang chi tiết (tự động phát)</span>
                    </label>

                    <div style="display:flex; gap:18px; margin-bottom:12px;">
                        <label style="display:flex; align-items:center; gap:6px; font-size:0.85rem; cursor:pointer;">
                            <input type="radio" name="room-video-mode" id="room-video-mode-upload" value="upload" checked onchange="AdminApp.switchRoomVideoMode('upload')">
                            Tải video lên
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; font-size:0.85rem; cursor:pointer;">
                            <input type="radio" name="room-video-mode" id="room-video-mode-youtube" value="youtube" onchange="AdminApp.switchRoomVideoMode('youtube')">
                            Nhúng YouTube
                        </label>
                    </div>

                    <div id="room-video-upload-block">
                        <input type="file" id="room-video-input" accept="video/mp4,video/quicktime,video/webm" style="display:none">
                        <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                            <div id="room-video-slot"
                                 style="width:160px; height:90px; border:2px dashed var(--border-strong); border-radius:11px; overflow:hidden; position:relative; cursor:pointer; background:#FAFAFA; display:flex; align-items:center; justify-content:center; flex-shrink:0;"
                                 onclick="AdminApp.openRoomVideoPicker()">
                                <div class="slot-empty" id="room-video-empty">
                                    <i class="ph ph-video-camera" style="font-size:1.6rem; color:#CBD5E1;"></i>
                                </div>
                                <video id="room-video-preview" muted style="display:none; width:100%; height:100%; object-fit:cover;"></video>
                                <div id="room-video-uploading" style="display:none; position:absolute; inset:0; background:rgba(255,255,255,.85); flex-direction:column; align-items:center; justify-content:center; gap:6px;">
                                    <div style="width:70%; height:3px; background:var(--border); border-radius:2px; overflow:hidden;">
                                        <div id="room-video-bar" style="height:100%; background:var(--orange); width:0%; transition:width .3s;"></div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <button type="button" class="btn-view" onclick="AdminApp.openRoomVideoPicker()">
                                    <i class="ph ph-upload-simple"></i> Tải video lên
                                </button>
                                <button type="button" class="btn-view" style="margin-left:8px; color:#dc2626;" onclick="AdminApp.clearRoomVideo()">
                                    <i class="ph ph-trash"></i> Xóa
                                </button>
                                <p style="margin:7px 0 0; font-size:0.74rem; color:var(--text-muted);">MP4/MOV/WebM, tối đa 50MB.</p>
                            </div>
                        </div>
                    </div>

                    <div id="room-video-youtube-block" style="display:none;">
                        <input type="text" id="room-video-youtube-url" class="mf-input" placeholder="https://www.youtube.com/watch?v=...">
                        <p style="margin:7px 0 0; font-size:0.74rem; color:var(--text-muted);">Dán link YouTube, video sẽ tự động phát ở trang chi tiết.</p>
                    </div>

                    <input type="hidden" id="room-video">
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

{{-- ═══ MODAL: Villa ═══ --}}
<div id="villa-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:660px; max-height:90vh; overflow-y:auto;">
        <button class="modal-close" id="villa-modal-close"><i class="ph ph-x"></i></button>
        <h2 id="villa-modal-title" style="font-size:1.4rem; font-weight:800; margin-bottom:24px; color:var(--text);">
            Thêm villa
        </h2>

        <form id="villa-form">
            @csrf
            <input type="hidden" id="villa-id">

            <div class="mf-grid-2">
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">Tên villa</label>
                    <input type="text" id="villa-name" class="mf-input" placeholder="VD: VILLA 6V4A">
                </div>
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">Slug <span style="font-weight:400; font-size:0.75rem; text-transform:none; letter-spacing:0;">(URL, không dấu)</span></label>
                    <input type="text" id="villa-slug" class="mf-input" placeholder="villa-6v4a">
                </div>
                <div class="mf-group">
                    <label class="mf-label">Khu vực <span style="font-weight:400; font-size:0.75rem; text-transform:none; letter-spacing:0;">(dùng để lọc tab)</span></label>
                    <input type="text" id="villa-location" class="mf-input" placeholder="Đà Lạt">
                </div>
                <div class="mf-group">
                    <label class="mf-label">Địa chỉ</label>
                    <input type="text" id="villa-location-desc" class="mf-input" placeholder="VD: Triệu Việt Vương, Đà Lạt">
                </div>
                <div class="mf-group">
                    <label class="mf-label">Số phòng</label>
                    <input type="text" id="villa-beds" class="mf-input" placeholder="VD: 6 Phòng">
                </div>
                <div class="mf-group">
                    <label class="mf-label">Số khách</label>
                    <input type="text" id="villa-guests" class="mf-input" placeholder="VD: 12 Khách">
                </div>

                {{-- Gallery --}}
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">
                        Ảnh villa
                        <span style="font-weight:400; font-size:0.75rem; text-transform:none; letter-spacing:0;"> — tối đa 10 tấm, tấm đầu là ảnh đại diện</span>
                    </label>

                    <input type="file" id="villa-file-input" accept="image/*" style="display:none">

                    <div id="villa-gallery-grid" style="display:grid; grid-template-columns:repeat(5,72px); gap:8px; align-items:start;">

                        {{-- Slot 0: main --}}
                        <div class="villa-img-slot villa-img-slot--main" data-slot="0"
                             style="grid-column:1/-1; height:160px; border:2px dashed var(--border-strong); border-radius:11px; overflow:hidden; position:relative; cursor:pointer; background:#FAFAFA; display:flex; flex-direction:column; align-items:center; justify-content:center; transition:border-color .2s;"
                             onclick="AdminApp.openVillaSlotPicker(0)"
                             ondragover="event.preventDefault(); this.style.borderColor='var(--orange)'"
                             ondragleave="this.style.borderColor='var(--border-strong)'"
                             ondrop="AdminApp.handleVillaSlotDrop(event,0)">
                            <div class="slot-empty">
                                <i class="ph ph-image" style="font-size:1.8rem; color:#CBD5E1;"></i>
                                <span style="font-size:0.72rem; color:#94A3B8; margin-top:5px; display:block;">Ảnh chính</span>
                            </div>
                            <img class="slot-img" style="display:none; width:100%; height:100%; object-fit:cover;">
                            <div class="slot-overlay" style="display:none; position:absolute; inset:0; background:rgba(0,0,0,.4); align-items:center; justify-content:center; gap:8px;">
                                <button type="button" onclick="event.stopPropagation(); AdminApp.openVillaSlotPicker(0)"
                                        style="background:#fff; border:none; border-radius:7px; padding:5px 9px; cursor:pointer; font-size:0.78rem; font-weight:600;">
                                    <i class="ph ph-pencil"></i>
                                </button>
                                <button type="button" onclick="event.stopPropagation(); AdminApp.clearVillaSlot(0)"
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

                        {{-- Slots 1–9 --}}
                        @for($s = 1; $s <= 9; $s++)
                        <div class="villa-img-slot" data-slot="{{ $s }}"
                             style="border:2px dashed var(--border-strong); border-radius:9px; overflow:hidden; aspect-ratio:1; position:relative; cursor:pointer; background:#FAFAFA; display:flex; flex-direction:column; align-items:center; justify-content:center; transition:border-color .2s;"
                             onclick="AdminApp.openVillaSlotPicker({{ $s }})"
                             ondragover="event.preventDefault(); this.style.borderColor='var(--orange)'"
                             ondragleave="this.style.borderColor='var(--border-strong)'"
                             ondrop="AdminApp.handleVillaSlotDrop(event,{{ $s }})">
                            <div class="slot-empty">
                                <i class="ph ph-plus" style="font-size:1.2rem; color:#CBD5E1;"></i>
                            </div>
                            <img class="slot-img" style="display:none; width:100%; height:100%; object-fit:cover;">
                            <div class="slot-overlay" style="display:none; position:absolute; inset:0; background:rgba(0,0,0,.4); align-items:center; justify-content:center; gap:5px;">
                                <button type="button" onclick="event.stopPropagation(); AdminApp.openVillaSlotPicker({{ $s }})"
                                        style="background:#fff; border:none; border-radius:6px; padding:4px 7px; cursor:pointer; font-size:0.72rem; font-weight:600;">
                                    <i class="ph ph-pencil"></i>
                                </button>
                                <button type="button" onclick="event.stopPropagation(); AdminApp.clearVillaSlot({{ $s }})"
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
                    <input type="hidden" id="villa-image">
                </div>

                {{-- Video --}}
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">
                        Video villa
                        <span style="font-weight:400; font-size:0.75rem; text-transform:none; letter-spacing:0;"> — tuỳ chọn, hiển thị đầu tiên ở trang chi tiết (tự động phát)</span>
                    </label>

                    <div style="display:flex; gap:18px; margin-bottom:12px;">
                        <label style="display:flex; align-items:center; gap:6px; font-size:0.85rem; cursor:pointer;">
                            <input type="radio" name="villa-video-mode" id="villa-video-mode-upload" value="upload" checked onchange="AdminApp.switchVillaVideoMode('upload')">
                            Tải video lên
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; font-size:0.85rem; cursor:pointer;">
                            <input type="radio" name="villa-video-mode" id="villa-video-mode-youtube" value="youtube" onchange="AdminApp.switchVillaVideoMode('youtube')">
                            Nhúng YouTube
                        </label>
                    </div>

                    <div id="villa-video-upload-block">
                        <input type="file" id="villa-video-input" accept="video/mp4,video/quicktime,video/webm" style="display:none">
                        <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                            <div id="villa-video-slot"
                                 style="width:160px; height:90px; border:2px dashed var(--border-strong); border-radius:11px; overflow:hidden; position:relative; cursor:pointer; background:#FAFAFA; display:flex; align-items:center; justify-content:center; flex-shrink:0;"
                                 onclick="AdminApp.openVillaVideoPicker()">
                                <div class="slot-empty" id="villa-video-empty">
                                    <i class="ph ph-video-camera" style="font-size:1.6rem; color:#CBD5E1;"></i>
                                </div>
                                <video id="villa-video-preview" muted style="display:none; width:100%; height:100%; object-fit:cover;"></video>
                                <div id="villa-video-uploading" style="display:none; position:absolute; inset:0; background:rgba(255,255,255,.85); flex-direction:column; align-items:center; justify-content:center; gap:6px;">
                                    <div style="width:70%; height:3px; background:var(--border); border-radius:2px; overflow:hidden;">
                                        <div id="villa-video-bar" style="height:100%; background:var(--orange); width:0%; transition:width .3s;"></div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <button type="button" class="btn-view" onclick="AdminApp.openVillaVideoPicker()">
                                    <i class="ph ph-upload-simple"></i> Tải video lên
                                </button>
                                <button type="button" class="btn-view" style="margin-left:8px; color:#dc2626;" onclick="AdminApp.clearVillaVideo()">
                                    <i class="ph ph-trash"></i> Xóa
                                </button>
                                <p style="margin:7px 0 0; font-size:0.74rem; color:var(--text-muted);">MP4/MOV/WebM, tối đa 50MB.</p>
                            </div>
                        </div>
                    </div>

                    <div id="villa-video-youtube-block" style="display:none;">
                        <input type="text" id="villa-video-youtube-url" class="mf-input" placeholder="https://www.youtube.com/watch?v=...">
                        <p style="margin:7px 0 0; font-size:0.74rem; color:var(--text-muted);">Dán link YouTube, video sẽ tự động phát ở trang chi tiết.</p>
                    </div>

                    <input type="hidden" id="villa-video">
                </div>

                <div class="mf-group">
                    <label class="mf-label">Trạng thái</label>
                    <select id="villa-status" class="mf-select">
                        <option value="active">Đang hoạt động</option>
                        <option value="inactive">Tạm ẩn</option>
                    </select>
                </div>
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">Mô tả <span style="font-weight:400; font-size:0.75rem; text-transform:none; letter-spacing:0;">(tuỳ chọn, hiển thị ở trang chi tiết)</span></label>
                    <textarea id="villa-description" class="mf-input" rows="3" placeholder="Mô tả ngắn về villa..."></textarea>
                </div>
            </div>

            <div id="villa-form-error"
                 style="display:none; margin-top:14px; padding:11px 14px; background:#FEE2E2; border-radius:9px; color:#991B1B; font-weight:600; font-size:0.85rem;"></div>

            <div style="margin-top:24px; display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" id="villa-modal-cancel" class="btn-view">Hủy</button>
                <button type="submit" id="villa-submit-btn" class="btn-primary" style="padding:10px 22px;">Lưu villa</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══ MODAL: News ═══ --}}
<div id="news-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:640px; max-height:90vh; overflow-y:auto;">
        <button class="modal-close" id="news-modal-close"><i class="ph ph-x"></i></button>
        <h2 id="news-modal-title" style="font-size:1.4rem; font-weight:800; margin-bottom:24px; color:var(--text);">
            Thêm bài viết
        </h2>

        <form id="news-form">
            @csrf
            <input type="hidden" id="news-id">

            <div class="mf-grid-2">
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">Tiêu đề</label>
                    <input type="text" id="news-title" class="mf-input" placeholder="VD: LuxNest khai trương villa mới">
                </div>

                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">Slug (đường dẫn) <span style="font-weight:400;font-size:0.75rem;text-transform:none;letter-spacing:0;">— tự động tạo từ tiêu đề</span></label>
                    <input type="text" id="news-slug" class="mf-input" placeholder="vd: luxnest-khai-truong-villa-moi">
                </div>

                {{-- Image --}}
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">
                        Ảnh đại diện <span style="font-weight:400; font-size:0.75rem; text-transform:none; letter-spacing:0;">(tuỳ chọn)</span>
                    </label>
                    <input type="file" id="news-file-input" accept="image/*" style="display:none">
                    <div id="news-image-slot"
                         style="border:2px dashed var(--border-strong); border-radius:11px; overflow:hidden; aspect-ratio:16/9; max-width:280px; position:relative; cursor:pointer; background:#FAFAFA; display:flex; flex-direction:column; align-items:center; justify-content:center; transition:border-color .2s;"
                         onclick="AdminApp.openNewsImagePicker()"
                         ondragover="event.preventDefault(); this.style.borderColor='var(--orange)'"
                         ondragleave="this.style.borderColor='var(--border-strong)'"
                         ondrop="AdminApp.handleNewsImageDrop(event)">
                        <div id="news-image-empty" class="slot-empty">
                            <i class="ph ph-image" style="font-size:1.8rem; color:#CBD5E1;"></i>
                            <span style="font-size:0.72rem; color:#94A3B8; margin-top:5px; display:block;">Chọn ảnh</span>
                        </div>
                        <img id="news-image-preview" style="display:none; width:100%; height:100%; object-fit:cover;">
                        <div id="news-image-overlay" style="display:none; position:absolute; inset:0; background:rgba(0,0,0,.4); align-items:center; justify-content:center; gap:8px;">
                            <button type="button" onclick="event.stopPropagation(); AdminApp.openNewsImagePicker()"
                                    style="background:#fff; border:none; border-radius:7px; padding:5px 9px; cursor:pointer; font-size:0.78rem; font-weight:600;">
                                <i class="ph ph-pencil"></i>
                            </button>
                            <button type="button" onclick="event.stopPropagation(); AdminApp.clearNewsImage()"
                                    style="background:#EF4444; color:#fff; border:none; border-radius:7px; padding:5px 9px; cursor:pointer; font-size:0.78rem;">
                                <i class="ph ph-trash"></i>
                            </button>
                        </div>
                        <div id="news-image-uploading" style="display:none; position:absolute; inset:0; background:rgba(255,255,255,.85); flex-direction:column; align-items:center; justify-content:center; gap:7px;">
                            <div style="width:60%; height:4px; background:var(--border); border-radius:3px; overflow:hidden;">
                                <div id="news-image-bar" style="height:100%; background:var(--orange); width:0%; transition:width .3s;"></div>
                            </div>
                            <span style="font-size:0.72rem; color:var(--text-muted);">Đang tải...</span>
                        </div>
                    </div>
                    <input type="hidden" id="news-image">
                </div>

                <div class="mf-group">
                    <label class="mf-label">Tag <span style="font-weight:400; font-size:0.75rem; text-transform:none; letter-spacing:0;">(tuỳ chọn)</span></label>
                    <input type="text" id="news-tag" class="mf-input" placeholder="VD: Ưu đãi, Sự kiện...">
                </div>
                <div class="mf-group">
                    <label class="mf-label">Ngày đăng</label>
                    <input type="date" id="news-published-at" class="mf-input">
                </div>

                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">Mô tả ngắn</label>
                    <textarea id="news-excerpt" class="mf-input" rows="2" placeholder="Tóm tắt ngắn hiển thị ở trang Tin tức..."></textarea>
                </div>
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">Nội dung chi tiết <span style="font-weight:400; font-size:0.75rem; text-transform:none; letter-spacing:0;">(tuỳ chọn)</span></label>
                    <textarea id="news-content" class="mf-input" rows="5" placeholder="Nội dung đầy đủ của bài viết..."></textarea>
                </div>

                <div class="mf-group">
                    <label class="mf-label">Trạng thái</label>
                    <select id="news-status" class="mf-select">
                        <option value="active">Hiển thị</option>
                        <option value="draft">Bản nháp</option>
                    </select>
                </div>
            </div>

            <div id="news-form-error"
                 style="display:none; margin-top:14px; padding:11px 14px; background:#FEE2E2; border-radius:9px; color:#991B1B; font-weight:600; font-size:0.85rem;"></div>

            <div style="margin-top:24px; display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" id="news-modal-cancel" class="btn-view">Hủy</button>
                <button type="submit" id="news-submit-btn" class="btn-primary" style="padding:10px 22px;">Lưu bài viết</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══ MODAL: FAQ ═══ --}}
<div id="faq-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:560px; max-height:90vh; overflow-y:auto;">
        <button class="modal-close" id="faq-modal-close"><i class="ph ph-x"></i></button>
        <h2 id="faq-modal-title" style="font-size:1.4rem; font-weight:800; margin-bottom:24px; color:var(--text);">
            Thêm câu hỏi
        </h2>

        <form id="faq-form">
            @csrf
            <input type="hidden" id="faq-id">

            <div class="mf-grid-2">
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">Nhóm</label>
                    <input type="text" id="faq-group" class="mf-input" placeholder="VD: Đặt phòng & Thanh toán">
                </div>
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">Câu hỏi</label>
                    <input type="text" id="faq-question" class="mf-input" placeholder="VD: Tôi có thể đặt phòng như thế nào?">
                </div>
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">Câu trả lời</label>
                    <textarea id="faq-answer" class="mf-input" rows="4" placeholder="Nội dung trả lời..."></textarea>
                </div>
                <div class="mf-group">
                    <label class="mf-label">Thứ tự</label>
                    <input type="number" id="faq-sort-order" class="mf-input" min="0" step="1">
                </div>
            </div>

            <div id="faq-form-error"
                 style="display:none; margin-top:14px; padding:11px 14px; background:#FEE2E2; border-radius:9px; color:#991B1B; font-weight:600; font-size:0.85rem;"></div>

            <div style="margin-top:24px; display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" id="faq-modal-cancel" class="btn-view">Hủy</button>
                <button type="submit" id="faq-submit-btn" class="btn-primary" style="padding:10px 22px;">Lưu câu hỏi</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ═══════════════════ VOUCHER MODAL (admin only) ═══════════════════ --}}
@if(auth()->user()->isAdmin())
<div id="voucher-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:560px;">
        <button class="modal-close" id="voucher-modal-close"><i class="ph ph-x"></i></button>
        <h2 id="voucher-modal-title" style="font-size:1.4rem;font-weight:800;margin-bottom:24px;color:var(--text);">Tạo voucher</h2>

        <form id="voucher-form">
            @csrf
            <input type="hidden" id="voucher-id">

            <div class="mf-grid-2">
                <div class="mf-group">
                    <label class="mf-label">Mã voucher <span style="color:#dc2626;">*</span></label>
                    <input type="text" id="voucher-code" class="mf-input" placeholder="LUXNEST20" style="text-transform:uppercase;" oninput="this.value=this.value.toUpperCase()">
                </div>
                <div class="mf-group">
                    <label class="mf-label">Tên / Mô tả <span style="color:#dc2626;">*</span></label>
                    <input type="text" id="voucher-name" class="mf-input" placeholder="Voucher hè 2026">
                </div>

                <div class="mf-group">
                    <label class="mf-label">Loại giảm giá</label>
                    <select id="voucher-type" class="mf-select" onchange="AdminApp.onVoucherTypeChange()">
                        <option value="percent">Phần trăm (%)</option>
                        <option value="fixed">Số tiền cố định (VNĐ)</option>
                    </select>
                </div>
                <div class="mf-group">
                    <label class="mf-label" id="voucher-value-label">Giá trị giảm (%)</label>
                    <input type="number" id="voucher-value" class="mf-input" min="1" value="10" placeholder="10">
                </div>

                <div class="mf-group">
                    <label class="mf-label">Đơn hàng tối thiểu (VNĐ)</label>
                    <input type="number" id="voucher-min-order" class="mf-input" min="0" placeholder="Để trống = không giới hạn">
                </div>
                <div class="mf-group">
                    <label class="mf-label">Số lần dùng tối đa</label>
                    <input type="number" id="voucher-max-uses" class="mf-input" min="1" placeholder="Để trống = không giới hạn">
                </div>

                <div class="mf-group">
                    <label class="mf-label">Ngày hết hạn</label>
                    <input type="date" id="voucher-expires" class="mf-input">
                </div>
                <div class="mf-group" style="display:flex;align-items:center;gap:12px;padding-top:24px;">
                    <label class="toggle-switch">
                        <input type="checkbox" id="voucher-active" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span style="font-size:0.88rem;">Kích hoạt</span>
                </div>

                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">Ghi chú</label>
                    <textarea id="voucher-notes" class="mf-input" rows="2" placeholder="Nội dung nội bộ, khách không thấy"></textarea>
                </div>
            </div>

            <div id="voucher-form-error" style="display:none;margin-top:14px;padding:11px 14px;background:#FEE2E2;border-radius:9px;color:#991B1B;font-weight:600;font-size:0.85rem;"></div>

            <div style="margin-top:24px;display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" id="voucher-modal-cancel" class="btn-view">Hủy</button>
                <button type="submit" class="btn-primary" style="padding:10px 22px;">Lưu voucher</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ═══════════════════ CAMPAIGN MODAL (admin only) ═══════════════════ --}}
@if(auth()->user()->isAdmin())
<div id="campaign-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:680px;">
        <button class="modal-close" id="campaign-modal-close"><i class="ph ph-x"></i></button>
        <h2 id="campaign-modal-title" style="font-size:1.4rem;font-weight:800;margin-bottom:24px;color:var(--text);">Tạo chiến dịch email</h2>

        <form id="campaign-form">
            @csrf
            <input type="hidden" id="campaign-id">

            {{-- Section: Basic info --}}
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:20px;margin-bottom:16px;">
                <h3 style="margin:0 0 14px;font-size:0.78rem;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);">Nội dung email</h3>
                <div class="mf-group">
                    <label class="mf-label">Tên chiến dịch <span style="color:#dc2626;">*</span></label>
                    <input type="text" id="campaign-name" class="mf-input" placeholder="Remarketing hè 2026">
                </div>
                <div class="mf-group" style="margin-top:12px;">
                    <label class="mf-label">Tiêu đề email (Subject)</label>
                    <input type="text" id="campaign-subject" class="mf-input" placeholder="LuxNest nhớ bạn — Quà tặng đặc biệt 🎁">
                    <p style="margin:4px 0 0;font-size:0.72rem;color:var(--text-muted);">Để trống dùng tiêu đề mặc định.</p>
                </div>
                <div class="mf-group" style="margin-top:12px;">
                    <label class="mf-label">Lời mở đầu</label>
                    <textarea id="campaign-greeting" class="mf-input" rows="3" placeholder="LuxNest rất vui được đón tiếp bạn..."></textarea>
                </div>
                <div class="mf-group" style="margin-top:12px;">
                    <label class="mf-label">Nội dung phụ (dưới voucher)</label>
                    <textarea id="campaign-body" class="mf-input" rows="3" placeholder="⏰ Voucher có hiệu lực trong 30 ngày..."></textarea>
                </div>
            </div>

            {{-- Section: Voucher --}}
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:20px;margin-bottom:16px;">
                <h3 style="margin:0 0 14px;font-size:0.78rem;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);">Voucher</h3>
                <div class="mf-group">
                    <label class="mf-label">Loại voucher</label>
                    <select id="campaign-voucher-mode" class="mf-select" onchange="AdminApp.onCampaignVoucherModeChange()">
                        <option value="auto">Tự tạo mã riêng cho mỗi người</option>
                        <option value="fixed">Dùng voucher có sẵn (cùng 1 mã)</option>
                        <option value="none">Không kèm voucher</option>
                    </select>
                </div>
                <div id="campaign-voucher-auto-opts" style="margin-top:12px;">
                    <div class="mf-group">
                        <label class="mf-label">Giảm giá (%)</label>
                        <input type="number" id="campaign-discount" class="mf-input" min="1" max="100" value="10" style="max-width:140px;">
                    </div>
                </div>
                <div id="campaign-voucher-fixed-opts" style="display:none;margin-top:12px;">
                    <div class="mf-group">
                        <label class="mf-label">Chọn voucher</label>
                        <select id="campaign-voucher-id" class="mf-select">
                            <option value="">-- Chọn voucher --</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Section: Recipients --}}
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:20px;margin-bottom:16px;">
                <h3 style="margin:0 0 14px;font-size:0.78rem;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);">Người nhận</h3>

                {{-- Mode toggle --}}
                <div class="rcpt-mode-tabs" style="display:flex;gap:6px;margin-bottom:16px;">
                    <button type="button" class="rcpt-mode-btn active" data-mode="eligible" onclick="AdminApp.setCampaignRecipientMode('eligible')">
                        <i class="ph ph-funnel"></i> Đủ điều kiện
                    </button>
                    <button type="button" class="rcpt-mode-btn" data-mode="manual" onclick="AdminApp.setCampaignRecipientMode('manual')">
                        <i class="ph ph-pencil-line"></i> Nhập email
                    </button>
                    <button type="button" class="rcpt-mode-btn" data-mode="members" onclick="AdminApp.setCampaignRecipientMode('members')">
                        <i class="ph ph-users-three"></i> Chọn member
                    </button>
                </div>
                <input type="hidden" id="campaign-recipient-mode" value="eligible">

                {{-- Eligible sub-section --}}
                <div id="rcpt-eligible-section">
                    <p style="margin:0 0 12px;font-size:0.76rem;color:var(--text-muted);">Chỉ gửi đến khách thỏa tất cả điều kiện bên dưới. Mỗi email chỉ nhận 1 lần.</p>
                    <div style="background:var(--bg);border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:0.8rem;color:var(--text-muted);display:flex;align-items:center;gap:6px;">
                        <i class="ph ph-calendar-blank" style="color:var(--orange);"></i>
                        Gửi đến khách đã trả phòng trong khoảng
                        <strong id="cond-range-preview" style="color:var(--text);">30 – 60 ngày trước</strong>
                    </div>
                    <div class="mf-grid-2">
                        <div class="mf-group">
                            <label class="mf-label">Trả phòng ít nhất <span style="font-weight:400;">(ngày trước)</span></label>
                            <input type="number" id="cond-min-days" class="mf-input" min="0" value="30" placeholder="30"
                                   oninput="AdminApp.updateCondRangePreview()">
                        </div>
                        <div class="mf-group">
                            <label class="mf-label">Trả phòng không quá <span style="font-weight:400;">(ngày trước)</span></label>
                            <input type="number" id="cond-max-days" class="mf-input" min="1" value="60" placeholder="60"
                                   oninput="AdminApp.updateCondRangePreview()">
                        </div>
                        <div class="mf-group">
                            <label class="mf-label">Số booking tối thiểu</label>
                            <input type="number" id="cond-min-bookings" class="mf-input" min="1" value="1" placeholder="1">
                        </div>
                        <div class="mf-group">
                            <label class="mf-label">Chi tiêu tối thiểu (VNĐ)</label>
                            <input type="number" id="cond-min-spent" class="mf-input" min="0" placeholder="Để trống = không giới hạn">
                        </div>
                    </div>
                    <div id="campaign-eligible-preview" style="display:none;margin-top:12px;padding:10px 14px;background:var(--bg);border-radius:8px;font-size:0.82rem;color:var(--text-muted);">
                        <i class="ph ph-users"></i> <span id="campaign-eligible-count">–</span> khách đủ điều kiện
                        <button type="button" onclick="AdminApp.previewEligible()" style="margin-left:8px;font-size:0.78rem;color:var(--orange);background:none;border:none;cursor:pointer;text-decoration:underline;">Xem lại</button>
                    </div>
                    <button type="button" onclick="AdminApp.previewEligible()" style="margin-top:10px;font-size:0.8rem;color:var(--orange);background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:4px;">
                        <i class="ph ph-magnifying-glass"></i> Xem ai sẽ nhận email này
                    </button>
                </div>

                {{-- Manual email sub-section --}}
                <div id="rcpt-manual-section" style="display:none;">
                    <div class="mf-group">
                        <label class="mf-label">Danh sách email <span style="font-weight:400;color:var(--text-muted);">(mỗi dòng 1 email hoặc ngăn bằng dấu phẩy)</span></label>
                        <textarea id="campaign-manual-emails" class="mf-input" rows="6"
                            style="font-family:monospace;font-size:0.82rem;resize:vertical;"
                            placeholder="customer1@gmail.com&#10;customer2@yahoo.com&#10;customer3@gmail.com"
                            oninput="AdminApp.onManualEmailsChange()"></textarea>
                        <div id="manual-email-count" style="margin-top:6px;font-size:0.78rem;color:var(--text-muted);">0 email hợp lệ</div>
                    </div>
                </div>

                {{-- Members sub-section --}}
                <div id="rcpt-members-section" style="display:none;">
                    <input type="text" id="campaign-member-filter" class="mf-input" placeholder="Tìm theo tên hoặc email..."
                           style="margin-bottom:10px;" oninput="AdminApp.filterCampaignMembers()">
                    <div id="campaign-member-list"
                         style="max-height:240px;overflow-y:auto;border:1px solid var(--border);border-radius:8px;background:var(--bg);">
                        <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:0.82rem;">Đang tải...</div>
                    </div>
                    <div id="member-selected-count" style="margin-top:8px;font-size:0.78rem;color:var(--text-muted);">0 member được chọn</div>
                </div>
            </div>

            {{-- Section: Schedule --}}
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:20px;margin-bottom:16px;">
                <h3 style="margin:0 0 14px;font-size:0.78rem;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);">Thời gian gửi</h3>
                <div class="mf-group">
                    <label class="mf-label">Chế độ</label>
                    <select id="campaign-status" class="mf-select" onchange="AdminApp.onCampaignStatusChange()">
                        <option value="draft">Lưu nháp (chưa gửi)</option>
                        <option value="scheduled">Gửi 1 lần — đúng giờ đặt</option>
                        <option value="recurring">Lặp lại định kỳ — tự động gửi tiếp</option>
                    </select>
                </div>
                {{-- One-shot datetime --}}
                <div id="campaign-send-at-wrap" style="display:none;margin-top:12px;">
                    <div class="mf-group">
                        <label class="mf-label">Ngày & giờ gửi</label>
                        <input type="datetime-local" id="campaign-send-at" class="mf-input">
                    </div>
                </div>
                {{-- Recurring interval --}}
                <div id="campaign-repeat-wrap" style="display:none;margin-top:12px;">
                    <div class="mf-group">
                        <label class="mf-label">Lặp lại mỗi</label>
                        <select id="campaign-repeat-interval" class="mf-select">
                            <option value="daily">Ngày — kiểm tra & gửi mỗi ngày 1 lần</option>
                            <option value="weekly">Tuần — kiểm tra & gửi mỗi tuần 1 lần</option>
                        </select>
                        <p style="margin:6px 0 0;font-size:0.72rem;color:var(--text-muted);">
                            <i class="ph ph-info"></i>
                            Mỗi lần chạy chỉ gửi cho khách chưa nhận email này. Khách đã nhận sẽ không bị gửi lại.
                        </p>
                    </div>
                </div>
            </div>

            <div id="campaign-form-error" style="display:none;margin-top:4px;padding:11px 14px;background:#FEE2E2;border-radius:9px;color:#991B1B;font-weight:600;font-size:0.85rem;"></div>

            <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" id="campaign-modal-cancel" class="btn-view">Hủy</button>
                <button type="submit" class="btn-primary" style="padding:10px 22px;">Lưu chiến dịch</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ═══════════════════ GALLERY PHOTO MODAL (admin only) ═══════════════════ --}}
@if(auth()->user()->isAdmin())
<div id="gallery-photo-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:520px;">
        <button class="modal-close" id="gallery-photo-modal-close"><i class="ph ph-x"></i></button>
        <h2 id="gallery-photo-modal-title" style="font-size:1.4rem;font-weight:800;margin-bottom:24px;color:var(--text);">Thêm ảnh gallery</h2>

        <form id="gallery-photo-form">
            @csrf
            <input type="hidden" id="gallery-photo-id">
            <input type="hidden" id="gallery-photo-image-path">

            {{-- Image upload --}}
            <div class="mf-group" style="margin-bottom:18px;">
                <label class="mf-label">Ảnh <span style="color:#dc2626;">*</span></label>
                <div id="gallery-photo-slot" class="gallery-photo-upload-slot" onclick="AdminApp.triggerGalleryPhotoUpload()" ondragover="event.preventDefault()" ondrop="AdminApp.handleGalleryPhotoDrop(event)">
                    <div class="gallery-photo-upload-placeholder" id="gallery-photo-placeholder">
                        <i class="ph ph-image" style="font-size:2rem;color:var(--text-muted);"></i>
                        <span style="font-size:.82rem;color:var(--text-muted);">Kéo ảnh vào đây hoặc click để chọn</span>
                        <span style="font-size:.72rem;color:var(--text-muted);opacity:.6;margin-top:4px;">JPG / PNG / WebP · tối đa 4 MB</span>
                    </div>
                    <img id="gallery-photo-preview" src="" alt="" style="display:none;width:100%;border-radius:8px;object-fit:cover;max-height:220px;">
                    <button type="button" id="gallery-photo-clear" style="display:none;position:absolute;top:8px;right:8px;background:rgba(0,0,0,.55);border:none;border-radius:6px;color:#fff;padding:4px 8px;cursor:pointer;font-size:.75rem;" onclick="event.stopPropagation();AdminApp.clearGalleryPhoto()">✕ Xóa</button>
                </div>
                <input type="file" id="gallery-photo-file-input" accept="image/*" style="display:none;" onchange="AdminApp.handleGalleryPhotoFile(this.files[0])">
            </div>

            <div class="mf-grid-2">
                <div class="mf-group" style="grid-column:1/-1;">
                    <label class="mf-label">Chú thích</label>
                    <input type="text" id="gallery-photo-caption" class="mf-input" placeholder="Mô tả ngắn hiển thị khi hover (không bắt buộc)">
                </div>
                <div class="mf-group">
                    <label class="mf-label">Thứ tự sắp xếp</label>
                    <input type="number" id="gallery-photo-sort" class="mf-input" value="0" min="0">
                </div>
                <div class="mf-group" style="display:flex;align-items:center;gap:12px;padding-top:24px;">
                    <label class="toggle-switch">
                        <input type="checkbox" id="gallery-photo-active" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span style="font-size:.88rem;">Hiển thị</span>
                </div>
            </div>

            <div id="gallery-photo-form-error" style="display:none;margin-top:14px;padding:11px 14px;background:#FEE2E2;border-radius:9px;color:#991B1B;font-weight:600;font-size:.85rem;"></div>

            <div style="margin-top:24px;display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" id="gallery-photo-modal-cancel" class="btn-view">Hủy</button>
                <button type="submit" class="btn-primary" style="padding:10px 22px;">Lưu ảnh</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ═══════════════════ MEDIA LIBRARY MODAL (admin only) ═══════════════════ --}}
@if(auth()->user()->isAdmin())
<div id="media-library-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:880px; max-height:92vh; display:flex; flex-direction:column;">
        <button class="modal-close" id="media-lib-close"><i class="ph ph-x"></i></button>
        <h2 style="font-size:1.2rem;font-weight:800;color:var(--text);margin-bottom:14px;flex-shrink:0;">
            <i class="ph ph-images" style="color:var(--orange);"></i> Chọn Ảnh
        </h2>

        {{-- Main tabs: Thư viện / Upload mới --}}
        <div class="media-lib-main-tabs">
            <button class="media-lib-main-tab active" id="media-lib-tab-library" onclick="AdminApp.switchMediaLibTab('library')">
                <i class="ph ph-images"></i> Thư viện
            </button>
            <button class="media-lib-main-tab" id="media-lib-tab-upload" onclick="AdminApp.switchMediaLibTab('upload')">
                <i class="ph ph-upload-simple"></i> Upload mới
            </button>
        </div>

        {{-- Library section --}}
        <div id="media-lib-section-library" style="display:flex;flex-direction:column;flex:1;min-height:0;">
            <div style="flex-shrink:0;padding-top:12px;">
                <input type="text" id="media-lib-search" class="media-lib-search-input"
                       placeholder="Tìm kiếm tên file..." oninput="AdminApp.handleMediaLibSearch()">
            </div>
            <div class="media-lib-tabs" style="flex-shrink:0;margin-top:10px;">
                <button class="media-lib-tab active" data-folder="all"     onclick="AdminApp.loadMediaLibraryImages('all',     this, true)">Tất cả</button>
                <button class="media-lib-tab"         data-folder="rooms"   onclick="AdminApp.loadMediaLibraryImages('rooms',   this, true)">Phòng</button>
                <button class="media-lib-tab"         data-folder="villas"  onclick="AdminApp.loadMediaLibraryImages('villas',  this, true)">Villa</button>
                <button class="media-lib-tab"         data-folder="gallery" onclick="AdminApp.loadMediaLibraryImages('gallery', this, true)">Gallery</button>
                <button class="media-lib-tab"         data-folder="news"    onclick="AdminApp.loadMediaLibraryImages('news',    this, true)">Tin tức</button>
            </div>
            <div id="media-lib-grid" class="media-lib-grid" style="flex:1;overflow-y:auto;margin-top:12px;align-content:start;">
                <div class="table-empty-state"><i class="ph ph-images"></i><span>Đang tải...</span></div>
            </div>
            <div id="media-lib-sentinel" style="height:1px;flex-shrink:0;"></div>
        </div>

        {{-- Upload section --}}
        <div id="media-lib-section-upload" style="display:none;flex:1;overflow-y:auto;padding-top:16px;">
            <div class="media-lib-upload-zone" id="media-lib-upload-zone"
                 ondragover="event.preventDefault(); this.classList.add('dragging')"
                 ondragleave="this.classList.remove('dragging')"
                 ondrop="AdminApp.handleMediaLibDrop(event)">
                <i class="ph ph-upload-simple" style="font-size:2.5rem;color:var(--text-muted);"></i>
                <p style="margin:10px 0 4px;font-weight:700;font-size:1rem;color:var(--text);">Kéo ảnh vào đây</p>
                <p style="font-size:.82rem;color:var(--text-muted);margin:0 0 16px;">hoặc</p>
                <label class="btn-primary" style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;padding:8px 20px;">
                    <i class="ph ph-folder-open"></i> Chọn file
                    <input type="file" id="media-lib-file-input" accept="image/*" style="display:none;"
                           onchange="AdminApp.handleMediaLibFileSelect(this.files[0]); this.value=''">
                </label>
                <p style="font-size:.72rem;color:var(--text-muted);margin-top:14px;opacity:.7;">JPG · PNG · WebP · GIF · tối đa 8 MB</p>
            </div>
            <div id="media-lib-upload-progress" style="display:none;margin-top:20px;text-align:center;">
                <div style="height:4px;background:var(--border);border-radius:3px;overflow:hidden;max-width:320px;margin:0 auto 10px;">
                    <div id="media-lib-upload-bar" style="height:100%;background:var(--orange);width:0%;transition:width .3s;"></div>
                </div>
                <span style="font-size:.8rem;color:var(--text-muted);">Đang tải lên...</span>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="{{ asset_v('assets/js/admin-app.js') }}"></script>
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
        overview:    'Bảng Điều Khiển',
        bookings:    'Quản Lý Booking',
        rooms:       'Quản Lý Phòng',
        members:     'Quản Lý Thành Viên',
        settings:    'Thông Tin Doanh Nghiệp',
        news:        'Quản Lý Tin Tức',
        faqs:        'Câu Hỏi Thường Gặp',
        pagecontent: 'Nội Dung Trang'
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
