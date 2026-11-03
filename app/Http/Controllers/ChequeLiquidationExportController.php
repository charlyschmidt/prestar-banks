<?php

namespace App\Http\Controllers;

use App\Exports\ChequeLiquidationExport;
use App\Models\ChequeLiquidation;
use App\Services\CompanyContextService;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ChequeLiquidationExportController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | EXPORTAR LIQUIDACIÓN DE CHEQUES
    |--------------------------------------------------------------------------
    |
    | Genera un archivo Excel con dos hojas:
    |
    | 1. Resumen financiero de la liquidación.
    | 2. Detalle individual de todos los cheques.
    |
    | La exportación es de solo lectura.
    | No modifica estados ni movimientos financieros.
    |
    */

    public function __invoke(
        ChequeLiquidation $liquidation
    ): BinaryFileResponse {

        /*
        |--------------------------------------------------------------------------
        | VALIDAR AUTENTICACIÓN
        |--------------------------------------------------------------------------
        */

        abort_unless(auth()->check(), 401);

        /*
        |--------------------------------------------------------------------------
        | VALIDAR EMPRESA ACTIVA
        |--------------------------------------------------------------------------
        */

        $companyId = (int) app(
            CompanyContextService::class
        )->id();

        abort_if($companyId <= 0, 403);

        /*
         * Impedimos exportar liquidaciones
         * pertenecientes a otras empresas.
         */

        abort_unless(
            (int) $liquidation->company_id === $companyId,
            404
        );

        /*
        |--------------------------------------------------------------------------
        | CARGAR CHEQUES
        |--------------------------------------------------------------------------
        */

        $liquidation->load('cheques');

        /*
        |--------------------------------------------------------------------------
        | NOMBRE DEL ARCHIVO
        |--------------------------------------------------------------------------
        |
        | Ejemplo:
        |
        | AERIA_Liquidacion_15_2026-11-03.xlsx
        |
        */

        $date = $liquidation->operation_date
            ->format('Y-m-d');

        $filename = sprintf(
            'AERIA_Liquidacion_%d_%s.xlsx',
            $liquidation->id,
            $date
        );

        /*
        |--------------------------------------------------------------------------
        | GENERAR Y DESCARGAR EXCEL
        |--------------------------------------------------------------------------
        */

        return Excel::download(
            new ChequeLiquidationExport($liquidation),
            $filename
        );
    }
}
