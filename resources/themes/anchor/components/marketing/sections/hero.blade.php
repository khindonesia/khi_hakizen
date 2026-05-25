@props(['homePageContent' => null])

<section
    class="relative top-0 flex flex-col items-center justify-center w-full min-h-screen -mt-24 bg-white lg:min-h-screen">

    <div
        class="flex flex-col items-center justify-between flex-1 w-full max-w-2xl gap-6 px-8 pt-32 mx-auto text-left md:px-12 xl:px-20 lg:pt-32 lg:pb-16 lg:max-w-7xl lg:flex-row">
        <div class="w-full lg:w-1/2">
            <h1
                class="text-4xl font-bold tracking-tighter text-left sm:text-7xl md:text-6xl sm:text-center lg:text-left text-zinc-900 text-balance">
                <span class="block origin-left lg:scale-90">
                    {{ $homePageContent?->hero_title ?? 'Komunitas Historia Indonesia' }}
                </span>
            </h1>
            <p
                class="mx-auto mt-5 text-xl font-normal text-left sm:max-w-md lg:ml-0 lg:max-w-md sm:text-center lg:text-left text-zinc-500">
                {{ $homePageContent?->hero_subtitle ?? 'Komunitas sejarah untuk belajar, berbagi, dan melestarikan warisan budaya Indonesia.' }}
            </p>
            <div
                class="flex flex-col items-center justify-center gap-3 mx-auto mt-8 md:gap-2 lg:justify-start md:ml-0 md:flex-row">
                <x-button :href="route('register')" tag="a" size="lg" class="w-full lg:w-auto">
                    {{ $homePageContent?->hero_button_text ?? 'Bergabung Sekarang' }}
                </x-button>
                {{-- <x-button :href="route('store')" tag="a" wire:navigate size="lg" color="secondary" class="w-full lg:w-auto">Kunjungi Toko Kami</x-button> --}}
            </div>
        </div>
        <div class="flex items-center justify-center w-full mt-12 lg:w-1/2 lg:mt-0">
            <img alt="Hero Image" class="relative w-full lg:scale-125 xl:translate-x-6 rounded-lg"
                src="{{ $homePageContent && $homePageContent->hero_image ? Storage::url($homePageContent->hero_image) : '/images/img-hero.jpeg' }}"
                style="max-width:450px;">
        </div>
    </div>
</section>
