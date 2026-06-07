<footer
    class="relative mt-auto border-t border-zinc-200/80 bg-white/80 py-12 backdrop-blur-xl dark:border-zinc-800 dark:bg-zinc-950/80 md:py-16">
    <x-container class="space-y-10">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
            <div class="space-y-4 max-w-xl">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <x-logo class="h-8 w-auto" />
                    <span class="text-base font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">{{ setting('site.title', 'Komunitas Historia Indonesia') }}</span>
                </a>
                <p class="max-w-lg text-sm leading-7 text-zinc-500 dark:text-zinc-400">
                    {{ setting('header_tagline', 'Scholarly preservation for modern minds. Clean navigation, focused content, and a lighter canvas for the community.') }}
                </p>
                <div class="text-xs text-zinc-500 space-y-2 mt-4">
                    @if($address = setting('footer_address'))
                        <p class="flex items-start gap-2">
                            <x-phosphor-map-pin class="w-4 h-4 shrink-0 mt-0.5 text-zinc-400" />
                            <span>{{ $address }}</span>
                        </p>
                    @endif
                    @if($phone = setting('footer_contact_phone'))
                        <p class="flex items-center gap-2">
                            <x-phosphor-phone class="w-4 h-4 shrink-0 text-zinc-400" />
                            <span>{{ $phone }}</span>
                        </p>
                    @endif
                    @if($email = setting('footer_contact_email'))
                        <p class="flex items-center gap-2">
                            <x-phosphor-envelope-simple class="w-4 h-4 shrink-0 text-zinc-400" />
                            <span>{{ $email }}</span>
                        </p>
                    @endif
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">Explore
                    </p>
                    <div class="grid gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <a href="{{ route('historia-news') }}" wire:navigate
                            class="hover:text-red-700 dark:hover:text-blue-300">Historialita</a>
                        <a href="{{ route('aspirasi') }}" wire:navigate
                            class="hover:text-red-700 dark:hover:text-blue-300">Aspirasi</a>
                        <a href="{{ route('library') }}" wire:navigate
                            class="hover:text-red-700 dark:hover:text-blue-300">E-Library</a>
                        <a href="{{ route('events') }}" wire:navigate
                            class="hover:text-red-700 dark:hover:text-blue-300">Events</a>
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">More
                    </p>
                    <div class="grid gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <a href="{{ route('organization') }}" wire:navigate
                            class="hover:text-red-700 dark:hover:text-blue-300">Organization</a>
                        <a href="{{ route('privacy-policy') }}" wire:navigate
                            class="hover:text-red-700 dark:hover:text-blue-300">Privacy Policy</a>
                        <a href="{{ route('terms-of-service') }}" wire:navigate
                            class="hover:text-red-700 dark:hover:text-blue-300">Terms of Service</a>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="flex flex-col gap-4 border-t border-zinc-200/80 pt-6 text-sm text-zinc-500 dark:border-zinc-800 dark:text-zinc-400 sm:flex-row sm:items-center sm:justify-between">
            <p>{!! setting_sanitized('footer_copyright', '&copy; 2003-' . date('Y') . ' Komunitas Historia Indonesia.') !!}</p>

            <div class="flex items-center gap-4 flex-wrap">
                @php
                    $socialLinks = setting_social_links();
                @endphp
                @foreach ($socialLinks as $social)
                    @if (!empty($social['url']))
                        @php
                            $name = $social['name'] ?? '';
                            $url = $social['url'];
                            $logo = $social['logo'] ?? '';
                            $iconName = strtolower($name);
                        @endphp
                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" title="{{ $name }}"
                            class="hover:text-red-700 dark:hover:text-blue-300 flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400 transition">
                            @if (!empty($logo))
                                <img src="{{ Storage::url($logo) }}" class="w-5 h-5 object-contain" alt="{{ $name }}" />
                            @else
                                @if($iconName === 'facebook')
                                    <x-phosphor-facebook-logo class="w-5 h-5" />
                                @elseif($iconName === 'instagram')
                                    <x-phosphor-instagram-logo class="w-5 h-5" />
                                @elseif($iconName === 'twitter' || $iconName === 'x')
                                    <x-phosphor-twitter-logo class="w-5 h-5" />
                                @elseif($iconName === 'youtube')
                                    <x-phosphor-youtube-logo class="w-5 h-5" />
                                @else
                                    <x-phosphor-share-network class="w-5 h-5" />
                                @endif
                            @endif
                            <span>{{ $name }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </x-container>
</footer>
