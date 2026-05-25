<?php
use function Laravel\Folio\{name};
name("merchandise");
?>

<x-layouts.marketing :seo="[
    'title' => 'Komunitas Historia Indonesia - Merchandise',
    'description' => 'Official merchandise from Komunitas Historia Indonesia. Support historical preservation with every purchase.',
]">
    @push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
    @endpush

    @php
        $selectedCategory = request()->integer("category");
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

            $products = $productQuery->latest()->paginate(9)->withQueryString();

        } catch (\Throwable $e) {
            $categories = collect();
            $products = $emptyPaginator();
        }
    @endphp

    <!-- Page Content -->
    <main class="relative mx-auto max-w-[1280px] px-4 pb-24 pt-24 sm:px-6">
        <!-- Header Section -->
        <header class="mb-16 border-b border-zinc-200/80 pb-12">
            <div class="stitch-chip mb-4">Merchandise</div>
            <h1 class="mb-3 text-4xl font-semibold leading-[1.1] tracking-tight text-zinc-900 md:text-[56px]">KHI Store</h1>
            <p class="max-w-2xl text-lg leading-[1.55] text-zinc-500">Support historical preservation. Every purchase directly funds our educational programs and archive maintenance.</p>
        </header>

        <!-- Category Filters -->
        <div class="mb-8 flex flex-wrap gap-2">
            <a href="{{ route('merchandise') }}" wire:navigate 
               class="rounded-full border px-4 py-2 text-sm font-medium transition duration-200 {{ !$selectedCategory ? 'border-red-600 bg-red-600 text-white shadow-sm' : 'border-zinc-200 bg-white text-zinc-700 hover:border-red-200 hover:text-red-700' }}">
                All Items
            </a>
            @foreach ($categories as $category)
                <a href="{{ url('/merchandise?category=' . $category->id) }}" wire:navigate 
                   class="rounded-full border px-4 py-2 text-sm font-medium transition duration-200 {{ $selectedCategory === $category->id ? 'border-red-600 bg-red-600 text-white shadow-sm' : 'border-zinc-200 bg-white text-zinc-700 hover:border-red-200 hover:text-red-700' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        @if ($products->count())
            <!-- Product Grid -->
            <div class="mb-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <x-merchandise.card :product="$product" />
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
