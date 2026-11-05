<?php

namespace App\Services;

use App\Models\Cheque;
use App\Models\FinancialReminder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinancialReminderService
{
    /*
    |--------------------------------------------------------------------------
    | Recordatorios pendientes de notificación
    |--------------------------------------------------------------------------
    |
    | El Scheduler no tiene empresa activa en sesión.
    | Por eso consultamos sin scopes globales.
    |
    */

    public function getDueForNotification(): Collection
    {
        $reminders = FinancialReminder::withoutGlobalScopes()
            ->where(
                'status',
                FinancialReminder::STATUS_PENDING
            )
            ->whereNull('notified_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->orderBy('id')
            ->get();

        return $reminders
            ->filter(function (FinancialReminder $reminder) {

                /*
                |--------------------------------------------------------------------------
                | Recordatorios manuales
                |--------------------------------------------------------------------------
                |
                | No alteramos su comportamiento.
                |
                */

                if (!$reminder->isChequeReminder()) {
                    return true;
                }

                /*
                |--------------------------------------------------------------------------
                | Recordatorios de cheques
                |--------------------------------------------------------------------------
                */

                if (!$this->canNotifyCheque($reminder)) {

                    $this->dismissChequeReminder($reminder);

                    return false;
                }

                return true;
            })
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Validar recordatorio de cheque
    |--------------------------------------------------------------------------
    */

    public function canNotifyCheque(
        FinancialReminder $reminder
    ): bool {
        if (!$reminder->isChequeReminder()) {
            return false;
        }

        if (
            !$reminder->cheque_id ||
            !$reminder->company_id ||
            !$reminder->user_id ||
            !$reminder->reminder_key
        ) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Verificar cheque y empresa
        |--------------------------------------------------------------------------
        */

        $cheque = Cheque::withoutGlobalScopes()
            ->where('id', $reminder->cheque_id)
            ->where('company_id', $reminder->company_id)
            ->where('kind', 'third_party')
            ->first();

        if (!$cheque) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Solo cheques disponibles en cartera
        |--------------------------------------------------------------------------
        */

        if (
            $cheque->status !==
            Cheque::STATUS_IN_PORTFOLIO
        ) {
            return false;
        }

        if (!$cheque->due_date) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Validar vencimiento original
        |--------------------------------------------------------------------------
        |
        | La clave contiene :due:YYYYMMDD:
        |
        | Si cambia due_date, los recordatorios
        | anteriores ya no son válidos.
        |
        */

        $dueDate = $cheque->due_date->format('Ymd');

        if (
            !str_contains(
                $reminder->reminder_key,
                ':due:' . $dueDate . ':'
            )
        ) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Permisos Super Admin
        |--------------------------------------------------------------------------
        |
        | isSuperAdmin() depende de la sesión.
        |
        | Comprobamos directamente company_user,
        | igual que ChequeReminderService.
        |
        */

        return DB::table('company_user')
            ->join(
                'users',
                'users.id',
                '=',
                'company_user.user_id'
            )
            ->where(
                'company_user.company_id',
                $reminder->company_id
            )
            ->where(
                'company_user.user_id',
                $reminder->user_id
            )
            ->where(
                'company_user.is_admin',
                1
            )
            ->whereNull('users.deleted_at')
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Descartar recordatorio inválido
    |--------------------------------------------------------------------------
    |
    | Nunca descartamos automáticamente avisos
    | que ya fueron notificados.
    |
    | Los registros enviados se conservan
    | para permitir su historial.
    |
    */

    public function dismissChequeReminder(
        FinancialReminder $reminder
    ): void {
        if (!$reminder->isChequeReminder()) {
            return;
        }

        FinancialReminder::withoutGlobalScopes()
            ->where('id', $reminder->id)
            ->where(
                'reminder_type',
                FinancialReminder::TYPE_CHEQUE_DUE
            )
            ->where(
                'status',
                FinancialReminder::STATUS_PENDING
            )
            ->whereNull('notified_at')
            ->update([
                'status' =>
                    FinancialReminder::STATUS_DISMISSED,

                'dismissed_at' => now(),
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Reservar notificación para envío
    |--------------------------------------------------------------------------
    |
    | Actualización condicional atómica.
    |
    | Solo una ejecución puede pasar de
    | notified_at NULL a una fecha.
    |
    | Si otra ejecución ya reservó el aviso,
    | devuelve false.
    |
    | IMPORTANTE:
    | La reserva se debe realizar inmediatamente
    | antes de emitir ReminderDue.
    |
    */

    public function claimForNotification(
        FinancialReminder $reminder
    ): bool {
        /*
         * Revalidar los cheques antes de reservar.
         */

        if (
            $reminder->isChequeReminder() &&
            !$this->canNotifyCheque($reminder)
        ) {
            $this->dismissChequeReminder($reminder);

            return false;
        }

        $updated = FinancialReminder::withoutGlobalScopes()
            ->where('id', $reminder->id)
            ->where(
                'status',
                FinancialReminder::STATUS_PENDING
            )
            ->whereNull('notified_at')
            ->where('scheduled_at', '<=', now())
            ->update([
                'notified_at' => now(),
            ]);

        return $updated === 1;
    }

    /*
    |--------------------------------------------------------------------------
    | Liberar reserva si falla el envío
    |--------------------------------------------------------------------------
    |
    | Solo se usa después de que claimForNotification()
    | haya devuelto true.
    |
    | No cambia el estado del recordatorio.
    |
    */

    public function releaseNotificationClaim(
        FinancialReminder $reminder
    ): void {
        FinancialReminder::withoutGlobalScopes()
            ->where('id', $reminder->id)
            ->where(
                'status',
                FinancialReminder::STATUS_PENDING
            )
            ->whereNotNull('notified_at')
            ->update([
                'notified_at' => null,
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Compatibilidad con código existente
    |--------------------------------------------------------------------------
    |
    | Conservamos este método para no romper
    | llamadas anteriores.
    |
    | El Scheduler nuevo utilizará
    | claimForNotification() antes de emitir.
    |
    */

    public function markAsNotified(
        FinancialReminder $reminder
    ): void {
        FinancialReminder::withoutGlobalScopes()
            ->where('id', $reminder->id)
            ->where(
                'status',
                FinancialReminder::STATUS_PENDING
            )
            ->whereNull('notified_at')
            ->update([
                'notified_at' => now(),
            ]);
    }
}
