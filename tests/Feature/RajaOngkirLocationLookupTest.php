<?php

use App\Http\Controllers\RajaOngkirLocationLookup;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config()->set('services.rajaongkir.base_url', 'https://rajaongkir.test/api/v1');
    config()->set('services.rajaongkir.api_key', 'test-rajaongkir-key');
    config()->set('services.rajaongkir.cache_ttl', 60);
    Cache::flush();

    Http::fake(function ($request) {
        $path = ltrim((string) parse_url(str_replace('https://rajaongkir.test/api/v1/', '', $request->url()), PHP_URL_PATH), '/');

        $fixtures = [
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

        return Http::response($fixtures[$path] ?? ['data' => [], 'meta' => []]);
    });
});

it('loads location hierarchy from the RajaOngkir api', function (): void {
    $lookup = app(RajaOngkirLocationLookup::class);

    expect($lookup->provinceOptions())->toBe([
        '11' => 'DKI JAKARTA',
        '12' => 'BANTEN',
    ]);

    expect($lookup->cityOptions('11'))->toBe([
        '1360' => 'KOTA JAKARTA SELATAN',
        '1361' => 'KOTA JAKARTA PUSAT',
    ]);

    expect($lookup->districtOptions('1360'))->toBe([
        '5823' => 'JAGAKARSA',
        '5824' => 'PASAR MINGGU',
    ]);

    expect($lookup->subdistrictOptions('5823'))->toBe([
        '1001' => 'SRENGSENG SAWAH',
        '1002' => 'TANJUNG BARAT',
    ]);
});

it('finds destinations by postal code from RajaOngkir search', function (): void {
    $lookup = app(RajaOngkirLocationLookup::class);

    expect($lookup->destinationByPostalCode('12630'))->toMatchArray([
        'id' => '98765',
        'province_name' => 'DKI JAKARTA',
        'city_name' => 'KOTA JAKARTA SELATAN',
        'district_name' => 'JAGAKARSA',
        'subdistrict_name' => 'SRENGSENG SAWAH',
        'zip_code' => '12630',
    ]);
});
