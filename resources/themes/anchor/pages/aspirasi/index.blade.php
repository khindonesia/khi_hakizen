<?php
use App\Actions\PublishAspirasiAction;
use App\Models\Aspirasi;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Filament\Notifications\Notification;
use function Laravel\Folio\{name};

name('aspirasi');

new class extends Component {
    use WithPagination;

    public string $categoryFilter = 'all';
    public string $tabFilter = 'all'; // all, my-posts
    
    // Form fields
    public string $title = '';
    public string $categorySlug = 'cagar-budaya';
    public string $body = '';
    public string $excerpt = '';

    protected $rules = [
        'title' => 'required|min:5|max:150',
        'categorySlug' => 'required|in:cagar-budaya,edukasi,komunitas,lainnya',
        'body' => 'required|min:15|max:5000',
        'excerpt' => 'nullable|max:200',
    ];

    public function submitAspiration(): void
    {
        if (!auth()->check()) {
            Notification::make()
                ->danger()
                ->title('Akses Ditolak')
                ->body('Anda harus masuk sebagai anggota untuk menulis aspirasi.')
                ->send();
            return;
        }

        $rateLimitKey = 'aspirasi-submit:user:' . auth()->id();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 6)) {
            $this->addError('rate_limit', 'Terlalu banyak kiriman aspirasi. Coba lagi sebentar.');

            Notification::make()
                ->danger()
                ->title('Terlalu Banyak Percobaan')
                ->body('Silakan coba kirim aspirasi lagi sebentar.')
                ->send();

            return;
        }

        RateLimiter::hit($rateLimitKey, 60);

        $this->validate();

        $categoryNameMap = [
            'cagar-budaya' => 'Cagar Budaya',
            'edukasi' => 'Edukasi Sejarah',
            'komunitas' => 'Kegiatan Komunitas',
            'lainnya' => 'Lainnya',
        ];
        $categoryName = $categoryNameMap[$this->categorySlug] ?? 'Lainnya';

        $category = \Wave\Category::firstOrCreate(
            ['slug' => $this->categorySlug],
            ['name' => $categoryName, 'order' => 10]
        );

        app(PublishAspirasiAction::class)->create(auth()->user(), [
            'category_id' => $category->id,
            'title' => $this->title,
            'body' => $this->body,
            'excerpt' => $this->excerpt ?: Str::limit(strip_tags($this->body), 150, ''),
            'slug' => Str::slug($this->title) . '-' . uniqid(),
        ]);

        $this->reset(['title', 'body', 'excerpt']);

        Notification::make()
            ->success()
            ->title('Aspirasi Terbit!')
            ->body('Terima kasih! Aspirasi Anda telah dipublikasikan sebagai artikel komunitas.')
            ->send();
            
        $this->dispatch('close-modal');
        $this->resetPage();
    }
}
?>

<x-layouts.marketing :seo="[
    'title' => 'Aspirasi Komunitas - Komunitas Historia Indonesia',
    'description' => 'Platform aspirasi, artikel, usulan cagar budaya, dan opini sejarah yang ditulis langsung oleh anggota Komunitas Historia Indonesia.',
]">
<div class="bg-[#fffafb] min-h-screen font-['Inter'] py-12 md:py-20" x-data="{ createOpen: false, authOpen: false }" @close-modal.window="createOpen = false" x-cloak>
        <x-container>
            @volt('aspirasi')
            <div class="w-full">
                <!-- Page Header -->
                <header class="mb-16 text-center max-w-3xl mx-auto">
                    <div class="inline-flex items-center gap-[8px] px-[16px] py-[4px] bg-[#df1c24]/10 text-[#df1c24] rounded-full mb-[16px] border border-[#df1c24]/20">
                        <span class="material-symbols-outlined text-[16px]">campaign</span>
                        <span class="text-[13px] font-semibold tracking-wide uppercase">{{ setting('aspirasi_chip', 'Suara Anggota KHI') }}</span>
                    </div>
                    <h1 class="text-5xl md:text-[80px] font-semibold tracking-[-2px] text-[#020611] leading-[1.05] mb-6">{{ setting('aspirasi_title', 'Aspirasi') }}</h1>
                    <p class="text-[18px] leading-[1.55] text-[#575e75]">
                        {{ setting('aspirasi_subtitle', 'Opini, esai, dan pemikiran mendalam mengenai pelestarian sejarah, identitas budaya, dan masa depan warisan Indonesia dari para anggota dan cendekiawan.') }}
                    </p>
                </header>

                <!-- Action & Tab Filters Bar -->
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#E9E9E8] pb-6 mb-8">
                    <div class="flex gap-2">
                        @if(auth()->check())
                            <button type="button" wire:click="$set('tabFilter', 'all')"
                                class="px-5 py-2.5 rounded-xl text-sm font-semibold tracking-tight transition duration-200 {{ $tabFilter === 'all' ? 'bg-[#df1c24] text-white shadow-sm' : 'bg-white text-[#37352F] border border-[#E9E9E8] hover:bg-zinc-50' }}">
                                Semua Aspirasi
                            </button>
                            <button type="button" wire:click="$set('tabFilter', 'my-posts')"
                                class="px-5 py-2.5 rounded-xl text-sm font-semibold tracking-tight transition duration-200 {{ $tabFilter === 'my-posts' ? 'bg-[#df1c24] text-white shadow-sm' : 'bg-white text-[#37352F] border border-[#E9E9E8] hover:bg-zinc-50' }}">
                                Tulisan Saya
                            </button>
                        @else
                            <div class="px-5 py-2.5 bg-white/50 border border-[#E9E9E8] rounded-xl text-sm font-medium text-[#575e75] flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-base">lock</span>
                                Halaman Publik
                            </div>
                        @endif
                    </div>

                    @if(auth()->check())
                        <button type="button" @click="createOpen = true" class="bg-[#df1c24] hover:bg-[#c41219] text-white font-semibold py-2.5 px-6 rounded-xl flex items-center justify-center gap-2 transition duration-200 shadow-sm hover:shadow-md">
                            <span class="material-symbols-outlined text-[20px]">edit_note</span>
                            <span>Tulis Aspirasi Baru</span>
                        </button>
                    @else
                        <button type="button" @click="authOpen = true" class="bg-[#df1c24] hover:bg-[#c41219] text-white font-semibold py-2.5 px-6 rounded-xl flex items-center justify-center gap-2 transition duration-200 shadow-sm hover:shadow-md">
                            <span class="material-symbols-outlined text-[20px]">edit_note</span>
                            <span>Tulis Aspirasi Baru</span>
                        </button>
                    @endif
                </div>

                <!-- Category Filters -->
                <div class="flex items-center justify-between mb-8 pb-4 border-b border-[#E9E9E8]">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="$set('categoryFilter', 'all')"
                            class="px-4 py-2 rounded-full text-xs font-semibold tracking-wide uppercase transition duration-200 {{ $categoryFilter === 'all' ? 'bg-[#37352F] text-white' : 'bg-white text-[#37352F] border border-[#E9E9E8] hover:bg-zinc-50' }}">
                            Semua Kategori
                        </button>
                        <button type="button" wire:click="$set('categoryFilter', 'cagar-budaya')"
                            class="px-4 py-2 rounded-full text-xs font-semibold tracking-wide uppercase transition duration-200 {{ $categoryFilter === 'cagar-budaya' ? 'bg-[#37352F] text-white' : 'bg-white text-[#37352F] border border-[#E9E9E8] hover:bg-zinc-50' }}">
                            Cagar Budaya
                        </button>
                        <button type="button" wire:click="$set('categoryFilter', 'edukasi')"
                            class="px-4 py-2 rounded-full text-xs font-semibold tracking-wide uppercase transition duration-200 {{ $categoryFilter === 'edukasi' ? 'bg-[#37352F] text-white' : 'bg-white text-[#37352F] border border-[#E9E9E8] hover:bg-zinc-50' }}">
                            Edukasi Sejarah
                        </button>
                        <button type="button" wire:click="$set('categoryFilter', 'komunitas')"
                            class="px-4 py-2 rounded-full text-xs font-semibold tracking-wide uppercase transition duration-200 {{ $categoryFilter === 'komunitas' ? 'bg-[#37352F] text-white' : 'bg-white text-[#37352F] border border-[#E9E9E8] hover:bg-zinc-50' }}">
                            Kegiatan Komunitas
                        </button>
                        <button type="button" wire:click="$set('categoryFilter', 'lainnya')"
                            class="px-4 py-2 rounded-full text-xs font-semibold tracking-wide uppercase transition duration-200 {{ $categoryFilter === 'lainnya' ? 'bg-[#37352F] text-white' : 'bg-white text-[#37352F] border border-[#E9E9E8] hover:bg-zinc-50' }}">
                            Lainnya
                        </button>
                    </div>
                </div>

                @php
                    $query = Aspirasi::query()->with(['user', 'category'])->where('status', 'PUBLISHED')->latest();
                    
                    if ($this->categoryFilter !== 'all') {
                        $query->whereHas('category', function($q) {
                            $q->where('slug', $this->categoryFilter);
                        });
                    }

                    if ($this->tabFilter === 'my-posts' && auth()->check()) {
                        $query->where('author_id', auth()->id());
                    }

                    $aspirations = $query->paginate(7); // 7 per page provides a beautiful layout with 1 featured and 6 grid items
                    
                    $isFirstPage = $aspirations->currentPage() === 1;
                    $featured = $isFirstPage ? $aspirations->first() : null;
                    $gridItems = $isFirstPage ? $aspirations->skip(1) : $aspirations;
                    
                    $getImage = function ($item, $defaultIndex = 0) {
                        if ($item->image && !str_starts_with($item->image, 'demo/')) {
                            return Storage::url($item->image);
                        }
                        
                        $defaults = [
                            'https://lh3.googleusercontent.com/aida-public/AB6AXuD-_JRsZevZGrabdo83i9ekT0fifmbkKCwxLd7VhyobQ2qi-gLYKwb_SfTLRaONbRkIOsk-cqdcjWMWyKR2OdXVOlpL8NQSZPZjtvn3IpLxeZUw4nahIyXMJq9oV0NcNQnrMypCTK4WmRTk4EZQ77BUvujbhMtqKoisLRttvAnxLWq2kbjNic8pNK1zMVX79s7f9OdrseK34Z5TKrMaY8wh7iQnLq2mg7neH9UXI_5l2zBDS7Y0R0PWodytiTnVRdQZH5-VOyZNy0n0',
                            'https://lh3.googleusercontent.com/aida-public/AB6AXuAsCjQCXCex4MDfLFYKlYNXTU_NllCagKmEAyjiyIe-sro455B5DuZiX8D_ct2_CasnyBAO30IfAlV9iRsAFBJz1UUOKQBmFOZN7WGXOachIhukQYW1KCDOaTgXoNCMOxu0tDeeWWUtVuOk_gFageiEx1RMvBtyMRfHMklLwr5Nhpx7CDZJHMP2Tepiu1hFCMFTV9KncT1F-Dr7QH39hjkFtDU53PXiDahvCSB-VEtLmDg5n9oTuAT7VljU5JhAucQvAjCTeG1WuqrW',
                            'https://lh3.googleusercontent.com/aida-public/AB6AXuC2z8foSjnNo_eAXxOD3ae_h784gVyw2VZZiBDG6iQKaIrkw_rCntnLKcKalO5M2ZEvmTufb0QrvOVE1BKnqwH2dcV59HAe-RacbZXHfVV4NrykZ5BPUEvclFkIxo7avx_xbGa2lX8sruq9unvmdYxs9R9TZjDXBqsd-54HN8J3K-pP2ExxoTVF8KIL6GrCRnrTD56HebQMerpFio49kUxOh0Ql3PI21xPc68pFO3aZf6ieFqtI33jrNz68-Qdb_t2L8V0-OBH3SMRV',
                            'https://lh3.googleusercontent.com/aida-public/AB6AXuCwPF_Zb04AfnutWijRZEd3cNrpIj9VIGY7mlqOq2pcW6hKHnzR4CnMUng_gyHN7t_KwT0hSDKZfyx_Nec9Csqu5q3M3YX3qaeS2c0YLofln1iKpLjcWjXl-pTqXQ7AU_nslgRarrqYLp-M2fpZxPJL50oErHfv-bGZfn11miBOjg139QQbNwmz5X6Rb00DS6yi5glNCGEmPjvj0lrq6XMePj38nD7A21uMK7qYtvOo7N236upRwD1CleTg5YA2Qo_6Bvy1EaQNjyBL'
                        ];
                        
                        return $defaults[$defaultIndex % count($defaults)];
                    };
                @endphp

                <!-- Featured Article Hero -->
                @if($featured)
                <section class="mb-16">
                    <a href="{{ route('aspirasi.detail', ['slug' => $featured->slug]) }}"
                       wire:navigate
                       class="block grid grid-cols-1 md:grid-cols-2 gap-0 md:gap-8 rounded-2xl overflow-hidden bg-[#FFF9F5] border border-[#E9E9E8] group cursor-pointer hover:shadow-md transition-shadow">
                        <div class="h-64 md:h-auto overflow-hidden">
                            <img alt="Featured Article" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="{{ $getImage($featured, 0) }}">
                        </div>
                        <div class="p-8 md:p-12 flex flex-col justify-center">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="text-xs font-bold text-[#df1c24] bg-[#df1c24]/10 px-3 py-1 rounded-full">Esai Utama</span>
                                <span class="text-xs font-bold text-[#979A9B]">{{ $featured->created_at->format('d M Y') }}</span>
                            </div>
                            <h2 class="text-2xl md:text-4xl font-semibold text-[#020611] mb-4 group-hover:text-[#df1c24] transition-colors leading-tight">
                                {{ $featured->title }}
                            </h2>
                            <p class="text-[16px] leading-[1.55] text-[#37352F] mb-6 line-clamp-3">
                                {{ $featured->excerpt ?: substr(strip_tags($featured->body), 0, 150) }}
                            </p>
                            <div class="flex items-center gap-3 mt-auto">
                                <div class="w-10 h-10 rounded-full bg-[#dfd7e3] overflow-hidden flex items-center justify-center font-bold text-[#df1c24]">
                                    @if($featured->user && $featured->user->avatar)
                                        <img src="{{ Storage::url($featured->user->avatar) }}" alt="{{ $featured->user->name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ substr($featured->user->name ?? 'A', 0, 1) }}
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-[#020611]">{{ $featured->user->name ?? 'Anggota KHI' }}</p>
                                    <p class="text-xs text-[#979A9B] font-semibold">Kontributor KHI</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </section>
                @endif

                <!-- Bento Grid Section -->
                <section class="mb-16">
                    <div class="flex items-center justify-between mb-8 pb-4 border-b border-[#E9E9E8]">
                        <h3 class="text-2xl font-semibold text-[#020611]">Tulisan Terbaru</h3>
                    </div>
                    
                    @if($gridItems->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            @foreach($gridItems as $index => $item)
                                @php
                                    $categorySlug = $item->category ? $item->category->slug : 'lainnya';
                                    $categoryName = $item->category ? $item->category->name : 'Lainnya';
                                    $stylePattern = $index % 4;
                                @endphp

                                @if($stylePattern === 0)
                                    <!-- Style 1: Mint card with image -->
                                    <a href="{{ route('aspirasi.detail', ['slug' => $item->slug]) }}" wire:navigate class="block">
                                    <article class="bg-white border border-[#E9E9E8] rounded-2xl overflow-hidden hover:shadow-md transition-shadow group flex flex-col cursor-pointer">
                                        <div class="h-48 overflow-hidden bg-[#EBF9F4]">
                                            <img alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="{{ $getImage($item, $index + 1) }}">
                                        </div>
                                        <div class="p-6 flex-grow flex flex-col">
                                            <div class="flex items-center gap-2 mb-3">
                                                <span class="text-[11px] font-bold text-[#107F5B] bg-[#EBF9F4] px-2.5 py-0.5 rounded uppercase tracking-wider">{{ $categoryName }}</span>
                                                <span class="text-xs text-[#979A9B] font-semibold">{{ $item->created_at->format('d M Y') }}</span>
                                            </div>
                                            <h4 class="text-lg font-semibold text-[#020611] mb-2 group-hover:text-[#df1c24] transition-colors leading-snug">{{ $item->title }}</h4>
                                            <p class="text-sm text-[#37352F] mb-6 line-clamp-3 leading-relaxed">{{ $item->excerpt ?: substr(strip_tags($item->body), 0, 120) }}</p>
                                            <div class="mt-auto pt-4 border-t border-[#E9E9E8] flex items-center justify-between">
                                                <span class="text-xs font-semibold text-[#575e75]">{{ $item->user->name ?? 'Anggota KHI' }}</span>
                                                <span class="material-symbols-outlined text-[#df1c24] group-hover:translate-x-1 transition-transform text-lg" data-icon="arrow_forward">arrow_forward</span>
                                            </div>
                                        </div>
                                    </article>
                                    </a>

                                @elseif($stylePattern === 1)
                                    <!-- Style 2: Lavender text-only card -->
                                    <a href="{{ route('aspirasi.detail', ['slug' => $item->slug]) }}" wire:navigate class="block">
                                    <article class="bg-[#fff5f5] border border-[#E9E9E8] rounded-2xl overflow-hidden hover:shadow-md transition-shadow group flex flex-col cursor-pointer p-8">
                                        <div class="flex-grow flex flex-col justify-center">
                                            <div class="flex items-center gap-2 mb-4">
                                                <span class="text-[11px] font-bold text-[#df1c24] bg-[#df1c24]/10 px-2.5 py-0.5 rounded uppercase tracking-wider">Kolom</span>
                                                <span class="text-xs text-[#979A9B] font-semibold">{{ $item->created_at->format('d M Y') }}</span>
                                            </div>
                                            <h4 class="text-xl font-semibold text-[#020611] mb-4 group-hover:text-[#df1c24] transition-colors leading-snug">{{ $item->title }}</h4>
                                            <p class="text-sm text-[#37352F] mb-6 line-clamp-4 leading-relaxed">{{ $item->excerpt ?: substr(strip_tags($item->body), 0, 140) }}</p>
                                            <div class="mt-auto pt-4 flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-[#dfd7e3] overflow-hidden flex items-center justify-center font-bold text-[#df1c24] text-xs">
                                                    @if($item->user && $item->user->avatar)
                                                        <img src="{{ Storage::url($item->user->avatar) }}" alt="{{ $item->user->name }}" class="w-full h-full object-cover">
                                                    @else
                                                        {{ substr($item->user->name ?? 'A', 0, 1) }}
                                                    @endif
                                                </div>
                                                <span class="text-xs font-semibold text-[#020611]">{{ $item->user->name ?? 'Anggota KHI' }}</span>
                                            </div>
                                        </div>
                                    </article>
                                    </a>

                                @elseif($stylePattern === 2)
                                    <!-- Style 3: Rose card with image -->
                                    <a href="{{ route('aspirasi.detail', ['slug' => $item->slug]) }}" wire:navigate class="block">
                                    <article class="bg-white border border-[#E9E9E8] rounded-2xl overflow-hidden hover:shadow-md transition-shadow group flex flex-col cursor-pointer">
                                        <div class="h-48 overflow-hidden bg-[#FFECF0]">
                                            <img alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="{{ $getImage($item, $index + 1) }}">
                                        </div>
                                        <div class="p-6 flex-grow flex flex-col">
                                            <div class="flex items-center gap-2 mb-3">
                                                <span class="text-[11px] font-bold text-[#ba1a1a] bg-[#FFECF0] px-2.5 py-0.5 rounded uppercase tracking-wider">{{ $categoryName }}</span>
                                                <span class="text-xs text-[#979A9B] font-semibold">{{ $item->created_at->format('d M Y') }}</span>
                                            </div>
                                            <h4 class="text-lg font-semibold text-[#020611] mb-2 group-hover:text-[#df1c24] transition-colors leading-snug">{{ $item->title }}</h4>
                                            <p class="text-sm text-[#37352F] mb-6 line-clamp-3 leading-relaxed">{{ $item->excerpt ?: substr(strip_tags($item->body), 0, 120) }}</p>
                                            <div class="mt-auto pt-4 border-t border-[#E9E9E8] flex items-center justify-between">
                                                <span class="text-xs font-semibold text-[#575e75]">{{ $item->user->name ?? 'Anggota KHI' }}</span>
                                                <span class="material-symbols-outlined text-[#df1c24] group-hover:translate-x-1 transition-transform text-lg" data-icon="arrow_forward">arrow_forward</span>
                                            </div>
                                        </div>
                                    </article>
                                    </a>

                                @elseif($stylePattern === 3)
                                    <!-- Style 4: col-span-2 Horizontal card -->
                                    <a href="{{ route('aspirasi.detail', ['slug' => $item->slug]) }}" wire:navigate class="block md:col-span-2">
                                    <article class="bg-[#EBF5FF] border border-[#E9E9E8] rounded-2xl overflow-hidden hover:shadow-md transition-shadow group flex flex-col md:flex-row cursor-pointer">
                                        <div class="p-8 md:w-2/3 flex flex-col justify-center">
                                            <div class="flex items-center gap-2 mb-4">
                                                <span class="text-[11px] font-bold text-[#006ADC] border border-[#006ADC] px-2.5 py-0.5 rounded uppercase tracking-wider">{{ $categoryName }}</span>
                                                <span class="text-xs text-[#979A9B] font-semibold">{{ $item->created_at->format('d M Y') }}</span>
                                            </div>
                                            <h4 class="text-xl font-semibold text-[#020611] mb-2 group-hover:text-[#df1c24] transition-colors leading-snug">{{ $item->title }}</h4>
                                            <p class="text-sm text-[#37352F] mb-6 line-clamp-3 leading-relaxed">{{ $item->excerpt ?: substr(strip_tags($item->body), 0, 150) }}</p>
                                            <div class="mt-auto pt-4 flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-[#dfd7e3] overflow-hidden flex items-center justify-center font-bold text-[#df1c24] text-xs">
                                                    @if($item->user && $item->user->avatar)
                                                        <img src="{{ Storage::url($item->user->avatar) }}" alt="{{ $item->user->name }}" class="w-full h-full object-cover">
                                                    @else
                                                        {{ substr($item->user->name ?? 'A', 0, 1) }}
                                                    @endif
                                                </div>
                                                <span class="text-xs font-semibold text-[#020611]">{{ $item->user->name ?? 'Anggota KHI' }}</span>
                                            </div>
                                        </div>
                                        <div class="h-48 md:h-auto md:w-1/3 overflow-hidden bg-[#e7e0eb]">
                                            <img alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="{{ $getImage($item, $index + 1) }}">
                                        </div>
                                    </article>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <!-- Empty State inside bento grid -->
                        <div class="text-center py-20 bg-white border border-dashed border-[#E9E9E8] rounded-2xl p-8 flex flex-col items-center justify-center">
                            <span class="material-symbols-outlined text-5xl text-[#979A9B] mb-3 block">campaign</span>
                            <h3 class="text-lg font-semibold text-[#37352F]">Belum Ada Artikel Aspirasi</h3>
                            <p class="text-sm text-[#37352F]/70 mt-1 max-w-md">Belum ada aspirasi terbit pada kategori ini atau filter terpilih. Jadilah anggota pertama yang membagikan aspirasimu.</p>
                        </div>
                    @endif

                    <!-- Pagination -->
                    @if($aspirations->hasPages())
                    <div class="mt-12 flex justify-center">
                        {{ $aspirations->links('theme::partials.pagination') }}
                    </div>
                    @endif
                </section>
            </div>
            @endvolt
        </x-container>

        <!-- Create New Aspiration Modal Overlay -->
        <div x-show="createOpen" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity duration-300"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;">
            
            <div class="bg-white border border-[#E9E9E8] rounded-3xl p-6 md:p-8 max-w-lg w-full shadow-2xl space-y-6"
                 @click.away="createOpen = false"
                 x-transition:enter="ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <div class="flex justify-between items-start border-b border-[#E9E9E8] pb-4">
                    <div>
                        <h2 class="text-xl font-bold text-[#37352F] tracking-tight">Tulis Aspirasi Baru</h2>
                        <p class="text-xs text-[#37352F]/70 mt-1">Sampaikan gagasan atau usulan cagar budaya Anda ke publik.</p>
                    </div>
                    <button @click="createOpen = false" class="text-[#979A9B] hover:text-[#37352F]">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form wire:submit.prevent="submitAspiration" class="space-y-4">
                    <div class="space-y-1.5">
                        <label for="modal-title" class="text-xs font-semibold text-[#37352F]">Judul Usulan / Topik</label>
                        <input type="text" id="modal-title" wire:model="title" placeholder="Contoh: Menjaga Kelestarian Situs Cagar Budaya Majapahit"
                            class="w-full bg-[#fffafb] border border-[#E9E9E8] rounded-xl px-3 py-2.5 text-sm text-[#37352F] placeholder-[#979A9B] focus:ring-1 focus:ring-primary focus:border-primary focus:outline-none transition-all">
                        @error('title') <span class="text-xs text-red-500 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="modal-categorySlug" class="text-xs font-semibold text-[#37352F]">Kategori</label>
                        <select id="modal-categorySlug" wire:model="categorySlug"
                            class="w-full bg-[#fffafb] border border-[#E9E9E8] rounded-xl px-3 py-2.5 text-sm text-[#37352F] focus:ring-1 focus:ring-primary focus:border-primary focus:outline-none transition-all">
                            <option value="cagar-budaya">Pelestarian Cagar Budaya</option>
                            <option value="edukasi">Edukasi & Kurikulum Sejarah</option>
                            <option value="komunitas">Gagasan Kegiatan Komunitas</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                        @error('categorySlug') <span class="text-xs text-red-500 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="modal-body" class="text-xs font-semibold text-[#37352F]">Detail / Isi Artikel</label>
                        <textarea id="modal-body" wire:model="body" rows="6" placeholder="Tuliskan gagasan, fakta, narasi sejarah, atau usulan Anda di sini..."
                            class="w-full bg-[#fffafb] border border-[#E9E9E8] rounded-xl px-3 py-2.5 text-sm text-[#37352F] placeholder-[#979A9B] focus:ring-1 focus:ring-primary focus:border-primary focus:outline-none transition-all"></textarea>
                        @error('body') <span class="text-xs text-red-500 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit"
                        class="w-full bg-[#df1c24] hover:bg-[#c41219] text-white text-center text-sm font-semibold py-3.5 rounded-xl transition duration-200 flex items-center justify-center gap-1.5 shadow-sm mt-6">
                        <span class="material-symbols-outlined text-[18px]">publish</span>
                        Terbitkan Sekarang
                    </button>
                </form>
            </div>
        </div>

        <!-- Guest Authorization Modal Overlay -->
        <div x-show="authOpen" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity duration-300"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;">
            
            <div class="bg-white border border-[#E9E9E8] rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-6"
                 @click.away="authOpen = false"
                 x-transition:enter="ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <div class="flex justify-between items-start border-b border-[#E9E9E8] pb-4">
                    <div class="flex items-center gap-2 text-[#E06D3B]">
                        <span class="material-symbols-outlined text-2xl">edit_document</span>
                        <h2 class="text-xl font-bold text-[#37352F] tracking-tight">Ingin Menulis Aspirasi?</h2>
                    </div>
                    <button @click="authOpen = false" class="text-[#979A9B] hover:text-[#37352F] transition p-1 rounded-full hover:bg-zinc-50 flex items-center justify-center">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <p class="text-sm text-[#37352F]/70 leading-relaxed">
                    Halaman aspirasi ini merupakan platform editorial eksklusif bagi anggota resmi Komunitas Historia Indonesia untuk mempublikasikan artikel, usulan cagar budaya, atau opini sejarah.
                </p>

                <div class="pt-4 space-y-3">
                    <a href="{{ route('join') }}" wire:navigate class="w-full inline-flex items-center justify-center bg-[#df1c24] hover:bg-[#c41219] text-white font-semibold py-3.5 px-4 rounded-xl transition duration-200 text-sm shadow-sm hover:shadow">
                        Daftar Anggota KHI
                    </a>
                    <a href="{{ route('login') }}" wire:navigate class="w-full inline-flex items-center justify-center bg-white border border-[#E9E9E8] hover:bg-zinc-50 text-[#37352F] font-semibold py-3.5 px-4 rounded-xl transition duration-200 text-sm">
                        Masuk Ke Akun Anda
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.marketing>
