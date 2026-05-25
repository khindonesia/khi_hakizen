<footer class="relative mt-auto border-t border-zinc-200/80 bg-white/80 py-12 backdrop-blur-xl dark:border-zinc-800 dark:bg-zinc-950/80 md:py-16">
    <x-container class="space-y-10">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
            <div class="space-y-4 max-w-xl">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <x-logo class="h-8 w-auto" />
                    <span class="text-base font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">Komunitas Historia Indonesia</span>
                </a>
                <p class="max-w-lg text-sm leading-7 text-zinc-500 dark:text-zinc-400">
                    Scholarly preservation for modern minds. Clean navigation, focused content, and a lighter canvas for the community.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">Explore</p>
                    <div class="grid gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <a href="{{ route('historia-news') }}" wire:navigate class="hover:text-red-700 dark:hover:text-blue-300">Historialita</a>
                        <a href="{{ route('aspirasi') }}" wire:navigate class="hover:text-red-700 dark:hover:text-blue-300">Aspirasi</a>
                        <a href="{{ route('library') }}" wire:navigate class="hover:text-red-700 dark:hover:text-blue-300">E-Library</a>
                        <a href="{{ route('events') }}" wire:navigate class="hover:text-red-700 dark:hover:text-blue-300">Events</a>
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">More</p>
                    <div class="grid gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <a href="{{ route('organization') }}" wire:navigate class="hover:text-red-700 dark:hover:text-blue-300">Organization</a>
                        <a href="{{ route('privacy-policy') }}" wire:navigate class="hover:text-red-700 dark:hover:text-blue-300">Privacy Policy</a>
                        <a href="{{ route('terms-of-service') }}" wire:navigate class="hover:text-red-700 dark:hover:text-blue-300">Terms of Service</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4 border-t border-zinc-200/80 pt-6 text-sm text-zinc-500 dark:border-zinc-800 dark:text-zinc-400 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; 2003-{{ date('Y') }} Komunitas Historia Indonesia.</p>
            <div class="flex items-center gap-4">
                <a href="https://www.facebook.com/komunitashistoria/?locale=id_ID" target="_blank" class="hover:text-red-700 dark:hover:text-blue-300">Facebook</a>
                <a href="https://www.instagram.com/komunitashistoria/" target="_blank" class="hover:text-red-700 dark:hover:text-blue-300">Instagram</a>
                <a href="https://x.com/IndoHistoria" target="_blank" class="hover:text-red-700 dark:hover:text-blue-300">X</a>
            </div>
        </div>
    </x-container>
</footer>
