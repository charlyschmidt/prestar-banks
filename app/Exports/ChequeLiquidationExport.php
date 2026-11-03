<?php

namespace App\Exports;

use App\Models\ChequeLiquidation;
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
use Maatwebsite\Excel\Concerns\Export;

class ChequeLiquidationExport implements Export, WithMultipleSheets
{
    protected ChequeLiquidation $liquidation;

    public function __construct(ChequeLiquidation $liquidation)
    {
        $this->liquidation = $liquidation->loadMissing('cheques');
    }

    /*
    |--------------------------------------------------------------------------
    | HOJAS DEL EXCEL
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
            'deposited' => 'Depositado',
            'accredited' => 'Acreditado',
            'rejected' => 'Rechazado',
        ];

        $statusName = static fn($status) =>
            $statusLabels[$status] ?? ucfirst((string) $status);

        /*
        |--------------------------------------------------------------------------
        | HOJA 1: RESUMEN
        |--------------------------------------------------------------------------
        */

        $summaryRows = [
            ['Liquidación', '#' . $liquidation->id],
            ['Cliente', $liquidation->client_name],
            [
                'Fecha de operación',
                $liquidation->operation_date?->format('d/m/Y'),
            ],
            ['Estado', $statusName($liquidation->status)],
            ['Cantidad de cheques', $liquidation->cheques->count()],
            ['Moneda', 'ARS'],
            ['', null],
            ['RESUMEN FINANCIERO', null],
            ['Nominal total', (float) $liquidation->nominal_amount],
            ['Intereses', (float) $liquidation->interest_amount],
            ['Impuesto al cheque', (float) $liquidation->cheque_tax_amount],
            ['Ingresos Brutos', (float) $liquidation->gross_income_amount],
            ['IVA', (float) $liquidation->vat_amount],
            ['Descuento total', (float) $liquidation->total_discount],
            ['Neto a entregar', (float) $liquidation->net_amount],
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

            public function styles(Worksheet $sheet): array
            {
                $sheet->getColumnDimension('A')
                    ->setWidth(34);

                $sheet->getColumnDimension('B')
                    ->setWidth(42);

                $sheet->getRowDimension(1)
                    ->setRowHeight(30);

                $sheet->getStyle('A1:B1')
                    ->getFont()
                    ->getColor()
                    ->setRGB('FFFFFF');

                $sheet->getStyle('A1:B1')
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('172638');

                $sheet->getStyle('A1:B1')
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle('A9:B9')
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle('A15:B16')
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle('A16:B16')
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('DCFCE7');

                return [];
            }
        };

        /*
        |--------------------------------------------------------------------------
        | HOJA 2: DETALLE INDIVIDUAL
        |--------------------------------------------------------------------------
        */

        $detailRows = [];

        foreach ($liquidation->cheques as $index => $cheque) {

            /*
             * Los valores NULL de cheques antiguos
             * se conservan como celdas vacías.
             */

            $numeric = static fn($value) =>
                $value === null ? null : (float) $value;

            $percentage = static fn($value) =>
                $value === null ? null : (float) $value / 100;

            $detailRows[] = [
                $index + 1,
                $cheque->format === 'echeq' ? 'Echeq' : 'Físico',
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
         * Los totales provienen de la cabecera
         * consolidada de la liquidación.
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

            public function styles(Worksheet $sheet): array
            {
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

        return [
            $summarySheet,
            $detailSheet,
        ];
    }
}
