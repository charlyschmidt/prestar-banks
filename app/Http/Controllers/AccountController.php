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
use App\Services\MovementControlService;


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
| Control de movimientos
|--------------------------------------------------------------------------
*/

    public function movementControl(
        Account $account,
        FinancialDayService $financialDayService
    ) {

        /*
    |--------------------------------------------------------------------------
    | Jornada actual
    |--------------------------------------------------------------------------
    */

        $day = $financialDayService->current();


        if (!$day) {

            return redirect()
                ->route('dashboard')
                ->with(
                    'error',
                    'No hay jornada abierta.'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Saldos de la cuenta por moneda
    |--------------------------------------------------------------------------
    |
    | El control siempre se realiza sobre un AccountBalance concreto.
    |
    | Ejemplo:
    |
    | Banco Provincia
    |   ARS
    |   USD
    |
    | De esta manera nunca mezclamos movimientos de distintas monedas
    | durante el control del extracto bancario.
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
    | Vista 
    |--------------------------------------------------------------------------
    */

        return view(
            'accounts.movement-control',
            compact(
                'account',
                'day',
                'balances'
            )
        );
    }


    /*
|--------------------------------------------------------------------------
| Procesar extracto para control de movimientos
|--------------------------------------------------------------------------
*/

    public function processMovementControl(
        Request $request,
        Account $account,
        FinancialDayService $financialDayService,
        MovementControlService $movementControlService
    ) {

        /*
    |--------------------------------------------------------------------------
    | Jornada actual
    |--------------------------------------------------------------------------
    */

        $day = $financialDayService->current();


        if (!$day) {

            return redirect()
                ->route('dashboard')
                ->with(
                    'error',
                    'No hay jornada abierta.'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Validación básica
    |--------------------------------------------------------------------------
    */

        $data = $request->validate([

            'account_balance_id' => [
                'required',
                'integer',
            ],

            'statement' => [
                'required',
                'file',
                'mimes:csv,xlsx,xls',
                'max:10240',
            ],

        ], [

            'account_balance_id.required' =>
            'Seleccioná una moneda.',

            'statement.required' =>
            'Seleccioná un extracto bancario.',

            'statement.file' =>
            'El extracto seleccionado no es válido.',

            'statement.mimes' =>
            'El extracto debe ser un archivo CSV, XLSX o XLS.',

            'statement.max' =>
            'El extracto no puede superar los 10 MB.',

        ]);


        /*
    |--------------------------------------------------------------------------
    | Verificar AccountBalance
    |--------------------------------------------------------------------------
    |
    | No confiamos únicamente en el ID recibido desde el formulario.
    |
    | El saldo seleccionado tiene que:
    |
    | - pertenecer a esta cuenta
    | - pertenecer a la empresa actual
    |
    | El scope BelongsToCompany del modelo AccountBalance se encarga
    | del aislamiento por empresa.
    |
    */

        $accountBalance = AccountBalance::query()
            ->where(
                'id',
                $data['account_balance_id']
            )
            ->where(
                'account_id',
                $account->id
            )
            ->first();


        if (!$accountBalance) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'La moneda seleccionada no pertenece a esta cuenta.'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Verificar que participa de la jornada
    |--------------------------------------------------------------------------
    */

        $dailyBalance = AccountDailyBalance::query()
            ->where(
                'financial_day_id',
                $day->id
            )
            ->where(
                'account_id',
                $account->id
            )
            ->where(
                'account_balance_id',
                $accountBalance->id
            )
            ->first();


        if (!$dailyBalance) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'La moneda seleccionada no está disponible en la jornada actual.'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Leer extracto
    |--------------------------------------------------------------------------
    */

        try {

            $file = $request->file('statement');

            $statement = $movementControlService->readStatement($file);

            $temporaryPath = $movementControlService->storeTemporaryStatement($file);
        } catch (\Throwable $e) {

            dd([
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }


        /*
    |--------------------------------------------------------------------------
    | Resultado temporal
    |--------------------------------------------------------------------------
    |
    | Por ahora detenemos el flujo acá.
    |
    | Esto nos permite verificar exactamente qué encabezados y filas
    | detectó AERIA antes de construir la pantalla de mapeo.
    |
    */

        return view(
            'accounts.movement-control-mapping',
            [
                'account' => $account,
                'accountBalance' => $accountBalance,
                'day' => $day,
                'statement' => $statement,
                'temporaryPath' => $temporaryPath,
            ]
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

    public function compareMovementControl(
        Request $request,
        Account $account,
        MovementControlService $movementControlService
    ) {
        /*
    |--------------------------------------------------------------------------
    | Validar mapeo
    |--------------------------------------------------------------------------
    */

        $data = $request->validate([
            'account_balance_id' => [
                'required',
                'integer',
            ],

            'temporary_path' => [
                'required',
                'string',
            ],

            'date_column' => [
                'required',
                'integer',
                'min:0',
            ],

            'description_column' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'amount_column' => [
                'required',
                'integer',
                'min:0',
            ],

            'balance_column' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ], [
            'account_balance_id.required' =>
            'No se pudo identificar la moneda seleccionada.',

            'temporary_path.required' =>
            'No se pudo identificar el extracto bancario.',

            'date_column.required' =>
            'Seleccioná la columna correspondiente a la fecha.',

            'amount_column.required' =>
            'Seleccioná la columna correspondiente al importe.',
        ]);


        /*
    |--------------------------------------------------------------------------
    | Verificar cuenta / moneda
    |--------------------------------------------------------------------------
    */

        $accountBalance = AccountBalance::query()
            ->where('id', $data['account_balance_id'])
            ->where('account_id', $account->id)
            ->first();


        if (!$accountBalance) {

            return redirect()
                ->route(
                    'accounts.movement-control',
                    $account->id
                )
                ->with(
                    'error',
                    'La moneda seleccionada no pertenece a esta cuenta.'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Seguridad del archivo temporal
    |--------------------------------------------------------------------------
    |
    | El navegador nos devuelve temporary_path,
    | pero no confiamos directamente en ese valor.
    |
    | El archivo debe pertenecer al directorio temporal
    | del usuario autenticado.
    |--------------------------------------------------------------------------
    */

        $expectedPrefix =
            'movement-control/' . auth()->id() . '/';


        if (
            !str_starts_with(
                $data['temporary_path'],
                $expectedPrefix
            )
        ) {

            return redirect()
                ->route(
                    'accounts.movement-control',
                    $account->id
                )
                ->with(
                    'error',
                    'El extracto bancario no es válido.'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Preparar mapeo
    |--------------------------------------------------------------------------
    */

        $mapping = [
            'date' =>
            (int) $data['date_column'],

            'description' =>
            isset($data['description_column'])
                ? (int) $data['description_column']
                : null,

            'amount' =>
            (int) $data['amount_column'],

            'balance' =>
            isset($data['balance_column'])
                ? (int) $data['balance_column']
                : null,
        ];


        /*
    |--------------------------------------------------------------------------
    | Comparar
    |--------------------------------------------------------------------------
    */

        try {

            $result = $movementControlService->compare(
                $data['temporary_path'],
                $accountBalance,
                $mapping
            );
        } catch (\Throwable $e) {

            report($e);


            return redirect()
                ->route(
                    'accounts.movement-control',
                    $account->id
                )
                ->with(
                    'error',
                    'No se pudo comparar el extracto bancario.'
                );
        }

        $movementControlService->deleteTemporaryStatement(
            $data['temporary_path']
        );

        return view(
            'accounts.movement-control-results',
            [
                'account' => $account,
                'accountBalance' => $accountBalance,
                'result' => $result,
            ]
        );
    }

    public function alerts(Account $account)
    {
        $account->load([
            'balances' => function ($query) {
                $query->orderBy('currency');
            }
        ]);

        return view(
            'accounts.alerts',
            compact('account')
        );
    }

    public function updateAlerts(
        Request $request,
        Account $account
    ) {
        $validated = $request->validate([
            'thresholds' => [
                'nullable',
                'array',
            ],

            'thresholds.*' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);


        $thresholds = $validated['thresholds'] ?? [];


        $balances = $account
            ->balances()
            ->get();


        foreach ($balances as $balance) {

            $threshold = $thresholds[$balance->id] ?? null;


            $balance->update([
                'low_balance_threshold' =>
                $threshold !== null && $threshold !== ''
                    ? $threshold
                    : null,
            ]);
        }


        return redirect()
            ->route('accounts.alerts', $account->id)
            ->with(
                'success',
                'Las alertas fueron actualizadas correctamente.'
            );
    }
}
