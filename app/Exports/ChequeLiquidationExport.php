<?php

namespace App\Exports;

use App\Models\ChequeLiquidation;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ChequeLiquidationExport implements Export, WithMultipleSheets
{
    protected ChequeLiquidation $liquidation;

    public function __construct(ChequeLiquidation $liquidation)
    {
        /*
        |--------------------------------------------------------------------------
        | Cargar información histórica
        |--------------------------------------------------------------------------
        |
        | Conservamos los importes, cotizaciones y
        | movimientos registrados en cada operación.
        |
        | No utilizamos cotizaciones actuales.
        |
        */

        $this->liquidation = $liquidation->loadMissing([
            'cheques',
            'deliveries.account',
            'deliveries.deliveredCheque',
            'deliveries.transaction',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Generar las tres hojas
    |--------------------------------------------------------------------------
    */

    public function sheets(): array
    {
        $liquidation = $this->liquidation;

        $statusLabels = [
            'draft' => 'Borrador',
            'held' => 'Retenida',
            'confirmed' => 'Confirmada',
            'in_portfolio' => 'En cartera',
            'delivered' => 'Entregado a tercero',
            'deposited' => 'Depositado',
            'accredited' => 'Acreditado',
            'rejected' => 'Rechazado',
        ];

        $statusName = static fn($status) =>
        $statusLabels[$status]
            ?? ucfirst((string) $status);

        $numeric = static fn($value) =>
        $value === null ? null : (float) $value;

        $percentage = static fn($value) =>
        $value === null ? null : (float) $value / 100;

        /*
        |--------------------------------------------------------------------------
        | Preparar entregas
        |--------------------------------------------------------------------------
        */

        $deliveries = $liquidation->deliveries
            ->sortBy('id')
            ->values();

        $hasDeliveries = $deliveries->isNotEmpty();

        $totalDeliveredCents = $deliveries->sum(
            fn($delivery) =>
            (int) round(
                (float) $delivery->amount_ars * 100
            )
        );

        $netCents = (int) round(
            (float) $liquidation->net_amount * 100
        );

        $pendingCents = $netCents - $totalDeliveredCents;

        /*
        |--------------------------------------------------------------------------
        | HOJA 1: RESUMEN
        |--------------------------------------------------------------------------
        */

        $summaryRows = [
            [
                'Liquidación',
                '#' . $liquidation->id,
            ],
            [
                'Cliente',
                $liquidation->client_name,
            ],
            [
                'Fecha de operación',
                $liquidation->operation_date?->format('d/m/Y'),
            ],
            [
                'Estado',
                $statusName($liquidation->status),
            ],
            [
                'Fecha de confirmación',
                $liquidation->confirmed_at?->format('d/m/Y H:i'),
            ],
            [
                'Cantidad de cheques recibidos',
                $liquidation->cheques->count(),
            ],
            [
                'Moneda de liquidación',
                'ARS',
            ],
            ['', null],

            ['RESUMEN FINANCIERO', null],

            [
                'Nominal total',
                $numeric($liquidation->nominal_amount),
            ],
            [
                'Intereses',
                $numeric($liquidation->interest_amount),
            ],
            [
                'Impuesto al cheque',
                $numeric($liquidation->cheque_tax_amount),
            ],
            [
                'Ingresos Brutos',
                $numeric($liquidation->gross_income_amount),
            ],
            [
                'IVA',
                $numeric($liquidation->vat_amount),
            ],
            [
                'Descuento total',
                $numeric($liquidation->total_discount),
            ],
            [
                'Neto a entregar',
                $numeric($liquidation->net_amount),
            ],

            ['', null],

            ['DISTRIBUCIÓN DE MEDIOS DE PAGO', null],

            [
                'Cantidad de medios utilizados',
                $hasDeliveries
                    ? $deliveries->count()
                    : null,
            ],
            [
                'Total distribuido ARS',
                $hasDeliveries
                    ? $totalDeliveredCents / 100
                    : null,
            ],
            [
                'Diferencia pendiente ARS',
                $hasDeliveries
                    ? $pendingCents / 100
                    : null,
            ],
            [
                'Detalle de entregas',
                $hasDeliveries
                    ? 'Consultar hoja Medios de pago'
                    : 'Sin desglose de entregas registrado',
            ],
        ];

        $summarySheet = new class($summaryRows)
        extends DefaultValueBinder
        implements
            FromArray,
            WithTitle,
            WithHeadings,
            WithStyles,
            WithColumnFormatting,
            WithCustomValueBinder
        {
            public function __construct(
                private array $rows
            ) {}

            public function title(): string
            {
                return 'Resumen';
            }

            public function headings(): array
            {
                return [
                    'AERIA FINANCE - LIQUIDACIÓN',
                    'Detalle',
                ];
            }

            public function array(): array
            {
                return $this->rows;
            }

            public function bindValue(
                Cell $cell,
                $value
            ): bool {
                if (is_string($value)) {
                    $cell->setValueExplicit(
                        $value,
                        DataType::TYPE_STRING
                    );

                    return true;
                }

                return parent::bindValue($cell, $value);
            }

            public function columnFormats(): array
            {
                return [
                    'B' => '#,##0.00',
                ];
            }

            public function styles(
                Worksheet $sheet
            ): array {
                $sheet->getColumnDimension('A')
                    ->setWidth(38);

                $sheet->getColumnDimension('B')
                    ->setWidth(48);

                $sheet->getRowDimension(1)
                    ->setRowHeight(30);

                /*
                 * Encabezado principal.
                 */

                $sheet->getStyle('A1:B1')
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle('A1:B1')
                    ->getFont()
                    ->getColor()
                    ->setRGB('FFFFFF');

                $sheet->getStyle('A1:B1')
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('172638');

                /*
                 * Secciones y totales.
                 *
                 * Buscamos las filas por su nombre
                 * para evitar índices rígidos.
                 */

                foreach ($this->rows as $index => $row) {
                    $excelRow = $index + 2;

                    $label = $row[0] ?? '';

                    if (in_array(
                        $label,
                        [
                            'RESUMEN FINANCIERO',
                            'DISTRIBUCIÓN DE MEDIOS DE PAGO',
                        ],
                        true
                    )) {
                        $range = "A{$excelRow}:B{$excelRow}";

                        $sheet->getStyle($range)
                            ->getFont()
                            ->setBold(true);

                        $sheet->getStyle($range)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('E2E8F0');
                    }

                    if (in_array(
                        $label,
                        [
                            'Neto a entregar',
                            'Total distribuido ARS',
                        ],
                        true
                    )) {
                        $range = "A{$excelRow}:B{$excelRow}";

                        $sheet->getStyle($range)
                            ->getFont()
                            ->setBold(true);

                        $sheet->getStyle($range)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('DCFCE7');
                    }

                    if ($label === 'Diferencia pendiente ARS') {
                        $range = "A{$excelRow}:B{$excelRow}";

                        $sheet->getStyle($range)
                            ->getFont()
                            ->setBold(true);

                        $sheet->getStyle($range)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('FEF3C7');
                    }
                }

                return [];
            }
        };

        /*
        |--------------------------------------------------------------------------
        | HOJA 2: DETALLE DE CHEQUES RECIBIDOS
        |--------------------------------------------------------------------------
        |
        | Conservamos los campos individuales y
        | sus cálculos financieros originales.
        |
        */

        $detailRows = [];

        foreach ($liquidation->cheques as $index => $cheque) {
            $detailRows[] = [
                $index + 1,

                $cheque->format === 'echeq'
                    ? 'Echeq'
                    : 'Físico',

                $cheque->bank ?? '',
                $cheque->number ?? '',
                $cheque->issuer ?? '',

                $cheque->due_date?->format('d/m/Y') ?? '',

                $statusName($cheque->status),

                $cheque->calendar_days,
                $cheque->clearing_days,
                $cheque->term_days,

                $percentage($cheque->annual_rate),
                $percentage($cheque->cheque_tax_rate),
                $percentage($cheque->gross_income_rate),
                $percentage($cheque->vat_rate),

                $numeric($cheque->nominal_amount),
                $numeric($cheque->interest_amount),
                $numeric($cheque->cheque_tax_amount),
                $numeric($cheque->gross_income_amount),
                $numeric($cheque->vat_amount),
                $numeric($cheque->total_discount),
                $numeric($cheque->net_amount),
            ];
        }

        /*
         * Totales históricos de la cabecera.
         */

        $detailRows[] = array_fill(0, 21, null);

        $totalRow = array_fill(0, 21, null);
        $totalRow[0] = 'TOTALES';

        $fields = [
            14 => 'nominal_amount',
            15 => 'interest_amount',
            16 => 'cheque_tax_amount',
            17 => 'gross_income_amount',
            18 => 'vat_amount',
            19 => 'total_discount',
            20 => 'net_amount',
        ];

        foreach ($fields as $position => $field) {
            $totalRow[$position] = (float) $liquidation->{$field};
        }

        $detailRows[] = $totalRow;

        $detailSheet = new class($detailRows)
        extends DefaultValueBinder
        implements
            FromArray,
            WithTitle,
            WithHeadings,
            WithStyles,
            WithColumnFormatting,
            WithCustomValueBinder,
            ShouldAutoSize
        {
            public function __construct(
                private array $rows
            ) {}

            public function title(): string
            {
                return 'Detalle de cheques';
            }

            public function headings(): array
            {
                return [
                    'Nº',
                    'Formato',
                    'Banco',
                    'Número de cheque',
                    'Emisor',
                    'Vencimiento',
                    'Estado',
                    'Días calendario',
                    'Clearing',
                    'Plazo computado',
                    'Tasa anual',
                    'Imp. cheque %',
                    'IIBB %',
                    'IVA %',
                    'Nominal',
                    'Intereses',
                    'Imp. cheque',
                    'Ingresos Brutos',
                    'IVA',
                    'Descuento total',
                    'Neto',
                ];
            }

            public function array(): array
            {
                return $this->rows;
            }

            public function bindValue(
                Cell $cell,
                $value
            ): bool {
                if (is_string($value)) {
                    $cell->setValueExplicit(
                        $value,
                        DataType::TYPE_STRING
                    );

                    return true;
                }

                return parent::bindValue($cell, $value);
            }

            public function columnFormats(): array
            {
                return [
                    'K' => '0.00%',
                    'L' => '0.00%',
                    'M' => '0.00%',
                    'N' => '0.00%',

                    'O' => '#,##0.00',
                    'P' => '#,##0.00',
                    'Q' => '#,##0.00',
                    'R' => '#,##0.00',
                    'S' => '#,##0.00',
                    'T' => '#,##0.00',
                    'U' => '#,##0.00',
                ];
            }

            public function styles(
                Worksheet $sheet
            ): array {
                $sheet->getRowDimension(1)
                    ->setRowHeight(30);

                $sheet->getStyle('A1:U1')
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle('A1:U1')
                    ->getFont()
                    ->getColor()
                    ->setRGB('FFFFFF');

                $sheet->getStyle('A1:U1')
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('172638');

                $lastRow = count($this->rows) + 1;

                $sheet->getStyle("A{$lastRow}:U{$lastRow}")
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle("A{$lastRow}:U{$lastRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('DCFCE7');

                $sheet->freezePane('H2');

                if ($lastRow > 3) {
                    $sheet->setAutoFilter(
                        'A1:U' . ($lastRow - 2)
                    );
                }

                return [];
            }
        };

        /*
        |--------------------------------------------------------------------------
        | HOJA 3: MEDIOS DE PAGO
        |--------------------------------------------------------------------------
        |
        | Cada fila representa una entrega real
        | o una entrega guardada pendiente.
        |
        | Se conserva:
        |
        | - Medio utilizado.
        | - Cuenta de origen.
        | - Banco de destino.
        | - Moneda e importe original.
        | - Cotización histórica.
        | - Equivalente ARS.
        | - Impuesto de transferencia.
        | - Débito monetario.
        | - Datos del cheque de terceros.
        | - Referencia a Transaction.
        |
        */

        $deliveryRows = [];

        foreach ($deliveries as $index => $delivery) {
            $transaction = $delivery->transaction;
            $cheque = $delivery->deliveredCheque;

            $typeLabel = match ($delivery->type) {
                'cash' => 'Efectivo',
                'transfer' => 'Transferencia bancaria',
                'third_party_cheque' => 'Cheque de terceros',
                default => $delivery->type,
            };

            $isMonetary = in_array(
                $delivery->type,
                ['cash', 'transfer'],
                true
            );

            /*
             * El impuesto registrado está expresado
             * en la moneda del movimiento.
             *
             * Si no existe movimiento, no inventamos
             * un impuesto histórico.
             */

            $transferTax = $transaction
                ? $numeric($transaction->transfer_tax_amount)
                : null;

            $debitAmount = $transaction
                ? (float) $transaction->amount
                + (float) (
                    $transaction->transfer_tax_amount ?? 0
                )
                : null;

            $deliveryRows[] = [
                $index + 1,

                $typeLabel,

                $isMonetary
                    ? ($delivery->account?->name ?? '')
                    : 'Cheque de cartera',

                $delivery->isTransfer()
                    ? ($delivery->notes ?? '')
                    : '',

                $delivery->currency,

                $numeric($delivery->amount),

                $delivery->currency === 'ARS'
                    ? null
                    : $numeric($delivery->exchange_rate),

                $numeric($delivery->amount_ars),

                $transferTax,

                $debitAmount,

                $cheque?->bank ?? '',

                $cheque?->number ?? '',

                $cheque?->issuer ?? '',

                $cheque?->due_date?->format('d/m/Y') ?? '',

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Totales de la hoja
        |--------------------------------------------------------------------------
        |
        | No sumamos importes en diferentes monedas.
        |
        | Solo sumamos el equivalente en ARS.
        |
        */

        if ($hasDeliveries) {
            $deliveryRows[] = array_fill(0, 14, null);

            $deliveryTotalRow = array_fill(0, 14, null);

            $deliveryTotalRow[0] = 'TOTAL ENTREGADO';

            $deliveryTotalRow[7] =
                $totalDeliveredCents / 100;

            $deliveryRows[] = $deliveryTotalRow;
        } else {
            /*
             * Para liquidaciones antiguas sin
             * desglose, no inventamos entregas.
             */

            $emptyRow = array_fill(0, 14, null);

            $emptyRow[0] =
                'Sin desglose de medios de pago registrado';

            $deliveryRows[] = $emptyRow;
        }

        $deliverySheet = new class(
            $deliveryRows,
            $hasDeliveries
        )
        extends DefaultValueBinder
        implements
            FromArray,
            WithTitle,
            WithHeadings,
            WithStyles,
            WithColumnFormatting,
            WithCustomValueBinder,
            ShouldAutoSize
        {
            public function __construct(
                private array $rows,
                private bool $hasDeliveries
            ) {}

            public function title(): string
            {
                return 'Medios de pago';
            }

            public function headings(): array
            {
                return [
                    'Nº',
                    'Medio de pago',
                    'Cuenta de origen',
                    'Banco destino',
                    'Moneda',
                    'Importe original',
                    'Cotización ARS',
                    'Equivalente ARS',
                    'Impuesto transferencia',
                    'Débito total moneda origen',
                    'Banco del cheque',
                    'Número de cheque',
                    'Emisor del cheque',
                    'Vencimiento cheque',
                ];
            }

            public function array(): array
            {
                return $this->rows;
            }

            public function bindValue(
                Cell $cell,
                $value
            ): bool {
                /*
                 * Conservar cadenas como texto.
                 *
                 * Especialmente importante para:
                 * - Números de cheques.
                 * - Referencias de movimientos.
                 * - Bancos y emisores.
                 */

                if (is_string($value)) {
                    $cell->setValueExplicit(
                        $value,
                        DataType::TYPE_STRING
                    );

                    return true;
                }

                return parent::bindValue($cell, $value);
            }

            public function columnFormats(): array
            {
                return [
                    'F' => '#,##0.00',
                    'G' => '#,##0.000000',
                    'H' => '#,##0.00',
                    'I' => '#,##0.00',
                    'J' => '#,##0.00',
                ];
            }

            public function styles(
                Worksheet $sheet
            ): array {
                /*
                 * Encabezado.
                 */

                $sheet->getRowDimension(1)
                    ->setRowHeight(32);

                $sheet->getStyle('A1:N1')
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle('A1:N1')
                    ->getFont()
                    ->getColor()
                    ->setRGB('FFFFFF');

                $sheet->getStyle('A1:N1')
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('172638');

                /*
                 * Mantener visible el encabezado.
                 */

                $sheet->freezePane('F2');

                /*
                 * Filtros de las entregas.
                 */

                if ($this->hasDeliveries) {
                    $lastDataRow = count($this->rows) - 1;

                    if ($lastDataRow >= 2) {
                        $sheet->setAutoFilter(
                            'A1:N' . $lastDataRow
                        );
                    }
                }

                /*
                 * Destacar los equivalentes en pesos.
                 */

                $lastRow = count($this->rows) + 1;

                $sheet->getStyle("H2:H{$lastRow}")
                    ->getFont()
                    ->getColor()
                    ->setRGB('15803D');

                /*
                 * Total consolidado.
                 */

                if ($this->hasDeliveries) {
                    $totalRange =
                        "A{$lastRow}:N{$lastRow}";

                    $sheet->getStyle($totalRange)
                        ->getFont()
                        ->setBold(true);

                    $sheet->getStyle($totalRange)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('DCFCE7');
                }

                return [];
            }
        };

        /*
        |--------------------------------------------------------------------------
        | Resultado
        |--------------------------------------------------------------------------
        */

        return [
            $summarySheet,
            $detailSheet,
            $deliverySheet,
        ];
    }
}
