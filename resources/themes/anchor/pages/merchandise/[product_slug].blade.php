<?php
    use function Laravel\Folio\name;
    name('merchandise.detail');
?>

@php
    $slug = $productSlug ?? $product_slug ?? null;
    $product = null;

    if ($slug) {
        $product = \App\Models\Product::query()
            ->where('status', 'active')
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
                        <img src="{{ $galleryItems->first() ?? $fallbackImageUrl }}"
                             alt="{{ $product->name }}"
                             width="800"
                             height="800"
                             fetchpriority="high"
                             decoding="sync"
                             class="w-full h-auto object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                    </div>
                    <!-- Thumbnail carousel (if more images) -->
                    @if($galleryItems->count() > 1)
                        <div class="mt-4 grid grid-cols-4 gap-2">
                            @foreach($galleryItems->skip(1) as $thumb)
                            <button type="button" class="relative overflow-hidden rounded-2xl border border-zinc-200/80 transition-all hover:border-red-300" onclick="this.parentElement.parentElement.querySelector('img').src='{{ $thumb }}'">
                                <img src="{{ $thumb }}"
                                     alt="Thumbnail"
                                     width="200"
                                     height="200"
                                     loading="lazy"
                                     decoding="async"
                                     class="w-full h-20 object-cover">
                            </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Right Column: Product Info & Purchase -->
                <aside class="lg:col-span-6 lg:sticky lg:top-[100px]">
                    <div class="stitch-panel flex flex-col gap-6 p-6 md:p-8"
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
                        <div class="flex flex-col gap-3 mt-4">
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
                            @else
                                <a href="{{ route('join') }}" class="w-full rounded-full bg-red-600 py-3 text-center font-semibold text-white transition-all hover:bg-red-500">
                                    Join KHI to Purchase
                                </a>
                            @endauth

                            <!-- Share Dropdown -->
                            <div x-data="{ open: false, copied: false }" class="relative w-full">
                                <button type="button" 
                                        @click="open = !open"
                                        class="w-full bg-white border border-zinc-200 text-zinc-700 text-sm font-semibold py-3 rounded-full hover:bg-zinc-50 transition-all flex items-center justify-center gap-1.5 shadow-sm">
                                    <span class="material-symbols-outlined text-[18px]">share</span>
                                    Share with Friends
                                </button>

                                <div x-show="open" 
                                     @click.outside="open = false"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                     class="absolute bottom-full left-0 right-0 z-50 mb-3 p-3 bg-white border border-[#E9E9E8] rounded-xl shadow-xl flex flex-col gap-1.5"
                                     x-cloak>
                                    
                                    <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider px-2.5 pb-1.5 border-b border-zinc-100 text-left">Share Merchandise</p>
                                    
                                    <!-- WhatsApp -->
                                    <a href="https://api.whatsapp.com/send?text={{ rawurlencode($product->name) }}%20{{ rawurlencode(request()->url()) }}" 
                                       target="_blank" 
                                       class="flex items-center gap-2.5 px-2.5 py-2 text-xs font-medium text-zinc-700 hover:bg-emerald-50 hover:text-emerald-700 rounded-lg transition-colors">
                                        <svg class="w-4 h-4 text-emerald-500 fill-current" viewBox="0 0 24 24">
                                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.713-1.455L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.625 1.451 5.48-.002 9.932-4.453 9.935-9.93.001-2.652-1.03-5.144-2.905-7.022C16.427 1.775 13.935.744 11.997.744 6.513.744 2.06 5.192 2.057 10.677c-.002 1.503.398 2.972 1.16 4.29l-.994 3.63 3.731-.978zm11.567-7.25c-.247-.124-1.47-.726-1.698-.81-.228-.084-.393-.124-.558.124-.165.247-.638.81-.782.975-.145.166-.29.185-.537.062-.247-.125-1.045-.385-1.99-1.23-.738-.657-1.235-1.47-1.38-1.717-.146-.247-.015-.38.11-.504.112-.111.247-.29.37-.435.124-.145.165-.247.247-.412.083-.165.042-.31-.02-.435-.063-.124-.558-1.346-.763-1.84-.2-.48-.401-.416-.558-.424-.144-.007-.31-.008-.475-.008-.166 0-.435.062-.663.31-.228.247-.87.85-.87 2.075s.89 2.41 1.012 2.575c.125.166 1.75 2.673 4.24 3.743.59.254 1.053.405 1.41.52.597.19 1.14.163 1.57.1.48-.07 1.47-.6 1.677-1.18.207-.58.207-1.077.145-1.18-.062-.102-.228-.142-.475-.266z"/>
                                        </svg>
                                        <span>WhatsApp</span>
                                    </a>

                                    <!-- Facebook -->
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode(request()->url()) }}" 
                                       target="_blank" 
                                       class="flex items-center gap-2.5 px-2.5 py-2 text-xs font-medium text-zinc-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors">
                                        <svg class="w-4 h-4 text-blue-600 fill-current" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                        <span>Facebook</span>
                                    </a>

                                    <!-- Twitter/X -->
                                    <a href="https://twitter.com/intent/tweet?text={{ rawurlencode($product->name) }}&url={{ rawurlencode(request()->url()) }}" 
                                       target="_blank" 
                                       class="flex items-center gap-2.5 px-2.5 py-2 text-xs font-medium text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 rounded-lg transition-colors">
                                        <svg class="w-4 h-4 text-zinc-900 fill-current" viewBox="0 0 24 24">
                                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                        </svg>
                                        <span>Twitter / X</span>
                                    </a>

                                    <!-- Telegram -->
                                    <a href="https://t.me/share/url?url={{ rawurlencode(request()->url()) }}&text={{ rawurlencode($product->name) }}" 
                                       target="_blank" 
                                       class="flex items-center gap-2.5 px-2.5 py-2 text-xs font-medium text-zinc-700 hover:bg-sky-50 hover:text-sky-700 rounded-lg transition-colors">
                                        <svg class="w-4 h-4 text-sky-500 fill-current" viewBox="0 0 24 24">
                                            <path d="M12 0C5.37 0 0 5.37 0 12s5.37 12 12 12 12-5.37 12-12S18.63 0 12 0zm5.56 8.61l-1.88 8.87c-.14.63-.51.79-1.04.49l-2.87-2.11-1.38 1.33c-.15.15-.28.28-.57.28l.2-2.92 5.31-4.8c.23-.21-.05-.32-.36-.11L10.3 13.06l-2.83-.89c-.61-.19-.63-.61.13-.91l11.07-4.27c.51-.19.96.11.89.62z"/>
                                        </svg>
                                        <span>Telegram</span>
                                    </a>

                                    <div class="h-px bg-zinc-100 my-1"></div>

                                    <!-- Copy Link -->
                                    <button type="button" 
                                            @click="navigator.clipboard.writeText(window.location.href); copied = true; setTimeout(() => { copied = false; open = false; }, 2000)"
                                            class="w-full flex items-center justify-between px-2.5 py-2 text-xs font-medium text-zinc-700 hover:bg-zinc-50 rounded-lg transition-colors">
                                        <div class="flex items-center gap-2.5">
                                            <span class="material-symbols-outlined text-[16px] text-zinc-500" x-show="!copied">content_copy</span>
                                            <span class="material-symbols-outlined text-[16px] text-emerald-600" x-show="copied" x-cloak>check</span>
                                            <span x-text="copied ? 'Copied!' : 'Copy Link'"></span>
                                        </div>
                                        <span class="text-[9px] font-bold text-zinc-400 uppercase tracking-widest bg-zinc-100 px-1.5 py-0.5 rounded" x-show="!copied">Ctrl+C</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Product Description -->
                        <div class="border-t border-zinc-200/70 pt-4">
                            <h2 class="mb-2 text-lg font-semibold text-zinc-900">Product Description</h2>
                            <div class="prose prose-sm max-w-none text-zinc-500">
                                {!! clean($product->description) !!}
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
