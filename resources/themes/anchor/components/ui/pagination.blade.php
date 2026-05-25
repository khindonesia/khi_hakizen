<!-- resources/themes/anchor/components/ui/pagination.blade.php -->
@props(['paginator'])
@php
    $total = $paginator->lastPage();
    $current = $paginator->currentPage();
@endphp
<div class="flex items-center justify-center space-x-1">
    @if($paginator->onFirstPage())
        <span class="px-3 py-1 text-sm text-[#979A9B] rounded-md bg-[#f5f5f5] cursor-not-allowed">Prev</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" wire:navigate class="px-3 py-1 text-sm text-[#df1c24] rounded-md hover:bg-[#fef2f2]/10 transition-colors">Prev</a>
    @endif

    @for($i = 1; $i <= $total; $i++)
        @if($i == $current)
            <span class="px-3 py-1 text-sm font-medium text-white bg-[#df1c24] rounded-md">{{ $i }}</span>
        @else
            <a href="{{ $paginator->url($i) }}" wire:navigate class="px-3 py-1 text-sm text-[#df1c24] rounded-md hover:bg-[#fef2f2]/10 transition-colors">{{ $i }}</a>
        @endif
    @endfor

    @if($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" wire:navigate class="px-3 py-1 text-sm text-[#df1c24] rounded-md hover:bg-[#fef2f2]/10 transition-colors">Next</a>
    @else
        <span class="px-3 py-1 text-sm text-[#979A9B] rounded-md bg-[#f5f5f5] cursor-not-allowed">Next</span>
    @endif
</div>
