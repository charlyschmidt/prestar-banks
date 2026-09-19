@extends('layouts.app')


@section('content')

<div class="page-container account-movements-page">


    {{-- =========================================================
        HEADER
    ========================================================== --}}

    <div class="account-movements-header">

        <div class="account-movements-heading">

            <a
                href="{{ route('dashboard') }}"
                class="secondary-button back-button"
            >
                <i class="bi bi-arrow-left"></i>
                Volver
            </a>


            <div class="account-movements-account">

                @if ($account->logo)

                    <img
                        src="{{ asset('storage/' . $account->logo) }}"
                        alt="{{ $account->name }}"
                        class="account-movements-logo"
                    >

                @else

                    <div class="account-movements-logo account-movements-logo-empty">

                        <i class="bi bi-bank"></i>

                    </div>

                @endif


                <div class="account-movements-title">

                    <h1>
                        {{ $account->name }}
                    </h1>

                    <p>
                        Movimientos de la jornada actual
                    </p>

                </div>

            </div>

        </div>


        <div class="account-movements-header-actions">

            <a
                href="{{ route('accounts.export', $account->id) }}"
                class="primary-action-button"
            >
                <i class="bi bi-file-earmark-arrow-down"></i>

                Exportar a Excel
            </a>

        </div>

    </div>



    {{-- =========================================================
        RESUMEN POR MONEDA
    ========================================================== --}}

    <div class="account-balance-summary">

        @forelse ($balances as $balance)

            @php

                $currency =
                    $balance->accountBalance?->currency
                    ?? '---';

            @endphp


            <div
                class="account-balance-card"
                data-account-balance-id="{{ $balance->account_balance_id }}"
            >

                <div class="account-balance-card-header">

                    <span class="account-balance-currency">
                        {{ $currency }}
                    </span>

                    <span class="account-balance-label">
                        Saldo de la jornada
                    </span>

                </div>


                <div class="account-balance-values">

                    <div class="account-balance-value">

                        <span>
                            Saldo inicial
                        </span>

                        <strong>
                            {{ number_format(
                                $balance->initial_balance ?? 0,
                                2,
                                ',',
                                '.'
                            ) }}
                        </strong>

                    </div>


                    <div class="account-balance-divider"></div>


                    <div class="account-balance-value account-balance-current">

                        <span>
                            Saldo actual
                        </span>

                        <strong>
                            {{ number_format(
                                $balance->current_balance ?? 0,
                                2,
                                ',',
                                '.'
                            ) }}
                        </strong>

                    </div>

                </div>

            </div>

        @empty

            <div class="account-balance-empty">

                <i class="bi bi-wallet2"></i>

                <span>
                    Esta cuenta no tiene saldos configurados para la jornada actual.
                </span>

            </div>

        @endforelse

    </div>



    {{-- =========================================================
        MOVIMIENTOS
    ========================================================== --}}

    <div class="movements account-movements-table-section">


        <div class="section-title">

            <div>

                <h3>
                    Movimientos
                </h3>

                <span class="account-movements-count">

                    {{ $movements->count() }}

                    {{ $movements->count() === 1 ? 'movimiento' : 'movimientos' }}

                </span>

            </div>

        </div>



        <div class="table-responsive">

            <table class="modern-table account-movements-table">

                <thead>

                    <tr>

                        <th>
                            Fecha
                        </th>

                        <th>
                            Concepto
                        </th>

                        <th>
                            Banco destino
                        </th>

                        <th>
                            Usuario
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
                            Acciones
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse ($movements as $movement)

                        @php

                            $currency =
                                $movement->accountBalance?->currency
                                ?? '---';

                            $canExecute =
                                auth()->user()->canExecuteTransactions();

                            $canManage =
                                auth()->user()->isSuperAdmin()
                                ||
                                $movement->user_id === auth()->id();

                        @endphp


                        <tr
                            data-transaction-id="{{ $movement->id }}"
                            data-account-balance-id="{{ $movement->account_balance_id }}"
                        >


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



                            {{-- CONCEPTO --}}

                            <td>

                                <strong class="movement-description">

                                    {{ $movement->description ?: 'Sin descripción' }}

                                </strong>

                            </td>



                            {{-- BANCO DESTINO --}}

                            <td>

                                @if (
                                    $movement->type === 'expense'
                                    &&
                                    $movement->destination_bank
                                )

                                    <div class="destination-bank">

                                        <i class="bi bi-bank"></i>

                                        <span>
                                            {{ $movement->destination_bank }}
                                        </span>

                                    </div>


                                    <div
                                        class="execution-badge"
                                        data-execution-badge="{{ $movement->id }}"
                                        @if (!$movement->executed_at)
                                            hidden
                                        @endif
                                    >

                                        <i class="bi bi-check-circle-fill"></i>

                                        <span>
                                            Ejecutada
                                        </span>

                                    </div>

                                @else

                                    <span class="text-muted">
                                        —
                                    </span>

                                @endif

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

                                    <span class="green">

                                        +

                                        {{ number_format(
                                            $movement->amount,
                                            2,
                                            ',',
                                            '.'
                                        ) }}

                                    </span>

                                @else

                                    <span class="red">

                                        -

                                        {{ number_format(
                                            $movement->amount,
                                            2,
                                            ',',
                                            '.'
                                        ) }}

                                    </span>

                                @endif

                            </td>



                            {{-- ACCIONES --}}

                            <td>

                                <div class="table-actions">


                                    {{-- EJECUTAR TRANSFERENCIA --}}

                                    @if (
                                        !$canExecute
                                        &&
                                        $movement->type === 'expense'
                                        &&
                                        !$movement->executed_at
                                    )

                                        <button
                                            type="button"
                                            class="icon-button execute-transaction-button"
                                            data-transaction-id="{{ $movement->id }}"
                                            data-execute-url="{{ route('transactions.execute', $movement) }}"
                                            title="Ejecutar transferencia"
                                        >

                                            <i class="bi bi-send-check"></i>

                                        </button>

                                    @endif



                                    {{-- EDITAR / ELIMINAR --}}

                                    @if ($canManage)

                                        <a
                                            href="{{ route('transactions.edit', $movement) }}"
                                            class="icon-button"
                                            title="Editar movimiento"
                                        >

                                            <i class="bi bi-pencil"></i>

                                        </a>


                                        <form
                                            method="POST"
                                            action="{{ route('transactions.destroy', $movement) }}"
                                            onsubmit="return confirm('¿Seguro que querés eliminar este movimiento?')"
                                        >

                                            @csrf
                                            @method('DELETE')


                                            <button
                                                type="submit"
                                                class="icon-button danger"
                                                title="Eliminar movimiento"
                                            >

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </form>

                                    @endif



                                    {{-- SIN ACCIONES --}}

                                    @if (
                                        !(
                                            !$canExecute
                                            &&
                                            $movement->type === 'expense'
                                            &&
                                            !$movement->executed_at
                                        )
                                        &&
                                        !$canManage
                                    )

                                        <span class="text-muted">
                                            —
                                        </span>

                                    @endif

                                </div>

                            </td>


                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="text-center"
                            >

                                Sin movimientos en esta jornada

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection