<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use DOMDocument;
use DOMXPath;

class BankHolidayService
{
    private const CACHE_PREFIX = 'aeria:bank-holidays:';

    /*
    |--------------------------------------------------------------------------
    | Fuentes oficiales
    |--------------------------------------------------------------------------
    |
    | Solo se utilizan páginas verificadas del BCRA.
    | Incorporaremos cada año al publicarse.
    |
    | No suponemos que una URL futura exista.
    |
    */

    private const SOURCES = [
        2026 => 'https://www.bcra.gob.ar/consulta-feriados-bancarios-2026/',
    ];

    /*
    |--------------------------------------------------------------------------
    | Meses en español
    |--------------------------------------------------------------------------
    */

    private const MONTHS = [
        'ene' => 1,
        'feb' => 2,
        'mar' => 3,
        'abr' => 4,
        'may' => 5,
        'jun' => 6,
        'jul' => 7,
        'ago' => 8,
        'sep' => 9,
        'oct' => 10,
        'nov' => 11,
        'dic' => 12,
    ];

    /*
    |--------------------------------------------------------------------------
    | Consultar feriados
    |--------------------------------------------------------------------------
    |
    | Devuelve fechas YYYY-MM-DD.
    |
    | Utiliza caché compartida para evitar
    | consultar el BCRA por cada cheque.
    |
    */

    public function holidays(int $year): array
    {
        if (!isset(self::SOURCES[$year])) {
            throw new RuntimeException(
                "No hay una fuente BCRA verificada para {$year}."
            );
        }

        return Cache::remember(
            self::CACHE_PREFIX . $year,
            now()->addHours(12),
            fn() => $this->fetchYear($year)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Actualizar un año
    |--------------------------------------------------------------------------
    |
    | Solo reemplaza la caché después
    | de una descarga y validación exitosas.
    |
    */

    public function refresh(int $year): array
    {
        $dates = $this->fetchYear($year);

        Cache::put(
            self::CACHE_PREFIX . $year,
            $dates,
            now()->addHours(12)
        );

        return $dates;
    }

    /*
    |--------------------------------------------------------------------------
    | Estado del calendario
    |--------------------------------------------------------------------------
    */

    public function isSupported(int $year): bool
    {
        return isset(self::SOURCES[$year]);
    }

    /*
    |--------------------------------------------------------------------------
    | Determinar día hábil
    |--------------------------------------------------------------------------
    */

    public function isBusinessDay(
        CarbonImmutable $date
    ): bool {
        $dates = $this->holidays($date->year);

        return !$date->isWeekend()
            && !in_array(
                $date->format('Y-m-d'),
                $dates,
                true
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Próximo día hábil
    |--------------------------------------------------------------------------
    |
    | Incluye la fecha de partida.
    |
    | Si el calendario no está confirmado,
    | no devuelve una fecha inventada.
    |
    */

    public function nextBusinessDay(
        CarbonImmutable $date
    ): CarbonImmutable {
        $result = $date;

        for ($attempt = 0; $attempt < 370; $attempt++) {

            if ($this->isBusinessDay($result)) {
                return $result;
            }

            $result = $result->addDay();
        }

        throw new RuntimeException(
            'No fue posible determinar el próximo día hábil.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Restar días hábiles
    |--------------------------------------------------------------------------
    */

    public function subtractBusinessDays(
        CarbonImmutable $date,
        int $days
    ): CarbonImmutable {
        if ($days < 0) {
            throw new RuntimeException(
                'La cantidad de días no puede ser negativa.'
            );
        }

        $result = $date;
        $count = 0;

        for ($attempt = 0; $attempt < 370; $attempt++) {

            if ($count >= $days) {
                return $result;
            }

            $result = $result->subDay();

            if ($this->isBusinessDay($result)) {
                $count++;
            }
        }

        throw new RuntimeException(
            'No fue posible calcular los días hábiles.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Descargar calendario oficial
    |--------------------------------------------------------------------------
    */

    private function fetchYear(int $year): array
    {
        $response = Http::timeout(15)
            ->retry(2, 500)
            ->get(self::SOURCES[$year]);

        if (!$response->successful()) {
            throw new RuntimeException(
                "No se pudo consultar el calendario BCRA {$year}."
            );
        }

        $html = $response->body();

        /*
         * El BCRA publica una tabla HTML.
         * No asumimos que un resultado vacío
         * representa un calendario válido.
         */

        $previous = libxml_use_internal_errors(true);

        try {
            $document = new DOMDocument();

            if (!$document->loadHTML(
                '<?xml encoding="UTF-8">' . $html
            )) {
                throw new RuntimeException(
                    'No fue posible interpretar la página del BCRA.'
                );
            }

            $xpath = new DOMXPath($document);

            $rows = $xpath->query(
                '//table//tr'
            );

            $dates = [];

            foreach ($rows as $row) {

                $cells = $xpath->query(
                    './td',
                    $row
                );

                if (!$cells || $cells->length < 2) {
                    continue;
                }

                $value = trim(
                    preg_replace(
                        '/\s+/u',
                        ' ',
                        $cells->item(0)->textContent
                    )
                );

                $date = $this->parseDate(
                    $value,
                    $year
                );

                if ($date !== null) {
                    $dates[] = $date;
                }
            }

        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        $dates = array_values(
            array_unique($dates)
        );

        sort($dates);

        /*
         * Protección frente a cambios del HTML.
         *
         * Un calendario anual con muy pocas fechas
         * podría indicar que el parser dejó de funcionar.
         */

        if (count($dates) < 10) {
            throw new RuntimeException(
                "El calendario BCRA {$year} no superó la validación."
            );
        }

        return $dates;
    }

    /*
    |--------------------------------------------------------------------------
    | Interpretar fecha del HTML
    |--------------------------------------------------------------------------
    |
    | Formato esperado:
    |
    | 01 ene 2026
    | 7 dic 2026
    |
    */

    private function parseDate(
        string $value,
        int $year
    ): ?string {

        $normalized = mb_strtolower(
            trim($value),
            'UTF-8'
        );

        if (!preg_match(
            '/^(\d{1,2})\s+([a-záéíóú]{3,})\.?\s+(\d{4})$/u',
            $normalized,
            $matches
        )) {
            return null;
        }

        $day = (int) $matches[1];
        $month = mb_substr(
            $matches[2],
            0,
            3,
            'UTF-8'
        );
        $parsedYear = (int) $matches[3];

        if (
            $parsedYear !== $year ||
            !isset(self::MONTHS[$month])
        ) {
            return null;
        }

        $monthNumber = self::MONTHS[$month];

        if (!checkdate($monthNumber, $day, $year)) {
            return null;
        }

        return sprintf(
            '%04d-%02d-%02d',
            $year,
            $monthNumber,
            $day
        );
    }
}
