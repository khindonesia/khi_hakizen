<?php
    use function Laravel\Folio\{middleware, name};
    name('library.book');

    $book = \App\Models\Ebook::where('slug', $slug ?? '')->first();
?>

<x-layouts.marketing :seo="[
    'title' => 'KHI - Library',
    'description' => 'library',
]">
    <x-container class="py-12">
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-6">
            <div class="col-span-1">
                <div class="relative">
                    <img class="w-full"
                        src="{{Storage::url('/' . $book->cover_image)}}" />
                </div>
            </div>
            <div class="col-span-4">
                <h1 class="text-xl font-medium">{{$book->title}}</h1>
                <div class="max-w-4xl mx-auto py-4">
                    {!! $book->description !!}
                </div>
                @guest
                    <x-button disabled>Download now</x-button>
                @else
                    <x-button :href="Storage::url($book->ebook_file)" tag="a" download>Download now</x-button>
                @endguest
            </div>
        </div>
    </x-container>

</x-layouts.marketing>