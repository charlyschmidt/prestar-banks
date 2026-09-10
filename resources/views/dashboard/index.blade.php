@extends('layouts.app')

@section('content')
    <div class="dashboard">

        {{-- HEADER --}}
        <div class="dashboard-header">

            <div>

                <h1>
                    Jornada actual
                </h1>

                <p id="last-update" class="mt-2">

                    Jornada abierta:
                    {{ $summary['day']->date->format('d/m/Y') }}

                    · Desde:

                    {{ $summary['day']->opened_at->format('H:i') }}

                    @if ($summary['movimiento_ultimo'])
                        · Último movimiento:

                        {{ \Carbon\Carbon::parse($summary['movimiento_ultimo']->date)->format('H:i') }}

                        ·

                        {{ $summary['movimiento_ultimo']->description }}

                        ·

                        {{ $summary['movimiento_ultimo']->account->name }}
                    @else
                        · Sin movimientos todavía
                    @endif

                </p>

            </div>

        </div>


        {{-- BANCOS --}}

        {{-- BANCOS --}}

        <div class="section-title mb-4">

            <h1>
                Mis bancos
            </h1>

        </div>

        <div class="banks-grid">

            @foreach ($summary['accounts'] as $account)
                <a href="{{ route('accounts.movements', $account['id']) }}">

                    <div class="bank-card" data-account-id="{{ $account['id'] }}">

                        <div class="bank-header">

                            @if ($account['logo'])
                                <img src="{{ $account['logo'] }}" alt="{{ $account['name'] }}">
                            @endif

                            <div>

                                <h4>
                                    {{ $account['name'] }}
                                </h4>

                                <small>
                                    Cuenta bancaria
                                </small>

                            </div>

                        </div>


                        <div class="bank-balance" data-balance>

                            ${{ number_format($account['balance'], 0, ',', '.') }}

                        </div>


                        <div class="bank-initial">

                            Inicial:
                            ${{ number_format($account['initial_balance'], 0, ',', '.') }}

                        </div>

                        <div class="bank-footer mt-2">

                            <span class="income" data-income>
                                <i class="bi bi-arrow-up"></i>

                                ${{ number_format($account['income'], 0, ',', '.') }}

                            </span>


                            <span class="expense" data-expense>
                                <i class="bi bi-arrow-down"></i>

                                ${{ number_format($account['expense'], 0, ',', '.') }}

                            </span>


                            <span class="bank-movements" data-movements title="Movimientos de la jornada">
                                <i class="bi bi-arrow-left-right"></i>

                                {{ $account['movements'] ?? 0 }}

                            </span>

                        </div>

                    </div>

                </a>
            @endforeach

        </div>


        {{-- MOVIMIENTOS --}}

        <div class="movements">

            <div class="section-title">

                <h3>
                    Movimientos de la jornada
                </h3>

            </div>


            <table>

                <thead>

                    <tr>

                        <th>
                            Fecha
                        </th>

                        <th>
                            Concepto
                        </th>

                        <th>
                            Banco
                        </th>

                        <th>
                            Tipo
                        </th>

                        <th>
                            Monto
                        </th>

                        <th>
                            Saldo inicial
                        </th>

                        <th>
                            Saldo después
                        </th>

                    </tr>

                </thead>


                <tbody id="movements-body">

                    @foreach ($summary['movements'] as $movement)
                        @php

                            $dailyBalance = $movement->account->dailyBalances
                                ->where('financial_day_id', $movement->financial_day_id)
                                ->first();

                        @endphp

                        <tr>

                            <td>

                                <div class="movement-date">

                                    <strong>
                                        {{ \Carbon\Carbon::parse($movement->date)->format('d/m') }}
                                    </strong>

                                    <small>
                                        {{ \Carbon\Carbon::parse($movement->date)->format('H:i') }}
                                    </small>

                                </div>

                            </td>


                            <td>

                                <strong>
                                    {{ $movement->description }}
                                </strong>

                            </td>


                            <td>

                                {{ $movement->account->name }}

                            </td>


                            <td>

                                @if (in_array($movement->type, ['income', 'transfer_in']))
                                    <span class="tag income-tag">

                                        <i class="bi bi-arrow-up"></i>
                                        Ingreso

                                    </span>
                                @else
                                    <span class="tag expense-tag">

                                        <i class="bi bi-arrow-down"></i>
                                        Egreso

                                    </span>
                                @endif

                            </td>


                            <td class="amount">

                                ${{ number_format($movement->amount, 0, ',', '.') }}

                            </td>


                            <td>

                                @if ($dailyBalance)
                                    ${{ number_format($dailyBalance->initial_balance, 0, ',', '.') }}
                                @endif

                            </td>


                            <td>

                                @if ($movement->balance_after)
                                    ${{ number_format($movement->balance_after, 0, ',', '.') }}
                                @endif

                            </td>

                        </tr>
                    @endforeach

                </tbody>

            </table>

        </div>


    </div>
@endsection
