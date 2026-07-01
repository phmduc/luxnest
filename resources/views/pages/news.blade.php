@extends('layouts.app')

@section('title', 'Tin tức - ' . $settings->site_name)

@push('styles')
<link rel="stylesheet" href="{{ asset_v('assets/css/pages.css') }}">
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
        @if($articles->isEmpty())
        <div style="text-align:center; padding: 40px 0;">
            <p style="color:#64748b;">Chưa có bài viết nào. Vui lòng quay lại sau.</p>
        </div>
        @else
        <div class="pg-news-grid">
            @foreach($articles as $article)
            <a href="{{ $article->slug ? route('news.show', $article->slug) : '#' }}"
               style="text-decoration:none; color:inherit;">
                <article class="pg-news-card">
                    @if($article->image)
                    <div class="pg-news-card__thumb pg-news-card__thumb--img" style="background-image:url('{{ $article->image }}');"></div>
                    @else
                    <div class="pg-news-card__thumb">📰</div>
                    @endif
                    <div class="pg-news-card__body">
                        @if($article->tag)
                        <span class="pg-news-card__tag">{{ $article->tag }}</span>
                        @endif
                        <h3 class="pg-news-card__title">{{ $article->title }}</h3>
                        <p class="pg-news-card__excerpt">{{ $article->excerpt }}</p>
                        <span class="pg-news-card__date">{{ $article->published_at?->format('d/m/Y') }}</span>
                    </div>
                </article>
            </a>
            @endforeach
        </div>

        <div style="text-align:center; margin-top: 40px;">
            <p style="color:#64748b;">Thêm nhiều bài viết hấp dẫn sẽ sớm được cập nhật.</p>
        </div>
        @endif
    </div>
</section>

@endsection
