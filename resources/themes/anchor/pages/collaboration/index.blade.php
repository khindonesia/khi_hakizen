<?php
use function Laravel\Folio\{name};
name('collaboration');
?>

<x-layouts.marketing :seo="[
    'title' => 'Collaboration',
    'description' => 'Collaboration',
]">
    <div class="w-full bg-center bg-cover h-[20rem] lg:h-[38rem]"
    style="background-image: url('https://static.wixstatic.com/media/4dda9f_9ef8c0b1d37349c19b2c061f1f321e2d.jpg/v1/fill/w_1000,h_666,al_c,q_85,usm_0.66_1.00_0.01/4dda9f_9ef8c0b1d37349c19b2c061f1f321e2d.jpg');">
        <div class="flex items-center justify-center w-full h-full bg-gray-900/40">
            <div class="text-center mx-8">
                <h1 class="text-3xl font-semibold text-white lg:text-7xl">Komunitas Historia Indonesia</h1>
                <h2 class="text-3xl font-semibold text-white lg:text-7xl">Collaboration</h2>
            </div>
        </div>
    </div>
    <x-container>
        <div class="relative pt-6">
            <x-marketing.elements.heading
                title="Collaboration"
                description="Temukan kolaborasi yang menarik, ayo berkolaborasi bersama kami!"
                align="left"
            />
        </div>

        <x-marketing.sections.clients/>
    </x-container>
</x-layouts.marketing>
