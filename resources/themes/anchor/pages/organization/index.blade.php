<?php
use function Laravel\Folio\{name};
name('organization');
?>

<x-layouts.marketing :seo="[
    'title' => 'Struktur, Visi & Perjalanan - Komunitas Historia Indonesia',
    'description' => 'Mengenal struktur organisasi Komunitas Historia Indonesia, visi misi, tata kelola kepengurusan, serta lini masa pencapaian kami sejak 2003.',
]">
@php
        $teams = collect();
        try {
            $teams = \App\Models\Organization::all();
        } catch (\Throwable $e) {
            $teams = collect();
        }

        $achievements = [
            [
                'year' => '2003/2004',
                'title' => 'Komunitas Peduli Museum',
                'provider' => 'Museum Sejarah Jakarta & Gubernur DKI Jakarta',
                'tint' => 'peach',
                'border' => 'border-[#FFF0EA]',
                'bg' => 'bg-[#FFF0EA]',
                'text' => 'text-[#E06D3B]',
                'icon' => 'emoji_events',
            ],
            [
                'year' => '2010',
                'title' => 'Most Recommended Consumer Community Award',
                'provider' => 'SWA Magazine',
                'tint' => 'sky',
                'border' => 'border-[#EBF5FF]',
                'bg' => 'bg-[#EBF5FF]',
                'text' => 'text-[#006ADC]',
                'icon' => 'thumb_up',
            ],
            [
                'year' => '2010',
                'title' => 'The Best Entrepreneurial & Business Community Award',
                'provider' => 'Prasetya Mulya Business School',
                'tint' => 'mint',
                'border' => 'border-[#EBF9F4]',
                'bg' => 'bg-[#EBF9F4]',
                'text' => 'text-[#107F5B]',
                'icon' => 'workspace_premium',
            ],
            [
                'year' => '2013',
                'title' => 'Komunitas Peduli Museum',
                'provider' => 'Museum Bahari, Dinas Pariwisata & Kebudayaan DKI Jakarta',
                'tint' => 'lavender',
                'border' => 'border-[#fff5f5]',
                'bg' => 'bg-[#fff5f5]',
                'text' => 'text-[#df1c24]',
                'icon' => 'museum',
            ],
            [
                'year' => '2014',
                'title' => 'Pengabdian Terhadap Kelestarian Budaya Indonesia',
                'provider' => 'NutriSari W\'dank, Nutrifood',
                'tint' => 'rose',
                'border' => 'border-[#FFECF0]',
                'bg' => 'bg-[#FFECF0]',
                'text' => 'text-[#E03B6D]',
                'icon' => 'volunteer_activism',
            ],
            [
                'year' => '2018',
                'title' => 'Komunitas Kreatif yang Berkhidmat Terhadap Tanah Air Indonesia',
                'provider' => 'Menteri Pendidikan & Kebudayaan RI',
                'tint' => 'yellow',
                'border' => 'border-[#FFF9E6]',
                'bg' => 'bg-[#FFF9E6]',
                'text' => 'text-[#A07000]',
                'icon' => 'verified',
            ],
        ];

        $milestones = [
            [
                'year' => '2003',
                'title' => 'Pendirian KHI',
                'desc' => 'KHI didirikan pada 22 Maret 2003 oleh Asep Kambali bersama mahasiswa UNJ dan UI di Jakarta.',
                'tint' => 'peach',
                'bg' => 'bg-[#FFF0EA]',
                'text' => 'text-[#E06D3B]',
                'border' => 'border-[#FFF0EA]',
            ],
            [
                'year' => '2003/2004',
                'title' => 'Advokasi Museum',
                'desc' => 'Mendapatkan penghargaan kepedulian museum dari Gubernur DKI Jakarta atas kampanye revitalisasi museum.',
                'tint' => 'orange',
                'bg' => 'bg-[#FFF0EA]',
                'text' => 'text-[#E06D3B]',
                'border' => 'border-[#FFF0EA]',
            ],
            [
                'year' => '2010',
                'title' => 'Pengakuan Publik',
                'desc' => 'Dianugerahi Best Business Community Award atas kemampuan tata kelola organisasi yang mandiri.',
                'tint' => 'mint',
                'bg' => 'bg-[#EBF9F4]',
                'text' => 'text-[#107F5B]',
                'border' => 'border-[#EBF9F4]',
            ],
            [
                'year' => '2018',
                'title' => 'Apresiasi Nasional',
                'desc' => 'Menerima penghargaan kepedulian sejarah dan cagar budaya dari Menteri Pendidikan & Kebudayaan RI.',
                'tint' => 'purple',
                'bg' => 'bg-[#fff5f5]',
                'text' => 'text-[#df1c24]',
                'border' => 'border-[#fff5f5]',
            ],
        ];
    @endphp

    <div class="bg-[#fffafb] min-h-screen font-['Inter'] py-12 md:py-20">
        <x-container class="space-y-24">
            
            <!-- Page Title Header -->
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 border border-primary/20 text-xs font-bold text-primary uppercase tracking-wider">
                    <span class="material-symbols-outlined text-[14px]">account_tree</span>
                    {{ setting('org_page_chip', 'Portal Informasi KHI') }}
                </div>
                <h1 class="text-4xl md:text-[56px] leading-[1.1] font-bold text-[#000000] tracking-tight">
                    {{ setting('org_page_title', 'Structure, Vision & Heritage') }}
                </h1>
                <p class="text-sm md:text-base text-[#575e75] leading-[1.6]">
                    {{ setting('org_page_subtitle', 'Mengenal tata kelola keorganisasian, komitmen pelestarian nilai sejarah, serta lini masa pencapaian luar biasa Komunitas Historia Indonesia sejak tahun 2003.') }}
                </p>
            </div>

            <!-- Mission & Vision (Split side-by-side equal-width white cards) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-8">
                <!-- Vision -->
                <div class="bg-white border border-[#E9E9E8] rounded-[32px] p-8 md:p-12 space-y-4 shadow-sm hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-2xl bg-[#FFF0EA] flex items-center justify-center text-[#E06D3B]">
                        <span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;">visibility</span>
                    </div>
                    <h3 class="text-2xl font-bold text-[#000000] tracking-tight">Our Vision</h3>
                    <div class="text-sm md:text-base text-charcoal leading-[1.7]">
                        {!! setting_sanitized('about_vision', 'Menjadi lembaga pelestarian sejarah dan budaya independen terkemuka di Asia Tenggara, menumbuhkan pemahaman mendalam bangsa sebagai pondasi patriotisme rakyat Indonesia yang berkepribadian mandiri.') !!}
                    </div>
                    <div class="flex flex-wrap gap-2 pt-2">
                        <span class="px-3 py-1 rounded-full bg-[#fffafb] border border-[#E9E9E8] text-[11px] font-semibold text-[#575e75]">Preservation</span>
                        <span class="px-3 py-1 rounded-full bg-[#fffafb] border border-[#E9E9E8] text-[11px] font-semibold text-[#575e75]">Independent</span>
                        <span class="px-3 py-1 rounded-full bg-[#fffafb] border border-[#E9E9E8] text-[11px] font-semibold text-[#575e75]">Scholarly</span>
                    </div>
                </div>
                <!-- Mission -->
                <div class="bg-white border border-[#E9E9E8] rounded-[32px] p-8 md:p-12 space-y-4 shadow-sm hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-2xl bg-[#fff5f5] flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;">track_changes</span>
                    </div>
                    <h3 class="text-2xl font-bold text-[#000000] tracking-tight">Our Mission</h3>
                    <div class="text-sm md:text-base text-charcoal leading-[1.7]">
                        {!! setting_sanitized('about_mission', 'Mengemas pembelajaran nilai luhur sejarah secara rekreatif, edukatif, dan interaktif guna mendidik generasi muda aktif menjaga kelestarian cagar budaya serta warisan pusaka nasional.') !!}
                    </div>
                    <div class="flex flex-wrap gap-2 pt-2">
                        <span class="px-3 py-1 rounded-full bg-[#fffafb] border border-[#E9E9E8] text-[11px] font-semibold text-[#575e75]">Educators</span>
                        <span class="px-3 py-1 rounded-full bg-[#fffafb] border border-[#E9E9E8] text-[11px] font-semibold text-[#575e75]">Community</span>
                        <span class="px-3 py-1 rounded-full bg-[#fffafb] border border-[#E9E9E8] text-[11px] font-semibold text-[#575e75]">Interactive</span>
                    </div>
                </div>
            </div>

            <!-- Our Team (Database Driven & Fallback) -->
            @if ($teams->isNotEmpty())
            <div class="space-y-10">
                <div class="text-center max-w-3xl mx-auto space-y-4">
                    <h3 class="text-3xl md:text-4xl font-bold text-[#000000] tracking-tight">
                        {{ setting('org_page_team_title', 'Our Team') }}
                    </h3>
                    <p class="text-sm md:text-base text-[#575e75] leading-[1.6]">
                        {{ setting('org_page_team_subtitle', 'Tim kami terdiri dari individu-individu yang penuh semangat, berdedikasi untuk mempromosikan sejarah, budaya, dan nasionalisme Indonesia. Bersama-sama, kami mengorganisir acara, program, dan tur edukatif yang menginspirasi masyarakat untuk lebih mengenal dan mencintai warisan bangsa.') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                    @foreach ($teams as $team)
                        <div class="bg-white border border-[#E9E9E8] rounded-3xl p-6 flex flex-col items-center justify-between text-center hover:shadow-[0_12px_32px_rgba(86,37,168,0.05)] hover:border-primary/20 hover:-translate-y-0.5 transition duration-300 group">
                            <div class="space-y-4 w-full">
                                <div class="relative w-32 h-32 mx-auto rounded-full overflow-hidden border border-[#E9E9E8] p-1 bg-zinc-50 group-hover:scale-105 transition duration-300">
                                    @if(isset($team->avatar) && $team->avatar)
                                        <img class="w-full h-full object-cover rounded-full" 
                                             src="{{ Storage::url($team->avatar) }}" 
                                             alt="{{ $team->name }}">
                                    @else
                                        <div class="w-full h-full bg-primary/10 rounded-full flex items-center justify-center text-primary font-bold text-3xl">
                                            {{ substr($team->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-bold text-lg text-[#000000] tracking-tight group-hover:text-primary transition-colors">
                                        {{ $team->name }}
                                    </h4>
                                    <p class="text-xs text-[#979A9B] font-semibold uppercase tracking-wider mt-1">
                                        {{ $team->position }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-col gap-3 w-full">
                                <div class="flex justify-center gap-3">
                                    @if(isset($team->facebook_url) && $team->facebook_url)
                                        <a href="{{ $team->facebook_url }}" target="_blank" class="text-zinc-400 hover:text-primary transition" title="Facebook">
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                        </a>
                                    @endif
                                    @if(isset($team->instagram_url) && $team->instagram_url)
                                        <a href="{{ $team->instagram_url }}" target="_blank" class="text-zinc-400 hover:text-primary transition" title="Instagram">
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051C.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                        </a>
                                    @endif
                                    @if(isset($team->linkedin_url) && $team->linkedin_url)
                                        <a href="{{ $team->linkedin_url }}" target="_blank" class="text-zinc-400 hover:text-primary transition" title="LinkedIn">
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.779-1.75-1.75s.784-1.75 1.75-1.75 1.75.779 1.75 1.75-.784 1.75-1.75 1.75zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                                        </a>
                                    @endif
                                    @if(isset($team->twitter_url) && $team->twitter_url)
                                        <a href="{{ $team->twitter_url }}" target="_blank" class="text-zinc-400 hover:text-primary transition" title="X (Twitter)">
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Achievements Section (3-Column Layout with custom card designs) -->
            <div class="space-y-10">
                <div class="text-center max-w-2xl mx-auto space-y-3">
                    <h2 class="text-3xl md:text-4xl font-bold text-[#000000] tracking-tight">
                        {{ setting('org_page_achievements_title', 'Penghargaan & Prestasi') }}
                    </h2>
                    <p class="text-sm text-[#575e75] leading-relaxed">
                        {{ setting('org_page_achievements_subtitle', 'Dedikasi KHI mendapat apresiasi tinggi dan pengakuan nasional dari kementerian serta lembaga terkemuka.') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($achievements as $ach)
                        <div class="bg-white border border-[#E9E9E8] rounded-3xl p-6 md:p-8 flex flex-col justify-between hover:shadow-md hover:border-primary/20 hover:-translate-y-0.5 transition duration-300 group">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $ach['bg'] }} {{ $ach['text'] }}">
                                        {{ $ach['year'] }}
                                    </span>
                                    <span class="material-symbols-outlined {{ $ach['text'] }} text-[24px]">
                                        {{ $ach['icon'] }}
                                    </span>
                                </div>
                                <h4 class="font-bold text-base text-[#000000] tracking-tight group-hover:text-primary transition-colors">
                                    {{ $ach['title'] }}
                                </h4>
                                <p class="text-xs text-charcoal leading-relaxed">
                                    Pemberi: <span class="font-semibold text-[#000000]">{{ $ach['provider'] }}</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Milestones Timeline Section (Horizontal Sequence layout spec) -->
            <div class="space-y-12 pb-12">
                <div class="text-center max-w-2xl mx-auto space-y-3">
                    <h3 class="text-2xl md:text-3xl font-bold text-[#000000] tracking-tight">
                        {{ setting('org_page_milestones_title', 'Lini Masa Perjalanan KHI') }}
                    </h3>
                    <p class="text-xs text-[#575e75]">
                        {{ setting('org_page_milestones_subtitle', 'Menyusuri tonggak-tonggak sejarah pembentukan Komunitas Historia Indonesia.') }}
                    </p>
                </div>

                <!-- Horizontally scrolling/sequence card structure as spec -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($milestones as $ms)
                        <div class="bg-white border border-[#E9E9E8] rounded-3xl p-6 hover:shadow-md hover:border-primary/20 transition duration-300 flex flex-col justify-between relative space-y-4">
                            <div class="space-y-3">
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $ms['bg'] }} {{ $ms['text'] }} inline-block">
                                    {{ $ms['year'] }}
                                </span>
                                <h4 class="font-bold text-base text-[#000000] tracking-tight">
                                    {{ $ms['title'] }}
                                </h4>
                                <p class="text-xs text-[#575e75] leading-relaxed">
                                    {{ $ms['desc'] }}
                                </p>
                            </div>
                            <div class="pt-4 border-t border-[#E9E9E8] flex justify-end">
                                <span class="material-symbols-outlined text-[18px] {{ $ms['text'] }}">check_circle</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Collaboration & Sponsor CTA Section -->
            <div class="border-t border-[#E9E9E8] pt-16 pb-12">
                <div class="bg-gradient-to-br from-brand-navy-deep to-brand-navy-mid rounded-[32px] p-8 md:p-16 text-white relative overflow-hidden shadow-xl">
                    <!-- Background decorative glow -->
                    <div class="absolute -right-24 -bottom-24 w-80 h-80 bg-primary/10 rounded-full blur-[100px] pointer-events-none"></div>
                    <div class="absolute -left-24 -top-24 w-80 h-80 bg-tertiary/10 rounded-full blur-[100px] pointer-events-none"></div>

                    <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                        <div class="lg:col-span-7 space-y-6">
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/20 text-xs font-bold text-white uppercase tracking-wider">
                                <span class="material-symbols-outlined text-[14px]">handshake</span>
                                {{ setting('org_page_collab_chip', 'Sponsorship & Partnership') }}
                            </span>
                            <h2 class="text-3xl md:text-5xl font-bold tracking-tight leading-[1.1] text-white">
                                {{ setting('org_page_collab_title', 'Kolaborasi Bersama KHI') }}
                            </h2>
                            <p class="text-sm md:text-base text-zinc-300 leading-relaxed max-w-2xl">
                                {{ setting('org_page_collab_subtitle', 'Komunitas Historia Indonesia (KHI) membuka kesempatan seluas-luasnya bagi perusahaan, institusi, dan sponsor untuk bermitra dalam melestarikan sejarah, cagar budaya, dan mengedukasi publik. Mari bersama wujudkan sinergi positif demi menjaga memori kolektif bangsa.') }}
                            </p>
                        </div>
                        
                        <div class="lg:col-span-5 bg-white/5 border border-white/10 rounded-2xl p-6 md:p-8 space-y-6 backdrop-blur-sm">
                            <h3 class="text-lg font-bold text-white tracking-tight border-b border-white/10 pb-3 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[20px] text-primary-container">contact_support</span>
                                {{ setting('org_page_collab_contact_title', 'Hubungi Tim Kemitraan') }}
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center text-primary-container">
                                        <span class="material-symbols-outlined text-[20px]">mail</span>
                                    </div>
                                    <div>
                                        <p class="text-xs text-zinc-400">{{ setting('org_page_collab_email_label', 'Email Resmi') }}</p>
                                        <a href="mailto:{{ setting('org_page_collab_email', 'info@komunitashistoria.com') }}" class="text-sm md:text-base font-semibold text-white hover:text-primary-container transition">
                                            {{ setting('org_page_collab_email', 'info@komunitashistoria.com') }}
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center text-primary-container">
                                        <span class="material-symbols-outlined text-[20px]">phone_iphone</span>
                                    </div>
                                    <div>
                                        <p class="text-xs text-zinc-400">{{ setting('org_page_collab_phone_label', 'WhatsApp / Telepon') }}</p>
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', setting('org_page_collab_phone', '+62 818-0807-3636')) }}" target="_blank" class="text-sm md:text-base font-semibold text-white hover:text-primary-container transition">
                                            {{ setting('org_page_collab_phone', '+62 818-0807-3636') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="pt-2">
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', setting('org_page_collab_phone', '+62 818-0807-3636')) }}" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary text-white rounded-xl font-bold text-sm shadow-md hover:bg-[#c41219] hover:shadow-lg transition duration-200">
                                    <span class="material-symbols-outlined text-[16px]">chat</span>
                                    {{ setting('org_page_collab_btn_text', 'Ajukan Penawaran') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </x-container>
    </div>
</x-layouts.marketing>
