<?php

namespace App\Http\Controllers;

use App\Services\OfficialExchangeRatesService;
use Illuminate\Http\JsonResponse;

class OfficialExchangeRatesController extends Controller
{
    public function __invoke(OfficialExchangeRatesService $service): JsonResponse
    {
        try {
            $data = $service->getRates();

            return response()->json($data)
                ->header('Cache-Control', 'private, no-store, max-age=0');

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Cotizaciones temporalmente no disponibles.',
            ], 503)->header('Cache-Control', 'private, no-store, max-age=0');
        }
    }
}
