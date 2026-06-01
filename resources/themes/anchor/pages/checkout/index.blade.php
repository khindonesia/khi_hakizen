<?php
use function Laravel\Folio\{middleware, name};

middleware('auth');
name('checkout');
?>
<x-layouts.marketing :seo="[
    'title' => 'Checkout - Komunitas Historia Indonesia',
    'description' => 'Complete your order securely and support historical education programs and archive conservation.',
]">
@php
        // Get the authenticated user
        $user = auth()->user();

        // Get active cart with its items and related models
        $cart = null;
        $addresses = collect();
        if ($user) {
            $cart = \App\Models\Cart::with(['items.variant.product.images', 'items.variant.variantAttributes.attributeValue'])
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
            
            // Fetch all addresses for the user
            $addresses = \App\Models\UserAddress::where('user_id', $user->id)->get();
        }

        // Calculate subtotal
        $subtotal = $cart ? $cart->getTotalPrice() : 0;
        
        // Calculate total weight in grams
        $totalWeight = 0;
        if ($cart) {
            foreach ($cart->items as $item) {
                $productWeight = $item->variant?->product?->weight ?? 1000;
                $totalWeight += (int) $item->quantity * (int) ($productWeight > 0 ? $productWeight : 1000);
            }
        }
        $totalWeight = max(1, $totalWeight);
        
        // Convert addresses to JSON for Alpine.js
        $addressesJson = $addresses->toJson();
    @endphp

    <div class="relative min-h-screen" x-data="checkoutPage({{ $subtotal }}, {{ $addressesJson }}, {{ $totalWeight }})">
        <main class="relative mx-auto grid w-full max-w-[1280px] grid-cols-1 gap-8 px-4 py-12 sm:px-6 md:py-20 lg:grid-cols-12">
            
            <!-- Left Column: Delivery Address & Courier Selection -->
            <div class="lg:col-span-7 flex flex-col gap-8">
                <div>
                    <div class="stitch-chip mb-3 inline-flex">Checkout</div>
                    <h1 class="text-3xl font-semibold tracking-tight text-zinc-900 md:text-[36px]">Checkout</h1>
                    <p class="mt-1.5 text-sm leading-[1.55] text-zinc-500">Complete your order with secure local delivery services and payment processing.</p>
                </div>

                <!-- Delivery Address Section -->
                <section class="stitch-panel p-6 md:p-8">
                    <div class="mb-6 flex items-center justify-between border-b border-zinc-200/70 pb-4">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-red-700" style="font-variation-settings: 'FILL' 1;">local_shipping</span>
                            <h2 class="text-xl font-semibold text-zinc-900">Delivery Address</h2>
                        </div>
                        <a href="/user-addresses/create" class="flex items-center gap-1 rounded-full border border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-700 transition-all hover:border-red-200 hover:text-red-700">
                            <span class="material-symbols-outlined text-[18px]">add</span>
                            Add address
                        </a>
                    </div>

                    <!-- List All Addresses -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($addresses as $address)
                            <div class="relative cursor-pointer select-none rounded-2xl border border-zinc-200 bg-white p-4 transition-all"
                                 @click="selectAddress({{ $address->id }})"
                                 :class="selectedAddressId === {{ $address->id }} ? 'border-blue-500 ring-4 ring-blue-100 bg-red-50/70' : 'border-zinc-200 hover:border-red-200 hover:bg-red-50/30'">
                                <div class="flex items-center mb-3">
                                    <input type="radio" name="address" id="address-{{ $address->id }}" class="mr-2.5 h-4 w-4 text-red-600 focus:ring-red-100 border-zinc-300" 
                                           x-model="selectedAddressId" 
                                           :value="{{ $address->id }}">
                                    <label for="address-{{ $address->id }}" class="cursor-pointer text-sm font-semibold text-zinc-900">{{ ucfirst($address->address_type) }}</label>
                                </div>
                                <div class="space-y-0.5 pl-6.5 text-sm leading-[1.55] text-zinc-500">
                                    <p class="font-medium text-zinc-900">{{ $address->address_line }}</p>
                                    <p>{{ $address->city }}, {{ $address->state }}</p>
                                    <p>Postal Code: {{ $address->postal_code }}</p>
                                    <p class="mt-2 flex items-center gap-1 text-xs text-zinc-400">
                                        <span class="material-symbols-outlined text-[14px]">phone</span>
                                        {{ $address->phone_number }}
                                    </p>
                                </div>
                                @if($address->is_primary)
                                    <span class="absolute right-4 top-4 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-red-700">Primary</span>
                                @endif
                            </div>
                        @endforeach
                        
                        @if($addresses->isEmpty())
                            <div class="col-span-2 rounded-2xl border border-dashed border-zinc-200 bg-white/70 py-8 text-center text-zinc-400">
                                <span class="material-symbols-outlined text-4xl text-[#ccc3d5] mb-2 block">location_off</span>
                                <p class="text-sm font-medium text-zinc-500">You have no addresses saved.</p>
                                <a href="/user-addresses/create" class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-red-600 px-4 py-2 text-xs font-semibold text-white transition-all hover:bg-red-500">
                                    <span class="material-symbols-outlined text-[16px]">add</span>
                                    Add Your First Address
                                </a>
                            </div>
                        @endif
                    </div>
                </section>

                <!-- Courier Service Section -->
                <section class="stitch-panel p-6 md:p-8">
                    <div class="mb-6 flex items-center gap-2 border-b border-zinc-200/70 pb-4">
                        <span class="material-symbols-outlined text-red-700" style="font-variation-settings: 'FILL' 1;">package</span>
                        <h2 class="text-xl font-semibold text-zinc-900">Delivery Service</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        <!-- JNE Option -->
                        <label class="flex select-none items-center rounded-2xl border border-zinc-200 bg-white p-4 cursor-pointer transition-all hover:bg-red-50/30"
                               :class="selectedCourier === 'jne' ? 'border-blue-500 ring-4 ring-blue-100 bg-red-50/70' : 'border-zinc-200'">
                            <input type="radio" name="courier" id="jne" class="mr-3 h-4 w-4 border-zinc-300 text-red-600 focus:ring-red-100" value="jne" 
                                   @click="changeCourier('jne')" x-model="selectedCourier">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-zinc-900">JNE Express</span>
                                <span class="mt-0.5 text-[11px] text-zinc-400">Jalur Nugraha Ekakurir</span>
                            </div>
                        </label>
                        
                        <!-- POS Option -->
                        <label class="flex select-none items-center rounded-2xl border border-zinc-200 bg-white p-4 cursor-pointer transition-all hover:bg-red-50/30"
                               :class="selectedCourier === 'pos' ? 'border-blue-500 ring-4 ring-blue-100 bg-red-50/70' : 'border-zinc-200'">
                            <input type="radio" name="courier" id="pos" class="mr-3 h-4 w-4 border-zinc-300 text-red-600 focus:ring-red-100" value="pos" 
                                   @click="changeCourier('pos')" x-model="selectedCourier">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-zinc-900">POS Indonesia</span>
                                <span class="mt-0.5 text-[11px] text-zinc-400">BUMN Pos & Logistik</span>
                            </div>
                        </label>
                        
                        <!-- JNT Option -->
                        <label class="flex select-none items-center rounded-2xl border border-zinc-200 bg-white p-4 cursor-pointer transition-all hover:bg-red-50/30"
                               :class="selectedCourier === 'jnt' ? 'border-blue-500 ring-4 ring-blue-100 bg-red-50/70' : 'border-zinc-200'">
                            <input type="radio" name="courier" id="jnt" class="mr-3 h-4 w-4 border-zinc-300 text-red-600 focus:ring-red-100" value="jnt" 
                                   @click="changeCourier('jnt')" x-model="selectedCourier">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-zinc-900">J&T Express</span>
                                <span class="mt-0.5 text-[11px] text-zinc-400">Jet & Tony Express</span>
                            </div>
                        </label>
                    </div>

                    <p class="text-sm font-semibold text-[#37352F] mb-3">Available services:</p>

                    <!-- Shipping Options List -->
                    <div class="border border-[#E9E9E8] rounded-xl overflow-hidden bg-white shadow-sm">
                        <div class="grid grid-cols-12 text-[#979A9B] uppercase font-semibold text-xs tracking-wider p-4 bg-[#fffafb] border-b border-[#E9E9E8]">
                            <div class="col-span-6">Service & Description</div>
                            <div class="col-span-3 text-center">Estimate</div>
                            <div class="col-span-3 text-right">Cost</div>
                        </div>
                        
                        <div id="shipping-options" class="divide-y divide-[#E9E9E8]">
                            <template x-if="isLoading">
                                <div class="text-center py-8 text-[#575e75] flex flex-col items-center gap-2 justify-center bg-white">
                                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-[#df1c24]"></div>
                                    <span class="text-sm font-medium">Fetching available shipping services...</span>
                                </div>
                            </template>
                            
                            <template x-if="!isLoading && shippingError">
                                <div class="text-center py-8 text-zinc-500 text-sm bg-white px-4 flex flex-col items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-zinc-400 text-3xl">info</span>
                                    <p class="font-medium text-zinc-800">Layanan pengiriman tidak tersedia saat ini</p>
                                    <p class="text-xs text-zinc-400 max-w-md">Terjadi kesalahan saat memproses data pengiriman. Silakan periksa kembali alamat Anda atau coba beberapa saat lagi.</p>
                                </div>
                            </template>
                            
                            <template x-if="!isLoading && !shippingError && shippingOptions.length === 0">
                                <div class="text-center py-8 text-[#979A9B] text-sm bg-white">
                                    <span x-text="selectedAddressId ? 'No shipping services available for the selected address.' : 'Please select or add an address.'"></span>
                                </div>
                            </template>
                            
                            <template x-if="!isLoading && shippingOptions.length > 0">
                                <div>
                                    <template x-for="(option, index) in shippingOptions" :key="index">
                                        <div class="p-4 grid grid-cols-12 items-center hover:bg-[#fef2f2]/10 transition-colors cursor-pointer select-none"
                                             @click="selectShippingService(option.service, option.cost)"
                                             :class="selectedService === option.service ? 'bg-[#fff5f5]' : 'bg-white'">
                                            <div class="col-span-6 flex items-center">
                                                <input type="radio" :id="'service-' + index" name="shipping-service" 
                                                       class="mr-3 h-4 w-4 text-[#df1c24] focus:ring-[#df1c24] border-[#D1D1D0]" 
                                                       :value="option.service" 
                                                       x-model="selectedService">
                                                <div>
                                                    <label :for="'service-' + index" class="font-semibold text-sm text-[#37352F] cursor-pointer" x-text="option.service"></label>
                                                    <p class="text-xs text-[#979A9B] mt-0.5" x-text="option.description || 'Courier service option'"></p>
                                                </div>
                                            </div>
                                            <div class="col-span-3 text-center text-sm text-[#575e75]" x-text="option.etd ? option.etd + ' Days' : '-'"></div>
                                            <div class="col-span-3 text-right font-semibold text-sm text-[#37352F]" x-text="formatCurrency(option.cost)"></div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </section>

                <div class="flex items-center justify-between border-t border-[#E9E9E8] pt-6 mt-2">
                    <a href="{{ route('shopping-cart') }}" wire:navigate class="px-5 py-2.5 border border-[#E9E9E8] text-[#37352F] hover:bg-[#e7e0eb]/30 rounded-lg text-sm font-medium transition-all flex items-center gap-1">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Back to Cart
                    </a>
                </div>
            </div>

            <!-- Right Column: Order Summary Sidebar -->
            <aside class="lg:col-span-5">
                <div class="bg-[#fff5f5] rounded-xl p-6 md:p-8 shadow-sm border border-[#E9E9E8] sticky top-[100px]">
                    <h2 class="text-xl md:text-[28px] font-semibold text-[#37352F] pb-4 mb-6 border-b border-[#D1D1D0]">Order Summary</h2>
                    
                    <!-- Cart Items preview -->
                    <div class="space-y-4 mb-6 max-h-[300px] overflow-y-auto pr-2 divide-y divide-[#E9E9E8]/30">
                        @if($cart && $cart->items->count() > 0)
                            @foreach($cart->items as $index => $item)
                                @php
                                    $product = $item->variant->product;
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
                                    
                                    $tints = ['bg-[#FFF0EA]', 'bg-[#FFF9E6]', 'bg-[#FFF9F5]', 'bg-[#EBF5FF]', 'bg-[#fff5f5]'];
                                    $tint = $tints[$index % count($tints)];
                                @endphp
                                <div class="flex justify-between items-center py-3.5 @if($loop->first) pt-0 @endif">
                                    <div class="flex items-center gap-3">
                                        <div class="w-14 h-14 {{ $tint }} border border-[#E9E9E8]/60 rounded-lg overflow-hidden flex-shrink-0 flex items-center justify-center">
                                            <img src="{{ $itemImage }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-sm text-[#37352F] leading-tight">{{ $product->name }}</h4>
                                            <p class="text-xs text-[#979A9B] mt-1 flex flex-wrap items-center gap-1.5">
                                                <span>Qty: {{ $item->quantity }}</span>
                                                @if($item->variant->variantAttributes->count() > 0)
                                                    <span>•</span>
                                                    @foreach($item->variant->variantAttributes as $varAttr)
                                                        <span>{{ $varAttr->attributeValue->value }}</span>
                                                        @if(!$loop->last)<span>,</span>@endif
                                                    @endforeach
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <span class="font-semibold text-sm text-[#37352F] whitespace-nowrap">
                                        Rp{{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        @else
                            <div class="py-6 text-center text-[#979A9B] text-sm">
                                Your cart is empty.
                            </div>
                        @endif
                    </div>
                    
                    <!-- Pricing Breakdown -->
                    <div class="flex flex-col gap-4 mb-6 border-t border-[#D1D1D0] pt-4">
                        <div class="flex justify-between items-center text-[#575e75] text-sm md:text-base">
                            <span>Item Subtotal</span>
                            <span class="text-[#37352F] font-medium">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-[#575e75] text-sm md:text-base">
                            <span>Shipping Fee</span>
                            <span class="text-[#37352F] font-medium" x-text="formatCurrency(shippingFee)"></span>
                        </div>
                    </div>

                    <div class="border-t border-[#D1D1D0] pt-4 mb-8 flex justify-between items-baseline">
                        <span class="text-lg font-semibold text-[#37352F]">Total</span>
                        <span class="text-xl md:text-[28px] font-bold text-[#df1c24]">
                            <span id="grand-total" x-text="formatCurrency(grandTotal)"></span>
                        </span>
                    </div>

                    <button class="w-full bg-[#df1c24] text-white font-medium py-3.5 rounded-lg hover:bg-opacity-90 transition-all flex justify-center items-center gap-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="!canPlaceOrder || isProcessingPayment"
                            @click="placeOrder">
                        <span class="material-symbols-outlined text-[18px]">lock</span>
                        <span x-text="isProcessingPayment ? 'Processing Payment...' : 'Place Order'"></span>
                    </button>
                    
                    <p class="text-xs text-[#979A9B] text-center mt-4 flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">verified_user</span> Secure checkout provided by KHI Store.
                    </p>
                </div>
            </aside>
            
        </main>
    </div>

    <script>
    /**
     * Checkout page Alpine.js component
     * @param {number} itemSubtotal - The subtotal of items in the cart
     * @param {Array} addresses - List of user addresses
     * @return {Object} Alpine.js component definition
     */
     function checkoutPage(itemSubtotal = 0, addresses = [], totalWeight = 1000) {
        return {
            // State variables
            selectedAddressId: null,
            selectedCourier: 'jne',
            selectedService: '',
            shippingOptions: [],
            isLoading: false,
            isProcessingPayment: false,
            shippingFee: 0,
            shippingError: null,
            itemSubtotal: itemSubtotal,
            destinationId: null,
            originCity: {{ config('services.rajaongkir.origin_id', 17693) }},
            shippingPriceType: @json(config('services.rajaongkir.price_type', 'lowest')),
            totalWeight: totalWeight,
            searchResults: [],
            selectedDestination: null,
            addressCache: {},
            paymentUrl: null,
            
            /**
             * Initialize component
             */
            init() {
                // Create a lookup cache for addresses for O(1) access
                this.addresses = Array.isArray(addresses) ? addresses : [];
                
                // Cache addresses for quick lookup
                if (this.addresses.length > 0) {
                    this.addresses.forEach(address => {
                        if (address && address.id) {
                            this.addressCache[address.id] = address;
                        }
                    });
                    
                    // Find primary address or use the first one
                    const primaryAddress = this.addresses.find(a => a.is_primary);
                    const defaultAddress = primaryAddress || this.addresses[0];
                    
                    if (defaultAddress && defaultAddress.id) {
                        // Select the default address
                        this.$nextTick(() => {
                            this.selectAddress(defaultAddress.id);
                        });
                    }
                }
            },
            
            /**
             * Computed property for grand total
             */
            get grandTotal() {
                return this.itemSubtotal + this.shippingFee;
            },
            
            /**
             * Computed property to check if order can be placed
             */
            get canPlaceOrder() {
                return this.selectedService && this.destinationId && this.selectedAddressId;
            },
            
            /**
             * Select an address and fetch destination data
             * @param {number} addressId - The ID of the selected address
             */
            selectAddress(addressId) {
                if (!addressId) return;
                
                this.selectedAddressId = addressId;
                this.shippingError = null;
                this.destinationId = null;
                this.selectedDestination = null;
                this.shippingOptions = [];
                this.shippingFee = 0;
                this.selectedService = '';
                const address = this.addressCache[addressId];
                
                if (address && address.postal_code) {
                    this.searchDestinationByPostalCode(address.postal_code);
                }
            },
            
            /**
             * Change courier and fetch shipping options
             * @param {string} courierCode - The courier code
             */
            changeCourier(courierCode) {
                this.selectedCourier = courierCode;
                this.selectedService = '';
                this.shippingError = null;
                this.fetchShippingOptions();
            },
            
            /**
             * Select shipping service and update shipping fee
             * @param {string} service - The service code
             * @param {number} cost - The service cost
             */
            selectShippingService(service, cost) {
                this.selectedService = service;
                this.shippingFee = cost;
            },
            
            /**
             * Handle destination search input
             * @param {string} query - The search query
             */
            handleDestinationSearch(query) {
                if (!query || query.length < 3) {
                    this.searchResults = [];
                    return;
                }
                
                // Debounce is handled by Alpine's @input.debounce
                this.searchDestination(query);
            },
            
            /**
             * Search for destinations by query
             * @param {string} query - The search query
             */
            searchDestination(query) {
                fetch(`/api/checkout/search-destination?search=${encodeURIComponent(query)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.data && Array.isArray(data.data)) {
                            this.searchResults = data.data;
                        } else {
                            this.searchResults = [];
                        }
                    })
                    .catch(error => {
                        console.error('Error searching destinations:', error);
                        this.searchResults = [];
                    });
            },
            
            /**
             * Search for destinations by postal code
             * @param {string} postalCode - The postal code to search
             */
            searchDestinationByPostalCode(postalCode) {
                if (!postalCode) return;
                
                this.isLoading = true;
                this.shippingError = null;
                
                fetch(`/api/checkout/search-destination?search=${encodeURIComponent(postalCode)}`)
                    .then(response => {
                        if (!response.ok) {
                            return response.json().catch(() => ({})).then(errorBody => {
                                throw new Error(errorBody.message || errorBody.error || 'Gagal mencari destinasi pengiriman.');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        this.isLoading = false;
                        if (data && data.data && Array.isArray(data.data) && data.data.length > 0) {
                            // Select the first matching destination
                            this.selectDestination(data.data[0]);
                        } else {
                            this.destinationId = null;
                            this.selectedDestination = null;
                            this.shippingOptions = [];
                            this.shippingFee = 0;
                            this.selectedService = '';
                            this.shippingError = 'Alamat tersimpan belum cocok dengan data destinasi RajaOngkir.';
                            console.warn('No destinations found for postal code:', postalCode);
                        }
                    })
                    .catch(error => {
                        this.isLoading = false;
                        this.destinationId = null;
                        this.selectedDestination = null;
                        this.shippingOptions = [];
                        this.shippingFee = 0;
                        this.selectedService = '';
                        this.shippingError = error.message || 'Gagal mencari destinasi pengiriman.';
                        console.error('Error searching destinations by postal code:', error);
                    });
            },
            
            /**
             * Select a destination and fetch shipping options
             * @param {Object} destination - The selected destination
             */
            selectDestination(destination) {
                if (!destination) return;
                
                this.selectedDestination = destination;
                this.destinationId = destination.id;
                this.searchResults = [];
                this.fetchShippingOptions();
            },
            
            /**
             * Fetch shipping options based on selected parameters
             */
            fetchShippingOptions() {
                if (!this.destinationId) {
                    return;
                }
                
                this.isLoading = true;
                this.shippingError = null;
                this.shippingOptions = [];
                this.shippingFee = 0;
                this.selectedService = '';
                
                const requestData = {
                    courier: this.selectedCourier,
                    origin: this.originCity,
                    destination: this.destinationId,
                    weight: this.totalWeight,
                    price: this.shippingPriceType
                };
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                
                fetch('/api/checkout/shipping-cost', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().catch(() => ({})).then(errorBody => {
                            throw new Error(errorBody.message || errorBody.error || 'Failed to fetch shipping options.');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    this.isLoading = false;
                    if (data && data.data && Array.isArray(data.data) && data.data.length > 0) {
                        this.shippingOptions = data.data;
                        
                        // Auto-select the first option for convenience
                        if (this.shippingOptions.length > 0) {
                            this.selectShippingService(
                                this.shippingOptions[0].service, 
                                this.shippingOptions[0].cost
                            );
                            return;
                        }
                    }

                    this.shippingError = data.message || data.error || 'Tidak ada layanan pengiriman untuk alamat ini.';
                })
                .catch(error => {
                    console.error('Error fetching shipping options:', error);
                    this.shippingError = error.message || 'Gagal mengambil ongkos kirim.';
                    this.shippingOptions = [];
                    this.shippingFee = 0;
                    this.selectedService = '';
                    this.isLoading = false;
                });
            },
            
            /**
             * Format currency value to IDR format
             * @param {number} amount - The amount to format
             * @return {string} Formatted currency string
             */
            formatCurrency(amount) {
                return 'Rp' + Number(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            },
            
            /**
             * Create invoice with Xendit and redirect to payment page
             */
            placeOrder() {
                if (!this.canPlaceOrder) {
                    alert('Please select a shipping service and address before placing your order.');
                    return;
                }
                
                this.isProcessingPayment = true;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                
                const orderData = {
                    address_id: this.selectedAddressId,
                    courier_code: this.selectedCourier,
                    service_code: this.selectedService
                };
                
                fetch('/api/checkout/create-invoice', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
                    },
                    body: JSON.stringify(orderData)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    this.isProcessingPayment = false;
                    
                    if (data.status === 'success' && data.data && data.data.invoice_url) {
                        // Redirect to Xendit payment page
                        window.location.href = data.data.invoice_url;
                    } else {
                        alert('Failed to create payment: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    this.isProcessingPayment = false;
                    console.error('Error creating invoice:', error);
                    alert('Failed to create payment: ' + error.message);
                });
            }
        }
    }
    </script>
</x-layouts.marketing>
