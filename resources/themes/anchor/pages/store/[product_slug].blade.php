<?php
use function Laravel\Folio\{name};
name('store.detail');

// Query product with all related data
$product = \App\Models\Product::where('products.slug', $product_slug ?? '')
    ->with(['variants', 'variants.variantAttributes.attribute', 'variants.variantAttributes.attributeValue', 'images', 'productAttributes.attribute', 'productAttributes.attributeValue'])
    ->first();

if ($product) {
    // Get default variant
    $defaultVariant = $product->defaultVariant;

    // Get all images (variant images and product images)
    $allImages = collect();

    // Add variant images
    foreach ($product->variants as $variant) {
        if ($variant->image_url) {
            $allImages->push([
                'url' => $variant->image_url,
                'variant_id' => $variant->id,
                'is_default' => $variant->is_default,
            ]);
        }
    }

    // Add product images
    foreach ($product->images as $image) {
        $allImages->push([
            'url' => $image->image_url,
            'variant_id' => null,
            'is_default' => false,
        ]);
    }

    // Sort images to ensure default variant image comes first
    $allImages = $allImages->sortByDesc('is_default')->values();

    // Get the main image (default variant image or first available)
    $mainImage = $allImages->first()['url'] ?? null;

    // Organize attributes and values for selection
    $attributeGroups = collect();

    // Get unique attributes used by this product's variants
    $productAttributes = $product->productAttributes->groupBy(function ($item) {
        return $item->attribute->name;
    });

    // For each attribute, collect all possible values used across variants
    foreach ($productAttributes as $attributeName => $attributes) {
        $values = collect();
        foreach ($attributes as $productAttribute) {
            $values->push([
                'value_id' => $productAttribute->attribute_value_id,
                'value' => $productAttribute->attributeValue->value,
            ]);
        }
        $attributeGroups->push([
            'name' => $attributeName,
            'id' => $attributes->first()->attribute_id,
            'values' => $values->unique('value_id')->values(),
        ]);
    }

    // Create a mapping of variants to their attributes for JavaScript
    $variantsMap = [];
    foreach ($product->variants as $variant) {
        $attributes = [];
        foreach ($variant->variantAttributes as $variantAttribute) {
            $attributes[$variantAttribute->attribute_id] = $variantAttribute->attribute_value_id;
        }

        $variantsMap[] = [
            'id' => $variant->id,
            'price' => $variant->price,
            'stock' => $variant->stock_quantity,
            'sku' => $variant->sku,
            'image_url' => $variant->image_url ? Storage::url('/' . $variant->image_url) : null,
            'attributes' => $attributes,
        ];
    }
}
?>

<x-layouts.marketing>
    <section class="py-8 bg-white md:py-16 dark:bg-gray-900 antialiased">
        <div class="max-w-screen-xl px-4 mx-auto 2xl:px-0">
            <div class="md:grid md:grid-cols-2 md:gap-8 xl:gap-16">
                <div class="shrink-0 max-w-md lg:max-w-lg mx-auto">
                    <!-- Main Product Image -->
                    <img id="main-product-image" class="w-full h-auto object-cover rounded-lg"
                        src="{{ Storage::url('/' . $mainImage) }}" alt="{{ $product->name }}" />

                    <!-- Image Slider -->
                    @if ($allImages->count() > 0)
                        <div class="flex flex-wrap gap-3 items-center mt-4 overflow-x-auto">
                            @foreach ($allImages as $index => $image)
                                <img class="w-24 h-24 object-cover rounded cursor-pointer product-thumbnail {{ $index === 0 ? 'ring-2 ring-primary-500' : '' }}"
                                    src="{{ Storage::url('/' . $image['url']) }}"
                                    data-image-url="{{ Storage::url('/' . $image['url']) }}"
                                    data-variant-id="{{ $image['variant_id'] }}" alt="{{ $product->name }} thumbnail"
                                    onclick="updateMainImage(this)" />
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="mt-6 sm:mt-8 lg:mt-0">
                    <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">
                        {{ $product->name }}
                    </h1>
                    <div class="mt-4 sm:items-center sm:gap-4 sm:flex">
                        <p class="text-2xl font-extrabold text-gray-900 sm:text-3xl dark:text-white">
                            Rp {{ number_format($defaultVariant->price ?? 0, 0, ',', '.') }}
                        </p>

                        <div class="flex items-center gap-2 mt-2 sm:mt-0">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-yellow-300" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M13.849 4.22c-.684-1.626-3.014-1.626-3.698 0L8.397 8.387l-4.552.361c-1.775.14-2.495 2.331-1.142 3.477l3.468 2.937-1.06 4.392c-.413 1.713 1.472 3.067 2.992 2.149L12 19.35l3.897 2.354c1.52.918 3.405-.436 2.992-2.15l-1.06-4.39 3.468-2.938c1.353-1.146.633-3.336-1.142-3.477l-4.552-.36-1.754-4.17Z" />
                                </svg>
                                <svg class="w-4 h-4 text-yellow-300" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M13.849 4.22c-.684-1.626-3.014-1.626-3.698 0L8.397 8.387l-4.552.361c-1.775.14-2.495 2.331-1.142 3.477l3.468 2.937-1.06 4.392c-.413 1.713 1.472 3.067 2.992 2.149L12 19.35l3.897 2.354c1.52.918 3.405-.436 2.992-2.15l-1.06-4.39 3.468-2.938c1.353-1.146.633-3.336-1.142-3.477l-4.552-.36-1.754-4.17Z" />
                                </svg>
                                <svg class="w-4 h-4 text-yellow-300" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M13.849 4.22c-.684-1.626-3.014-1.626-3.698 0L8.397 8.387l-4.552.361c-1.775.14-2.495 2.331-1.142 3.477l3.468 2.937-1.06 4.392c-.413 1.713 1.472 3.067 2.992 2.149L12 19.35l3.897 2.354c1.52.918 3.405-.436 2.992-2.15l-1.06-4.39 3.468-2.938c1.353-1.146.633-3.336-1.142-3.477l-4.552-.36-1.754-4.17Z" />
                                </svg>
                                <svg class="w-4 h-4 text-yellow-300" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M13.849 4.22c-.684-1.626-3.014-1.626-3.698 0L8.397 8.387l-4.552.361c-1.775.14-2.495 2.331-1.142 3.477l3.468 2.937-1.06 4.392c-.413 1.713 1.472 3.067 2.992 2.149L12 19.35l3.897 2.354c1.52.918 3.405-.436 2.992-2.15l-1.06-4.39 3.468-2.938c1.353-1.146.633-3.336-1.142-3.477l-4.552-.36-1.754-4.17Z" />
                                </svg>
                                <svg class="w-4 h-4 text-yellow-300" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M13.849 4.22c-.684-1.626-3.014-1.626-3.698 0L8.397 8.387l-4.552.361c-1.775.14-2.495 2.331-1.142 3.477l3.468 2.937-1.06 4.392c-.413 1.713 1.472 3.067 2.992 2.149L12 19.35l3.897 2.354c1.52.918 3.405-.436 2.992-2.15l-1.06-4.39 3.468-2.938c1.353-1.146.633-3.336-1.142-3.477l-4.552-.36-1.754-4.17Z" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium leading-none text-gray-500 dark:text-gray-400">
                                (5.0)
                            </p>
                            <a href="#"
                                class="text-sm font-medium leading-none text-gray-900 underline hover:no-underline dark:text-white">
                                345 Reviews
                            </a>
                        </div>
                    </div>

                    <!-- Variant Attributes Selection -->
                    @if ($attributeGroups->count() > 0)
                        <div class="mt-6">
                            @foreach ($attributeGroups as $attributeGroup)
                                <div class="mb-4">
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                                        {{ $attributeGroup['name'] }}
                                    </h3>
                                    <div class="flex flex-wrap gap-2" data-attribute-id="{{ $attributeGroup['id'] }}">
                                        @foreach ($attributeGroup['values'] as $value)
                                            <button type="button"
                                                class="attribute-selector px-3 py-1 text-sm border border-gray-300 rounded-md hover:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                                data-attribute-id="{{ $attributeGroup['id'] }}"
                                                data-value-id="{{ $value['value_id'] }}">
                                                {{ $value['value'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Quantity Selection -->
                    <div class="mt-6">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Quantity</h3>
                        <div class="flex items-center">
                            <button type="button" id="decrease-quantity"
                                class="p-2 border border-gray-300 rounded-l-md hover:bg-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 12H4" />
                                </svg>
                            </button>
                            <input type="number" id="quantity" min="1" value="1"
                                class="p-2 w-16 text-center border-t border-b border-gray-300 focus:outline-none focus:ring-primary-500" />
                            <button type="button" id="increase-quantity"
                                class="p-2 border border-gray-300 rounded-r-md hover:bg-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                            <span id="stock-status" class="ml-3 text-sm text-gray-500">
                                {{ $defaultVariant ? ($defaultVariant->stock_quantity > 0 ? 'In Stock: ' . $defaultVariant->stock_quantity : 'Out of Stock') : 'No Stock Information' }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 sm:gap-6 sm:items-center sm:flex">
                        <button id="add-to-cart-btn"
                            class="text-white mt-4 sm:mt-0 bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800 flex items-center justify-center"
                            {{ !$defaultVariant || $defaultVariant->stock_quantity <= 0 ? 'disabled' : '' }}>
                            <svg class="w-5 h-5 -ms-2 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 4h1.5L8 16m0 0h8m-8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm.75-3H7.5M11 7H6.312M17 4v6m-3-3h6" />
                            </svg>
                            Add to cart
                        </button>
                    </div>

                    <hr class="my-6 md:my-8 border-gray-200 dark:border-gray-800" />

                    <div class="line-clamp-[13]">
                        {!! $product->description !!}
                    </div>
                    <div class="mt-2">
                        <span
                            class="cursor-pointer text-[#c6303e] border-2 border-[#c6303e] rounded-md px-1">Readmore</span>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Store variants data for easy access
            const variants = @json($variantsMap);

            // Initialize current variant data
            let currentVariant = null;
            @if ($defaultVariant)
                currentVariant = {
                    id: {{ $defaultVariant->id }},
                    price: {{ $defaultVariant->price }},
                    stock: {{ $defaultVariant->stock_quantity }},
                    image_url: @if ($defaultVariant->image_url)
                        "{{ Storage::url('/' . $defaultVariant->image_url) }}"
                    @else
                        null
                    @endif
                };
            @endif

            // Set initial selections based on default variant
            if (currentVariant) {
                const defaultAttributes = variants.find(v => v.id === currentVariant.id)?.attributes || {};

                // Select the default attribute values
                Object.entries(defaultAttributes).forEach(([attributeId, valueId]) => {
                    const button = document.querySelector(
                        `button[data-attribute-id="${attributeId}"][data-value-id="${valueId}"]`);
                    if (button) {
                        button.classList.add('bg-primary-100', 'border-primary-500');
                    }
                });
            }

            // Handle attribute selection
            const attributeSelectors = document.querySelectorAll('.attribute-selector');
            attributeSelectors.forEach(button => {
                button.addEventListener('click', function() {
                    const attributeId = this.dataset.attributeId;
                    const valueId = this.dataset.valueId;

                    // Update UI for this attribute group
                    const siblings = document.querySelectorAll(
                        `button[data-attribute-id="${attributeId}"]`);
                    siblings.forEach(sib => {
                        sib.classList.remove('bg-primary-100', 'border-primary-500');
                    });
                    this.classList.add('bg-primary-100', 'border-primary-500');

                    // Get current selections
                    const selections = {};
                    document.querySelectorAll('.attribute-selector.bg-primary-100').forEach(
                        selected => {
                            selections[selected.dataset.attributeId] = selected.dataset.valueId;
                        });

                    // Find matching variant
                    findAndUpdateVariant(selections);
                });
            });

            // Function to find and update the selected variant
            function findAndUpdateVariant(selections) {
                // Convert selections to the expected format
                const attributeSelections = {};
                Object.entries(selections).forEach(([key, value]) => {
                    attributeSelections[key] = parseInt(value);
                });

                // Find a variant that matches all selected attributes
                const matchingVariant = variants.find(variant => {
                    const variantAttributes = variant.attributes;
                    // Check if all selections match this variant
                    for (const [attrId, valueId] of Object.entries(attributeSelections)) {
                        if (variantAttributes[attrId] !== valueId) {
                            return false;
                        }
                    }
                    return true;
                });

                // Update UI with the matching variant
                if (matchingVariant) {
                    updateVariantUI(matchingVariant);
                }
            }

            // Update UI based on selected variant
            function updateVariantUI(variant) {
                currentVariant = variant;

                // Update price
                const priceElement = document.querySelector('.text-2xl.font-extrabold');
                if (priceElement) {
                    priceElement.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(variant.price)}`;
                }

                // Update stock information
                const stockStatus = document.getElementById('stock-status');
                if (stockStatus) {
                    if (variant.stock > 0) {
                        stockStatus.textContent = `In Stock: ${variant.stock}`;
                        stockStatus.classList.remove('text-red-500');
                        stockStatus.classList.add('text-gray-500');
                    } else {
                        stockStatus.textContent = 'Out of Stock';
                        stockStatus.classList.remove('text-gray-500');
                        stockStatus.classList.add('text-red-500');
                    }
                }

                // Update add to cart button
                const addToCartBtn = document.getElementById('add-to-cart-btn');
                if (addToCartBtn) {
                    if (variant.stock > 0) {
                        addToCartBtn.removeAttribute('disabled');
                    } else {
                        addToCartBtn.setAttribute('disabled', 'disabled');
                    }
                }

                // Update image if variant has its own image
                if (variant.image_url) {
                    // Find the corresponding thumbnail
                    const thumbnails = document.querySelectorAll('.product-thumbnail');
                    thumbnails.forEach(thumb => {
                        if (thumb.dataset.imageUrl === variant.image_url) {
                            updateMainImage(thumb);
                        }
                    });
                }

                // Update quantity input max value
                const quantityInput = document.getElementById('quantity');
                if (quantityInput && variant.stock > 0) {
                    quantityInput.max = variant.stock;
                    if (parseInt(quantityInput.value) > variant.stock) {
                        quantityInput.value = variant.stock;
                    }
                }
            }

            // Handle quantity controls
            const quantityInput = document.getElementById('quantity');
            const decreaseBtn = document.getElementById('decrease-quantity');
            const increaseBtn = document.getElementById('increase-quantity');

            decreaseBtn.addEventListener('click', function() {
                const currentVal = parseInt(quantityInput.value);
                if (currentVal > 1) {
                    quantityInput.value = currentVal - 1;
                }
            });

            increaseBtn.addEventListener('click', function() {
                const currentVal = parseInt(quantityInput.value);
                const max = currentVariant ? currentVariant.stock : 0;
                if (currentVal < max) {
                    quantityInput.value = currentVal + 1;
                }
            });

            quantityInput.addEventListener('change', function() {
                const max = currentVariant ? currentVariant.stock : 0;
                if (parseInt(this.value) > max) {
                    this.value = max;
                }
                if (parseInt(this.value) < 1) {
                    this.value = 1;
                }
            });

            // Add to cart button event listener
            const addToCartBtn = document.getElementById('add-to-cart-btn');
            addToCartBtn.addEventListener('click', async function(e) {
                e.preventDefault()

                if (!currentVariant) return;

                // Check if user is logged in
                const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};

                if (!isLoggedIn) {
                    // Redirect to login page with returnUrl parameter to come back to this page after login
                    window.location.href = '/login?returnUrl=' + encodeURIComponent(window.location
                        .pathname);
                    return;
                }

                const quantity = parseInt(quantityInput.value);

                try {
                    // Show loading state
                    addToCartBtn.disabled = true;
                    addToCartBtn.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Adding...
                    `;

                    // Make AJAX request to add to cart
                    // Setup the AJAX request with CSRF and Authorization header
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        }
                    });


                    $.ajax({
                        url: "{{ url('/cart') }}",
                        type: 'POST',
                        dataType: "json",  // Expecting JSON response
                        contentType: "application/json",  // Set the Content-Type to application/json
                        data: JSON.stringify({
                            variant_id: currentVariant.id,
                            quantity: quantity
                        }),  // Send data as a JSON string
                        success: function(result) {
                            try {
                                // Process the result here
                                if (result.cart_count) {
                                    // Update the cart counter in the navbar
                                    const cartCounter = document.querySelector('.cart-counter');
                                    if (cartCounter) {
                                        cartCounter.textContent = result.cart_count;
                                        cartCounter.classList.remove('hidden');
                                    }
                                }

                                // Show success toast notification
                                showNotification('success', 'Item added to cart successfully!');

                                // Optional: Show mini cart preview
                                showMiniCartPreview(currentVariant, quantity);

                                // Update cart counter in navbar if present
                                const cartCounter = document.querySelector('.cart-counter');
                                if (cartCounter) {
                                    cartCounter.textContent = result.cart_count;
                                    cartCounter.classList.remove('hidden');
                                }

                                // Show success toast notification
                                showNotification('success', 'Item added to cart successfully!');

                                // Optional: Show mini cart preview
                                showMiniCartPreview(currentVariant, quantity);
                            } catch (error) {
                                console.error('Error parsing JSON:', error);
                                showNotification('error', 'Failed to parse the response.');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Request failed with status:', response.status);
                            showNotification('error', 'Failed to add item to cart.');
                        }
                    });
                } catch (error) {
                    console.error('Error adding to cart:', error);

                    // Show error notification
                    showNotification('error', error.message || 'Something went wrong');
                } finally {
                    // Reset button state
                    addToCartBtn.disabled = false;
                    addToCartBtn.innerHTML = `
                        <svg class="w-5 h-5 -ms-2 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 4h1.5L8 16m0 0h8m-8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm.75-3H7.5M11 7H6.312M17 4v6m-3-3h6" />
                        </svg>
                        Add to cart
                    `;
                }
            });

            // Function to show notification toast
            function showNotification(type, message) {
                // Remove any existing notifications
                const existingNotifications = document.querySelectorAll('.notification-toast');
                existingNotifications.forEach(toast => toast.remove());

                // Create notification element
                const notification = document.createElement('div');
                notification.className = `notification-toast fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 flex items-center space-x-2 transform transition-transform duration-300 ease-in-out ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;

                // Set icon based on type
                const icon = type === 'success' ?
                    '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>' :
                    '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';

                notification.innerHTML = `
            <div class="flex items-center">
                ${icon}
                <span class="ml-2">${message}</span>
            </div>
            <button class="ml-4 text-white hover:text-gray-200 focus:outline-none" onclick="this.parentElement.remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;

                // Add to body
                document.body.appendChild(notification);

                // Auto remove after 3 seconds
                setTimeout(() => {
                    notification.classList.add('translate-x-full');
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            }

            // Optional: Show mini cart preview after adding item
            function showMiniCartPreview(variant, quantity) {
                // Create mini cart preview element
                const miniCart = document.createElement('div');
                miniCart.className =
                    'fixed top-4 right-4 bg-white rounded-lg shadow-lg z-40 p-4 w-72 transform transition-transform duration-300 ease-in-out';
                miniCart.style.marginTop = '60px'; // Position below notification

                const productName = document.querySelector('h1').textContent.trim();
                const price = new Intl.NumberFormat('id-ID').format(variant.price);

                miniCart.innerHTML = `
            <div class="flex justify-between items-center mb-2">
                <h3 class="font-medium text-gray-900">Added to cart</h3>
                <button class="text-gray-500 hover:text-gray-700" onclick="this.parentElement.parentElement.remove()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="flex items-center space-x-4">
                <img class="w-16 h-16 object-cover rounded" src="${variant.image_url || document.getElementById('main-product-image').src}" alt="${productName}" />
                <div>
                    <p class="text-sm font-medium text-gray-900">${productName}</p>
                    <p class="text-sm text-gray-500">Qty: ${quantity}</p>
                    <p class="text-sm font-semibold text-gray-900">Rp ${price}</p>
                </div>
            </div>
            <div class="mt-4 flex justify-between">
                <a href="/store" class="text-primary-600 hover:text-primary-800 text-sm font-medium">
                    Continue Shopping
                </a>
                <a href="/shopping-cart" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium py-1 px-3 rounded">
                    View Cart
                </a>
            </div>
        `;

                // Add to body
                document.body.appendChild(miniCart);

                // Auto remove after 5 seconds
                setTimeout(() => {
                    miniCart.classList.add('translate-x-full');
                    setTimeout(() => miniCart.remove(), 300);
                }, 5000);
            }
        });

        // Image gallery management
        function updateMainImage(thumbnail) {
            // Update main image src
            const mainImage = document.getElementById('main-product-image');
            mainImage.src = thumbnail.dataset.imageUrl;

            // Update active thumbnail styling
            document.querySelectorAll('.product-thumbnail').forEach(thumb => {
                thumb.classList.remove('ring-2', 'ring-primary-500');
            });
            thumbnail.classList.add('ring-2', 'ring-primary-500');
        }

        // Read more functionality for product description
        document.addEventListener('DOMContentLoaded', function() {
            const readMoreBtn = document.querySelector('.text-[#c6303e]');
            const descriptionContainer = document.querySelector('.line-clamp-\\[13\\]');

            if (readMoreBtn && descriptionContainer) {
                readMoreBtn.addEventListener('click', function() {
                    if (descriptionContainer.classList.contains('line-clamp-[13]')) {
                        // Expand
                        descriptionContainer.classList.remove('line-clamp-[13]');
                        readMoreBtn.textContent = 'Read less';
                    } else {
                        // Collapse
                        descriptionContainer.classList.add('line-clamp-[13]');
                        readMoreBtn.textContent = 'Read more';
                    }
                });
            }
        });
    </script>
</x-layouts.marketing>
