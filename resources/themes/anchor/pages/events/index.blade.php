<?php
    use Illuminate\View\View;
    use function Laravel\Folio\{name, render};

    name('events');

    render(function (View $view): View {
        $filter = request('filter');
        $search = request('search');

        $query = \App\Models\Event::published();

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

<x-layouts.marketing :seo="[
    'title' => 'Events - Komunitas Historia Indonesia',
    'description' => 'Join our upcoming walking tours, heritage seminars, museum exhibitions, and historical education events.',
]">
<div class="relative min-h-screen py-12 md:py-20">
        <x-container>
            
            <!-- Page Header Section -->
            <div class="max-w-3xl mb-12">
                <div class="stitch-chip mb-3 inline-flex">
                    <span class="material-symbols-outlined text-[14px]">event_upcoming</span>
                    Historical Gatherings
                </div>
                <h1 class="text-3xl font-semibold leading-tight tracking-tight text-zinc-900 md:text-[44px]">
                    KHI Community Events
                </h1>
                <p class="mt-3 text-sm leading-[1.6] text-zinc-500 md:text-base">
                    Participate in our interactive walking tours, archive conservation workshops, history forums, and heritage conservation programs across Indonesia.
                </p>
            </div>

            <!-- Filters & Search Toolbar -->
            <div class="stitch-panel mb-10 flex flex-col items-center justify-between gap-4 p-4 md:flex-row">
                <!-- Tabs / Filters -->
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <a href="{{ route('events', ['search' => $search]) }}" wire:navigate 
                       class="rounded-full border px-4 py-2 text-xs font-semibold transition-all {{ $filter === null ? 'border-red-600 bg-red-600 text-white' : 'border-zinc-200 bg-white text-zinc-600 hover:border-red-200 hover:text-red-700' }}">
                        All Events
                    </a>
                    <a href="{{ route('events', ['filter' => 'upcoming', 'search' => $search]) }}" wire:navigate 
                       class="rounded-full border px-4 py-2 text-xs font-semibold transition-all {{ $filter === 'upcoming' ? 'border-red-600 bg-red-600 text-white' : 'border-zinc-200 bg-white text-zinc-600 hover:border-red-200 hover:text-red-700' }}">
                        Upcoming
                    </a>
                    <a href="{{ route('events', ['filter' => 'ongoing', 'search' => $search]) }}" wire:navigate 
                       class="rounded-full border px-4 py-2 text-xs font-semibold transition-all {{ $filter === 'ongoing' ? 'border-red-600 bg-red-600 text-white' : 'border-zinc-200 bg-white text-zinc-600 hover:border-red-200 hover:text-red-700' }}">
                        Ongoing
                    </a>
                    <a href="{{ route('events', ['filter' => 'past', 'search' => $search]) }}" wire:navigate 
                       class="rounded-full border px-4 py-2 text-xs font-semibold transition-all {{ $filter === 'past' ? 'border-red-600 bg-red-600 text-white' : 'border-zinc-200 bg-white text-zinc-600 hover:border-red-200 hover:text-red-700' }}">
                        Past Events
                    </a>
                </div>

                <!-- Search Input Form -->
                <form action="{{ route('events') }}" method="GET" class="flex w-full items-center rounded-2xl border border-zinc-200 bg-white px-3 py-2 transition-all focus-within:border-red-300 focus-within:ring-4 focus-within:ring-blue-100 md:w-80">
                    @if($filter)
                        <input type="hidden" name="filter" value="{{ $filter }}">
                    @endif
                    <span class="material-symbols-outlined mr-2 text-[20px] text-zinc-400">search</span>
                    <input type="text" name="search" placeholder="Search events..." value="{{ $search }}"
                           class="w-full border-none bg-transparent p-0 text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-0">
                    @if($search)
                        <a href="{{ route('events', ['filter' => $filter]) }}" class="text-zinc-400 hover:text-zinc-600 ml-1.5 flex items-center">
                            <span class="material-symbols-outlined text-[16px]">close</span>
                        </a>
                    @endif
                </form>
            </div>

            <!-- Events Grid -->
            @if($events->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($events as $event)
                        @php
                            $eventImage = $event->image
                                ? Storage::url(ltrim($event->image, '/'))
                                : 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=600&auto=format&fit=crop';
                            
                            $isUpcoming = $event->start_datetime->isFuture();
                            $isOngoing = $event->start_datetime->isPast() && $event->end_datetime->isFuture();
                        @endphp
                            <a href="{{ route('events.show', ['slug' => $event->slug]) }}" wire:navigate class="group block h-full">
                            <article class="stitch-panel relative flex h-full flex-col overflow-hidden transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_18px_50px_rgba(15,23,42,0.08)]">
                                
                                <!-- Floating Date Card Overlay -->
                                <div class="absolute left-4 top-4 z-10 min-w-[54px] rounded-2xl border border-zinc-200/70 bg-white/95 p-2.5 text-center shadow-sm backdrop-blur-md">
                                    <span class="text-[10px] font-bold uppercase leading-none tracking-wider text-red-700">{{ $event->start_datetime->format('M') }}</span>
                                    <span class="mt-1.5 text-xl font-bold leading-none text-zinc-900">{{ $event->start_datetime->format('d') }}</span>
                                </div>

                                <!-- Card Cover Image -->
                                <div class="relative h-52 overflow-hidden bg-zinc-100">
                                    <img src="{{ $eventImage }}" alt="{{ $event->title }}" 
                                         class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                                </div>

                                <!-- Card Content -->
                                <div class="p-6 flex-grow flex flex-col">
                                    <!-- Dynamic Badge Status & Cost -->
                                    <div class="mb-2 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            @if($isOngoing)
                                                <span class="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-red-700">Ongoing</span>
                                            @elseif($isUpcoming)
                                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-700">Upcoming</span>
                                            @else
                                                <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-zinc-600">Past Event</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center">
                                            @if($event->type === 'PAID')
                                                <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-700 border border-amber-200">
                                                    Rp {{ number_format($event->price, 0, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-700 border border-emerald-200">
                                                    Gratis
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Event Title -->
                                    <h3 class="text-lg font-semibold text-zinc-900 line-clamp-1 group-hover:text-red-700 transition-colors leading-snug">
                                        {{ $event->title }}
                                    </h3>

                                    <!-- Body summary snippet -->
                                    <p class="mt-2.5 mb-5 line-clamp-3 flex-grow text-sm leading-relaxed text-zinc-500">
                                        {{ Str::limit(strip_tags($event->body), 120) }}
                                    </p>

                                    <!-- Metadata footer -->
                                    <div class="mt-auto space-y-1.5 border-t border-zinc-200/60 pt-4">
                                        <div class="flex items-center gap-1.5 text-xs text-zinc-500">
                                            <span class="material-symbols-outlined text-[14px]">schedule</span>
                                            <span>{{ $event->start_datetime->format('H:i') }} - {{ $event->end_datetime->format('H:i') }} WIB</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs text-zinc-500">
                                            <span class="material-symbols-outlined text-[14px]">location_on</span>
                                            <span>{{ $event->location ?? 'Jakarta, Indonesia' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </a>
                    @endforeach
                </div>

                <!-- Custom Pagination -->
                <div class="flex justify-center mt-12">
                    {{ $events->appends(['filter' => $filter, 'search' => $search])->links('theme::partials.pagination') }}
                </div>
            @else
                <div class="stitch-panel mx-auto max-w-lg p-8 py-16 text-center">
                    <span class="material-symbols-outlined mb-3 block text-5xl text-zinc-400">event_busy</span>
                    <h3 class="text-lg font-semibold text-zinc-900">No events found</h3>
                    <p class="mt-1.5 text-sm text-zinc-500">We couldn't find any events matching your request. Try altering your filter tab or search query!</p>
                    <a href="{{ route('events') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-red-600 px-5 py-2.5 text-xs font-semibold text-white transition-all hover:bg-red-500">
                        <span class="material-symbols-outlined text-[16px]">rotate_left</span> Reset Filters
                    </a>
                </div>
            @endif

        </x-container>
    </div>
</x-layouts.marketing>
