<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;


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


    /*
    |--------------------------------------------------------------------------
    | Query
    |--------------------------------------------------------------------------
    */

    public function query(): Builder
    {
        $query = Transaction::query()
            ->with([
                'account',
                'accountBalance',
                'user',
                'executedBy',
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
        | Moneda
        |--------------------------------------------------------------------------
        */

        if (!empty($this->filters['currency'])) {

            $currency = strtoupper(
                trim($this->filters['currency'])
            );


            $query->whereHas(
                'accountBalance',
                function ($q) use ($currency) {

                    $q->where(
                        'currency',
                        $currency
                    );
                }
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
        | Estado de ejecución
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
        | Monto mínimo
        |--------------------------------------------------------------------------
        */

        if (
            isset($this->filters['amount_min'])
            &&
            $this->filters['amount_min'] !== ''
        ) {

            $query->where(
                'amount',
                '>=',
                $this->filters['amount_min']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Monto máximo
        |--------------------------------------------------------------------------
        */

        if (
            isset($this->filters['amount_max'])
            &&
            $this->filters['amount_max'] !== ''
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


                    /*
                    |--------------------------------------------------------------------------
                    | Cuenta
                    |--------------------------------------------------------------------------
                    */

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


                    /*
                    |--------------------------------------------------------------------------
                    | Moneda
                    |--------------------------------------------------------------------------
                    */

                    $q->orWhereHas(
                        'accountBalance',
                        function ($balance) use ($search) {

                            $balance->where(
                                'currency',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Usuario
                    |--------------------------------------------------------------------------
                    */

                    $q->orWhereHas(
                        'user',
                        function ($user) use ($search) {

                            $user->where(
                                function ($q) use ($search) {

                                    $q->where(
                                        'name',
                                        'like',
                                        '%' . $search . '%'
                                    )
                                        ->orWhere(
                                            'email',
                                            'like',
                                            '%' . $search . '%'
                                        );
                                }
                            );
                        }
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Orden
        |--------------------------------------------------------------------------
        */

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
            'Moneda',
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
        /*
        |--------------------------------------------------------------------------
        | Tipo
        |--------------------------------------------------------------------------
        */

        $type = match ($transaction->type) {

            'income' =>
            'Ingreso',

            'expense' =>
            'Egreso',

            'reserve' =>
            'Reserva',

            default =>
            $transaction->type,

        };


        /*
        |--------------------------------------------------------------------------
        | Estado
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Monto
        |--------------------------------------------------------------------------
        |
        | El monto continúa siendo numérico.
        |
        | La moneda se exporta en su propia columna para que Excel
        | pueda seguir trabajando con el valor como número.
        |
        */

        $amount =
            $transaction->type === 'income'
            ? (float) $transaction->amount
            : -(float) $transaction->amount;


        /*
        |--------------------------------------------------------------------------
        | Fila
        |--------------------------------------------------------------------------
        */

        return [

            $transaction->date?->format(
                'd/m/Y'
            ),

            $transaction->date?->format(
                'H:i'
            ),

            $transaction->account?->name
                ?? 'Cuenta eliminada',

            $transaction->accountBalance?->currency
                ?? '---',

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