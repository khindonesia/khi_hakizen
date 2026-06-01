@php
    $coverImage = $coverImage ?: url('/og_image.png');
    $categoryLabel = $categoryLabel ?? 'Artikel';
    $authorName = $authorName ?? 'Admin KHI';
    $publishedText = $publishedText ?? '';
    $updatedText = $updatedText ?? '';
    $body = $body ?? '';
    $summary = $summary ?? \Illuminate\Support\Str::limit(strip_tags($body), 180);
    $readingTime = max(1, (int) ceil(str_word_count(strip_tags($body)) / 220));
@endphp

<div class="bg-[#fffafb] min-h-screen font-['Inter'] py-12 md:py-16">
    <x-container>
        <a href="{{ $backHref }}" wire:navigate
            class="inline-flex items-center gap-2 text-sm font-semibold text-[#575e75] hover:text-[#df1c24] mb-8 transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            {{ $backText ?? 'Back' }}
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            <div class="lg:col-span-8 flex flex-col gap-6">
                <div
                    class="relative overflow-hidden rounded-3xl border border-[#E9E9E8] bg-white shadow-sm aspect-[16/9]">
                    <img src="{{ $coverImage }}" alt="{{ $title }}" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/15 to-transparent"></div>

                    <div class="absolute inset-x-0 bottom-0 p-6 md:p-8 text-white">
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-[11px] font-bold uppercase tracking-[0.16em]">
                                {{ $categoryLabel }}
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-[11px] font-bold uppercase tracking-[0.16em]">
                                {{ $readingTime }} min read
                            </span>
                        </div>

                        <h1 class="text-3xl md:text-[52px] font-semibold tracking-[-1.5px] leading-[1.03] max-w-4xl">
                            {{ $title }}
                        </h1>

                        <div class="flex flex-wrap items-center gap-3 mt-5 text-sm text-white/80">
                            <span>Ditulis oleh {{ $authorName }}</span>
                            <span class="w-1 h-1 rounded-full bg-white/50"></span>
                            <span>{{ $publishedText }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-[#E9E9E8] bg-white shadow-sm p-6 md:p-8">
                    <div class="flex flex-wrap gap-3 mb-6">
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-[#E9E9E8] bg-[#f8fafc] px-4 py-2 text-xs font-semibold text-[#37352F]">
                            <span class="material-symbols-outlined text-[16px] text-[#df1c24]">schedule</span>
                            {{ $readingTime }} menit baca
                        </div>

                        @if ($publishedText !== '')
                            <div
                                class="inline-flex items-center gap-2 rounded-full border border-[#E9E9E8] bg-[#f8fafc] px-4 py-2 text-xs font-semibold text-[#37352F]">
                                <span class="material-symbols-outlined text-[16px] text-[#df1c24]">calendar_today</span>
                                Dipublikasikan {{ $publishedText }}
                            </div>
                        @endif

                        @if ($updatedText !== '' && $updatedText !== $publishedText)
                            <div
                                class="inline-flex items-center gap-2 rounded-full border border-[#E9E9E8] bg-[#f8fafc] px-4 py-2 text-xs font-semibold text-[#37352F]">
                                <span class="material-symbols-outlined text-[16px] text-[#df1c24]">update</span>
                                Diperbarui {{ $updatedText }}
                            </div>
                        @endif
                    </div>

                    <div
                        class="prose prose-zinc max-w-none text-[#575e75] prose-headings:text-[#37352F] prose-headings:font-semibold prose-a:text-[#df1c24] prose-strong:text-[#37352F] prose-img:rounded-2xl prose-img:shadow-sm leading-[1.8]">
                        {!! clean($body) !!}
                    </div>
                </div>
            </div>

            <aside class="lg:col-span-4 lg:sticky lg:top-[100px]">
                <div class="bg-white border border-[#E9E9E8] rounded-3xl p-6 md:p-8 shadow-sm flex flex-col gap-6">
                    <div>
                        <h2 class="text-xl font-semibold text-[#37352F] tracking-tight">
                            {{ $sidebarTitle ?? 'Detail Artikel' }}</h2>
                        <p class="text-sm text-[#575e75] mt-1 leading-relaxed">
                            {{ $sidebarDescription ?? 'Informasi ringkas tentang artikel ini.' }}
                        </p>
                    </div>

                    <div class="space-y-4 border-y border-[#E9E9E8] py-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-[#979A9B]">Kategori</p>
                                <p class="text-sm font-semibold text-[#37352F] mt-1">{{ $categoryLabel }}</p>
                            </div>
                            <span class="material-symbols-outlined text-[#df1c24] text-[20px]">sell</span>
                        </div>

                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-[#979A9B]">Penulis</p>
                                <p class="text-sm font-semibold text-[#37352F] mt-1">{{ $authorName }}</p>
                            </div>
                            <span class="material-symbols-outlined text-[#df1c24] text-[20px]">person</span>
                        </div>

                        @if ($publishedText !== '')
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-[#979A9B]">Terbit</p>
                                    <p class="text-sm font-semibold text-[#37352F] mt-1">{{ $publishedText }}</p>
                                </div>
                                <span class="material-symbols-outlined text-[#df1c24] text-[20px]">calendar_month</span>
                            </div>
                        @endif

                        @if ($updatedText !== '' && $updatedText !== $publishedText)
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-[#979A9B]">Diperbarui
                                    </p>
                                    <p class="text-sm font-semibold text-[#37352F] mt-1">{{ $updatedText }}</p>
                                </div>
                                <span class="material-symbols-outlined text-[#df1c24] text-[20px]">update</span>
                            </div>
                        @endif

                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-[#979A9B]">Bacaan</p>
                                <p class="text-sm font-semibold text-[#37352F] mt-1">{{ $readingTime }} menit</p>
                            </div>
                            <span class="material-symbols-outlined text-[#df1c24] text-[20px]">schedule</span>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-[#fff5f5] border border-[#E9E9E8] p-5">
                        <p class="text-sm font-semibold text-[#37352F]">Ringkasan</p>
                        <p class="mt-2 text-sm leading-6 text-[#575e75]">{{ $summary }}</p>
                    </div>
                </div>
            </aside>
        </div>
    </x-container>
</div>
