<?php
use function Laravel\Folio\{name};
name('organization.show');

$pic = \App\Models\Organization::where('id', $pic ?? '')->first();
?>

<x-layouts.marketing :seo="[
    'title' => 'Organization',
    'description' => 'Organization',
]">
    <x-container>
        <x-elements.back-button class="max-w-full mx-auto mt-4 md:mt-8" text="back" :href="route('organization')" />

        <!-- Profile -->
        <div class="flex items-center gap-x-3 my-6">
            <div class="shrink-0">
            <img class="shrink-0 size-16 rounded-full" src="{{ Storage::url('/' . $pic->avatar) }}" alt="{{ $pic->name }}">
            </div>
        
            <div class="grow">
            <h1 class="text-lg font-medium text-gray-800">
                {{ $pic->name }}
            </h1>
            <p class="text-sm text-gray-600">
                {{ $pic->position }}
            </p>
            </div>
        </div>
        <!-- End Profile -->
        
        <!-- About -->
        <article id="event-{{ $pic->id }}"
            class="max-w-full px-5 pb-20 mx-auto prose prose-md dark:prose-invert lg:prose-lg lg:px-0">
            <div class="mx-auto">
                {!! $pic->description !!}
            </div>
        
            <ul class="mt-5 flex flex-col gap-y-3">
                <!-- Facebook -->
                <li class="flex items-center gap-x-2.5">
                    <svg class="shrink-0 size-3.5 text-gray-800" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2.00195 12.002C2.00312 16.9214 5.58036 21.1101 10.439 21.881V14.892H7.90195V12.002H10.442V9.80204C10.3284 8.75958 10.6845 7.72064 11.4136 6.96698C12.1427 6.21332 13.1693 5.82306 14.215 5.90204C14.9655 5.91417 15.7141 5.98101 16.455 6.10205V8.56104H15.191C14.7558 8.50405 14.3183 8.64777 14.0017 8.95171C13.6851 9.25566 13.5237 9.68693 13.563 10.124V12.002H16.334L15.891 14.893H13.563V21.881C18.8174 21.0506 22.502 16.2518 21.9475 10.9611C21.3929 5.67041 16.7932 1.73997 11.4808 2.01722C6.16831 2.29447 2.0028 6.68235 2.00195 12.002Z"></path>
                    </svg>
                    <a class="text-[13px] text-gray-500 underline hover:text-gray-800 hover:decoration-2 focus:outline-hidden focus:decoration-2" href="{{ $pic->facebook_url }}" target="_blank">
                        Facebook
                    </a>
                </li>
            
                <!-- Instagram -->
                <li class="flex items-center gap-x-2.5">
                    <svg class="shrink-0 size-3.5 text-gray-800" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM12 18C8.68629 18 6 15.3137 6 12C6 8.68629 8.68629 6 12 6C15.3137 6 18 8.68629 18 12C18 15.3137 15.3137 18 12 18ZM12 8C10.3431 8 9 9.34315 9 11C9 12.6569 10.3431 14 12 14C13.6569 14 15 12.6569 15 11C15 9.34315 13.6569 8 12 8ZM12 13C11.4477 13 11 12.5523 11 12C11 11.4477 11.4477 11 12 11C12.5523 11 13 11.4477 13 12C13 12.5523 12.5523 13 12 13Z"></path>
                    </svg>
                    <a class="text-[13px] text-gray-500 underline hover:text-gray-800 hover:decoration-2 focus:outline-hidden focus:decoration-2" href="{{ $pic->instagram_url }}" target="_blank">
                        Instagram
                    </a>
                </li>
            
                <!-- LinkedIn -->
                <li class="flex items-center gap-x-2.5">
                    <svg class="shrink-0 size-3.5 text-gray-800" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C21.9939 17.5203 17.5203 21.9939 12 22C6.47715 22 2 17.5228 2 12ZM14.29 15.29L13.53 16.05C13.24 16.34 12.77 16.34 12.47 16.05L10.47 14.05C10.18 13.76 10.18 13.24 10.47 12.94C10.77 12.64 11.24 12.64 11.53 12.94L12.53 13.94L15.47 10.94C15.77 10.64 16.24 10.64 16.53 10.94C16.82 11.24 16.82 11.76 16.53 12.06L14.29 15.29ZM10 6.5C10.8284 6.5 11.5 7.17157 11.5 8C11.5 8.82843 10.8284 9.5 10 9.5C9.17157 9.5 8.5 8.82843 8.5 8C8.5 7.17157 9.17157 6.5 10 6.5ZM16 12C16 10.3431 14.6569 9 13 9C11.3431 9 10 10.3431 10 12C10 13.6569 11.3431 15 13 15C14.6569 15 16 13.6569 16 12Z"></path>
                    </svg>
                    <a class="text-[13px] text-gray-500 underline hover:text-gray-800 hover:decoration-2 focus:outline-hidden focus:decoration-2" href="{{ $pic->linkedin_url }}" target="_blank">
                        LinkedIn
                    </a>
                </li>
            
                <!-- Twitter -->
                <li class="flex items-center gap-x-2.5">
                    <svg class="shrink-0 size-3.5 text-gray-800" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 5.403a8.287 8.287 0 0 1-2.357.646 4.145 4.145 0 0 0 1.818-2.292A8.367 8.367 0 0 1 18.144 5.15a4.127 4.127 0 0 0-7.046 3.013 11.623 11.623 0 0 1-8.446-4.283A4.125 4.125 0 0 0 2 6.716a4.113 4.113 0 0 0 1.26 5.492A4.149 4.149 0 0 1 .8 11.7v.052a4.125 4.125 0 0 0 3.295 4.037 4.053 4.053 0 0 1-1.095.146c-.267 0-.526-.026-.782-.075a4.126 4.126 0 0 0 3.856 2.865 8.3 8.3 0 0 1-5.125 1.772 8.388 8.388 0 0 1-.99-.058A11.553 11.553 0 0 0 7.293 22a11.644 11.644 0 0 0 11.856-11.856c0-.18 0-.356-.011-.535A8.206 8.206 0 0 0 22 5.403z"></path>
                    </svg>
                    <a class="text-[13px] text-gray-500 underline hover:text-gray-800 hover:decoration-2 focus:outline-hidden focus:decoration-2" href="{{ $pic->twitter_url }}" target="_blank">
                        Twitter
                    </a>
                </li>
            </ul>
            
        </article>
        <!-- End About -->
    </x-container>
</x-layouts.marketing>