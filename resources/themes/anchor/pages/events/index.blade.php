<?php
use function Laravel\Folio\{name};
name('events');
?>

<x-layouts.marketing :seo="[
    'title' => 'Events',
    'description' => 'Events',
]">
    <x-container>
        <div class="relative pt-6">
            <x-marketing.elements.heading title="Events" description="Check out some of our latest event posts below."
                align="left" />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 pt-12">
            <div class="flex flex-col w-full bg-white rounded shadow-lg">
                <div class="w-full h-64 bg-top bg-cover rounded-t"
                    style="background-image: url(https://www.si.com/.image/t_share/MTY4MTkyMjczODM4OTc0ODQ5/cfp-trophy-deitschjpg.jpg)">
                </div>
                <div class="flex flex-col w-full md:flex-row">
                    <div
                        class="flex flex-row justify-around p-4 font-bold leading-none text-gray-800 uppercase bg-gray-400 rounded md:flex-col md:items-center md:justify-center md:w-1/4">
                        <div class="md:text-3xl">Jan</div>
                        <div class="md:text-6xl">13</div>
                        <div class="md:text-xl">7 pm</div>
                    </div>
                    <div class="p-4 font-normal text-gray-800">
                        <h1 class="mb-4 text-3xl font-bold leading-none tracking-tight text-gray-800">2020 National
                            Championship</h1>
                        <p class="leading-normal line-clamp-3">The College Football Playoff (CFP) determines the
                            national
                            champion of
                            the
                            top division of college football. The format fits within the academic calendar and preserves
                            the
                            sport’s unique and compelling regular season.</p>
                    </div>
                </div>
            </div>
        </div>
    </x-container>
</x-layouts.marketing>
