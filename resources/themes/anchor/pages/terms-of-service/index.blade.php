<?php
use function Laravel\Folio\name;

name('terms-of-service');
?>

<x-layouts.marketing :seo="[
    'title' => 'Terms of Service - Komunitas Historia Indonesia',
    'description' => 'Terms of Service for Komunitas Historia Indonesia.',
    'image' => url('/og_image.png'),
    'type' => 'website',
]">
    <div class="bg-[#F8FAFC] min-h-screen font-['Inter'] pb-20">
        <section class="bg-white border-b border-gray-200/80 py-16 md:py-24 text-center">
            <x-container class="max-w-4xl mx-auto px-6">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#df1c24]/10 border border-[#df1c24]/20 text-xs font-bold text-[#df1c24] uppercase tracking-wider mb-6">
                    Legal
                </div>
                <h1 class="text-4xl md:text-[56px] font-extrabold text-gray-900 tracking-tight leading-none">
                    Terms of Service
                </h1>
                <p class="text-gray-600 max-w-2xl text-base md:text-lg mt-6 mx-auto leading-relaxed">
                    These terms govern use of Komunitas Historia Indonesia and its community features.
                </p>
            </x-container>
        </section>

        <main class="w-full max-w-4xl mx-auto px-6 mt-16">
            <div class="bg-white rounded-2xl border border-gray-200/70 p-8 md:p-10 space-y-6 text-gray-700 leading-relaxed">
                <p>Use the site respectfully and follow applicable laws and community guidelines.</p>
                <p>Content may be updated, removed, or moderated when needed to keep the platform safe and useful.</p>
                <p>By using the site, you agree to the terms and policies published here.</p>
            </div>
        </main>
    </div>
</x-layouts.marketing>
