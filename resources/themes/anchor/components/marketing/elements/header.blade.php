@php
    $cartItemCount = auth()->check()
        ? \App\Models\Cart::query()
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->with(['items'])
            ->first()?->items->sum('quantity') ?? 0
        : 0;
@endphp

<header x-data="{ mobileMenuOpen: false, scrolled: false, cartCount: @js($cartItemCount) }"
    x-init="
        $watch('mobileMenuOpen', value => document.body.classList.toggle('overflow-hidden', value));
        const updateScroll = () => { scrolled = window.scrollY > 8; };
        updateScroll();
        window.addEventListener('scroll', updateScroll);
        window.addEventListener('cart-updated', (event) => {
            cartCount = Math.max(0, cartCount + Number(event.detail?.quantity ?? 1));
        });
    "
    :class="scrolled ? 'border-b border-zinc-200/80 bg-white/90 shadow-sm backdrop-blur-xl' : 'border-transparent bg-white/70 backdrop-blur-xl'"
    class="sticky top-0 z-50 w-full transition-all duration-300">
    <div class="mx-auto flex h-20 w-full max-w-[1600px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-3 rounded-full border border-zinc-200/80 bg-white px-3 py-2 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
            <x-logo class="h-7 w-auto" />
            <span class="hidden text-sm font-semibold text-zinc-900 sm:block">Komunitas Historia Indonesia</span>
        </a>

        <nav class="hidden items-center gap-1 rounded-full border border-zinc-200/80 bg-white/90 px-2 py-2 shadow-sm lg:flex dark:border-zinc-800 dark:bg-zinc-950/90">
            @php
                $links = [
                    ['label' => 'Home', 'route' => 'home'],
                    ['label' => 'Historialita', 'route' => 'historia-news'],
                    ['label' => 'Aspirasi', 'route' => 'aspirasi'],
                    ['label' => 'Library', 'route' => 'library'],
                    ['label' => 'Merchandise', 'route' => 'merchandise'],
                    ['label' => 'Events', 'route' => 'events'],
                    ['label' => 'Organization', 'url' => url('organization')],
                ];
            @endphp

            @foreach ($links as $link)
                @php
                    $isActive = isset($link['route'])
                        ? request()->routeIs($link['route']) || request()->routeIs($link['route'] . '.*')
                        : request()->is(trim(parse_url($link['url'], PHP_URL_PATH), '/') . '*');
                    $href = $link['route'] ?? $link['url'];
                @endphp
                <a href="{{ isset($link['route']) ? route($link['route']) : $link['url'] }}" wire:navigate
                    @if ($link['label'] === 'Historialita') dusk="nav-historialita" @endif
                    class="@if($isActive){{ 'bg-red-50 text-red-700 shadow-sm dark:bg-red-500/10 dark:text-red-100' }}@else{{ 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-zinc-100' }}@endif rounded-full px-3 py-2 text-sm font-medium transition">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-2 lg:flex">
            <a href="{{ route('shopping-cart') }}" wire:navigate class="relative inline-flex h-11 w-11 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-500 shadow-sm transition hover:border-red-200 hover:text-red-700 dark:border-zinc-800 dark:bg-zinc-950">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
                <span
                    id="cart-count-badge"
                    x-show="cartCount > 0"
                    x-cloak
                    x-text="cartCount"
                    class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1.5 text-[11px] font-semibold leading-5 text-white shadow-sm"
                >{{ $cartItemCount }}</span>
            </a>

            @auth
                <a href="{{ route('dashboard') }}" class="rounded-full border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-700 shadow-sm transition hover:border-red-200 hover:text-red-700 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-200">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="rounded-full border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-700 shadow-sm transition hover:border-red-200 hover:text-red-700 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-200">
                    Login
                </a>
            @endauth

            @guest
                <a href="{{ route('join') }}" wire:navigate
                   class="rounded-full bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-500">
                    Join
                </a>
            @endguest
        </div>

        <button @click="mobileMenuOpen = !mobileMenuOpen" type="button"
            class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-700 shadow-sm transition hover:border-red-200 hover:text-red-700 lg:hidden dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-200">
            <svg x-show="!mobileMenuOpen" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
            <svg x-show="mobileMenuOpen" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" x-cloak>
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <div x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-zinc-950/40 backdrop-blur-sm lg:hidden"
        @click="mobileMenuOpen = false"
        x-cloak>
    </div>

    <div x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed right-0 top-0 z-50 flex h-screen w-[88vw] max-w-sm flex-col justify-between border-l border-zinc-200/80 bg-white p-5 shadow-[0_20px_60px_rgba(15,23,42,0.18)] dark:border-zinc-800 dark:bg-zinc-950 lg:hidden"
        x-cloak>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Menu</span>
                <button @click="mobileMenuOpen = false" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 text-zinc-500 transition hover:border-red-200 hover:text-red-700 dark:border-zinc-800 dark:text-zinc-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <nav class="grid gap-2">
                @foreach ($links as $link)
                    <a href="{{ isset($link['route']) ? route($link['route']) : $link['url'] }}" wire:navigate @click="mobileMenuOpen = false"
                        @if ($link['label'] === 'Historialita') dusk="nav-historialita-mobile" @endif
                        class="rounded-2xl border border-zinc-200/80 px-4 py-3 text-sm font-medium text-zinc-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700 dark:border-zinc-800 dark:text-zinc-200 dark:hover:border-red-500/30 dark:hover:bg-red-500/10">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="space-y-3 border-t border-zinc-200/80 pt-4 dark:border-zinc-800">
            @auth
                <a href="{{ route('dashboard') }}" @click="mobileMenuOpen = false"
                   class="flex w-full items-center justify-center rounded-full border border-zinc-200 px-4 py-3 text-sm font-semibold text-zinc-700 transition hover:border-red-200 hover:text-red-700 dark:border-zinc-800 dark:text-zinc-200">
                    Dashboard
                </a>
            @endauth
            @guest
                <a href="{{ route('join') }}" wire:navigate @click="mobileMenuOpen = false"
                   class="flex w-full items-center justify-center rounded-full bg-red-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-500">
                    Join
                </a>
            @endguest
        </div>
    </div>
</header>
