@extends('layouts.app')

@section('title', 'Tin tức - ' . $settings->site_name)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages.css') }}">
@endpush

@section('content')

<div class="pg-hero">
    <div class="lx-container">
        <h1>Tin tức & Cẩm nang du lịch</h1>
        <p>Cập nhật tin tức, ưu đãi và những gợi ý du lịch hữu ích từ {{ $settings->site_name }}.</p>
    </div>
</div>

<section class="pg-section">
    <div class="lx-container">
        <div class="pg-news-grid">
            @foreach($articles as $article)
            <article class="pg-news-card">
                <div class="pg-news-card__thumb">📰</div>
                <div class="pg-news-card__body">
                    <span class="pg-news-card__tag">{{ $article['tag'] }}</span>
                    <h3 class="pg-news-card__title">{{ $article['title'] }}</h3>
                    <p class="pg-news-card__excerpt">{{ $article['excerpt'] }}</p>
                    <span class="pg-news-card__date">{{ $article['date'] }}</span>
                </div>
            </article>
            @endforeach
        </div>

        <div style="text-align:center; margin-top: 40px;">
            <p style="color:#64748b;">Thêm nhiều bài viết hấp dẫn sẽ sớm được cập nhật.</p>
        </div>
    </div>
</section>

@endsection
