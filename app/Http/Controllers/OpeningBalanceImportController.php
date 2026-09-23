<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class OpeningBalanceImportController extends Controller
{
    public function analyze(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240',
            ],
        ]);

        $file = $request->file('file');

        try {

            $path =
                $file->getRealPath();

            $reader =
                IOFactory::createReaderForFile($path);

            $reader->setReadDataOnly(true);

            $spreadsheet =
                $reader->load($path);

            $worksheet =
                $spreadsheet->getSheetByName('Saldos');

            if (!$worksheet) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la hoja Saldos.',
                ], 422);
            }

            $rows =
                $worksheet->toArray(
                    null,
                    true,
                    true,
                    true
                );

            $preview = [];

            foreach (array_slice($rows, 0, 50, true) as $rowNumber => $row) {

                $values = [];

                foreach ($row as $column => $value) {

                    if (
                        $value !== null &&
                        $value !== ''
                    ) {
                        $values[$column] = $value;
                    }
                }

                if (!empty($values)) {
                    $preview[$rowNumber] = $values;
                }
            }

            $detectedBalances = [];

            $rowNumbers =
                array_keys($rows);

            foreach ($rows as $rowNumber => $row) {

                foreach ($row as $column => $value) {

                    if (
                        !is_string($value) ||
                        mb_strtolower(trim($value)) !== 'actual'
                    ) {
                        continue;
                    }

                    /*
                    * Buscamos hacia arriba el nombre del banco/cuenta
                    * dentro de la misma columna.
                    */
                    $accountName = null;

                    for (
                        $previousRow = $rowNumber - 1;
                        $previousRow >= max(1, $rowNumber - 5);
                        $previousRow--
                    ) {

                        $candidate =
                            trim(
                                (string) (
                                    $rows[$previousRow][$column]
                                    ?? ''
                                )
                            );

                        if (
                            $candidate === '' ||
                            mb_strtolower($candidate) === '24 hs'
                        ) {
                            continue;
                        }

                        $accountName =
                            $candidate;

                        break;
                    }

                    if (!$accountName) {
                        continue;
                    }

                    /*
                    * Desde "Actual" buscamos hacia la derecha
                    * el primer valor numérico de esa fila.
                    */
                    $columnIndex =
                        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
                            $column
                        );

                    $balance = null;

                    for (
                        $offset = 1;
                        $offset <= 6;
                        $offset++
                    ) {

                        $valueColumn =
                            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
                                $columnIndex + $offset
                            );

                        $candidate =
                            $row[$valueColumn]
                            ?? null;

                        if (
                            $candidate !== null &&
                            $candidate !== '' &&
                            is_numeric($candidate)
                        ) {
                            $balance =
                                round(
                                    (float) $candidate,
                                    2
                                );

                            break;
                        }
                    }

                    if ($balance === null) {
                        continue;
                    }

                    $detectedBalances[] = [
                        'account' => $accountName,
                        'balance' => $balance,
                        'source' => [
                            'label_cell' =>
                            $column . $rowNumber,

                            'value_cell' =>
                            $valueColumn . $rowNumber,
                        ],
                    ];
                }
            }

            return response()->json([
                'success' => true,

                'message' =>
                count($detectedBalances)
                    . ' saldos detectados.',

                'balances' =>
                $detectedBalances,
            ]);
        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo leer el archivo Excel.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
