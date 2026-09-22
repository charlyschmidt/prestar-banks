<?php

namespace App\Events;

use App\Models\FinancialReminder;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReminderDue implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;


    public function __construct(
        public FinancialReminder $reminder
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Canal
    |--------------------------------------------------------------------------
    |
    | Cada usuario recibe únicamente sus propios recordatorios.
    |
    */

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'user.' . $this->reminder->user_id
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Nombre del evento
    |--------------------------------------------------------------------------
    */

    public function broadcastAs(): string
    {
        return 'reminder.due';
    }


    /*
    |--------------------------------------------------------------------------
    | Payload
    |--------------------------------------------------------------------------
    */

    public function broadcastWith(): array
    {
        return [

            'id' =>
                $this->reminder->id,

            'title' =>
                $this->reminder->title,

            'scheduled_at' =>
                $this->reminder
                    ->scheduled_at
                    ->toIso8601String(),

        ];
    }
}