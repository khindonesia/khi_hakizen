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

                    <!-- Share Dropdown -->
                    <div x-data="{ open: false, copied: false }" class="relative w-full">
                        <button type="button" 
                                @click="open = !open"
                                class="w-full bg-white border border-[#E9E9E8] text-[#37352F] text-sm font-semibold py-3.5 rounded-xl hover:bg-zinc-50 transition-all flex items-center justify-center gap-1.5 shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">share</span>
                            Share with Friends
                        </button>

                        <div x-show="open" 
                             @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                             class="absolute bottom-full left-0 right-0 z-50 mb-3 p-3 bg-white border border-[#E9E9E8] rounded-xl shadow-xl flex flex-col gap-1.5"
                             x-cloak>
                            
                            <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider px-2.5 pb-1.5 border-b border-zinc-100 text-left">Share Book</p>
                            
                            <!-- WhatsApp -->
                            <a href="https://api.whatsapp.com/send?text={{ rawurlencode($book->title) }}%20{{ rawurlencode(request()->url()) }}" 
                               target="_blank" 
                               class="flex items-center gap-2.5 px-2.5 py-2 text-xs font-medium text-zinc-700 hover:bg-emerald-50 hover:text-emerald-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-emerald-500 fill-current" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.713-1.455L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.625 1.451 5.48-.002 9.932-4.453 9.935-9.93.001-2.652-1.03-5.144-2.905-7.022C16.427 1.775 13.935.744 11.997.744 6.513.744 2.06 5.192 2.057 10.677c-.002 1.503.398 2.972 1.16 4.29l-.994 3.63 3.731-.978zm11.567-7.25c-.247-.124-1.47-.726-1.698-.81-.228-.084-.393-.124-.558.124-.165.247-.638.81-.782.975-.145.166-.29.185-.537.062-.247-.125-1.045-.385-1.99-1.23-.738-.657-1.235-1.47-1.38-1.717-.146-.247-.015-.38.11-.504.112-.111.247-.29.37-.435.124-.145.165-.247.247-.412.083-.165.042-.31-.02-.435-.063-.124-.558-1.346-.763-1.84-.2-.48-.401-.416-.558-.424-.144-.007-.31-.008-.475-.008-.166 0-.435.062-.663.31-.228.247-.87.85-.87 2.075s.89 2.41 1.012 2.575c.125.166 1.75 2.673 4.24 3.743.59.254 1.053.405 1.41.52.597.19 1.14.163 1.57.1.48-.07 1.47-.6 1.677-1.18.207-.58.207-1.077.145-1.18-.062-.102-.228-.142-.475-.266z"/>
                                </svg>
                                <span>WhatsApp</span>
                            </a>

                            <!-- Facebook -->
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode(request()->url()) }}" 
                               target="_blank" 
                               class="flex items-center gap-2.5 px-2.5 py-2 text-xs font-medium text-zinc-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-blue-600 fill-current" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                                <span>Facebook</span>
                            </a>

                            <!-- Twitter/X -->
                            <a href="https://twitter.com/intent/tweet?text={{ rawurlencode($book->title) }}&url={{ rawurlencode(request()->url()) }}" 
                               target="_blank" 
                               class="flex items-center gap-2.5 px-2.5 py-2 text-xs font-medium text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-zinc-900 fill-current" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                                <span>Twitter / X</span>
                            </a>

                            <!-- Telegram -->
                            <a href="https://t.me/share/url?url={{ rawurlencode(request()->url()) }}&text={{ rawurlencode($book->title) }}" 
                               target="_blank" 
                               class="flex items-center gap-2.5 px-2.5 py-2 text-xs font-medium text-zinc-700 hover:bg-sky-50 hover:text-sky-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-sky-500 fill-current" viewBox="0 0 24 24">
                                    <path d="M12 0C5.37 0 0 5.37 0 12s5.37 12 12 12 12-5.37 12-12S18.63 0 12 0zm5.56 8.61l-1.88 8.87c-.14.63-.51.79-1.04.49l-2.87-2.11-1.38 1.33c-.15.15-.28.28-.57.28l.2-2.92 5.31-4.8c.23-.21-.05-.32-.36-.11L10.3 13.06l-2.83-.89c-.61-.19-.63-.61.13-.91l11.07-4.27c.51-.19.96.11.89.62z"/>
                                </svg>
                                <span>Telegram</span>
                            </a>

                            <div class="h-px bg-zinc-100 my-1"></div>

                            <!-- Copy Link -->
                            <button type="button" 
                                    @click="navigator.clipboard.writeText(window.location.href); copied = true; setTimeout(() => { copied = false; open = false; }, 2000)"
                                    class="w-full flex items-center justify-between px-2.5 py-2 text-xs font-medium text-zinc-700 hover:bg-zinc-50 rounded-lg transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-[16px] text-zinc-500" x-show="!copied">content_copy</span>
                                    <span class="material-symbols-outlined text-[16px] text-emerald-600" x-show="copied" x-cloak>check</span>
                                    <span x-text="copied ? 'Copied!' : 'Copy Link'"></span>
                                </div>
                                <span class="text-[9px] font-bold text-zinc-400 uppercase tracking-widest bg-zinc-100 px-1.5 py-0.5 rounded" x-show="!copied">Ctrl+C</span>
                            </button>
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
