@foreach ($posts as $post)
    <article id="post-{{ $post->id }}"
        class="overflow-hidden relative col-span-2 p-4 bg-white rounded-2xl border cursor-pointer transition-all duration-300 sm:col-span-1 group
        {{ $post->featured ? 'border-amber-500 ring-1 ring-amber-500 dark:bg-zinc-900' : 'border-zinc-200 dark:bg-black' }}">

        <meta property="name" content="{{ $post->title }}">
        <meta property="author" typeof="Person" content="{{ $post->user->name }}">
        <meta property="dateModified" content="{{ Carbon\Carbon::parse($post->updated_at)->toIso8601String() }}">
        <meta class="uk-margin-remove-adjacent" property="datePublished"
            content="{{ Carbon\Carbon::parse($post->created_at)->toIso8601String() }}">

        <div class="overflow-hidden relative w-full aspect-[16/9] rounded-lg bg-zinc-100">
            @if ($post->featured)
                <div
                    class="flex absolute top-2 left-2 z-10 gap-x-1 items-center px-2.5 py-1 text-xs font-bold tracking-wide text-white bg-red-400 rounded-md shadow-md border border-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        class="w-3.5 h-3.5 text-white">
                        <path fill-rule="evenodd"
                            d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.6 3.102-1.196 4.622c-.21.81.67 1.45 1.366 1.012L10 15.655l4.18 2.604c.696.438 1.577-.202 1.366-1.012l-1.196-4.622 3.6-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z"
                            clip-rule="evenodd" />
                    </svg>
                    FEATURED
                </div>
            @endif

            <img src="{{ $post->image() }}" alt="{{ $post->title }}"
                class="object-cover w-full h-full transition duration-300 group-hover:scale-105">
        </div>

        <div class="px-1 py-1">
            <div class="flex gap-x-4 items-center my-3 text-xs">
                <time datetime="{{ $post->updated_at->toIso8601String() }}" class="text-zinc-500">
                    {{ $post->updated_at->format('M d, Y') }}
                </time>
                <span>|</span>
                <span class="text-zinc-500">{{ $post->user->name }}</span>
            </div>

            <h2 class="text-lg font-semibold leading-6 text-zinc-900 group-hover:text-zinc-600 dark:text-zinc-100">
                <a href="{{ $post->link() }}" wire:navigate>
                    <span class="absolute inset-0"></span>
                    {{ $post->title }}
                </a>
            </h2>

            <p class="mt-5 text-sm leading-6 text-zinc-600 line-clamp-3 dark:text-zinc-400">
                {{ Str::limit(strip_tags($post->body), 110, '...') }}
            </p>
        </div>
    </article>
@endforeach
