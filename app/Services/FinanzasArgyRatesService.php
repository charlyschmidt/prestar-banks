<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Cotizaciones informativas de Finanzas Argy para el header.
 * Consulta solo desde Scheduler. No participa en suscripciones ni cobros.
 * Los esquemas JSON del proveedor deben validarse en el entorno real.
 */
class FinanzasArgyRatesService
{
    private const API = 'https://api.finanzasargy.com';
    private const CACHE_KEY = 'aeria:header:finanzasargy:v1';
    private const LAST_GOOD_KEY = 'aeria:header:finanzasargy:last_good:v1';

    private const MARKETS = [
        'OFICIAL' => 'USD Oficial',
        'EUR_OFICIAL' => 'EUR Oficial',
        'BRL_OFICIAL' => 'BRL Oficial',
        'BLUE' => 'USD Blue',
        'INTERIOR' => 'USD Blue Interior',
        'EURO_BLUE' => 'EUR Blue',
        'REAL_BLUE' => 'BRL Blue',
    ];

    public function current(): array
    {
        $data = Cache::get(self::CACHE_KEY)
            ?? Cache::get(self::LAST_GOOD_KEY);

        if (!is_array($data) || !isset($data['rates'])) {
            return $this->emptyResult();
        }

        // Si el cron se detuvo, no presentar datos como actualizados.
        try {
            $checked = $data['checked_at'] ?? null;
            if (!$checked || Carbon::parse($checked)->lt(now()->subMinutes(11))) {
                $data['stale'] = true;
            }
        } catch (\Throwable $e) {
            $data['stale'] = true;
        }

        return $data;
    }

    /** Compatibilidad con la ruta JSON anterior, sin consultas HTTP. */
    public function getRates(): array
    {
        $data = $this->current();
        if (empty(array_filter($data['rates'], fn ($quote) => $quote !== null))) {
            throw new RuntimeException('Todavía no hay cotizaciones en caché.');
        }
        return $data;
    }

    /** Punto de consulta único: ejecutar desde Scheduler cada cinco minutos. */
    public function refresh(): array
    {
        $previous = Cache::get(self::LAST_GOOD_KEY);
        $rates = [];
        $problems = [];

        // Los endpoints figuran en https://api.finanzasargy.com/swagger
        $sources = [
            'dollar' => '/api/dolar/v2/general',
            'blue'   => '/api/mercado-blue',
            'euro'   => '/api/euro/panel',
            'real'   => '/api/real/panel',
        ];

        $responses = [];
        foreach ($sources as $name => $path) {
            try {
                $response = Http::acceptJson()
                    ->timeout(8)
                    ->connectTimeout(4)
                    ->get(self::API . $path)
                    ->throw();

                $payload = $response->json();
                if (!is_array($payload)) {
                    throw new RuntimeException('La respuesta no contiene JSON objeto/lista.');
                }
                $responses[$name] = $payload;
            } catch (\Throwable $e) {
                $problems[] = $name . ': ' . $e->getMessage();
            }
        }

        // Mantener mercados separados: nunca sustituir Blue Interior por Blue común.
        $routes = [
            'OFICIAL' => [['dollar', 'oficial']],
            'EUR_OFICIAL' => [['euro', 'euro_oficial']],
            'BRL_OFICIAL' => [['real', 'real_oficial']],
            'BLUE' => [['blue', 'blue'], ['dollar', 'blue']],
            'INTERIOR' => [['blue', 'interior']],
            'EURO_BLUE' => [['euro', 'euro_blue'], ['blue', 'euro_blue']],
            'REAL_BLUE' => [['real', 'real_blue'], ['blue', 'real_blue']],
        ];

        foreach (self::MARKETS as $code => $label) {
            $quote = null;

            foreach ($routes[$code] as [$source, $type]) {
                if (!isset($responses[$source])) {
                    continue;
                }
                $quote = $this->extractQuote($responses[$source], $type, $source);
                if ($quote !== null) {
                    break;
                }
            }

            if ($quote !== null) {
                $rates[$code] = $quote + [
                    'label' => $label,
                    'stale' => false,
                ];
                continue;
            }

            $old = $previous['rates'][$code] ?? null;
            $rates[$code] = is_array($old) ? array_merge($old, [
                'label' => $label,
                'stale' => true,
            ]) : null;
            $problems[] = "Sin datos verificables para {$label}";
        }

        if ($problems) {
            Log::warning('AERIA: cotizaciones Finanzas Argy incompletas', [
                'issues' => $problems,
            ]);
        }

        $hasFresh = collect($rates)->contains(
            fn ($quote) => is_array($quote) && ($quote['stale'] ?? true) === false
        );
        $allFresh = collect($rates)->every(
            fn ($quote) => is_array($quote) && ($quote['stale'] ?? true) === false
        );

        $result = [
            'rates' => $rates,
            'source' => 'Finanzas Argy',
            'stale' => !$allFresh,
            'checked_at' => now('America/Argentina/Buenos_Aires')->toIso8601String(),
        ];

        // Nunca borrar el último dato válido por una caída del proveedor.
        if ($hasFresh) {
            Cache::put(self::LAST_GOOD_KEY, $result, now()->addDays(7));
        }
        Cache::put(self::CACHE_KEY, $result, now()->addDays(7));

        return $result;
    }

    private function emptyResult(): array
    {
        return [
            'rates' => array_fill_keys(array_keys(self::MARKETS), null),
            'source' => 'Finanzas Argy',
            'stale' => true,
            'checked_at' => null,
        ];
    }

    /**
     * Busca una cotización identificada inequívocamente por mercado.
     * Acepta estructuras de mapa o listas y compra/venta en español o inglés.
     * Si no encuentra una pareja fiable, devuelve null (nunca inventa precios).
     */
    private function extractQuote(array $payload, string $target, string $source): ?array
    {
        $nodes = [];
        $walk = function ($value, string $path = '') use (&$walk, &$nodes) {
            if (!is_array($value)) {
                return;
            }

            $nodes[] = ['path' => $path, 'node' => $value];

            foreach ($value as $key => $child) {
                if (!is_array($child)) {
                    continue;
                }

                // No incorporar índices numéricos al nombre del mercado.
                $segment = is_string($key) ? $key : '';
                $walk($child, trim($path . ' ' . $segment));
            }
        };
        $walk($payload);

        foreach ($nodes as $record) {
            $node = $record['node'];
            $sourceHint = match ($source) {
                'real' => 'real ',
                'euro' => 'euro ',
                default => '',
            };
            $identified = $sourceHint . $record['path'];

            foreach (['name', 'nombre', 'tipo', 'type', 'casa', 'label', 'titulo', 'title', 'mercado', 'market'] as $field) {
                if (isset($node[$field]) && is_scalar($node[$field])) {
                    $identified .= ' ' . (string) $node[$field];
                }
            }

            if (!$this->isCorrectMarket($identified, $target)) {
                continue;
            }

            $buy = $this->readPrice($node, ['compra', 'buy', 'precioCompra', 'precio_compra', 'valorCompra']);
            $sell = $this->readPrice($node, ['venta', 'sell', 'precioVenta', 'precio_venta', 'valorVenta']);

            if ($buy === null || $sell === null || $buy > $sell) {
                continue;
            }

            return [
                'buy' => round($buy, 2),
                'sell' => round($sell, 2),
                'confirmed_at' => $this->readTimestamp($node),
            ];
        }

        return null;
    }

    private function isCorrectMarket(string $description, string $target): bool
    {
        $text = $this->normalized($description);
        $words = ' ' . $text . ' ';

        if ($target === 'oficial') {
            return (str_contains($words, ' oficial ') || str_contains($words, ' bna '))
                && !str_contains($words, ' euro ')
                && !str_contains($words, ' real ')
                && !str_contains($words, ' blue ');
        }

        // EUR y BRL oficiales deben identificarse explícitamente dentro de
        // sus respectivos paneles; nunca mezclar con Blue ni Tarjeta.
        if ($target === 'euro_oficial') {
            return preg_match('/\b(euro|eur)\b/', $words)
                && preg_match('/\b(oficial|bna)\b/', $words)
                && !preg_match('/\b(blue|tarjeta|qatar|real|brl)\b/', $words);
        }

        if ($target === 'real_oficial') {
            return preg_match('/\b(real|brl)\b/', $words)
                && preg_match('/\b(oficial|bna)\b/', $words)
                && !preg_match('/\b(blue|tarjeta|qatar|euro|eur)\b/', $words);
        }

        if ($target === 'blue') {
            return str_contains($words, ' blue ')
                && !preg_match('/\b(interior|gba|portales|euro|real|brasil)\b/', $words);
        }

        if ($target === 'interior') {
            return str_contains($words, ' interior ')
                && !str_contains($words, ' real ');
        }

        if ($target === 'euro_blue') {
            return str_contains($words, ' euro ')
                && str_contains($words, ' blue ')
                && !preg_match('/\b(real|brl|interior|oficial|tarjeta)\b/', $words);
        }

        if ($target === 'real_blue') {
            return str_contains($words, ' blue ')
                && (str_contains($words, ' real ') || str_contains($words, ' brl '))
                && !str_contains($words, ' euro ');
        }

        return false;
    }

    private function normalized(string $text): string
    {
        // Distingue nombres como blueInterior y realBlue.
        $text = preg_replace('/([a-z])([A-Z])/', '$1 $2', $text);
        $text = strtolower(Str::ascii($text));
        return trim(preg_replace('/[^a-z0-9]+/', ' ', $text));
    }

    private function readPrice(array $node, array $aliases): ?float
    {
        $normalizedAliases = array_map(fn ($item) => $this->normalized($item), $aliases);

        foreach ($node as $key => $raw) {
            if (!is_string($key) || !in_array($this->normalized($key), $normalizedAliases, true)) {
                continue;
            }
            if (is_array($raw)) {
                $raw = $raw['precio'] ?? $raw['valor'] ?? $raw['value'] ?? null;
            }
            if (!is_scalar($raw)) {
                continue;
            }
            $number = $this->parsePrice($raw);
            if ($number !== null) {
                return $number;
            }
        }
        return null;
    }

    private function parsePrice(mixed $raw): ?float
    {
        if (is_int($raw) || is_float($raw)) {
            $value = (float) $raw;
            return is_finite($value) && $value > 0 ? $value : null;
        }
        if (!is_string($raw)) {
            return null;
        }
        $clean = trim(preg_replace('/[^0-9,.-]/', '', $raw));
        if ($clean === '') {
            return null;
        }
        // $1.520,50 => 1520.50 / 1,520.50 => 1520.50
        if (str_contains($clean, ',') && str_contains($clean, '.')) {
            $lastComma = strrpos($clean, ',');
            $lastDot = strrpos($clean, '.');
            $clean = $lastComma > $lastDot
                ? str_replace(',', '.', str_replace('.', '', $clean))
                : str_replace(',', '', $clean);
        } elseif (str_contains($clean, ',')) {
            $clean = str_replace(',', '.', $clean);
        } elseif (preg_match('/^\d{1,3}(?:\.\d{3})+$/', $clean)) {
            $clean = str_replace('.', '', $clean);
        }

        if (!is_numeric($clean)) {
            return null;
        }
        $value = (float) $clean;
        return is_finite($value) && $value > 0 ? $value : null;
    }

    private function readTimestamp(array $node): ?string
    {
        foreach (['updatedAt', 'updated_at', 'fechaActualizacion', 'fecha_actualizacion', 'fecha', 'timestamp', 'lastUpdate'] as $field) {
            if (isset($node[$field]) && is_scalar($node[$field])) {
                $v = (string) $node[$field];
                try {
                    return Carbon::parse($v)->toIso8601String();
                } catch (\Throwable $e) {
                    // Algunas fuentes usan formatos no ISO: dejar sin fecha.
                }
            }
        }
        return null;
    }
}
