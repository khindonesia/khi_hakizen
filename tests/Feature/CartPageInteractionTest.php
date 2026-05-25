<?php

use App\Models\{Cart, CartItem, Product, ProductCategory, ProductImage, User, Variant};
use Illuminate\Support\Facades\Hash;

it('renders cart action buttons that call the global cart helper', function (): void {
    $user = User::create([
        'name' => 'Cart UI Tester',
        'email' => 'cart-ui-' . uniqid() . '@khi.org',
        'password' => Hash::make('secret123'),
        'username' => 'cart-ui-' . uniqid(),
        'verified' => 1,
    ]);

    $category = ProductCategory::create([
        'name' => 'Cart UI Category ' . uniqid(),
        'status' => 'active',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Cart UI Product ' . uniqid(),
        'slug' => 'cart-ui-product-' . uniqid(),
        'description' => 'Cart UI description',
        'status' => 'active',
    ]);

    ProductImage::create([
        'product_id' => $product->id,
        'image_url' => 'products/images/' . uniqid() . '.png',
        'sort_order' => 0,
    ]);

    $variant = Variant::create([
        'product_id' => $product->id,
        'sku' => 'UI-' . uniqid(),
        'price' => 150000,
        'stock_quantity' => 2,
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
        'quantity' => 1,
        'price' => $variant->price,
    ]);

    $response = $this->actingAs($user)->get('/shopping-cart');

    $response->assertOk();
    $response->assertSee('onclick="window.KhiCart.removeItem(', false);
    $response->assertSee('onclick="window.KhiCart.updateQuantity(', false);
});
