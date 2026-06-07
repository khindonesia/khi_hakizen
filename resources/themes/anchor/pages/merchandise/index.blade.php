<?php
use function Laravel\Folio\{name};
name("merchandise");
?>

<x-layouts.marketing :seo="[
    'title' => 'Komunitas Historia Indonesia - Merchandise',
    'description' => 'Official merchandise from Komunitas Historia Indonesia. Support historical preservation with every purchase.',
]">


    @php
        $selectedCategory = request()->integer("category");
        $selectedType = request("type");
        $emptyPaginator = function (int $perPage = 9) {
            return new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                $perPage,
                \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage(),
                [
                    "path" => request()->url(),
                    "query" => request()->query(),
                ],
            );
        };

        try {
            $categories = \App\Models\ProductCategory::query()
                ->where("status", "active")
                ->withCount([
                    "products" => fn($query) => $query->where("status", "active"),
                ])
                ->orderBy("name")
                ->get();

            $types = \App\Models\Type::all();

            $productQuery = \App\Models\Product::query()
                ->where("status", "active")
                ->with(["category", "defaultVariant", "images"])
                ->withCount([
                    "variants as active_variants_count" => fn(
                        $query,
                    ) => $query->active(),
                ]);

            if ($selectedCategory) {
                $productQuery->whereHas(
                    "category",
                    fn($query) => $query->whereKey($selectedCategory),
                );
            }

            if ($selectedType) {
                if ($selectedType === 'terbaru') {
                    $productQuery->latest();
                } elseif ($selectedType === 'terlama') {
                    $productQuery->oldest();
                } else {
                    $productQuery->whereHas(
                        "types",
                        fn($query) => $query->where("slug", $selectedType),
                    );
                    $productQuery->latest();
                }
            } else {
                $productQuery->latest();
            }

            $products = $productQuery->paginate(9)->withQueryString();

        } catch (\Throwable $e) {
            $categories = collect();
            $types = collect();
            $products = $emptyPaginator();
        }
    @endphp

    <!-- Page Content -->
    <main class="relative mx-auto max-w-[1280px] px-4 pb-24 pt-24 sm:px-6">
        <!-- Header Section -->
        <header class="mb-16 border-b border-zinc-200/80 pb-12">
            <div class="stitch-chip mb-4">{{ setting('merchandise_chip', 'Merchandise') }}</div>
            <h1 class="mb-3 text-4xl font-semibold leading-[1.1] tracking-tight text-zinc-900 md:text-[56px]">{{ setting('merchandise_title', 'KHI Store') }}</h1>
            <p class="max-w-2xl text-lg leading-[1.55] text-zinc-500">{{ setting('merchandise_subtitle', 'Support historical preservation. Every purchase directly funds our educational programs and archive maintenance.') }}</p>
        </header>

        <!-- Category Filters -->
        <div class="mb-4 flex flex-wrap gap-2 items-center">
            <span class="text-sm font-semibold text-zinc-500 mr-2">Category:</span>
            <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" wire:navigate 
               class="rounded-full border px-4 py-2 text-sm font-medium transition duration-200 {{ !$selectedCategory ? 'border-red-600 bg-red-600 text-white shadow-sm' : 'border-zinc-200 bg-white text-zinc-700 hover:border-red-200 hover:text-red-700' }}">
                All Items
            </a>
            @foreach ($categories as $category)
                <a href="{{ request()->fullUrlWithQuery(['category' => $category->id]) }}" wire:navigate 
                   class="rounded-full border px-4 py-2 text-sm font-medium transition duration-200 {{ $selectedCategory === $category->id ? 'border-red-600 bg-red-600 text-white shadow-sm' : 'border-zinc-200 bg-white text-zinc-700 hover:border-red-200 hover:text-red-700' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        <!-- Type Filters -->
        <div class="mb-8 flex flex-wrap gap-2 items-center">
            <span class="text-sm font-semibold text-zinc-500 mr-2">Tipe:</span>
            <a href="{{ request()->fullUrlWithQuery(['type' => null]) }}" wire:navigate 
               class="rounded-full border px-4 py-1.5 text-xs font-medium transition duration-200 {{ !$selectedType ? 'border-red-600 bg-red-600 text-white shadow-sm' : 'border-zinc-200 bg-white text-zinc-700 hover:border-red-200 hover:text-red-700' }}">
                Semua Tipe
            </a>
            @foreach ($types as $type)
                <a href="{{ request()->fullUrlWithQuery(['type' => $type->slug]) }}" wire:navigate 
                   class="rounded-full border px-4 py-1.5 text-xs font-medium transition duration-200 {{ $selectedType === $type->slug ? 'border-red-600 bg-red-600 text-white shadow-sm' : 'border-zinc-200 bg-white text-zinc-700 hover:border-red-200 hover:text-red-700' }}">
                    {{ $type->name }}
                </a>
            @endforeach
        </div>

        @if ($products->count())
            <!-- Product Grid -->
            <div class="mb-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <x-merchandise.card :product="$product" :is-first="$loop->first" />
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-12 flex justify-center">
                {{ $products->links('theme::partials.pagination') }}
            </div>
        @else
            <!-- Empty State -->
            <div class="stitch-panel py-20 text-center">
                <span class="material-symbols-outlined text-6xl text-[#979A9B] mb-4">storefront</span>
                <h3 class="mb-1 text-xl font-semibold text-zinc-900">No merchandise matches this filter</h3>
                <p class="mb-6 text-sm text-zinc-500">Try a different category, or clear the filter to see the full catalog.</p>
                <a href="{{ route('merchandise') }}" wire:navigate class="inline-flex items-center justify-center rounded-full bg-red-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-red-500">
                    Show all items
                </a>
            </div>
        @endif
    </main>
</x-layouts.marketing>
