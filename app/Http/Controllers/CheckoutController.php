<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function searchDestination(Request $request)
    {
        $validated = $request->validate([
            'search' => 'required|string',
            'limit' => 'nullable|integer|min:1',
            'offset' => 'nullable|integer|min:0',
        ]);
    
        $search = $validated['search'];
        $limit = $validated['limit'] ?? 5;
        $offset = $validated['offset'] ?? 0;
    
        $url = 'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination';
        $apiKey = env('RAJAONGKIR_API_KEY');
    
        $response = Http::withHeader('key', $apiKey)
            ->withQueryParameters([
                'search' => $search,
                'limit' => $limit,
                'offset' => $offset,
            ])
            ->get($url);
    
        // log response
        Log::info('RajaOngkir API Response:', [
            'url' => $url,
            'headers' => ['key' => $apiKey],
            'params' => [
                'search' => $search,
                'limit' => $limit,
                'offset' => $offset,
            ],
            'response' => $response->json(),
        ]);
    
        if ($response->successful()) {
            return response()->json($response->json());
        }
    
        return response()->json([
            'error' => 'Gagal mengambil data dari API RajaOngkir.',
            'message' => $response->body(),
        ], $response->status());
    }

    public function getShippingCost(Request $request)
{
    $validated = $request->validate([
        'courier' => 'required|string',
        'origin' => 'required|integer',
        'destination' => 'required|integer',
        'weight' => 'required|integer|min:1',
    ]);

    $url = 'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost';
    $apiKey = env('RAJAONGKIR_API_KEY');

    // Gunakan asForm() untuk mengirim data sebagai application/x-www-form-urlencoded
    $response = Http::withHeaders([
        'key' => $apiKey,
        'Content-Type' => 'application/x-www-form-urlencoded'
    ])->asForm()->post($url, [
        'origin' => $validated['origin'],
        'destination' => $validated['destination'],
        'weight' => $validated['weight'],
        'courier' => $validated['courier'],
        'price' => 'lowest' 
    ]);

    // Log response untuk debugging
    Log::info('RajaOngkir Cost API Response:', [
        'url' => $url,
        'params' => [
            'origin' => $validated['origin'],
            'destination' => $validated['destination'],
            'weight' => $validated['weight'],
            'courier' => $validated['courier'],
            'price' => 'lowest'
        ],
        'response' => $response->json(),
    ]);

    if ($response->successful()) {
        return response()->json($response->json());
    }

    return response()->json([
        'error' => 'Gagal mengambil data dari API RajaOngkir.',
        'message' => $response->body(),
    ], $response->status());
}
}