<?php

namespace App\Support;

class ArgentineBanks
{
    public const BANKS = [

        'Banco Comafi',

        'Banco Credicoop',

        'Banco de Córdoba',

        'Banco de Corrientes',

        'Banco de Entre Ríos',

        'Banco de Formosa',

        'Banco de La Pampa',

        'Banco de la Ciudad de Buenos Aires',

        'Banco de la Nación Argentina',

        'Banco de la Provincia de Buenos Aires',
	 'Banco de la Provincia de Neuquen',

        'Banco de San Juan',

        'Banco de Santa Cruz',

        'Banco de Santa Fe',

        'Banco del Chubut',

        'Banco del Sol',

        'Banco Dino',

        'Banco Galicia',

        'Banco Hipotecario',

        'Banco Julio',

        'Banco Macro',

        'Banco Mariva',

        'Banco Meridian',

        'Banco Municipal de Rosario',

        'Banco Patagonia',

        'Banco Piano',

        'Banco Roela',

        'Banco Sáenz',

        'Banco Santander Argentina',

        'Banco Santiago del Estero',

        'Banco Supervielle',

        'BBVA Argentina',

	'Bibank S.A.',

        'Brubank',

        'Cuenta Virtual',

        'ICBC Argentina',

        'Reba',

    ];
    
    public static function all(): array
    {
        return self::BANKS;
    }
}
