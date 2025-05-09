<x-layouts.marketing :seo="[
    'title' => 'KHI - Checkout',
]">

    @php
        // Get the authenticated user
        $user = auth()->user();

        // Get active cart with its items and related models
        $cart = \App\Models\Cart::with(['items.variant.product', 'items.variant.variantAttributes.attributeValue'])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        // Calculate subtotal
        $subtotal = $cart ? $cart->getTotalPrice() : 0;

        // Fetch all addresses for the user
        $addresses = \App\Models\UserAddress::where('user_id', $user->id)->get();
        
        // Convert addresses to JSON for Alpine.js
        $addressesJson = $addresses->toJson();
    @endphp

<div class="container mx-auto p-4" x-data="checkoutPage({{ $subtotal }}, {{ $addressesJson }})">
    <div class="flex flex-col lg:flex-row lg:space-x-10">
        <div class="lg:w-2/3">
            <!-- Delivery Address Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                        <h2 class="text-lg font-medium">Delivery Address</h2>
                    </div>
                    <button class="px-4 py-1 border border-gray-300 rounded text-sm">Add a new address</button>
                </div>

          

                <!-- List All Addresses (Home, Office, etc.) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($addresses as $address)
                        <div class="border border-gray-200 rounded p-4 relative"
                             :class="{'border-green-500': selectedAddressId === {{ $address->id }}}">
                            <div class="flex items-center mb-2">
                                <div class="flex items-center">
                                    <input type="radio" name="address" id="{{ $address->address_type }}-{{ $address->id }}" class="mr-2 h-4 w-4 text-blue-600" 
                                           @click="selectAddress({{ $address->id }})" 
                                           x-model="selectedAddressId" 
                                           :value="{{ $address->id }}">
                                    <label for="{{ $address->address_type }}-{{ $address->id }}" class="font-medium">{{ ucfirst($address->address_type) }}</label>
                                </div>
                            </div>
                            <div class="text-sm">
                                <p class="font-medium">{{ $address->address_line }}</p>
                                <p>{{ $address->city }}, {{ $address->state }}</p>
                                <p>Postal Code: {{ $address->postal_code }}</p>
                                <p class="mt-1">P: <a href="tel:{{ $address->phone_number }}" class="text-gray-600">{{ $address->phone_number }}</a></p>
                            </div>
                            @if($address->is_primary)
                                <p class="mt-3 text-sm text-red-500">Primary address</p>
                            @endif
                        </div>
                    @endforeach
                    @if($addresses->isEmpty())
                        <div class="py-3 text-center text-gray-500">
                            You have no addresses saved. Please add a new address.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Delivery Service Section -->
            <div class="mb-8">
                <div class="flex items-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                        <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7h-3v7h4.05a2.5 2.5 0 014.9 0H20a1 1 0 001-1v-3a1 1 0 00-.293-.707l-2-2A1 1 0 0018 7h-4z" />
                    </svg>
                    <h2 class="text-lg font-medium">Delivery Service</h2>
                </div>

                <div class="flex space-x-4 mb-4">
                    <div class="flex items-center">
                        <input type="radio" name="courier" id="jne" class="mr-2 h-4 w-4 text-blue-600" value="jne" 
                               @click="changeCourier('jne')" x-model="selectedCourier">
                        <label for="jne">JNE</label>
                    </div>
                    <div class="flex items-center">
                        <input type="radio" name="courier" id="pos" class="mr-2 h-4 w-4 text-blue-600" value="pos" 
                               @click="changeCourier('pos')" x-model="selectedCourier">
                        <label for="pos">POS</label>
                    </div>
                    <div class="flex items-center">
                        <input type="radio" name="courier" id="jnt" class="mr-2 h-4 w-4 text-blue-600" value="jnt" 
                               @click="changeCourier('jnt')" x-model="selectedCourier">
                        <label for="jnt">JNT</label>
                    </div>
                </div>

                <p class="text-sm text-gray-600 mb-4">Available services:</p>

                <div class="border-t border-gray-200 mb-4"></div>

                <div class="grid grid-cols-3 text-sm font-medium py-2">
                    <div>Service</div>
                    <div>Estimate</div>
                    <div>Cost</div>
                </div>

                <div class="border-t border-gray-200"></div>

                <!-- Shipping Options Section -->
                <div id="shipping-options" class="mt-2">
                    <template x-if="isLoading">
                        <div class="text-center py-4 text-gray-500">Loading shipping options...</div>
                    </template>
                    
                    <template x-if="!isLoading && shippingOptions.length === 0">
                        <div class="text-center py-4 text-gray-500">No shipping options available</div>
                    </template>
                    
                    <template x-if="!isLoading && shippingOptions.length > 0">
                        <div>
                            <template x-for="(option, index) in shippingOptions" :key="index">
                                <div class="border-b border-gray-200 py-3">
                                    <div class="flex items-center">
                                        <input type="radio" :id="'service-' + index" name="shipping-service" 
                                               class="mr-2 h-4 w-4 text-blue-600" 
                                               :value="option.service" 
                                               @click="selectShippingService(option.service, option.cost)"
                                               x-model="selectedService">
                                        <div class="grid grid-cols-3 w-full">
                                            <div>
                                                <label :for="'service-' + index" class="font-medium" x-text="option.service"></label>
                                                <p class="text-xs text-gray-500" x-text="option.description"></p>
                                            </div>
                                            <div class="text-sm">
                                                <span x-text="option.etd"></span>
                                            </div>
                                            <div class="text-sm font-medium">
                                                <span x-text="formatCurrency(option.cost)"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex items-center justify-between mt-8">
                <button class="px-6 py-2 border border-green-500 text-green-500 rounded-md hover:bg-green-50">Back to Shopping Cart</button>
                <button class="px-6 py-2 bg-green-500 text-white rounded-md hover:bg-green-600" 
                        :class="{'opacity-50 cursor-not-allowed': !canPlaceOrder}" 
                        :disabled="!canPlaceOrder"
                        @click="placeOrder">Place Order</button>
            </div>
        </div>

        <!-- Order Details Section -->
        <div class="lg:w-1/3 mt-8 lg:mt-0">
            <div class="border border-gray-200 rounded-md p-6 sticky top-4">
                <h2 class="text-lg font-medium mb-6">Order Details</h2>

                <!-- Product Items from Cart -->
                @if($cart && $cart->items->count() > 0)
                    @foreach($cart->items as $item)
                    <div class="flex justify-between items-center py-3 border-b border-gray-200">
                        <div class="flex">
                            @php
                                $product = $item->variant->product;
                            @endphp
                            <img src="{{ Storage::url('/' . $item->variant->image_url) ?: 'https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front.svg' }}" alt="{{ $product->name }}" class="w-10 h-10 mr-3 object-cover">
                            <div>
                                <a href="#" class="text-blue-600 hover:underline">{{ $product->name }}</a>
                                <p class="text-sm text-gray-600">
                                    @if($item->variant->variantAttributes->count() > 0)
                                        @foreach($item->variant->variantAttributes as $varAttr)
                                            {{ $varAttr->attributeValue->value }}
                                            @if(!$loop->last), @endif
                                        @endforeach
                                    @endif
                                </p>
                                <p class="text-sm text-gray-600">IDR {{ number_format($item->price, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <span class="mr-8">{{ $item->quantity }}</span>
                            <span class="font-medium">IDR {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="py-3 text-center text-gray-500">
                        Your cart is empty.
                    </div>
                @endif

                <!-- Calculations -->
                <div class="mt-6 space-y-3">
                    <div class="flex justify-between">
                        <span>Item Subtotal</span>
                        <span class="font-medium">IDR {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Shipping Fee</span>
                        <span class="font-medium" x-text="formatCurrency(shippingFee)"></span>
                    </div>
                    <div class="flex justify-between pt-4 border-t border-gray-200 font-medium">
                        <span>Grand Total</span>
                        <span id="grand-total" x-text="formatCurrency(grandTotal)"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Checkout page Alpine.js component
 * @param {number} itemSubtotal - The subtotal of items in the cart
 * @param {Array} addresses - List of user addresses
 * @return {Object} Alpine.js component definition
 */
 function checkoutPage(itemSubtotal = 0, addresses = []) {
    return {
        // State variables
        selectedAddressId: null,
        selectedCourier: 'jne',
        selectedService: '',
        shippingOptions: [],
        isLoading: false,
        isProcessingPayment: false,
        shippingFee: 0,
        itemSubtotal: itemSubtotal,
        destinationId: null,
        originCity: 17693,
        totalWeight: 1000,
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
            
            fetch(`/api/checkout/search-destination?search=${encodeURIComponent(postalCode)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    this.isLoading = false;
                    if (data && data.data && Array.isArray(data.data) && data.data.length > 0) {
                        // Select the first matching destination
                        this.selectDestination(data.data[0]);
                    } else {
                        console.warn('No destinations found for postal code:', postalCode);
                    }
                })
                .catch(error => {
                    this.isLoading = false;
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
            this.shippingOptions = [];
            this.shippingFee = 0;
            this.selectedService = '';
            
            const requestData = {
                courier: this.selectedCourier,
                origin: this.originCity,
                destination: this.destinationId,
                weight: this.totalWeight
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
                    throw new Error('Network response was not ok');
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
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching shipping options:', error);
                this.isLoading = false;
            });
        },
        
        /**
         * Format currency value to IDR format
         * @param {number} amount - The amount to format
         * @return {string} Formatted currency string
         */
        formatCurrency(amount) {
            return 'IDR ' + Number(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
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
                courier: this.selectedCourier,
                service: this.selectedService,
                shipping_fee: this.shippingFee
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