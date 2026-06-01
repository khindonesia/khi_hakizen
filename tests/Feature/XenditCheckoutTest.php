<?php

use App\Models\User;
use App\Models\UserAddress;
use App\Models\{Cart, CartItem, Order, Product, ProductCategory, Variant};
use App\Services\XenditInvoiceGateway;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use function Pest\Laravel\actingAs;

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

if (!function_exists('createCheckoutCart')) {
    function createCheckoutCart(User $user, int $quantity = 2, int $stock = 3): Variant
    {
        $category = ProductCategory::create([
            'name' => 'Checkout Category ' . uniqid(),
            'status' => 'active',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Checkout Product ' . uniqid(),
            'slug' => 'checkout-product-' . uniqid(),
            'description' => 'Checkout test product',
            'status' => 'active',
        ]);

        $variant = Variant::create([
            'product_id' => $product->id,
            'sku' => 'CHECKOUT-' . uniqid(),
            'price' => 100000,
            'stock_quantity' => $stock,
            'image_url' => null,
            'is_default' => true,
            'status' => 'active',
        ]);

        $cart = Cart::create([
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => $quantity,
            'price' => $variant->price,
        ]);

        return $variant;
    }
}

function fakeCheckoutDependencies(int $shippingCost = 18000): void
{
    config()->set('services.xendit.secret_key', 'xnd_test_key');
    config()->set('services.rajaongkir.api_key', 'rajaongkir-test-key');

    Http::fake([
        '*/destination/domestic-destination*' => Http::response([
            'data' => [
                [
                    'id' => '17693',
                    'label' => 'Jagakarsa, Jakarta Selatan 12630',
                    'zip_code' => '12630',
                ],
            ],
        ]),
        '*/calculate/domestic-cost' => Http::response([
            'data' => [
                [
                    'service' => 'REG',
                    'description' => 'Regular',
                    'cost' => $shippingCost,
                    'etd' => '2-3',
                ],
            ],
        ]),
    ]);

    app()->bind(XenditInvoiceGateway::class, fn () => new class extends XenditInvoiceGateway {
        public function __construct()
        {
        }

        /**
         * @param array<int, array{name: string, quantity: int, price: int|float}> $items
         * @return array{id: string, invoice_url: string}
         */
        public function createInvoice(Order $order, User $user, array $items): array
        {
            return [
                'id' => 'inv-test-' . $order->id,
                'invoice_url' => 'https://pay.test/invoice/' . $order->id,
            ];
        }

        /**
         * Mock event invoice creation
         */
        public function createEventInvoice(
            string $externalId,
            float $amount,
            string $description,
            User $user,
            array $items,
            string $successRedirectUrl,
            string $failureRedirectUrl
        ): array {
            return [
                'id' => 'evt-inv-test-' . $externalId,
                'invoice_url' => 'https://pay.test/event-invoice/' . $externalId,
            ];
        }
    });
}

it('returns a clear error when the xendit api key is missing', function (): void {
    config()->set('services.xendit.secret_key', null);

    $user = createCheckoutUser('missing-key');

    actingAs($user);

    $address = createCheckoutAddress($user);

    $response = $this->postJson('/api/checkout/create-invoice', [
        'address_id' => $address->id,
        'courier_code' => 'jne',
        'service_code' => 'REG',
    ]);

    $response->assertStatus(500);
    $response->assertJsonPath('status', 'error');
    $response->assertJsonPath('message', 'Xendit API key belum dikonfigurasi. Set XENDIT_SECRET_KEY di .env lalu jalankan php artisan config:clear.');
});

it('requires authentication before creating a checkout invoice', function (): void {
    $response = $this->postJson('/api/checkout/create-invoice', [
        'address_id' => 1,
        'courier_code' => 'jne',
        'service_code' => 'REG',
    ]);

    $response->assertUnauthorized();
});

it('rejects checkout when address belongs to another user', function (): void {
    fakeCheckoutDependencies();

    $user = createCheckoutUser('owner');
    $otherUser = createCheckoutUser('other');
    $address = createCheckoutAddress($otherUser);
    createCheckoutCart($user);

    $response = $this->actingAs($user)->postJson('/api/checkout/create-invoice', [
        'address_id' => $address->id,
        'courier_code' => 'jne',
        'service_code' => 'REG',
    ]);

    $response->assertNotFound();
});

it('recalculates shipping server side and decrements locked stock during checkout', function (): void {
    fakeCheckoutDependencies(shippingCost: 18000);

    $user = createCheckoutUser('zero-trust');
    $address = createCheckoutAddress($user);
    $variant = createCheckoutCart($user, quantity: 2, stock: 3);

    $response = $this->actingAs($user)->postJson('/api/checkout/create-invoice', [
        'address_id' => $address->id,
        'courier_code' => 'jne',
        'service_code' => 'REG',
        'shipping_fee' => 1,
    ]);

    $response->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.invoice_url', 'https://pay.test/invoice/1');

    expect($variant->refresh()->stock_quantity)->toBe(1);

    $order = Order::query()->where('user_id', $user->id)->firstOrFail();

    expect((int) $order->subtotal)->toBe(200000)
        ->and((int) $order->shipping_fee)->toBe(18000)
        ->and((int) $order->total_amount)->toBe(218000);

    expect(Cart::query()->where('user_id', $user->id)->value('status'))->toBe('converted');
});

it('rejects xendit webhook without a verifier', function (): void {
    config()->set('services.xendit.webhook_secret', 'webhook-secret');

    $response = $this->postJson('/api/xendit/callback', [
        'external_id' => 'order-missing-token',
        'status' => 'PAID',
    ]);

    $response->assertUnauthorized();
});

it('rejects xendit webhook with an invalid callback token', function (): void {
    config()->set('services.xendit.webhook_secret', 'webhook-secret');

    $response = $this->withHeader('x-callback-token', 'wrong-secret')
        ->postJson('/api/xendit/callback', [
            'external_id' => 'order-wrong-token',
            'status' => 'PAID',
        ]);

    $response->assertUnauthorized();
});

it('marks a merchandise order paid from a verified xendit webhook and ignores duplicate paid callbacks', function (): void {
    config()->set('services.xendit.webhook_secret', 'webhook-secret');

    $user = createCheckoutUser('webhook-order');
    $address = createCheckoutAddress($user);

    $order = Order::create([
        'user_id' => $user->id,
        'address_id' => $address->id,
        'courier' => 'jne',
        'service' => 'REG',
        'subtotal' => 100000,
        'shipping_fee' => 18000,
        'total_amount' => 118000,
        'payment_status' => 'pending',
        'status' => 'pending',
        'external_id' => 'order-webhook-' . uniqid(),
    ]);

    $payload = [
        'external_id' => $order->external_id,
        'status' => 'PAID',
    ];

    $response = $this->withHeader('x-callback-token', 'webhook-secret')
        ->postJson('/api/xendit/callback', $payload);

    $response->assertOk()->assertJsonPath('status', 'success');

    expect($order->refresh()->payment_status)->toBe('paid')
        ->and($order->status)->toBe('processing');

    $duplicate = $this->withHeader('x-callback-token', 'webhook-secret')
        ->postJson('/api/xendit/callback', $payload);

    $duplicate->assertOk()->assertJsonPath('status', 'success');

    expect($order->refresh()->payment_status)->toBe('paid')
        ->and($order->status)->toBe('processing');
});

it('does not downgrade a paid merchandise order when an expired webhook arrives', function (): void {
    config()->set('services.xendit.webhook_secret', 'webhook-secret');

    $user = createCheckoutUser('webhook-expired');
    $address = createCheckoutAddress($user);

    $order = Order::create([
        'user_id' => $user->id,
        'address_id' => $address->id,
        'courier' => 'jne',
        'service' => 'REG',
        'subtotal' => 100000,
        'shipping_fee' => 18000,
        'total_amount' => 118000,
        'payment_status' => 'paid',
        'status' => 'processing',
        'external_id' => 'order-webhook-expired-' . uniqid(),
    ]);

    $response = $this->withHeader('x-callback-token', 'webhook-secret')
        ->postJson('/api/xendit/callback', [
            'external_id' => $order->external_id,
            'status' => 'EXPIRED',
        ]);

    $response->assertOk()->assertJsonPath('status', 'success');

    expect($order->refresh()->payment_status)->toBe('paid')
        ->and($order->status)->toBe('processing');
});

it('accepts a valid hmac signed xendit webhook', function (): void {
    config()->set('services.xendit.webhook_secret', 'webhook-secret');

    $user = createCheckoutUser('webhook-hmac');
    $address = createCheckoutAddress($user);

    $order = Order::create([
        'user_id' => $user->id,
        'address_id' => $address->id,
        'courier' => 'jne',
        'service' => 'REG',
        'subtotal' => 100000,
        'shipping_fee' => 18000,
        'total_amount' => 118000,
        'payment_status' => 'pending',
        'status' => 'pending',
        'external_id' => 'order-webhook-hmac-' . uniqid(),
    ]);

    $payload = [
        'external_id' => $order->external_id,
        'status' => 'PAID',
    ];
    $rawBody = json_encode($payload);
    $timestamp = (string) now()->timestamp;
    $signature = hash_hmac('sha256', "{$timestamp}.{$rawBody}", 'webhook-secret');

    $response = $this->call('POST', '/api/xendit/callback', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X_CALLBACK_TIMESTAMP' => $timestamp,
        'HTTP_X_CALLBACK_SIGNATURE' => 'sha256=' . $signature,
    ], $rawBody);

    $response->assertOk()->assertJsonPath('status', 'success');

    expect($order->refresh()->payment_status)->toBe('paid')
        ->and($order->status)->toBe('processing');
});

it('enforces event booking capacity limits', function (): void {
    fakeCheckoutDependencies();

    $author = createCheckoutUser('evt-author');
    $user1 = createCheckoutUser('evt-u1');
    $user2 = createCheckoutUser('evt-u2');

    $event = \App\Models\Event::create([
        'author_id' => $author->id,
        'title' => 'Limited Capacity Event ' . uniqid(),
        'slug' => 'limited-event-' . uniqid(),
        'body' => 'Event description',
        'status' => 'PUBLISHED',
        'type' => 'PAID',
        'price' => 50000,
        'capacity' => 1,
        'start_datetime' => now()->addDays(2),
        'end_datetime' => now()->addDays(2)->addHours(2),
    ]);

    // Book first ticket (succeeds)
    $response1 = $this->actingAs($user1)->postJson('/api/events/checkout/create-invoice', [
        'event_id' => $event->id,
        'phone_number' => '0811111111',
    ]);
    $response1->assertOk()->assertJsonPath('status', 'success');

    // Verify first registration is pending
    expect(DB::table('event_user')->where('event_id', $event->id)->count())->toBe(1);

    // Book second ticket (fails due to full capacity)
    $response2 = $this->actingAs($user2)->postJson('/api/events/checkout/create-invoice', [
        'event_id' => $event->id,
        'phone_number' => '0822222222',
    ]);
    $response2->assertStatus(422)
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('message', 'Maaf, kuota pendaftaran tiket untuk event ini sudah penuh.');
});

it('restores merchandise stock when an order expires', function (): void {
    config()->set('services.xendit.webhook_secret', 'webhook-secret');

    $user = createCheckoutUser('restorer');
    $address = createCheckoutAddress($user);

    // Create a product and variant
    $category = ProductCategory::create([
        'name' => 'Restock Category ' . uniqid(),
        'status' => 'active',
    ]);
    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Restock Product ' . uniqid(),
        'slug' => 'restock-product-' . uniqid(),
        'description' => 'Test product description',
        'status' => 'active',
    ]);
    $variant = Variant::create([
        'product_id' => $product->id,
        'sku' => 'RESTOCK-' . uniqid(),
        'price' => 50000,
        'stock_quantity' => 5,
        'is_default' => true,
        'status' => 'active',
    ]);

    // Create an order that is currently pending, with 2 items checked out (so stock was reduced from 5 to 3)
    $order = Order::create([
        'user_id' => $user->id,
        'address_id' => $address->id,
        'courier' => 'jne',
        'service' => 'REG',
        'subtotal' => 100000,
        'shipping_fee' => 18000,
        'total_amount' => 118000,
        'payment_status' => 'pending',
        'status' => 'pending',
        'external_id' => 'order-webhook-expired-stock-' . uniqid(),
    ]);

    \App\Models\OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'variant_id' => $variant->id,
        'quantity' => 2,
        'price' => $variant->price,
        'total_price' => 100000,
    ]);

    // Update variant stock to represent stock after checkout (5 - 2 = 3)
    $variant->updateStock(3);

    // Send EXPIRED webhook callback
    $response = $this->withHeader('x-callback-token', 'webhook-secret')
        ->postJson('/api/xendit/callback', [
            'external_id' => $order->external_id,
            'status' => 'EXPIRED',
        ]);

    $response->assertOk()->assertJsonPath('status', 'success');

    // Expect order to be cancelled and variant stock restored to 5!
    expect($order->refresh()->status)->toBe('cancelled')
        ->and($order->payment_status)->toBe('expired')
        ->and($variant->refresh()->stock_quantity)->toBe(5);
});

it('reuses existing pending invoices during event bookings instead of overwriting', function (): void {
    fakeCheckoutDependencies();

    $author = createCheckoutUser('evt-author2');
    $user = createCheckoutUser('evt-user-reuse');

    $event = \App\Models\Event::create([
        'author_id' => $author->id,
        'title' => 'Reusable Event ' . uniqid(),
        'slug' => 'reusable-event-' . uniqid(),
        'body' => 'Event description',
        'status' => 'PUBLISHED',
        'type' => 'PAID',
        'price' => 75000,
        'capacity' => 10,
        'start_datetime' => now()->addDays(2),
        'end_datetime' => now()->addDays(2)->addHours(2),
    ]);

    // Book first ticket (generates new invoice)
    $response1 = $this->actingAs($user)->postJson('/api/events/checkout/create-invoice', [
        'event_id' => $event->id,
        'phone_number' => '0877777777',
    ]);
    $response1->assertOk()->assertJsonPath('status', 'success');
    
    $url1 = $response1->json('data.invoice_url');
    expect($url1)->toContain('https://pay.test/event-invoice/');

    // Get the generated external_id from database
    $reg1 = DB::table('event_user')->where('event_id', $event->id)->where('user_id', $user->id)->first();
    $extId1 = $reg1->external_id;

    // Book second time (should reuse pending registration and invoice URL)
    $response2 = $this->actingAs($user)->postJson('/api/events/checkout/create-invoice', [
        'event_id' => $event->id,
        'phone_number' => '0877777777',
    ]);
    $response2->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('message', 'Menampilkan tagihan pembayaran yang sudah ada.')
        ->assertJsonPath('data.invoice_url', $url1);

    // Verify external_id was NOT overwritten in database
    $reg2 = DB::table('event_user')->where('event_id', $event->id)->where('user_id', $user->id)->first();
    expect($reg2->external_id)->toBe($extId1);
});
