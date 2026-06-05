<?php
use function Laravel\Folio\name;

name('historia-news');
?>

@php
    $posts = \Wave\Post::query()
        ->where('status', 'PUBLISHED')
        ->orderByRaw('featured DESC') // Mengutamakan featured (true/1 sebelum false/0)
        ->latest() // Sama dengan orderBy('created_at', 'desc')
        ->paginate(9);
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
            @if ($posts->isEmpty())
                <div
                    class="rounded-2xl border border-dashed border-gray-200 bg-white px-6 py-16 text-center text-gray-600">
                    Belum ada artikel Historialita saat ini.
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
