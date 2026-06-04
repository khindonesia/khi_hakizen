<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class GeoNamesController extends Controller
{
    public function searchCities(Request $request): JsonResponse
    {
        $country = $request->query('country');
        $adminCode1 = $request->query('adminCode1');
        $query = $request->query('name_startsWith');

        if (empty($country)) {
            return response()->json(['error' => 'Country code is required'], 400);
        }

        if (empty($query)) {
            return response()->json(['geonames' => []]);
        }

        try {
            $params = [
                'username' => 'komunitashistoriaid',
                'country' => $country,
                'featureClass' => 'P',
                'maxRows' => 10,
                'name_startsWith' => $query,
            ];

            if (!empty($adminCode1)) {
                $params['adminCode1'] = $adminCode1;
            }

            // GeoNames API request (via HTTP request from backend to avoid Mixed Content errors)
            $response = Http::timeout(8)
                ->get('http://api.geonames.org/searchJSON', $params);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Failed to fetch cities from GeoNames',
                    'fallback' => true
                ], 502);
            }

            $data = $response->json();

            // GeoNames returns 200 with a 'status' object when there is an API error (e.g. limit reached)
            if (isset($data['status'])) {
                return response()->json([
                    'error' => $data['status']['message'] ?? 'GeoNames API limit or error',
                    'fallback' => true
                ], 502);
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Exception calling GeoNames: ' . $e->getMessage(),
                'fallback' => true
            ], 500);
        }
    }

    public function searchProvinces(Request $request): JsonResponse
    {
        $country = $request->query('country');

        if (empty($country)) {
            return response()->json(['error' => 'Country code is required'], 400);
        }

        try {
            // GeoNames API request for provinces in the selected country
            $response = Http::timeout(8)
                ->get('http://api.geonames.org/searchJSON', [
                    'username' => 'komunitashistoriaid',
                    'country' => $country,
                    'featureClass' => 'A',
                    'featureCode' => 'ADM1',
                    'maxRows' => 100,
                ]);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Failed to fetch provinces from GeoNames',
                    'fallback' => true
                ], 502);
            }

            $data = $response->json();

            // GeoNames returns 200 with a 'status' object when there is an API error (e.g. limit reached)
            if (isset($data['status'])) {
                return response()->json([
                    'error' => $data['status']['message'] ?? 'GeoNames API limit or error',
                    'fallback' => true
                ], 502);
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Exception calling GeoNames: ' . $e->getMessage(),
                'fallback' => true
            ], 500);
        }
    }
}
