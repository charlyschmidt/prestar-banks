<?php

namespace App\Services;

use App\Models\FinancialReminder;
use Illuminate\Support\Collection;

class FinancialReminderService
{
    /*
    |--------------------------------------------------------------------------
    | Recordatorios que deben notificarse
    |--------------------------------------------------------------------------
    */

    public function getDueForNotification(): Collection
    {
        return FinancialReminder::query()
            ->where('status', 'pending')
            ->whereNull('notified_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->get();
    }


    /*
    |--------------------------------------------------------------------------
    | Marcar como notificado
    |--------------------------------------------------------------------------
    */

    public function markAsNotified(
        FinancialReminder $reminder
    ): void {

        $reminder->update([
            'notified_at' => now(),
        ]);
    }
}