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


        return view(
            'transactions.create',
            compact('accounts')
        );
    }







    public function store(
        Request $request,
        FinancialDayService $financialDayService
    ) {

        $data = $request->validate([

            'account_id' => [
                'required',
                'exists:accounts,id'
            ],

            'type' => [
                'required',
                'in:income,expense,transfer_in,transfer_out'
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
            ]

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
                            0,
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
                        'income',
                        'transfer_in'
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

        $transaction->load('account');


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


        $day = app(\App\Services\FinancialDayService::class)->current();


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
            compact(
                'transaction',
                'accounts'
            )
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



        $data = $request->validate([

            'account_id' => 'required|exists:accounts,id',

            'type' => 'required|in:income,expense,transfer_in,transfer_out',

            'amount' => 'required|numeric|min:0.01',

            'description' => 'nullable|string',

            'date' => 'required|date'

        ]);



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



                if (
                    in_array(
                        $transaction->type,
                        [
                            'income',
                            'transfer_in'
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
            | 3. Validar saldo disponible
            |--------------------------------------------------------------------------
            */

                $isExpense = in_array(
                    $data['type'],
                    [
                        'expense',
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
                                0,
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
                            'income',
                            'transfer_in'
                        ]
                    )
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



                /*
            |--------------------------------------------------------------------------
            | 5. Actualizar movimiento
            |--------------------------------------------------------------------------
            */

                $data['financial_day_id'] = $day->id;

                $data['balance_after'] = $newBalance->current_balance;


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
                            'income',
                            'transfer_in'
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
}
