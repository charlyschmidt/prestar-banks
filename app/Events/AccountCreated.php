<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Account $account
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Canal privado de la empresa
    |--------------------------------------------------------------------------
    */

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'dashboard.' . $this->account->company_id
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
        return 'account.created';
    }

    /*
    |--------------------------------------------------------------------------
    | Payload
    |--------------------------------------------------------------------------
    |
    | El frontend recibe la notificación
    | y ejecuta syncDashboard().
    |
    */

    public function broadcastWith(): array
    {
        return [
            'companyId' => $this->account->company_id,

            'accountId' => $this->account->id,

            'name' => $this->account->name,
        ];
    }
}
