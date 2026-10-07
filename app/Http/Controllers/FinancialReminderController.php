<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\FinancialReminder;
use App\Services\CompanyContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialReminderController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Listado
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        $companyId = app(
            CompanyContextService::class
        )->id();

        $userId = auth()->id();


        $reminders = FinancialReminder::query()
            ->with([
                'account',
                'accountBalance',
            ])
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'user_id',
                $userId
            )
            ->orderBy('scheduled_at')
            ->get();


        $accounts = Account::query()
            ->with([
                'balances' => function ($query) {
                    $query->orderBy('currency');
                }
            ])
            ->orderBy('name')
            ->get();


        return view(
            'reminders.index',
            compact(
                'reminders',
                'accounts'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Crear
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): RedirectResponse {

        $data = $request->validate([

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'scheduled_at' => [
                'required',
                'date',
            ],

            'recurrence_type' => [
                'nullable',
                'in:weekly,monthly',
            ],

            'account_id' => [
                'nullable',
                'integer',
            ],

            'account_balance_id' => [
                'nullable',
                'integer',
            ],

            'amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

        ]);


        $companyId = app(
            CompanyContextService::class
        )->id();


        /*
         * Si se seleccionó una cuenta, comprobamos
         * que realmente pertenezca a la empresa activa.
         */

        $account = null;

        if (!empty($data['account_id'])) {

            $account = Account::query()
                ->where(
                    'company_id',
                    $companyId
                )
                ->findOrFail(
                    $data['account_id']
                );
        }


        /*
         * Si se seleccionó una moneda/balance,
         * comprobamos que pertenezca a la cuenta.
         */

        $accountBalanceId = null;

        if (!empty($data['account_balance_id'])) {

            abort_unless(
                $account,
                422
            );

            $balance = $account
                ->balances()
                ->whereKey(
                    $data['account_balance_id']
                )
                ->firstOrFail();

            $accountBalanceId = $balance->id;
        }


        FinancialReminder::create([
            'company_id' => $companyId,
            'user_id' => auth()->id(),
            'title' => $data['title'],
            'scheduled_at' => $data['scheduled_at'],
            'status' => 'pending',
            'recurrence_type' => $data['recurrence_type'] ?? null,
        ]);


        return back()->with(
            'success',
            'Recordatorio creado correctamente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Marcar como realizado
    |--------------------------------------------------------------------------
    */

    /*
|--------------------------------------------------------------------------
| Marcar como realizado
|--------------------------------------------------------------------------
*/

    public function complete(
        FinancialReminder $reminder
    ) {

        $this->ensureReminderBelongsToUser(
            $reminder
        );


        /*
    |--------------------------------------------------------------------------
    | GUARDAMOS DATOS ANTES DE COMPLETAR
    |--------------------------------------------------------------------------
    */

        $recurrenceType = $reminder->recurrence_type;

        $nextScheduledAt = null;


        /*
    |--------------------------------------------------------------------------
    | CALCULAR PRÓXIMA FECHA
    |--------------------------------------------------------------------------
    */

        if ($recurrenceType === 'weekly') {

            $nextScheduledAt = $reminder
                ->scheduled_at
                ->copy()
                ->addWeek();
        }


        if ($recurrenceType === 'monthly') {

            $nextScheduledAt = $reminder
                ->scheduled_at
                ->copy()
                ->addMonthNoOverflow();
        }


        /*
    |--------------------------------------------------------------------------
    | COMPLETAR ACTUAL
    |--------------------------------------------------------------------------
    */

        $reminder->update([

            'status' => 'completed',

            'completed_at' => now(),

        ]);

        $nextReminder = null;
        /*
    |--------------------------------------------------------------------------
    | CREAR SIGUIENTE RECORDATORIO
    |--------------------------------------------------------------------------
    */

        if ($nextScheduledAt) {

            $nextReminder = FinancialReminder::create([

                'company_id' => $reminder->company_id,
                'user_id' => $reminder->user_id,
                'account_id' => $reminder->account_id,
                'account_balance_id' => $reminder->account_balance_id,
                'title' => $reminder->title,
                'amount' => $reminder->amount,
                'scheduled_at' => $nextScheduledAt,
                'recurrence_type' => $recurrenceType,
                'status' => 'pending',

            ]);
        }


        /*
    |--------------------------------------------------------------------------
    | RESPUESTA
    |--------------------------------------------------------------------------
    */

        if (request()->expectsJson()) {

            return response()->json([

                'success' => true,

                'reminder_id' => $reminder->id,

                'recurring' => $nextScheduledAt !== null,

                'next_reminder' => $nextReminder
                    ? [
                        'id' => $nextReminder->id,
                        'title' => $nextReminder->title,
                        'scheduled_at' => $nextReminder->scheduled_at->toIso8601String(),
                    ]
                    : null,

                'next_scheduled_at' => $nextScheduledAt
                    ? $nextScheduledAt->toIso8601String()
                    : null,

                'message' => $nextScheduledAt
                    ? 'Recordatorio realizado. Se creó el próximo recordatorio.'
                    : 'Recordatorio marcado como realizado.',

            ]);
        }


        return back()->with(
            'success',
            $nextScheduledAt
                ? 'Recordatorio realizado. Se creó el próximo recordatorio.'
                : 'Recordatorio marcado como realizado.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminar
    |--------------------------------------------------------------------------
    */

    public function destroy(
        FinancialReminder $reminder
    ): RedirectResponse {

        $this->ensureReminderBelongsToUser(
            $reminder
        );


        $reminder->delete();


        return back()->with(
            'success',
            'Recordatorio eliminado.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Seguridad
    |--------------------------------------------------------------------------
    */

    private function ensureReminderBelongsToUser(
        FinancialReminder $reminder
    ): void {

        $companyId = app(
            CompanyContextService::class
        )->id();


        abort_unless(

            (int) $reminder->company_id
                ===
                (int) $companyId

                &&

                (int) $reminder->user_id
                ===
                (int) auth()->id(),

            404

        );
    }
}
