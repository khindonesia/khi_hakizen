@php
    $settings = setting()->all(); // Ambil semua setting (misalnya dari DB cache)
    $seo = isset($seo) ? (object) (is_array($seo) ? $seo : get_object_vars($seo)) : null;
@endphp

<title>
    {{ $seo->title ?? $settings['site.title'] ?? 'Laravel Wave' }}
</title>

<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="url" content="{{ url('/') }}">

{{-- Load Favicon --}}
<x-favicon />

{{-- Auto-generate meta from all site.* settings --}}
@foreach ($settings as $key => $value)
    @php
        // Handle site.* keys like site.description, site.keywords, etc
        if (Str::startsWith($key, 'site.') && !in_array($key, ['site.title'])) {
            $metaName = str_replace('site.', '', $key);
            echo '<meta name="' . e($metaName) . '" content="' . e($value) . '">' . PHP_EOL;
        }

        // If you have special keys like og:title etc (optional)
        if (Str::startsWith($key, 'meta.')) {
            $metaKey = explode('.', $key, 2)[1];
            echo '<meta name="' . e($metaKey) . '" content="' . e($value) . '">' . PHP_EOL;
        }
    @endphp
@endforeach

{{-- SEO override via controller --}}
@if ($seo?->description)
    <meta name="description" content="{{ $seo->description }}">
@endif

@if ($seo?->keywords)
    <meta name="keywords" content="{{ $seo->keywords }}">
@endif

{{-- Open Graph --}}
@if ($seo?->title && $seo?->description && $seo?->image)
    <meta property="og:title" content="{{ $seo->title }}">
    <meta property="og:description" content="{{ $seo->description }}">
    <meta property="og:image" content="{{ $seo->image }}">
    <meta property="og:url" content="{{ Request::url() }}">
    <meta property="og:type" content="{{ $seo->type ?? 'article' }}">
    <meta property="og:site_name" content="{{ $settings['site.title'] ?? 'Laravel Wave' }}">

    <meta itemprop="name" content="{{ $seo->title }}">
    <meta itemprop="description" content="{{ $seo->description }}">
    <meta itemprop="image" content="{{ $seo->image }}">

    @if ($seo?->image_w && $seo?->image_h)
        <meta property="og:image:width" content="{{ $seo->image_w }}">
        <meta property="og:image:height" content="{{ $seo->image_h }}">
    @endif
@endif

{{-- Default Robots --}}
<meta name="robots" content="index,follow">
<meta name="googlebot" content="index,follow">

{{-- Styles and Scripts --}}
@filamentStyles
@livewireStyles
@vite([
    'resources/themes/anchor/assets/css/app.css',
    'resources/themes/anchor/assets/js/app.js',
])
