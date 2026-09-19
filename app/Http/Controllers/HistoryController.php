<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use App\Exports\HistoryExport;
use Maatwebsite\Excel\Facades\Excel;


class HistoryController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Historial
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Seguridad
        |--------------------------------------------------------------------------
        */

        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }


        /*
        |--------------------------------------------------------------------------
        | Consulta base
        |--------------------------------------------------------------------------
        */

        $query = Transaction::with([
            'account',
            'accountBalance',
            'user',
            'financialDay',
            'executedBy',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Fecha desde
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_from')) {

            $query->whereDate(
                'date',
                '>=',
                $request->date_from
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Fecha hasta
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_to')) {

            $query->whereDate(
                'date',
                '<=',
                $request->date_to
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Cuenta
        |--------------------------------------------------------------------------
        */

        if ($request->filled('account_id')) {

            $query->where(
                'account_id',
                $request->account_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Moneda
        |--------------------------------------------------------------------------
        */

        if ($request->filled('currency')) {

            $currency = strtoupper(
                trim($request->currency)
            );


            $query->whereHas(
                'accountBalance',
                function ($q) use ($currency) {

                    $q->where(
                        'currency',
                        $currency
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Usuario
        |--------------------------------------------------------------------------
        */

        if ($request->filled('user_id')) {

            $query->where(
                'user_id',
                $request->user_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Tipo
        |--------------------------------------------------------------------------
        */

        if ($request->filled('type')) {

            $query->where(
                'type',
                $request->type
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Banco destino
        |--------------------------------------------------------------------------
        */

        if ($request->filled('destination_bank')) {

            $query->where(
                'destination_bank',
                $request->destination_bank
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Estado de ejecución
        |--------------------------------------------------------------------------
        */

        if ($request->filled('execution')) {

            if ($request->execution === 'executed') {

                $query->whereNotNull(
                    'executed_at'
                );

            } elseif (
                $request->execution === 'pending'
            ) {

                $query
                    ->whereNull('executed_at')
                    ->where('type', 'expense');
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Búsqueda global
        |--------------------------------------------------------------------------
        |
        | Busca por:
        |
        | - Descripción
        | - Cuenta
        | - Usuario
        | - Banco destino
        | - Moneda
        |
        */

        if ($request->filled('search')) {

            $search = trim(
                $request->search
            );


            $query->where(
                function ($q) use ($search) {

                    /*
                    |--------------------------------------------------------------------------
                    | Descripción
                    |--------------------------------------------------------------------------
                    */

                    $q->where(
                        'description',
                        'like',
                        '%' . $search . '%'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Banco destino
                    |--------------------------------------------------------------------------
                    */

                    $q->orWhere(
                        'destination_bank',
                        'like',
                        '%' . $search . '%'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Cuenta
                    |--------------------------------------------------------------------------
                    */

                    $q->orWhereHas(
                        'account',
                        function ($accountQuery) use ($search) {

                            $accountQuery->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Moneda
                    |--------------------------------------------------------------------------
                    */

                    $q->orWhereHas(
                        'accountBalance',
                        function ($balanceQuery) use ($search) {

                            $balanceQuery->where(
                                'currency',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Usuario
                    |--------------------------------------------------------------------------
                    */

                    $q->orWhereHas(
                        'user',
                        function ($userQuery) use ($search) {

                            $userQuery->where(
                                function ($q) use ($search) {

                                    $q->where(
                                        'name',
                                        'like',
                                        '%' . $search . '%'
                                    )
                                        ->orWhere(
                                            'email',
                                            'like',
                                            '%' . $search . '%'
                                        );
                                }
                            );
                        }
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Monto mínimo
        |--------------------------------------------------------------------------
        */

        if ($request->filled('amount_min')) {

            $query->where(
                'amount',
                '>=',
                $request->amount_min
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Monto máximo
        |--------------------------------------------------------------------------
        */

        if ($request->filled('amount_max')) {

            $query->where(
                'amount',
                '<=',
                $request->amount_max
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Total de resultados
        |--------------------------------------------------------------------------
        */

        $totalResults =
            (clone $query)->count();


        /*
        |--------------------------------------------------------------------------
        | Totales agrupados por moneda
        |--------------------------------------------------------------------------
        |
        | Nunca sumamos monedas diferentes.
        |
        | Resultado:
        |
        | [
        |     'ARS' => 1500000,
        |     'USD' => 2500,
        | ]
        |
        */

        $totalIncome = $this->totalsByCurrency(
            clone $query,
            ['income']
        );


        $totalExpense = $this->totalsByCurrency(
            clone $query,
            [
                'expense',
                'transfer_out',
            ]
        );


        $totalReserve = $this->totalsByCurrency(
            clone $query,
            ['reserve']
        );


        /*
        |--------------------------------------------------------------------------
        | Movimientos paginados
        |--------------------------------------------------------------------------
        */

        $transactions = $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Datos para filtros
        |--------------------------------------------------------------------------
        */

        $accounts = Account::orderBy(
            'name'
        )->get();


        /*
        |--------------------------------------------------------------------------
        | Monedas disponibles
        |--------------------------------------------------------------------------
        |
        | Las obtenemos de AccountBalance a través de las cuentas disponibles
        | para esta empresa.
        |
        */

        $currencies = $accounts
            ->load('balances')
            ->flatMap(
                fn($account) =>
                $account->balances->pluck('currency')
            )
            ->filter()
            ->unique()
            ->sort()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Usuarios
        |--------------------------------------------------------------------------
        */

        $users = User::withTrashed()
            ->whereHas(
                'companies',
                function ($query) {

                    $query->where(
                        'companies.id',
                        session('company_id')
                    );
                }
            )
            ->orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Bancos destino
        |--------------------------------------------------------------------------
        */

        $destinationBanks = Transaction::whereNotNull(
            'destination_bank'
        )
            ->where(
                'destination_bank',
                '!=',
                ''
            )
            ->distinct()
            ->orderBy(
                'destination_bank'
            )
            ->pluck(
                'destination_bank'
            );


        /*
        |--------------------------------------------------------------------------
        | Vista
        |--------------------------------------------------------------------------
        */

        return view(
            'history.index',
            compact(
                'transactions',
                'accounts',
                'currencies',
                'users',
                'destinationBanks',
                'totalResults',
                'totalIncome',
                'totalExpense',
                'totalReserve'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Exportar
    |--------------------------------------------------------------------------
    */

    public function export(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Seguridad
        |--------------------------------------------------------------------------
        */

        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }


        /*
        |--------------------------------------------------------------------------
        | Filtros
        |--------------------------------------------------------------------------
        */

        $filters = $request->only([
            'date_from',
            'date_to',
            'account_id',
            'currency',
            'user_id',
            'type',
            'destination_bank',
            'execution',
            'search',
            'amount_min',
            'amount_max',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Archivo
        |--------------------------------------------------------------------------
        */

        $fileName =
            'historial_'
            . now()->format('Y-m-d_H-i')
            . '.xlsx';


        return Excel::download(
            new HistoryExport($filters),
            $fileName
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Totales por moneda
    |--------------------------------------------------------------------------
    |
    | Recibe la misma consulta filtrada del historial y solamente suma
    | las monedas de forma independiente.
    |
    */

    private function totalsByCurrency(
        $query,
        array $types
    ): array {

        $transactions = $query
            ->whereIn(
                'type',
                $types
            )
            ->with('accountBalance')
            ->get();


        $totals = [];


        foreach ($transactions as $transaction) {

            $currency =
                $transaction->accountBalance?->currency;


            /*
            |--------------------------------------------------------------------------
            | Movimiento sin AccountBalance
            |--------------------------------------------------------------------------
            |
            | No lo mezclamos arbitrariamente con ARS.
            |
            */

            if (!$currency) {
                continue;
            }


            if (!isset($totals[$currency])) {
                $totals[$currency] = 0;
            }


            $totals[$currency] +=
                (float) $transaction->amount;
        }


        ksort($totals);


        return $totals;
    }
}