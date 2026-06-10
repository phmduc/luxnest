@extends('layouts.auth')
@section('title', 'Đăng nhập - LuxNest')

@section('content')
<div class="auth-header">
    <h1>Chào mừng trở lại</h1>
    <p>Đăng nhập vào tài khoản LuxNest của bạn</p>
</div>

@if($errors->any())
<div class="auth-alert auth-alert--error">
    <i class="ph ph-warning-circle"></i>
    {{ $errors->first() }}
</div>
@endif

@if(session('status'))
<div class="auth-alert auth-alert--success">
    <i class="ph ph-check-circle"></i>
    {{ session('status') }}
</div>
@endif

<form method="POST" action="{{ route('login.post') }}" class="auth-form">
    @csrf

    <div class="auth-field">
        <label for="email">Email</label>
        <div class="auth-input-wrap">
            <i class="ph ph-envelope"></i>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   placeholder="your@email.com" required autofocus>
        </div>
    </div>

    <div class="auth-field">
        <label for="password">
            Mật khẩu
        </label>
        <div class="auth-input-wrap">
            <i class="ph ph-lock"></i>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>
    </div>

    <div class="auth-options">
        <label class="auth-remember">
            <input type="checkbox" name="remember">
            <span>Ghi nhớ đăng nhập</span>
        </label>
    </div>

    <button type="submit" class="auth-btn">
        <i class="ph ph-sign-in"></i>
        Đăng nhập
    </button>
</form>

<div class="auth-footer-links">
    <p>Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a></p>
</div>
@endsection
