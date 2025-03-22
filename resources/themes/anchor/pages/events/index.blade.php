<?php
use function Laravel\Folio\{name};
name('events');

// Get the filter from the request without a default
$filter = request('filter');

// Query events based on filter
$query = \App\Models\Event::published()->orderBy('start_datetime', 'asc');

switch ($filter) {
    case 'ongoing':
        $query->ongoing();
        break;
    case 'past':
        $query->past()->orderBy('start_datetime', 'desc'); // Past events in reverse chronological order
        break;
    case 'upcoming':
        $query->upcoming();
        break;
    default:
        // No filter applied - show all events
        break;
}

// Fetch events from the database
$events = $query->paginate(6);
?>

<x-layouts.marketing :seo="[
    'title' => 'Events',
    'description' => 'Discover and join our upcoming events',
]">
    <x-container>
        <div class="relative pt-6">
            <x-marketing.elements.heading 
                title="Events" 
                description="Check out our upcoming events and join us"
                align="left" />
        </div>
        
        <!-- Event filter tabs -->
        <div class="flex flex-wrap justify-center gap-2 mt-8">
            <x-button wire:navigate href="{{ route('events') }}" tag="a" color="{{request('filter') === null ? 'primary' : 'secondary'}}" class="text-sm">
                All Events
            </x-button>
            <x-button wire:navigate href="{{ route('events', ['filter' => 'upcoming']) }}" tag="a" color="{{request('filter') === 'upcoming' ? 'primary' : 'secondary'}}" class="text-sm">
                Upcoming
            </x-button>
            <x-button wire:navigate href="{{ route('events', ['filter' => 'ongoing']) }}" tag="a" color="{{request('filter') === 'ongoing' ? 'primary' : 'secondary'}}" class="text-sm">
                Ongoing
            </x-button>
            <x-button wire:navigate href="{{ route('events', ['filter' => 'past']) }}" tag="a" color="{{request('filter') === 'past' ? 'primary' : 'secondary'}}" class="text-sm">
                Past
            </x-button>
        </div>
        
        @if($events->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 pt-12 gap-6">
                @foreach($events as $event)
                <a href="{{ route('events.show', ['slug' => $event->slug]) }}" wire:navigate class="block hover:no-underline">
                        <div class="flex flex-col w-full bg-white rounded shadow-lg hover:shadow-xl transition-shadow duration-300 h-full">
                            <div class="w-full h-44 sm:h-64 bg-center bg-cover rounded-t"
                                style="background-image: url({{ Storage::url('/' . $event->image) }})">
                            </div>
                            <div class="flex flex-col w-full md:flex-row flex-grow">
                                <div
                                    class="flex flex-row justify-around p-4 font-bold leading-none text-gray-800 uppercase bg-gray-400 rounded md:flex-col md:items-center md:justify-center md:w-1/4">
                                    <div class="md:text-3xl">{{ $event->start_datetime->format('M') }}</div>
                                    <div class="md:text-6xl">{{ $event->start_datetime->format('d') }}</div>
                                    <div class="md:text-xl">{{ $event->start_datetime->format('H:i') }}</div>
                                </div>
                                <div class="p-4 font-normal text-gray-800 flex-grow flex flex-col">
                                    <h1 class="mb-4 text-xl sm:text-2xl font-bold leading-none tracking-tight text-gray-800">
                                        {{ $event->title }}
                                    </h1>
                                    <div class="leading-normal line-clamp-3 flex-grow">
                                        {{ Str::limit(strip_tags($event->body), 150) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            
            <div class="flex justify-center my-10">
                {{ $events->appends(['filter' => request('filter')])->links('theme::partials.pagination') }}
            </div>
        @else
            <div class="py-12 text-center">
                <p class="text-xl text-gray-600">No events found. Please check back later!</p>
            </div>
        @endif
    </x-container>
</x-layouts.marketing>