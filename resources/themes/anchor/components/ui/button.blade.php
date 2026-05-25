<!-- resources/themes/anchor/components/ui/button.blade.php -->
@props([
    'type' => 'button',
    'color' => 'primary', // primary | secondary | danger
    'size' => 'md', // sm | md | lg
    'href' => null,
    'disabled' => false,
])
@php
    $bgColors = [
        'primary' => 'bg-[#df1c24] text-white hover:bg-[#df1c24]/90',
        'secondary' => 'bg-white text-[#df1c24] border border-[#E9E9E8] hover:bg-[#fef2f2]/10',
        'danger' => 'bg-[#e53e3e] text-white hover:bg-[#c53030]'
    ];
    $padding = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-5 py-3 text-base',
        'lg' => 'px-6 py-4 text-lg',
    ];
    $classes = ($bgColors[$color] ?? $bgColors['primary']).' rounded-lg font-semibold transition-all '.($padding[$size] ?? $padding['md']).' disabled:opacity-50 disabled:cursor-not-allowed';
@endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes, 'wire:navigate' => true]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes, 'disabled' => $disabled]) }}>
        {{ $slot }}
    </button>
@endif
