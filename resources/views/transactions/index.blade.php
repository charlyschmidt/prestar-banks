@extends('layouts.app')


@section('content')
    <div class="page-container transactions-page">


        {{-- =========================================================
    HEADER
========================================================== --}}

        <div class="transactions-header">

            <div class="transactions-header-main">

                <a href="{{ route('dashboard') }}" class="secondary-button back-button">
                    <i class="bi bi-arrow-left"></i>

                    <span>
                        Volver
                    </span>
                </a>


                <div class="transactions-title">

                    <h1>
                        Movimientos
                    </h1>

                    <p>
                        Jornada actual
                    </p>

                </div>

            </div>


            <div class="transactions-header-actions">

                <a href="{{ route('transactions.create') }}" class="primary-action-button">
                    <i class="bi bi-plus"></i>

                    <span>
                        Nuevo
                    </span>
                </a>


                <a href="{{ route('transactions.export') }}" class="primary-action-button">
                    <i class="bi bi-file-earmark-arrow-down"></i>

                    <span>
                        Exportar Excel
                    </span>
                </a>

            </div>

        </div>



        {{-- =========================================================
        MOVIMIENTOS
    ========================================================== --}}

        <div class="movements-card transactions-table-card">

            <div class="table-responsive">

                <table class="modern-table transactions-table">

                    <thead>

                        <tr>

                            <th>
                                Fecha
                            </th>

                            <th>
                                Cuenta
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
                                Descripción
                            </th>

                            <th class="text-end">
                                Monto
                            </th>

                            <th class="text-end">
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody id="transactions-body">

                        @forelse ($transactions as $transaction)
                            @php

                                $currency = $transaction->accountBalance?->currency ?? '---';

                                $canExecute = auth()->user()->canExecuteTransactions();

                                $canManage = auth()->user()->isSuperAdmin() || $transaction->user_id === auth()->id();

                            @endphp


                            <tr data-transaction-id="{{ $transaction->id }}"
                                data-account-balance-id="{{ $transaction->account_balance_id }}">


                                {{-- FECHA --}}

                                <td>

                                    <div class="movement-date">

                                        <strong>
                                            {{ \Carbon\Carbon::parse($transaction->date)->format('d/m') }}
                                        </strong>

                                        <small>
                                            {{ \Carbon\Carbon::parse($transaction->date)->format('H:i') }}
                                        </small>

                                    </div>

                                </td>



                                {{-- CUENTA --}}

                                <td>

                                    <div class="account-cell">

                                        @if ($transaction->account?->logo)
                                            <img src="{{ Storage::url($transaction->account->logo) }}"
                                                alt="{{ $transaction->account->name }}">
                                        @else
                                            <div class="mini-logo">

                                                <i class="bi bi-bank"></i>

                                            </div>
                                        @endif


                                        <span>
                                            {{ $transaction->account?->name ?? 'Cuenta eliminada' }}
                                        </span>

                                    </div>

                                </td>



                                {{-- BANCO DESTINO --}}

                                <td>

                                    @if ($transaction->type === 'expense' && $transaction->destination_bank)
                                        <div class="destination-bank">

                                            <i class="bi bi-bank"></i>

                                            <span>
                                                {{ $transaction->destination_bank }}
                                            </span>

                                        </div>


                                        <div class="execution-badge" data-execution-badge="{{ $transaction->id }}"
                                            @if (!$transaction->executed_at) style="display: none;" @endif>

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
                                            {{ $transaction->user?->name ?? 'Sin registro' }}
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

                                    @if ($transaction->type === 'income')
                                        <span class="movement-income">

                                            <i class="bi bi-arrow-up"></i>

                                            Ingreso

                                        </span>
                                    @elseif ($transaction->type === 'reserve')
                                        <span class="movement-reserve">

                                            <i class="bi bi-lock"></i>

                                            Reserva

                                        </span>
                                    @else
                                        <span class="movement-expense">

                                            <i class="bi bi-arrow-down"></i>

                                            Egreso

                                        </span>
                                    @endif

                                </td>



                                {{-- DESCRIPCIÓN --}}

                                <td>

                                    <span class="transaction-description">

                                        {{ $transaction->description ?? 'Sin descripción' }}

                                    </span>

                                </td>



                                {{-- MONTO --}}

                                <td class="text-end amount">

                                    @if ($transaction->type === 'income')
                                        <span class="amount-income">

                                            +

                                            {{ number_format($transaction->amount, 2, ',', '.') }}

                                        </span>
                                    @else
                                        <span class="amount-expense">

                                            -

                                            {{ number_format($transaction->amount, 2, ',', '.') }}

                                        </span>
                                    @endif

                                </td>



                                {{-- ACCIONES --}}

                                <td>

                                    <div class="table-actions">


                                        {{-- EJECUTAR TRANSFERENCIA --}}

                                        @if ($canExecute && $transaction->type === 'expense' && !$transaction->executed_at)
                                            <button type="button" class="icon-button execute-transaction-button"
                                                data-transaction-id="{{ $transaction->id }}"
                                                data-execute-url="{{ route('transactions.execute', $transaction) }}"
                                                title="Ejecutar transferencia">

                                                <i class="bi bi-send-check"></i>

                                            </button>
                                        @endif



                                        {{-- VER TRANSFERENCIA EJECUTADA --}}

                                        @if ($transaction->type === 'expense' && $transaction->executed_at)
                                            <button type="button" class="icon-button" data-bs-toggle="modal"
                                                data-bs-target="#transactionDetailModal{{ $transaction->id }}"
                                                title="Ver detalle de transferencia">

                                                <i class="bi bi-eye"></i>

                                            </button>
                                        @endif



                                        {{-- EDITAR / ELIMINAR --}}

                                        @if ($canManage)
                                            <a href="{{ route('transactions.edit', $transaction) }}" class="icon-button"
                                                title="Editar movimiento">

                                                <i class="bi bi-pencil"></i>

                                            </a>


                                            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}"
                                                onsubmit="return confirm('¿Seguro que querés eliminar este movimiento?')">

                                                @csrf
                                                @method('DELETE')


                                                <button type="submit" class="icon-button danger"
                                                    title="Eliminar movimiento">

                                                    <i class="bi bi-trash"></i>

                                                </button>

                                            </form>
                                        @endif



                                        {{-- SIN ACCIONES --}}

                                        @if (
                                            !($canExecute && $transaction->type === 'expense' && !$transaction->executed_at) &&
                                                !($transaction->type === 'expense' && $transaction->executed_at) &&
                                                !$canManage)
                                            <span class="text-muted">
                                                —
                                            </span>
                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="9" class="text-center">
                                    Sin movimientos en esta jornada
                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>



        {{-- =========================================================
        MODALES DE TRANSFERENCIAS EJECUTADAS
    ========================================================== --}}

        @foreach ($transactions as $transaction)
            @if ($transaction->type === 'expense' && $transaction->executed_at)
                @php

                    $currency = $transaction->accountBalance?->currency ?? '---';

                @endphp


                <div class="modal fade transaction-detail-modal" id="transactionDetailModal{{ $transaction->id }}"
                    tabindex="-1" aria-hidden="true">

                    <div class="modal-dialog modal-dialog-centered">

                        <div class="modal-content transaction-detail-content">


                            {{-- HEADER --}}

                            <div class="transaction-detail-header">

                                <div class="transaction-detail-title">

                                    <div class="transaction-detail-icon">

                                        <i class="bi bi-check2-circle"></i>

                                    </div>


                                    <div>

                                        <span class="transaction-detail-label">
                                            TRANSFERENCIA EJECUTADA
                                        </span>

                                        <h3>

                                            {{ $currency }}

                                            {{ number_format($transaction->amount, 2, ',', '.') }}

                                        </h3>

                                    </div>

                                </div>


                                <button type="button" class="transaction-modal-close" data-bs-dismiss="modal"
                                    aria-label="Cerrar">

                                    <i class="bi bi-x-lg"></i>

                                </button>

                            </div>



                            {{-- ESTADO --}}

                            <div class="transaction-detail-status">

                                <i class="bi bi-check-circle-fill"></i>


                                <div>

                                    <strong>
                                        Transferencia realizada
                                    </strong>

                                    <span>

                                        Ejecutada el

                                        {{ $transaction->executed_at->format('d/m/Y H:i') }}

                                    </span>

                                </div>

                            </div>



                            {{-- TRANSFERENCIA --}}

                            <div class="transaction-detail-section">

                                <span class="transaction-section-title">
                                    Transferencia
                                </span>


                                <div class="transaction-bank-route">


                                    {{-- ORIGEN --}}

                                    <div class="transaction-bank">

                                        <span class="transaction-bank-label">
                                            ORIGEN
                                        </span>


                                        <div class="transaction-bank-info">

                                            @if ($transaction->account?->logo)
                                                <img src="{{ Storage::url($transaction->account->logo) }}"
                                                    alt="{{ $transaction->account->name }}">
                                            @else
                                                <div class="transaction-bank-placeholder">

                                                    <i class="bi bi-bank"></i>

                                                </div>
                                            @endif


                                            <div>

                                                <strong>
                                                    {{ $transaction->account?->name ?? 'Cuenta eliminada' }}
                                                </strong>

                                                <span>
                                                    Cuenta de origen · {{ $currency }}
                                                </span>

                                            </div>

                                        </div>

                                    </div>



                                    {{-- FLECHA --}}

                                    <div class="transaction-route-arrow">

                                        <i class="bi bi-arrow-right"></i>

                                    </div>



                                    {{-- DESTINO --}}

                                    <div class="transaction-bank">

                                        <span class="transaction-bank-label">
                                            DESTINO
                                        </span>


                                        <div class="transaction-bank-info">

                                            <div class="transaction-bank-placeholder">

                                                <i class="bi bi-bank"></i>

                                            </div>


                                            <div>

                                                <strong>
                                                    {{ $transaction->destination_bank }}
                                                </strong>

                                                <span>
                                                    Banco destino
                                                </span>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>



                            {{-- DETALLE --}}

                            <div class="transaction-detail-section">

                                <span class="transaction-section-title">
                                    Detalle del movimiento
                                </span>


                                <div class="transaction-detail-grid transaction-detail-grid-compact">


                                    <div class="transaction-detail-item">

                                        <span>
                                            Fecha y hora
                                        </span>

                                        <strong>
                                            {{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y H:i') }}
                                        </strong>

                                    </div>


                                    <div class="transaction-detail-item">

                                        <span>
                                            Moneda
                                        </span>

                                        <strong>
                                            {{ $currency }}
                                        </strong>

                                    </div>


                                    <div class="transaction-detail-item">

                                        <span>
                                            Tipo
                                        </span>

                                        <strong class="red">
                                            Egreso
                                        </strong>

                                    </div>


                                    <div class="transaction-detail-item transaction-detail-concept">

                                        <span>
                                            Concepto
                                        </span>

                                        <strong>
                                            {{ $transaction->description ?? 'Sin descripción' }}
                                        </strong>

                                    </div>

                                </div>

                            </div>



                            {{-- EJECUCIÓN --}}

                            <div class="transaction-detail-section transaction-execution-section">

                                <span class="transaction-section-title">
                                    Ejecución
                                </span>


                                <div class="transaction-execution-info">

                                    <i class="bi bi-check-circle-fill"></i>


                                    <div>

                                        <span>
                                            Fecha y hora de ejecución
                                        </span>

                                        <strong>
                                            {{ $transaction->executed_at->format('d/m/Y H:i') }}
                                        </strong>

                                    </div>

                                </div>

                            </div>



                            {{-- FOOTER --}}

                            <div class="transaction-detail-footer">

                                <button type="button" class="secondary-button" data-bs-dismiss="modal">
                                    Cerrar
                                </button>

                            </div>


                        </div>

                    </div>

                </div>
            @endif
        @endforeach


    </div>
@endsection
