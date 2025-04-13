<?php
use function Laravel\Folio\{name};
name('opini');

$query = \App\Models\Post::whereHas('category', function ($query) {
    $query->where('name', 'Opini');
})->when(request()->has('user'), function ($query) {
    $query->where('author_id', request('user'));
})->where('status', 'PUBLISHED')
->paginate(5);

$user = request()->has('user');

?>

<x-layouts.marketing>
    <x-container>
        <div class="relative pt-6">
            <x-marketing.elements.heading
                title="Opini Anggota"
                description="{{ $user ? 'Beberapa Opini terbaru yang di buat oleh ' . $query[0]->user->name : 'Our latest opini posts below.' }} "
                align="left"
            />
            
            {{-- @include('theme::partials.blog.categories') --}}

            <div class="grid gap-5 mx-auto mt-7 sm:grid-cols-2 lg:grid-cols-3">
                @include('theme::partials.opini.opini-loop', ['opinions' => $query])
            </div>
            
        </div>

        <div class="flex justify-center my-10">
            {{ $query->links('theme::partials.pagination') }}
        </div>

    </x-container>
</x-layouts.marketing>
