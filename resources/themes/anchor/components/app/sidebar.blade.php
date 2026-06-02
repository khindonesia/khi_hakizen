<div x-data="{ sidebarOpen: false }" @open-sidebar.window="sidebarOpen = true" x-init="$watch('sidebarOpen', function(value) {
    if (value) { document.body.classList.add('overflow-hidden'); } else { document.body.classList.remove('overflow-hidden'); }
});"
    class="relative z-50 w-screen md:w-auto" x-cloak>
    <div x-show="sidebarOpen" @click="sidebarOpen=false"
        class="fixed inset-0 z-50 bg-zinc-950/20 backdrop-blur-sm dark:bg-white/10"></div>

    <aside :class="{ '-translate-x-full': !sidebarOpen }"
        class="fixed left-0 top-0 z-50 flex h-dvh w-[18rem] -translate-x-full overflow-hidden border-r border-zinc-200/80 bg-white/90 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur-xl transition-transform duration-200 ease-out dark:border-zinc-800 dark:bg-zinc-950/90 lg:translate-x-0 @if (config('wave.dev_bar')) {{ 'pb-10' }} @endif">
        <div class="flex h-full w-full flex-col justify-between gap-6 overflow-y-auto p-4">
            <div class="space-y-4">
                <div class="flex items-center justify-between lg:hidden">
                    <a href="/"
                        class="inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white px-3 py-2 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <x-logo class="h-6 w-auto" />
                    </a>
                    <button x-on:click="sidebarOpen=false"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-500 shadow-sm transition hover:border-red-200 hover:text-red-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div
                    class="rounded-[28px] border border-zinc-200/80 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-3">
                        <a href="/"
                            class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-700 shadow-sm dark:bg-red-500/10 dark:text-blue-200">
                            <x-logo class="h-6 w-auto" />
                        </a>
                        <div class="min-w-0">
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">
                                Workspace</p>
                            <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">Komunitas
                                Historia Indonesia</p>
                        </div>
                    </div>

                    <div
                        class="mt-4 rounded-2xl border border-zinc-200/80 bg-zinc-50 px-3 py-2 dark:border-zinc-800 dark:bg-zinc-950">
                        <div class="flex items-center gap-2 text-zinc-400">
                            <x-phosphor-magnifying-glass class="h-4 w-4" />
                            <input type="text" placeholder="Search"
                                class="w-full border-0 bg-transparent p-0 text-sm text-zinc-700 placeholder:text-zinc-400 focus:ring-0 dark:text-zinc-200">
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <p
                        class="px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">
                        Core</p>
                    <div class="space-y-1">
                        <x-app.sidebar-link href="{{ route('home') }}" icon="phosphor-arrow-left" :active="false">
                            Kembali ke Website
                        </x-app.sidebar-link>
                        <x-app.sidebar-link href="{{ route('dashboard') }}" icon="phosphor-house" :active="Request::is('dashboard')">
                            Dashboard
                        </x-app.sidebar-link>
                        <x-app.sidebar-link href="{{ route('orders') }}" icon="phosphor-shopping-bag" :active="Request::is('orders') || Request::is('orders/*')">
                            Orders
                        </x-app.sidebar-link>
                        <x-app.sidebar-link href="/dashboard/aspirasi" icon="phosphor-pencil-line" :active="Request::is('dashboard/aspirasi') || Request::is('dashboard/aspirasi/*')">
                            Aspirasi
                        </x-app.sidebar-link>
                        <x-app.sidebar-link href="/dashboard/historialita" icon="phosphor-pencil-line"
                            :active="Request::is('dashboard/historialita') || Request::is('dashboard/historialita/*')">
                            Historialita
                        </x-app.sidebar-link>
                    </div>
                </div>

                <div class="space-y-2">
                    <p
                        class="px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">
                        Account</p>
                    <div class="space-y-1">
                        <x-app.sidebar-link href="/dashboard/events" icon="phosphor-calendar" :active="Request::is('dashboard/events') || Request::is('dashboard/events/*')">
                            Events
                        </x-app.sidebar-link>
                        <x-app.sidebar-link href="/user-addresses" icon="phosphor-map-pin" :active="Request::is('user-addresses*')">
                            Addresses
                        </x-app.sidebar-link>
                        <x-app.sidebar-link href="{{ route('settings.profile') }}" icon="phosphor-gear"
                            :active="Request::is('settings/*')">
                            Settings
                        </x-app.sidebar-link>
                        <x-app.sidebar-link href="{{ route('notifications') }}" icon="phosphor-bell" :active="Request::is('notifications')">
                            Notifications
                        </x-app.sidebar-link>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div
                    class="rounded-[28px] border border-zinc-200/80 bg-gradient-to-br from-blue-50 to-white p-4 shadow-sm dark:border-zinc-800 dark:from-blue-500/10 dark:to-zinc-900">
                    <div class="flex items-center gap-3">
                        <div
                            class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-red-700 shadow-sm dark:bg-zinc-950 dark:text-blue-200">
                            <x-phosphor-sparkle class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Workspace ready</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Clean canvas for content and orders.</p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-[28px] border border-zinc-200/80 bg-white p-2 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <x-app.user-menu />
                </div>
            </div>
        </div>
    </aside>
</div>
