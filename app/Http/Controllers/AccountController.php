<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AccountDailyBalance;
use App\Models\Transaction;
use App\Services\FinancialDayService;
use App\Exports\AccountTransactionsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\FinancialDay;

class AccountController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Monedas disponibles
    |--------------------------------------------------------------------------
    */

    private function currencies(): array
    {
        return [

            'ARS' => 'Peso argentino',

            'USD' => 'Dólar estadounidense',

            'EUR' => 'Euro',

            'BRL' => 'Real brasileño',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Listado de cuentas
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $accounts = Account::with('balances')
            ->get();


        return view(
            'accounts.index',
            compact('accounts')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Crear cuenta
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $currencies = $this->currencies();


        return view(
            'accounts.create',
            compact('currencies')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Guardar cuenta
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $data = $request->validate([

            'name' =>
            'required|string|max:100',

            'type' =>
            'required|string',

            'logo' =>
            'nullable|image|max:2048',

            'currencies' =>
            'required|array|min:1',

            'currencies.*' => [
                'required',
                'string',
                'size:3',
                'in:' . implode(
                    ',',
                    array_keys(
                        $this->currencies()
                    )
                ),
            ],

        ], [

            'currencies.required' =>
            'Seleccioná al menos una moneda.',

            'currencies.min' =>
            'Seleccioná al menos una moneda.',

            'currencies.*.in' =>
            'Una de las monedas seleccionadas no es válida.',

        ]);


        if ($request->hasFile('logo')) {

            $data['logo'] =
                $request->file('logo')
                ->store(
                    'banks',
                    'public'
                );
        }


        DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | Monedas
            |--------------------------------------------------------------------------
            */

            $currencies = $data['currencies'];

            unset(
                $data['currencies']
            );


            /*
            |--------------------------------------------------------------------------
            | Crear cuenta
            |--------------------------------------------------------------------------
            */

            $account = Account::create(
                $data
            );


            /*
            |--------------------------------------------------------------------------
            | Crear saldos por moneda
            |--------------------------------------------------------------------------
            */

            foreach ($currencies as $currency) {

                AccountBalance::create([

                    'account_id' =>
                    $account->id,

                    'currency' =>
                    strtoupper($currency),

                ]);
            }
        });


        return redirect()
            ->route('accounts.index')
            ->with(
                'success',
                'Cuenta creada correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Editar cuenta
    |--------------------------------------------------------------------------
    */

    public function edit(Account $account)
    {
        $account->load('balances');


        $currencies = $this->currencies();


        $selectedCurrencies =
            $account->balances
            ->pluck('currency')
            ->toArray();


        return view(
            'accounts.edit',
            compact(
                'account',
                'currencies',
                'selectedCurrencies'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Actualizar cuenta
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Account $account
    ) {

        $data = $request->validate([

            'name' =>
            'required|string|max:100',

            'type' =>
            'required|string',

            'logo' =>
            'nullable|image|max:2048',

            'currencies' =>
            'required|array|min:1',

            'currencies.*' => [
                'required',
                'string',
                'size:3',
                'in:' . implode(
                    ',',
                    array_keys(
                        $this->currencies()
                    )
                ),
            ],

        ], [

            'currencies.required' =>
            'Seleccioná al menos una moneda.',

            'currencies.min' =>
            'Seleccioná al menos una moneda.',

            'currencies.*.in' =>
            'Una de las monedas seleccionadas no es válida.',

        ]);


        if ($request->hasFile('logo')) {

            $data['logo'] =
                $request->file('logo')
                ->store(
                    'banks',
                    'public'
                );
        }


        DB::transaction(
            function () use (
                $data,
                $account
            ) {

                /*
            |--------------------------------------------------------------------------
            | Monedas seleccionadas
            |--------------------------------------------------------------------------
            */

                $currencies =
                    collect($data['currencies'])
                    ->map(
                        fn($currency) =>
                        strtoupper($currency)
                    )
                    ->unique()
                    ->values()
                    ->toArray();


                unset(
                    $data['currencies']
                );


                /*
            |--------------------------------------------------------------------------
            | Actualizar cuenta
            |--------------------------------------------------------------------------
            */

                $account->update(
                    $data
                );


                /*
            |--------------------------------------------------------------------------
            | Monedas actuales
            |--------------------------------------------------------------------------
            */

                $existingBalances =
                    $account->balances()
                    ->get();


                $existingCurrencies =
                    $existingBalances
                    ->pluck('currency')
                    ->toArray();


                /*
            |--------------------------------------------------------------------------
            | Agregar monedas nuevas
            |--------------------------------------------------------------------------
            */

                $currenciesToAdd =
                    array_diff(
                        $currencies,
                        $existingCurrencies
                    );


                foreach (
                    $currenciesToAdd as $currency
                ) {

                    AccountBalance::create([

                        'company_id' =>
                        $account->company_id,

                        'account_id' =>
                        $account->id,

                        'currency' =>
                        $currency,

                    ]);
                }


                /*
            |--------------------------------------------------------------------------
            | Recargar saldos de la cuenta
            |--------------------------------------------------------------------------
            |
            | Es importante volver a consultarlos porque arriba pudimos
            | haber creado nuevas monedas.
            |
            */

                $account->load('balances');


                $balances =
                    $account->balances;


                /*
            |--------------------------------------------------------------------------
            | Jornada abierta
            |--------------------------------------------------------------------------
            */

                $financialDay =
                    FinancialDay::where(
                        'company_id',
                        $account->company_id
                    )
                    ->where(
                        'status',
                        'open'
                    )
                    ->latest('id')
                    ->first();


                /*
            |--------------------------------------------------------------------------
            | Sincronizar monedas con la jornada abierta
            |--------------------------------------------------------------------------
            |
            | Revisamos TODAS las monedas seleccionadas.
            |
            | Esto cubre:
            |
            | - monedas recién creadas;
            | - monedas creadas antes de implementar multimoneda;
            | - jornadas antiguas que no tengan AccountDailyBalance;
            | - inconsistencias previas.
            |
            */

                if ($financialDay) {

                    foreach (
                        $balances as $balance
                    ) {

                        /*
                     * Solo sincronizamos monedas que siguen
                     * seleccionadas en el formulario.
                     */

                        if (
                            !in_array(
                                $balance->currency,
                                $currencies,
                                true
                            )
                        ) {

                            continue;
                        }


                        AccountDailyBalance::firstOrCreate(
                            [
                                'financial_day_id' =>
                                $financialDay->id,

                                'account_balance_id' =>
                                $balance->id,
                            ],
                            [
                                'company_id' =>
                                $account->company_id,

                                'account_id' =>
                                $account->id,

                                'initial_balance' =>
                                0,

                                'current_balance' =>
                                0,
                            ]
                        );
                    }
                }


                /*
            |--------------------------------------------------------------------------
            | Monedas que intentaron quitar
            |--------------------------------------------------------------------------
            */

                $currenciesToRemove =
                    array_diff(
                        $existingCurrencies,
                        $currencies
                    );


                foreach (
                    $currenciesToRemove as $currency
                ) {

                    $balance =
                        $existingBalances
                        ->firstWhere(
                            'currency',
                            $currency
                        );


                    if (!$balance) {
                        continue;
                    }


                    /*
                |--------------------------------------------------------------------------
                | Verificar historial
                |--------------------------------------------------------------------------
                */

                    $hasDailyBalances =
                        AccountDailyBalance::where(
                            'account_balance_id',
                            $balance->id
                        )
                        ->exists();


                    $hasTransactions =
                        Transaction::where(
                            'account_balance_id',
                            $balance->id
                        )
                        ->withTrashed()
                        ->exists();


                    /*
                 * Solo eliminamos la moneda si nunca
                 * tuvo jornadas ni movimientos.
                 */

                    if (
                        !$hasDailyBalances &&
                        !$hasTransactions
                    ) {

                        $balance->delete();
                    }
                }
            }
        );


        return redirect()
            ->route('accounts.index')
            ->with(
                'success',
                'Cuenta actualizada correctamente.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminar cuenta
    |--------------------------------------------------------------------------
    */

    public function destroy(Account $account)
    {
        $account->delete();


        return back();
    }


    /*
    |--------------------------------------------------------------------------
    | Movimientos de cuenta
    |--------------------------------------------------------------------------
    */

    public function movements(
        Account $account,
        FinancialDayService $financialDayService
    ) {

        $day = $financialDayService->current();


        if (!$day) {

            return redirect()
                ->route('dashboard')
                ->with(
                    'error',
                    'No hay jornada abierta'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Saldos de la cuenta por moneda
    |--------------------------------------------------------------------------
    |
    | Una cuenta puede tener:
    |
    | Santander
    |   ARS
    |   USD
    |   EUR
    |
    | Cada moneda tiene su propio saldo diario.
    |
    */

        $balances = AccountDailyBalance::with(
            'accountBalance'
        )
            ->where(
                'financial_day_id',
                $day->id
            )
            ->where(
                'account_id',
                $account->id
            )
            ->get()
            ->sortBy(
                fn($balance) =>
                $balance->accountBalance?->currency
            )
            ->values();


        /*
    |--------------------------------------------------------------------------
    | Movimientos
    |--------------------------------------------------------------------------
    |
    | Cargamos AccountBalance para conocer
    | la moneda de cada movimiento.
    |
    */

        $movements = Transaction::with([
            'accountBalance',
            'user',
            'executedBy',
        ])
            ->where(
                'financial_day_id',
                $day->id
            )
            ->where(
                'account_id',
                $account->id
            )
            ->orderBy(
                'date',
                'desc'
            )
            ->get();


        return view(
            'accounts.movements',
            compact(
                'account',
                'balances',
                'movements'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Exportar movimientos
    |--------------------------------------------------------------------------
    */

    public function export(
        Account $account,
        FinancialDayService $financialDayService
    ) {

        $day =
            $financialDayService->current();


        if (!$day) {

            return back()
                ->with(
                    'error',
                    'No hay jornada abierta.'
                );
        }


        return Excel::download(

            new AccountTransactionsExport(
                $account->id,
                $day,
                auth()->user()
            ),

            'Movimientos-'
                . $account->name
                . '-'
                . $day->date->format('Y-m-d')
                . '.xlsx'

        );
    }
}
