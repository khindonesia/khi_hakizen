<?php
    use function Laravel\Folio\{middleware, name};
    name('events.show');

    $event = \App\Models\Event::where('slug', $slug ?? '')->first();
?>

<x-layouts.marketing>
    <article id="event-{{ $event->id }}"
        class="max-w-4xl px-5 pb-20 mx-auto prose prose-md dark:prose-invert lg:prose-lg lg:px-0">
        <x-elements.back-button class="max-w-4xl mx-auto mt-4 md:mt-8" text="back" :href="route('events')" />
        <div class="max-w-4xl mx-auto mt-6">

            <h1 class="flex flex-col leading-none">
                <span>{{ $event->title }}</span>
                {{-- <span class="mt-0 sm:mt-10 text-base font-normal">Written on <time datetime="{{ Carbon\Carbon::parse($post->created_at)->toIso8601String() }}">{{ Carbon\Carbon::parse($post->created_at)->toFormattedDateString() }}</time>. Posted in <a href="{{ route('blog.category', $post->category->slug) }}" rel="category">{{ $post->category->name }}</a>.</span> --}}
            </h1>

        </div>
        <div class="relative">
            <img class="w-full h-auto rounded-lg" src="{{ Storage::url('/' . $event->image) }}" alt="{{ $event->title }}"
                srcset="{{ Storage::url('/' . $event->image) }}">
        </div>
        <div class="max-w-4xl mx-auto">
            {!! $event->body !!}
        </div>
    </article>
</x-layouts.marketing>