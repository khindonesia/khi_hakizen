<?php
use function Laravel\Folio\name;

name('layout.fullscreen');
?>

<x-layouts.marketing>
    <div class="flex min-h-screen items-center justify-center bg-white px-6">
        <div class="max-w-md rounded-2xl border border-zinc-200 bg-zinc-50 p-8 text-center shadow-sm">
            <p class="text-lg font-semibold text-zinc-900">Fullscreen layout</p>
            <p class="mt-2 text-sm text-zinc-600">Blank utility shell for pages that need a distraction-free canvas.</p>
        </div>
    </div>
</x-layouts.marketing>
