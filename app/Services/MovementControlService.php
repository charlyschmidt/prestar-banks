<?php

namespace App\Services;

use App\Imports\BankStatementImport;
use App\Models\AccountBalance;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;

class MovementControlService
{
    /**
     * Lee un extracto bancario y devuelve una vista previa
     * de su estructura.
     *
     * En esta etapa NO:
     * - crea movimientos
     * - modifica movimientos
     * - compara contra AERIA
     * - guarda el archivo
     *
     * Solamente lee el archivo para poder mapear sus columnas.
     */
    public function readStatement(UploadedFile $file): array
    {
        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        if (!in_array($extension, ['csv', 'xlsx', 'xls'], true)) {
            throw new RuntimeException(
                'El formato del extracto no es compatible.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Leer archivo
        |--------------------------------------------------------------------------
        |
        | toArray() devuelve:
        |
        | [
        |     0 => [ // primera hoja
        |         0 => [...],
        |         1 => [...],
        |         ...
        |     ]
        | ]
        |
        */

        $sheets = Excel::toArray(
            new BankStatementImport(),
            $file
        );

        if (empty($sheets) || empty($sheets[0])) {
            throw new RuntimeException(
                'El extracto está vacío.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Primera hoja
        |--------------------------------------------------------------------------
        |
        | Para la primera versión trabajamos únicamente con la primera hoja.
        |
        */

        $rows = collect($sheets[0])
            ->map(function ($row) {
                return is_array($row)
                    ? array_values($row)
                    : [];
            })
            ->filter(function ($row) {
                return $this->rowHasContent($row);
            })
            ->values();


        if ($rows->isEmpty()) {
            throw new RuntimeException(
                'No se encontraron datos en el extracto.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Detectar fila de encabezados
        |--------------------------------------------------------------------------
        |
        | No asumimos que la fila 1 contiene los encabezados.
        |
        | Algunos bancos exportan:
        |
        | Cuenta: XXXXX
        | Período: XXXXX
        |
        | Fecha | Descripción | Importe | Saldo
        |
        | Buscamos automáticamente una fila razonable.
        |
        */

        $headerIndex = $this->detectHeaderRow($rows);


        if ($headerIndex === null) {
            throw new RuntimeException(
                'No se pudo detectar la fila de encabezados del extracto.'
            );
        }


        $headers = $this->normalizeHeaders(
            $rows->get($headerIndex)
        );


        /*
        |--------------------------------------------------------------------------
        | Filas posteriores al encabezado
        |--------------------------------------------------------------------------
        */

        $dataRows = $rows
            ->slice($headerIndex + 1)
            ->filter(function ($row) {
                return $this->rowHasContent($row);
            })
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Preview
        |--------------------------------------------------------------------------
        |
        | Sólo mandamos algunas filas a la pantalla de mapeo.
        | No necesitamos renderizar un extracto de miles de movimientos.
        |
        */

        $preview = $dataRows
            ->take(5)
            ->map(function ($row) use ($headers) {

                $result = [];

                foreach ($headers as $index => $header) {
                    $result[$index] = $row[$index] ?? null;
                }

                return $result;
            })
            ->values()
            ->all();


        return [

            'filename' => $file->getClientOriginalName(),

            'extension' => $extension,

            'header_row' => $headerIndex,

            'headers' => $headers,

            'preview' => $preview,

            'rows_count' => $dataRows->count(),
        ];
    }

    public function storeTemporaryStatement(UploadedFile $file): string
    {
        return $file->store(
            'movement-control/' . auth()->id(),
            'local'
        );
    }

    /**
     * Detecta qué fila parece contener los nombres de columnas.
     */
    private function detectHeaderRow(Collection $rows): ?int
    {
        /*
        |--------------------------------------------------------------------------
        | Sólo analizamos las primeras 20 filas
        |--------------------------------------------------------------------------
        |
        | Si un banco tiene metadata antes de la tabla, normalmente estará
        | dentro de este rango.
        |
        */

        $candidates = $rows->take(20);

        $bestIndex = null;
        $bestScore = 0;


        foreach ($candidates as $index => $row) {

            $values = collect($row)
                ->map(function ($value) {
                    return trim((string) $value);
                })
                ->filter(function ($value) {
                    return $value !== '';
                });


            /*
            |--------------------------------------------------------------------------
            | Una cabecera bancaria razonable debería tener varias columnas
            |--------------------------------------------------------------------------
            */

            if ($values->count() < 2) {
                continue;
            }


            $score = 0;


            foreach ($values as $value) {

                /*
                |--------------------------------------------------------------------------
                | Texto
                |--------------------------------------------------------------------------
                |
                | Los encabezados suelen ser texto:
                |
                | Fecha
                | Descripción
                | Concepto
                | Importe
                | Saldo
                |
                */

                if (!is_numeric($value)) {
                    $score += 1;
                }


                /*
                |--------------------------------------------------------------------------
                | Palabras habituales
                |--------------------------------------------------------------------------
                |
                | Esto ayuda a detectar la cabecera sin exigir nombres exactos.
                |
                */

                $normalized = mb_strtolower($value);


                $keywords = [
                    'fecha',
                    'descripcion',
                    'descripción',
                    'concepto',
                    'detalle',
                    'movimiento',
                    'importe',
                    'monto',
                    'debito',
                    'débito',
                    'credito',
                    'crédito',
                    'saldo',
                ];


                foreach ($keywords as $keyword) {

                    if (str_contains($normalized, $keyword)) {

                        $score += 3;

                        break;
                    }
                }
            }


            if ($score > $bestScore) {

                $bestScore = $score;

                $bestIndex = $index;
            }
        }


        return $bestIndex;
    }


    /**
     * Normaliza los nombres de columnas sin perder
     * el nombre original que ve el usuario.
     */
    private function normalizeHeaders(array $row): array
    {
        $headers = [];


        foreach ($row as $index => $value) {

            $header = trim((string) $value);


            /*
            |--------------------------------------------------------------------------
            | Columna sin nombre
            |--------------------------------------------------------------------------
            */

            if ($header === '') {

                $header = 'Columna ' . ($index + 1);
            }


            /*
            |--------------------------------------------------------------------------
            | Encabezados duplicados
            |--------------------------------------------------------------------------
            |
            | Algunos extractos pueden tener:
            |
            | Importe | Importe
            |
            | Para la interfaz necesitamos distinguirlos.
            |
            */

            $original = $header;

            $counter = 2;


            while (in_array($header, $headers, true)) {

                $header = $original . ' (' . $counter . ')';

                $counter++;
            }


            $headers[] = $header;
        }


        return $headers;
    }


    /**
     * Determina si una fila contiene algún dato real.
     */
    private function rowHasContent(array $row): bool
    {
        foreach ($row as $value) {

            if (
                $value !== null &&
                trim((string) $value) !== ''
            ) {
                return true;
            }
        }


        return false;
    }

    public function compare(
        string $temporaryPath,
        AccountBalance $accountBalance,
        array $mapping
    ): array {

        /*
    |--------------------------------------------------------------------------
    | Validar archivo temporal
    |--------------------------------------------------------------------------
    */

        if (!Storage::disk('local')->exists($temporaryPath)) {
            throw new RuntimeException(
                'El archivo temporal del extracto ya no está disponible.'
            );
        }


        /*
    |--------------------------------------------------------------------------
    | Leer nuevamente el extracto
    |--------------------------------------------------------------------------
    */

        $fullPath = Storage::disk('local')->path($temporaryPath);

        $sheets = Excel::toArray(
            new BankStatementImport(),
            $fullPath
        );


        if (empty($sheets) || empty($sheets[0])) {
            throw new RuntimeException(
                'No se pudieron leer los movimientos del extracto.'
            );
        }


        /*
    |--------------------------------------------------------------------------
    | Preparar filas
    |--------------------------------------------------------------------------
    */

        $rows = collect($sheets[0])
            ->map(function ($row) {
                return is_array($row)
                    ? array_values($row)
                    : [];
            })
            ->filter(function ($row) {
                return $this->rowHasContent($row);
            })
            ->values();


        if ($rows->isEmpty()) {
            throw new RuntimeException(
                'El extracto no contiene movimientos.'
            );
        }


        /*
    |--------------------------------------------------------------------------
    | Detectar nuevamente encabezados
    |--------------------------------------------------------------------------
    */

        $headerIndex = $this->detectHeaderRow($rows);

        if ($headerIndex === null) {
            throw new RuntimeException(
                'No se pudo detectar la fila de encabezados.'
            );
        }


        $dataRows = $rows
            ->slice($headerIndex + 1)
            ->values();


        /*
    |--------------------------------------------------------------------------
    | Normalizar movimientos bancarios
    |--------------------------------------------------------------------------
    */

        $bankMovements = collect();


        foreach ($dataRows as $row) {

            try {

                $date = $this->normalizeDate(
                    $row[$mapping['date']] ?? null
                );

                $signedAmount = $this->normalizeAmount(
                    $row[$mapping['amount']] ?? null
                );
            } catch (\Throwable $e) {

                /*
             * Si una fila no tiene una fecha o importe válido,
             * no la consideramos movimiento.
             */

                continue;
            }


            /*
         * Importe cero no representa un movimiento útil
         * para la conciliación.
         */

            if (abs($signedAmount) < 0.00001) {
                continue;
            }


            $type = $signedAmount < 0
                ? 'expense'
                : 'income';


            $description = null;

            if ($mapping['description'] !== null) {

                $description = trim(
                    (string) (
                        $row[$mapping['description']] ?? ''
                    )
                );

                if ($description === '') {
                    $description = null;
                }
            }


            $balance = null;

            if ($mapping['balance'] !== null) {

                $rawBalance =
                    $row[$mapping['balance']] ?? null;

                if (
                    $rawBalance !== null &&
                    trim((string) $rawBalance) !== ''
                ) {

                    try {

                        $balance = $this->normalizeAmount(
                            $rawBalance
                        );
                    } catch (\Throwable $e) {

                        $balance = null;
                    }
                }
            }


            $bankMovements->push([
                'date' => $date,
                'type' => $type,
                'amount' => abs($signedAmount),
                'description' => $description,
                'balance' => $balance,
            ]);
        }


        if ($bankMovements->isEmpty()) {
            throw new RuntimeException(
                'No se encontraron movimientos válidos para comparar.'
            );
        }


        /*
    |--------------------------------------------------------------------------
    | Período real contenido en el extracto
    |--------------------------------------------------------------------------
    */

        $dateFrom = $bankMovements
            ->min('date');

        $dateTo = $bankMovements
            ->max('date');


        /*
    |--------------------------------------------------------------------------
    | Movimientos AERIA
    |--------------------------------------------------------------------------
    |
    | IMPORTANTE:
    |
    | No usamos la jornada actual.
    |
    | Buscamos los movimientos de esta cuenta/moneda
    | dentro del período que realmente contiene el extracto.
    |--------------------------------------------------------------------------
    */

        $aeriaMovements = Transaction::query()
            ->where('account_id', $accountBalance->account_id)
            ->where('account_balance_id', $accountBalance->id)
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->whereIn('type', [
                'income',
                'expense',
            ])
            ->orderBy('date')
            ->orderBy('id')
            ->get();


        /*
    |--------------------------------------------------------------------------
    | Preparar movimientos AERIA
    |--------------------------------------------------------------------------
    */

        $availableAeria = $aeriaMovements
            ->map(function ($transaction) {

                return [
                    'id' => $transaction->id,

                    'date' => Carbon::parse(
                        $transaction->date
                    )->format('Y-m-d'),

                    'type' => $transaction->type,

                    'amount' => round(
                        (float) $transaction->amount,
                        2
                    ),

                    'description' =>
                    $transaction->description,

                    'matched' => false,
                ];
            })
            ->values()
            ->all();


        /*
    |--------------------------------------------------------------------------
    | Matching uno a uno
    |--------------------------------------------------------------------------
    |
    | Clave:
    |
    | fecha + tipo + importe
    |
    | Cada movimiento de AERIA puede utilizarse una sola vez.
    |--------------------------------------------------------------------------
    */

        $matched = [];

        $missingInAeria = [];


        foreach ($bankMovements as $bankMovement) {

            $matchedIndex = null;


            foreach ($availableAeria as $index => $aeriaMovement) {

                if ($aeriaMovement['matched']) {
                    continue;
                }


                if (
                    $aeriaMovement['date']
                    !== $bankMovement['date']
                ) {
                    continue;
                }


                if (
                    $aeriaMovement['type']
                    !== $bankMovement['type']
                ) {
                    continue;
                }


                if (
                    abs(
                        $aeriaMovement['amount']
                            - $bankMovement['amount']
                    ) > 0.009
                ) {
                    continue;
                }


                $matchedIndex = $index;

                break;
            }


            /*
         * Encontrado
         */

            if ($matchedIndex !== null) {

                $availableAeria[$matchedIndex]['matched'] = true;


                $matched[] = [
                    'bank' => $bankMovement,
                    'aeria' => $availableAeria[$matchedIndex],
                ];


                continue;
            }


            /*
         * Está en el banco pero no en AERIA
         */

            $missingInAeria[] = $bankMovement;
        }


        /*
    |--------------------------------------------------------------------------
    | Está en AERIA pero no en el banco
    |--------------------------------------------------------------------------
    */

        $onlyInAeria = collect($availableAeria)
            ->filter(
                fn($movement) =>
                !$movement['matched']
            )
            ->map(function ($movement) {

                unset($movement['matched']);

                return $movement;
            })
            ->values()
            ->all();


        /*
    |--------------------------------------------------------------------------
    | Resultado
    |--------------------------------------------------------------------------
    */

        return [

            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],

            'summary' => [
                'bank_total' =>
                $bankMovements->count(),

                'aeria_total' =>
                $aeriaMovements->count(),

                'matched' =>
                count($matched),

                'missing_in_aeria' =>
                count($missingInAeria),

                'only_in_aeria' =>
                count($onlyInAeria),
            ],

            'matched' => $matched,

            'missing_in_aeria' =>
            $missingInAeria,

            'only_in_aeria' =>
            $onlyInAeria,
        ];
    }


    private function normalizeDate(mixed $value): string
    {
        if ($value === null || $value === '') {
            throw new RuntimeException(
                'Fecha vacía.'
            );
        }


        /*
    |--------------------------------------------------------------------------
    | Fecha serial de Excel
    |--------------------------------------------------------------------------
    */

        if (is_numeric($value)) {

            return Carbon::instance(
                ExcelDate::excelToDateTimeObject(
                    (float) $value
                )
            )->format('Y-m-d');
        }


        $value = trim((string) $value);


        /*
    |--------------------------------------------------------------------------
    | Formatos habituales
    |--------------------------------------------------------------------------
    */

        $formats = [
            'd/m/Y',
            'd-m-Y',
            'Y-m-d',
            'd/m/y',
            'd-m-y',
        ];


        foreach ($formats as $format) {

            try {

                $date = Carbon::createFromFormat(
                    '!' . $format,
                    $value
                );


                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                //
            }
        }


        throw new RuntimeException(
            'Formato de fecha no reconocido.'
        );
    }


    private function normalizeAmount(mixed $value): float
    {
        if ($value === null || $value === '') {
            throw new RuntimeException(
                'Importe vacío.'
            );
        }


        /*
     * Excel puede devolver directamente números.
     */

        if (
            is_int($value) ||
            is_float($value)
        ) {

            return round(
                (float) $value,
                2
            );
        }


        $value = trim((string) $value);


        if ($value === '') {
            throw new RuntimeException(
                'Importe vacío.'
            );
        }


        /*
     * Limpiar símbolos y espacios.
     *
     * Conservamos:
     *
     * números
     * coma
     * punto
     * signo negativo
     */

        $value = preg_replace(
            '/[^\d,\.\-]/u',
            '',
            $value
        );


        if (
            $value === null ||
            $value === '' ||
            $value === '-'
        ) {
            throw new RuntimeException(
                'Importe inválido.'
            );
        }


        /*
    |--------------------------------------------------------------------------
    | Formato argentino
    |--------------------------------------------------------------------------
    |
    | 35.000,00
    | -35.000,00
    |
    |--------------------------------------------------------------------------
    */

        if (
            str_contains($value, ',') &&
            str_contains($value, '.')
        ) {

            $lastComma = strrpos(
                $value,
                ','
            );

            $lastDot = strrpos(
                $value,
                '.'
            );


            if ($lastComma > $lastDot) {

                $value = str_replace(
                    '.',
                    '',
                    $value
                );

                $value = str_replace(
                    ',',
                    '.',
                    $value
                );
            } else {

                /*
             * Ej:
             * 35,000.00
             */

                $value = str_replace(
                    ',',
                    '',
                    $value
                );
            }
        } elseif (str_contains($value, ',')) {

            /*
         * 35000,00
         */

            $value = str_replace(
                ',',
                '.',
                $value
            );
        }


        if (!is_numeric($value)) {
            throw new RuntimeException(
                'Importe inválido.'
            );
        }


        return round(
            (float) $value,
            2
        );
    }

    public function deleteTemporaryStatement(string $temporaryPath): void
    {
        if (Storage::disk('local')->exists($temporaryPath)) {
            Storage::disk('local')->delete($temporaryPath);
        }
    }
}
