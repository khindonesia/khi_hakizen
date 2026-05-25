<?php
    use function Laravel\Folio\{middleware, name};
    name('events.show');
?>

@php
    $event = null;

    // Retrieve the active event by its unique slug.
    if (isset($slug) && $slug !== '') {
        $event = \App\Models\Event::where('slug', $slug)
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
    @push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
    @endpush

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
                        {!! $event->body !!}
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

                            <button type="button" 
                                    onclick="navigator.clipboard.writeText(window.location.href); alert('Event link copied to clipboard!')"
                                    class="w-full bg-white border border-[#E9E9E8] text-[#37352F] text-sm font-semibold py-3.5 rounded-lg hover:bg-zinc-50 transition-all flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">share</span>
                                Share with Friends
                            </button>
                        </div>

                    </div>
                </aside>

            </div>

        </x-container>
    </div>
</x-layouts.marketing>
