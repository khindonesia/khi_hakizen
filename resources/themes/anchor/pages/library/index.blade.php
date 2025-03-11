<?php
use function Laravel\Folio\{name};
name('library');

$posts = \Wave\Post::orderBy('created_at', 'DESC')->paginate(6);
$categories = \Wave\Category::all();
?>

<x-layouts.marketing :seo="[
    'title' => 'KHI - Library',
    'description' => 'library',
]">
    <x-container>
        <div class="relative pt-6">
            <x-marketing.elements.heading title="Libraries" description="Check out some of our latest book posts below."
                align="left" />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-4 pt-12">
            <!-- Create By Joker Banny -->
            <div class="bg-white rounded-xl shadow-lg cursor-pointer">
                <div class="p-4">
                    <h1 class="mt-4 text-3xl font-bold hover:underline cursor-pointer">Super Books</h1>
                    <p class="mt-2 font-sans text-gray-700">by Diseño Constructivo</p>
                </div>
                <div class="relative">
                    <img class="w-full"
                        src="https://images.unsplash.com/photo-1571167530149-c1105da4c2c7?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=376&q=80" />
                    <p
                        class="absolute text-lg transform translate-x-20 -translate-y-24 bg-blue-600 text-white py-3 px-6 rounded-full cursor-pointer hover:scale-105 duration-500">
                        Comprar ahora</p>
                </div>
            </div>
        </div>
    </x-container>
</x-layouts.marketing>
