<?php

use App\Models\{Cart, CartItem, Product, ProductCategory, ProductImage, User, Variant};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

function createCartWithExternalImage(string $imageUrl): User
{
    $user = User::create([
        'name' => 'Cart Image Tester',
        'email' => 'cart-image-' . uniqid() . '@khi.org',
        'password' => Hash::make('secret123'),
        'username' => 'cart-image-' . uniqid(),
        'verified' => 1,
    ]);

    $category = ProductCategory::create([
        'name' => 'Test Category',
        'status' => 'active',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Test Product',
        'slug' => 'test-product-' . uniqid(),
        'description' => 'Test product description',
        'status' => 'active',
    ]);

    $variant = Variant::create([
        'product_id' => $product->id,
        'sku' => 'TEST-' . uniqid(),
        'price' => 125000,
        'stock_quantity' => 3,
        'image_url' => $imageUrl,
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

    return $user;
}

function createCartWithProductImage(string $imagePath): User
{
    $user = User::create([
        'name' => 'Cart Product Image Tester',
        'email' => 'cart-product-image-' . uniqid() . '@khi.org',
        'password' => Hash::make('secret123'),
        'username' => 'cart-product-image-' . uniqid(),
        'verified' => 1,
    ]);

    $category = ProductCategory::create([
        'name' => 'Test Category ' . uniqid(),
        'status' => 'active',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Test Product ' . uniqid(),
        'slug' => 'test-product-' . uniqid(),
        'description' => 'Test product description',
        'status' => 'active',
    ]);

    ProductImage::create([
        'product_id' => $product->id,
        'image_url' => $imagePath,
        'sort_order' => 0,
    ]);

    $variant = Variant::create([
        'product_id' => $product->id,
        'sku' => 'TEST-' . uniqid(),
        'price' => 125000,
        'stock_quantity' => 3,
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

    return $user;
}

dataset('cartPages', [
    'shopping cart' => '/shopping-cart',
    'checkout' => '/checkout',
]);

it('renders external variant image urls without forcing storage paths', function (string $path): void {
    $imageUrl = 'https://example.com/cart-item.jpg';
    $user = createCartWithExternalImage($imageUrl);

    $response = $this->actingAs($user)->get($path);

    $response->assertOk();
    $response->assertSee('src="' . $imageUrl . '"', false);
    $response->assertDontSee('/storage/' . $imageUrl, false);
})->with('cartPages');

it('falls back to the product image when the variant image is missing', function (string $path): void {
    $imagePath = 'products/images/' . uniqid() . '.png';
    $user = createCartWithProductImage($imagePath);

    $response = $this->actingAs($user)->get($path);

    $response->assertOk();
    $response->assertSee('src="' . Storage::url($imagePath) . '"', false);
    $response->assertDontSee('src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/imac-front.svg"', false);
})->with('cartPages');
