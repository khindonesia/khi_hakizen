<x-card class="mx-auto w-full max-w-5xl">
    <div class="flex flex-col gap-4 border-b border-zinc-200/80 pb-5 dark:border-zinc-800 sm:flex-row sm:items-end sm:justify-between">
        <div class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-red-700 dark:text-blue-300">Settings</p>
            <h2 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">{{ $title ?? '' }}</h2>
            <p class="max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">{{ $description ?? '' }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[14rem_minmax(0,1fr)]">
        <aside class="rounded-3xl border border-zinc-200/80 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-950">
            <nav class="space-y-1">
                <p class="px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Account</p>
                <x-settings-sidebar-link :href="route('settings.profile')" icon="phosphor-user-circle-duotone">Profile</x-settings-sidebar-link>
                <x-settings-sidebar-link :href="route('settings.security')" icon="phosphor-lock-duotone">Security</x-settings-sidebar-link>
                {{-- <x-settings-sidebar-link :href="route('settings.api')" icon="phosphor-code-duotone">API Keys</x-settings-sidebar-link> --}}
            </nav>
        </aside>

        <div class="min-w-0">
            {{ $slot }}
        </div>
    </div>
</x-card>
