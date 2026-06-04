<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    @include('theme::partials.head', ['seo' => $seo ?? null])
    @stack('styles')
    <script>
        if (typeof(Storage) !== "undefined") {
            if (localStorage.getItem('theme') && localStorage.getItem('theme') == 'dark') {
                document.documentElement.classList.add('dark');
            }
        }
    </script>
</head>

@php
    $showHeader = $showHeader ?? true;
    $showFooter = $showFooter ?? true;
    $showDecor = $showDecor ?? true;
@endphp

<body x-data
    class="min-h-screen font-sans overflow-x-hidden text-zinc-900 @if ($bodyClass ?? false) {{ $bodyClass }} @endif"
    x-cloak>
    @if ($showDecor)
        <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
            <div class="absolute left-[-10%] top-[-8rem] h-96 w-96 rounded-full bg-red-100/70 blur-3xl"></div>
            <div class="absolute right-[-8%] top-24 h-80 w-80 rounded-full bg-slate-200/50 blur-3xl"></div>
        </div>
    @endif

    @if ($showHeader)
        <x-marketing.elements.header />
    @endif

    <main class="relative z-10 flex-1 overflow-x-hidden">
        {{ $slot }}
    </main>

    <x-app.live-chat />

    @livewire('notifications')
    @if ($showFooter)
        @include('theme::partials.footer')
    @endif
    @include('theme::partials.footer-scripts')
    {{ $javascript ?? '' }}

    @stack('javascript')
</body>

</html>
