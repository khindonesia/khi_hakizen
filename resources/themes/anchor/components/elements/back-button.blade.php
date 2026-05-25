<div {{ $attributes->twMerge('mx-auto w-full') }}>
    <a href="{{ $href ?? '' }}" wire:navigate class="group stitch-chip mb-4 text-zinc-700 hover:border-red-200 hover:bg-red-50 hover:text-red-700">
        <svg class="relative -ml-0.5 mr-1.5 h-3.5 w-3.5 translate-x-0.5 duration-200 ease-out group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        {{ $text ?? '' }}
    </a>
</div>
