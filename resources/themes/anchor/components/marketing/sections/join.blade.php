@props([
    'homePageContent' => null,
    'achievements' => null,
])

@php
    $achievements = $achievements ?? ($homePageContent ? $homePageContent->achievements()->ordered()->get() : collect());
@endphp

<!-- Features -->
<div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
    <!-- Grid -->
    <div class="md:grid md:grid-cols-2 md:items-center md:gap-12 xl:gap-32">
        <div>
            <img class="rounded-xl"
                src="{{ $homePageContent?->leader_image ? Storage::url($homePageContent->leader_image) : url('/images/achievement.jpg') }}"
                alt="Leader Image">
        </div>
        <!-- End Col -->

        <div class="mt-5 sm:mt-10 lg:mt-0">
            <div class="space-y-4 sm:space-y-6">
                <!-- Title -->
                <div class="space-y-2 md:space-y-4">
                    <h2 class="font-bold text-3xl lg:text-4xl text-gray-800">
                        {{ $homePageContent?->org_name ?? 'Komunitas Historia Indonesia' }}
                        @if ($homePageContent?->org_acronym)
                            ({{ $homePageContent->org_acronym }})
                        @endif
                    </h2>
                    <p class="text-gray-500">
                        {{ $homePageContent?->org_description ?? 'Komunitas sejarah yang aktif mengedukasi publik melalui program, tulisan, dan kegiatan lapangan.' }}
                    </p>
                </div>
                <!-- End Title -->


                <!-- List -->
                <ul class="space-y-2">
                    <h3 class="font-bold text-xl text-gray-800">Prestasi & Penghargaan</h3>
                    @foreach ($achievements as $achievement)
                        <li class="flex items-center gap-x-3">
                            <span
                                class="mt-0.5 size-5 flex justify-center items-center rounded-full bg-red-50 text-red-600">
                                <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24"
                                    height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </span>
                            <div class="grow">
                                <span class="text-sm sm:text-base text-gray-500">
                                    <span class="font-medium">
                                        {{ $achievement->achievement_title }}
                                    </span>
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <x-button href="{{ route('organization') }}" wire:navigate tag="a" class="text-sm">
                    Lihat selengkapnya
                </x-button>
                <!-- End List -->
            </div>
        </div>
        <!-- End Col -->
    </div>
    <!-- End Grid -->
</div>
<!-- End Features -->
