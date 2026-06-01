<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, UserAddress, Product, ProductCategory, Variant, Cart, CartItem};

beforeEach(function (): void {
    config()->set('services.rajaongkir.base_url', 'https://rajaongkir.test/api/v1');
    config()->set('services.rajaongkir.api_key', 'test-rajaongkir-key');
    config()->set('services.rajaongkir.origin_id', 17693);
    config()->set('services.rajaongkir.price_type', 'lowest');
});

it('searches destinations using the configured rajaongkir key', function (): void {
    Http::fake([
        'https://rajaongkir.test/api/v1/destination/domestic-destination*' => Http::response([
            'data' => [
                [
                    'id' => 12345,
                    'label' => 'Kota Banjar, Jawa Barat',
                ],
            ],
            'meta' => [
                'status' => 'success',
            ],
        ]),
    ]);

    $response = $this->actingAs(createCheckoutUser('search-dest'))->getJson('/api/checkout/search-destination?search=banjar');

    $response->assertOk();
    $response->assertJsonPath('data.0.id', 12345);

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://rajaongkir.test/api/v1/destination/domestic-destination?search=banjar&limit=5&offset=0'
            && $request->hasHeader('key', 'test-rajaongkir-key');
    });
});

it('fetches shipping cost using the configured rajaongkir key', function (): void {
    Http::fake([
        'https://rajaongkir.test/api/v1/calculate/domestic-cost' => Http::response([
            'meta' => [
                'message' => 'Success Calculate Domestic Shipping cost',
                'status' => 'success',
            ],
            'data' => [
                [
                    'service' => 'REG',
                    'cost' => 18000,
                    'etd' => '2-3 hari',
                ],
            ],
        ]),
    ]);

    $response = $this->actingAs(createCheckoutUser('ship-cost'))->postJson('/api/checkout/shipping-cost', [
        'courier' => 'jne',
        'origin' => 17693,
        'destination' => 12345,
        'weight' => 1000,
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.0.service', 'REG');

    Http::assertSent(function (Request $request): bool {
        $data = $request->data();

        return $request->url() === 'https://rajaongkir.test/api/v1/calculate/domestic-cost'
            && $request->method() === 'POST'
            && $request->hasHeader('key', 'test-rajaongkir-key')
            && ($data['origin'] ?? null) === 17693
            && ($data['destination'] ?? null) === 12345
            && ($data['weight'] ?? null) === 1000
            && ($data['courier'] ?? null) === 'jne'
            && ($data['price'] ?? null) === 'lowest';
    });
});

it('returns a clear error when the rajaongkir key is missing', function (): void {
    config()->set('services.rajaongkir.api_key', null);
    Http::fake();

    $response = $this->actingAs(createCheckoutUser('missing-key'))->getJson('/api/checkout/search-destination?search=banjar');

    $response->assertStatus(500);
    $response->assertJsonPath('error', 'RajaOngkir API key belum dikonfigurasi.');

    Http::assertNothingSent();
});

if (!function_exists('createCheckoutUser')) {
    function createCheckoutUser(string $prefix = 'checkout'): User
    {
        return User::create([
            'name' => 'Checkout Tester',
            'email' => $prefix . '-' . uniqid() . '@example.com',
            'password' => Hash::make('secret123'),
            'username' => $prefix . '-' . uniqid(),
            'verified' => 1,
        ]);
    }
}

if (!function_exists('createCheckoutAddress')) {
    function createCheckoutAddress(User $user): UserAddress
    {
        return UserAddress::create([
            'user_id' => $user->id,
            'address_line' => 'Jl. Test No. 1',
            'city' => 'KOTA JAKARTA SELATAN',
            'district' => 'JAGAKARSA',
            'village' => 'SRENGSENG SAWAH',
            'state' => 'DKI JAKARTA',
            'postal_code' => '12630',
            'country' => 'Indonesia',
            'is_primary' => true,
            'phone_number' => '08123456789',
            'address_type' => 'Home',
        ]);
    }
}

it('calculates weight dynamically from cart product weight', function (): void {
    $user = createCheckoutUser('dynamic-weight');
    $address = createCheckoutAddress($user);
    
    // Create product category
    $category = ProductCategory::create([
        'name' => 'Weight Category',
        'status' => 'active',
    ]);
    
    // Create product with a specific custom weight (e.g. 500 grams)
    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Custom Weight Product',
        'slug' => 'custom-weight-product',
        'description' => 'Test product',
        'weight' => 500, // custom weight
        'status' => 'active',
    ]);
    
    $variant = Variant::create([
        'product_id' => $product->id,
        'sku' => 'SKU-WEIGHT-500',
        'price' => 50000,
        'stock_quantity' => 10,
        'is_default' => true,
        'status' => 'active',
    ]);
    
    // Create cart with 3 items of this product (total weight: 3 * 500 = 1500 grams)
    $cart = Cart::create([
        'user_id' => $user->id,
        'status' => 'active',
    ]);
    
    CartItem::create([
        'cart_id' => $cart->id,
        'variant_id' => $variant->id,
        'quantity' => 3,
        'price' => $variant->price,
    ]);

    Http::fake([
        'https://rajaongkir.test/api/v1/calculate/domestic-cost' => Http::response([
            'data' => [
                [
                    'service' => 'REG',
                    'cost' => 15000,
                    'etd' => '2-3 hari',
                ],
            ],
        ]),
    ]);

    // Request shipping cost while authenticated as user
    $response = $this->actingAs($user)->postJson('/api/checkout/shipping-cost', [
        'courier' => 'jne',
        'origin' => 17693,
        'destination' => 12345,
    ]);

    $response->assertOk();

    // Verify RajaOngkir was called with the calculated dynamic weight of 1500g
    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://rajaongkir.test/api/v1/calculate/domestic-cost'
            && ($request->data()['weight'] ?? null) === 1500;
    });
});
