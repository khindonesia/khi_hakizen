<?php

use App\Models\{Cart, CartItem, Product, User};
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\get;

dataset('marketingNavigationPages', [
    'historia-news' => '/historia-news',
    'privacy-policy' => '/privacy-policy',
    'terms-of-service' => '/terms-of-service',
]);

dataset('folioRoutesPages', [
    'changelog list' => '/changelog',
    'changelog detail' => '/changelog/1',
    'events' => '/events',
    'library' => '/library',
    'shopping cart' => '/shopping-cart',
    'fullscreen layout' => '/layout/fullscreen',
]);

it('serves marketing navigation pages', function (string $path): void {
    get($path)->assertOk();
})->with('marketingNavigationPages');

it('renders marketing navigation hrefs on home page', function (): void {
    $response = get('/');

    $response->assertOk();
    $response->assertSee(route('historia-news'), false);
    $response->assertSee(route('privacy-policy'), false);
    $response->assertSee(route('terms-of-service'), false);
});

it('hides the join button for authenticated users', function (): void {
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin-test@example.com',
        'password' => Hash::make('password'),
        'verified' => 1,
    ]);

    $response = $this->actingAs($user)->get('/');

    $response->assertOk();
    $response->assertDontSeeHtml('href="' . route('join') . '" wire:navigate class="rounded-full bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500"');
});

it('shows cart item count in header for authenticated users with active cart', function (): void {
    $user = User::create([
        'name' => 'Cart Tester',
        'email' => 'cart-' . uniqid() . '@khi.org',
        'password' => Hash::make('password'),
        'username' => 'cart-' . uniqid(),
        'verified' => 1,
    ]);

    $product = Product::where('slug', 'batavia-1850-map-reprint')->first();
    $variant = $product->defaultVariant ?? $product->variants->first();

    $cart = Cart::create([
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    CartItem::create([
        'cart_id' => $cart->id,
        'variant_id' => $variant->id,
        'quantity' => 2,
        'price' => $variant->price,
    ]);

    $response = $this->actingAs($user)->get('/merchandise/batavia-1850-map-reprint');

    $response->assertOk();
    $response->assertSee('id="cart-count-badge"', false);
    $response->assertSee('>2</span>', false);
});

it('serves folio routes without errors', function (string $path): void {
    get($path)->assertOk();
})->with('folioRoutesPages');

it('generates the fullscreen layout route name', function (): void {
    expect(route('layout.fullscreen'))->toContain('/layout/fullscreen');
});
