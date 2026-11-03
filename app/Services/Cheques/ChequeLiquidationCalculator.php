<?php

namespace App\Services\Cheques;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Calcula una liquidación de un cheque de tercero.
 * Los porcentajes se expresan como 62, 1.2, 0.6 y 21.
 * Reproduce el ejemplo Prestar usando interés simple ACT/365 y redondeo a centavos.
 * El IVA sobre intereses e impuestos es SOLO una regla operativa configurable:
 * su validez tributaria debe ser revisada por el responsable fiscal.
 */
final class ChequeLiquidationCalculator
{
    public function calculate(array $data): array
    {
        $origin = new DateTimeImmutable($data['operation_date']);
        $due = new DateTimeImmutable($data['due_date']);
        $calendarDays = (int) $origin->diff($due)->format('%r%a');
        $clearingDays = (int) $data['clearing_days'];
        $termDays = $calendarDays + $clearingDays;

        if ($calendarDays <= 0 || $clearingDays < 0 || $termDays <= 0) {
            throw new InvalidArgumentException('El vencimiento debe ser posterior a la operación.');
        }

        $nominalCents = (int) round((float) $data['nominal_amount'] * 100);
        if ($nominalCents <= 0) {
            throw new InvalidArgumentException('El nominal debe ser mayor a cero.');
        }

        $pct = static fn(string $key): float => (float) $data[$key];
        foreach (['annual_rate','cheque_tax_rate','gross_income_rate','vat_rate'] as $key) {
            if ($pct($key) < 0 || $pct($key) > 300) {
                throw new InvalidArgumentException('El porcentaje es inválido.');
            }
        }

        // Se redondea cada concepto ANTES de calcular el IVA y el neto.
        $interestCents = (int) round($nominalCents * $pct('annual_rate') / 100 * $termDays / 365);
        $chequeTaxCents = (int) round($nominalCents * $pct('cheque_tax_rate') / 100);
        $grossIncomeCents = (int) round($nominalCents * $pct('gross_income_rate') / 100);
        $vatBaseCents = $interestCents + $chequeTaxCents + $grossIncomeCents;
        $vatCents = (int) round($vatBaseCents * $pct('vat_rate') / 100);
        $totalDiscountCents = $vatBaseCents + $vatCents;
        $netCents = $nominalCents - $totalDiscountCents;

        if ($netCents <= 0) {
            throw new InvalidArgumentException('Los descuentos superan el nominal del cheque.');
        }

        $money = static fn(int $cents): string => number_format($cents / 100, 2, '.', '');

        return [
            'calendar_days' => $calendarDays,
            'clearing_days' => $clearingDays,
            'term_days' => $termDays,
            'annual_rate' => number_format($pct('annual_rate'), 4, '.', ''),
            'cheque_tax_rate' => number_format($pct('cheque_tax_rate'), 4, '.', ''),
            'gross_income_rate' => number_format($pct('gross_income_rate'), 4, '.', ''),
            'vat_rate' => number_format($pct('vat_rate'), 4, '.', ''),
            'nominal_amount' => $money($nominalCents),
            'interest_amount' => $money($interestCents),
            'cheque_tax_amount' => $money($chequeTaxCents),
            'gross_income_amount' => $money($grossIncomeCents),
            'vat_amount' => $money($vatCents),
            'total_discount' => $money($totalDiscountCents),
            'net_amount' => $money($netCents),
        ];
    }
}
