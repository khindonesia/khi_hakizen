@props([
    'href' => '',
    'icon' => 'phosphor-house-duotone',
    'active' => false,
    'hideUntilGroupHover' => true,
    'target' => '_self',
    'ajax' => true,
])

@php
    $isActive = filter_var($active, FILTER_VALIDATE_BOOLEAN);
@endphp

<a {{ $attributes }}
    href="{{ $href }}"
    @if((($href ?? false) && $target == '_self') && $ajax) wire:navigate @else @if($ajax) target="_blank" @endif @endif
    @class([
        'group flex w-full items-center gap-3 rounded-2xl border px-3 py-2.5 text-sm transition',
        'border-red-100 bg-red-50 text-red-700 shadow-sm dark:border-blue-500/30 dark:bg-red-500/10 dark:text-red-100' => $isActive,
        'border-transparent text-zinc-600 hover:border-zinc-200 hover:bg-white hover:text-zinc-900 dark:text-zinc-300 dark:hover:border-zinc-700 dark:hover:bg-zinc-900 dark:hover:text-zinc-100' => ! $isActive,
    ])>
    <x-dynamic-component :component="$icon" class="h-5 w-5 flex-shrink-0" />
    <span class="flex-1 truncate font-medium">{{ $slot }}</span>
    @if ($isActive)
        <span class="h-2 w-2 rounded-full bg-current"></span>
    @endif
</a>
