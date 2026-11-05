<?php

namespace App\Services;

use App\Models\Cheque;
use App\Models\FinancialReminder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LogicException;
use RuntimeException;

class ChequeReminderService
{
    /*
    |--------------------------------------------------------------------------
    | Servicio de calendario bancario
    |--------------------------------------------------------------------------
    */

    public function __construct(
        private readonly BankHolidayService $bankHolidays
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Sincronización general
    |--------------------------------------------------------------------------
    |
    | dryRun = true:
    | - No modifica cheques ni recordatorios.
    | - No emite notificaciones.
    | - Puede consultar el BCRA y actualizar su caché.
    |
    | dryRun = false:
    | - Sincroniza los recordatorios.
    | - Requiere habilitación en config/cheques.php.
    |
    */

    public function sync(bool $dryRun = false): array
    {
        if (
            !$dryRun &&
            !config('cheques.reminders.enabled', false)
        ) {
            throw new LogicException(
                'Los recordatorios de cheques están desactivados.'
            );
        }

        $stats = [
            'processed' => 0,
            'eligible' => 0,
            'would_create' => 0,
            'would_dismiss' => 0,
            'adjusted_due_dates' => 0,
            'calendar_unavailable' => 0,
            'errors' => 0,
        ];

        $timezone = config(
            'cheques.reminders.timezone',
            'America/Argentina/Buenos_Aires'
        );

        $today = CarbonImmutable::today($timezone);

        /*
        |--------------------------------------------------------------------------
        | Procesar todas las empresas
        |--------------------------------------------------------------------------
        |
        | El Scheduler no dispone de empresa en sesión.
        | Las consultas se limitan posteriormente
        | mediante company_id.
        |
        */

        Cheque::withoutGlobalScopes()
            ->chunkById(100, function ($cheques) use (
                $dryRun,
                $today,
                $timezone,
                &$stats
            ) {
                foreach ($cheques as $cheque) {

                    $stats['processed']++;

                    try {

                        $this->syncCheque(
                            $cheque,
                            $dryRun,
                            $today,
                            $timezone,
                            $stats
                        );

                    } catch (\Throwable $exception) {

                        /*
                         * Un error en un cheque no debe
                         * detener a los demás.
                         */

                        $stats['errors']++;

                        Log::error(
                            'Error sincronizando recordatorios de cheque',
                            [
                                'cheque_id' => $cheque->id,
                                'company_id' => $cheque->company_id,
                                'message' => $exception->getMessage(),
                            ]
                        );
                    }
                }
            });

        return $stats;
    }

    /*
    |--------------------------------------------------------------------------
    | Sincronización individual
    |--------------------------------------------------------------------------
    */

    private function syncCheque(
        Cheque $cheque,
        bool $dryRun,
        CarbonImmutable $today,
        string $timezone,
        array &$stats
    ): void {

        $companyId = (int) $cheque->company_id;

        /*
        |--------------------------------------------------------------------------
        | Recordatorios existentes
        |--------------------------------------------------------------------------
        */

        $existing = FinancialReminder::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('cheque_id', $cheque->id)
            ->where(
                'reminder_type',
                FinancialReminder::TYPE_CHEQUE_DUE
            )
            ->get([
                'id',
                'reminder_key',
                'status',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Verificar disponibilidad
        |--------------------------------------------------------------------------
        */

        if (
            $cheque->kind !== 'third_party' ||
            $cheque->status !== Cheque::STATUS_IN_PORTFOLIO ||
            !$cheque->due_date
        ) {

            $this->dismissObsolete(
                $existing,
                [],
                $dryRun,
                $companyId,
                $cheque->id,
                $stats
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Fecha nominal
        |--------------------------------------------------------------------------
        */

        $dueDate = CarbonImmutable::parse(
            $cheque->due_date->format('Y-m-d'),
            $timezone
        )->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | Calendario bancario oficial
        |--------------------------------------------------------------------------
        |
        | Si el año no está disponible o falla
        | la consulta, no inventamos una fecha.
        |
        | Tampoco descartamos los recordatorios
        | previamente existentes.
        |
        */

        try {

            $presentationDate =
                $this->bankHolidays->nextBusinessDay(
                    $dueDate
                );

            /*
             * Al calcular recordatorios anticipados
             * podríamos cruzar hacia otro año.
             * Ese calendario también debe existir.
             */

            $stages = $this->buildStages(
                $presentationDate,
                $today
            );

        } catch (RuntimeException $exception) {

            $stats['calendar_unavailable']++;

            Log::warning(
                'Calendario bancario no disponible para cheque',
                [
                    'cheque_id' => $cheque->id,
                    'company_id' => $companyId,
                    'due_date' => $dueDate->format('Y-m-d'),
                    'message' => $exception->getMessage(),
                ]
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Fecha ajustada
        |--------------------------------------------------------------------------
        */

        if (!$presentationDate->equalTo($dueDate)) {
            $stats['adjusted_due_dates']++;
        }

        $stats['eligible']++;

        /*
        |--------------------------------------------------------------------------
        | Solo Super Admin de la empresa
        |--------------------------------------------------------------------------
        |
        | Equivalente al criterio isSuperAdmin(),
        | pero sin depender de la sesión.
        |
        */

        $userIds = DB::table('company_user')
            ->join(
                'users',
                'users.id',
                '=',
                'company_user.user_id'
            )
            ->where(
                'company_user.company_id',
                $companyId
            )
            ->where(
                'company_user.is_admin',
                1
            )
            ->whereNull('users.deleted_at')
            ->distinct()
            ->pluck('company_user.user_id');

        $validKeys = [];

        /*
        |--------------------------------------------------------------------------
        | Recordatorios por usuario y etapa
        |--------------------------------------------------------------------------
        */

        foreach ($userIds as $userId) {

            foreach ($stages as $stage) {

                $scheduledDate = $stage['date'];

                /*
                 * No generamos retroactivamente
                 * avisos de etapas anteriores.
                 */

                if ($scheduledDate->lt($today)) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Identificador único
                |--------------------------------------------------------------------------
                |
                | Cheque + usuario + vencimiento
                | nominal + etapa.
                |
                */

                $key = implode(':', [
                    'cheque',
                    $cheque->id,
                    'user',
                    $userId,
                    'due',
                    $dueDate->format('Ymd'),
                    'stage',
                    $stage['key'],
                ]);

                $validKeys[] = $key;

                /*
                 * No volver a generar avisos que
                 * ya existen, aun si fueron enviados.
                 */

                if (
                    $existing->contains(
                        'reminder_key',
                        $key
                    )
                ) {
                    continue;
                }

                $stats['would_create']++;

                if ($dryRun) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Hora de notificación
                |--------------------------------------------------------------------------
                */

                $time = config(
                    'cheques.reminders.notification_time',
                    '09:00'
                );

                $scheduledAt = CarbonImmutable::parse(
                    $scheduledDate->format('Y-m-d')
                        . ' ' . $time,
                    $timezone
                );

                $now = CarbonImmutable::now($timezone);

                if ($scheduledAt->lt($now)) {
                    $scheduledAt = $now;
                }

                /*
                |--------------------------------------------------------------------------
                | Título
                |--------------------------------------------------------------------------
                */

                $title = sprintf(
                    '%s - %s - Nº %s',
                    $stage['title'],
                    $cheque->bank ?: 'Banco no indicado',
                    $cheque->number ?: $cheque->id
                );

                /*
                |--------------------------------------------------------------------------
                | Persistencia
                |--------------------------------------------------------------------------
                */

                FinancialReminder::withoutGlobalScopes()
                    ->firstOrCreate(
                        [
                            'reminder_key' => $key,
                        ],
                        [
                            'company_id' => $companyId,
                            'user_id' => (int) $userId,
                            'cheque_id' => $cheque->id,

                            'reminder_type' =>
                                FinancialReminder::TYPE_CHEQUE_DUE,

                            'title' => $title,
                            'amount' => $cheque->nominal_amount,
                            'scheduled_at' => $scheduledAt,

                            'status' =>
                                FinancialReminder::STATUS_PENDING,

                            'recurrence_type' => null,
                        ]
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Cancelar recordatorios obsoletos
        |--------------------------------------------------------------------------
        */

        $this->dismissObsolete(
            $existing,
            $validKeys,
            $dryRun,
            $companyId,
            $cheque->id,
            $stats
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Construir etapas de notificación
    |--------------------------------------------------------------------------
    */

    private function buildStages(
        CarbonImmutable $presentationDate,
        CarbonImmutable $today
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Cheques atrasados
        |--------------------------------------------------------------------------
        |
        | Generamos una sola etapa overdue.
        | No generamos una alerta diaria nueva.
        |
        */

        if ($presentationDate->lt($today)) {

            if (
                !config(
                    'cheques.reminders.notify_overdue',
                    true
                )
            ) {
                return [];
            }

            return [
                [
                    'key' => 'overdue',
                    'date' => $today,
                    'title' =>
                        'Cheque pendiente de presentación vencida',
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Anticipación configurable
        |--------------------------------------------------------------------------
        */

        $advanceDays = config(
            'cheques.reminders.advance_business_days',
            [3, 1]
        );

        $stages = [];

        foreach ($advanceDays as $days) {

            $days = (int) $days;

            if ($days <= 0) {
                continue;
            }

            $stages[] = [
                'key' => 'before_' . $days,

                'date' =>
                    $this->bankHolidays->subtractBusinessDays(
                        $presentationDate,
                        $days
                    ),

                'title' =>
                    'Cheque a presentar en '
                    . $days
                    . ' días hábiles',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Día de presentación
        |--------------------------------------------------------------------------
        */

        $stages[] = [
            'key' => 'due_today',
            'date' => $presentationDate,
            'title' => 'Cheque para presentar hoy',
        ];

        return $stages;
    }

    /*
    |--------------------------------------------------------------------------
    | Descartar recordatorios obsoletos
    |--------------------------------------------------------------------------
    |
    | Se conservan los registros históricos.
    | Solo se modifican los pendientes.
    |
    */

    private function dismissObsolete(
        $existing,
        array $validKeys,
        bool $dryRun,
        int $companyId,
        int $chequeId,
        array &$stats
    ): void {

        $obsoleteIds = $existing
            ->filter(function ($reminder) use ($validKeys) {

                return
                    $reminder->status ===
                        FinancialReminder::STATUS_PENDING
                    && !in_array(
                        $reminder->reminder_key,
                        $validKeys,
                        true
                    );
            })
            ->pluck('id')
            ->all();

        $stats['would_dismiss'] += count($obsoleteIds);

        if ($dryRun || empty($obsoleteIds)) {
            return;
        }

        FinancialReminder::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('cheque_id', $chequeId)
            ->whereIn('id', $obsoleteIds)
            ->where(
                'status',
                FinancialReminder::STATUS_PENDING
            )
            ->update([
                'status' =>
                    FinancialReminder::STATUS_DISMISSED,

                'dismissed_at' => now(),
            ]);
    }
}
