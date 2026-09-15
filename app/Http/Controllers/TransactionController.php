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
        $day = app(\App\Services\FinancialDayService::class)->current();

        $accounts = Account::with([
            'dailyBalances' => function ($q) use ($day) {
                $q->where('financial_day_id', $day->id);
            }
        ])->get();


        return view('transactions.create', [
            'accounts' => $accounts,
            'banks' => ArgentineBanks::all(),
        ]);
    }







    public function store(Request $request, FinancialDayService $financialDayService)
    {

        $data = $request->validate([

            'account_id' => [
                'required',
                'exists:accounts,id'
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
    | Validar jornada abierta
    |--------------------------------------------------------------------------
    */

        $day = $financialDayService->current();


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
    | Buscar saldo diario de la cuenta
    |--------------------------------------------------------------------------
    */

        $balance = AccountDailyBalance::where(
            'financial_day_id',
            $day->id
        )
            ->where(
                'account_id',
                $data['account_id']
            )
            ->first();



        if (!$balance) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'La cuenta no pertenece a la jornada actual.'
                );
        }



        /*
    |--------------------------------------------------------------------------
    | Validar saldo disponible
    |--------------------------------------------------------------------------
    */

        $isExpense = in_array(
            $data['type'],
            [
                'expense',
                'reserve',
                'transfer_out'
            ]
        );


        if (
            $isExpense &&
            $balance->current_balance < $data['amount']
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Saldo insuficiente. Disponible: $' .
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
    | Usuario creador
    |--------------------------------------------------------------------------
    */

        $data['user_id'] = auth()->id();



        /*
    |--------------------------------------------------------------------------
    | Crear movimiento y actualizar saldo
    |--------------------------------------------------------------------------
    */
        if ($data['type'] !== 'expense') {
            $data['destination_bank'] = null;
        }

        DB::transaction(function () use (
            $data,
            $day,
            $balance,
            &$transaction
        ) {

            $data['financial_day_id'] = $day->id;


            $transaction = Transaction::create(
                $data
            );


            if (
                in_array(
                    $transaction->type,
                    [
                        'income'
                    ]
                )
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
        | Guardar saldo después del movimiento
        |--------------------------------------------------------------------------
        */

            $transaction->update([

                'balance_after' => $balance->current_balance

            ]);
        });



        /*
    |--------------------------------------------------------------------------
    | Evento realtime
    |--------------------------------------------------------------------------
    */

        $transaction->refresh();

        $transaction->load([
            'account',
            'user',
            'financialDay'
        ]);

        event(
            new TransactionCreated($transaction)
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
            !auth()->user()->is_admin &&
            $transaction->user_id !== auth()->id()
        ) {

            return redirect()
                ->route('transactions.index')
                ->with(
                    'error',
                    'No tenés permisos para editar este movimiento.'
                );
        }


        $day = app(
            \App\Services\FinancialDayService::class
        )->current();


        if (!$day) {

            return redirect()
                ->route('transactions.index')
                ->with(
                    'error',
                    'No hay una jornada abierta actualmente.'
                );
        }


        $accounts = Account::with([
            'dailyBalances' => function ($q) use ($day) {

                $q->where(
                    'financial_day_id',
                    $day->id
                );
            }
        ])->get();


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
    | Solo puede editarlo el usuario que lo creó
    |--------------------------------------------------------------------------
    */

        if (
            !auth()->user()->is_admin &&
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
                'exists:accounts,id'
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
    |
    | Solamente los egresos tienen banco destino.
    |
    | Reserva -> null
    | Ingreso -> null
    |
    */

        if ($data['type'] !== 'expense') {

            $data['destination_bank'] = null;
        }


        $day = $financialDayService->current();


        if (!$day) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No hay una jornada abierta actualmente.'
                );
        }


        try {

            DB::transaction(function () use (
                $transaction,
                $data,
                $day
            ) {

                /*
            |--------------------------------------------------------------------------
            | 1. Revertir movimiento anterior
            |--------------------------------------------------------------------------
            */

                $oldBalance = AccountDailyBalance::where(
                    'financial_day_id',
                    $transaction->financial_day_id
                )
                    ->where(
                        'account_id',
                        $transaction->account_id
                    )
                    ->lockForUpdate()
                    ->first();


                if (!$oldBalance) {

                    throw new \Exception(
                        'No existe el saldo diario del movimiento anterior.'
                    );
                }


                /*
             * Si el movimiento anterior era ingreso,
             * lo revertimos restándolo.
             *
             * Si era egreso o reserva,
             * lo revertimos sumándolo.
             */

                if (
                    in_array(
                        $transaction->type,
                        [
                            'income'
                        ]
                    )
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
            | 2. Buscar saldo de la nueva cuenta
            |--------------------------------------------------------------------------
            */

                $newBalance = AccountDailyBalance::where(
                    'financial_day_id',
                    $day->id
                )
                    ->where(
                        'account_id',
                        $data['account_id']
                    )
                    ->lockForUpdate()
                    ->first();


                if (!$newBalance) {

                    throw new \Exception(
                        'La cuenta no pertenece a la jornada actual.'
                    );
                }


                /*
            |--------------------------------------------------------------------------
            | IMPORTANTE
            |--------------------------------------------------------------------------
            |
            | Si estamos editando un movimiento de la MISMA cuenta,
            | $oldBalance y $newBalance representan el mismo registro.
            |
            | Como arriba usamos increment/decrement directo en DB,
            | refrescamos para tener el saldo actualizado después
            | de haber revertido el movimiento anterior.
            |
            */

                $newBalance->refresh();


                /*
            |--------------------------------------------------------------------------
            | 3. Validar saldo disponible
            |--------------------------------------------------------------------------
            |
            | Tanto Egreso como Reserva descuentan saldo.
            |--------------------------------------------------------------------------
            */

                $isExpense = in_array(
                    $data['type'],
                    [
                        'expense',
                        'reserve',
                        'transfer_out'
                    ]
                );


                if (
                    $isExpense &&
                    $newBalance->current_balance < $data['amount']
                ) {

                    throw new \Exception(
                        'Saldo insuficiente. Disponible: $' .
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
            | 4. Aplicar nuevo movimiento
            |--------------------------------------------------------------------------
            */

                if (
                    in_array(
                        $data['type'],
                        [
                            'income'
                        ]
                    )
                ) {

                    $newBalance->increment(
                        'current_balance',
                        $data['amount']
                    );
                } else {

                    /*
                 * expense
                 * reserve
                 * transfer_out
                 */

                    $newBalance->decrement(
                        'current_balance',
                        $data['amount']
                    );
                }


                /*
             * Volvemos a refrescar para guardar
             * el saldo final correcto.
             */

                $newBalance->refresh();


                /*
            |--------------------------------------------------------------------------
            | 5. Actualizar movimiento
            |--------------------------------------------------------------------------
            */

                $data['financial_day_id'] =
                    $day->id;

                $data['balance_after'] =
                    $newBalance->current_balance;


                $transaction->update($data);
            });
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
            !auth()->user()->is_admin &&
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


                $balance = AccountDailyBalance::where(
                    'financial_day_id',
                    $transaction->financial_day_id
                )
                    ->where(
                        'account_id',
                        $transaction->account_id
                    )
                    ->lockForUpdate()
                    ->first();



                if (!$balance) {

                    throw new \Exception(
                        'No existe saldo diario'
                    );
                }



                /*
            |--------------------------------------------------------------------------
            | Revertir movimiento
            |--------------------------------------------------------------------------
            */

                if (
                    in_array(
                        $transaction->type,
                        [
                            'income'
                        ]
                    )
                ) {

                    /*
                 * Si eliminamos un ingreso,
                 * debemos restarlo del saldo.
                 */

                    $balance->decrement(
                        'current_balance',
                        $transaction->amount
                    );
                } else {

                    /*
                 * Si eliminamos un egreso,
                 * debemos devolver el dinero al saldo.
                 */

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
            | Como Transaction usa SoftDeletes,
            | esto completa deleted_at y NO borra el registro físicamente.
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
                    'transaction_id' => $transaction->id,
                    'user_id'        => auth()->id(),
                    'mensaje'        => $e->getMessage(),
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
    |
    | Pueden ejecutar:
    |
    | - Super Admin
    | - Administración
    |
    */

        if (
            !$user->is_admin &&
            $user->role !== 'administration'
        ) {
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
    | Ejecutar
    |--------------------------------------------------------------------------
    */

        $transaction->update([
            'executed_at' => now(),
            'executed_by' => $user->id,
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Transferencia ejecutada correctamente.',
            'transaction_id' => $transaction->id,
            'executed_at' => $transaction->executed_at
                ->format('d/m/Y H:i'),
            'executed_by' => $user->name,
        ]);
    }
}
