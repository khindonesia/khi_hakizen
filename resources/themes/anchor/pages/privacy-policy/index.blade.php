<?php
use function Laravel\Folio\name;

name('privacy-policy');
?>

<x-layouts.marketing :seo="[
    'title' => 'Privacy Policy - Komunitas Historia Indonesia',
    'description' => 'Privacy Policy for Komunitas Historia Indonesia.',
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
                    Privacy Policy
                </h1>
                <p class="text-gray-600 max-w-2xl text-base md:text-lg mt-6 mx-auto leading-relaxed">
                    This page explains how Komunitas Historia Indonesia handles user data and site activity.
                </p>
            </x-container>
        </section>

        <main class="w-full max-w-4xl mx-auto px-6 mt-16">
            <div class="bg-white rounded-2xl border border-gray-200/70 p-8 md:p-10 space-y-6 text-gray-700 leading-relaxed">
                {!! setting_sanitized('privacy_policy', '<p>We only collect information needed to operate the site, manage accounts, and support community features.</p><p>We do not sell personal data. Shared data is limited to trusted service providers required for the app to function.</p><p>If you have privacy questions, contact the community team through the site\'s official channels.</p>') !!}
            </div>
        </main>
    </div>
</x-layouts.marketing>
