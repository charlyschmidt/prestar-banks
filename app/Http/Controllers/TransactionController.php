<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Http\Request;
use App\Events\TransactionCreated;
use App\Services\FinancialDayService;
use App\Models\AccountDailyBalance;
use Illuminate\Support\Facades\DB;
use App\Exports\TransactionsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Support\ArgentineBanks;
use Illuminate\Validation\Rule;
use App\Models\AccountBalance;

class TransactionController extends Controller
{


    public function index(FinancialDayService $financialDayService)
    {
        $day = $financialDayService->current();


        if (!$day) {

            return view(
                'transactions.index',
                [
                    'transactions' => []
                ]
            );
        }


        $transactions = Transaction::with([
            'account',
            'user'
        ])
            ->where(
                'financial_day_id',
                $day->id
            )
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();



        return view(
            'transactions.index',
            compact('transactions')
        );
    }





    public function create()
    {
        $day = app(
            FinancialDayService::class
        )->current();


        if (!$day) {

            return redirect()
                ->route('dashboard')
                ->with(
                    'error',
                    'No hay una jornada abierta.'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Cuentas + monedas + saldo diario
    |--------------------------------------------------------------------------
    |
    | Santander
    |   ARS
    |       saldo diario
    |
    |   USD
    |       saldo diario
    |
    | Galicia
    |   ARS
    |       saldo diario
    |
    */

        $accounts = Account::with([
            'balances' => function ($query) use ($day) {

                $query
                    ->with([
                        'dailyBalance' => function ($query) use ($day) {

                            $query->where(
                                'financial_day_id',
                                $day->id
                            );
                        }
                    ])
                    ->orderBy('currency');
            }
        ])
            ->orderBy('name')
            ->get();


        return view(
            'transactions.create',
            [
                'accounts' => $accounts,
                'banks' => ArgentineBanks::all(),
            ]
        );
    }







    public function store(
        Request $request,
        FinancialDayService $financialDayService
    ) {

        /*
    |--------------------------------------------------------------------------
    | Validación
    |--------------------------------------------------------------------------
    */

        $data = $request->validate([

            'account_id' => [
                'required',
                'integer',

                Rule::exists(
                    'accounts',
                    'id'
                )->where(
                    fn($query) =>
                    $query->where(
                        'company_id',
                        session('company_id')
                    )
                ),
            ],

            'account_balance_id' => [
                'required',
                'integer',

                Rule::exists(
                    'account_balances',
                    'id'
                )->where(
                    fn($query) =>
                    $query->where(
                        'company_id',
                        session('company_id')
                    )
                ),
            ],

            'type' => [
                'required',
                'in:income,expense,reserve,transfer_in,transfer_out'
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01'
            ],

            'description' => [
                'nullable',
                'string'
            ],

            'date' => [
                'required',
                'date'
            ],

            'destination_bank' => [
                'required_if:type,expense',
                'nullable',
                'string',
                'max:150',
            ],

        ]);


        /*
    |--------------------------------------------------------------------------
    | Jornada actual
    |--------------------------------------------------------------------------
    */

        $day =
            $financialDayService->current();


        if (!$day) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No hay una jornada abierta. Primero iniciá el día.'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Validar cuenta + moneda
    |--------------------------------------------------------------------------
    |
    | No alcanza con validar que ambos IDs existan.
    |
    | Debemos comprobar que el AccountBalance
    | seleccionado realmente pertenece a la
    | Account seleccionada.
    |
    */

        $accountBalance =
            AccountBalance::where(
                'id',
                $data['account_balance_id']
            )
            ->where(
                'account_id',
                $data['account_id']
            )
            ->first();


        if (!$accountBalance) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'La moneda seleccionada no pertenece a la cuenta.'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Banco destino
    |--------------------------------------------------------------------------
    */

        if (
            $data['type'] !== 'expense'
        ) {

            $data['destination_bank'] =
                null;
        }


        /*
    |--------------------------------------------------------------------------
    | Usuario creador
    |--------------------------------------------------------------------------
    */

        $data['user_id'] =
            auth()->id();


        /*
    |--------------------------------------------------------------------------
    | Crear movimiento
    |--------------------------------------------------------------------------
    */

        try {

            DB::transaction(
                function () use (
                    $data,
                    $day,
                    $accountBalance,
                    &$transaction
                ) {

                    /*
                |--------------------------------------------------------------------------
                | Bloquear saldo diario
                |--------------------------------------------------------------------------
                |
                | Ahora buscamos por account_balance_id.
                |
                | Esto identifica exactamente:
                |
                | Santander / ARS
                | Santander / USD
                | Santander / EUR
                |
                | lockForUpdate evita que dos movimientos
                | concurrentes modifiquen el mismo saldo
                | al mismo tiempo.
                |
                */

                    $balance =
                        AccountDailyBalance::where(
                            'financial_day_id',
                            $day->id
                        )
                        ->where(
                            'account_balance_id',
                            $accountBalance->id
                        )
                        ->lockForUpdate()
                        ->first();


                    if (!$balance) {

                        throw new \Exception(
                            'La moneda seleccionada no pertenece a la jornada actual.'
                        );
                    }


                    /*
                |--------------------------------------------------------------------------
                | Validar consistencia
                |--------------------------------------------------------------------------
                */

                    if (
                        (int) $balance->account_id !==
                        (int) $data['account_id']
                    ) {

                        throw new \Exception(
                            'El saldo seleccionado no pertenece a la cuenta.'
                        );
                    }


                    /*
                |--------------------------------------------------------------------------
                | Validar saldo disponible
                |--------------------------------------------------------------------------
                */

                    $isExpense =
                        in_array(
                            $data['type'],
                            [
                                'expense',
                                'reserve',
                                'transfer_out'
                            ],
                            true
                        );


                    if (
                        $isExpense &&
                        $balance->current_balance <
                        $data['amount']
                    ) {

                        throw new \Exception(

                            'Saldo insuficiente. Disponible: ' .

                                $accountBalance->currency .

                                ' ' .

                                number_format(
                                    $balance->current_balance,
                                    2,
                                    ',',
                                    '.'
                                )

                        );
                    }


                    /*
                |--------------------------------------------------------------------------
                | Preparar movimiento
                |--------------------------------------------------------------------------
                */

                    $data['financial_day_id'] =
                        $day->id;


                    /*
                 * Guardamos ambos.
                 *
                 * account_id:
                 * banco/cuenta
                 *
                 * account_balance_id:
                 * moneda concreta dentro del banco
                 */

                    $data['account_id'] =
                        $accountBalance->account_id;


                    $data['account_balance_id'] =
                        $accountBalance->id;


                    /*
                |--------------------------------------------------------------------------
                | Crear movimiento
                |--------------------------------------------------------------------------
                */

                    $transaction =
                        Transaction::create(
                            $data
                        );


                    /*
                |--------------------------------------------------------------------------
                | Actualizar saldo
                |--------------------------------------------------------------------------
                */

                    if (
                        $transaction->type ===
                        'income'
                    ) {

                        $balance->increment(
                            'current_balance',
                            $transaction->amount
                        );
                    } else {

                        $balance->decrement(
                            'current_balance',
                            $transaction->amount
                        );
                    }


                    /*
                |--------------------------------------------------------------------------
                | Saldo resultante
                |--------------------------------------------------------------------------
                */

                    $balance->refresh();


                    $transaction->update([

                        'balance_after' =>
                        $balance->current_balance

                    ]);
                }
            );
        } catch (\Exception $e) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Realtime
    |--------------------------------------------------------------------------
    */

        $transaction->refresh();


        $transaction->load([
            'account',
            'accountBalance',
            'user',
            'financialDay'
        ]);


        event(
            new TransactionCreated(
                $transaction
            )
        );


        return redirect()
            ->route('transactions.index')
            ->with(
                'success',
                'Movimiento creado correctamente.'
            );
    }



    public function edit(Transaction $transaction)
    {
        /*
    |--------------------------------------------------------------------------
    | Solo puede editarlo el usuario que lo creó
    |--------------------------------------------------------------------------
    */

        if (
            !auth()->user()->isSuperAdmin() &&
            $transaction->user_id !== auth()->id()
        ) {

            return redirect()
                ->route('transactions.index')
                ->with(
                    'error',
                    'No tenés permisos para editar este movimiento.'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Jornada actual
    |--------------------------------------------------------------------------
    */

        $day = app(
            FinancialDayService::class
        )->current();


        if (!$day) {

            return redirect()
                ->route('transactions.index')
                ->with(
                    'error',
                    'No hay una jornada abierta actualmente.'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Verificar que el movimiento pertenece a la jornada actual
    |--------------------------------------------------------------------------
    */

        if (
            (int) $transaction->financial_day_id !==
            (int) $day->id
        ) {

            return redirect()
                ->route('transactions.index')
                ->with(
                    'error',
                    'Este movimiento no pertenece a la jornada actual.'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Cuentas + monedas + saldos diarios
    |--------------------------------------------------------------------------
    */

        $accounts = Account::with([
            'balances' => function ($query) use ($day) {

                $query
                    ->with([
                        'dailyBalance' => function ($query) use ($day) {

                            $query->where(
                                'financial_day_id',
                                $day->id
                            );
                        }
                    ])
                    ->orderBy('currency');
            }
        ])
            ->orderBy('name')
            ->get();


        /*
    |--------------------------------------------------------------------------
    | Cargar moneda actual del movimiento
    |--------------------------------------------------------------------------
    */

        $transaction->load(
            'accountBalance'
        );


        return view(
            'transactions.edit',
            [
                'transaction' => $transaction,
                'accounts' => $accounts,
                'banks' => ArgentineBanks::all(),
            ]
        );
    }



    public function update(
        Request $request,
        Transaction $transaction,
        FinancialDayService $financialDayService
    ) {

        /*
    |--------------------------------------------------------------------------
    | Permisos
    |--------------------------------------------------------------------------
    */

        if (
            !auth()->user()->isSuperAdmin() &&
            $transaction->user_id !== auth()->id()
        ) {

            return redirect()
                ->route('transactions.index')
                ->with(
                    'error',
                    'No tenés permisos para editar este movimiento.'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Validación
    |--------------------------------------------------------------------------
    */

        $data = $request->validate([

            'account_id' => [
                'required',
                'integer',

                Rule::exists(
                    'accounts',
                    'id'
                )->where(
                    fn($query) =>
                    $query->where(
                        'company_id',
                        session('company_id')
                    )
                ),
            ],

            'account_balance_id' => [
                'required',
                'integer',

                Rule::exists(
                    'account_balances',
                    'id'
                )->where(
                    fn($query) =>
                    $query->where(
                        'company_id',
                        session('company_id')
                    )
                ),
            ],

            'type' => [
                'required',
                'in:income,expense,reserve,transfer_in,transfer_out'
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01'
            ],

            'destination_bank' => [
                'required_if:type,expense',
                'nullable',
                'string',
                'max:150'
            ],

            'description' => [
                'nullable',
                'string'
            ],

            'date' => [
                'required',
                'date'
            ]

        ]);


        /*
    |--------------------------------------------------------------------------
    | Banco destino
    |--------------------------------------------------------------------------
    */

        if (
            $data['type'] !== 'expense'
        ) {

            $data['destination_bank'] =
                null;
        }


        /*
    |--------------------------------------------------------------------------
    | Jornada actual
    |--------------------------------------------------------------------------
    */

        $day =
            $financialDayService->current();


        if (!$day) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No hay una jornada abierta actualmente.'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | El movimiento debe pertenecer a la jornada actual
    |--------------------------------------------------------------------------
    */

        if (
            (int) $transaction->financial_day_id !==
            (int) $day->id
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'El movimiento no pertenece a la jornada actual.'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Validar nueva cuenta + moneda
    |--------------------------------------------------------------------------
    */

        $newAccountBalance =
            AccountBalance::where(
                'id',
                $data['account_balance_id']
            )
            ->where(
                'account_id',
                $data['account_id']
            )
            ->first();


        if (!$newAccountBalance) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'La moneda seleccionada no pertenece a la cuenta.'
                );
        }


        try {

            DB::transaction(
                function () use (
                    $transaction,
                    $data,
                    $day,
                    $newAccountBalance
                ) {

                    /*
                |--------------------------------------------------------------------------
                | 1. Buscar saldo original
                |--------------------------------------------------------------------------
                */

                    $oldBalance =
                        AccountDailyBalance::where(
                            'financial_day_id',
                            $transaction->financial_day_id
                        )
                        ->where(
                            'account_balance_id',
                            $transaction->account_balance_id
                        )
                        ->lockForUpdate()
                        ->first();


                    if (!$oldBalance) {

                        throw new \Exception(
                            'No existe el saldo original del movimiento.'
                        );
                    }


                    /*
                |--------------------------------------------------------------------------
                | 2. Revertir movimiento anterior
                |--------------------------------------------------------------------------
                |
                | Ingreso:
                | había sumado dinero -> ahora restamos.
                |
                | Egreso / reserva / transfer_out:
                | había descontado -> ahora devolvemos.
                |
                */

                    if (
                        $transaction->type ===
                        'income'
                    ) {

                        $oldBalance->decrement(
                            'current_balance',
                            $transaction->amount
                        );
                    } else {

                        $oldBalance->increment(
                            'current_balance',
                            $transaction->amount
                        );
                    }


                    /*
                |--------------------------------------------------------------------------
                | 3. Buscar nuevo saldo
                |--------------------------------------------------------------------------
                */

                    $newBalance =
                        AccountDailyBalance::where(
                            'financial_day_id',
                            $day->id
                        )
                        ->where(
                            'account_balance_id',
                            $newAccountBalance->id
                        )
                        ->lockForUpdate()
                        ->first();


                    if (!$newBalance) {

                        throw new \Exception(
                            'La moneda seleccionada no pertenece a la jornada actual.'
                        );
                    }


                    /*
                |--------------------------------------------------------------------------
                | Validar consistencia
                |--------------------------------------------------------------------------
                */

                    if (
                        (int) $newBalance->account_id !==
                        (int) $newAccountBalance->account_id
                    ) {

                        throw new \Exception(
                            'El saldo seleccionado no pertenece a la cuenta.'
                        );
                    }


                    /*
                |--------------------------------------------------------------------------
                | IMPORTANTE
                |--------------------------------------------------------------------------
                |
                | Si seguimos usando exactamente el mismo AccountBalance,
                | oldBalance y newBalance representan el mismo registro.
                |
                | Como increment/decrement actualizan directamente la DB,
                | refrescamos el modelo antes de validar el nuevo importe.
                |
                */

                    $newBalance->refresh();


                    /*
                |--------------------------------------------------------------------------
                | 4. Validar saldo para el nuevo movimiento
                |--------------------------------------------------------------------------
                */

                    $isExpense =
                        in_array(
                            $data['type'],
                            [
                                'expense',
                                'reserve',
                                'transfer_out'
                            ],
                            true
                        );


                    if (
                        $isExpense &&
                        $newBalance->current_balance <
                        $data['amount']
                    ) {

                        throw new \Exception(

                            'Saldo insuficiente. Disponible: ' .

                                $newAccountBalance->currency .

                                ' ' .

                                number_format(
                                    $newBalance->current_balance,
                                    2,
                                    ',',
                                    '.'
                                )

                        );
                    }


                    /*
                |--------------------------------------------------------------------------
                | 5. Aplicar nuevo movimiento
                |--------------------------------------------------------------------------
                */

                    if (
                        $data['type'] ===
                        'income'
                    ) {

                        $newBalance->increment(
                            'current_balance',
                            $data['amount']
                        );
                    } else {

                        $newBalance->decrement(
                            'current_balance',
                            $data['amount']
                        );
                    }


                    $newBalance->refresh();


                    /*
                |--------------------------------------------------------------------------
                | 6. Actualizar transacción
                |--------------------------------------------------------------------------
                */

                    $data['account_id'] =
                        $newAccountBalance->account_id;


                    $data['account_balance_id'] =
                        $newAccountBalance->id;


                    $data['financial_day_id'] =
                        $day->id;


                    $data['balance_after'] =
                        $newBalance->current_balance;


                    $transaction->update(
                        $data
                    );
                }
            );
        } catch (\Exception $e) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }


        return redirect()
            ->route('transactions.index')
            ->with(
                'success',
                'Movimiento actualizado correctamente.'
            );
    }

    public function destroy(Transaction $transaction)
    {
        /*
    |--------------------------------------------------------------------------
    | Solo puede eliminarlo el usuario que lo creó
    |--------------------------------------------------------------------------
    */

        if (
            !auth()->user()->isSuperAdmin() &&
            $transaction->user_id !== auth()->id()
        ) {

            return back()
                ->with(
                    'error',
                    'No tenés permisos para eliminar este movimiento.'
                );
        }


        try {

            DB::transaction(function () use ($transaction) {

                /*
            |--------------------------------------------------------------------------
            | Buscar exactamente el saldo de la moneda del movimiento
            |--------------------------------------------------------------------------
            */

                $balance = AccountDailyBalance::where(
                    'financial_day_id',
                    $transaction->financial_day_id
                )
                    ->where(
                        'account_balance_id',
                        $transaction->account_balance_id
                    )
                    ->lockForUpdate()
                    ->first();


                if (!$balance) {

                    throw new \Exception(
                        'No existe el saldo diario correspondiente al movimiento.'
                    );
                }


                /*
            |--------------------------------------------------------------------------
            | Validar consistencia
            |--------------------------------------------------------------------------
            */

                if (
                    (int) $balance->account_id !==
                    (int) $transaction->account_id
                ) {

                    throw new \Exception(
                        'El saldo del movimiento no pertenece a la cuenta.'
                    );
                }


                /*
            |--------------------------------------------------------------------------
            | Revertir movimiento
            |--------------------------------------------------------------------------
            |
            | income:
            | El movimiento había sumado dinero.
            | Al eliminarlo debemos restarlo.
            |
            | expense / reserve / transfer_out:
            | El movimiento había descontado dinero.
            | Al eliminarlo debemos devolverlo.
            |
            */

                if (
                    $transaction->type === 'income'
                ) {

                    $balance->decrement(
                        'current_balance',
                        $transaction->amount
                    );
                } else {

                    $balance->increment(
                        'current_balance',
                        $transaction->amount
                    );
                }


                /*
            |--------------------------------------------------------------------------
            | Eliminación lógica
            |--------------------------------------------------------------------------
            |
            | Transaction usa SoftDeletes.
            |
            | No eliminamos físicamente el movimiento:
            | simplemente se completa deleted_at.
            |
            */

                $transaction->delete();
            });


            return back()
                ->with(
                    'success',
                    'Movimiento eliminado correctamente.'
                );
        } catch (\Exception $e) {

            \Log::error(
                'Error al eliminar movimiento',
                [
                    'transaction_id' =>
                    $transaction->id,

                    'account_id' =>
                    $transaction->account_id,

                    'account_balance_id' =>
                    $transaction->account_balance_id,

                    'user_id' =>
                    auth()->id(),

                    'mensaje' =>
                    $e->getMessage(),
                ]
            );


            return back()
                ->with(
                    'error',
                    'Ocurrió un error al eliminar el movimiento.'
                );
        }
    }

    public function export(
        FinancialDayService $financialDayService
    ) {
        $day = $financialDayService->current();

        if (!$day) {

            return back()->with(
                'error',
                'No hay una jornada abierta.'
            );
        }

        return Excel::download(

            new TransactionsExport(
                $day->id,
                $day,
                auth()->user()
            ),

            'Movimientos-' . $day->date->format('Y-m-d') . '.xlsx'

        );
    }

    public function execute(Transaction $transaction)
    {
        $user = auth()->user();


        /*
    |--------------------------------------------------------------------------
    | Permisos
    |--------------------------------------------------------------------------
    */

        if (!$user->canExecuteTransactions()) {

            return response()->json([
                'success' => false,
                'message' => 'No tenés permisos para ejecutar movimientos.'
            ], 403);
        }


        /*
    |--------------------------------------------------------------------------
    | Solo egresos
    |--------------------------------------------------------------------------
    */

        if ($transaction->type !== 'expense') {

            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden ejecutar movimientos de egreso.'
            ], 422);
        }


        /*
    |--------------------------------------------------------------------------
    | Debe tener banco destino
    |--------------------------------------------------------------------------
    */

        if (!$transaction->destination_bank) {

            return response()->json([
                'success' => false,
                'message' => 'El movimiento no tiene un banco destino.'
            ], 422);
        }


        /*
    |--------------------------------------------------------------------------
    | No ejecutar dos veces
    |--------------------------------------------------------------------------
    */

        if ($transaction->executed_at) {

            return response()->json([
                'success' => false,
                'message' => 'Este movimiento ya fue ejecutado.'
            ], 422);
        }


        /*
    |--------------------------------------------------------------------------
    | Cargar moneda
    |--------------------------------------------------------------------------
    */

        $transaction->load(
            'accountBalance'
        );


        if (!$transaction->accountBalance) {

            return response()->json([
                'success' => false,
                'message' => 'El movimiento no tiene una moneda asociada.'
            ], 422);
        }


        /*
    |--------------------------------------------------------------------------
    | Ejecutar
    |--------------------------------------------------------------------------
    |
    | IMPORTANTE:
    |
    | No modificamos el saldo.
    |
    | El dinero ya fue descontado al crear
    | el movimiento de egreso.
    |
    */

        $transaction->update([
            'executed_at' => now(),
            'executed_by' => $user->id,
        ]);


        return response()->json([

            'success' => true,

            'message' =>
            'Transferencia ejecutada correctamente.',

            'transaction_id' =>
            $transaction->id,

            'currency' =>
            $transaction->accountBalance->currency,

            'amount' =>
            $transaction->amount,

            'executed_at' =>
            $transaction->executed_at
                ->format('d/m/Y H:i'),

            'executed_by' =>
            $user->name,

        ]);
    }
}
