<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function searchDestination(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => 'required|string',
            'limit' => 'nullable|integer|min:1',
            'offset' => 'nullable|integer|min:0',
        ]);

        if (! $this->rajaOngkirApiKey()) {
            return response()->json([
                'error' => 'RajaOngkir API key belum dikonfigurasi.',
                'message' => 'Set RAJAONGKIR_API_KEY di environment application.',
            ], 500);
        }

        $response = Http::withHeaders($this->rajaOngkirHeaders())
            ->withQueryParameters([
                'search' => $validated['search'],
                'limit' => $validated['limit'] ?? 5,
                'offset' => $validated['offset'] ?? 0,
            ])
            ->get($this->rajaOngkirBaseUrl() . '/destination/domestic-destination');

        Log::info('RajaOngkir destination lookup completed.', [
            'url' => $this->rajaOngkirBaseUrl() . '/destination/domestic-destination',
            'params' => [
                'search' => $validated['search'],
                'limit' => $validated['limit'] ?? 5,
                'offset' => $validated['offset'] ?? 0,
            ],
            'status' => $response->status(),
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json([
            'error' => 'Gagal mengambil data dari API RajaOngkir.',
            'message' => $response->body(),
        ], $response->status());
    }

    public function getShippingCost(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'courier' => 'required|string',
            'origin' => 'required|integer',
            'destination' => 'required|integer',
            'weight' => 'required|integer|min:1',
        ]);

        if (! $this->rajaOngkirApiKey()) {
            return response()->json([
                'error' => 'RajaOngkir API key belum dikonfigurasi.',
                'message' => 'Set RAJAONGKIR_API_KEY di environment application.',
            ], 500);
        }

        $response = Http::withHeaders($this->rajaOngkirHeaders())
            ->asForm()
            ->post($this->rajaOngkirBaseUrl() . '/calculate/domestic-cost', [
                'origin' => $validated['origin'],
                'destination' => $validated['destination'],
                'weight' => $validated['weight'],
                'courier' => $validated['courier'],
                'price' => $this->rajaOngkirPriceType(),
            ]);

        Log::info('RajaOngkir shipping cost lookup completed.', [
            'url' => $this->rajaOngkirBaseUrl() . '/calculate/domestic-cost',
            'params' => [
                'origin' => $validated['origin'],
                'destination' => $validated['destination'],
                'weight' => $validated['weight'],
                'courier' => $validated['courier'],
                'price' => $this->rajaOngkirPriceType(),
            ],
            'status' => $response->status(),
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json([
            'error' => 'Gagal mengambil data dari API RajaOngkir.',
            'message' => $response->body(),
        ], $response->status());
    }

    private function rajaOngkirApiKey(): ?string
    {
        $apiKey = config('services.rajaongkir.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            return null;
        }

        return $apiKey;
    }

    private function rajaOngkirBaseUrl(): string
    {
        return rtrim((string) config('services.rajaongkir.base_url', 'https://rajaongkir.komerce.id/api/v1'), '/');
    }

    /**
     * @return array{key: string}
     */
    private function rajaOngkirHeaders(): array
    {
        return [
            'key' => $this->rajaOngkirApiKey() ?? '',
        ];
    }

    private function rajaOngkirPriceType(): string
    {
        $priceType = config('services.rajaongkir.price_type', 'lowest');

        if (! is_string($priceType) || $priceType === '') {
            return 'lowest';
        }

        return $priceType;
    }
}
