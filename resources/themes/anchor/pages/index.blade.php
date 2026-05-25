<?php

use App\Models\HomePageContent;
use function Laravel\Folio\name;

name('home');
?>

<x-layouts.marketing
    :seo="[
        'title' => 'Komunitas Historia Indonesia',
        'description' => 'Komunitas Historia Indonesia, penjaga memori kolektif bangsa melalui sejarah, kearsipan, kebudayaan, dan pariwisata.',
        'image' => url('/og_image.png'),
        'type' => 'website',
    ]"
    :showHeader="true"
    :showFooter="true"
    :showDecor="false"
    bodyClass="bg-[#fffafb] text-[#1d1a22]"
>
    @push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
    @endpush

    @php
        $homePageContent = HomePageContent::with(['achievements' => fn ($query) => $query->ordered()])->first();

        $heroTitle = $homePageContent?->hero_title
            ?? 'Komunitas Historia Indonesia: Penjaga Memori Kolektif Bangsa';
        $heroSubtitle = $homePageContent?->hero_subtitle
            ?? 'Komunitas Historia Indonesia (KHI) telah membuktikan bahwa sejarah bukan sekadar pelajaran tentang masa lalu, tetapi fondasi penting dalam membangun nasionalisme dan ketahanan bangsa yang kokoh.';
        $heroButtonText = $homePageContent?->hero_button_text ?? 'Bergabung Sekarang!';
        $heroImage = $homePageContent?->hero_image
            ? \Illuminate\Support\Facades\Storage::url($homePageContent->hero_image)
            : url('/images/img-hero.jpeg');

        $orgName = $homePageContent?->org_name ?? 'Komunitas Historia Indonesia';
        $orgAcronym = $homePageContent?->org_acronym ?? 'KHI';
        $orgDescription = $homePageContent?->org_description
            ?? 'Komunitas sejarah yang aktif mengedukasi publik melalui program, tulisan, kearsipan, kebudayaan, dan kegiatan lapangan.';
        $leaderName = $homePageContent?->leader_name ?? 'Asep Kambali';
        $leaderPosition = $homePageContent?->leader_position ?? 'Founder KHI';
        $leaderImage = $homePageContent?->leader_image
            ? \Illuminate\Support\Facades\Storage::url($homePageContent->leader_image)
            : url('/images/achievement.jpg');

        $achievements = $homePageContent?->achievements ?? collect();
        if ($achievements->isEmpty()) {
            $achievements = collect([
                (object) ['achievement_title' => 'Komunitas Peduli Museum, dari Museum Sejarah Jakarta & Gubernur DKI Jakarta 2003/2004.'],
                (object) ['achievement_title' => 'Most Recommended Consumer Community Award, dari SWA Magazine 2010.'],
                (object) ['achievement_title' => 'The Best Enterpreneurial & Business Consumunity Award, dari Prasetya Mulya Business School 2010.'],
                (object) ['achievement_title' => 'Komunitas Kreatif yang Berkhidmat Terhadap Tanah Air Indonesia, Menteri Pendidikan & Kebudayaan RI 2018.'],
            ]);
        }

        $newsPosts = \App\Models\Post::query()
            ->with('category')
            ->where('status', 'PUBLISHED')
            ->whereHas('category', function ($query) {
                $query->where('name', 'Historia News');
            })
            ->latest()
            ->take(3)
            ->get();

        if ($newsPosts->isEmpty()) {
            $newsPosts = \App\Models\Post::query()
                ->with('category')
                ->where('status', 'PUBLISHED')
                ->latest()
                ->take(3)
                ->get();
        }

        // Helper function to resolve paths
        $normalizeImageUrl = static fn (?string $path) => $path ? (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://']) ? $path : \Illuminate\Support\Facades\Storage::url(ltrim($path, '/'))) : null;

        // Fetch products dynamically
        $products = \App\Models\Product::query()
            ->where('status', 'active')
            ->with(['defaultVariant', 'images'])
            ->latest()
            ->take(4)
            ->get();

        // Fetch ebooks dynamically
        $ebooks = \App\Models\Ebook::published()
            ->latest()
            ->take(2)
            ->get();
    @endphp

    <!-- Main Content -->
    <div class="font-sans antialiased text-[#1d1a22]">
        <!-- Hero Section -->
        <section class="bg-[#f9f1fc] py-20 lg:py-28 px-6 relative overflow-hidden">
            <div class="max-w-[1280px] mx-auto grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center relative z-10">
                <div class="flex flex-col gap-6 lg:gap-8">
                    <h1 class="font-bold text-4xl lg:text-7xl tracking-tight leading-[1.1] text-[#37352F]">
                        {{ $heroTitle }}
                    </h1>
                    <p class="text-lg lg:text-xl text-[#575e75] leading-relaxed max-w-xl">
                        {{ $heroSubtitle }}
                    </p>
                    <div class="pt-2">
                        <a class="inline-flex items-center justify-center px-8 py-3 bg-[#df1c24] text-white rounded-full font-semibold text-sm shadow-md hover:bg-[#c41219] hover:shadow-lg transition-all duration-200 hover:-translate-y-0.5" href="{{ route('join') }}" wire:navigate>
                            {{ $heroButtonText }}
                        </a>
                    </div>
                </div>
                <div class="relative mt-8 lg:mt-0 lg:ml-8">
                    <!-- Workspace Mockup Card -->
                    <div class="bg-white rounded-xl p-4 shadow-2xl transform lg:-rotate-2 transition-transform hover:rotate-0 duration-500 border border-zinc-200/40">
                        <div class="flex items-center gap-2 mb-3 px-1 pt-1">
                            <div class="w-3 h-3 rounded-full bg-[#ba1a1a]"></div>
                            <div class="w-3 h-3 rounded-full bg-[#FFEBB3]"></div>
                            <div class="w-3 h-3 rounded-full bg-[#EBF9F4]"></div>
                        </div>
                        <img alt="KHI Team at historical site" class="w-full h-auto rounded-lg object-cover aspect-[1.15] shadow-inner" src="{{ $heroImage }}">
                    </div>
                    <!-- Decorative Element -->
                    <div class="absolute -bottom-12 -right-12 w-48 h-48 bg-[#df1c24]/20 rounded-full blur-3xl pointer-events-none"></div>
                </div>
            </div>
        </section>

        <!-- Mission & Awards Section (Bento Grid) -->
        <section class="py-20 px-6 max-w-[1280px] mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 lg:gap-8">
                <!-- KHI Description Card -->
                <div class="md:col-span-7 bg-[#FFEBB3] rounded-xl p-8 lg:p-12 shadow-sm hover:shadow-md transition-shadow duration-300 relative overflow-hidden group border border-[#E9E9E8]">
                    <div class="relative z-10 flex flex-col h-full justify-between gap-6">
                        <div>
                            <div class="inline-flex items-center gap-2 px-4 py-1 bg-white/50 rounded-full mb-4 backdrop-blur-sm border border-white/20">
                                <span class="material-symbols-outlined text-[#663d00] text-sm">history_edu</span>
                                <span class="font-bold text-xs tracking-wider uppercase text-[#663d00]">Our Mission</span>
                            </div>
                            <h2 class="font-bold text-3xl lg:text-4xl tracking-tight text-[#37352F] mb-4">
                                {{ $orgName }} @if ($orgAcronym) ({{ $orgAcronym }}) @endif
                            </h2>
                            <p class="text-base text-[#37352F]/80 leading-relaxed">
                                {{ $orgDescription }}
                            </p>
                        </div>
                        <div class="mt-auto pt-6 border-t border-[#663d00]/10">
                            <a class="inline-flex items-center gap-2 text-[#663d00] font-semibold text-sm group-hover:gap-4 transition-all duration-300" href="{{ route('organization') }}" wire:navigate>
                                Lihat selengkapnya <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                    <!-- Decorative blur -->
                    <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-white/40 rounded-full blur-3xl pointer-events-none group-hover:bg-white/60 transition-colors duration-500"></div>
                </div>

                <!-- Leader Image Card -->
                <div class="md:col-span-5 rounded-xl overflow-hidden shadow-md relative group border border-[#E9E9E8] h-full min-h-[400px]">
                    <img alt="{{ $leaderName }}, {{ $leaderPosition }}" class="w-full h-full object-cover absolute inset-0 transition-transform duration-700 group-hover:scale-105" src="{{ $leaderImage }}">
                    <div class="absolute inset-0 bg-gradient-to-t from-zinc-900/80 via-zinc-900/20 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 p-6 z-10 w-full">
                        <h3 class="font-bold text-xl lg:text-2xl text-white mb-1">{{ $leaderName }}</h3>
                        <p class="font-semibold text-sm text-white/80 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">person_play</span> {{ $leaderPosition }}
                        </p>
                    </div>
                </div>

                <!-- Awards Card -->
                <div class="md:col-span-12 bg-[#EBF9F4] rounded-xl p-8 lg:p-12 shadow-sm hover:shadow-md transition-shadow duration-300 border border-[#E9E9E8]">
                    <div class="flex flex-col md:flex-row gap-6 lg:gap-8 items-start">
                        <div class="md:w-1/3">
                            <div class="inline-flex items-center gap-2 px-4 py-1 bg-white/50 rounded-full mb-4 backdrop-blur-sm border border-white/20">
                                <span class="material-symbols-outlined text-[#40465d] text-sm">military_tech</span>
                                <span class="font-bold text-xs tracking-wider uppercase text-[#40465d]">Recognition</span>
                            </div>
                            <h2 class="font-bold text-2xl lg:text-3xl tracking-tight text-[#37352F]">Prestasi &amp; Penghargaan</h2>
                            <p class="text-base text-[#37352F]/70 leading-relaxed mt-2">Dedikasi kami dalam melestarikan sejarah telah diakui oleh berbagai institusi.</p>
                        </div>
                        <div class="md:w-2/3 grid grid-cols-1 sm:grid-cols-2 gap-4 w-full">
                            @foreach ($achievements->take(4) as $achievement)
                                <div class="bg-white/60 p-4 rounded-lg border border-white flex items-start gap-3">
                                    <span class="material-symbols-outlined text-[#df1c24] mt-1">check_circle</span>
                                    <p class="text-sm text-[#37352F]">
                                        {{ $achievement->achievement_title }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Merchandise Catalog Section -->
        <section class="py-20 bg-white border-t border-[#E9E9E8]">
            <div class="max-w-[1280px] mx-auto px-6">
                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h2 class="font-bold text-3xl lg:text-4xl tracking-tight text-[#37352F]">Merchandise Catalog</h2>
                        <p class="text-sm lg:text-base text-[#575e75]">Support our mission by wearing history.</p>
                    </div>
                    <a class="text-[#df1c24] font-semibold text-sm hover:underline" href="{{ route('merchandise') }}" wire:navigate>View All Shop</a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @if ($products->isNotEmpty())
                        @foreach ($products as $product)
                            @php
                                $productImage = null;
                                $variantImage = $product->defaultVariant?->image_url;
                                if ($variantImage) {
                                    $productImage = $normalizeImageUrl($variantImage);
                                } else {
                                    $productImage = $normalizeImageUrl($product->images->sortBy('sort_order')->first()?->image_url);
                                }
                                $price = 'Coming soon';
                                if ($product->defaultVariant) {
                                    $price = 'Rp ' . number_format($product->defaultVariant->price, 0, ',', '.');
                                }
                            @endphp
                            <div class="group flex flex-col justify-between h-full">
                                <div>
                                    <a href="{{ url('/merchandise/' . $product->slug) }}" wire:navigate class="aspect-square bg-zinc-100 rounded-xl mb-4 overflow-hidden flex items-center justify-center relative block">
                                        @if ($productImage)
                                            <img alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="{{ $productImage }}"/>
                                        @else
                                            <div class="text-[#979A9B] flex flex-col items-center">
                                                <span class="material-symbols-outlined text-4xl mb-1">image</span>
                                                <span class="text-[10px] uppercase font-semibold">No Image</span>
                                            </div>
                                        @endif
                                    </a>
                                    <h4 class="text-base font-semibold text-[#37352F]">
                                        <a href="{{ url('/merchandise/' . $product->slug) }}" wire:navigate class="hover:text-[#df1c24] transition-colors line-clamp-1">{{ $product->name }}</a>
                                    </h4>
                                    <p class="font-bold text-sm text-[#df1c24] mb-4 mt-1">{{ $price }}</p>
                                </div>
                                <a href="{{ url('/merchandise/' . $product->slug) }}" wire:navigate class="w-full text-center py-2.5 border border-zinc-200 text-[#37352F] rounded-lg font-semibold text-sm hover:bg-zinc-50 transition-colors block">
                                    View Product
                                </a>
                            </div>
                        @endforeach
                    @else
                        <!-- Fallback Stitch mockups -->
                        <div class="group flex flex-col justify-between h-full">
                            <div>
                                <div class="aspect-square bg-zinc-100 rounded-xl mb-4 overflow-hidden flex items-center justify-center">
                                    <img alt="KHI Official T-Shirt" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA7ipM72L6cuYHt3YWsvlVIOIgv7VhVp3c5GhZ_NVBLvb57vAe8dXKHqDFVLjZ2D_eR8V-EUfuwfk0wx14xddmTP6SLxYtgXn231Ha93xOKNFwEM2Ivp7e6C3ewlqrZ9eHIkjjvKQzwUZfC5JKMWKi4qDomN3rMz-ob9U1z7zwcD9EP-A4Y0jD-frg3CgqpdEeydZhUnun7e2TwYb_ynGWqnvrVshehFJ7xGZMmcGSt6mynkcyA5xnCdk6fJOl7egxxBx6IWDpgC5lD"/>
                                </div>
                                <h4 class="text-base font-semibold text-[#37352F]">KHI Official T-Shirt</h4>
                                <p class="font-bold text-sm text-[#df1c24] mb-4 mt-1">Rp 150.000</p>
                            </div>
                            <a href="{{ route('merchandise') }}" wire:navigate class="w-full text-center py-2.5 border border-zinc-200 text-[#37352F] rounded-lg font-semibold text-sm hover:bg-zinc-50 transition-colors block">View Product</a>
                        </div>
                        <div class="group flex flex-col justify-between h-full">
                            <div>
                                <div class="aspect-square bg-zinc-100 rounded-xl mb-4 overflow-hidden flex items-center justify-center">
                                    <img alt="Historia Tote Bag" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBiIoz7wKEHebu-L2j6FZYswNUdF4SpxYSf6ikU7MAaxIHq7xS3RaipGuJCGQ1y931pwraOABcmJb18_6wKaOwAvzp9sB9bhGV9gHeCz1ZORAg_8EGZSbfnfc4ou4AJbC1dh9xVZ4KqroXy7rkbxs5Fcy_M0B1Ly37uPmXK59M9eB9Csh7zE548_PkTuZ-XL4AuwMmbFdCRHkYT1AsarXYLHA1yVh05CK4rRbOScXrELKDHUhReplGV-74re6lsQOWSjhsX4MLjDhPo"/>
                                </div>
                                <h4 class="text-base font-semibold text-[#37352F]">Historia Tote Bag</h4>
                                <p class="font-bold text-sm text-[#df1c24] mb-4 mt-1">Rp 85.000</p>
                            </div>
                            <a href="{{ route('merchandise') }}" wire:navigate class="w-full text-center py-2.5 border border-zinc-200 text-[#37352F] rounded-lg font-semibold text-sm hover:bg-zinc-50 transition-colors block">View Product</a>
                        </div>
                        <div class="group flex flex-col justify-between h-full">
                            <div>
                                <div class="aspect-square bg-zinc-100 rounded-xl mb-4 overflow-hidden flex items-center justify-center">
                                    <img alt="Old Batavia Map Print" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCqsQiTLdoeqsz-YlYRQdWM-4hSzz5sXy58pedSqBSOCa2ph33EQ2_0kJ0ZTR_4B15aZ0B5aYDLZI3lDVn-Cm7kPienQoH7iKgxsxFj2guMRilt6NSqMLMHg5DxD08OWmYOpGB_ZYDEo2ARNp_EynHvPPRIL0KJjpYXBlVIvYTmNug9AH8r37FmGvAif6xzn7kbXE117XmM3NJ4g9vjkU7GYKDOGnK75FE87JSaukL79U-VM3UmCk1st5vFqU5DGY2MDZyCv0Xxw70n"/>
                                </div>
                                <h4 class="text-base font-semibold text-[#37352F]">Old Batavia Map Print</h4>
                                <p class="font-bold text-sm text-[#df1c24] mb-4 mt-1">Rp 120.000</p>
                            </div>
                            <a href="{{ route('merchandise') }}" wire:navigate class="w-full text-center py-2.5 border border-zinc-200 text-[#37352F] rounded-lg font-semibold text-sm hover:bg-zinc-50 transition-colors block">View Product</a>
                        </div>
                        <div class="group flex flex-col justify-between h-full">
                            <div>
                                <div class="aspect-square bg-zinc-100 rounded-xl mb-4 overflow-hidden flex items-center justify-center">
                                    <img alt="Exclusive Pin Set" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC7qade8_xtkqbbnia5hmSbONXlsWtFAW8-X9jdsoxPE4um7tr-VGWH3otc6hFQFMCZC6htcDdkwhmDjXgll7NK-cZXuVTBAMkWiO5ppf4nBi75shSnrMrAJf1SVHTN7NEMAGL5gzit4JEUM9ZCyCNaalmdD2NLexWXIWU9eueLVqpeDsB7U36IftpF7J5j_3HW1TNUimX0yS9amAT5LyajVOrEfKIVvl10w8L_iOf39JNRVSIPzaIhsoGj7jTAYPJJAcLOt5UERROz"/>
                                </div>
                                <h4 class="text-base font-semibold text-[#37352F]">Exclusive Pin Set</h4>
                                <p class="font-bold text-sm text-[#df1c24] mb-4 mt-1">Rp 45.000</p>
                            </div>
                            <a href="{{ route('merchandise') }}" wire:navigate class="w-full text-center py-2.5 border border-zinc-200 text-[#37352F] rounded-lg font-semibold text-sm hover:bg-zinc-50 transition-colors block">View Product</a>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <!-- E-Book Showcase Section -->
        <section class="py-20 bg-[#fff5f5] border-t border-[#E9E9E8]">
            <div class="max-w-[1280px] mx-auto px-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    <div class="lg:col-span-4">
                        <h2 class="font-bold text-3xl lg:text-4xl tracking-tight text-[#37352F] mb-4">E-Book Showcase</h2>
                        <p class="text-base text-[#37352F]/80 leading-relaxed mb-6">Explore our curated digital library of Indonesian history. Exclusive publications available for members.</p>
                        <a class="inline-flex items-center justify-center px-6 py-2.5 bg-[#df1c24] text-white rounded-lg font-semibold text-sm hover:bg-[#c41219] transition-colors shadow" href="{{ route('library') }}" wire:navigate>Explore E-Library</a>
                    </div>
                    <div class="lg:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @if ($ebooks->isNotEmpty())
                            @foreach ($ebooks as $book)
                                @php
                                    $coverUrl = $book->cover_image
                                        ? (str_starts_with($book->cover_image, 'http') ? $book->cover_image : Storage::url(ltrim($book->cover_image, '/')))
                                        : 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=400&auto=format&fit=crop';
                                    
                                    // Fallback for missing/demo images that don't exist on disk
                                    if ($book->cover_image && str_starts_with($book->cover_image, 'demo/')) {
                                        $coverUrl = 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=400&auto=format&fit=crop';
                                    }
                                @endphp
                                <div class="bg-white p-6 rounded-xl flex gap-6 shadow-sm hover:shadow transition-shadow duration-300">
                                    <div class="w-24 h-32 bg-zinc-100 rounded flex-shrink-0 flex items-center justify-center overflow-hidden border border-zinc-200">
                                        <img alt="{{ $book->title }}" class="w-full h-full object-cover" src="{{ $coverUrl }}"/>
                                    </div>
                                    <div class="flex flex-col justify-between">
                                        <div>
                                            <h4 class="text-base text-[#37352F] font-semibold leading-snug line-clamp-2">{{ $book->title }}</h4>
                                            <p class="text-xs text-zinc-400 mt-1">By {{ $book->author }}</p>
                                        </div>
                                        <div class="flex flex-col gap-2 mt-2">
                                            <a href="{{ route('library.book', ['slug' => $book->slug]) }}" wire:navigate class="text-[#df1c24] text-left hover:underline text-sm font-semibold">Read Sample</a>
                                            <a href="{{ route('library.book', ['slug' => $book->slug]) }}" wire:navigate class="text-zinc-600 text-left hover:underline text-sm font-semibold">Get E-Book</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <!-- Fallback Stitch mockups -->
                            <div class="bg-white p-6 rounded-xl flex gap-6 shadow-sm">
                                <div class="w-24 h-32 bg-zinc-100 rounded flex-shrink-0 flex items-center justify-center overflow-hidden border border-zinc-200">
                                    <img alt="Sejarah Nasional E-Book" class="w-full h-full object-cover rounded" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDiN-EXoSFyMEVwkTracELbrpOVNUqdHAXR-fg0kzdNJPkEXJCFjn4FR2HLCcFmCwxZG0v1ph0HVfvzEsjqV_3DIPD13WlebDsqli_B3ShPDLM_o_vFa44QzuZg5SHvGyyhr2m9qH9LQNAyA5Ga8bpu9LSj2TRi2yHzCWKtQXDhziICFNayCwbbBn2icOrgI7f-K05QCrjRexKXPTARwvUTS5-JPunPYYVZ9f6Uf-upW6vYDHdkz6nhOGzVyaU0JDz4deyppKuyrGDY"/>
                                </div>
                                <div class="flex flex-col justify-between">
                                    <div>
                                        <h4 class="text-base text-[#37352F] font-semibold">Sejarah Nasional</h4>
                                        <p class="text-xs text-zinc-400 mt-1">Digital Edition</p>
                                    </div>
                                    <div class="flex flex-col gap-2 mt-2">
                                        <a href="{{ route('library') }}" wire:navigate class="text-[#df1c24] text-left hover:underline text-sm font-semibold">Read Sample</a>
                                        <a href="{{ route('library') }}" wire:navigate class="text-zinc-600 text-left hover:underline text-sm font-semibold">Get E-Book</a>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white p-6 rounded-xl flex gap-6 shadow-sm">
                                <div class="w-24 h-32 bg-zinc-100 rounded flex-shrink-0 flex items-center justify-center overflow-hidden border border-zinc-200">
                                    <img alt="Surat Batavia E-Book" class="w-full h-full object-cover rounded" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCyPe8GCol3WwzuDhK5RhLmhrzx9tQwgXd97-i6nv1ASOWZbjFXZaSqtqqLB6Or6Njo-4W9pUw5EcH792awkgu4hzMCqzCwwRMhZrQEw0Ds0SZgywphezWAEdyqLcKqQHBVpVUTH0tGxpjuj7oKa47_wRjF0KOo9nudyXHkThgdyGKPb3YBSsNN9qHQAJgZ_CBfi_1IqWg5TX7oJwu6-glJxCo7E7r9lanpz8xKLUBmy2f7PMFLpeKhduhRIOuwAGCg4h-OIPKcD-0C"/>
                                </div>
                                <div class="flex flex-col justify-between">
                                    <div>
                                        <h4 class="text-base text-[#37352F] font-semibold">Surat Batavia</h4>
                                        <p class="text-xs text-zinc-400 mt-1">Archive Study</p>
                                    </div>
                                    <div class="flex flex-col gap-2 mt-2">
                                        <a href="{{ route('library') }}" wire:navigate class="text-[#df1c24] text-left hover:underline text-sm font-semibold">Read Sample</a>
                                        <a href="{{ route('library') }}" wire:navigate class="text-zinc-600 text-left hover:underline text-sm font-semibold">Get E-Book</a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <!-- Historia News Section -->
        <section class="py-16 bg-white border-t border-[#E9E9E8]">
            <div class="max-w-[1280px] mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="font-bold text-3xl lg:text-4xl tracking-tight text-[#37352F] mb-2">Historia News</h2>
                    <p class="text-base text-zinc-500">Check out some of our latest blog posts below.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($newsPosts as $post)
                        @php
                            $excerpt = \Illuminate\Support\Str::limit(strip_tags($post->body), 160);
                            $publishedAt = $post->created_at->format('M d, Y');
                            $categoryName = $post->category?->name ?? 'Admin KHI';
                        @endphp
                        <article class="bg-white rounded-xl border border-[#E9E9E8] shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col overflow-hidden group">
                            <div class="relative h-48 overflow-hidden bg-zinc-100 flex items-center justify-center">
                                @if ($post->image)
                                    <img alt="{{ $post->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="{{ $post->image() }}">
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#FFEBB3]/30 to-[#fff5f5]/30">
                                        <span class="material-symbols-outlined text-5xl text-[#df1c24] opacity-60">article</span>
                                    </div>
                                @endif
                            </div>
                            <div class="p-6 flex flex-col flex-grow">
                                <div class="flex items-center gap-2 font-bold text-xs tracking-wider uppercase text-zinc-400 mb-2">
                                    <span>{{ $publishedAt }}</span>
                                    <span class="w-1 h-1 rounded-full bg-zinc-300"></span>
                                    <span>{{ $categoryName }}</span>
                                </div>
                                <h3 class="text-base lg:text-lg font-semibold text-[#37352F] mb-2 group-hover:text-[#df1c24] transition-colors line-clamp-2">
                                    <a href="{{ $post->link() }}" wire:navigate>
                                        {{ $post->title }}
                                    </a>
                                </h3>
                                <p class="text-sm text-zinc-500 leading-relaxed line-clamp-3 mb-4">
                                    {{ $excerpt }}
                                </p>
                                <a class="mt-auto inline-flex items-center gap-1 font-semibold text-sm text-[#df1c24] hover:text-[#c41219] transition-colors" href="{{ $post->link() }}" wire:navigate>
                                    Read More <span class="material-symbols-outlined text-sm">arrow_right_alt</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-8 text-center">
                    <a class="inline-flex items-center justify-center px-6 py-2.5 border border-[#D1D1D0] text-[#37352F] rounded-lg font-semibold text-sm hover:bg-zinc-50 transition-colors duration-200" href="{{ route('historia-news') }}" wire:navigate>
                        Lihat selengkapnya
                    </a>
                </div>
            </div>
        </section>

        <!-- CTA Callout Banner (Gabung Bersama KHI) -->
        <section class="py-20 bg-[#020611] text-white overflow-hidden relative">
            <div class="absolute top-0 right-0 w-1/3 h-full bg-[#df1c24]/10 blur-3xl rounded-full translate-x-1/2"></div>
            <div class="max-w-[1280px] mx-auto px-6 relative z-10">
                <div class="bg-white/5 p-8 lg:p-12 rounded-3xl border border-white/10 backdrop-blur-sm text-center md:text-left">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                        <div class="flex flex-col gap-4">
                            <h2 class="font-bold text-3xl lg:text-5xl tracking-tight text-white">Gabung Bersama KHI</h2>
                            <p class="text-base lg:text-lg text-white/70 leading-relaxed">Jadilah bagian dari penjaga memori bangsa. Nikmati akses eksklusif ke arsip digital, prioritas pendaftaran acara, dan jaringan komunitas sejarah terbesar di Indonesia.</p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-end">
                            <a class="px-8 py-3 bg-[#df1c24] text-white rounded-full font-semibold text-sm text-center hover:bg-[#c41219] transition-all shadow-lg shadow-[#df1c24]/20" href="{{ route('join') }}" wire:navigate>Daftar Member</a>
                            <a class="px-8 py-3 border border-white/30 text-white rounded-full font-semibold text-sm text-center hover:bg-white/10 transition-all" href="{{ route('join') }}" wire:navigate>Pelajari Benefit</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.marketing>
