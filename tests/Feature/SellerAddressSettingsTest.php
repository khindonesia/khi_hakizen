<?php

use App\Filament\Pages\SellerAddressSettings;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    config()->set('services.rajaongkir.base_url', 'https://rajaongkir.test/api/v1');
    config()->set('services.rajaongkir.api_key', 'test-rajaongkir-key');
    Cache::flush();

    Http::fake([
        'https://rajaongkir.test/api/v1/destination/province' => Http::response([
            'data' => [
                ['id' => 11, 'name' => 'DKI JAKARTA'],
                ['id' => 12, 'name' => 'BANTEN'],
            ],
            'meta' => ['code' => 200, 'status' => 'success'],
        ]),
        'https://rajaongkir.test/api/v1/destination/city/11' => Http::response([
            'data' => [
                ['id' => 1360, 'name' => 'KOTA JAKARTA SELATAN', 'zip_code' => '0'],
            ],
            'meta' => ['code' => 200, 'status' => 'success'],
        ]),
        'https://rajaongkir.test/api/v1/destination/district/1360' => Http::response([
            'data' => [
                ['id' => 5823, 'name' => 'JAGAKARSA', 'zip_code' => '12630'],
            ],
            'meta' => ['code' => 200, 'status' => 'success'],
        ]),
        'https://rajaongkir.test/api/v1/destination/sub-district/5823' => Http::response([
            'data' => [
                ['id' => 1001, 'name' => 'SRENGSENG SAWAH', 'zip_code' => '12630'],
            ],
            'meta' => ['code' => 200, 'status' => 'success'],
        ]),
    ]);
});

it('renders the seller address settings page and saves settings', function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $adminRole = Role::firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);

    $admin = User::withoutEvents(function (): User {
        return User::create([
            'name' => 'Admin User',
            'email' => 'seller-settings-test@example.com',
            'username' => 'seller-settings-test',
            'password' => Hash::make('password'),
        ]);
    });

    $admin->assignRole($adminRole);

    // Initial mount to verify form loading
    Livewire::actingAs($admin)
        ->test(SellerAddressSettings::class)
        ->assertFormSet([
            'seller_name' => null,
            'seller_phone' => null,
        ])
        ->fillForm([
            'seller_name' => 'KHI Headquarters',
            'seller_phone' => '+628123456789',
            'seller_address' => 'Jl. TB Simatupang No. 15',
            'province_id' => '11',
            'city_id' => '1360',
            'district_id' => '5823',
            'subdistrict_id' => '1001',
            'seller_postal_code' => '12630',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    // Verify settings were successfully updated in DB and cached settings are cleared
    expect(\Wave\Setting::get('shop.seller_name'))->toBe('KHI Headquarters');
    expect(\Wave\Setting::get('shop.seller_phone'))->toBe('+628123456789');
    expect(\Wave\Setting::get('shop.seller_address'))->toBe('Jl. TB Simatupang No. 15');
    expect(\Wave\Setting::get('shop.seller_province_id'))->toBe('11');
    expect(\Wave\Setting::get('shop.seller_province_name'))->toBe('DKI JAKARTA');
    expect(\Wave\Setting::get('shop.seller_city_id'))->toBe('1360');
    expect(\Wave\Setting::get('shop.seller_city_name'))->toBe('KOTA JAKARTA SELATAN');
    expect(\Wave\Setting::get('shop.seller_district_id'))->toBe('5823');
    expect(\Wave\Setting::get('shop.seller_district_name'))->toBe('JAGAKARSA');
    expect(\Wave\Setting::get('shop.seller_subdistrict_id'))->toBe('1001');
    expect(\Wave\Setting::get('shop.seller_subdistrict_name'))->toBe('SRENGSENG SAWAH');
    expect(\Wave\Setting::get('shop.seller_postal_code'))->toBe('12630');
});
