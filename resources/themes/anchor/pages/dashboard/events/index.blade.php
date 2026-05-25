<?php
    use Illuminate\View\View;
    use function Laravel\Folio\{middleware, name, render};

    middleware('auth');
    name('dashboard.events');

    render(function (View $view): View {
        $filter = request('filter');
        $search = request('search');
        $user = auth()->user();

        // Query only events owned by user OR registered by user
        $query = \App\Models\Event::published()
            ->with(['author'])
            ->with(['users' => function ($uq) use ($user): void {
                $uq->where('users.id', $user->id);
            }])
            ->where(function ($q) use ($user): void {
                $q->where('author_id', $user->id)
                  ->orWhereHas('users', function ($uq) use ($user): void {
                      $uq->where('users.id', $user->id);
                  });
            });

        if ($search) {
            $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('body', 'like', '%' . $search . '%');
            });
        }

        switch ($filter) {
            case 'ongoing':
                $query->ongoing();
                break;
            case 'past':
                $query->past()->orderBy('start_datetime', 'desc');
                break;
            case 'upcoming':
                $query->upcoming()->orderBy('start_datetime', 'asc');
                break;
            default:
                $query->orderBy('start_datetime', 'desc');
                break;
        }

        return $view->with([
            'filter' => $filter,
            'search' => $search,
            'events' => $query->paginate(6),
        ]);
    });
?>

<x-layouts.app>
    @push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        /* Custom styles matching premium Google Stitch/Notion design system */
        .stitch-chip {
            background-color: #FFF9E6;
            color: #B28200;
            border: 1px solid #FFEBB3;
            border-radius: 9999px;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .dark .stitch-chip {
            background-color: rgba(178, 130, 0, 0.1);
            color: #FFD566;
            border-color: rgba(178, 130, 0, 0.2);
        }
        .stitch-panel {
            background: #FFFFFF;
            border: 1px solid #E9E9E8;
            box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.02), 0px 4px 16px rgba(0, 0, 0, 0.01);
            border-radius: 20px;
        }
        .dark .stitch-panel {
            background: #18181B;
            border-color: #27272A;
            box-shadow: none;
        }
        .bg-card-tint-peach { background-color: #FFF0EA; }
        .bg-card-tint-sky { background-color: #EBF5FF; }
        .bg-card-tint-mint { background-color: #EBF9F4; }
        .bg-card-tint-cream { background-color: #FDFCF7; }
        .bg-card-tint-lavender { background-color: #fff5f5; }
    </style>
    @endpush

    <div class="max-w-[1200px] mx-auto space-y-8" 
         x-data="{ showModal: false, activeEvent: null }"
         x-init="window.addEventListener('keydown', (e) => { if(e.key === 'Escape') showModal = false; })">
        
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="stitch-chip mb-3 inline-flex">
                    <span class="material-symbols-outlined text-[14px]">event_upcoming</span>
                    Historical Gatherings
                </div>
                <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-zinc-900 dark:text-white leading-tight">
                    KHI Community Events
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1.5">
                    Participate in our interactive walking tours, webinars, and exclusive community gatherings.
                </p>
            </div>
        </div>

        <!-- Filters & Search Toolbar -->
        <div class="stitch-panel p-4 flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Tabs -->
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                <a href="{{ route('dashboard.events', ['search' => $search]) }}" wire:navigate 
                   class="rounded-full px-4 py-2 text-xs font-semibold transition-all {{ $filter === null ? 'bg-[#df1c24] text-white' : 'border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-650 dark:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600' }}">
                    All Events
                </a>
                <a href="{{ route('dashboard.events', ['filter' => 'upcoming', 'search' => $search]) }}" wire:navigate 
                   class="rounded-full px-4 py-2 text-xs font-semibold transition-all {{ $filter === 'upcoming' ? 'bg-[#df1c24] text-white' : 'border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-650 dark:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600' }}">
                    Upcoming
                </a>
                <a href="{{ route('dashboard.events', ['filter' => 'ongoing', 'search' => $search]) }}" wire:navigate 
                   class="rounded-full px-4 py-2 text-xs font-semibold transition-all {{ $filter === 'ongoing' ? 'bg-[#df1c24] text-white' : 'border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-650 dark:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600' }}">
                    Ongoing
                </a>
                <a href="{{ route('dashboard.events', ['filter' => 'past', 'search' => $search]) }}" wire:navigate 
                   class="rounded-full px-4 py-2 text-xs font-semibold transition-all {{ $filter === 'past' ? 'bg-[#df1c24] text-white' : 'border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-650 dark:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600' }}">
                    Past Events
                </a>
            </div>

            <!-- Search -->
            <form action="{{ route('dashboard.events') }}" method="GET" class="flex w-full md:w-80 items-center rounded-2xl border border-zinc-200 dark:border-zinc-750 bg-white dark:bg-zinc-850 px-3 py-2 transition-all focus-within:border-zinc-300">
                @if($filter)
                    <input type="hidden" name="filter" value="{{ $filter }}">
                @endif
                <span class="material-symbols-outlined mr-2 text-[20px] text-zinc-400">search</span>
                <input type="text" name="search" placeholder="Search events..." value="{{ $search }}"
                       class="w-full border-none bg-transparent p-0 text-sm text-zinc-900 dark:text-white placeholder:text-zinc-400 focus:outline-none focus:ring-0">
                @if($search)
                    <a href="{{ route('dashboard.events', ['filter' => $filter]) }}" class="text-zinc-400 hover:text-zinc-600 ml-1.5 flex items-center">
                        <span class="material-symbols-outlined text-[16px]">close</span>
                    </a>
                @endif
            </form>
        </div>

        <!-- Events Grid -->
        @if($events->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($events as $event)
                    @php
                        $eventImage = $event->image
                            ? Storage::url(ltrim($event->image, '/'))
                            : 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=600&auto=format&fit=crop';
                        
                        $isUpcoming = $event->start_datetime->isFuture();
                        $isOngoing = $event->start_datetime->isPast() && $event->end_datetime->isFuture();
                        
                        $registration = $event->users->first();
                        $isActiveRegistration = $registration && $registration->pivot->status === 'active';
                    @endphp
                    <div class="stitch-panel flex flex-col justify-between overflow-hidden hover:border-[#df1c24]/35 transition duration-200 relative group">
                        
                        <!-- Floating Calendar Card -->
                        <div class="absolute left-4 top-4 z-10 min-w-[54px] rounded-2xl border border-zinc-200/50 bg-white/95 dark:bg-zinc-800/95 p-2 text-center shadow-sm backdrop-blur-md">
                            <span class="text-[9px] font-bold uppercase leading-none tracking-wider text-[#df1c24] dark:text-primary-fixed block">{{ $event->start_datetime->format('M') }}</span>
                            <span class="mt-1 text-lg font-bold leading-none text-zinc-900 dark:text-white block">{{ $event->start_datetime->format('d') }}</span>
                        </div>

                        <!-- Card Cover Image -->
                        <div class="h-44 bg-zinc-100 overflow-hidden relative">
                            <img src="{{ $eventImage }}" alt="{{ $event->title }}" class="w-full h-full object-cover group-hover:scale-102 transition duration-300">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                        </div>

                        <!-- Content -->
                        <div class="p-5 flex-grow flex flex-col justify-between">
                            <div>
                                <!-- Tag Badges -->
                                <div class="mb-2.5 flex items-center gap-2">
                                    @if($isOngoing)
                                        <span class="rounded-full bg-red-50 dark:bg-blue-900/20 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-red-700 dark:text-blue-300">Ongoing</span>
                                    @elseif($isUpcoming)
                                        <span class="rounded-full bg-emerald-50 dark:bg-emerald-900/20 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Upcoming</span>
                                    @else
                                        <span class="rounded-full bg-zinc-100 dark:bg-zinc-800 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-zinc-650 dark:text-zinc-400">Past Event</span>
                                    @endif

                                    @if($event->type === 'PAID')
                                        <span class="rounded-full bg-amber-50 dark:bg-amber-900/20 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">Berbayar</span>
                                    @else
                                        <span class="rounded-full bg-emerald-50 dark:bg-emerald-900/20 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Gratis</span>
                                    @endif
                                </div>

                                <!-- Title -->
                                <h3 class="text-base font-bold text-zinc-900 dark:text-white leading-snug line-clamp-1">
                                    {{ $event->title }}
                                </h3>

                                <!-- Body description -->
                                <p class="text-xs text-zinc-550 dark:text-zinc-400 mt-2 line-clamp-2 leading-relaxed">
                                    {{ Str::limit(strip_tags($event->body), 100) }}
                                </p>
                            </div>

                            <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-750 flex items-center justify-between">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5 text-[11px] text-zinc-500 dark:text-zinc-400">
                                        <span class="material-symbols-outlined text-[13px]">schedule</span>
                                        <span>{{ $event->start_datetime->format('H:i') }} - {{ $event->end_datetime->format('H:i') }} WIB</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-[11px] text-zinc-500 dark:text-zinc-400">
                                        <span class="material-symbols-outlined text-[13px]">location_on</span>
                                        <span>Kepulauan Seribu, Jakarta</span>
                                    </div>
                                </div>

                                @if($isActiveRegistration)
                                    <a href="{{ route('dashboard.events.ticket', $event->id) }}" target="_blank"
                                       class="bg-[#df1c24] hover:bg-[#df1c24]/90 text-white text-xs font-bold px-3 py-1.5 rounded-lg border border-transparent transition duration-200 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">confirmation_number</span>
                                        Cetak Tiket
                                    </a>
                                @else
                                    <button type="button" 
                                            @click="showModal = true; activeEvent = {
                                                title: '{{ addslashes($event->title) }}',
                                                body: '{{ addslashes(str_replace("\n", "", $event->body)) }}',
                                                image: '{{ $eventImage }}',
                                                start_datetime: '{{ $event->start_datetime->format('d M Y, H:i') }}',
                                                end_datetime: '{{ $event->end_datetime->format('H:i') }} WIB',
                                                dateFormatted: '{{ $event->start_datetime->format('l, d F Y') }}',
                                                organizer: '{{ addslashes($event->author->name ?? "Admin KHI") }}',
                                                isUpcoming: {{ $isUpcoming ? 'true' : 'false' }},
                                                isOngoing: {{ $isOngoing ? 'true' : 'false' }},
                                                type: '{{ $event->type }}',
                                                priceFormatted: '{{ $event->type === "PAID" ? "Rp " . number_format($event->price, 0, ",", ".") : "Pendaftaran Gratis" }}',
                                                isActiveRegistration: false,
                                                ticketUrl: ''
                                            }"
                                            class="bg-[#fff5f5] dark:bg-zinc-800 hover:bg-[#df1c24] hover:text-white dark:hover:bg-[#df1c24] text-[#df1c24] dark:text-zinc-300 text-xs font-bold px-3 py-1.5 rounded-lg border border-[#E9E9E8]/50 dark:border-zinc-700 hover:border-transparent transition duration-200">
                                        Detail
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Custom Pagination -->
            <div class="flex justify-center mt-8">
                {{ $events->appends(['filter' => $filter, 'search' => $search])->links('theme::partials.pagination') }}
            </div>
        @else
            <div class="stitch-panel p-8 py-16 text-center max-w-md mx-auto">
                <span class="material-symbols-outlined text-4xl text-zinc-400 mb-2">event_busy</span>
                <h3 class="text-base font-bold text-zinc-900 dark:text-white">No events found</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Try altering your filter tab or search keyword!</p>
                <a href="{{ route('dashboard.events') }}" class="mt-4 inline-flex items-center gap-1 bg-[#df1c24] text-white text-xs font-semibold px-4 py-2 rounded-full hover:bg-opacity-90">
                    <span class="material-symbols-outlined text-[14px]">rotate_left</span> Reset Filters
                </a>
            </div>
        @endif

        <!-- Inline Detail Modal Container -->
        <div x-show="showModal" 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 md:p-6"
             x-cloak>
            
            <!-- Modal Backdrop Blur -->
            <!-- Backdrop (kept simple) -->
                 <div class="fixed inset-0 bg-zinc-950/45 dark:bg-zinc-950/70 transition-opacity"
                      x-show="showModal"
                      @click="showModal = false"></div>

            <!-- Modal Content Card Box -->
            <div class="bg-white dark:bg-zinc-900 w-full max-w-4xl rounded-3xl overflow-hidden border border-[#E9E9E8] dark:border-zinc-700 shadow-2xl flex flex-col md:flex-row relative z-10 max-h-[90vh] md:max-h-[80vh]"
                 x-show="showModal"
                 x-transition:enter="ease-out duration-350"
                 x-transition:enter-start="opacity-0 translate-x-full"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="ease-in duration-250"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-full">
                 <!-- Optional backdrop with glassmorphism -->
                 <div class="absolute inset-0 bg-white/30 dark:bg-black/30 backdrop-blur-lg pointer-events-none"></div>
                
                <!-- Left Side: Cover Image & Event Info -->
                <div class="flex-1 overflow-y-auto p-6 md:p-8 space-y-6">
                    <div class="relative h-60 md:h-72 w-full rounded-2xl overflow-hidden bg-zinc-150 shrink-0">
                        <img :src="activeEvent?.image" alt="Event Cover" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>
                        
                        <!-- Floating Organizer Avatar -->
                        <div class="absolute bottom-4 left-4 flex items-center gap-2 bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/20">
                            <div class="w-5 h-5 rounded-full bg-zinc-200 overflow-hidden flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[12px] text-zinc-650">person</span>
                            </div>
                            <span class="text-[10px] font-bold text-white uppercase tracking-wider" x-text="activeEvent?.organizer"></span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <span x-show="activeEvent?.isOngoing" class="rounded-full bg-red-50 dark:bg-blue-900/20 px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-wider text-red-700 dark:text-blue-300">Ongoing</span>
                            <span x-show="activeEvent?.isUpcoming" class="rounded-full bg-emerald-50 dark:bg-emerald-900/20 px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Upcoming</span>
                            <span x-show="!activeEvent?.isUpcoming && !activeEvent?.isOngoing" class="rounded-full bg-zinc-100 dark:bg-zinc-800 px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-wider text-zinc-650 dark:text-zinc-400">Past Event</span>
                        </div>
                        <h2 class="text-xl md:text-2xl font-bold tracking-tight text-[#37352F] dark:text-white" x-text="activeEvent?.title"></h2>
                    </div>

                    <!-- Description Content (HTML formatted) -->
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-[#575e75] dark:text-zinc-300 leading-relaxed text-sm" x-html="activeEvent?.body"></div>
                </div>

                <!-- Right Side: Details Sidebar Panel -->
                <div class="w-full md:w-80 bg-[#fff5f5] dark:bg-zinc-800/50 p-6 md:p-8 flex flex-col justify-between border-t md:border-t-0 md:border-l border-[#E9E9E8] dark:border-zinc-700 shrink-0">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-bold text-[#37352F] dark:text-white tracking-tight">Event Details</h3>
                            <p class="text-xs text-[#979A9B] mt-0.5">Please review the details and RSVP status.</p>
                        </div>

                        <div class="space-y-4 py-4 border-y border-[#E9E9E8]/50 dark:border-zinc-700/80">
                            <!-- Calendar row -->
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-[#df1c24] dark:text-primary-fixed text-[20px] mt-0.5" style="font-variation-settings: 'FILL' 1;">calendar_today</span>
                                <div>
                                    <p class="text-xs font-semibold text-[#37352F] dark:text-zinc-200">Date</p>
                                    <p class="text-xs text-[#575e75] dark:text-zinc-400 mt-0.5" x-text="activeEvent?.dateFormatted"></p>
                                </div>
                            </div>

                            <!-- Schedule row -->
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-[#df1c24] dark:text-primary-fixed text-[20px] mt-0.5" style="font-variation-settings: 'FILL' 1;">schedule</span>
                                <div>
                                    <p class="text-xs font-semibold text-[#37352F] dark:text-zinc-200">Time & Hours</p>
                                    <p class="text-xs text-[#575e75] dark:text-zinc-400 mt-0.5" x-text="activeEvent?.time"></p>
                                </div>
                            </div>

                            <!-- Venue row -->
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-[#df1c24] dark:text-primary-fixed text-[20px] mt-0.5" style="font-variation-settings: 'FILL' 1;">location_on</span>
                                <div>
                                    <p class="text-xs font-semibold text-[#37352F] dark:text-zinc-200">Location</p>
                                    <p class="text-xs text-[#575e75] dark:text-zinc-400 mt-0.5">Pulau Onrust, Kepulauan Seribu</p>
                                </div>
                            </div>

                            <!-- Cost row -->
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-[#df1c24] dark:text-primary-fixed text-[20px] mt-0.5" style="font-variation-settings: 'FILL' 1;">confirmation_number</span>
                                <div>
                                    <p class="text-xs font-semibold text-[#37352F] dark:text-zinc-200">Admission</p>
                                    <p class="text-xs font-semibold mt-0.5" :class="activeEvent?.type === 'PAID' ? 'text-amber-600 dark:text-amber-400' : 'text-[#107F5B] dark:text-emerald-400'" x-text="activeEvent?.priceFormatted"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RSVP / Action Buttons -->
                    <div class="flex flex-col gap-2.5 mt-6 md:mt-0">
                        <template x-if="activeEvent?.isActiveRegistration">
                            <a :href="activeEvent?.ticketUrl" target="_blank"
                               class="w-full bg-[#df1c24] text-white text-center text-xs font-bold py-3 rounded-xl hover:bg-opacity-95 transition flex items-center justify-center gap-1.5 shadow-sm">
                                <span class="material-symbols-outlined text-[16px]">confirmation_number</span>
                                Cetak Tiket Anda
                            </a>
                        </template>

                        <template x-if="!activeEvent?.isActiveRegistration">
                            <div class="flex flex-col gap-2.5">
                                <button type="button" 
                                        x-show="activeEvent?.isUpcoming || activeEvent?.isOngoing"
                                        @click="alert('Thank you for your interest! Registered members can automatically attend this event. Live schedule is synced to your calendar.')"
                                        class="w-full bg-[#df1c24] text-white text-xs font-bold py-3 rounded-xl hover:bg-opacity-95 transition flex items-center justify-center gap-1.5 shadow-sm">
                                    <span class="material-symbols-outlined text-[16px]">add_circle</span>
                                    RSVP & Join Event
                                </button>
                                <button type="button" disabled
                                        x-show="!activeEvent?.isUpcoming && !activeEvent?.isOngoing"
                                        class="w-full bg-zinc-200 dark:bg-zinc-700 text-zinc-400 dark:text-zinc-500 text-xs font-bold py-3 rounded-xl flex items-center justify-center gap-1.5 cursor-not-allowed">
                                    <span class="material-symbols-outlined text-[16px]">event_busy</span>
                                    Event Has Ended
                                </button>
                            </div>
                        </template>

                        <button type="button" 
                                @click="showModal = false"
                                class="w-full bg-white dark:bg-zinc-800 border border-[#E9E9E8] dark:border-zinc-750 text-[#37352F] dark:text-zinc-300 text-xs font-bold py-3 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-700 transition flex items-center justify-center gap-1.5 shadow-xs">
                            Close Details
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
