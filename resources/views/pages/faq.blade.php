@extends('layouts.app')

@section('title', 'Câu hỏi thường gặp - ' . $settings->site_name)

@push('styles')
<link rel="stylesheet" href="{{ asset_v('assets/css/pages.css') }}">
@endpush

@section('content')

<div class="pg-hero">
    <div class="lx-container">
        <h1>Câu hỏi thường gặp</h1>
        <p>Giải đáp những thắc mắc phổ biến về đặt phòng, thanh toán và các dịch vụ của {{ $settings->site_name }}.</p>
    </div>
</div>

<section class="pg-section">
    <div class="lx-container" style="max-width: 760px;">
        @foreach($faqs as $group)
        <div class="pg-faq-group">
            <h3>{{ $group['group'] }}</h3>
            @foreach($group['items'] as $item)
            <details class="pg-faq-item">
                <summary>{{ $item['q'] }}</summary>
                <div class="pg-faq-answer">{{ $item['a'] }}</div>
            </details>
            @endforeach
        </div>
        @endforeach

        <div style="text-align:center; margin-top: 32px;">
            <p style="margin-bottom: 16px;">Không tìm thấy câu trả lời bạn cần?</p>
            <a href="{{ route('contact.index') }}" class="pg-btn">Liên hệ với chúng tôi</a>
        </div>
    </div>
</section>

@endsection
