<?php

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Volt\Volt;
use function Pest\Laravel\actingAs;

function rajaOngkirLocationFixtures(): array
{
    return [
        'destination/province' => [
            'meta' => [
                'message' => 'Success Get Province',
                'code' => 200,
                'status' => 'success',
            ],
            'data' => [
                ['id' => 11, 'name' => 'DKI JAKARTA'],
                ['id' => 12, 'name' => 'BANTEN'],
            ],
        ],
        'destination/city/11' => [
            'meta' => [
                'message' => 'Success Get City By Province ID',
                'code' => 200,
                'status' => 'success',
            ],
            'data' => [
                ['id' => 1360, 'name' => 'KOTA JAKARTA SELATAN', 'zip_code' => '0'],
                ['id' => 1361, 'name' => 'KOTA JAKARTA PUSAT', 'zip_code' => '0'],
            ],
        ],
        'destination/district/1360' => [
            'meta' => [
                'message' => 'Success Get District By City ID',
                'code' => 200,
                'status' => 'success',
            ],
            'data' => [
                ['id' => 5823, 'name' => 'JAGAKARSA', 'zip_code' => '12630'],
                ['id' => 5824, 'name' => 'PASAR MINGGU', 'zip_code' => '12560'],
            ],
        ],
        'destination/sub-district/5823' => [
            'meta' => [
                'message' => 'Success Get Sub District By District ID',
                'code' => 200,
                'status' => 'success',
            ],
            'data' => [
                ['id' => 1001, 'name' => 'SRENGSENG SAWAH', 'zip_code' => '12630'],
                ['id' => 1002, 'name' => 'TANJUNG BARAT', 'zip_code' => '12530'],
            ],
        ],
        'destination/domestic-destination' => [
            'meta' => [
                'message' => 'Success Get Domestic Destinations',
                'code' => 200,
                'status' => 'success',
            ],
            'data' => [
                [
                    'id' => 98765,
                    'label' => 'JAGAKARSA, KOTA JAKARTA SELATAN, DKI JAKARTA, 12630',
                    'province_name' => 'DKI JAKARTA',
                    'city_name' => 'KOTA JAKARTA SELATAN',
                    'district_name' => 'JAGAKARSA',
                    'subdistrict_name' => 'SRENGSENG SAWAH',
                    'zip_code' => '12630',
                ],
            ],
        ],
    ];
}

beforeEach(function (): void {
    config()->set('services.rajaongkir.base_url', 'https://rajaongkir.test/api/v1');
    config()->set('services.rajaongkir.api_key', 'test-rajaongkir-key');
    config()->set('services.rajaongkir.cache_ttl', 60);
    Cache::flush();

    Http::fake(function ($request) {
        $path = ltrim((string) parse_url(str_replace('https://rajaongkir.test/api/v1/', '', $request->url()), PHP_URL_PATH), '/');
        $fixtures = rajaOngkirLocationFixtures();

        if (array_key_exists($path, $fixtures)) {
            return Http::response($fixtures[$path]);
        }

        return Http::response(['data' => [], 'meta' => []]);
    });
});

it('stores a user address using RajaOngkir hierarchy', function (): void {
    $user = User::create([
        'name' => 'Region Tester',
        'email' => 'region-' . uniqid() . '@example.com',
        'password' => bcrypt('secret123'),
    ]);
    actingAs($user);

    Volt::test('user-addresses.create')
        ->set('data.address_line', 'Jl. Raya Contoh No. 123')
        ->set('data.address_type', 'Home')
        ->set('data.province_id', '11')
        ->set('data.province_name', 'DKI JAKARTA')
        ->set('data.city_id', '1360')
        ->set('data.city_name', 'KOTA JAKARTA SELATAN')
        ->set('data.district_id', '5823')
        ->set('data.district_name', 'JAGAKARSA')
        ->set('data.subdistrict_id', '1001')
        ->set('data.subdistrict_name', 'SRENGSENG SAWAH')
        ->set('data.postal_code', '12630')
        ->set('data.phone_number', '08123456789')
        ->set('data.is_primary', true)
        ->call('create')
        ->assertHasNoErrors();

    expect(UserAddress::query()->count())->toBe(1);

    $address = UserAddress::query()->first();

    expect($address)->not->toBeNull();
    expect($address->user_id)->toBe($user->id);
    expect($address->state)->toBe('DKI JAKARTA');
    expect($address->city)->toBe('KOTA JAKARTA SELATAN');
    expect($address->district)->toBe('JAGAKARSA');
    expect($address->village)->toBe('SRENGSENG SAWAH');
    expect($address->postal_code)->toBe('12630');
    expect($address->is_primary)->toBeTrue();
});

it('hydrates edit form state from RajaOngkir data and saves updates', function (): void {
    $user = User::create([
        'name' => 'Region Editor',
        'email' => 'editor-' . uniqid() . '@example.com',
        'password' => bcrypt('secret123'),
    ]);
    actingAs($user);

    $address = UserAddress::create([
        'user_id' => $user->id,
        'address_line' => 'Jl. Raya Lama No. 5',
        'city' => 'KOTA JAKARTA SELATAN',
        'district' => 'JAGAKARSA',
        'village' => 'SRENGSENG SAWAH',
        'state' => 'DKI JAKARTA',
        'postal_code' => '12630',
        'country' => 'Indonesia',
        'is_primary' => true,
        'phone_number' => '081200000000',
        'address_type' => 'Home',
    ]);

    Volt::test('user-addresses.edit', ['address' => $address->id])
        ->assertSee('Edit address')
        ->assertSet('data.province_id', '11')
        ->assertSet('data.city_id', '1360')
        ->assertSet('data.district_id', '5823')
        ->assertSet('data.subdistrict_id', '1001')
        ->set('data.address_line', 'Jl. Raya Baru No. 7')
        ->set('data.subdistrict_id', '1002')
        ->set('data.subdistrict_name', 'TANJUNG BARAT')
        ->set('data.postal_code', '12530')
        ->call('update')
        ->assertHasNoErrors();

    $address->refresh();

    expect($address->address_line)->toBe('Jl. Raya Baru No. 7');
    expect($address->village)->toBe('TANJUNG BARAT');
    expect($address->postal_code)->toBe('12530');
});

it('does not allow editing another users address', function (): void {
    $owner = User::create([
        'name' => 'Address Owner',
        'email' => 'address-owner-' . uniqid() . '@example.com',
        'password' => bcrypt('secret123'),
    ]);

    $attacker = User::create([
        'name' => 'Address Attacker',
        'email' => 'address-attacker-' . uniqid() . '@example.com',
        'password' => bcrypt('secret123'),
    ]);

    $address = UserAddress::create([
        'user_id' => $owner->id,
        'address_line' => 'Jl. Rahasia No. 1',
        'city' => 'KOTA JAKARTA SELATAN',
        'district' => 'JAGAKARSA',
        'village' => 'SRENGSENG SAWAH',
        'state' => 'DKI JAKARTA',
        'postal_code' => '12630',
        'country' => 'Indonesia',
        'is_primary' => true,
        'phone_number' => '081200000000',
        'address_type' => 'Home',
    ]);

    actingAs($attacker);

    expect(fn () => Volt::test('user-addresses.edit', ['address' => $address->id]))
        ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
});
