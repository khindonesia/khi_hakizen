@php
    if (isset($seo)) {
        $seo = is_array($seo) ? (object) $seo : $seo;
    }
@endphp

<title>
    @if (isset($seo->title))
        {{ $seo->title }}
    @else
        {{ setting('site.title', 'Laravel Wave') . ' - ' . setting('site.description', 'The Software as a Service Starter Kit built with Laravel') }}
    @endif
</title>

<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="url" content="{{ url('/') }}">

{{-- Favicon --}}
<x-favicon />

{{-- SEO Meta --}}
@if (isset($seo->description))
    <meta name="description" content="{{ $seo->description }}">
@endif

@if (setting('site.keywords'))
    <meta name="keywords" content="{{ setting('site.keywords') }}">
@endif

<meta name="robots" content="index,follow">
<meta name="googlebot" content="index,follow">


{{-- Open Graph / Social Sharing --}}
@if (isset($seo->title) && isset($seo->description) && isset($seo->image))
    <meta property="og:title" content="{{ $seo->title }}">
    <meta property="og:description" content="{{ $seo->description }}">
    <meta property="og:image" content="{{ $seo->image }}">
    <meta property="og:url" content="{{ Request::url() }}">
    <meta property="og:type" content="{{ $seo->type ?? 'article' }}">
    <meta property="og:site_name" content="{{ setting('site.title') }}">

    <meta itemprop="name" content="{{ $seo->title }}">
    <meta itemprop="description" content="{{ $seo->description }}">
    <meta itemprop="image" content="{{ $seo->image }}">

    @if (isset($seo->image_w) && isset($seo->image_h))
        <meta property="og:image:width" content="{{ $seo->image_w }}">
        <meta property="og:image:height" content="{{ $seo->image_h }}">
    @endif
@endif

{{-- Styles & Scripts --}}
@filamentStyles
@livewireStyles
@vite([
    'resources/themes/anchor/assets/css/app.css',
    'resources/themes/anchor/assets/js/app.js',
])

{{-- Google Fonts --}}
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Inter+Tight:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<style>
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
</style>
