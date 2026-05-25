<?php
    use Illuminate\View\View;
    use function Laravel\Folio\{name, render};

    name('shopping-cart');

    render(function (View $view): View {
        $cart = \App\Models\Cart::with(['items.variant.product', 'items.variant.variantAttributes.attributeValue'])
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        $cartItems = $cart ? $cart->items : collect([]);

        return $view->with([
            'cart' => $cart,
            'cartItems' => $cartItems,
            'totalPrice' => $cart ? $cart->getTotalPrice() : 0,
            'itemCount' => $cartItems->count(),
        ]);
    });
?>

<x-layouts.marketing :seo="[
    'title' => 'Shopping Cart - Komunitas Historia Indonesia',
    'description' => 'Review the items in your cart, adjust quantities, and continue to checkout with a premium historical store experience.',
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

    <div class="relative min-h-screen">
        <!-- Main Content -->
        <main class="relative mx-auto grid w-full max-w-[1280px] grid-cols-1 gap-8 px-4 py-12 sm:px-6 md:py-20 lg:grid-cols-12">
            
            <!-- Cart Items Column -->
            <div class="lg:col-span-8 flex flex-col gap-6">
                <div class="flex items-baseline justify-between border-b border-zinc-200/80 pb-4">
                    <div>
                        <div class="stitch-chip mb-3 inline-flex">Cart</div>
                        <h1 class="text-3xl font-semibold tracking-tight text-zinc-900 md:text-[36px]">Your Cart</h1>
                    </div>
                    <span id="cart-item-count-stat" class="text-base text-zinc-500">{{ $itemCount }} items</span>
                </div>

                <!-- Items list -->
                <div id="cart-items-list" class="space-y-4 @if ($cartItems->count() === 0) hidden @endif">
                    @foreach ($cartItems as $item)
                        @php
                            $normalizeImageUrl = static function (?string $path): ?string {
                                if (! $path) {
                                    return null;
                                }

                                if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
                                    return $path;
                                }

                                return \Illuminate\Support\Facades\Storage::url(ltrim($path, '/'));
                            };

                            $itemImage = $normalizeImageUrl($item->variant->image_url)
                                ?? $normalizeImageUrl($item->variant->product->images->sortBy('sort_order')->first()?->image_url)
                                ?? 'https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front.svg';
                            
                            // High-fidelity background tints for standard KHI catalog aesthetics
                            $tints = ['bg-[#FFF0EA]', 'bg-[#FFF9E6]', 'bg-[#FFF9F5]', 'bg-[#EBF5FF]', 'bg-[#fff5f5]', 'bg-[#EBF9F4]', 'bg-[#FFECF0]'];
                            $tint = $tints[$loop->index % count($tints)];
                        @endphp

                        <article id="cart-item-{{ $item->id }}" class="cart-item-card stitch-panel flex flex-col gap-6 p-4 transition-all duration-200 sm:flex-row">
                            <!-- Image container with Stitch Tint -->
                            <div class="flex w-full aspect-square flex-shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-zinc-200/60 {{ $tint }} sm:w-[120px]">
                                <img src="{{ $itemImage }}" alt="{{ $item->variant->product->name }}" class="w-full h-full object-cover">
                            </div>

                            <!-- Details container -->
                            <div class="flex flex-col flex-grow justify-between py-1">
                                <div class="flex justify-between items-start gap-4">
                                    <div>
                                        <h3 class="mb-1 text-lg font-semibold leading-snug text-zinc-900 md:text-[22px]">
                                            {{ $item->variant->product->name }}
                                        </h3>
                                        <p class="flex flex-wrap items-center gap-1.5 text-sm text-zinc-500">
                                            @if ($item->variant->variantAttributes->count() > 0)
                                                @foreach ($item->variant->variantAttributes as $varAttr)
                                                    <span>{{ $varAttr->attribute->name }}: {{ $varAttr->attributeValue->value }}</span>
                                                    @if (!$loop->last)<span>•</span>@endif
                                                @endforeach
                                            @else
                                                <span>SKU: {{ $item->variant->sku }}</span>
                                            @endif
                                        </p>
                                    </div>
                                    <p id="cart-item-total-{{ $item->id }}" class="whitespace-nowrap text-lg font-semibold text-zinc-900 md:text-[22px]">
                                        Rp{{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                                    </p>
                                </div>

                                <div class="flex justify-between items-center mt-6">
                                    <!-- Quantity Selector -->
                                    <div class="flex items-center overflow-hidden rounded-full border border-zinc-200 bg-white shadow-sm">
                                        <button
                                            type="button"
                                            onclick="window.KhiCart.updateQuantity({{ $item->id }}, 'minus', {{ $item->id }})"
                                            class="flex items-center justify-center px-3 py-1.5 text-zinc-700 transition-colors hover:bg-red-50 hover:text-red-700">
                                            <span class="material-symbols-outlined text-[18px]">remove</span>
                                        </button>
                                        <input
                                            type="number"
                                            min="1"
                                            inputmode="numeric"
                                            id="counter-input-{{ $item->id }}"
                                            class="w-10 border-0 bg-transparent p-0 text-center text-sm font-semibold text-zinc-900 focus:outline-none focus:ring-0"
                                            value="{{ $item->quantity }}"
                                            onchange="window.KhiCart.updateQuantity({{ $item->id }}, this.value, {{ $item->id }})"
                                            required />
                                        <button
                                            type="button"
                                            onclick="window.KhiCart.updateQuantity({{ $item->id }}, 'plus', {{ $item->id }})"
                                            class="flex items-center justify-center px-3 py-1.5 text-zinc-700 transition-colors hover:bg-red-50 hover:text-red-700">
                                            <span class="material-symbols-outlined text-[18px]">add</span>
                                        </button>
                                    </div>

                                    <!-- Delete Button -->
                                    <button
                                        type="button"
                                        onclick="window.KhiCart.removeItem({{ $item->id }})"
                                        class="flex items-center gap-1 text-sm font-medium text-rose-600 transition-colors hover:underline">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Empty State -->
                <div id="cart-empty-state" class="stitch-panel p-12 text-center @if ($cartItems->count() > 0) hidden @endif">
                    <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-red-50 text-red-700">
                        <span class="material-symbols-outlined text-3xl">shopping_bag</span>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-zinc-900">Your cart is empty</h3>
                    <p class="mx-auto mb-6 max-w-sm text-sm text-zinc-500">
                        Start exploring the KHI Official Store catalog to add heritage books and merchandise.
                    </p>
                    <a href="{{ route('merchandise') }}" wire:navigate class="inline-flex items-center justify-center rounded-full bg-red-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-red-500">
                        Continue Shopping
                    </a>
                </div>

                <!-- Footer link -->
                <div class="mt-4">
                    <a href="{{ route('merchandise') }}" wire:navigate class="flex w-fit items-center gap-1.5 text-sm font-medium text-zinc-700 transition-colors hover:text-red-700">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Continue Shopping
                    </a>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="lg:col-span-4">
                <div class="stitch-panel sticky top-[100px] p-6 md:p-8">
                    <h2 class="mb-6 border-b border-zinc-200/70 pb-4 text-xl font-semibold text-zinc-900 md:text-[28px]">Order Summary</h2>
                    
                    <div class="flex flex-col gap-4 mb-6">
                        <div class="flex items-center justify-between text-sm text-zinc-500 md:text-base">
                            <span>Subtotal</span>
                            <span class="font-medium text-zinc-900">Rp<span id="cart-subtotal-stat">{{ number_format($totalPrice, 0, ',', '.') }}</span></span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-zinc-500 md:text-base">
                            <span>Shipping estimate</span>
                            <span class="font-medium text-zinc-900">Calculated at checkout</span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-zinc-500 md:text-base">
                            <span>Tax estimate</span>
                            <span class="font-medium text-zinc-900">Rp0</span>
                        </div>
                    </div>

                    <div class="mb-8 flex items-baseline justify-between border-t border-zinc-200/70 pt-4">
                        <span class="text-lg font-semibold text-zinc-900">Total</span>
                        <span class="text-xl font-semibold text-red-700 md:text-[28px]">
                            Rp<span id="cart-total">{{ number_format($totalPrice, 0, ',', '.') }}</span>
                        </span>
                    </div>

                    <a href="{{ route('checkout') }}" wire:navigate class="flex w-full items-center justify-center gap-2 rounded-full bg-red-600 py-3.5 font-semibold text-white transition-all hover:bg-red-500 shadow-sm">
                        Proceed to Checkout
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </a>
                    
                    <p class="mt-4 text-center text-xs text-zinc-400">
                        Secure checkout powered by Xendit.
                    </p>
                </div>
            </div>
            
        </main>
    </div>

</x-layouts.marketing>
