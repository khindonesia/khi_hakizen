<?php
    use function Laravel\Folio\{middleware, name};
    name('events.show');
?>

@php
    $event = null;

    // Retrieve the active event by its unique slug.
    if (isset($slug) && $slug !== '') {
        $event = \App\Models\Event::published()
            ->where('slug', $slug)
            ->with(['author'])
            ->first();

        if (! app()->runningInConsole()) {
            abort_unless($event, 404);
        }
    }

    $isUpcoming = $event ? $event->start_datetime->isFuture() : false;
    $isOngoing  = $event ? ($event->start_datetime->isPast() && $event->end_datetime->isFuture()) : false;

    $isAlreadyRegistered = false;
    if (auth()->check() && $event) {
        $isAlreadyRegistered = \Illuminate\Support\Facades\DB::table('event_user')
            ->where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->exists();
    }
@endphp

<x-layouts.marketing :seo="[
    'title' => ($event?->title ?? 'Event') . ' - KHI Events',
    'description' => $event ? Str::limit(strip_tags($event->body), 155) : '',
    'image' => $event?->image ? Storage::url(ltrim($event->image, '/')) : url('/og_image.png'),
    'type' => 'article',
]">
<div class="bg-[#fffafb] min-h-screen font-['Inter'] py-12 md:py-16">
        <x-container>
            
            <!-- Back Button Row -->
            <a href="{{ route('events') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold text-[#575e75] hover:text-[#df1c24] mb-8 transition-colors">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Back to events
            </a>

            <!-- Event Structure Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                
                <!-- Left Column: Image and Main Body -->
                <div class="lg:col-span-8 flex flex-col gap-6">
                    
                    <!-- Cover Image -->
                    <div class="relative bg-zinc-100 rounded-3xl overflow-hidden shadow-sm border border-[#E9E9E8] aspect-[16/9]">
                        @php
                            $eventImage = $event?->image
                                ? Storage::url(ltrim($event->image, '/'))
                                : 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=1200&auto=format&fit=crop';
                        @endphp
                        <img src="{{ $eventImage }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                    </div>

                    <!-- Header Titles & Metadata -->
                    <div>
                        <!-- Category/Status Tag -->
                        <div class="flex items-center gap-2 mb-3">
                            <span class="bg-[#f9f1fc] text-[#df1c24] text-xs font-bold px-3 py-0.5 rounded-full uppercase tracking-wider">
                                KHI Heritage Program
                            </span>
                            @if($isOngoing)
                                <span class="bg-indigo-50 text-indigo-700 text-xs font-bold px-3 py-0.5 rounded-full uppercase tracking-wider">Ongoing</span>
                            @elseif($isUpcoming)
                                <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-0.5 rounded-full uppercase tracking-wider">Upcoming</span>
                            @else
                                <span class="bg-zinc-100 text-zinc-600 text-xs font-bold px-3 py-0.5 rounded-full uppercase tracking-wider">Past Event</span>
                            @endif
                        </div>

                        <h1 class="text-2xl md:text-[36px] font-semibold text-[#37352F] tracking-tight leading-tight mb-4">
                            {{ $event->title }}
                        </h1>

                        <!-- Author & Date Info bar -->
                        <div class="flex items-center gap-3 border-y border-[#E9E9E8]/50 py-3 mt-4">
                            <div class="w-8 h-8 rounded-full bg-[#fef2f2] flex items-center justify-center text-[#df1c24] font-bold text-xs">
                                {{ substr($event->author->name ?? 'KHI', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-xs text-[#37352F] font-semibold">Organized by {{ $event->author->name ?? 'KHI Team' }}</p>
                                <p class="text-[10px] text-[#979A9B] uppercase tracking-wider font-semibold mt-0.5">Community Curator</p>
                            </div>
                        </div>
                    </div>

                    <!-- Event Main Article / Body Content -->
                    <div class="prose prose-zinc max-w-none text-[#575e75] leading-[1.7] mt-2 prose-headings:text-[#37352F] prose-headings:font-semibold prose-a:text-[#df1c24] prose-strong:text-[#37352F] font-normal">
                        {!! clean($event->body) !!}
                    </div>

                </div>

                <!-- Right Column: Info Details Sidebar -->
                <aside class="lg:col-span-4 lg:sticky lg:top-[100px]">
                    <div class="bg-[#fff5f5] rounded-2xl p-6 md:p-8 shadow-sm border border-[#E9E9E8] flex flex-col gap-6">
                        
                        <div>
                            <h2 class="text-xl font-semibold text-[#37352F] tracking-tight">Event Details</h2>
                            <p class="text-xs text-[#979A9B] mt-0.5">Please review the agenda and timing details below.</p>
                        </div>

                        <div class="space-y-4 py-4 border-y border-[#E9E9E8]">
                            <!-- Calendar row -->
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-[#df1c24] text-[20px] mt-0.5" style="font-variation-settings: 'FILL' 1;">calendar_today</span>
                                <div>
                                    <p class="text-xs font-semibold text-[#37352F]">Date</p>
                                    <p class="text-xs text-[#575e75] mt-0.5">{{ $event->start_datetime->format('l, d F Y') }}</p>
                                </div>
                            </div>

                            <!-- Schedule row -->
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-[#df1c24] text-[20px] mt-0.5" style="font-variation-settings: 'FILL' 1;">schedule</span>
                                <div>
                                    <p class="text-xs font-semibold text-[#37352F]">Time & Hours</p>
                                    <p class="text-xs text-[#575e75] mt-0.5">
                                        {{ $event->start_datetime->format('H:i') }} - {{ $event->end_datetime->format('H:i') }} WIB
                                    </p>
                                </div>
                            </div>

                            <!-- Venue row -->
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-[#df1c24] text-[20px] mt-0.5" style="font-variation-settings: 'FILL' 1;">location_on</span>
                                <div>
                                    <p class="text-xs font-semibold text-[#37352F]">Location</p>
                                    <p class="text-xs text-[#575e75] mt-0.5">{{ $event->location ?? 'Kota Tua Jakarta, Special Capital Region of Jakarta' }}</p>
                                </div>
                            </div>

                            <!-- Cost row -->
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-[#df1c24] text-[20px] mt-0.5" style="font-variation-settings: 'FILL' 1;">confirmation_number</span>
                                <div>
                                    <p class="text-xs font-semibold text-[#37352F]">Admission</p>
                                    @if(($event->type ?? 'FREE') === 'PAID')
                                        <p class="text-xs text-amber-600 font-semibold mt-0.5">Rp {{ number_format($event->price, 0, ',', '.') }}</p>
                                    @else
                                        <p class="text-xs text-[#107F5B] font-semibold mt-0.5">Free Registration</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Registration / Share Actions -->
                        <div class="flex flex-col gap-2.5">
                            @if($isUpcoming || $isOngoing)
                                @if($isAlreadyRegistered)
                                    <a href="{{ route('dashboard.events') }}" wire:navigate
                                       class="w-full bg-emerald-600 hover:bg-emerald-700 text-[#fff] text-center text-sm font-semibold py-3.5 rounded-lg transition-all flex items-center justify-center gap-1.5 shadow-sm">
                                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                        You are Registered (View Ticket)
                                    </a>
                                @else
                                    <a href="{{ route('events.checkout', ['event' => $event->id]) }}" wire:navigate
                                       class="w-full bg-[#df1c24] hover:bg-opacity-95 text-center text-sm font-semibold py-3.5 rounded-lg transition-all flex items-center justify-center gap-1.5 shadow-sm"
                                       style="color: white !important;">
                                        <span class="material-symbols-outlined text-[18px]" style="color: white !important;">add_circle</span>
                                        RSVP & Join Event
                                    </a>
                                @endif
                            @else
                                <button type="button" disabled
                                        class="w-full bg-zinc-200 text-zinc-400 text-sm font-semibold py-3.5 rounded-lg flex items-center justify-center gap-1.5 cursor-not-allowed">
                                    <span class="material-symbols-outlined text-[18px]">event_busy</span>
                                    Event Has Ended
                                </button>
                            @endif

                            <!-- Share Dropdown -->
                            <div x-data="{ open: false, copied: false }" class="relative w-full">
                                <button type="button" 
                                        @click="open = !open"
                                        class="w-full bg-white border border-[#E9E9E8] text-[#37352F] text-sm font-semibold py-3.5 rounded-lg hover:bg-zinc-50 transition-all flex items-center justify-center gap-1.5">
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
                                    
                                    <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider px-2.5 pb-1.5 border-b border-zinc-100 text-left">Share Event</p>
                                    
                                    <!-- WhatsApp -->
                                    <a href="https://api.whatsapp.com/send?text={{ rawurlencode($event->title) }}%20{{ rawurlencode(request()->url()) }}" 
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
                                    <a href="https://twitter.com/intent/tweet?text={{ rawurlencode($event->title) }}&url={{ rawurlencode(request()->url()) }}" 
                                       target="_blank" 
                                       class="flex items-center gap-2.5 px-2.5 py-2 text-xs font-medium text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 rounded-lg transition-colors">
                                        <svg class="w-4 h-4 text-zinc-900 fill-current" viewBox="0 0 24 24">
                                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                        </svg>
                                        <span>Twitter / X</span>
                                    </a>

                                    <!-- Telegram -->
                                    <a href="https://t.me/share/url?url={{ rawurlencode(request()->url()) }}&text={{ rawurlencode($event->title) }}" 
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
                </aside>

            </div>

        </x-container>
    </div>
</x-layouts.marketing>
