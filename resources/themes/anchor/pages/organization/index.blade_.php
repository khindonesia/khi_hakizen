<?php
use function Laravel\Folio\{name};
name('organization');
?>

<x-layouts.marketing :seo="[
    'title' => 'Organization',
    'description' => 'Organization',
]">
    <x-container>
        <div class="relative pt-6 mb-6">
            <!-- Hero -->
            <div class="relative overflow-hidden">
                <div class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8 py-10">
                    <div class="max-w-2xl text-center mx-auto">
                        <h1 class="block text-3xl font-bold text-gray-800 sm:text-4xl md:text-5xl">Komunitas Historia Indonesia</h1>
                        <p class="mt-3 text-lg text-gray-800" lang="id">Komunitas yang berdedikasi untuk mempromosikan pengetahuan sejarah, mendorong diskusi, dan melestarikan warisan budaya Indonesia yang kaya.</p>
                    </div>                    
            
                    <div class="mt-10 relative max-w-5xl mx-auto">
                        <div class="w-full object-cover h-96 sm:h-120 bg-no-repeat bg-center bg-cover rounded-xl" style="background-image: url('https://static.wixstatic.com/media/4dda9f_9ef8c0b1d37349c19b2c061f1f321e2d.jpg/v1/fill/w_1000,h_666,al_c,q_85,usm_0.66_1.00_0.01/4dda9f_9ef8c0b1d37349c19b2c061f1f321e2d.jpg')"></div>
                
                            <div class="absolute inset-0 size-full">
                            <div class="flex flex-col justify-center items-center size-full">
                                <a href="https://youtu.be/K8td2zuo560?si=qjo8tpS4JiBJjmdu" target="_blank" class="py-3 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-full border border-gray-200 bg-white text-gray-800 shadow-2xs hover:bg-gray-50 focus:outline-hidden focus:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none" href="#">
                                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                Play the overview
                                </a>
                            </div>
                        </div>
                
                    </div>
                </div>
            </div>
            <!-- End Hero -->
        </div>
    </x-container>
</x-layouts.marketing>