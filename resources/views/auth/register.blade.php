@extends('layouts.auth')
@section('title', 'Đăng ký - LuxNest')

@section('content')
<div class="auth-header">
    <h1>Tạo tài khoản</h1>
    <p>Tham gia LuxNest để đặt phòng dễ dàng hơn</p>
</div>

@if($errors->any())
<div class="auth-alert auth-alert--error">
    <i class="ph ph-warning-circle"></i>
    {{ $errors->first() }}
</div>
@endif

<form method="POST" action="{{ route('register.post') }}" class="auth-form">
    @csrf

    <div class="auth-field">
        <label for="name">Họ và tên</label>
        <div class="auth-input-wrap">
            <i class="ph ph-user"></i>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                   placeholder="Nguyễn Văn A" required autofocus>
        </div>
    </div>

    <div class="auth-field">
        <label for="email">Email</label>
        <div class="auth-input-wrap">
            <i class="ph ph-envelope"></i>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   placeholder="your@email.com" required>
        </div>
    </div>

    <div class="auth-field">
        <label for="password">Mật khẩu</label>
        <div class="auth-input-wrap">
            <i class="ph ph-lock"></i>
            <input type="password" id="password" name="password" placeholder="Tối thiểu 8 ký tự" required>
        </div>
    </div>

    <div class="auth-field">
        <label for="password_confirmation">Xác nhận mật khẩu</label>
        <div class="auth-input-wrap">
            <i class="ph ph-lock-key"></i>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   placeholder="Nhập lại mật khẩu" required>
        </div>
    </div>

    <button type="submit" class="auth-btn">
        <i class="ph ph-user-plus"></i>
        Tạo tài khoản
    </button>
</form>

<div class="auth-footer-links">
    <p>Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p>
</div>
@endsection
