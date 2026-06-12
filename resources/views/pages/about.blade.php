@extends('layouts.app')

@section('title', 'Giới thiệu - ' . $settings->site_name)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages.css') }}">
@endpush

@section('content')

<div class="pg-hero">
    <div class="lx-container">
        <h1>{{ $content['hero_title'] }}</h1>
        <p>{{ $content['hero_subtitle'] }}</p>
    </div>
</div>

<section class="pg-section pg-section--center">
    <div class="lx-container">
        <h2>{{ $content['story_title'] }}</h2>
        <p>{{ $content['story_paragraph_1'] }}</p>
        <p>{{ $content['story_paragraph_2'] }}</p>
    </div>
</section>

<section class="pg-section pg-section--gray">
    <div class="lx-container">
        <h2 style="text-align:center;">{{ $content['why_title'] }}</h2>
        <div class="pg-grid pg-grid--3">
            @for ($i = 1; $i <= 3; $i++)
            <div class="pg-card">
                <div class="pg-card__icon">{{ $content["why_card_{$i}_icon"] }}</div>
                <div class="pg-card__title">{{ $content["why_card_{$i}_title"] }}</div>
                <p class="pg-card__text">{{ $content["why_card_{$i}_text"] }}</p>
            </div>
            @endfor
        </div>
    </div>
</section>

<section class="pg-section">
    <div class="lx-container">
        <div class="pg-grid">
            @for ($i = 1; $i <= 4; $i++)
            <div class="pg-stat">
                <div class="pg-stat__number">{{ $content["stat_{$i}_number"] }}</div>
                <div class="pg-stat__label">{{ $content["stat_{$i}_label"] }}</div>
            </div>
            @endfor
        </div>
    </div>
</section>

<section class="pg-section pg-section--gray pg-section--center">
    <div class="lx-container">
        <h2>{{ $content['cta_title'] }}</h2>
        <p>{{ $content['cta_text'] }}</p>
        <a href="{{ route('rooms.index') }}" class="pg-btn">{{ $content['cta_button'] }}</a>
    </div>
</section>

@endsection
