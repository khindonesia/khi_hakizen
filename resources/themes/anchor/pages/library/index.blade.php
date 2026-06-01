<?php
use App\Models\Ebook;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Laravel\Folio\{name};

name('library');

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        // Auto-seed if empty
        if (Ebook::count() === 0) {
            Ebook::create([
                'title' => 'Dinamika Kebangsaan Dalam Arus Global',
                'slug' => 'dinamika-kebangsaan-dalam-arus-global',
                'cover_image' => 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=400&auto=format&fit=crop',
                'author' => 'Kementerian Kebudayaan',
                'description' => 'Buku ini membedah dinamika sosial dan politik Indonesia di masa peralihan kolonial menuju kemerdekaan. Ditulis dengan pendekatan historis-sosiologis, karya ini menjadi rujukan penting bagi akademisi dan pencinta sejarah perjuangan bangsa.',
                'status' => 'PUBLISHED',
                'ebook_file' => 'dummy.pdf',
            ]);
            Ebook::create([
                'title' => 'Surat-Surat Batavia 1920',
                'slug' => 'surat-surat-batavia-1920',
                'cover_image' => 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?q=80&w=400&auto=format&fit=crop',
                'author' => 'A. Van Der Kemp',
                'description' => 'Dokumentasi otentik surat-menyurat resmi dan pribadi dari residen Batavia pada dekade 1920-an. Buku ini membuka tabir kehidupan sehari-hari, intrik politik, dan kebijakan kolonial Belanda di wilayah Sunda Kelapa.',
                'status' => 'PUBLISHED',
                'ebook_file' => 'dummy.pdf',
            ]);
            Ebook::create([
                'title' => 'Jejak Langkah Pejuang',
                'slug' => 'jejak-langkah-pejuang',
                'cover_image' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?q=80&w=400&auto=format&fit=crop',
                'author' => 'Asep Kambali',
                'description' => 'Sebuah kompilasi kisah inspiratif dari para pejuang kemerdekaan Indonesia yang terlupakan. Diterbitkan secara khusus oleh Komunitas Historia Indonesia untuk menyebarkan semangat nasionalisme kepada generasi muda.',
                'status' => 'PUBLISHED',
                'ebook_file' => 'dummy.pdf',
            ]);
            Ebook::create([
                'title' => 'Kumpulan Jurnal Sejarah 2023',
                'slug' => 'kumpulan-jurnal-sejarah-2023',
                'cover_image' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?q=80&w=400&auto=format&fit=crop',
                'author' => 'Various Authors',
                'description' => 'Antologi riset dan kajian ilmiah mendalam mengenai situs-situs cagar budaya Nusantara yang terbit sepanjang tahun 2023. Merupakan kontribusi akademis penting dari para peneliti senior Komunitas Historia Indonesia.',
                'status' => 'PUBLISHED',
                'ebook_file' => 'dummy.pdf',
            ]);
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setCategory(string $category): void
    {
        $this->categoryFilter = $category;
        $this->resetPage();
    }
}
?>

<x-layouts.marketing :seo="[
    'title' => 'Digital Library & Archive - Komunitas Historia Indonesia',
    'description' => 'Akses arsip digital, naskah kuno, jurnal ilmiah, dan e-book sejarah nusantara yang dihimpun oleh Komunitas Historia Indonesia.',
]">
@volt('library')
    <div class="w-full bg-[#fffafb] min-h-screen font-['Inter'] pb-20">

        <!-- Hero Banner Section -->
        <div class="bg-[#030712] text-white py-20 md:py-28 relative overflow-hidden flex flex-col items-center justify-center text-center">
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px]"></div>
            <x-container class="relative z-10 max-w-4xl px-6">
                <h1 style="color: #e4e4e7;" class="text-4xl md:text-6xl font-bold leading-tight tracking-tight mb-4 font-sans text-zinc-200">
                    Digital Archive
                </h1>
                <p class="text-zinc-400 text-base md:text-lg max-w-2xl mx-auto leading-relaxed mb-8">
                    Explore our extensive collection of digitized historical manuscripts, scholarly publications, and rare archival materials documenting the rich tapestry of Indonesian history.
                </p>

                <!-- Symmetrical Search Input Bar -->
                <div class="max-w-xl mx-auto bg-white rounded-full p-2 flex items-center shadow-lg border border-zinc-800">
                    <span class="material-symbols-outlined text-zinc-400 ml-3">search</span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by era, topic, or author..."
                           class="w-full border-none bg-transparent py-2 px-3 text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-0 text-sm">
                    @if($search)
                        <button type="button" wire:click="$set('search', '')" class="text-zinc-400 hover:text-zinc-600 mr-2">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    @endif
                    <button type="button" class="bg-[#df1c24] hover:bg-[#c41219] text-white font-semibold px-6 py-2 rounded-full text-sm transition">
                        Search
                    </button>
                </div>
            </x-container>
        </div>

        <x-container class="mt-12 px-6">
            <!-- Category Selection Pills -->
            <div class="flex flex-wrap gap-2 border-b border-[#E9E9E8] pb-6 mb-8 justify-start">
                <button type="button" wire:click="setCategory('all')"
                    class="px-5 py-2.5 rounded-full text-xs font-semibold tracking-wide uppercase transition duration-200 {{ $categoryFilter === 'all' ? 'bg-[#df1c24] text-white' : 'bg-white text-zinc-700 border border-[#E9E9E8] hover:bg-zinc-50' }}">
                    All Books
                </button>
                <button type="button" wire:click="setCategory('famous')"
                    class="px-5 py-2.5 rounded-full text-xs font-semibold tracking-wide uppercase transition duration-200 {{ $categoryFilter === 'famous' ? 'bg-[#df1c24] text-white' : 'bg-white text-zinc-700 border border-[#E9E9E8] hover:bg-zinc-50' }}">
                    Famous Works
                </button>
                <button type="button" wire:click="setCategory('khi')"
                    class="px-5 py-2.5 rounded-full text-xs font-semibold tracking-wide uppercase transition duration-200 {{ $categoryFilter === 'khi' ? 'bg-[#df1c24] text-white' : 'bg-white text-zinc-700 border border-[#E9E9E8] hover:bg-zinc-50' }}">
                    KHI Publications
                </button>
                <button type="button" wire:click="setCategory('manuscript')"
                    class="px-5 py-2.5 rounded-full text-xs font-semibold tracking-wide uppercase transition duration-200 {{ $categoryFilter === 'manuscript' ? 'bg-[#df1c24] text-white' : 'bg-white text-zinc-700 border border-[#E9E9E8] hover:bg-zinc-50' }}">
                    Rare Manuscripts
                </button>
            </div>

            <!-- E-Books Grid -->
            @php
                $query = Ebook::published();

                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', '%' . $search . '%')
                          ->orWhere('author', 'like', '%' . $search . '%')
                          ->orWhere('description', 'like', '%' . $search . '%');
                    });
                }

                if ($categoryFilter === 'famous') {
                    $query->whereIn('slug', ['dinamika-kebangsaan-dalam-arus-global', 'surat-surat-batavia-1920']);
                } elseif ($categoryFilter === 'khi') {
                    $query->whereIn('slug', ['jejak-langkah-pejuang', 'kumpulan-jurnal-sejarah-2023']);
                } elseif ($categoryFilter === 'manuscript') {
                    $query->whereIn('slug', ['surat-surat-batavia-1920']);
                }

                $ebooks = $query->paginate(8);
            @endphp

            @if($ebooks->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                    @foreach ($ebooks as $book)
                        @php
                            $coverUrl = $book->cover_image
                                ? (str_starts_with($book->cover_image, 'http') ? $book->cover_image : Storage::url(ltrim($book->cover_image, '/')))
                                : 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=400&auto=format&fit=crop';

                            $eraMap = [
                                'dinamika-kebangsaan-dalam-arus-global' => 'COLONIAL ERA',
                                'surat-surat-batavia-1920' => 'MANUSCRIPT',
                                'jejak-langkah-pejuang' => 'KHI PUBLICATION',
                                'kumpulan-jurnal-sejarah-2023' => 'ANTHOLOGY',
                            ];
                            $eraLabel = $eraMap[$book->slug] ?? 'HISTORICAL ARCHIVE';
                        @endphp

                        <div class="bg-white border border-[#E9E9E8] rounded-2xl overflow-hidden shadow-xs hover:shadow-[0_12px_36px_rgba(0,0,0,0.04)] transition-all duration-300 flex flex-col justify-between group h-full">
                            <div>
                                <!-- Image Top Container -->
                                <div class="bg-[#0f172a] aspect-square flex items-center justify-center p-8 relative overflow-hidden shrink-0">
                                    <div class="absolute inset-0 bg-radial-gradient from-transparent to-black/20"></div>
                                    <div class="relative shadow-[0_12px_28px_rgba(0,0,0,0.3)] rounded-lg overflow-hidden max-h-[160px] aspect-[3/4] z-10 transition-transform duration-500 group-hover:scale-105 group-hover:-translate-y-1">
                                        <img src="{{ $coverUrl }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                                    </div>

                                    <!-- Member Restricted Tag -->
                                    <div class="absolute top-3 right-3 bg-white/95 text-zinc-900 border border-zinc-200 px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider flex items-center gap-1 shadow-sm backdrop-blur-sm z-20">
                                        <span class="material-symbols-outlined text-[10px]" style="font-variation-settings: 'FILL' 1;">lock</span>
                                        <span>Member</span>
                                    </div>
                                </div>

                                <!-- Book metadata/info container -->
                                <div class="p-6 space-y-2.5">
                                    <span class="text-[10px] font-bold text-[#979A9B] uppercase tracking-wider block">
                                        {{ $eraLabel }}
                                    </span>
                                    <h3 class="text-base font-bold text-[#37352F] tracking-tight leading-snug group-hover:text-[#df1c24] transition-colors duration-200 line-clamp-2">
                                        {{ $book->title }}
                                    </h3>
                                    <p class="text-xs text-[#979A9B] font-semibold">
                                        {{ $book->author }}
                                    </p>
                                </div>
                            </div>

                            <!-- Bottom CTA Area -->
                            <div class="px-6 pb-6 pt-2">
                                <a href="{{ route('library.book', ['slug' => $book->slug]) }}" wire:navigate
                                   class="w-full block border border-[#E9E9E8] rounded-xl text-center py-3 text-xs font-bold text-[#37352F] hover:bg-zinc-50 transition duration-200">
                                    View Details
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Custom Pagination -->
                <div class="flex justify-center mt-12">
                    {{ $ebooks->links('theme::partials.pagination') }}
                </div>
            @else
                <div class="bg-white border border-[#E9E9E8] rounded-2xl max-w-md mx-auto p-12 text-center shadow-xs">
                    <span class="material-symbols-outlined text-5xl text-[#979A9B] mb-3 block">library_books</span>
                    <h3 class="text-lg font-semibold text-[#37352F]">No publications found</h3>
                    <p class="text-sm text-[#37352F]/70 mt-1">We couldn't find any documents matching your search term.</p>
                    <button type="button" wire:click="$set('search', '')" class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-[#df1c24] px-5 py-2.5 text-xs font-semibold text-white transition-all hover:bg-[#c41219]">
                        Clear Search
                    </button>
                </div>
            @endif
        </x-container>

    </div>
    @endvolt
</x-layouts.marketing>
