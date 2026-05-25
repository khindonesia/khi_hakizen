<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\get;

it('renders the merchandise detail page', function () {
    $user = User::create([
        'name' => 'Test Member',
        'email' => 'test-' . uniqid() . '@khi.org',
        'password' => Hash::make('secret123'),
        'username' => 'test-' . uniqid(),
        'verified' => 1,
    ]);

    $this->actingAs($user)
        ->get('/merchandise/khi-official-t-shirt')
        ->assertOk()
        ->assertSee('KHI Official T-shirt')
        ->assertSee('Add to Cart')
        ->assertSee('x-on:click="addToCart()"', false)
        ->assertSee('x-model.number="selectedVariantId"', false);
});

it('exposes a cart add route for merchandise forms', function () {
    expect(route('shopping-cart.add'))->toBe(url('/cart'));
});
