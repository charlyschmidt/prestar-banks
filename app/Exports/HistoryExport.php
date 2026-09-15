<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Database\Eloquent\Builder;


class HistoryExport implements
    FromQuery,
    WithHeadings,
    WithMapping
{
    protected array $filters;


    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }


    public function query(): Builder
    {
        $query = Transaction::query()
            ->with([
                'account',
                'user',
                'executedBy'
            ]);


        /*
        |--------------------------------------------------------------------------
        | Fechas
        |--------------------------------------------------------------------------
        */

        if (!empty($this->filters['date_from'])) {

            $query->whereDate(
                'date',
                '>=',
                $this->filters['date_from']
            );
        }


        if (!empty($this->filters['date_to'])) {

            $query->whereDate(
                'date',
                '<=',
                $this->filters['date_to']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Cuenta
        |--------------------------------------------------------------------------
        */

        if (!empty($this->filters['account_id'])) {

            $query->where(
                'account_id',
                $this->filters['account_id']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Usuario
        |--------------------------------------------------------------------------
        */

        if (!empty($this->filters['user_id'])) {

            $query->where(
                'user_id',
                $this->filters['user_id']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Tipo
        |--------------------------------------------------------------------------
        */

        if (!empty($this->filters['type'])) {

            $query->where(
                'type',
                $this->filters['type']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Banco destino
        |--------------------------------------------------------------------------
        */

        if (!empty($this->filters['destination_bank'])) {

            $query->where(
                'destination_bank',
                $this->filters['destination_bank']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Estado ejecución
        |--------------------------------------------------------------------------
        */

        if (!empty($this->filters['execution'])) {

            if (
                $this->filters['execution']
                === 'executed'
            ) {

                $query->whereNotNull(
                    'executed_at'
                );
            } elseif (
                $this->filters['execution']
                === 'pending'
            ) {

                $query
                    ->whereNull('executed_at')
                    ->where('type', 'expense');
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Monto
        |--------------------------------------------------------------------------
        */

        if (
            $this->filters['amount_min']
            ?? null
        ) {

            $query->where(
                'amount',
                '>=',
                $this->filters['amount_min']
            );
        }


        if (
            $this->filters['amount_max']
            ?? null
        ) {

            $query->where(
                'amount',
                '<=',
                $this->filters['amount_max']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Búsqueda global
        |--------------------------------------------------------------------------
        */

        if (!empty($this->filters['search'])) {

            $search = trim(
                $this->filters['search']
            );


            $query->where(
                function ($q) use ($search) {

                    $q->where(
                        'description',
                        'like',
                        '%' . $search . '%'
                    );


                    $q->orWhere(
                        'destination_bank',
                        'like',
                        '%' . $search . '%'
                    );


                    $q->orWhereHas(
                        'account',
                        function ($account) use ($search) {

                            $account->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    );


                    $q->orWhereHas(
                        'user',
                        function ($user) use ($search) {

                            $user
                                ->where(
                                    'name',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'username',
                                    'like',
                                    '%' . $search . '%'
                                );
                        }
                    );
                }
            );
        }


        return $query
            ->orderByDesc('date')
            ->orderByDesc('id');
    }


    /*
    |--------------------------------------------------------------------------
    | Encabezados Excel
    |--------------------------------------------------------------------------
    */

    public function headings(): array
    {
        return [
            'Fecha',
            'Hora',
            'Cuenta',
            'Usuario',
            'Tipo',
            'Descripción',
            'Banco destino',
            'Estado',
            'Monto',
            'Ejecutado por',
            'Fecha ejecución',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Datos
    |--------------------------------------------------------------------------
    */

    public function map($transaction): array
    {
        $type = match ($transaction->type) {
            'income' => 'Ingreso',
            'expense' => 'Egreso',
            'reserve' => 'Reserva',
            default => $transaction->type,
        };


        $status = match (true) {

            $transaction->type === 'reserve'
            => 'Reservado',

            $transaction->type === 'expense'
                && $transaction->executed_at
            => 'Ejecutado',

            $transaction->type === 'expense'
            => 'Pendiente',

            default
            => '',
        };


        $amount =
            $transaction->type === 'income'
            ? $transaction->amount
            : -$transaction->amount;


        return [
            $transaction->date?->format('d/m/Y'),

            $transaction->date?->format('H:i'),

            $transaction->account?->name
                ?? 'Cuenta eliminada',

            $transaction->user?->name
                ?? 'Sin registro',

            $type,

            $transaction->description
                ?? '',

            $transaction->destination_bank
                ?? '',

            $status,

            $amount,

            $transaction->executedBy?->name
                ?? '',

            $transaction->executed_at
                ? $transaction->executed_at
                ->format('d/m/Y H:i')
                : '',
        ];
    }
}
