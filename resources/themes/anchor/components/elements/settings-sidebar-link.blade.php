<a href="{{ $href }}"
    class="@if($href == RalphJSmit\Livewire\Urls\Facades\Url::current()){{ 'bg-white text-red-700 shadow-sm ring-1 ring-blue-100 dark:bg-zinc-900 dark:text-red-100 dark:ring-blue-500/20' }}@else{{ 'text-zinc-600 hover:bg-white hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-900 dark:hover:text-zinc-100' }}@endif relative flex items-center justify-start gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-100 disabled:pointer-events-none disabled:opacity-50">
    <span class="absolute left-0 top-1/2 block h-3/4 w-1 -translate-x-1 rounded-full bg-red-500 transition-all duration-300 ease-out @if($href == RalphJSmit\Livewire\Urls\Facades\Url::current()){{ 'opacity-100' }}@else{{ 'opacity-0' }}@endif"></span>
    <x-dynamic-component :component="$icon" class="h-5 w-5 flex-shrink-0 md:h-4 md:w-4" />
    <span class="truncate">{{ $slot }}</span>
</a>
