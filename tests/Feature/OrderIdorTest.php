<?php

use App\Models\{Order, User, UserAddress};
use Livewire\Volt\Volt;

if (!function_exists('createOrderIdorUser')) {
    function createOrderIdorUser(string $prefix = 'checkout'): User
    {
        return User::create([
            'name' => 'Checkout Tester',
            'email' => $prefix . '-' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
            'username' => $prefix . '-' . uniqid(),
            'verified' => 1,
        ]);
    }
}

if (!function_exists('createOrderIdorAddress')) {
    function createOrderIdorAddress(User $user): UserAddress
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

it('requires auth to print an order invoice', function (): void {
    $response = $this->get('/orders/1/print-invoice');

    $response->assertRedirect('/login');
});

it('does not print another users invoice', function (): void {
    $owner = createOrderIdorUser('invoice-owner');
    $attacker = createOrderIdorUser('invoice-attacker');
    $address = createOrderIdorAddress($owner);

    $order = Order::create([
        'user_id' => $owner->id,
        'address_id' => $address->id,
        'courier' => 'jne',
        'service' => 'REG',
        'subtotal' => 100000,
        'shipping_fee' => 18000,
        'total_amount' => 118000,
        'payment_status' => 'paid',
        'status' => 'processing',
        'external_id' => 'order-invoice-' . uniqid(),
        'invoice_id' => 'inv-idor',
    ]);

    $response = $this->actingAs($attacker)->get("/orders/{$order->id}/print-invoice");

    $response->assertNotFound();
});

it('does not show another users order detail page', function (): void {
    $owner = createOrderIdorUser('order-owner');
    $attacker = createOrderIdorUser('order-attacker');
    $address = createOrderIdorAddress($owner);

    $order = Order::create([
        'user_id' => $owner->id,
        'address_id' => $address->id,
        'courier' => 'jne',
        'service' => 'REG',
        'subtotal' => 100000,
        'shipping_fee' => 18000,
        'total_amount' => 118000,
        'payment_status' => 'paid',
        'status' => 'processing',
        'external_id' => 'order-detail-' . uniqid(),
        'invoice_id' => 'inv-detail-idor',
    ]);

    $this->actingAs($attacker);

    expect(fn () => Volt::test('orders.view', ['id' => $order->id]))
        ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

it('allows admin to print another users invoice', function (): void {
    $owner = createOrderIdorUser('invoice-owner-admin-test');
    $admin = createOrderIdorUser('invoice-admin-test');
    
    $adminRole = \Spatie\Permission\Models\Role::firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);
    
    $admin->assignRole($adminRole);
    
    $address = createOrderIdorAddress($owner);

    $order = Order::create([
        'user_id' => $owner->id,
        'address_id' => $address->id,
        'courier' => 'jne',
        'service' => 'REG',
        'subtotal' => 100000,
        'shipping_fee' => 18000,
        'total_amount' => 118000,
        'payment_status' => 'paid',
        'status' => 'processing',
        'external_id' => 'order-invoice-admin-' . uniqid(),
        'invoice_id' => 'inv-admin-test',
    ]);

    $response = $this->actingAs($admin)->get("/orders/{$order->id}/print-invoice");

    $response->assertStatus(200);
    $response->assertSee('Invoice');
    $response->assertSee('inv-admin-test');
});

