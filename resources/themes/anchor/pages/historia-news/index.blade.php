<?php
use function Laravel\Folio\name;

name('historia-news');
?>

@php
    $selectedType = request('type');
    $types = \App\Models\Type::all();

    $postQuery = \App\Models\Post::query()
        ->where('status', 'PUBLISHED');

    if ($selectedType) {
        if ($selectedType === 'terbaru') {
            $postQuery->orderByRaw('featured DESC')->latest();
        } elseif ($selectedType === 'terlama') {
            $postQuery->orderByRaw('featured DESC')->oldest();
        } else {
            $postQuery->whereHas('types', fn($q) => $q->where('slug', $selectedType));
            $postQuery->orderByRaw('featured DESC')->latest();
        }
    } else {
        $postQuery->orderByRaw('featured DESC')->latest();
    }

    $posts = $postQuery->paginate(9)->withQueryString();
@endphp

<x-layouts.marketing :seo="[
    'title' => 'Historialita - Komunitas Historia Indonesia',
    'description' => 'Kumpulan artikel Historialita dan pembaruan komunitas dari Komunitas Historia Indonesia.',
    'image' => url('/og_image.png'),
    'type' => 'website',
]">
    <div class="bg-[#F8FAFC] min-h-screen font-['Inter'] pb-20">
        <section class="bg-white border-b border-gray-200/80 py-16 md:py-24 text-center">
            <x-container class="max-w-4xl mx-auto px-6">
                <div
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#df1c24]/10 border border-[#df1c24]/20 text-xs font-bold text-[#df1c24] uppercase tracking-wider mb-6">
                    {{ setting('news_chip', 'Historialita') }}
                </div>
                <h1 class="text-4xl md:text-[56px] font-extrabold text-gray-900 tracking-tight leading-none">
                    {{ setting('news_title', 'Historialita') }}
                </h1>
                <p class="text-gray-600 max-w-2xl text-base md:text-lg mt-6 mx-auto leading-relaxed">
                    {{ setting('news_subtitle', 'Kurasi artikel, kabar komunitas, dan cerita sejarah dari Komunitas Historia Indonesia.') }}
                </p>
            </x-container>
        </section>

        <main class="w-full max-w-[1280px] mx-auto px-6 mt-16">
            <!-- Type Filters -->
            <div class="mb-10 flex flex-wrap gap-2 items-center justify-center">
                <span class="text-xs font-bold text-zinc-500 mr-2">Tipe:</span>
                <a href="{{ request()->fullUrlWithQuery(['type' => null]) }}" wire:navigate 
                   class="rounded-full border px-4 py-1.5 text-xs font-semibold transition-all {{ !$selectedType ? 'border-red-600 bg-red-600 text-white shadow-sm' : 'border-zinc-200 bg-white text-zinc-600 hover:border-red-200 hover:text-red-700' }}">
                    Semua Tipe
                </a>
                @foreach ($types as $type)
                    <a href="{{ request()->fullUrlWithQuery(['type' => $type->slug]) }}" wire:navigate 
                       class="rounded-full border px-4 py-1.5 text-xs font-semibold transition-all {{ $selectedType === $type->slug ? 'border-red-600 bg-red-600 text-white shadow-sm' : 'border-zinc-200 bg-white text-zinc-600 hover:border-red-200 hover:text-red-700' }}">
                        {{ $type->name }}
                    </a>
                @endforeach
            </div>

            @if ($posts->isEmpty())
                <div
                    class="rounded-2xl border border-dashed border-gray-200 bg-white px-6 py-16 text-center text-gray-600">
                    Belum ada artikel Historialita dengan tipe ini.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @include('theme::partials.blog.posts-loop', ['posts' => $posts])
                </div>

                <div class="mt-16 flex justify-center">
                    {{ $posts->links('theme::partials.pagination') }}
                </div>
            @endif
        </main>
    </div>
</x-layouts.marketing>
