<?php

namespace App\Http\Controllers;

use App\Models\Cheque;
use App\Services\CompanyContextService;
use Illuminate\View\View;

class ChequePortfolioController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | EMPRESA ACTIVA
    |--------------------------------------------------------------------------
    */

    private function activeCompanyId(): int
    {
        abort_unless(auth()->check(), 401);

        $companyId = (int) app(
            CompanyContextService::class
        )->id();

        abort_if($companyId <= 0, 403);

        return $companyId;
    }

    /*
    |--------------------------------------------------------------------------
    | CARTERA DE CHEQUES
    |--------------------------------------------------------------------------
    |
    | Muestra únicamente los cheques de terceros
    | que actualmente están disponibles en cartera.
    |
    | No incluye:
    | - Borradores.
    | - Cheques depositados.
    | - Cheques acreditados.
    | - Cheques rechazados.
    |
    | Próximamente también excluirá los cheques
    | entregados a otros clientes.
    |
    */

    public function index(): View
    {
        $companyId = $this->activeCompanyId();

        /*
         * Consulta base.
         *
         * Aplicamos explícitamente company_id
         * además del scope multiempresa.
         */

        $query = Cheque::query()
            ->where('company_id', $companyId)
            ->where('kind', 'third_party')
            ->where('status', 'in_portfolio');

        /*
        |--------------------------------------------------------------------------
        | INDICADORES
        |--------------------------------------------------------------------------
        */

        $chequeCount = (clone $query)->count();

        $totalNominal = (clone $query)
            ->sum('nominal_amount');

        /*
        |--------------------------------------------------------------------------
        | CHEQUES DISPONIBLES
        |--------------------------------------------------------------------------
        |
        | Primero los próximos a vencer.
        | Incluimos la liquidación de origen
        | para conocer quién entregó cada cheque.
        |
        */

        $cheques = (clone $query)
            ->with('liquidation')
            ->orderBy('due_date')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | VISTA
        |--------------------------------------------------------------------------
        */

        return view('cheques.portfolio.index', [
            'cheques' => $cheques,
            'chequeCount' => $chequeCount,
            'totalNominal' => $totalNominal,
        ]);
    }
}
