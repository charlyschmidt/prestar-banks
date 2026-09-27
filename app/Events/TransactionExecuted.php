<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionExecuted implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;


    public function __construct(
        public Transaction $transaction
    ) {
    }


    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'dashboard.' . $this->transaction->company_id
            ),
        ];
    }


    public function broadcastAs(): string
    {
        return 'transaction.executed';
    }


    public function broadcastWith(): array
    {
        return [

            'companyId' =>
                $this->transaction->company_id,

            'transaction_id' =>
                $this->transaction->id,

            'executed_at' =>
                $this->transaction->executed_at?->toISOString(),

            'executed_by' =>
                $this->transaction->executed_by,

        ];
    }
}