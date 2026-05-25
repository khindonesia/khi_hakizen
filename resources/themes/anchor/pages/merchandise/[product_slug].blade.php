<?php
    use function Laravel\Folio\name;
    name('merchandise.detail');
?>

@php
    $slug = $productSlug ?? $product_slug ?? null;
    $product = null;

    if ($slug) {
        $product = \App\Models\Product::query()
            ->with(['category', 'variants.variantAttributes.attribute', 'variants.variantAttributes.attributeValue', 'images', 'productAttributes.attribute'])
            ->where('slug', $slug)
            ->first();

        if (! app()->runningInConsole()) {
            abort_unless($product, 404);
        }
    }

    $fallbackImageUrl = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=800&auto=format&fit=crop';

    $resolveImageUrl = static function (?string $path) use ($fallbackImageUrl): string {
        if (! $path) {
            return $fallbackImageUrl;
        }

        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return \Illuminate\Support\Facades\Storage::url(ltrim($path, '/'));
    };

    $galleryItems = $product
        ? $product->images
            ->sortBy('sort_order')
            ->map(fn ($image) => $resolveImageUrl($image->image_url))
            ->values()
        : collect();

    $mainImageUrl = $galleryItems->first() ?? $fallbackImageUrl;

    $selectedVariant = $product
        ? ($product->defaultVariant
            ?? $product->variants->firstWhere('status', 'active')
            ?? $product->variants->first())
        : null;

    $selectedVariantPrice = $selectedVariant?->price ?? 0;
    $selectedVariantStock = $selectedVariant?->stock_quantity ?? 0;
    $selectedVariantStatus = $selectedVariant && $selectedVariant->isInStock()
        ? 'In stock'
        : 'Out of stock';
    $variantPayload = $product
        ? $product->variants->map(fn ($variant) => [
            'id' => $variant->id,
            'name' => $variant->name,
            'price' => $variant->price,
            'stock_quantity' => $variant->stock_quantity,
        ])->values()
        : collect();
@endphp

<x-layouts.marketing :seo="[
    'title' => ($product?->name ?? 'Merchandise') . ' – KHI Merchandise',
    'description' => Str::limit(strip_tags($product?->description ?? ''), 155),
    'image' => $mainImageUrl,
    'type' => 'product',
]">
    @push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
    @endpush
    @if($product)
    <div class="relative min-h-screen py-12 md:py-16">
        <x-container>
            <!-- Back Link -->
            <a href="{{ route('merchandise') }}" wire:navigate class="mb-8 inline-flex items-center gap-2 text-sm font-medium text-zinc-500 hover:text-red-700 transition-colors">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Back to Merchandise
            </a>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                <!-- Left Column: Image Gallery -->
                <div class="lg:col-span-6">
                    <div class="relative overflow-hidden rounded-[28px] border border-zinc-200/80 bg-white shadow-sm">
                        <img src="{{ $galleryItems->first() ?? $fallbackImageUrl }}" alt="{{ $product->name }}" class="w-full h-auto object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                    </div>
                    <!-- Thumbnail carousel (if more images) -->
                    @if($galleryItems->count() > 1)
                        <div class="mt-4 grid grid-cols-4 gap-2">
                            @foreach($galleryItems->skip(1) as $thumb)
                            <button type="button" class="relative overflow-hidden rounded-2xl border border-zinc-200/80 transition-all hover:border-red-300" onclick="this.parentElement.parentElement.querySelector('img').src='{{ $thumb }}'">
                                <img src="{{ $thumb }}" alt="Thumbnail" class="w-full h-20 object-cover">
                            </button>
                        @endforeach
                        </div>
                    @endif
                </div>

                <!-- Right Column: Product Info & Purchase -->
                <aside class="lg:col-span-6 lg:sticky lg:top-[100px]">
                    <div class="stitch-panel flex flex-col gap-6 p-6 md:p-8">
                        <!-- Category Tag -->
                        @if($product->category)
                            <div class="stitch-chip inline-flex w-fit">
                                {{ $product->category->name }}
                            </div>
                        @endif

                        <h1 class="text-2xl font-semibold leading-tight text-zinc-900 md:text-[36px]">
                            {{ $product->name }}
                        </h1>

                        <!-- Price & Stock -->
                        <div class="flex items-baseline gap-4">
                            <span class="text-2xl font-semibold text-red-700">Rp <span x-text="priceLabel">{{ number_format($selectedVariantPrice, 0, ',', '.') }}</span></span>
                            <span class="text-sm font-medium text-zinc-500" x-text="stockLabel">
                                {{ $selectedVariantStatus }}
                            </span>
                        </div>

                        <!-- Variant selector (if any) -->
                        @if($product->variants->count() > 0)
                            <div class="space-y-3">
                                <label class="block text-sm font-medium text-zinc-600">Choose Variant</label>
                                <select x-model.number="selectedVariantId" class="w-full rounded-2xl border border-zinc-200 bg-white py-2.5 px-3.5 focus:border-red-400 focus:outline-none focus:ring-4 focus:ring-red-100">
                                    @foreach($product->variants as $variant)
                                        <option value="{{ $variant->id }}">
                                            {{ $variant->name }} – Rp {{ number_format($variant->price, 0, ',', '.') }} ({{ $variant->stock_quantity > 0 ? $variant->stock_quantity.' in stock' : 'Out of stock' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Attribute groups (e.g., size, color) -->
                        @if($product->productAttributes->count() > 0)
                            <div class="space-y-4">
                                @foreach($product->productAttributes->groupBy('attribute_id') as $group)
                                    @php $attr = $group->first()->attribute; @endphp
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-zinc-600">{{ $attr->name }}</label>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($group as $value)
                                                <button type="button"
                                                        class="rounded-full border px-3 py-1.5 text-xs transition-colors {{ $value->attributeValue->value == $value->selected ? 'border-red-600 bg-red-600 text-white' : 'border-zinc-200 bg-white text-zinc-600 hover:border-red-300 hover:text-red-700' }}">
                                                    {{ $value->attributeValue->value }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Add to Cart CTA -->
                        <div
                            class="flex flex-col gap-3 mt-4"
                            x-data="{
                                addUrl: @js(route('shopping-cart.add')),
                                csrfToken: @js(csrf_token()),
                                variants: @js($variantPayload->all()),
                                selectedVariantId: @js($selectedVariant?->id),
                                quantity: 1,
                                loading: false,
                                message: '',
                                messageType: 'success',
                                get selectedVariant() {
                                    return this.variants.find((variant) => variant.id === this.selectedVariantId) ?? null;
                                },
                                get priceLabel() {
                                    return new Intl.NumberFormat('id-ID').format(this.selectedVariant?.price ?? @js($selectedVariantPrice));
                                },
                                get stockLabel() {
                                    return this.selectedVariant
                                        ? (this.selectedVariant.stock_quantity > 0 ? 'In stock' : 'Out of stock')
                                        : @js($selectedVariantStatus);
                                },
                                get stockHint() {
                                    return this.selectedVariant
                                        ? (this.selectedVariant.stock_quantity > 0 ? `${this.selectedVariant.stock_quantity} available` : 'Out of stock')
                                        : @js($selectedVariantStock > 0 ? $selectedVariantStock . ' available' : 'Out of stock');
                                },
                                get maxQuantity() {
                                    return this.selectedVariant?.stock_quantity ?? @js(max(1, (int) $selectedVariantStock));
                                },
                                decreaseQuantity() {
                                    if (this.quantity > 1) {
                                        this.quantity -= 1;
                                    }
                                },
                                increaseQuantity() {
                                    if (this.quantity < this.maxQuantity) {
                                        this.quantity += 1;
                                    }
                                },
                                async addToCart() {
                                    if (!this.selectedVariantId) {
                                        this.messageType = 'danger';
                                        this.message = 'Select variant first.';
                                        return;
                                    }

                                    this.loading = true;
                                    this.message = '';

                                    try {
                                        const response = await fetch(this.addUrl, {
                                            method: 'POST',
                                            headers: {
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': this.csrfToken,
                                            },
                                            body: new URLSearchParams({
                                                variant_id: this.selectedVariantId,
                                                quantity: this.quantity,
                                            }),
                                        });

                                        const payload = await response.json();

                                        if (!response.ok || !payload.success) {
                                            throw new Error(payload.message ?? 'Failed to add item to cart.');
                                        }

                                        this.messageType = 'success';
                                        this.message = payload.message ?? 'Item added to cart successfully.';
                                        window.dispatchEvent(new CustomEvent('cart-updated'));
                                    } catch (error) {
                                        this.messageType = 'danger';
                                        this.message = error.message ?? 'Failed to add item to cart.';
                                    } finally {
                                        this.loading = false;
                                    }
                                },
                            }"
                        >
                        @auth
                            <div class="flex flex-col gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center overflow-hidden rounded-full border border-zinc-200 bg-white shadow-sm">
                                        <button
                                            type="button"
                                            x-on:click="decreaseQuantity()"
                                            class="flex items-center justify-center px-3 py-2 text-zinc-700 transition-colors hover:bg-red-50 hover:text-red-700"
                                            :disabled="loading"
                                        >
                                            <span class="material-symbols-outlined text-[18px]">remove</span>
                                        </button>
                                        <input
                                            type="number"
                                            min="1"
                                            :max="maxQuantity"
                                            inputmode="numeric"
                                            x-model.number="quantity"
                                            class="w-14 border-0 bg-transparent p-0 text-center text-sm font-semibold text-zinc-900 focus:outline-none focus:ring-0"
                                        >
                                        <button
                                            type="button"
                                            x-on:click="increaseQuantity()"
                                            class="flex items-center justify-center px-3 py-2 text-zinc-700 transition-colors hover:bg-red-50 hover:text-red-700"
                                            :disabled="loading"
                                        >
                                            <span class="material-symbols-outlined text-[18px]">add</span>
                                        </button>
                                    </div>

                                    <p class="text-xs font-medium text-zinc-500" x-text="stockHint">
                                        {{ $selectedVariantStock > 0 ? $selectedVariantStock.' available' : 'Out of stock' }}
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    x-on:click="addToCart()"
                                    class="w-full rounded-full bg-red-600 py-3 font-semibold text-white transition-all hover:bg-red-500 disabled:cursor-not-allowed disabled:opacity-60"
                                    :disabled="loading || !selectedVariantId"
                                >
                                    Add to Cart
                                </button>

                                <p class="min-h-5 text-sm font-medium"
                                   x-text="message"
                                   :class="messageType === 'success' ? 'text-emerald-600' : 'text-rose-600'"></p>
                            </div>
                            @elseguest
                                <a href="{{ route('login') }}" class="w-full rounded-full bg-red-600 py-3 text-center font-semibold text-white transition-all hover:bg-red-500">
                                    Sign in to Purchase
                                </a>
                            @endauth
                        </div>

                        <!-- Product Description -->
                        <div class="border-t border-zinc-200/70 pt-4">
                            <h2 class="mb-2 text-lg font-semibold text-zinc-900">Product Description</h2>
                            <div class="prose prose-sm max-w-none text-zinc-500">
                                {!! $product->description !!}
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </x-container>
    </div>
    @else
    <div class="flex min-h-screen items-center justify-center py-12 md:py-16">
        <div class="text-center">
            <span class="material-symbols-outlined mb-4 text-6xl text-zinc-400">inventory_2</span>
            <p class="text-lg font-medium text-zinc-600">Product not found.</p>
            <a href="{{ route('merchandise') }}" wire:navigate class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-red-700 hover:underline">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Back to Merchandise
            </a>
        </div>
    </div>
    @endif
</x-layouts.marketing>
