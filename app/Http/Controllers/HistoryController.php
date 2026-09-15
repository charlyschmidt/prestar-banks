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

    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Seguridad
        |--------------------------------------------------------------------------
        |
        | Historial es exclusivo del Super Admin.
        |
        */

        if (!auth()->user()->is_admin) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Consulta base
        |--------------------------------------------------------------------------
        */

        $query = Transaction::with([
            'account',
            'user',
            'financialDay',
            'executedBy'
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
        | Búsqueda
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | Búsqueda global
        |--------------------------------------------------------------------------
        |
        | Busca simultáneamente por:
        |
        | - Descripción
        | - Cuenta / banco origen
        | - Usuario
        | - Banco destino
        |
        */

        if ($request->filled('search')) {

            $search = trim(
                $request->search
            );

            $query->where(function ($q) use ($search) {

                /*
        |--------------------------------------------------------------
        | Descripción
        |--------------------------------------------------------------
        */

                $q->where(
                    'description',
                    'like',
                    '%' . $search . '%'
                );


                /*
        |--------------------------------------------------------------
        | Banco destino
        |--------------------------------------------------------------
        */

                $q->orWhere(
                    'destination_bank',
                    'like',
                    '%' . $search . '%'
                );


                /*
        |--------------------------------------------------------------
        | Cuenta / Banco origen
        |--------------------------------------------------------------
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
        |--------------------------------------------------------------
        | Usuario
        |--------------------------------------------------------------
        */

                $q->orWhereHas(
                    'user',
                    function ($userQuery) use ($search) {

                        $userQuery
                            ->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'username',
                                'like',
                                '%' . $search . '%'
                            );
                    }
                );
            });
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
        | Totales reales de la consulta filtrada
        |--------------------------------------------------------------------------
        |
        | Clonamos la consulta ANTES de paginar.
        |
        */

        $totalResults = (clone $query)->count();


        $totalIncome = (clone $query)
            ->where('type', 'income')
            ->sum('amount');


        $totalExpense = (clone $query)
            ->where('type', 'expense')
            ->sum('amount');


        $totalReserve = (clone $query)
            ->where('type', 'reserve')
            ->sum('amount');


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


        $users = User::withTrashed()
            ->orderBy('name')
            ->get();


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
                'users',
                'destinationBanks',
                'totalResults',
                'totalIncome',
                'totalExpense',
                'totalReserve'
            )
        );
    }

    public function export(Request $request)
    {
        /*
    |--------------------------------------------------------------------------
    | Seguridad
    |--------------------------------------------------------------------------
    */

        if (!auth()->user()->is_admin) {
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
}
