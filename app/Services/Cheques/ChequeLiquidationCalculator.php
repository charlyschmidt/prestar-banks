<?php

namespace App\Services\Cheques;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Calcula liquidaciones de cheques de terceros.
 *
 * Admite:
 * - Un cheque mediante calculate().
 * - Varios cheques mediante calculateMultiple().
 *
 * Interés simple ACT/365.
 * Redondeo individual de cada concepto a centavos.
 *
 * Porcentajes expresados como 62, 1.2, 0.6 y 21.
 *
 * IMPORTANTE:
 * El IVA sobre intereses e impuestos es una regla
 * operativa configurable cuya validez tributaria
 * debe ser revisada por el responsable fiscal.
 */
final class ChequeLiquidationCalculator
{
    /**
     * Calcula un único cheque.
     *
     * Se conserva la interfaz y el resultado original
     * para mantener compatibilidad.
     */
    public function calculate(array $data): array
    {
        $origin = new DateTimeImmutable(
            $data['operation_date']
        );

        $due = new DateTimeImmutable(
            $data['due_date']
        );

        $calendarDays = (int) $origin
            ->diff($due)
            ->format('%r%a');

        $clearingDays = (int) $data['clearing_days'];

        $termDays = $calendarDays + $clearingDays;

        if (
            $calendarDays <= 0 ||
            $clearingDays < 0 ||
            $termDays <= 0
        ) {
            throw new InvalidArgumentException(
                'El vencimiento debe ser posterior a la operación.'
            );
        }

        $nominalCents = (int) round(
            (float) $data['nominal_amount'] * 100
        );

        if ($nominalCents <= 0) {
            throw new InvalidArgumentException(
                'El nominal debe ser mayor a cero.'
            );
        }

        $pct = static fn(string $key): float =>
            (float) $data[$key];

        foreach ([
            'annual_rate',
            'cheque_tax_rate',
            'gross_income_rate',
            'vat_rate'
        ] as $key) {
            if ($pct($key) < 0 || $pct($key) > 300) {
                throw new InvalidArgumentException(
                    'El porcentaje es inválido.'
                );
            }
        }

        // Cada concepto se redondea individualmente.

        $interestCents = (int) round(
            $nominalCents *
            $pct('annual_rate') / 100 *
            $termDays / 365
        );

        $chequeTaxCents = (int) round(
            $nominalCents *
            $pct('cheque_tax_rate') / 100
        );

        $grossIncomeCents = (int) round(
            $nominalCents *
            $pct('gross_income_rate') / 100
        );

        $vatBaseCents =
            $interestCents +
            $chequeTaxCents +
            $grossIncomeCents;

        $vatCents = (int) round(
            $vatBaseCents *
            $pct('vat_rate') / 100
        );

        $totalDiscountCents =
            $vatBaseCents + $vatCents;

        $netCents =
            $nominalCents - $totalDiscountCents;

        if ($netCents <= 0) {
            throw new InvalidArgumentException(
                'Los descuentos superan el nominal del cheque.'
            );
        }

        $money = static fn(int $cents): string =>
            number_format($cents / 100, 2, '.', '');

        return [
            'calendar_days' => $calendarDays,
            'clearing_days' => $clearingDays,
            'term_days' => $termDays,

            'annual_rate' => number_format(
                $pct('annual_rate'), 4, '.', ''
            ),

            'cheque_tax_rate' => number_format(
                $pct('cheque_tax_rate'), 4, '.', ''
            ),

            'gross_income_rate' => number_format(
                $pct('gross_income_rate'), 4, '.', ''
            ),

            'vat_rate' => number_format(
                $pct('vat_rate'), 4, '.', ''
            ),

            'nominal_amount' => $money($nominalCents),
            'interest_amount' => $money($interestCents),
            'cheque_tax_amount' => $money($chequeTaxCents),
            'gross_income_amount' => $money($grossIncomeCents),
            'vat_amount' => $money($vatCents),
            'total_discount' => $money($totalDiscountCents),
            'net_amount' => $money($netCents),
        ];
    }

    /**
     * Calcula varios cheques en una misma liquidación.
     *
     * $data contiene los parámetros generales.
     * $cheques contiene los datos individuales.
     *
     * Cada cheque puede sobrescribir:
     * - annual_rate
     * - clearing_days
     * - cheque_tax_rate
     * - gross_income_rate
     * - vat_rate
     *
     * Se devuelven resultados individuales y totales.
     */
    public function calculateMultiple(
        array $data,
        array $cheques
    ): array {
        if (count($cheques) === 0) {
            throw new InvalidArgumentException(
                'Debés agregar al menos un cheque.'
            );
        }

        if (count($cheques) > 100) {
            throw new InvalidArgumentException(
                'Una liquidación admite hasta 100 cheques.'
            );
        }

        $items = [];

        $totalsCents = [
            'nominal_amount' => 0,
            'interest_amount' => 0,
            'cheque_tax_amount' => 0,
            'gross_income_amount' => 0,
            'vat_amount' => 0,
            'total_discount' => 0,
            'net_amount' => 0,
        ];

        foreach ($cheques as $index => $cheque) {
            if (!is_array($cheque)) {
                throw new InvalidArgumentException(
                    'Los datos de uno de los cheques son inválidos.'
                );
            }

            /*
             * Se combinan los parámetros generales
             * con los particulares de cada cheque.
             *
             * Los particulares tienen prioridad.
             */
            $chequeData = array_merge(
                $data,
                $cheque
            );

            try {
                $result = $this->calculate($chequeData);
            } catch (InvalidArgumentException $error) {
                throw new InvalidArgumentException(
                    'Cheque ' . ($index + 1) . ': ' .
                    $error->getMessage()
                );
            }

            /*
             * Conservamos la información identificatoria
             * junto con el cálculo individual.
             */
            $items[] = array_merge(
                [
                    'format' => $chequeData['format']
                        ?? 'paper',

                    'bank' => $chequeData['bank']
                        ?? null,

                    'issuer' => $chequeData['issuer']
                        ?? null,

                    'number' => $chequeData['number']
                        ?? null,

                    'due_date' => $chequeData['due_date'],
                ],
                $result
            );

            /*
             * Sumamos en centavos para evitar acumular
             * errores de precisión de punto flotante.
             */
            foreach ($totalsCents as $field => $total) {
                $totalsCents[$field] += (int) round(
                    (float) $result[$field] * 100
                );
            }
        }

        $money = static fn(int $cents): string =>
            number_format($cents / 100, 2, '.', '');

        $totals = [];

        foreach ($totalsCents as $field => $cents) {
            $totals[$field] = $money($cents);
        }

        if ($totalsCents['net_amount'] <= 0) {
            throw new InvalidArgumentException(
                'El neto total de la liquidación debe ser mayor a cero.'
            );
        }

        return [
            'cheque_count' => count($items),

            'items' => $items,

            'totals' => $totals,
        ];
    }
}
