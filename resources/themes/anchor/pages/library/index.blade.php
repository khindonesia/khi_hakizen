<?php
use function Laravel\Folio\{name};
name('library');

// Query events based on filter
$ebooks = \App\Models\Ebook::published()->orderBy('created_at', 'asc')->paginate(6);
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
        <div class="grid grid-cols-2 sm:grid-cols-4 pt-12 gap-3">
            <!-- Create By Joker Banny -->
            @foreach ($ebooks as $book)
            <a href="{{route('library.book', ['slug' => $book->slug])}}" wire:navigate key="{{$book->id}}">
                <div class="bg-white rounded-xl shadow-lg cursor-pointer">
                    <div class="p-4">
                        <h1 class="mt-4 text-xl sm:text-2xl font-bold hover:underline cursor-pointer">{{$book->title}}</h1>
                        <p class="mt-2 font-sans text-gray-700">by {{$book->author}}</p>
                    </div>
                    <div class="relative">
                        <img class="w-full"
                        src="{{Storage::url('/' . $book->cover_image)}}" />
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </x-container>
</x-layouts.marketing>
