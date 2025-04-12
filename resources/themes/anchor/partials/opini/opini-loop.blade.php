<!-- Loop Through Posts Here -->
@foreach ($opinions as $opini)
    <article id="post-{{ $opini->id }}"
        class="overflow-hidden relative col-span-2 p-4 bg-white rounded-2xl border cursor-pointer border-zinc-200 dark:bg-black sm:col-span-1">
        <meta property="name" content="{{ $opini->title }}">
        <meta property="author" typeof="Person" content="admin">
        <meta property="dateModified" content="{{ Carbon\Carbon::parse($opini->updated_at)->toIso8601String() }}">
        <meta class="uk-margin-remove-adjacent" property="datePublished"
            content="{{ Carbon\Carbon::parse($opini->created_at)->toIso8601String() }}">

        <img src="{{ Storage::url('/' . $opini->user->avatar) }}" alt="{{ $opini->user->name }}" class="w-full h-auto rounded-lg">
        <div class="px-1 py-1">
            <div class="flex gap-x-4 items-center my-3 text-xs">
                <time datetime="{{ $opini->updated_at }}" class="text-zinc-500">
                    {{ \Carbon\Carbon::parse($opini->updated_at)->format('M d, Y') }}
                </time>
                
                <span>|</span>
                <span class="text-zinc-500">{{ $opini->user->name }}</span>
            </div>
            <h2 class="text-lg font-semibold leading-6 text-zinc-900 group-hover:text-zinc-600">
                <a href="{{ url('/opini') . '/' . $opini->slug }}" wire:navigate>
                    <span class="absolute inset-0"></span>
                    {{ $opini->title }}
                </a>
            </h2>
            <p class="mt-5 text-sm leading-6 text-zinc-600 line-clamp-3">{{ substr(strip_tags($opini->body), 0, 110) }}
                @if (strlen(strip_tags($opini->body)) > 200)
                    {{ '...' }}
                @endif
            </p>

        </div>
    </article>
@endforeach
<!-- End Post Loop Here -->
