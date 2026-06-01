<?php

namespace App\Services;

use App\Models\UserAddress;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RajaOngkirShippingService
{
    /**
     * @return array{service: string, cost: int, description: string|null, etd: string|null}
     */
    public function quoteForAddress(UserAddress $address, string $courierCode, string $serviceCode, int $weight): array
    {
        $destinationId = $this->destinationIdForAddress($address);
        $origin = (int) setting('shop.seller_subdistrict_id', config('services.rajaongkir.origin_id', 17693));
        $priceType = (string) config('services.rajaongkir.price_type', 'lowest');

        // Cache shipping quote calculation for 30 minutes to make form adjustments instant
        $cacheKey = 'rajaongkir_quote_' . md5(implode('_', [
            $origin,
            $destinationId,
            $weight,
            $courierCode,
            $priceType
        ]));

        $shippingData = cache()->remember($cacheKey, now()->addMinutes(30), function () use ($origin, $destinationId, $weight, $courierCode, $priceType, $address) {
            $response = Http::withHeaders($this->headers())
                ->asForm()
                ->timeout(10)
                ->retry(2, 250)
                ->post($this->endpoint('calculate/domestic-cost'), [
                    'origin' => $origin,
                    'destination' => $destinationId,
                    'weight' => $weight,
                    'courier' => $courierCode,
                    'price' => $priceType,
                ]);

            if (! $response->successful()) {
                Log::warning('RajaOngkir shipping quote failed', [
                    'address_id' => $address->id,
                    'status' => $response->status(),
                ]);

                throw ValidationException::withMessages([
                    'courier_code' => 'Unable to calculate shipping cost for selected address.',
                ]);
            }

            return data_get($response->json(), 'data', []);
        });

        $quote = collect($shippingData)
            ->filter(fn (mixed $item): bool => is_array($item))
            ->first(fn (array $item): bool => strcasecmp((string) data_get($item, 'service'), $serviceCode) === 0);

        if (! is_array($quote)) {
            throw ValidationException::withMessages([
                'service_code' => 'Selected shipping service is not available for this address.',
            ]);
        }

        return [
            'service' => (string) data_get($quote, 'service'),
            'cost' => (int) data_get($quote, 'cost', 0),
            'description' => data_get($quote, 'description'),
            'etd' => data_get($quote, 'etd'),
        ];
    }

    /**
     * Track an airwaybill (AWB) status.
     *
     * @return array<string, mixed>|null
     */
    public function trackWaybill(string $awb, string $courier, ?string $phoneNumber = null): ?array
    {
        $params = [
            'awb' => $awb,
            'courier' => $courier,
        ];

        if ($phoneNumber) {
            // Keep only digits and get the last 5 characters
            $digitsOnly = preg_replace('/\D/', '', $phoneNumber);
            if (strlen($digitsOnly) >= 5) {
                $params['last_phone_number'] = (int) substr($digitsOnly, -5);
            }
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->asForm()
                ->timeout(12)
                ->retry(2, 200)
                ->post($this->endpoint('track/waybill'), $params);

            if ($response->successful()) {
                $json = $response->json();
                if (data_get($json, 'meta.status') === 'success' || data_get($json, 'meta.code') == 200) {
                    return data_get($json, 'data');
                } else {
                    Log::warning('RajaOngkir tracking API returned error', [
                        'response' => $json,
                        'params' => $params,
                    ]);
                }
            } else {
                Log::warning('RajaOngkir tracking request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'params' => $params,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('RajaOngkir tracking exception', [
                'message' => $e->getMessage(),
                'params' => $params,
            ]);
        }

        return null;
    }

    private function destinationIdForAddress(UserAddress $address): int
    {
        $cacheKey = 'rajaongkir_destination_id_' . md5($address->postal_code);

        return cache()->remember($cacheKey, now()->addDays(30), function () use ($address) {
            $response = Http::acceptJson()
                ->timeout(10)
                ->retry(2, 250)
                ->withHeaders($this->headers())
                ->get($this->endpoint('destination/domestic-destination'), [
                    'search' => $address->postal_code,
                    'limit' => 5,
                    'offset' => 0,
                ]);

            if (! $response->successful()) {
                Log::warning('RajaOngkir destination lookup failed during checkout', [
                    'address_id' => $address->id,
                    'status' => $response->status(),
                ]);

                throw ValidationException::withMessages([
                    'address_id' => 'Unable to resolve shipping destination for selected address.',
                ]);
            }

            $destination = collect(data_get($response->json(), 'data', []))
                ->filter(fn (mixed $item): bool => is_array($item))
                ->first();

            $destinationId = (int) data_get($destination, 'id', 0);

            if ($destinationId <= 0) {
                throw ValidationException::withMessages([
                    'address_id' => 'Selected address cannot be matched to a shipping destination.',
                ]);
            }

            return $destinationId;
        });
    }

    /**
     * @return array{key: string}
     */
    private function headers(): array
    {
        return [
            'key' => (string) config('services.rajaongkir.api_key', ''),
        ];
    }

    private function endpoint(string $path): string
    {
        return rtrim((string) config('services.rajaongkir.base_url', 'https://rajaongkir.komerce.id/api/v1'), '/') . '/' . ltrim($path, '/');
    }
}
