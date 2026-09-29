<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ExchangeRateService
{
    private const URL = 'https://dolarapi.com/v1/dolares/oficial';

    /*
    |--------------------------------------------------------------------------
    | Dólar oficial vendedor
    |--------------------------------------------------------------------------
    */

    public function getUsdSellRate(): float
    {
        $response = Http::timeout(10)
            ->retry(2, 500)
            ->get(self::URL);

        if (!$response->successful()) {
            throw new RuntimeException(
                'No se pudo obtener la cotización del dólar oficial.'
            );
        }

        $data = $response->json();

        if (
            !isset($data['venta']) ||
            !is_numeric($data['venta'])
        ) {
            throw new RuntimeException(
                'La respuesta de la cotización no es válida.'
            );
        }

        $rate = (float) $data['venta'];

        if ($rate <= 0) {
            throw new RuntimeException(
                'La cotización del dólar oficial no es válida.'
            );
        }

        return $rate;
    }


    /*
    |--------------------------------------------------------------------------
    | Convertir USD a ARS
    |--------------------------------------------------------------------------
    */

    public function convertUsdToArs(float $usd): float
    {
        if ($usd <= 0) {
            throw new RuntimeException(
                'El importe en USD debe ser mayor a cero.'
            );
        }

        return round(
            $usd * $this->getUsdSellRate(),
            2
        );
    }
}