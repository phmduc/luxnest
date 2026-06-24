@extends('layouts.admin')
@section('title', 'Tài khoản của tôi - LuxNest')

@section('content')
<div class="manager-dashboard-container">

    {{-- ===== SIDEBAR ===== --}}
    <aside class="manager-sidebar" style="--sidebar-accent: #FF5B00;">
        <div class="sidebar-header">
            <div style="width:48px; height:48px; border-radius:50%; background:rgba(255,91,0,0.15); color:var(--lux-orange); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.3rem; margin-bottom:16px;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <h3 style="font-size:1.1rem; line-height:1.3;">{{ $user->name }}</h3>
            <p style="margin:4px 0 0; font-size:0.8rem; color:rgba(255,255,255,0.4);">{{ $user->email }}</p>
        </div>
        <nav class="sidebar-nav">
            <a href="#" class="nav-item active" data-tab="bookings">
                <i class="ph ph-calendar-check"></i> Đặt phòng của tôi
            </a>
            <a href="#" class="nav-item" data-tab="profile">
                <i class="ph ph-user-circle"></i> Hồ sơ cá nhân
            </a>

            <div class="sidebar-footer" style="margin-top: auto;">
                <a href="{{ url('/') }}" class="nav-item">
                    <i class="ph ph-house"></i> Trang Chủ
                </a>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="nav-item logout"
                            style="width:100%; background:none; border:none; cursor:pointer; font-family:inherit; font-size:1.05rem; font-weight:500; text-align:left; margin-top:0; color:rgba(255,255,255,0.4); padding:18px 20px; display:flex; align-items:center; gap:14px; border-radius:14px; margin-bottom:8px; transition:all 0.3s;">
                        <i class="ph ph-sign-out"></i> Đăng Xuất
                    </button>
                </form>
            </div>
        </nav>
    </aside>

    {{-- ===== MAIN CONTENT ===== --}}
    <main class="manager-content">

        {{-- ===== TAB: MY BOOKINGS ===== --}}
        <section id="tab-bookings" class="dashboard-tab active">
            <header class="content-header" style="flex-wrap:wrap; gap:20px; align-items:center; display:flex;">
                <div>
                    <h2 style="margin:0 0 8px;">Đặt phòng của tôi</h2>
                    <p style="margin:0; color:var(--lux-gray);">Lịch sử booking theo email <strong>{{ $user->email }}</strong></p>
                </div>
                <div style="display:flex; gap:15px; flex:1; min-width:250px; justify-content:flex-end;">
                    <div class="filter-wrapper">
                        <input type="month" id="my-booking-month" value="{{ date('Y-m') }}" class="month-input">
                    </div>
                </div>
            </header>

            <div class="data-table-container">
                <table class="manager-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Phòng</th>
                            <th>Lịch trình</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody id="my-bookings-body">
                        <tr>
                            <td colspan="5" style="text-align:center; color:var(--lux-gray); padding:40px;">
                                <div class="spinner" style="margin:0 auto 16px;"></div>
                                Đang tải dữ liệu...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ===== TAB: PROFILE ===== --}}
        <section id="tab-profile" class="dashboard-tab">
            <header class="content-header">
                <h2>Hồ sơ cá nhân</h2>
                <p>Cập nhật thông tin tài khoản của bạn.</p>
            </header>

            @if(session('profile_success'))
            <div style="background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; padding:16px 20px; border-radius:14px; margin-bottom:28px; display:flex; align-items:center; gap:10px; font-weight:600;">
                <i class="ph ph-check-circle" style="font-size:20px;"></i>
                {{ session('profile_success') }}
            </div>
            @endif

            <div class="recent-card" style="max-width:560px;">
                <form method="POST" action="{{ route('member.profile.update') }}">
                    @csrf

                    <div class="mf-group" style="margin-bottom:20px;">
                        <label class="mf-label">Họ và tên</label>
                        <div class="auth-input-wrap">
                            <i class="ph ph-user"></i>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                   class="mf-input" required>
                        </div>
                        @error('name') <p style="color:#e11d48; font-size:0.85rem; margin:6px 0 0;">{{ $message }}</p> @enderror
                    </div>

                    <div class="mf-group" style="margin-bottom:20px;">
                        <label class="mf-label">Email</label>
                        <div class="auth-input-wrap">
                            <i class="ph ph-envelope"></i>
                            <input type="email" value="{{ $user->email }}" disabled
                                   class="mf-input" style="opacity:0.6; cursor:not-allowed;">
                        </div>
                        <p style="font-size:0.82rem; color:var(--lux-gray); margin:6px 0 0;">Email không thể thay đổi.</p>
                    </div>

                    <div style="padding:20px; background:var(--lux-bg); border-radius:16px; margin-bottom:24px;">
                        <h4 style="margin:0 0 20px; font-size:0.95rem; color:var(--lux-gray); text-transform:uppercase; letter-spacing:1px;">Đổi mật khẩu</h4>

                        <div class="mf-group" style="margin-bottom:16px;">
                            <label class="mf-label">Mật khẩu hiện tại</label>
                            <div class="auth-input-wrap">
                                <i class="ph ph-lock"></i>
                                <input type="password" name="current_password" class="mf-input" placeholder="Nhập mật khẩu hiện tại">
                            </div>
                            @error('current_password') <p style="color:#e11d48; font-size:0.85rem; margin:6px 0 0;">{{ $message }}</p> @enderror
                        </div>

                        <div class="mf-group" style="margin-bottom:16px;">
                            <label class="mf-label">Mật khẩu mới</label>
                            <div class="auth-input-wrap">
                                <i class="ph ph-lock-key"></i>
                                <input type="password" name="password" class="mf-input" placeholder="Tối thiểu 8 ký tự">
                            </div>
                        </div>

                        <div class="mf-group">
                            <label class="mf-label">Xác nhận mật khẩu mới</label>
                            <div class="auth-input-wrap">
                                <i class="ph ph-lock-key"></i>
                                <input type="password" name="password_confirmation" class="mf-input" placeholder="Nhập lại mật khẩu mới">
                            </div>
                            @error('password') <p style="color:#e11d48; font-size:0.85rem; margin:6px 0 0;">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <button type="submit"
                            style="padding:14px 28px; border-radius:14px; background:var(--lux-orange); color:#fff; border:none; font-family:'Outfit',sans-serif; font-size:1rem; font-weight:700; cursor:pointer; box-shadow:0 4px 15px rgba(255,91,0,0.3); transition:all 0.2s; display:inline-flex; align-items:center; gap:8px;">
                        <i class="ph ph-floppy-disk"></i> Lưu thay đổi
                    </button>
                </form>
            </div>
        </section>

    </main>
</div>

@push('styles')
<style>
.mf-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--lux-gray);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
}
.mf-group { margin-bottom: 4px; }
.mf-input {
    width: 100%;
    padding: 13px 18px;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    font-family: 'Outfit', sans-serif;
    font-size: 1rem;
    color: var(--lux-dark);
    outline: none;
    transition: border-color 0.3s;
    background: #fff;
    box-sizing: border-box;
}
.mf-input:focus { border-color: var(--lux-orange); box-shadow: 0 0 0 3px rgba(255,91,0,0.1); }
.auth-input-wrap {
    position: relative;
}
.auth-input-wrap i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--lux-gray);
    font-size: 1.1rem;
    pointer-events: none;
}
.auth-input-wrap input {
    width: 100%;
    padding: 13px 18px 13px 46px;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    font-family: 'Outfit', sans-serif;
    font-size: 1rem;
    color: var(--lux-dark);
    outline: none;
    transition: border-color 0.3s;
    box-sizing: border-box;
    background: #fff;
}
.auth-input-wrap input:focus { border-color: var(--lux-orange); box-shadow: 0 0 0 3px rgba(255,91,0,0.1); }
</style>
@endpush

@push('scripts')
<script src="{{ asset_v('assets/js/member-app.js') }}"></script>
@endpush
@endsection
