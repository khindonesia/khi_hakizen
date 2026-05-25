@props([
    'level' => 'h1',
    'title' => 'No Heading Title Entered',
    'description' => 'Be sure to include the description attribute',
    'align' => 'center',
    'eyebrow' => null,
])


<div
    {{ $attributes->class([
        'relative w-full',
        'text-left' => $align == 'left',
        'text-right' => $align == 'right',
        'text-center' => $align != 'left' && $align != 'right',
    ]) }}>
    @if ($eyebrow)
        <div class="stitch-chip mb-4 @if ($align == 'left'){{ 'mr-auto' }}@elseif($align == 'right'){{ 'ml-auto' }}@else{{ 'mx-auto' }}@endif">
            {{ $eyebrow }}
        </div>
    @endif
    <{{ $level }} class="text-3xl font-semibold tracking-tight text-zinc-900 sm:text-4xl lg:text-5xl">
        {!! $title !!}
    </{{ $level }}>
    <p class="mt-4 text-sm font-medium leading-7 text-zinc-500 sm:text-base @if ($align == 'left') {{ 'ml-auto' }}@elseif($align == 'right'){{ 'mr-auto' }}@else{{ 'mx-auto max-w-2xl' }} @endif">
        {!! $description !!}
    </p>
</div>
