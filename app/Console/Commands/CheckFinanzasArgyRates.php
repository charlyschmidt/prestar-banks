<?php

namespace App\Console\Commands;

use App\Services\FinanzasArgyRatesService;
use Illuminate\Console\Command;

/** Prueba de integración: imprime datos públicos, no tokens ni secretos. */
class CheckFinanzasArgyRates extends Command
{
    protected $signature = 'aeria:rates:check';
    protected $description = 'Comprueba compra y venta de las siete cotizaciones Finanzas Argy';

    public function handle(FinanzasArgyRatesService $service): int
    {
        $data = $service->refresh();
        $rows = [];
        $complete = true;

        foreach ([
            'OFICIAL' => 'USD Oficial',
            'EUR_OFICIAL' => 'EUR Oficial',
            'BRL_OFICIAL' => 'BRL Oficial',
            'BLUE' => 'USD Blue',
            'INTERIOR' => 'USD Blue Interior',
            'EURO_BLUE' => 'EUR Blue',
            'REAL_BLUE' => 'BRL Blue',
        ] as $code => $label) {
            $quote = $data['rates'][$code] ?? null;
            $okay = is_array($quote)
                && ($quote['stale'] ?? true) === false
                && isset($quote['buy'], $quote['sell']);

            $complete = $complete && $okay;

            $rows[] = [
                $label,
                $quote['buy'] ?? '—',
                $quote['sell'] ?? '—',
                $okay ? 'OK' : 'SIN CONFIRMAR',
            ];
        }

        $this->table(['Mercado', 'Compra', 'Venta', 'Estado'], $rows);

        if (!$complete) {
            $this->error('No publicar hasta verificar el esquema JSON de Finanzas Argy y ajustar el parser.');
            return self::FAILURE;
        }

        $this->info('Las siete cotizaciones fueron leídas y validadas.');
        return self::SUCCESS;
    }
}
