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

<body x-data
    class="min-h-screen font-sans text-zinc-900 @if (config('wave.dev_bar')) {{ 'pb-10' }} @endif bg-transparent">
    <x-app.sidebar />

    <div class="relative min-h-screen lg:pl-72">
        <header class="sticky top-0 z-30 border-b border-zinc-200/80 bg-white/80 px-4 py-3 backdrop-blur-xl dark:border-zinc-800 dark:bg-zinc-950/80 lg:hidden">
            <div class="flex items-center justify-between gap-3">
                <button x-on:click="window.dispatchEvent(new CustomEvent('open-sidebar'))"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-700 shadow-sm transition hover:border-red-200 hover:text-red-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
                    </svg>
                </button>

                <div class="flex flex-1 items-center justify-center">
                    <a href="/" class="inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white px-4 py-2 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <x-logo class="h-6 w-auto" />
                    </a>
                </div>

                <x-app.user-menu position="top" />
            </div>
        </header>

        <main class="relative px-4 py-4 sm:px-6 sm:py-6 lg:px-8 lg:py-8">
            <div class="mx-auto w-full max-w-[1600px]">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewire('notifications')
    @include('theme::partials.footer-scripts')
    {{ $javascript ?? '' }}
</body>

</html>
