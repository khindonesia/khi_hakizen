<?php

namespace App\Actions;

use App\Http\Controllers\RajaOngkirLocationLookup;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Facades\DB;

class CreateUserAddressAction
{
    public function __construct(
        private readonly RajaOngkirLocationLookup $locations,
    ) {
    }

    /**
     * @return array{provinces: array<int, array<string, mixed>>, form: array<string, mixed>}
     */
    public function initialState(User $user): array
    {
        return [
            'provinces' => $this->locations->provinces()->all(),
            'form' => [
                'address_type' => 'Home',
                'is_primary' => ! $user->userAddresses()->exists(),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $state
     */
    public function create(User $user, array $state): UserAddress
    {
        return DB::transaction(function () use ($user, $state): UserAddress {
            $location = $this->resolveLocationData($state);
            $shouldBePrimary = (bool) ($state['is_primary'] ?? false) || ! $user->userAddresses()->exists();

            $address = UserAddress::query()->create(array_merge(
                $this->editableData($state),
                $location,
                [
                    'country' => 'Indonesia',
                    'user_id' => $user->id,
                    'is_primary' => false,
                ],
            ));

            if ($shouldBePrimary) {
                UserAddress::query()
                    ->where('user_id', $user->id)
                    ->whereKeyNot($address->id)
                    ->update(['is_primary' => false]);

                $address->forceFill(['is_primary' => true])->save();
            }

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
        ];
    }
}
