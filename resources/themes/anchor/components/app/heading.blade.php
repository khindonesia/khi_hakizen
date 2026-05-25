@props([
    'title' => '',
    'description' => '',
    'border' => true
])

<div class="@if($border){{ 'pb-5 border-b border-zinc-200/80 dark:border-zinc-800' }}@endif space-y-1">
    <h3 class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-2xl">{{ $title ?? '' }}</h3>
    <p class="max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">{{ $description ?? '' }}</p>
</div>
