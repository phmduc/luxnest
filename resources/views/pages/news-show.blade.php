@extends('layouts.app')

@section('title', $article->title . ' - ' . $settings->site_name)

@php
    $ogDesc  = $article->excerpt ? mb_substr(strip_tags($article->excerpt), 0, 160) : mb_substr(strip_tags($article->content ?? ''), 0, 160);
    $ogImage = $article->image ?: ($settings->logo ?: asset('promo-banner.png'));
@endphp
@section('meta_description', $ogDesc)
@section('og_title',       $article->title . ' - ' . $settings->site_name)
@section('og_description', $ogDesc)
@section('og_image',       $ogImage)

@push('styles')
<link rel="stylesheet" href="{{ asset_v('assets/css/pages.css') }}">
@endpush

@section('content')

{{-- BREADCRUMB --}}
<div style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:12px 0;">
    <div class="lx-container" style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:#64748b; flex-wrap:wrap;">
        <a href="{{ url('/') }}" style="color:#64748b; text-decoration:none;">Trang chủ</a>
        <i class="fa-solid fa-chevron-right" style="font-size:0.6rem;"></i>
        <a href="{{ route('news.index') }}" style="color:#64748b; text-decoration:none;">Tin tức</a>
        <i class="fa-solid fa-chevron-right" style="font-size:0.6rem;"></i>
        <span style="color:#0f172a; font-weight:500;">{{ $article->title }}</span>
    </div>
</div>

{{-- ARTICLE --}}
<div class="lx-container" style="max-width:800px; padding-top:40px; padding-bottom:64px;">

    {{-- Meta --}}
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px; flex-wrap:wrap;">
        @if($article->tag)
            <span class="pg-news-card__tag">{{ $article->tag }}</span>
        @endif
        @if($article->published_at)
            <span style="font-size:0.85rem; color:#94a3b8;">
                <i class="fa-regular fa-calendar" style="margin-right:4px;"></i>
                {{ $article->published_at->format('d/m/Y') }}
            </span>
        @endif
    </div>

    {{-- Title --}}
    <h1 style="font-size:2rem; font-weight:800; color:#0f172a; line-height:1.3; margin-bottom:20px;">
        {{ $article->title }}
    </h1>

    @if($article->excerpt)
    <p style="font-size:1.1rem; color:#475569; line-height:1.7; margin-bottom:28px; padding-bottom:28px; border-bottom:1px solid #e2e8f0;">
        {{ $article->excerpt }}
    </p>
    @endif

    {{-- Hero image --}}
    @if($article->image)
    <div style="margin-bottom:32px; border-radius:16px; overflow:hidden; aspect-ratio:16/9;">
        <img src="{{ $article->image }}" alt="{{ $article->title }}"
             style="width:100%; height:100%; object-fit:cover; display:block;">
    </div>
    @endif

    {{-- Content --}}
    @if($article->content)
    <div class="news-article-body">
        {!! nl2br(e($article->content)) !!}
    </div>
    @else
    <div style="text-align:center; padding:40px 0; color:#94a3b8;">
        <i class="ph ph-newspaper" style="font-size:3rem; display:block; margin-bottom:12px;"></i>
        Nội dung đang được cập nhật.
    </div>
    @endif

</div>

{{-- RELATED ARTICLES --}}
@if($related->isNotEmpty())
<section class="pg-section pg-section--gray">
    <div class="lx-container">
        <h2 style="font-size:1.3rem; font-weight:800; color:#0f172a; margin-bottom:24px;">Bài viết liên quan</h2>
        <div class="pg-news-grid">
            @foreach($related as $item)
            <a href="{{ $item->slug ? route('news.show', $item->slug) : '#' }}"
               style="text-decoration:none; color:inherit;">
                <article class="pg-news-card" style="transition:transform .2s,box-shadow .2s;"
                         onmouseenter="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.1)'"
                         onmouseleave="this.style.transform='';this.style.boxShadow=''">
                    @if($item->image)
                        <div class="pg-news-card__thumb pg-news-card__thumb--img"
                             style="background-image:url('{{ $item->image }}');"></div>
                    @else
                        <div class="pg-news-card__thumb">📰</div>
                    @endif
                    <div class="pg-news-card__body">
                        @if($item->tag)
                            <span class="pg-news-card__tag">{{ $item->tag }}</span>
                        @endif
                        <h3 class="pg-news-card__title">{{ $item->title }}</h3>
                        <p class="pg-news-card__excerpt">{{ $item->excerpt }}</p>
                        <span class="pg-news-card__date">{{ $item->published_at?->format('d/m/Y') }}</span>
                    </div>
                </article>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection
