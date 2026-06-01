<?php

namespace App\Actions;

use App\Http\Controllers\RajaOngkirLocationLookup;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Facades\DB;

class UpdateUserAddressAction
{
    public function __construct(
        private readonly RajaOngkirLocationLookup $locations,
    ) {
    }

    /**
     * @return array{address: UserAddress, is_primary: bool, location_resolved: bool, provinces: array<int, array<string, mixed>>, cities: array<int, array<string, mixed>>, districts: array<int, array<string, mixed>>, subdistricts: array<int, array<string, mixed>>, form: array<string, mixed>}
     */
    public function initialState(User $user, int|string $addressId): array
    {
        $address = UserAddress::query()
            ->where('user_id', $user->id)
            ->findOrFail($addressId);

        $state = [
            'address_line' => $address->address_line,
            'address_type' => $address->address_type,
            'phone_number' => $address->phone_number,
            'postal_code' => $address->postal_code,
            'is_primary' => $address->is_primary,
        ];

        $provinces = $this->locations->provinces()->all();
        $cities = [];
        $districts = [];
        $subdistricts = [];
        $locationResolved = true;

        $provinceId = $address->province_id;
        $cityId = $address->city_id;
        $districtId = $address->district_id;
        $subdistrictId = $address->subdistrict_id;

        if ($provinceId && $cityId && $districtId && $subdistrictId) {
            $cities = $this->locations->cities($provinceId)->all();
            $districts = $this->locations->districts($cityId)->all();
            $subdistricts = $this->locations->subdistricts($districtId)->all();

            $state = array_merge($state, [
                'province_id' => $provinceId,
                'province_name' => $address->state,
                'city_id' => $cityId,
                'city_name' => $address->city,
                'district_id' => $districtId,
                'district_name' => $address->district,
                'subdistrict_id' => $subdistrictId,
                'subdistrict_name' => $address->village,
            ]);
        } else {
            $destination = $this->locations->destinationByPostalCode($address->postal_code);
            $province = $destination ? $this->locations->provinceByName((string) data_get($destination, 'province_name', '')) : null;

            if (! $destination || ! $province) {
                $locationResolved = false;
            } else {
                $cities = $this->locations->cities($province['code'])->all();
                $city = $this->locations->cityByName($province['code'], (string) data_get($destination, 'city_name', ''));

                if (! $city) {
                    $locationResolved = false;
                } else {
                    $districts = $this->locations->districts($city['code'])->all();
                    $district = $this->locations->districtByName($city['code'], (string) data_get($destination, 'district_name', ''));

                    if (! $district) {
                        $locationResolved = false;
                    } else {
                        $subdistricts = $this->locations->subdistricts($district['code'])->all();
                        $subdistrict = $this->locations->subdistrictByName($district['code'], (string) data_get($destination, 'subdistrict_name', ''));

                        if (! $subdistrict) {
                            $locationResolved = false;
                        } else {
                            $state = array_merge($state, [
                                'province_id' => $province['code'],
                                'province_name' => $province['name'],
                                'city_id' => $city['code'],
                                'city_name' => $city['name'],
                                'district_id' => $district['code'],
                                'district_name' => $district['name'],
                                'subdistrict_id' => $subdistrict['code'],
                                'subdistrict_name' => $subdistrict['name'],
                            ]);
                        }
                    }
                }
            }
        }

        return [
            'address' => $address,
            'is_primary' => $address->is_primary,
            'location_resolved' => $locationResolved,
            'provinces' => $provinces,
            'cities' => $cities,
            'districts' => $districts,
            'subdistricts' => $subdistricts,
            'form' => $state,
        ];
    }

    /**
     * @param array<string, mixed> $state
     */
    public function update(User $user, int|string $addressId, array $state): UserAddress
    {
        return DB::transaction(function () use ($user, $addressId, $state): UserAddress {
            $address = UserAddress::query()
                ->where('user_id', $user->id)
                ->findOrFail($addressId);

            $location = $this->resolveLocationData($state);
            $shouldBePrimary = $address->is_primary || (bool) ($state['is_primary'] ?? false);

            $address->update(array_merge(
                $this->editableData($state),
                $location,
                [
                    'country' => 'Indonesia',
                    'is_primary' => $shouldBePrimary,
                ],
            ));

            return $address;
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function cities(?string $provinceCode): array
    {
        return filled($provinceCode) ? $this->locations->cities($provinceCode)->all() : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function districts(?string $cityCode): array
    {
        return filled($cityCode) ? $this->locations->districts($cityCode)->all() : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function subdistricts(?string $districtCode): array
    {
        return filled($districtCode) ? $this->locations->subdistricts($districtCode)->all() : [];
    }

    /**
     * @param array<string, mixed> $state
     * @return array{state: string, city: string, district: string, village: string}
     */
    private function resolveLocationData(array $state): array
    {
        $provinceName = $state['province_name'] ?? data_get($this->locations->provinceByCode((string) $state['province_id']), 'name');
        $cityName = $state['city_name'] ?? data_get($this->locations->cityByCode((string) $state['province_id'], (string) $state['city_id']), 'name');
        $districtName = $state['district_name'] ?? data_get($this->locations->districtByCode((string) $state['city_id'], (string) $state['district_id']), 'name');
        $subdistrictName = $state['subdistrict_name'] ?? data_get($this->locations->subdistrictByCode((string) $state['district_id'], (string) $state['subdistrict_id']), 'name');

        foreach ([
            'province_name' => $provinceName,
            'city_name' => $cityName,
            'district_name' => $districtName,
            'subdistrict_name' => $subdistrictName,
        ] as $field => $value) {
            if (blank($value)) {
                throw new \RuntimeException("Missing {$field}.");
            }
        }

        return [
            'state' => (string) $provinceName,
            'city' => (string) $cityName,
            'district' => (string) $districtName,
            'village' => (string) $subdistrictName,
        ];
    }

    /**
     * @param array<string, mixed> $state
     * @return array<string, mixed>
     */
    private function editableData(array $state): array
    {
        return [
            'address_line' => $state['address_line'],
            'address_type' => $state['address_type'],
            'postal_code' => $state['postal_code'],
            'phone_number' => $state['phone_number'],
            'province_id' => $state['province_id'] ?? null,
            'city_id' => $state['city_id'] ?? null,
            'district_id' => $state['district_id'] ?? null,
            'subdistrict_id' => $state['subdistrict_id'] ?? null,
        ];
    }
}
