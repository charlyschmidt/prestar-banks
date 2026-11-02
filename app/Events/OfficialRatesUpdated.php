<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Los tipos de cambio son públicos: no necesitan canal privado por empresa. */
class OfficialRatesUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public array $ratesData) {}

    public function broadcastOn(): array
    {
        return [new Channel('official-rates')];
    }

    public function broadcastAs(): string
    {
        return 'official.rates.updated';
    }

    public function broadcastWith(): array
    {
        return $this->ratesData;
    }
}
