<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

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

    $response = $this->getJson('/api/checkout/search-destination?search=banjar');

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

    $response = $this->postJson('/api/checkout/shipping-cost', [
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

    $response = $this->getJson('/api/checkout/search-destination?search=banjar');

    $response->assertStatus(500);
    $response->assertJsonPath('error', 'RajaOngkir API key belum dikonfigurasi.');

    Http::assertNothingSent();
});
