<?php

namespace App\Support;

/**
 * Mercados que hoy publica la barra de AERIA.
 * Los códigos son los mismos que devuelve FinanzasArgyRatesService.
 */
final class FxMarketVisibility
{
    public const GROUPS = [
        'Oficiales' => [
            'OFICIAL' => [
                'name' => 'USD Oficial',
                'description' => 'Cotización oficial del USD: compra y venta.',
            ],
            'EUR_OFICIAL' => [
                'name' => 'EUR Oficial',
                'description' => 'Euro oficial: compra y venta.',
            ],
            'BRL_OFICIAL' => [
                'name' => 'BRL Oficial',
                'description' => 'Real brasileño oficial: compra y venta por 1 BRL.',
            ],
        ],
        'Blue' => [
            'BLUE' => [
                'name' => 'USD Blue',
                'description' => 'Cotización USD blue: compra y venta.',
            ],
            'INTERIOR' => [
                'name' => 'USD Blue Interior',
                'description' => 'Cotización USD blue del interior: compra y venta.',
            ],
            'EURO_BLUE' => [
                'name' => 'EUR Blue',
                'description' => 'Euro blue: compra y venta.',
            ],
            'REAL_BLUE' => [
                'name' => 'BRL Blue',
                'description' => 'Real brasileño blue: compra y venta.',
            ],
        ],
    ];

    public static function labels(): array
    {
        $labels = [];
        foreach (self::GROUPS as $markets) {
            foreach ($markets as $code => $details) {
                $labels[$code] = $details['name'];
            }
        }
        return $labels;
    }

    /**
     * NULL = sin configurar: mostrar los mercados actuales para no
     * cambiar lo que ven los clientes existentes al desplegar.
     * []   = el administrador eligió ocultar todas las cotizaciones.
     */
    public static function selected(?array $saved): array
    {
        $available = array_keys(self::labels());
        if ($saved === null) {
            return $available;
        }

        return array_values(array_filter(
            $available,
            static fn (string $code): bool => in_array($code, $saved, true)
        ));
    }
}
