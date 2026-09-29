<?php

namespace App\Services;

use InvalidArgumentException;

class SubscriptionPlanService
{
    public const MONTHLY = 'monthly';
    public const ANNUAL = 'annual';

    private const PLANS = [

        self::MONTHLY => [
            'name' => 'Mensual',
            'price_usd' => 99.00,
            'billing_months' => 1,
        ],

        self::ANNUAL => [
            'name' => 'Anual',
            'price_usd' => 990.00,
            'billing_months' => 12,
        ],

    ];

    /*
    |--------------------------------------------------------------------------
    | Todos los planes
    |--------------------------------------------------------------------------
    */

    public function all(): array
    {
        return self::PLANS;
    }


    /*
    |--------------------------------------------------------------------------
    | Obtener un plan
    |--------------------------------------------------------------------------
    */

    public function get(string $plan): array
    {
        if (!isset(self::PLANS[$plan])) {
            throw new InvalidArgumentException(
                "Plan de suscripción inválido: {$plan}"
            );
        }

        return self::PLANS[$plan];
    }


    /*
    |--------------------------------------------------------------------------
    | Precio USD
    |--------------------------------------------------------------------------
    */

    public function price(string $plan): float
    {
        return (float) $this->get($plan)['price_usd'];
    }


    /*
    |--------------------------------------------------------------------------
    | Cantidad de meses
    |--------------------------------------------------------------------------
    */

    public function billingMonths(string $plan): int
    {
        return (int) $this->get($plan)['billing_months'];
    }


    /*
    |--------------------------------------------------------------------------
    | Validar plan
    |--------------------------------------------------------------------------
    */

    public function exists(string $plan): bool
    {
        return isset(self::PLANS[$plan]);
    }
}