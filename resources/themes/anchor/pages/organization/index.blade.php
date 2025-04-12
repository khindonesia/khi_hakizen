<?php
use function Laravel\Folio\{name};
name('organization');

$teams = \App\Models\Organization::paginate(5);
?>

<x-layouts.marketing :seo="[
    'title' => 'Organization',
    'description' => 'Organization',
]">
    <div class="w-full bg-center bg-cover h-[20rem] lg:h-[38rem]"
        style="background-image: url('https://static.wixstatic.com/media/4dda9f_9ef8c0b1d37349c19b2c061f1f321e2d.jpg/v1/fill/w_1000,h_666,al_c,q_85,usm_0.66_1.00_0.01/4dda9f_9ef8c0b1d37349c19b2c061f1f321e2d.jpg');">
        <div class="flex items-center justify-center w-full h-full bg-gray-900/40">
            <div class="text-center mx-8">
                <h1 class="text-3xl font-semibold text-white lg:text-7xl">Komunitas Historia Indonesia</h1>
                <h2 class="text-3xl font-semibold text-white lg:text-7xl">Organization</h2>
            </div>
        </div>
    </div>
    <x-container>
        <div class="py-12">
            <x-marketing.elements.heading title="Visi" description="" align="center" />
            <p class="text-2xl text-center">
                Menumbuhkan pemahaman akan sejarah dan budaya bangsa sebagai sumber patriotisme dan nasionalisme rakyat
                Indonesia.
            </p>
        </div>

        <section class="bg-white dark:bg-gray-900">
            <div class="container px-6 py-10 mx-auto">
                <x-marketing.elements.heading title="Our Team"
                    description="Tim kami terdiri dari individu-individu yang penuh semangat, berdedikasi untuk mempromosikan sejarah, budaya, dan nasionalisme Indonesia. Bersama-sama, kami mengorganisir acara, program, dan tur edukatif yang menginspirasi masyarakat untuk lebih mengenal dan mencintai warisan bangsa."
                    align="center" />

                <div class="grid grid-cols-1 gap-8 mt-8 xl:mt-16 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($teams as $team)
                        <div
                            class="flex flex-col items-center p-8 transition-colors duration-300 transform border cursor-pointer rounded-xl hover:border-transparent group hover:bg-[#c6303e] dark:border-gray-700 dark:hover:border-transparent">
                            <img class="object-cover w-32 h-32 rounded-full ring-4 ring-gray-300"
                                src="{{ Storage::url('/' . $team->avatar) }}"
                                alt="">

                            <a href="{{ url('/organization') . '/' . $team->id }}" wire:navigate
                                class="mt-4 text-2xl font-semibold text-gray-700 capitalize dark:text-white group-hover:text-white">
                                {{ $team->name }}
                            </a>

                            <p class="mt-2 text-center text-gray-500 capitalize dark:text-gray-300 group-hover:text-gray-300">design
                                {{ $team->position }}
                            </p>

                            <div class="flex mt-3 -mx-2">
                                <!-- Facebook -->
                                <a href="{{ $team->facebook_url }}" 
                                    class="mx-2 text-gray-600 dark:text-gray-300 hover:text-gray-500 dark:hover:text-gray-300 group-hover:text-white"
                                    aria-label="Facebook" target="_blank">
                                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M2.00195 12.002C2.00312 16.9214 5.58036 21.1101 10.439 21.881V14.892H7.90195V12.002H10.442V9.80204C10.3284 8.75958 10.6845 7.72064 11.4136 6.96698C12.1427 6.21332 13.1693 5.82306 14.215 5.90204C14.9655 5.91417 15.7141 5.98101 16.455 6.10205V8.56104H15.191C14.7558 8.50405 14.3183 8.64777 14.0017 8.95171C13.6851 9.25566 13.5237 9.68693 13.563 10.124V12.002H16.334L15.891 14.893H13.563V21.881C18.8174 21.0506 22.502 16.2518 21.9475 10.9611C21.3929 5.67041 16.7932 1.73997 11.4808 2.01722C6.16831 2.29447 2.0028 6.68235 2.00195 12.002Z">
                                        </path>
                                    </svg>
                                </a>
                            
                                <!-- Instagram -->
                                <a href="{{ $team->instagram_url }}" 
                                    class="mx-2 text-gray-600 dark:text-gray-300 hover:text-gray-500 dark:hover:text-gray-300 group-hover:text-white"
                                    aria-label="Instagram" target="_blank">
                                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM12 18C8.68629 18 6 15.3137 6 12C6 8.68629 8.68629 6 12 6C15.3137 6 18 8.68629 18 12C18 15.3137 15.3137 18 12 18ZM12 8C10.3431 8 9 9.34315 9 11C9 12.6569 10.3431 14 12 14C13.6569 14 15 12.6569 15 11C15 9.34315 13.6569 8 12 8ZM12 13C11.4477 13 11 12.5523 11 12C11 11.4477 11.4477 11 12 11C12.5523 11 13 11.4477 13 12C13 12.5523 12.5523 13 12 13Z">
                                        </path>
                                    </svg>
                                </a>
                            
                                <!-- LinkedIn -->
                                <a href="{{ $team->linkedin_url }}" 
                                    class="mx-2 text-gray-600 dark:text-gray-300 hover:text-gray-500 dark:hover:text-gray-300 group-hover:text-white"
                                    aria-label="LinkedIn" target="_blank">
                                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C21.9939 17.5203 17.5203 21.9939 12 22C6.47715 22 2 17.5228 2 12ZM14.29 15.29L13.53 16.05C13.24 16.34 12.77 16.34 12.47 16.05L10.47 14.05C10.18 13.76 10.18 13.24 10.47 12.94C10.77 12.64 11.24 12.64 11.53 12.94L12.53 13.94L15.47 10.94C15.77 10.64 16.24 10.64 16.53 10.94C16.82 11.24 16.82 11.76 16.53 12.06L14.29 15.29ZM10 6.5C10.8284 6.5 11.5 7.17157 11.5 8C11.5 8.82843 10.8284 9.5 10 9.5C9.17157 9.5 8.5 8.82843 8.5 8C8.5 7.17157 9.17157 6.5 10 6.5ZM16 12C16 10.3431 14.6569 9 13 9C11.3431 9 10 10.3431 10 12C10 13.6569 11.3431 15 13 15C14.6569 15 16 13.6569 16 12Z">
                                        </path>
                                    </svg>
                                </a>
                            
                                <!-- Twitter -->
                                <a href="{{ $team->twitter_url }}" 
                                    class="mx-2 text-gray-600 dark:text-gray-300 hover:text-gray-500 dark:hover:text-gray-300 group-hover:text-white"
                                    aria-label="Twitter" target="_blank">
                                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M22 5.403a8.287 8.287 0 0 1-2.357.646 4.145 4.145 0 0 0 1.818-2.292A8.367 8.367 0 0 1 18.144 5.15a4.127 4.127 0 0 0-7.046 3.013 11.623 11.623 0 0 1-8.446-4.283A4.125 4.125 0 0 0 2 6.716a4.113 4.113 0 0 0 1.26 5.492A4.149 4.149 0 0 1 .8 11.7v.052a4.125 4.125 0 0 0 3.295 4.037 4.053 4.053 0 0 1-1.095.146c-.267 0-.526-.026-.782-.075a4.126 4.126 0 0 0 3.856 2.865 8.3 8.3 0 0 1-5.125 1.772 8.388 8.388 0 0 1-.99-.058A11.553 11.553 0 0 0 7.293 22a11.644 11.644 0 0 0 11.856-11.856c0-.18 0-.356-.011-.535A8.206 8.206 0 0 0 22 5.403z">
                                        </path>
                                    </svg>
                                </a>
                            </div>
                            
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Features -->
        <div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
            <!-- Grid -->
            <div class="md:grid md:grid-cols-2 md:items-center md:gap-12 xl:gap-32">
                <div>
                    <img class="rounded-xl"
                        src="https://images.unsplash.com/photo-1648737963503-1a26da876aca?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=900&h=900&q=80"
                        alt="Features Image">
                </div>
                <!-- End Col -->

                <div class="mt-5 sm:mt-10 lg:mt-0">
                    <div class="space-y-6 sm:space-y-8">
                        <!-- Title -->
                        <div class="space-y-2 md:space-y-4">
                            <h2 class="font-bold text-3xl lg:text-4xl text-gray-800">
                                Achievements
                            </h2>
                        </div>
                        <!-- End Title -->

                        <!-- List -->
                        <ul class="space-y-2 sm:space-y-4">
                            <li class="flex gap-x-3">
                                <span
                                    class="mt-0.5 size-5 flex justify-center items-center rounded-full bg-blue-50 text-blue-600">
                                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                </span>
                                <div class="grow">
                                    <span class="text-sm sm:text-base text-gray-500">
                                        <span class="font-bold">Komunitas Peduli Museum</span> – Penghargaan dari <span
                                            class="font-bold">Museum Sejarah Jakarta & Gubernur DKI Jakarta</span> pada
                                        tahun 2003/2004.
                                    </span>
                                </div>
                            </li>
                            <li class="flex gap-x-3">
                                <span
                                    class="mt-0.5 size-5 flex justify-center items-center rounded-full bg-blue-50 text-blue-600">
                                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                </span>
                                <div class="grow">
                                    <span class="text-sm sm:text-base text-gray-500">
                                        <span class="font-bold">Most Recommended Consumer Community Award</span> –
                                        Diterima dari <span class="font-bold">SWA Magazine</span> pada tahun 2010.
                                    </span>
                                </div>
                            </li>
                            <li class="flex gap-x-3">
                                <span
                                    class="mt-0.5 size-5 flex justify-center items-center rounded-full bg-blue-50 text-blue-600">
                                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                </span>
                                <div class="grow">
                                    <span class="text-sm sm:text-base text-gray-500">
                                        <span class="font-bold">The Best Entrepreneurial & Business Community
                                            Award</span> – Diterima dari <span class="font-bold">Prasetya Mulya
                                            Business School</span> pada tahun 2010.
                                    </span>
                                </div>
                            </li>
                            <li class="flex gap-x-3">
                                <span
                                    class="mt-0.5 size-5 flex justify-center items-center rounded-full bg-blue-50 text-blue-600">
                                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                </span>
                                <div class="grow">
                                    <span class="text-sm sm:text-base text-gray-500">
                                        <span class="font-bold">Komunitas Peduli Museum</span> – Penghargaan dari <span
                                            class="font-bold">Museum Bahari, Dinas Pariwisata & Kebudayaan DKI
                                            Jakarta</span> pada tahun 2013.
                                    </span>
                                </div>
                            </li>
                            <li class="flex gap-x-3">
                                <span
                                    class="mt-0.5 size-5 flex justify-center items-center rounded-full bg-blue-50 text-blue-600">
                                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                </span>
                                <div class="grow">
                                    <span class="text-sm sm:text-base text-gray-500">
                                        <span class="font-bold">Pengabdian Terhadap Kelestarian Budaya Indonesia</span>
                                        – Diterima dari <span class="font-bold">NutriSari W'dank, Nutrifood</span> pada
                                        tahun 2014.
                                    </span>
                                </div>
                            </li>
                            <li class="flex gap-x-3">
                                <span
                                    class="mt-0.5 size-5 flex justify-center items-center rounded-full bg-blue-50 text-blue-600">
                                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                </span>
                                <div class="grow">
                                    <span class="text-sm sm:text-base text-gray-500">
                                        <span class="font-bold">Komunitas Kreatif yang Berkhidmat Terhadap Tanah Air
                                            Indonesia</span> – Penghargaan dari <span class="font-bold">Menteri
                                            Pendidikan & Kebudayaan RI</span> pada tahun 2018.
                                    </span>
                                </div>
                            </li>
                        </ul>
                        <!-- End List -->
                    </div>
                </div>
                <!-- End Col -->
            </div>
            <!-- End Grid -->
        </div>
        <!-- End Features -->

        <!-- Features -->
        <div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
            <!-- Grid -->
            <div class="md:grid md:grid-cols-2 md:items-center md:gap-12 xl:gap-32">
                <div class="mt-5 sm:mt-10 lg:mt-0">
                    <div class="space-y-6 sm:space-y-8">
                        <!-- Title -->
                        <div class="space-y-2 md:space-y-4">
                            <h2 class="font-bold text-3xl lg:text-4xl text-gray-800">
                                Milestones
                            </h2>
                        </div>
                        <!-- End Title -->

                        <!-- List -->
                        <ul class="space-y-2 sm:space-y-4">
                            <li class="flex gap-x-3">
                                <span
                                    class="mt-0.5 size-5 flex justify-center items-center rounded-full bg-blue-50 text-blue-600">
                                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                </span>
                                <div class="grow">
                                    <span class="text-sm sm:text-base text-gray-500">
                                        <span class="font-bold">Pendirian KHI (2003):</span> KHI didirikan pada <span
                                            class="font-bold">22 Maret 2003</span> oleh Asep Kambali bersama
                                        teman-temannya dari Universitas Negeri Jakarta (UNJ) dan Universitas Indonesia
                                        (UI).
                                    </span>
                                </div>
                            </li>
                            <li class="flex gap-x-3">
                                <span
                                    class="mt-0.5 size-5 flex justify-center items-center rounded-full bg-blue-50 text-blue-600">
                                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                </span>
                                <div class="grow">
                                    <span class="text-sm sm:text-base text-gray-500">
                                        <span class="font-bold">Perubahan Nama (2006):</span> Pada tahun 2006,
                                        KPSBI-Historia diubah menjadi <span class="font-bold">Komunitas Historia
                                            Indonesia (KHI).</span>
                                    </span>
                                </div>
                            </li>
                            <li class="flex gap-x-3">
                                <span
                                    class="mt-0.5 size-5 flex justify-center items-center rounded-full bg-blue-50 text-blue-600">
                                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                </span>
                                <div class="grow">
                                    <span class="text-sm sm:text-base text-gray-500">
                                        <span class="font-bold">Peluncuran Platform Online (2014):</span> Pada Januari
                                        2014, KHI meluncurkan <span class="font-bold">platform baru</span> berupa <span
                                            class="font-bold">social media website</span> yang menjadi sarana
                                        komunikasi antar anggota dan masyarakat luas.
                                    </span>
                                </div>
                            </li>
                            <li class="flex gap-x-3">
                                <span
                                    class="mt-0.5 size-5 flex justify-center items-center rounded-full bg-blue-50 text-blue-600">
                                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                </span>
                                <div class="grow">
                                    <span class="text-sm sm:text-base text-gray-500">
                                        <span class="font-bold">Kerja Sama dengan Kementerian Pertahanan RI
                                            (2016):</span> Pada <span class="font-bold">12 Februari 2016,</span> KHI
                                        menandatangani <span class="font-bold">nota kesepahaman</span> dengan <span
                                            class="font-bold">Kementrian Pertahanan RI</span> tentang kerja sama
                                        gerakan <span class="font-bold">Bela Negara.</span>
                                    </span>
                                </div>
                            </li>
                            <li class="flex gap-x-3">
                                <span
                                    class="mt-0.5 size-5 flex justify-center items-center rounded-full bg-blue-50 text-blue-600">
                                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                </span>
                                <div class="grow">
                                    <span class="text-sm sm:text-base text-gray-500">
                                        <span class="font-bold">Anggota yang Terus Bertambah:</span> KHI kini memiliki
                                        lebih dari <span class="font-bold">23.000 anggota</span> yang tersebar di
                                        seluruh dunia.
                                    </span>
                                </div>
                            </li>
                        </ul>
                        <!-- End List -->
                    </div>
                </div>
                <!-- End Col -->
                <div>
                    <img class="rounded-xl"
                        src="https://images.unsplash.com/photo-1648737963503-1a26da876aca?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=900&h=900&q=80"
                        alt="Features Image">
                </div>
                <!-- End Col -->
            </div>
            <!-- End Grid -->
        </div>
        <!-- End Features -->
    </x-container>
</x-layouts.marketing>
