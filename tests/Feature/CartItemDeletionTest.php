<?php

use App\Models\{Cart, CartItem, Product, ProductCategory, User, Variant};
use Illuminate\Support\Facades\Hash;

function createCartDeletionUser(string $prefix = 'cart-user'): User
{
    return User::create([
        'name' => 'Cart User',
        'email' => $prefix . '-' . uniqid() . '@khi.org',
        'password' => Hash::make('secret123'),
        'username' => $prefix . '-' . uniqid(),
        'verified' => 1,
    ]);
}

function createCartDeletionItem(User $user, int $quantity = 2, int $stock = 5): CartItem
{
    $category = ProductCategory::create([
        'name' => 'Delete Test Category ' . uniqid(),
        'status' => 'active',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Delete Test Product ' . uniqid(),
        'slug' => 'delete-test-product-' . uniqid(),
        'description' => 'Delete test description',
        'status' => 'active',
    ]);

    $variant = Variant::create([
        'product_id' => $product->id,
        'sku' => 'DELETE-' . uniqid(),
        'price' => 90000,
        'stock_quantity' => $stock,
        'image_url' => null,
        'is_default' => true,
        'status' => 'active',
    ]);

    $cart = Cart::create([
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    return CartItem::create([
        'cart_id' => $cart->id,
        'variant_id' => $variant->id,
        'quantity' => $quantity,
        'price' => $variant->price,
    ]);
}

it('deletes cart items and returns the updated subtotal', function (): void {
    $user = createCartDeletionUser('cart-delete');
    $cartItem = createCartDeletionItem($user);

    $response = $this->actingAs($user)->deleteJson("/cart/items/{$cartItem->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Cart item removed successfully',
        ])
        ->assertJsonPath('data.cartSubtotal', 0);

    $this->assertDatabaseMissing('cart_items', [
        'id' => $cartItem->id,
    ]);
});

it('does not delete another users cart item', function (): void {
    $owner = createCartDeletionUser('cart-owner');
    $attacker = createCartDeletionUser('cart-attacker');
    $cartItem = createCartDeletionItem($owner);

    $response = $this->actingAs($attacker)->deleteJson("/cart/items/{$cartItem->id}");

    $response->assertNotFound();

    $this->assertDatabaseHas('cart_items', [
        'id' => $cartItem->id,
    ]);
});

it('does not update another users cart item', function (): void {
    $owner = createCartDeletionUser('cart-update-owner');
    $attacker = createCartDeletionUser('cart-update-attacker');
    $cartItem = createCartDeletionItem($owner);

    $response = $this->actingAs($attacker)->patchJson("/cart/items/{$cartItem->id}", [
        'quantity' => 1,
    ]);

    $response->assertNotFound();

    expect($cartItem->refresh()->quantity)->toBe(2);
});

it('rate limits cart mutations', function (): void {
    $user = createCartDeletionUser('cart-throttle');

    for ($attempt = 0; $attempt < 30; $attempt++) {
        $this->actingAs($user)->postJson('/cart', [
            'variant_id' => 999999,
            'quantity' => 1,
        ])->assertStatus(422);
    }

    $this->actingAs($user)->postJson('/cart', [
        'variant_id' => 999999,
        'quantity' => 1,
    ])->assertTooManyRequests();
});
