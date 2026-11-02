<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Cotizaciones BNA de venta, solo informativas para el header.
 * No interviene en ExchangeRateService ni en las suscripciones.
 * La API externa se consulta exclusivamente con refresh() desde Scheduler.
 */
class OfficialExchangeRatesService
{
    private const API = 'https://monedapi.ar/api/v2/quotes';
    private const CACHE_KEY = 'aeria:header:bna_quotes:v1';
    private const LAST_GOOD_KEY = 'aeria:header:bna_quotes:last_good:v1';

    /** Lectura segura para Blade / Vue: nunca consulta la API externa. */
    public function current(): array
    {
        $current = Cache::get(self::CACHE_KEY);

        if (is_array($current) && isset($current['rates'])) {
            return $current;
        }

        $previous = Cache::get(self::LAST_GOOD_KEY);

        if (is_array($previous) && isset($previous['rates'])) {
            $previous['stale'] = true;
            return $previous;
        }

        return [
            'rates' => [],
            'source' => 'Banco Nación (vía MonedAPI)',
            'is_today' => false,
            'stale' => true,
            'checked_at' => null,
        ];
    }

    /** Compatibilidad con el endpoint JSON anterior (sin nuevas solicitudes HTTP). */
    public function getRates(): array
    {
        $data = $this->current();

        if (empty($data['rates'])) {
            throw new RuntimeException('Todavía no hay cotizaciones en caché.');
        }

        return $data;
    }

    /** Consultar API, validar, almacenar y retornar datos para evento Reverb. */
    public function refresh(): array
    {
        try {
            $response = Http::acceptJson()
                ->timeout(10)
                ->get(self::API)
                ->throw();

            $items = $response->json();

            if (!is_array($items)) {
                throw new RuntimeException('La API devolvió un formato inválido.');
            }

            $rates = [];
            $today = now('America/Argentina/Buenos_Aires')->toDateString();
            $allToday = true;

            foreach (['USD', 'EUR', 'BRL'] as $currency) {
                $item = collect($items)->first(function ($row) use ($currency) {
                    return is_array($row)
                        && strtoupper((string) ($row['currency'] ?? '')) === $currency
                        && strtoupper((string) ($row['origin'] ?? '')) === 'BNA';
                });

                $rawSell = $item['sell'] ?? null;

                if (!is_numeric($rawSell) || (float) $rawSell <= 0) {
                    throw new RuntimeException("Cotización no disponible: {$currency}/BNA.");
                }

                $sell = (float) $rawSell;

                // BNA publica el real por 100 unidades. Si el intermediario
                // ya devolviera una cotización por 1 BRL (~centenas ARS),
                // evitamos dividir de nuevo. Verificar con fuente al desplegar.
                if ($currency === 'BRL' && $sell >= 1000) {
                    $sell /= 100;
                }

                $confirmedAt = $item['lastScrapedAt']
                    ?? $item['updatedAt']
                    ?? null;

                try {
                    $isToday = is_string($confirmedAt)
                        && Carbon::parse($confirmedAt)
                            ->timezone('America/Argentina/Buenos_Aires')
                            ->toDateString() === $today;
                } catch (\Throwable $e) {
                    $isToday = false;
                }

                $allToday = $allToday && $isToday;

                $rates[$currency] = [
                    'sell' => round($sell, 2),
                    'confirmed_at' => $confirmedAt,
                ];
            }

            $result = [
                'rates' => $rates,
                'source' => 'Banco Nación (vía MonedAPI)',
                'is_today' => $allToday,
                'stale' => false,
                'checked_at' => now('America/Argentina/Buenos_Aires')->toIso8601String(),
            ];

            // Un fin de semana se mantiene el último valor válido.
            Cache::put(self::CACHE_KEY, $result, now()->addDays(7));
            Cache::put(self::LAST_GOOD_KEY, $result, now()->addDays(7));

            return $result;
        } catch (\Throwable $e) {
            report($e);

            // No reemplazar los precios por cero ante una falla.
            $result = $this->current();
            $result['stale'] = true;
            Cache::put(self::CACHE_KEY, $result, now()->addDays(7));

            return $result;
        }
    }
}
