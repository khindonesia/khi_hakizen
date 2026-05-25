@props([
    'position' => 'bottom',
])

<div x-data="{ dropdownOpen: false }" class="relative w-full" x-cloak>
    <button @click="dropdownOpen=!dropdownOpen"
        class="flex w-full items-center justify-between gap-3 rounded-2xl border border-zinc-200 bg-white px-3 py-2.5 text-left text-sm text-zinc-700 shadow-sm transition hover:border-red-200 hover:text-zinc-900 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:border-blue-500/40">
        <span class="flex min-w-0 items-center gap-3">
            <x-avatar src="{{ auth()->user()->avatar() }}" alt="{{ auth()->user()->name }} photo" size="sm" />
            <span class="min-w-0">
                <span class="block truncate font-semibold text-zinc-900 dark:text-zinc-100">{{ Auth::user()->name }}</span>
                <span class="block truncate text-xs text-zinc-500 dark:text-zinc-400">{{ Auth::user()->email }}</span>
            </span>
        </span>
        <svg :class="{ 'rotate-180': dropdownOpen }" class="h-4 w-4 flex-shrink-0 transition duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    <div wire:ignore x-show="dropdownOpen" @click.away="dropdownOpen=false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        @class([
            'absolute z-50 w-full pt-3',
            'bottom-full mb-2 origin-bottom' => $position == 'bottom',
            'top-full mt-2 origin-top' => $position != 'bottom',
        ])
        x-cloak>
        <div class="overflow-hidden rounded-3xl border border-zinc-200/80 bg-white shadow-[0_20px_60px_rgba(15,23,42,0.12)] dark:border-zinc-800 dark:bg-zinc-950">
            <div class="border-b border-zinc-100 px-4 py-3 dark:border-zinc-800">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Signed in as</p>
                <p class="mt-1 truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ auth()->user()->email }}</p>
            </div>

            <div class="space-y-1 p-2">
                <x-app.sidebar-link :hideUntilGroupHover="false" href="{{ route('notifications') }}" icon="phosphor-bell-duotone" active="false">
                    Notifications
                </x-app.sidebar-link>
                <x-app.sidebar-link :hideUntilGroupHover="false" href="{{ route('settings.profile') }}" icon="phosphor-gear-duotone" active="false">
                    Settings
                </x-app.sidebar-link>
                @if(auth()->user()->isAdmin())
                    <x-app.sidebar-link :hideUntilGroupHover="false" :ajax="false" href="/admin" icon="phosphor-crown-duotone" active="false">
                        View Admin
                    </x-app.sidebar-link>
                @endif
            </div>

            <div class="border-t border-zinc-100 p-2 dark:border-zinc-800">
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button onclick="event.preventDefault(); this.closest('form').submit();"
                        class="flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-left text-sm text-zinc-700 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-900">
                        <x-phosphor-sign-out-duotone class="h-5 w-5 flex-shrink-0" />
                        <span>Log out</span>
                    </button>
                </form>
                @impersonating
                    <x-app.sidebar-link href="{{ route('impersonate.leave') }}" icon="phosphor-user-circle-duotone" active="false">
                        Leave impersonation
                    </x-app.sidebar-link>
                @endImpersonating
            </div>
        </div>
    </div>
</div>
