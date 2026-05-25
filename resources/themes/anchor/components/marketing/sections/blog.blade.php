<?php
$posts = \Wave\Post::orderBy('created_at', 'DESC')->whereHas('category', function ($query) {
    $query->where('name', 'Historia News');
})->where('status', 'PUBLISHED')->paginate(3);
?>

<x-container>
    <div class="relative pt-12">
        <x-marketing.elements.heading title="Historialita" description="Curated articles, notes, and updates from Komunitas Historia Indonesia."
            align="left" />

        <div class="grid gap-5 mx-auto mt-5 md:mt-10 sm:grid-cols-2 lg:grid-cols-3">
            @include('theme::partials.blog.posts-loop', ['posts' => $posts])
        </div>
    </div>

    <div class="flex justify-center my-10">
        <x-filament::link wire:navigate :href="route('historia-news')">Lihat selengkapnya</x-filament::link>
    </div>

</x-container>
