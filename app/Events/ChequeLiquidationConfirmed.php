<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChequeLiquidationConfirmed implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /*
    |--------------------------------------------------------------------------
    | AERIA Finance — Liquidación confirmada
    |--------------------------------------------------------------------------
    |
    | Notifica los cambios de estado de una liquidación.
    |
    | No genera movimientos financieros.
    | No modifica saldos.
    | No cambia estados en base de datos.
    |
    | Solamente informa que una operación ya fue confirmada.
    |
    */

    public function __construct(
        public int $companyId,
        public int $liquidationId,
        public int $receivedChequesCount,
        public int $deliveredChequesCount,
        public int $deliveriesCount
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Canal privado por empresa
    |--------------------------------------------------------------------------
    */

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'dashboard.' . $this->companyId
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
        return 'cheque.liquidation.confirmed';
    }

    /*
    |--------------------------------------------------------------------------
    | Datos del evento
    |--------------------------------------------------------------------------
    */

    public function broadcastWith(): array
    {
        return [
            'companyId' => $this->companyId,

            'liquidationId' => $this->liquidationId,

            'status' => 'confirmed',

            'receivedChequesCount' =>
                $this->receivedChequesCount,

            'deliveredChequesCount' =>
                $this->deliveredChequesCount,

            'deliveriesCount' =>
                $this->deliveriesCount,

            'occurredAt' =>
                now()->toIso8601String(),
        ];
    }
}
