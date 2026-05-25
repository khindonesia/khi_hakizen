<?php

use App\Models\{Order, User};
use Livewire\Volt\Volt;

it('requires auth to print an order invoice', function (): void {
    $response = $this->get('/orders/1/print-invoice');

    $response->assertRedirect('/login');
});

it('does not print another users invoice', function (): void {
    $owner = createCheckoutUser('invoice-owner');
    $attacker = createCheckoutUser('invoice-attacker');
    $address = createCheckoutAddress($owner);

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
    $owner = createCheckoutUser('order-owner');
    $attacker = createCheckoutUser('order-attacker');
    $address = createCheckoutAddress($owner);

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
