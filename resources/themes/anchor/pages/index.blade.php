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
@php
        $homePageContent = HomePageContent::with(['achievements' => fn ($query) => $query->ordered()])->first();

        $heroTitle = setting('hero_title', 'Komunitas Historia Indonesia: Penjaga Memori Kolektif Bangsa');
        $heroSubtitle = setting('hero_subtitle', 'Komunitas Historia Indonesia (KHI) telah membuktikan bahwa sejarah bukan sekadar pelajaran tentang masa lalu, tetapi fondasi penting dalam membangun nasionalisme dan ketahanan bangsa yang kokoh.');
        $heroButtonText = setting('hero_button_text', 'Bergabung Sekarang!');
        $heroImage = setting_image('hero_image', asset('/images/img-hero.jpeg'));

        $orgName = setting('site.title', 'Komunitas Historia Indonesia');
        $orgAcronym = setting('org_acronym', 'KHI');
        $orgDescription = setting('about_description', 'Komunitas sejarah yang aktif mengedukasi publik melalui program, tulisan, kearsipan, kebudayaan, dan kegiatan lapangan.');
        $leaderName = $homePageContent?->leader_name ?? 'Asep Kambali';
        $leaderPosition = $homePageContent?->leader_position ?? 'Founder KHI';
        $leaderImage = $homePageContent?->leader_image
            ? \Illuminate\Support\Facades\Storage::url($homePageContent->leader_image)
            : asset('/images/achievement.jpg');

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

        // Fetch events dynamically
        $events = \App\Models\Event::query()
            ->published()
            ->upcoming()
            ->latest('start_datetime')
            ->take(3)
            ->get();

        if ($events->isEmpty()) {
            $events = \App\Models\Event::query()
                ->published()
                ->latest('start_datetime')
                ->take(3)
                ->get();
        }

        $heroPosts = \App\Models\Post::query()
            ->with('category')
            ->where('status', 'PUBLISHED')
            ->latest()
            ->take(3)
            ->get();

        if ($heroPosts->isEmpty()) {
            $heroPosts = collect([
                (object) [
                    'title' => $heroTitle,
                    'excerpt' => $heroSubtitle,
                    'body' => $heroSubtitle,
                    'category' => (object) ['name' => 'Featured']
                ]
            ]);
        }
    @endphp

    <!-- Main Content Wrapper -->
    <div class="font-sans antialiased text-zinc-900">
        
        <!-- 1. Hero Section -->
        <section class="relative bg-primary-fixed overflow-hidden border-b border-hairline py-12 lg:py-20 px-6" x-data="{ activeIndex: 0, postsCount: {{ count($heroPosts) }} }">
            <div class="max-w-[1280px] mx-auto">
                @foreach ($heroPosts as $idx => $post)
                    @php
                        $postImage = ($post instanceof \App\Models\Post) ? $post->image() : $heroImage;
                        $postLink = ($post instanceof \App\Models\Post) ? $post->link() : route('join');
                        $postCategory = ($post instanceof \App\Models\Post) ? ($post->category?->name ?? 'Featured') : 'Featured';
                        $postExcerpt = ($post instanceof \App\Models\Post) ? ($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 150)) : $post->excerpt;
                    @endphp
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 items-center" x-show="activeIndex === {{ $idx }}" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
                        <!-- Featured Content Side -->
                        <div class="lg:col-span-5 flex flex-col relative z-10">
                            <div class="flex flex-col gap-6 lg:gap-8">
                                <div class="inline-flex items-center">
                                    <span class="px-3 py-1 bg-primary text-white text-xs font-bold rounded-sm uppercase tracking-widest">{{ $postCategory }}</span>
                                </div>
                                <h1 class="font-bold text-4xl lg:text-6xl tracking-tight leading-[1.1] text-charcoal">
                                    {{ $post->title }}
                                </h1>
                                <p class="text-base lg:text-lg text-secondary leading-relaxed max-w-md">
                                    {{ $postExcerpt }}
                                </p>
                                <div class="flex flex-wrap items-center gap-6 mt-2">
                                    <a class="inline-flex items-center justify-center px-8 py-3.5 bg-primary text-white rounded-lg font-semibold text-sm shadow-md hover:bg-[#c41219] hover:shadow-lg transition-all duration-200 hover:-translate-y-0.5" href="{{ $postLink }}" wire:navigate>
                                        {{ ($post instanceof \App\Models\Post) ? 'Read More' : $heroButtonText }}
                                    </a>
                                    <div class="flex items-center gap-3" x-show="postsCount > 1">
                                        <button @click="activeIndex = (activeIndex === 0) ? postsCount - 1 : activeIndex - 1" class="w-12 h-12 rounded-full border border-hairline-strong flex items-center justify-center text-charcoal hover:border-primary hover:text-primary transition-all">
                                            <span class="material-symbols-outlined text-lg">arrow_back</span>
                                        </button>
                                        <button @click="activeIndex = (activeIndex === postsCount - 1) ? 0 : activeIndex + 1" class="w-12 h-12 rounded-full border border-hairline-strong flex items-center justify-center text-charcoal hover:border-primary hover:text-primary transition-all">
                                            <span class="material-symbols-outlined text-lg">arrow_forward</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex gap-2 mt-4 items-center" x-show="postsCount > 1">
                                    @foreach ($heroPosts as $dotIdx => $dotPost)
                                        <div @click="activeIndex = {{ $dotIdx }}" class="cursor-pointer transition-all duration-300" :class="activeIndex === {{ $dotIdx }} ? 'w-8 h-[2px] bg-primary' : 'w-4 h-[2px] bg-hairline-strong'"></div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <!-- Image Side -->
                        <div class="lg:col-span-7 relative h-[400px] lg:h-[550px] w-full rounded-2xl overflow-hidden shadow-xl border border-hairline/40 group">
                            <img alt="{{ $post->title }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-500" src="{{ $postImage }}" fetchpriority="high" loading="eager">
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- 2. Upcoming Events Section -->
        @if ($events->isNotEmpty())
        <section class="py-20 bg-zinc-50 border-t border-hairline">
            <div class="max-w-[1280px] mx-auto px-6">
                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h2 class="font-bold text-3xl lg:text-4xl tracking-tight text-charcoal">{{ setting('home_events_title', 'Upcoming Events') }}</h2>
                        <p class="text-sm lg:text-base text-zinc-500 mt-1">{{ setting('home_events_subtitle', 'Join our upcoming historical tours and webinars.') }}</p>
                    </div>
                    <a class="text-primary font-semibold text-sm hover:underline" href="{{ url('/events') }}" wire:navigate>{{ setting('home_events_view_all_text', 'View All Events') }}</a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($events as $idx => $event)
                        @php
                            // Assign distinct colors to the events dynamically
                            $colorClasses = [
                                0 => ['bg' => 'bg-card-tint-rose', 'text' => 'text-primary'],
                                1 => ['bg' => 'bg-card-tint-sky', 'text' => 'text-link-blue'],
                                2 => ['bg' => 'bg-card-tint-mint', 'text' => 'text-tertiary']
                            ];
                            $style = $colorClasses[$idx % 3];
                            $eventExcerpt = \Illuminate\Support\Str::limit(strip_tags($event->body), 150);
                            $eventMonth = $event->start_datetime->format('M');
                            $eventDay = $event->start_datetime->format('d');
                            $eventTime = $event->start_datetime->format('H:i') . ' - ' . $event->end_datetime->format('H:i') . ' WIB';
                        @endphp
                        <div class="bg-white rounded-xl p-6 border border-hairline shadow-sm hover:shadow-md transition-shadow flex flex-col gap-4 group">
                            <div class="flex items-start justify-between">
                                <div class="{{ $style['bg'] }} {{ $style['text'] }} px-4 py-2 rounded-lg text-center min-w-[60px]">
                                    <span class="block text-xs font-bold uppercase tracking-wider">{{ $eventMonth }}</span>
                                    <span class="block text-2xl lg:text-3xl font-bold leading-none mt-1">{{ $eventDay }}</span>
                                </div>
                                <span class="bg-zinc-100 text-zinc-600 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider">
                                    {{ $event->type === 'FREE' ? 'Free Event' : 'Paid Event' }}
                                </span>
                            </div>
                            <h3 class="font-bold text-lg lg:text-xl text-charcoal mt-2 group-hover:text-primary transition-colors line-clamp-1">
                                <a href="{{ url('/events/' . $event->slug) }}" wire:navigate>{{ $event->title }}</a>
                            </h3>
                            <p class="text-sm text-zinc-500 leading-relaxed line-clamp-3">
                                {{ $eventExcerpt }}
                            </p>
                            <div class="mt-auto pt-4 border-t border-hairline flex items-center justify-between">
                                <span class="text-xs text-zinc-400 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">schedule</span> {{ $eventTime }}
                                </span>
                                <a href="{{ url('/events/' . $event->slug) }}" wire:navigate class="text-primary font-semibold text-sm hover:text-[#c41219] transition-colors">
                                    {{ setting('home_events_register_text', 'Register') }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <!-- 3. E-Book Showcase Section -->
        @if ($ebooks->isNotEmpty())
        <section class="py-20 bg-card-tint-lavender border-t border-hairline">
            <div class="max-w-[1280px] mx-auto px-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    <div class="lg:col-span-4">
                        <h2 class="font-bold text-3xl lg:text-4xl tracking-tight text-charcoal mb-4">{{ setting('home_library_title', 'E-Book Showcase') }}</h2>
                        <p class="text-base text-zinc-700 leading-relaxed mb-6">{{ setting('home_library_subtitle', 'Explore our curated digital library of Indonesian history. Exclusive publications available for members.') }}</p>
                        <a class="inline-flex items-center justify-center px-6 py-2.5 bg-primary text-white rounded-lg font-semibold text-sm hover:bg-[#c41219] transition-colors shadow" href="{{ route('library') }}" wire:navigate>{{ setting('home_library_explore_text', 'Explore E-Library') }}</a>
                    </div>
                    <div class="lg:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
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
                            <div class="bg-white p-6 rounded-xl flex gap-6 shadow-sm hover:shadow transition-shadow duration-300 border border-hairline">
                                <div class="w-24 h-32 bg-zinc-100 rounded flex-shrink-0 flex items-center justify-center overflow-hidden border border-zinc-200">
                                    <img alt="{{ $book->title }}" class="w-full h-full object-cover" src="{{ $coverUrl }}"/>
                                </div>
                                <div class="flex flex-col justify-between">
                                    <div>
                                        <h4 class="text-base text-charcoal font-semibold leading-snug line-clamp-2">{{ $book->title }}</h4>
                                        <p class="text-xs text-zinc-400 mt-1">By {{ $book->author }}</p>
                                    </div>
                                    <div class="flex flex-col gap-2 mt-2">
                                        <a href="{{ route('library.book', ['slug' => $book->slug]) }}" wire:navigate class="text-primary text-left hover:underline text-sm font-semibold">{{ setting('home_library_read_sample_text', 'Read Sample') }}</a>
                                        <a href="{{ route('library.book', ['slug' => $book->slug]) }}" wire:navigate class="text-zinc-600 text-left hover:underline text-sm font-semibold">{{ setting('home_library_get_ebook_text', 'Get E-Book') }}</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
        @endif

        <!-- 4. Merchandise Catalog Section -->
        @if ($products->isNotEmpty())
        <section class="py-20 bg-white border-t border-hairline">
            <div class="max-w-[1280px] mx-auto px-6">
                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h2 class="font-bold text-3xl lg:text-4xl tracking-tight text-charcoal">{{ setting('home_merchandise_title', 'Merchandise Catalog') }}</h2>
                        <p class="text-sm lg:text-base text-zinc-500 mt-1">{{ setting('home_merchandise_subtitle', 'Support our mission by wearing history.') }}</p>
                    </div>
                    <a class="text-primary font-semibold text-sm hover:underline" href="{{ route('merchandise') }}" wire:navigate>{{ setting('home_merchandise_view_all_text', 'View All Shop') }}</a>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
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
                                <a href="{{ url('/merchandise/' . $product->slug) }}" wire:navigate class="aspect-square bg-zinc-50 rounded-xl mb-4 overflow-hidden flex items-center justify-center relative block">
                                    @if ($productImage)
                                        <img alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="{{ $productImage }}" loading="lazy" decoding="async" width="300" height="300"/>
                                    @else
                                        <div class="text-slate flex flex-col items-center">
                                            <span class="material-symbols-outlined text-4xl mb-1">image</span>
                                            <span class="text-[10px] uppercase font-semibold">No Image</span>
                                        </div>
                                    @endif
                                </a>
                                <h4 class="text-base font-semibold text-charcoal">
                                    <a href="{{ url('/merchandise/' . $product->slug) }}" wire:navigate class="hover:text-primary transition-colors line-clamp-1">{{ $product->name }}</a>
                                </h4>
                                <p class="font-bold text-sm text-primary mb-4 mt-1">{{ $price }}</p>
                            </div>
                            <a href="{{ url('/merchandise/' . $product->slug) }}" wire:navigate class="w-full text-center py-2.5 border border-zinc-200 text-charcoal rounded-lg font-semibold text-sm hover:bg-zinc-50 transition-colors block">
                                {{ setting('home_merchandise_view_product_text', 'View Product') }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <!-- 5. Historia News Section -->
        @if ($newsPosts->isNotEmpty())
        <section class="py-20 bg-white border-t border-hairline">
            <div class="max-w-[1280px] mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="font-bold text-3xl lg:text-4xl tracking-tight text-charcoal mb-2">{{ setting('home_news_title', 'Historia News') }}</h2>
                    <p class="text-base text-zinc-500">{{ setting('home_news_subtitle', 'Check out some of our latest blog posts below.') }}</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($newsPosts as $post)
                        @php
                            $excerpt = \Illuminate\Support\Str::limit(strip_tags($post->body), 160);
                            $publishedAt = $post->created_at->format('M d, Y');
                            $categoryName = $post->category?->name ?? 'Admin KHI';
                        @endphp
                        <article class="bg-white rounded-xl border border-hairline shadow-sm hover:shadow-md transition-shadow duration-300 flex flex-col overflow-hidden group">
                            <div class="relative h-48 overflow-hidden bg-zinc-50 flex items-center justify-center border-b border-hairline">
                                @if ($post->image)
                                    <img alt="{{ $post->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="{{ $post->image() }}">
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-card-tint-peach to-primary-fixed">
                                        <span class="material-symbols-outlined text-5xl text-primary opacity-60">article</span>
                                    </div>
                                @endif
                            </div>
                            <div class="p-6 flex flex-col flex-grow">
                                <div class="flex items-center gap-2 font-bold text-xs tracking-wider uppercase text-zinc-400 mb-2">
                                    <span>{{ $publishedAt }}</span>
                                    <span class="w-1 h-1 rounded-full bg-zinc-300"></span>
                                    <span>{{ $categoryName }}</span>
                                </div>
                                <h3 class="text-base lg:text-lg font-semibold text-charcoal mb-2 group-hover:text-primary transition-colors line-clamp-2">
                                    <a href="{{ $post->link() }}" wire:navigate>
                                        {{ $post->title }}
                                    </a>
                                </h3>
                                <p class="text-sm text-zinc-500 leading-relaxed line-clamp-3 mb-4">
                                    {{ $excerpt }}
                                </p>
                                <a class="mt-auto inline-flex items-center gap-1 font-semibold text-sm text-primary hover:text-[#c41219] transition-colors" href="{{ $post->link() }}" wire:navigate>
                                    {{ setting('home_news_read_more_text', 'Read More') }} <span class="material-symbols-outlined text-sm">arrow_right_alt</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-12 text-center">
                    <a class="inline-flex items-center justify-center px-6 py-2.5 border border-hairline-strong text-charcoal rounded-lg font-semibold text-sm hover:bg-zinc-50 transition-colors duration-200" href="{{ route('historia-news') }}" wire:navigate>
                        {{ setting('home_news_view_all_text', 'Lihat selengkapnya') }}
                    </a>
                </div>
            </div>
        </section>
        @endif

        <!-- 6. Bento Grid (Mission & Awards Section) -->
        <section class="py-20 px-6 max-w-[1280px] mx-auto border-t border-hairline">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 lg:gap-8">
                <!-- KHI Description Card -->
                <div class="md:col-span-7 bg-card-tint-yellow-bold rounded-xl p-8 lg:p-12 shadow-sm hover:shadow-md transition-shadow duration-300 relative overflow-hidden group border border-hairline">
                    <div class="relative z-10 flex flex-col h-full justify-between gap-6">
                        <div>
                            <div class="inline-flex items-center gap-2 px-4 py-1 bg-white/50 rounded-full mb-4 backdrop-blur-sm border border-white/20">
                                <span class="material-symbols-outlined text-[#663d00] text-sm">history_edu</span>
                                <span class="font-bold text-xs tracking-wider uppercase text-[#663d00]">{{ setting('home_mission_badge_text', 'Our Mission') }}</span>
                            </div>
                            <h2 class="font-bold text-3xl lg:text-4xl tracking-tight text-[#37352F] mb-4">
                                {{ $orgName }} @if ($orgAcronym) ({{ $orgAcronym }}) @endif
                            </h2>
                            <div class="text-base text-[#37352F]/80 leading-relaxed">
                                {!! setting_sanitized('about_description', $orgDescription) !!}
                            </div>
                        </div>
                        <div class="mt-auto pt-6 border-t border-[#663d00]/10">
                            <a class="inline-flex items-center gap-2 text-[#663d00] font-semibold text-sm group-hover:gap-4 transition-all duration-300" href="{{ route('organization') }}" wire:navigate>
                                {{ setting('home_mission_view_more_text', 'Lihat selengkapnya') }} <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                    <!-- Decorative blur -->
                    <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-white/40 rounded-full blur-3xl pointer-events-none group-hover:bg-white/60 transition-colors duration-500"></div>
                </div>

                <!-- Leader Image Card -->
                @if ($leaderName)
                <div class="md:col-span-5 rounded-xl overflow-hidden shadow-md relative group border border-hairline h-full min-h-[400px]">
                    <img alt="{{ $leaderName }}, {{ $leaderPosition }}" class="w-full h-full object-cover absolute inset-0 transition-transform duration-700 group-hover:scale-105" src="{{ $leaderImage }}" loading="lazy" decoding="async" width="500" height="600">
                    <div class="absolute inset-0 bg-gradient-to-t from-zinc-900/80 via-zinc-900/20 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 p-6 z-10 w-full">
                        <h3 class="font-bold text-xl lg:text-2xl text-white mb-1">{{ $leaderName }}</h3>
                        <p class="font-semibold text-sm text-white/80 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">person_play</span> {{ $leaderPosition }}
                        </p>
                    </div>
                </div>
                @endif

                <!-- Awards Card -->
                @if ($achievements->isNotEmpty())
                <div class="md:col-span-12 bg-card-tint-mint rounded-xl p-8 lg:p-12 shadow-sm hover:shadow-md transition-shadow duration-300 border border-hairline">
                    <div class="flex flex-col md:flex-row gap-6 lg:gap-8 items-start">
                        <div class="md:w-1/3">
                            <div class="inline-flex items-center gap-2 px-4 py-1 bg-white/50 rounded-full mb-4 backdrop-blur-sm border border-white/20">
                                <span class="material-symbols-outlined text-[#40465d] text-sm">military_tech</span>
                                <span class="font-bold text-xs tracking-wider uppercase text-[#40465d]">{{ setting('home_recognition_badge_text', 'Recognition') }}</span>
                            </div>
                            <h2 class="font-bold text-2xl lg:text-3xl tracking-tight text-charcoal">{{ setting('home_recognition_title', 'Prestasi &amp; Penghargaan') }}</h2>
                            <p class="text-base text-zinc-600 leading-relaxed mt-2">{{ setting('home_recognition_subtitle', 'Dedikasi kami dalam melestarikan sejarah telah diakui oleh berbagai institusi.') }}</p>
                        </div>
                        <div class="md:w-2/3 grid grid-cols-1 sm:grid-cols-2 gap-4 w-full">
                            @foreach ($achievements->take(4) as $achievement)
                                <div class="bg-white/60 p-4 rounded-lg border border-white flex items-start gap-3 shadow-sm hover:shadow-md transition-shadow duration-300">
                                    <span class="material-symbols-outlined text-primary mt-1">check_circle</span>
                                    <p class="text-sm text-charcoal">
                                        {{ $achievement->achievement_title }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </section>

        <!-- 7. CTA Banner (Gabung Bersama KHI) -->
        <section class="py-20 bg-brand-navy-deep text-white overflow-hidden relative">
            <div class="absolute top-0 right-0 w-1/3 h-full bg-primary/10 blur-3xl rounded-full translate-x-1/2"></div>
            <div class="max-w-[1280px] mx-auto px-6 relative z-10">
                <div class="bg-white/5 p-8 lg:p-12 rounded-3xl border border-white/10 backdrop-blur-sm text-center md:text-left">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                        <div class="flex flex-col gap-4">
                            <h2 class="font-bold text-3xl lg:text-5xl tracking-tight text-white">{{ setting('home_cta_title', 'Gabung Bersama KHI') }}</h2>
                            <p class="text-base lg:text-lg text-white/70 leading-relaxed">{{ setting('home_cta_subtitle', 'Jadilah bagian dari penjaga memori bangsa. Nikmati akses eksklusif ke arsip digital, prioritas pendaftaran acara, dan jaringan komunitas sejarah terbesar di Indonesia.') }}</p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-end">
                            <a class="px-8 py-3 bg-primary text-white rounded-full font-semibold text-sm text-center hover:bg-[#c41219] transition-all shadow-lg shadow-primary/20 animate-pulse hover:animate-none" href="{{ route('join') }}" wire:navigate>{{ setting('home_cta_primary_btn_text', 'Daftar Member') }}</a>
                            <a class="px-8 py-3 border border-white/30 text-white rounded-full font-semibold text-sm text-center hover:bg-white/10 transition-all" href="{{ route('join') }}" wire:navigate>{{ setting('home_cta_secondary_btn_text', 'Pelajari Benefit') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</x-layouts.marketing>
