<header x-data="{
    mobileMenuOpen: false,
    scrolled: false,
    showOverlay: false,
    topOffset: '5',
    evaluateScrollPosition() {
        if (window.pageYOffset > this.topOffset) {
            this.scrolled = true;
        } else {
            this.scrolled = false;
        }
    }
}" x-init="window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        mobileMenuOpen = false;
    }
});
$watch('mobileMenuOpen', function(value) {
    if (value) { document.body.classList.add('overflow-hidden'); } else { document.body.classList.remove('overflow-hidden'); }
});
evaluateScrollPosition();
window.addEventListener('scroll', function() {
    evaluateScrollPosition();
})"
    :class="{
        'border-gray-200/60 bg-white/90 border-b backdrop-blur-lg': scrolled,
        'border-transparent border-b bg-transparent translate-y-0':
            !scrolled
    }"
    class="box-content sticky top-0 z-50 w-full h-20">
    <div x-show="showOverlay" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" class="absolute inset-0 w-full h-screen pt-24" x-cloak>
        <div class="w-screen h-full bg-black/50"></div>
    </div>
    <div
        :class="{
            'bg-[#c6303e] text-white w-full text-center {{ request()->routeIs('home') ? 'hidden sm:block' : 'hidden' }}':
                !scrolled,
            'hidden': scrolled
        }">
        <span class="text-sm font-medium">Selamat Datang di Komunitas Historia Indonesia!</span>
    </div>
    <x-container>
        <div class="z-30 flex items-center justify-between h-20 md:space-x-8">
            <div class="z-20 flex items-center justify-between w-full md:w-auto">
                <div class="relative z-20 inline-flex">
                    <a href="{{ route('home') }}"
                        class="flex items-center justify-center space-x-3 font-bold text-zinc-900">
                        <x-logo class="w-auto h-8 md:h-9"></x-logo>
                        <span class="block sm:hidden">Komunitas Historia Indonesia</span>
                    </a>
                </div>
                <div class="flex justify-end flex-grow md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button"
                        class="inline-flex items-center justify-center p-2 transition duration-150 ease-in-out rounded-full text-zinc-400 hover:text-zinc-500 hover:bg-zinc-100">
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16">
                            </path>
                        </svg>
                        <svg x-show="mobileMenuOpen" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <nav :class="{
                'hidden': !
                    mobileMenuOpen,
                'block md:relative absolute top-0 left-0 md:w-auto w-screen md:h-auto h-screen pointer-events-none md:z-10 z-10': mobileMenuOpen
            }"
                class="h-full md:flex">
                <ul :class="{
                    'hidden md:flex': !
                        mobileMenuOpen,
                    'flex flex-col absolute md:relative md:w-auto w-screen h-full md:h-full md:overflow-auto overflow-scroll md:pt-0 mt-16 md:pb-0 pb-48 bg-white md:bg-transparent': mobileMenuOpen
                }"
                    id="menu"
                    class="flex items-stretch justify-start flex-1 w-full h-full ml-0 border-t border-gray-100 pointer-events-auto md:items-center md:justify-center gap-x-8 md:w-auto md:border-t-0 md:flex-row">
                    <li class="flex-shrink-0 h-16 border-b border-gray-100 md:border-b-0 md:h-full">
                        <a href="{{ route('home') }}" wire:navigate
                            class="flex items-center h-full text-sm transition duration-300 md:px-0 px-7 hover:bg-gray-100 md:hover:bg-transparent hover:text-gray-900 {{ request()->routeIs('home') ? 'font-semibold' : 'font-medium' }}">
                            Home
                        </a>
                    </li>
                    <li class="flex-shrink-0 h-16 border-b border-gray-100 md:border-b-0 md:h-full">
                        <a href="{{ route('blog') }}" wire:navigate
                            class="flex items-center h-full text-sm font-medium text-gray-700 transition duration-300 md:px-0 px-7 hover:bg-gray-100 md:hover:bg-transparent hover:text-gray-900 {{ request()->routeIs('blog') ? 'font-semibold' : 'font-medium' }}">
                            Historia News
                        </a>
                    </li>
                    <li class="flex-shrink-0 h-16 border-b border-gray-100 md:border-b-0 md:h-full">
                        <a href="{{ route('opini') }}" wire:navigate
                            class="flex items-center h-full text-sm font-medium text-gray-700 transition duration-300 md:px-0 px-7 hover:bg-gray-100 md:hover:bg-transparent hover:text-gray-900 {{ request()->routeIs('opini') ? 'font-semibold' : 'font-medium' }}">
                            Opini
                        </a>
                    </li>
                    <li class="flex-shrink-0 h-16 border-b border-gray-100 md:border-b-0 md:h-full">
                        <a href="{{ route('library') }}" wire:navigate
                            class="flex items-center h-full text-sm font-medium text-gray-700 transition duration-300 md:px-0 px-7 hover:bg-gray-100 md:hover:bg-transparent hover:text-gray-900">
                            E-Library
                        </a>
                    </li>
                    <li class="flex-shrink-0 h-16 border-b border-gray-100 md:border-b-0 md:h-full">
                        <a href="{{ route('blog') }}" wire:navigate
                            class="flex items-center h-full text-sm font-medium text-gray-700 transition duration-300 md:px-0 px-7 hover:bg-gray-100 md:hover:bg-transparent hover:text-gray-900">
                            Merchandise
                        </a>
                    </li>
                    <li class="flex-shrink-0 h-16 border-b border-gray-100 md:border-b-0 md:h-full">
                        <a href="{{ route('events') }}" wire:navigate
                            class="flex items-center h-full text-sm font-medium text-gray-700 transition duration-300 md:px-0 px-7 hover:bg-gray-100 md:hover:bg-transparent hover:text-gray-900">
                            Events
                        </a>
                    </li>
                    <li class="flex-shrink-0 h-16 border-b border-gray-100 md:border-b-0 md:h-full">
                        <a href="{{ route('blog') }}" wire:navigate
                            class="flex items-center h-full text-sm font-medium text-gray-700 transition duration-300 md:px-0 px-7 hover:bg-gray-100 md:hover:bg-transparent hover:text-gray-900">
                            Organization
                        </a>
                    </li>
                    <li class="flex-shrink-0 h-16 border-b border-gray-100 md:border-b-0 md:h-full">
                        <a href="{{ route('blog') }}" wire:navigate
                            class="flex items-center h-full text-sm font-medium text-gray-700 transition duration-300 md:px-0 px-7 hover:bg-gray-100 md:hover:bg-transparent hover:text-gray-900">
                            Collaboration
                        </a>
                    </li>

                    {{-- <li x-data="{ open: false }" @mouseenter="showOverlay=true" @mouseleave="showOverlay=false"
                        class="z-30 flex flex-col items-start h-auto border-b border-gray-100 md:h-full md:border-b-0 group md:flex-row md:items-center">
                        <a href="#_" x-on:click="open=!open"
                            class="flex items-center w-full h-16 gap-1 text-sm font-semibold text-gray-700 transition duration-300 hover:bg-gray-100 md:hover:bg-transparent px-7 md:h-full md:px-0 md:w-auto hover:text-gray-900">
                            <span class="">Resources</span>
                            <svg :class="{ 'group-hover:-rotate-180': !mobileMenuOpen, '-rotate-180': mobileMenuOpen && open }"
                                class="w-5 h-5 transition-all duration-300 ease-out" fill="currentColor"
                                viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" class=""></path>
                            </svg>
                        </a>
                        <div :class="{
                            'hidden md:block opacity-0 invisible md:absolute': !
                                open,
                            'md:invisible md:opacity-0 md:hidden md:absolute': open
                        }"
                            class="top-0 left-0 w-screen space-y-3 transition-transform duration-300 ease-out bg-white border-t border-b border-gray-100 md:shadow-md md:-translate-y-2 md:mt-24 md:block md:group-hover:block md:group-hover:visible md:group-hover:opacity-100 md:group-hover:translate-y-0"
                            x-cloak>
                            <ul class="flex flex-col justify-between mx-auto max-w-7xl md:flex-row md:px-12">
                                <div
                                    class="flex flex-col w-full border-l border-r divide-x md:flex-row divide-zinc-100 border-zinc-100">
                                    <div class="w-auto divide-y divide-zinc-100">
                                        <a href="#_"
                                            onclick="event.preventDefault(); new FilamentNotification().title('Modify this button in your theme folder').icon('heroicon-o-pencil-square').iconColor('info').send()"
                                            class="block text-sm p-7 hover:bg-neutral-100 group">
                                            <span class="block mb-1 font-medium text-black">Authentication</span>
                                            <span class="block font-light leading-5 opacity-50">Configure the login,
                                                register, and forgot password for your app</span>
                                        </a>
                                        <a href="#_"
                                            onclick="event.preventDefault(); new FilamentNotification().title('Modify this button in your theme folder').icon('heroicon-o-pencil-square').iconColor('info').send()"
                                            class="block text-sm p-7 hover:bg-neutral-100 group">
                                            <span class="block mb-1 font-medium text-black">Roles and
                                                Permissions</span>
                                            <span class="block leading-5 opacity-50">We utilize the bullet-proof Spatie
                                                Permissions package</span>
                                        </a>
                                    </div>
                                    <div class="w-auto divide-y divide-zinc-100">
                                        <a href="#_"
                                            onclick="event.preventDefault(); new FilamentNotification().title('Modify this button in your theme folder').icon('heroicon-o-pencil-square').iconColor('info').send()"
                                            class="block text-sm p-7 hover:bg-neutral-100">
                                            <span class="block mb-1 font-medium text-black">Posts and Pages</span>
                                            <span class="block font-light leading-5 opacity-50">Easily write blog
                                                articles and create pages for your application</span>
                                        </a>
                                        <a href="#_"
                                            onclick="event.preventDefault(); new FilamentNotification().title('Modify this button in your theme folder').icon('heroicon-o-pencil-square').iconColor('info').send()"
                                            class="block text-sm p-7 hover:bg-neutral-100">
                                            <span class="block mb-1 font-medium text-black">Themes</span>
                                            <span class="block leading-5 opacity-50">Kick-start your app with a
                                                pre-built theme or create your own</span>
                                        </a>
                                    </div>
                                    <div class="w-auto divide-y divide-zinc-100">
                                        <a href="#_"
                                            onclick="event.preventDefault(); new FilamentNotification().title('Modify this button in your theme folder').icon('heroicon-o-pencil-square').iconColor('info').send()"
                                            class="block text-sm p-7 hover:bg-neutral-100">
                                            <span class="block mb-1 font-medium text-black">Settings and More</span>
                                            <span class="block leading-5 opacity-50">Easily create and update app
                                                settings. And so much more</span>
                                        </a>
                                        <a href="#_"
                                            onclick="event.preventDefault(); new FilamentNotification().title('Modify this button in your theme folder').icon('heroicon-o-pencil-square').iconColor('info').send()"
                                            class="block text-sm p-7 hover:bg-neutral-100">
                                            <span class="block mb-1 font-medium text-black">Subscriptions</span>
                                            <span class="block leading-5 opacity-50">Integration payments and let users
                                                subscribe to a plan</span>
                                        </a>
                                    </div>
                                </div>
                            </ul>
                        </div>
                    </li> --}}

                    @guest
                        <li
                            class="relative z-30 flex flex-col items-center justify-center flex-shrink-0 w-full h-auto pt-3 space-y-3 text-sm md:hidden px-7">
                            <x-button href="{{ route('login') }}" tag="a" class="w-full text-sm"
                                color="secondary">Login</x-button>
                            <x-button href="{{ route('register') }}" tag="a" class="w-full text-sm">Sign
                                Up</x-button>
                        </li>
                    @else
                        <li class="flex items-center justify-center w-full pt-3 md:hidden px-7">
                            <x-button href="{{ route('login') }}" tag="a" class="w-full text-sm">View
                                Dashboard</x-button>
                        </li>
                    @endguest

                </ul>
            </nav>

            @guest
                <div
                    class="relative z-30 items-center justify-center flex-shrink-0 hidden h-full space-x-3 text-sm md:flex">
                    <x-button href="{{ route('login') }}" tag="a" class="text-sm" color="secondary">Login</x-button>
                    <x-button href="{{ route('register') }}" tag="a" class="text-sm">Sign Up</x-button>
                </div>
            @else
                <x-button href="{{ route('login') }}" tag="a" class="text-sm"
                    class="relative z-20 flex-shrink-0 hidden ml-2 md:block">View Dashboard</x-button>
            @endguest

        </div>
    </x-container>

</header>
