<?php
    use function Laravel\Folio\{name};
    name('library.book');
?>

@php
    $book = \App\Models\Ebook::where('slug', $slug)->first();
    if (!$book && !app()->runningInConsole()) {
        abort(404);
    }
@endphp

<x-layouts.marketing :seo="[
    'title' => ($book->title ?? 'Publication') . ' - KHI Digital Library',
    'description' => Str::limit(strip_tags($book->description ?? ''), 155),
    'image' => ($book && $book->cover_image) ? (str_starts_with($book->cover_image, 'http') ? $book->cover_image : Storage::url(ltrim($book->cover_image, '/'))) : url('/og_image.png'),
    'type' => 'book',
]">
<div class="bg-[#fffafb] min-h-screen font-['Inter'] py-12 md:py-16">
        <x-container class="px-6">

            <!-- Breadcrumbs / Back navigation link -->
            <div class="flex items-center gap-2 text-xs font-semibold text-[#979A9B] mb-8 uppercase tracking-wider">
                <a href="{{ route('library') }}" wire:navigate class="hover:text-[#df1c24] transition">E-Library</a>
                <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                <span class="text-[#37352F] truncate max-w-[200px] sm:max-w-none">{{ $book->title }}</span>
            </div>

            <!-- Two-Column Layout Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
                
                <!-- Left Main Content Column (8 Cols) -->
                <div class="lg:col-span-8 space-y-8">
                    
                    <div class="bg-white border border-[#E9E9E8] rounded-3xl p-6 md:p-8 shadow-xs space-y-8">
                        
                        <!-- Book Cover Header Area -->
                        <div class="flex flex-col md:flex-row gap-8 items-start md:items-center">
                            
                            <!-- Cream Book Card Container -->
                            <div class="bg-[#F8F7F4] border border-[#E9E9E8] rounded-2xl p-6 flex items-center justify-center w-full md:w-auto shrink-0 shadow-xs book-cover-container">
                                @php
                                    $coverUrl = $book->cover_image
                                        ? (str_starts_with($book->cover_image, 'http') ? $book->cover_image : Storage::url(ltrim($book->cover_image, '/')))
                                        : 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=400&auto=format&fit=crop';
                                @endphp
                                <img src="{{ $coverUrl }}" alt="{{ $book->title }}" class="block w-auto h-auto max-w-full max-h-[340px] object-contain shadow-[0_12px_28px_rgba(0,0,0,0.18)] rounded-lg transition-transform duration-300 hover:scale-103">
                            </div>

                            <!-- Title & Editorial Specs -->
                            <div class="space-y-4 flex-grow">
                                <div class="inline-flex px-3 py-1 bg-[#df1c24]/10 text-[#df1c24] text-[10px] font-bold uppercase tracking-wider rounded-full border border-[#df1c24]/10">
                                    Historical Archive
                                </div>
                                <h1 class="text-2xl md:text-[34px] font-bold text-[#37352F] tracking-tight leading-tight">
                                    {{ $book->title }}
                                </h1>
                                
                                <!-- Meta Details list -->
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs font-semibold text-[#575e75]">
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px] text-[#979A9B]">person</span>
                                        <span>{{ $book->author }}</span>
                                    </div>
                                    <span class="text-zinc-300 hidden sm:inline">•</span>
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px] text-[#979A9B]">calendar_month</span>
                                        <span>{{ $book->created_at->format('Y') }}</span>
                                    </div>
                                    <span class="text-zinc-300 hidden sm:inline">•</span>
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px] text-[#979A9B]">menu_book</span>
                                        <span>320 Pages</span>
                                    </div>
                                    <span class="text-zinc-300 hidden sm:inline">•</span>
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px] text-[#979A9B]">language</span>
                                        <span>Indonesian</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Book Detailed Prose Description -->
                        <div class="border-t border-[#E9E9E8] pt-8 mt-4">
                            <h2 class="text-sm font-bold uppercase text-[#37352F] tracking-wider mb-3">Sinopsis & Deskripsi</h2>
                            <div class="prose max-w-none text-sm md:text-base text-[#37352F]/80 leading-[1.7] whitespace-pre-line font-sans">
                                {{ $book->description }}
                            </div>
                        </div>

                        <!-- Action Buttons and specifications bar -->
                        <div class="border-t border-[#E9E9E8] pt-8 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                @guest
                                    <!-- Guest Action: Join to Download -->
                                    <a href="{{ route('join') }}" wire:navigate class="bg-[#df1c24] hover:bg-[#c41219] text-white font-semibold px-6 py-3 rounded-xl transition shadow-xs flex items-center justify-center gap-2 text-sm">
                                        <span class="material-symbols-outlined text-[18px]">workspace_premium</span>
                                        <span>Join KHI to Download</span>
                                    </a>
                                @else
                                    <!-- Member Action: Download PDF -->
                                    @php
                                        $downloadUrl = $book->ebook_file
                                            ? Storage::url(ltrim($book->ebook_file, '/'))
                                            : '#';
                                    @endphp
                                    @if($book->ebook_file && $book->ebook_file !== '#')
                                        <a href="{{ $downloadUrl }}" target="_blank" rel="noopener noreferrer" class="bg-[#df1c24] hover:bg-[#c41219] text-white font-semibold px-6 py-3 rounded-xl transition shadow-xs flex items-center justify-center gap-2 text-sm">
                                            <span class="material-symbols-outlined text-[18px]">download</span>
                                            <span>Download PDF Edition</span>
                                        </a>
                                    @else
                                        <button disabled class="bg-zinc-100 text-zinc-400 font-semibold px-6 py-3 rounded-xl flex items-center justify-center gap-2 text-sm cursor-not-allowed">
                                            <span class="material-symbols-outlined text-[18px]">unpublished</span>
                                            <span>File Pending Upload</span>
                                        </button>
                                    @endif
                                @endguest

                            </div>

                            <!-- PDF Details indicator tag -->
                            <span class="text-xs text-[#979A9B] font-semibold flex items-center gap-1 select-none">
                                PDF Format • 14.2 MB • High Resolution Scans
                            </span>
                        </div>

                    </div>
                </div>

                <!-- Right Sidebar Column (4 Cols) -->
                <div class="lg:col-span-4 sticky top-24 space-y-6">
                    
                    <!-- Why Join KHI Promotion Box -->
                    <div class="bg-[#FFF9F5] border border-[#E9E9E8] rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
                        
                        <!-- Purple Star Icon -->
                        <div class="flex items-center gap-2 text-[#df1c24]">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
                            <h3 class="text-lg font-bold text-[#37352F] tracking-tight">Why Join KHI?</h3>
                        </div>

                        <p class="text-sm text-[#37352F]/70 leading-[1.6]">
                            Unlock the full potential of our historical archives by becoming a member. Your support helps us preserve the past for modern minds.
                        </p>

                        <!-- Feature List -->
                        <div class="space-y-4 pt-2">
                            <!-- Free Access -->
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-[#107F5B] mt-0.5 text-[20px]">check_circle</span>
                                <div>
                                    <h4 class="text-xs font-bold text-[#37352F] uppercase tracking-wide">Free Archive Access</h4>
                                    <p class="text-xs text-[#37352F]/65 mt-0.5 leading-relaxed">Download thousands of high-res documents and books.</p>
                                </div>
                            </div>
                            
                            <!-- Exclusive Events -->
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-[#107F5B] mt-0.5 text-[20px]">check_circle</span>
                                <div>
                                    <h4 class="text-xs font-bold text-[#37352F] uppercase tracking-wide">Exclusive Events</h4>
                                    <p class="text-xs text-[#37352F]/65 mt-0.5 leading-relaxed">Priority booking for heritage walks and seminars.</p>
                                </div>
                            </div>
                            
                            <!-- Scholarly Community -->
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-[#107F5B] mt-0.5 text-[20px]">check_circle</span>
                                <div>
                                    <h4 class="text-xs font-bold text-[#37352F] uppercase tracking-wide">Scholarly Community</h4>
                                    <p class="text-xs text-[#37352F]/65 mt-0.5 leading-relaxed">Engage in private discussions with historians.</p>
                                </div>
                            </div>
                        </div>

                        <!-- CTA Button to plans page -->
                        <div class="pt-4 border-t border-[#E9E9E8]">
                            <a href="{{ route('join') }}" wire:navigate class="w-full text-center border border-[#df1c24] text-[#df1c24] hover:bg-[#df1c24]/5 font-semibold py-3.5 px-4 rounded-xl transition duration-200 text-sm flex items-center justify-center gap-1.5 shadow-xs">
                                <span>Join KHI</span>
                                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                            </a>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Related Readings Section -->
            @php
                $related = \App\Models\Ebook::published()
                    ->where('id', '!=', $book->id)
                    ->latest()
                    ->take(4)
                    ->get();
            @endphp
            @if($related->count() > 0)
                <div class="mt-20 border-t border-[#E9E9E8] pt-12 space-y-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl md:text-2xl font-bold text-[#37352F] tracking-tight">Related Reading</h2>
                        <a href="{{ route('library') }}" wire:navigate class="text-xs font-bold text-[#df1c24] hover:underline flex items-center gap-1">
                            Browse All Archive
                            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                        </a>
                    </div>

                    <!-- Related Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                        @foreach($related as $relBook)
                            @php
                                $relCover = $relBook->cover_image
                                    ? (str_starts_with($relBook->cover_image, 'http') ? $relBook->cover_image : Storage::url(ltrim($relBook->cover_image, '/')))
                                    : 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=400&auto=format&fit=crop';
                            @endphp
                            
                            <a href="{{ route('library.book', ['slug' => $relBook->slug]) }}" wire:navigate class="group block h-full">
                                <div class="bg-white border border-[#E9E9E8] rounded-2xl overflow-hidden shadow-xs hover:shadow-[0_12px_36px_rgba(0,0,0,0.04)] transition-all duration-300 flex flex-col justify-between h-full">
                                    <div>
                                        <div class="bg-[#0f172a] aspect-square flex items-center justify-center p-6 relative overflow-hidden">
                                            <div class="relative shadow-[0_8px_20px_rgba(0,0,0,0.3)] rounded overflow-hidden max-h-[120px] aspect-[3/4] z-10 transition-transform duration-500 group-hover:scale-103 group-hover:-translate-y-0.5">
                                                <img src="{{ $relCover }}" alt="{{ $relBook->title }}" class="w-full h-full object-cover">
                                            </div>
                                        </div>
                                        <div class="p-5 space-y-1">
                                            <h4 class="text-sm font-bold text-[#37352F] tracking-tight leading-snug line-clamp-2 group-hover:text-[#df1c24] transition-colors">
                                                {{ $relBook->title }}
                                            </h4>
                                            <p class="text-[11px] text-[#979A9B] font-semibold">{{ $relBook->author }}</p>
                                        </div>
                                    </div>
                                    <div class="px-5 pb-5 pt-1">
                                        <span class="inline-flex items-center gap-0.5 text-[10px] font-bold text-[#df1c24] group-hover:underline">
                                            View Details
                                            <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </x-container>
    </div>
</x-layouts.marketing>
