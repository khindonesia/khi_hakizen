<?php
use function Laravel\Folio\{name};
name('shopping-cart');

// Get the active cart with all necessary relationships
$cart = \App\Models\Cart::with(['items.variant.product', 'items.variant.variantAttributes.attribute'])
    ->where('user_id', auth()->id())
    ->where('status', 'active')
    ->first();

// Get cart items or empty collection if no cart exists
$cartItems = $cart ? $cart->items : collect([]);

// Calculate total price
$totalPrice = $cart ? $cart->getTotalPrice() : 0;
?>

<x-layouts.marketing :seo="[
    'title' => 'KHI - Shopping Cart',
]">
    <section class="bg-white py-8 antialiased dark:bg-gray-900 md:py-16">
        <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
            <x-marketing.elements.heading title="Shopping Cart"
                description="Review the items in your cart before proceeding to checkout." align="left" />

            <div class="mt-6 sm:mt-8 md:gap-6 lg:flex lg:items-start xl:gap-8">
                <div class="mx-auto w-full flex-none lg:max-w-2xl xl:max-w-4xl">
                    @if($cartItems->count() > 0)
                        <div class="space-y-6">
                            @foreach($cartItems as $item)
                                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 md:p-6" id="cart-item-{{ $item->id }}">
                                    <div class="space-y-4 md:flex md:items-center md:justify-between md:gap-6 md:space-y-0">
                                        <a href="#" class="shrink-0 md:order-1">
                                            <img class="h-20 w-20 object-cover rounded dark:hidden"
                                                src="{{ Storage::url('/' . $item->variant->image_url) ?: 'https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front.svg' }}"
                                                alt="{{ $item->variant->product->name }}" />
                                            <img class="hidden h-20 w-20 object-cover rounded dark:block"
                                                src="{{ Storage::url('/' . $item->variant->image_url) ?: 'https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front-dark.svg' }}"
                                                alt="{{ $item->variant->product->name }}" />
                                        </a>

                                        <div class="flex items-center justify-between md:order-3 md:justify-end">
                                            <div class="flex items-center">
                                                <button type="button" 
                                                    x-data
                                                    @click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                                    class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700">
                                                    <svg class="h-2.5 w-2.5 text-gray-900 dark:text-white" aria-hidden="true"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 2">
                                                        <path stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2" d="M1 1h16" />
                                                    </svg>
                                                </button>
                                                <input type="text" id="counter-input-{{ $item->id }}"
                                                    class="w-10 shrink-0 border-0 bg-transparent text-center text-sm font-medium text-gray-900 focus:outline-none focus:ring-0 dark:text-white"
                                                    placeholder="" value="{{ $item->quantity }}" 
                                                    x-data
                                                    @change="updateQuantity({{ $item->id }}, $event.target.value)"
                                                    required />
                                                <button type="button"
                                                    x-data
                                                    @click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                                    class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700">
                                                    <svg class="h-2.5 w-2.5 text-gray-900 dark:text-white" aria-hidden="true"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                                                        <path stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="text-end md:order-4 md:w-32">
                                                <p class="text-base font-bold text-gray-900 dark:text-white">
                                                    Rp{{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="w-full min-w-0 flex-1 space-y-4 md:order-2 md:max-w-md">
                                            <a href="#"
                                                class="text-base font-medium text-gray-900 hover:underline dark:text-white">
                                                {{ $item->variant->product->name }}
                                            </a>
                                            
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($item->variant->variantAttributes as $varAttr)
                                                <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                    {{ $varAttr->attribute->name }}: {{ $varAttr->attributeValue->value }}  <!-- Display attribute value -->
                                                </span>
                                            @endforeach
                                            
                                                <span class="inline-flex items-center rounded-md bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                                                    SKU: {{ $item->variant->sku }}
                                                </span>
                                            </div>

                                            <div class="flex items-center gap-4">
                                                <button type="button"
                                                    x-data
                                                    @click="removeItem({{ $item->id }})"
                                                    class="inline-flex items-center text-sm font-medium text-red-600 hover:underline dark:text-red-500">
                                                    <svg class="me-1.5 h-5 w-5" aria-hidden="true"
                                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        fill="none" viewBox="0 0 24 24">
                                                        <path stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"
                                                            d="M6 18 17.94 6M18 18 6.06 6" />
                                                    </svg>
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">Your cart is empty</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Start adding some products to your cart!</p>
                            <div class="mt-6">
                                <a href="/store" wire:navigate class="inline-flex items-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700">
                                    Continue Shopping
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mx-auto mt-6 max-w-4xl flex-1 space-y-6 lg:mt-0 lg:w-full">
                    <div class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-6">
                        <p class="text-xl font-semibold text-gray-900 dark:text-white">Order summary</p>

                        <div class="space-y-4">
                            <div class="space-y-2">
                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Subtotal</dt>
                                    <dd class="text-base font-medium text-gray-900 dark:text-white">
                                        Rp{{ number_format($totalPrice, 0, ',', '.') }}
                                    </dd>
                                </dl>

                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Shipping</dt>
                                    <dd class="text-base font-medium text-gray-900 dark:text-white">
                                        Calculated at checkout
                                    </dd>
                                </dl>

                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Tax</dt>
                                    <dd class="text-base font-medium text-gray-900 dark:text-white">
                                        Calculated at checkout
                                    </dd>
                                </dl>
                            </div>

                            <dl class="flex items-center justify-between gap-4 border-t border-gray-200 pt-2 dark:border-gray-700">
                                <dt class="text-base font-bold text-gray-900 dark:text-white">Total</dt>
                                <dd class="text-base font-bold text-gray-900 dark:text-white">
                                    Rp{{ number_format($totalPrice, 0, ',', '.') }}
                                </dd>
                            </dl>
                        </div>

                        <a href="#"
                            class="flex w-full items-center justify-center rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                            Proceed to Checkout
                        </a>

                        <div class="flex items-center justify-center gap-2">
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400"> or </span>
                            <a href="/store" wire:navigate title=""
                                class="inline-flex items-center gap-2 text-sm font-medium text-primary-700 underline hover:no-underline dark:text-primary-500">
                                Continue Shopping
                                <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        // Update quantity via AJAX
        function updateQuantity(cartItemId, quantity) {
            $.ajax({
                url: '/cart/items/' + cartItemId,
                type: 'PATCH',
                dataType: 'json',
                data: {
                    quantity: quantity
                },
                success: function(response) {
                    if (response.success) {
                        document.getElementById('counter-input-' + cartItemId).value = quantity;
                        document.querySelector(`#cart-item-${cartItemId} .text-base.font-bold.text-gray-900`).textContent =
                            'Rp' + new Intl.NumberFormat('id-ID').format(response.updatedItemPrice);
                    } else {
                        alert(response.message);
                    }
                }
            });
        }

        // Remove item via AJAX
        function removeItem(cartItemId) {
            $.ajax({
                url: '/cart/items/' + cartItemId,
                type: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        document.getElementById('cart-item-' + cartItemId).remove();
                    } else {
                        alert(response.message);
                    }
                }
            });
        }
    </script>
</x-layouts.marketing>
