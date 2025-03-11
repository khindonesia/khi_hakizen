<?php
use function Laravel\Folio\{name};
name('merchandise');

$posts = \Wave\Post::orderBy('created_at', 'DESC')->paginate(6);
$categories = \Wave\Category::all();
?>

<x-layouts.marketing :seo="[
    'title' => 'KHI - Merchandise',
    'description' => 'Merchandise',
]">
    <x-container>
        halo
    </x-container>
</x-layouts.marketing>
