<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Transaction $transaction
    ) {}

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
        return 'transaction.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'transaction_id' => $this->transaction->id,
        ];
    }
}