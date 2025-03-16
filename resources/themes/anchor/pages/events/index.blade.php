<?php
use function Laravel\Folio\{name};
name('events');

// Get the filter from the request
$filter = request('filter', 'upcoming');

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
    default:
        $query->upcoming();
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
        <div class="flex flex-wrap gap-2 mt-8">
            <a href="{{ route('events', ['filter' => 'upcoming']) }}" 
               class="px-4 py-2 rounded-full text-sm font-medium {{ request('filter', 'upcoming') == 'upcoming' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Upcoming
            </a>
            <a href="{{ route('events', ['filter' => 'ongoing']) }}" 
               class="px-4 py-2 rounded-full text-sm font-medium {{ request('filter') == 'ongoing' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Ongoing
            </a>
            <a href="{{ route('events', ['filter' => 'past']) }}" 
               class="px-4 py-2 rounded-full text-sm font-medium {{ request('filter') == 'past' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Past
            </a>
        </div>
        
        @if($events->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 pt-12 gap-6">
                @foreach($events as $event)
                {{-- <a href="{{ route('events.show', ['slug' => $event->slug]) }}" class="block hover:no-underline"> --}}
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
                                    <div class="mt-4">
                                        <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            View details
                                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {{-- </a> --}}
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