<?php

namespace App\Support;

class ArgentineBanks
{
    public const BANKS = [
        'Banco de la Nación Argentina',
        'Banco de la Provincia de Buenos Aires',
        'Banco de la Ciudad de Buenos Aires',
        'Banco Santander Argentina',
        'Banco Galicia',
        'BBVA Argentina',
        'Banco Macro',
        'Banco Credicoop',
        'ICBC Argentina',
        'Banco Patagonia',
        'Banco Supervielle',
        'Banco Hipotecario',
        'Banco Comafi',
        'Banco de Corrientes',
        'Banco de Córdoba',
        'Banco de Entre Ríos',
        'Banco de Formosa',
        'Banco de La Pampa',
        'Banco de San Juan',
        'Banco de Santa Cruz',
        'Banco de Santa Fe',
        'Banco del Chubut',
        'Banco del Sol',
        'Banco Dino',
        'Banco Julio',
        'Banco Mariva',
        'Banco Meridian',
        'Banco Municipal de Rosario',
        'Banco Piano',
        'Banco Roela',
        'Banco Sáenz',
        'Brubank',
        'Reba',
    ];

    public static function all(): array
    {
        return self::BANKS;
    }
}