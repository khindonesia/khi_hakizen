<?php
$posts = \Wave\Post::orderBy('created_at', 'DESC')->paginate(3);
$categories = \Wave\Category::all();
?>

<x-container>
    <div class="relative pt-12">
        <x-marketing.elements.heading title="Historia News" description="Check out some of our latest blog posts below."
            align="left" />

        <div class="grid gap-5 mx-auto mt-5 md:mt-10 sm:grid-cols-2 lg:grid-cols-3">
            @include('theme::partials.blog.posts-loop', ['posts' => $posts])
        </div>
    </div>

    <div class="flex justify-center my-10">
        <x-filament::link wire:navigate :href="route('blog')">Lihat selengkapnya</x-filament::link>
    </div>

</x-container>
