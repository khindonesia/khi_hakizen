<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirLocationLookup
{
    public function provinces(): Collection
    {
        return $this->locationList('destination/province');
    }

    public function cities(string $provinceCode): Collection
    {
        return $this->locationList("destination/city/{$provinceCode}");
    }

    public function districts(string $cityCode): Collection
    {
        return $this->locationList("destination/district/{$cityCode}");
    }

    public function subdistricts(string $districtCode): Collection
    {
        return $this->locationList("destination/sub-district/{$districtCode}");
    }

    public function provinceOptions(): array
    {
        return $this->provinces()->pluck('name', 'code')->all();
    }

    public function cityOptions(string $provinceCode): array
    {
        return $this->cities($provinceCode)->pluck('name', 'code')->all();
    }

    public function districtOptions(string $cityCode): array
    {
        return $this->districts($cityCode)->pluck('name', 'code')->all();
    }

    public function subdistrictOptions(string $districtCode): array
    {
        return $this->subdistricts($districtCode)->pluck('name', 'code')->all();
    }

    public function provinceByName(string $name): ?array
    {
        return $this->findByName($this->provinces(), $name);
    }

    public function provinceByCode(string $code): ?array
    {
        return $this->provinces()->firstWhere('code', (string) $code);
    }

    public function cityByName(string $provinceCode, string $name): ?array
    {
        return $this->findByName($this->cities($provinceCode), $name);
    }

    public function cityByCode(string $provinceCode, string $code): ?array
    {
        return $this->cities($provinceCode)->firstWhere('code', (string) $code);
    }

    public function districtByName(string $cityCode, string $name): ?array
    {
        return $this->findByName($this->districts($cityCode), $name);
    }

    public function districtByCode(string $cityCode, string $code): ?array
    {
        return $this->districts($cityCode)->firstWhere('code', (string) $code);
    }

    public function subdistrictByName(string $districtCode, string $name): ?array
    {
        return $this->findByName($this->subdistricts($districtCode), $name);
    }

    public function subdistrictByCode(string $districtCode, string $code): ?array
    {
        return $this->subdistricts($districtCode)->firstWhere('code', (string) $code);
    }

    /**
     * @return Collection<int, array{id: string, label: string, province_name?: string, city_name?: string, district_name?: string, subdistrict_name?: string, zip_code?: string}>
     */
    public function searchDestinations(string $search, int $limit = 10, int $offset = 0): Collection
    {
        return $this->searchList('destination/domestic-destination', [
            'search' => $search,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function destinationByPostalCode(string $postalCode): ?array
    {
        return $this->searchDestinations($postalCode)->first();
    }

    private function locationList(string $path): Collection
    {
        $cacheKey = $this->cacheKey($path);

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);

            return $cached instanceof Collection ? $cached : collect($cached ?? []);
        }

        try {
            $response = Http::acceptJson()
                ->timeout(10)
                ->retry(2, 250)
                ->withHeaders($this->headers())
                ->get($this->endpoint($path));

            if (! $response->successful()) {
                Log::warning('RajaOngkir location request failed', [
                    'path' => $path,
                    'status' => $response->status(),
                ]);

                return collect();
            }
        } catch (\Throwable $e) {
            Log::error('RajaOngkir location request exception', [
                'path' => $path,
                'message' => $e->getMessage(),
            ]);

            return collect();
        }

        $records = collect(data_get($response->json(), 'data', []))
            ->filter(fn ($item): bool => is_array($item))
            ->map(fn (array $item): array => [
                'code' => (string) data_get($item, 'id', data_get($item, 'code', '')),
                'name' => (string) data_get($item, 'name', ''),
                'zip_code' => (string) data_get($item, 'zip_code', ''),
            ])
            ->filter(fn (array $item): bool => $item['code'] !== '' && $item['name'] !== '')
            ->values();

        Cache::put($cacheKey, $records, now()->addSeconds((int) config('services.rajaongkir.cache_ttl', 86400)));

        return $records;
    }

    /**
     * @param array<string, scalar> $query
     * @return Collection<int, array{id: string, label: string, province_name?: string, city_name?: string, district_name?: string, subdistrict_name?: string, zip_code?: string}>
     */
    private function searchList(string $path, array $query): Collection
    {
        $cacheKey = $this->cacheKey($path . ':' . md5(json_encode($query)));

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);

            return $cached instanceof Collection ? $cached : collect($cached ?? []);
        }

        try {
            $response = Http::acceptJson()
                ->timeout(10)
                ->retry(2, 250)
                ->withHeaders($this->headers())
                ->get($this->endpoint($path), $query);

            if (! $response->successful()) {
                Log::warning('RajaOngkir destination search failed', [
                    'path' => $path,
                    'query' => $query,
                    'status' => $response->status(),
                ]);

                return collect();
            }
        } catch (\Throwable $e) {
            Log::error('RajaOngkir destination search exception', [
                'path' => $path,
                'query' => $query,
                'message' => $e->getMessage(),
            ]);

            return collect();
        }

        $records = collect(data_get($response->json(), 'data', []))
            ->filter(fn ($item): bool => is_array($item))
            ->map(function (array $item): array {
                return [
                    'id' => (string) data_get($item, 'id', ''),
                    'label' => (string) data_get($item, 'label', ''),
                    'province_name' => (string) data_get($item, 'province_name', ''),
                    'city_name' => (string) data_get($item, 'city_name', ''),
                    'district_name' => (string) data_get($item, 'district_name', ''),
                    'subdistrict_name' => (string) data_get($item, 'subdistrict_name', ''),
                    'zip_code' => (string) data_get($item, 'zip_code', ''),
                ];
            })
            ->filter(fn (array $item): bool => $item['id'] !== '' && $item['label'] !== '')
            ->values();

        Cache::put($cacheKey, $records, now()->addSeconds((int) config('services.rajaongkir.cache_ttl', 86400)));

        return $records;
    }

    private function findByName(Collection $items, string $name): ?array
    {
        $needle = $this->normalizeName($name);

        return $items->first(function (array $item) use ($needle): bool {
            return $this->normalizeName((string) ($item['name'] ?? '')) === $needle;
        });
    }

    private function normalizeName(string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim(mb_strtolower($value)));

        return is_string($normalized) ? $normalized : '';
    }

    private function headers(): array
    {
        return [
            'Key' => (string) config('services.rajaongkir.api_key', ''),
        ];
    }

    private function cacheKey(string $path): string
    {
        return 'rajaongkir-location:' . md5($this->endpointBase() . '|' . $path);
    }

    private function endpoint(string $path): string
    {
        return $this->endpointBase() . '/' . ltrim($path, '/');
    }

    private function endpointBase(): string
    {
        return rtrim((string) config('services.rajaongkir.base_url', 'https://rajaongkir.komerce.id/api/v1'), '/');
    }
}
