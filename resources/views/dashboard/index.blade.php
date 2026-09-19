@extends('layouts.app')


@section('content')

    <div class="page-container dashboard">


        {{-- =========================================================
        HEADER
    ========================================================== --}}

        <div class="page-header dashboard-page-header">

            <div>

                <h1>
                    Jornada actual
                </h1>


                <p id="last-update">

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


                        @if ($summary['movimiento_ultimo']->accountBalance)
                            ·

                            {{ $summary['movimiento_ultimo']->accountBalance->currency }}
                        @endif
                    @else
                        · Sin movimientos todavía
                    @endif

                </p>

            </div>

        </div>



        {{-- =========================================================
        BANCOS
    ========================================================== --}}

        <div class="section-title banks-section-title">

            <h1>
                Mis bancos
            </h1>

        </div>



        <div class="banks-grid">

            @foreach ($summary['accounts'] as $account)
                <a href="{{ route('accounts.movements', $account['id']) }}" class="bank-card-link">

                    <div class="bank-card" data-account-id="{{ $account['id'] }}">


                        {{-- =================================================
                        HEADER BANCO
                    ================================================== --}}

                        <div class="bank-header">

                            @if ($account['logo'])
                                <img src="{{ $account['logo'] }}" alt="{{ $account['name'] }}">
                            @endif


                            <div class="bank-header-content">

                                <h4>
                                    {{ $account['name'] }}
                                </h4>


                                <small>

                                    @switch($account['type'])
                                        @case('bank')
                                            Cuenta bancaria
                                        @break

                                        @case('wallet')
                                            Billetera virtual
                                        @break

                                        @case('cash')
                                            Efectivo
                                        @break

                                        @default
                                            {{ ucfirst($account['type']) }}
                                    @endswitch

                                </small>

                            </div>

                        </div>



                        {{-- =================================================
                        SALDOS POR MONEDA
                    ================================================== --}}

                        <div class="bank-currency-balances">

                            @forelse ($account['balances'] as $balance)
                                <div class="bank-currency-balance"
                                    data-account-balance-id="{{ $balance['account_balance_id'] }}"
                                    data-currency="{{ $balance['currency'] }}">


                                    {{-- SALDO ACTUAL --}}

                                    <div class="bank-balance" data-balance>

                                        {{ $balance['currency'] }}

                                        {{ number_format($balance['balance'], 2, ',', '.') }}

                                    </div>



                                    {{-- SALDO INICIAL --}}

                                    <div class="bank-initial">

                                        Inicial:

                                        <span data-initial-balance>

                                            {{ $balance['currency'] }}

                                            {{ number_format($balance['initial_balance'], 2, ',', '.') }}

                                        </span>

                                    </div>



                                    {{-- DATOS DE LA JORNADA --}}

                                    <div class="bank-footer">


                                        {{-- INGRESOS --}}

                                        <span class="income" data-income title="Ingresos de la jornada">

                                            <i class="bi bi-arrow-up"></i>

                                            {{ number_format($balance['income'], 2, ',', '.') }}

                                        </span>



                                        {{-- EGRESOS --}}

                                        <span class="expense" data-expense title="Egresos de la jornada">

                                            <i class="bi bi-arrow-down"></i>

                                            {{ number_format($balance['expense'], 2, ',', '.') }}

                                        </span>



                                        {{-- RESERVAS --}}

                                        <span class="reserve" data-reserve title="Reservas de la jornada">

                                            <i class="bi bi-lock"></i>

                                            {{ number_format($balance['reserve'] ?? 0, 2, ',', '.') }}

                                        </span>



                                        {{-- MOVIMIENTOS --}}

                                        <span class="bank-movements" data-movements title="Movimientos de la jornada">

                                            <i class="bi bi-arrow-left-right"></i>

                                            {{ $balance['movements'] ?? 0 }}

                                        </span>

                                    </div>


                                </div>


                            @empty

                                <div class="bank-currency-empty">

                                    Sin saldos configurados para esta jornada.

                                </div>
                            @endforelse

                        </div>


                    </div>

                </a>
            @endforeach

        </div>



        {{-- =========================================================
        MOVIMIENTOS
    ========================================================== --}}

        <div class="movements dashboard-movements">


            <div class="section-title">

                <h3>
                    Movimientos de la jornada
                </h3>

            </div>



            <div class="table-responsive">

                <table class="modern-table">


                    <thead>

                        <tr>

                            <th>
                                Fecha
                            </th>

                            <th>
                                Usuario
                            </th>

                            <th>
                                Concepto
                            </th>

                            <th>
                                Banco
                            </th>

                            <th>
                                Moneda
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

                        @forelse ($summary['movements'] as $movement)
                            @php

                                $currency = $movement->accountBalance?->currency ?? '---';

                            @endphp


                            <tr data-transaction-id="{{ $movement->id }}"
                                data-account-balance-id="{{ $movement->account_balance_id }}">


                                {{-- FECHA --}}

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



                                {{-- USUARIO --}}

                                <td>

                                    <div class="movement-user">

                                        <i class="bi bi-person-circle"></i>

                                        <span>

                                            {{ $movement->user?->name ?? 'Sin registro' }}

                                        </span>

                                    </div>

                                </td>



                                {{-- CONCEPTO --}}

                                <td>

                                    <strong>

                                        {{ $movement->description ?? 'Sin descripción' }}

                                    </strong>

                                </td>



                                {{-- BANCO --}}

                                <td>

                                    {{ $movement->account->name }}

                                </td>



                                {{-- MONEDA --}}

                                <td>

                                    <span class="movement-currency">

                                        {{ $currency }}

                                    </span>

                                </td>



                                {{-- TIPO --}}

                                <td>


                                    @if ($movement->type === 'income')
                                        <span class="tag income-tag">

                                            <i class="bi bi-arrow-up"></i>

                                            Ingreso

                                        </span>
                                    @elseif ($movement->type === 'reserve')
                                        <span class="tag reserve-tag">

                                            <i class="bi bi-lock"></i>

                                            Reserva

                                        </span>
                                    @else
                                        <span class="tag expense-tag">

                                            <i class="bi bi-arrow-down"></i>

                                            Egreso

                                        </span>
                                    @endif


                                </td>



                                {{-- MONTO --}}

                                <td class="amount">


                                    @if ($movement->type === 'income')
                                        <span class="amount-income">

                                            +

                                            {{ number_format($movement->amount, 2, ',', '.') }}

                                        </span>
                                    @else
                                        <span class="amount-expense">

                                            -

                                            {{ number_format($movement->amount, 2, ',', '.') }}

                                        </span>
                                    @endif


                                </td>



                                {{-- SALDO INICIAL --}}

                                <td>

                                    {{ number_format($movement->initial_balance ?? 0, 2, ',', '.') }}

                                </td>



                                {{-- SALDO DESPUÉS --}}

                                <td>

                                    {{ number_format($movement->balance_after ?? 0, 2, ',', '.') }}

                                </td>


                            </tr>


                        @empty


                            <tr data-empty-row>

                                <td colspan="9" class="text-center">

                                    Sin movimientos todavía.

                                </td>

                            </tr>
                        @endforelse

                    </tbody>


                </table>

            </div>


        </div>


    </div>

@endsection
