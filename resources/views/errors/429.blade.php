<x-layouts.marketing :seo="['title' => 'Terlalu Banyak Permintaan - Komunitas Historia Indonesia']">
    <div class="relative min-h-[60vh] flex items-center justify-center py-12 md:py-24">
        <x-container class="max-w-xl text-center space-y-6">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-50 text-red-600">
                <span class="material-symbols-outlined text-4xl">speed</span>
            </div>
            <h1 class="text-3xl font-semibold leading-tight tracking-tight text-zinc-900 md:text-4xl">
                Batas Permintaan Terlampaui
            </h1>
            <p class="text-sm leading-relaxed text-zinc-500">
                Anda melakukan terlalu banyak permintaan dalam waktu singkat (Too Many Requests). Silakan tunggu beberapa saat (sekitar 1 menit) sebelum mencoba kembali mengakses halaman ini.
            </p>
            <div class="pt-4">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 bg-[#df1c24] hover:bg-opacity-95 text-white font-semibold py-3 px-6 rounded-full transition shadow-xs">
                    <span class="material-symbols-outlined text-[18px]">home</span>
                    Kembali ke Beranda
                </a>
            </div>
        </x-container>
    </div>
</x-layouts.marketing>
