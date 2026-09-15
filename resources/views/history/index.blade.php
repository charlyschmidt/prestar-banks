@extends('layouts.app')


@section('content')

    <div class="page-container history-page">


        {{-- =========================================================
            HEADER
        ========================================================== --}}

        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

            <div>

                <h1>
                    Historial
                </h1>

                <p>
                    Consultá y filtrá todos los movimientos registrados
                </p>

            </div>

        </div>



        {{-- =========================================================
            FILTROS
        ========================================================== --}}

        <div class="history-filter-card">


            <form method="GET" action="{{ route('history.index') }}" id="history-filter-form">


                {{-- BUSCADOR PRINCIPAL --}}

                <div class="history-search-row">

                    <div class="history-search">

                        <i class="bi bi-search"></i>

                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Buscar descripción, cuenta, usuario o banco destino..." autocomplete="off">

                    </div>


                    <button type="submit" class="primary-action-button">

                        <i class="bi bi-funnel"></i>

                        Aplicar filtros

                    </button>


                    <a href="{{ route('history.index') }}" class="secondary-button">

                        <i class="bi bi-x-lg"></i>

                        Limpiar

                    </a>

                </div>



                {{-- FILTROS --}}

                <div class="history-filters-grid">


                    {{-- FECHA DESDE --}}

                    <div class="form-group">

                        <label>
                            Desde
                        </label>

                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="dark-input">

                    </div>



                    {{-- FECHA HASTA --}}

                    <div class="form-group">

                        <label>
                            Hasta
                        </label>

                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="dark-input">

                    </div>



                    {{-- CUENTA --}}

                    <div class="form-group">

                        <label>
                            Cuenta
                        </label>

                        <select name="account_id" class="dark-input">

                            <option value="">
                                Todas
                            </option>

                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}"
                                    {{ (string) request('account_id') === (string) $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach

                        </select>

                    </div>



                    {{-- USUARIO --}}

                    <div class="form-group">

                        <label>
                            Usuario
                        </label>

                        <select name="user_id" class="dark-input">

                            <option value="">
                                Todos
                            </option>

                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ (string) request('user_id') === (string) $user->id ? 'selected' : '' }}>
                                    {{ $user->name ?? $user->username }}

                                    @if ($user->trashed())
                                        (eliminado)
                                    @endif
                                </option>
                            @endforeach

                        </select>

                    </div>



                    {{-- TIPO --}}

                    <div class="form-group">

                        <label>
                            Tipo
                        </label>

                        <select name="type" class="dark-input">

                            <option value="">
                                Todos
                            </option>

                            <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>
                                Ingreso
                            </option>

                            <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>
                                Egreso
                            </option>

                            <option value="reserve" {{ request('type') === 'reserve' ? 'selected' : '' }}>
                                Reserva
                            </option>

                        </select>

                    </div>



                    {{-- BANCO DESTINO --}}

                    <div class="form-group">

                        <label>
                            Banco destino
                        </label>

                        <select name="destination_bank" class="dark-input">

                            <option value="">
                                Todos
                            </option>

                            @foreach ($destinationBanks as $bank)
                                <option value="{{ $bank }}"
                                    {{ request('destination_bank') === $bank ? 'selected' : '' }}>
                                    {{ $bank }}
                                </option>
                            @endforeach

                        </select>

                    </div>



                    {{-- EJECUCIÓN --}}

                    <div class="form-group">

                        <label>
                            Estado
                        </label>

                        <select name="execution" class="dark-input">

                            <option value="">
                                Todos
                            </option>

                            <option value="executed" {{ request('execution') === 'executed' ? 'selected' : '' }}>
                                Ejecutados
                            </option>

                            <option value="pending" {{ request('execution') === 'pending' ? 'selected' : '' }}>
                                Pendientes
                            </option>

                        </select>

                    </div>



                    {{-- MONTO MÍNIMO --}}

                    <div class="form-group">

                        <label>
                            Monto mínimo
                        </label>

                        <input type="number" step="0.01" min="0" name="amount_min"
                            value="{{ request('amount_min') }}" class="dark-input" placeholder="0,00">

                    </div>



                    {{-- MONTO MÁXIMO --}}

                    <div class="form-group">

                        <label>
                            Monto máximo
                        </label>

                        <input type="number" step="0.01" min="0" name="amount_max"
                            value="{{ request('amount_max') }}" class="dark-input" placeholder="Sin límite">

                    </div>


                </div>


            </form>

        </div>



        {{-- =========================================================
            RESUMEN DEL RESULTADO
        ========================================================== --}}




        <div class="history-summary-grid">


            {{-- RESULTADOS --}}

            <div class="history-summary-card">

                <span>
                    Resultados
                </span>

                <strong>
                    {{ number_format($totalResults, 0, ',', '.') }}
                </strong>

                <small>
                    movimientos encontrados
                </small>

            </div>



            {{-- INGRESOS --}}

            <div class="history-summary-card">

                <span>
                    Ingresos
                </span>

                <strong class="green">
                    ${{ number_format($totalIncome, 2, ',', '.') }}
                </strong>

                <small>
                    total según filtros
                </small>

            </div>



            {{-- EGRESOS --}}

            <div class="history-summary-card">

                <span>
                    Egresos
                </span>

                <strong class="red">
                    ${{ number_format($totalExpense, 2, ',', '.') }}
                </strong>

                <small>
                    sin incluir reservas
                </small>

            </div>



            {{-- RESERVAS --}}

            <div class="history-summary-card">

                <span>
                    Reservas
                </span>

                <strong class="history-reserve-color">
                    ${{ number_format($totalReserve, 2, ',', '.') }}
                </strong>

                <small>
                    total según filtros
                </small>

            </div>


        </div>



        {{-- =========================================================
            CABECERA TABLA
        ========================================================== --}}

        <div class="history-results-header" id="history-results">

            <div>

                <h3>
                    Movimientos
                </h3>

                <span>

                    Mostrando

                    {{ $transactions->firstItem() ?? 0 }}

                    –

                    {{ $transactions->lastItem() ?? 0 }}

                    de

                    {{ number_format($transactions->total(), 0, ',', '.') }}

                </span>

            </div>


            <div class="history-results-actions">

                <div class="history-active-filters">

                    {{-- DESDE --}}
                    @if (request('date_from'))
                        <a href="{{ route('history.index', request()->except(['date_from', 'page'])) }}"
                            class="history-filter-chip" title="Quitar filtro">
                            <span>
                                Desde:
                                <strong>
                                    {{ \Carbon\Carbon::parse(request('date_from'))->format('d/m/Y') }}
                                </strong>
                            </span>

                            <i class="bi bi-x"></i>
                        </a>
                    @endif


                    {{-- HASTA --}}
                    @if (request('date_to'))
                        <a href="{{ route('history.index', request()->except(['date_to', 'page'])) }}"
                            class="history-filter-chip" title="Quitar filtro">
                            <span>
                                Hasta:
                                <strong>
                                    {{ \Carbon\Carbon::parse(request('date_to'))->format('d/m/Y') }}
                                </strong>
                            </span>

                            <i class="bi bi-x"></i>
                        </a>
                    @endif


                    {{-- CUENTA --}}
                    @if (request('account_id'))
                        @php
                            $activeAccount = $accounts->firstWhere('id', request('account_id'));
                        @endphp

                        <a href="{{ route('history.index', request()->except(['account_id', 'page'])) }}"
                            class="history-filter-chip" title="Quitar filtro">
                            <span>
                                Cuenta:
                                <strong>
                                    {{ $activeAccount?->name ?? 'Cuenta' }}
                                </strong>
                            </span>

                            <i class="bi bi-x"></i>
                        </a>
                    @endif


                    {{-- USUARIO --}}
                    @if (request('user_id'))
                        @php
                            $activeUser = $users->firstWhere('id', request('user_id'));
                        @endphp

                        <a href="{{ route('history.index', request()->except(['user_id', 'page'])) }}"
                            class="history-filter-chip" title="Quitar filtro">
                            <span>
                                Usuario:
                                <strong>
                                    {{ $activeUser?->name ?? ($activeUser?->username ?? 'Usuario') }}
                                </strong>
                            </span>

                            <i class="bi bi-x"></i>
                        </a>
                    @endif


                    {{-- TIPO --}}
                    @if (request('type'))
                        @php
                            $typeLabels = [
                                'income' => 'Ingreso',
                                'expense' => 'Egreso',
                                'reserve' => 'Reserva',
                            ];
                        @endphp

                        <a href="{{ route('history.index', request()->except(['type', 'page'])) }}"
                            class="history-filter-chip" title="Quitar filtro">
                            <span>
                                Tipo:
                                <strong>
                                    {{ $typeLabels[request('type')] ?? request('type') }}
                                </strong>
                            </span>

                            <i class="bi bi-x"></i>
                        </a>
                    @endif


                    {{-- BANCO DESTINO --}}
                    @if (request('destination_bank'))
                        <a href="{{ route('history.index', request()->except(['destination_bank', 'page'])) }}"
                            class="history-filter-chip" title="Quitar filtro">
                            <span>
                                Destino:
                                <strong>
                                    {{ request('destination_bank') }}
                                </strong>
                            </span>

                            <i class="bi bi-x"></i>
                        </a>
                    @endif


                    {{-- ESTADO --}}
                    @if (request('execution'))
                        <a href="{{ route('history.index', request()->except(['execution', 'page'])) }}"
                            class="history-filter-chip" title="Quitar filtro">
                            <span>
                                Estado:
                                <strong>
                                    {{ request('execution') === 'executed' ? 'Ejecutado' : 'Pendiente' }}
                                </strong>
                            </span>

                            <i class="bi bi-x"></i>
                        </a>
                    @endif


                    {{-- MONTO MÍNIMO --}}
                    @if (request('amount_min'))
                        <a href="{{ route('history.index', request()->except(['amount_min', 'page'])) }}"
                            class="history-filter-chip" title="Quitar filtro">
                            <span>
                                Desde $
                                <strong>
                                    {{ number_format((float) request('amount_min'), 2, ',', '.') }}
                                </strong>
                            </span>

                            <i class="bi bi-x"></i>
                        </a>
                    @endif


                    {{-- MONTO MÁXIMO --}}
                    @if (request('amount_max'))
                        <a href="{{ route('history.index', request()->except(['amount_max', 'page'])) }}"
                            class="history-filter-chip" title="Quitar filtro">
                            <span>
                                Hasta $
                                <strong>
                                    {{ number_format((float) request('amount_max'), 2, ',', '.') }}
                                </strong>
                            </span>

                            <i class="bi bi-x"></i>
                        </a>
                    @endif


                    {{-- BÚSQUEDA --}}
                    @if (request('search'))
                        <a href="{{ route('history.index', request()->except(['search', 'page'])) }}"
                            class="history-filter-chip" title="Quitar filtro">
                            <span>
                                Búsqueda:
                                <strong>
                                    {{ request('search') }}
                                </strong>
                            </span>

                            <i class="bi bi-x"></i>
                        </a>
                    @endif


                    {{-- LIMPIAR TODOS --}}
                    @if (request()->hasAny([
                            'date_from',
                            'date_to',
                            'account_id',
                            'user_id',
                            'type',
                            'destination_bank',
                            'execution',
                            'search',
                            'amount_min',
                            'amount_max',
                        ]))
                        <a href="{{ route('history.index') }}" class="history-clear-filters">
                            Limpiar todos
                        </a>
                    @endif

                </div>


                {{-- EXPORTAR --}}

                @if ($transactions->total() > 0)
                    <a href="{{ route('history.export', request()->query()) }}"
                        class="history-export-button">
                        <i class="bi bi-file-earmark-arrow-down"></i>
                        Exportar Excel
                    </a>
                @endif

            </div>

        </div>



        {{-- =========================================================
            TABLA
        ========================================================== --}}

        <div class="history-table-card">


            <div class="table-responsive">


                <table class="modern-table history-table">


                    <thead>

                        <tr>

                            <th>
                                Fecha
                            </th>

                            <th>
                                Cuenta
                            </th>

                            <th>
                                Usuario
                            </th>

                            <th>
                                Tipo
                            </th>

                            <th>
                                Descripción
                            </th>

                            <th>
                                Banco destino
                            </th>

                            <th>
                                Estado
                            </th>

                            <th class="text-end">
                                Monto
                            </th>

                        </tr>

                    </thead>



                    <tbody>


                        @forelse ($transactions as $transaction)
                            <tr>


                                {{-- FECHA --}}

                                <td>

                                    <div class="movement-date">

                                        <strong>
                                            {{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y') }}
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



                                {{-- USUARIO --}}

                                <td>

                                    <div class="movement-user">

                                        <i class="bi bi-person-circle"></i>

                                        <span>
                                            {{ $transaction->user?->name ?? 'Sin registro' }}
                                        </span>

                                    </div>

                                </td>



                                {{-- TIPO --}}

                                <td>


                                    @if ($transaction->type === 'income')
                                        <span class="tag income-tag">
                                            <i class="bi bi-arrow-up"></i>
                                            Ingreso
                                        </span>
                                    @elseif ($transaction->type === 'reserve')
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



                                {{-- DESCRIPCIÓN --}}

                                <td>

                                    <div class="history-description">

                                        {{ $transaction->description ?? 'Sin descripción' }}

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
                                    @else
                                        <span class="text-muted">
                                            —
                                        </span>
                                    @endif


                                </td>



                                {{-- ESTADO --}}

                                <td>


                                    @if ($transaction->type === 'expense')
                                        @if ($transaction->executed_at)
                                            <span class="history-status executed">

                                                <i class="bi bi-check-circle-fill"></i>

                                                Ejecutado

                                            </span>
                                        @else
                                            <span class="history-status pending">

                                                <i class="bi bi-clock"></i>

                                                Pendiente

                                            </span>
                                        @endif
                                    @elseif ($transaction->type === 'reserve')
                                        <span class="history-status reserved">

                                            <i class="bi bi-lock"></i>

                                            Reservado

                                        </span>
                                    @else
                                        <span class="text-muted">
                                            —
                                        </span>
                                    @endif


                                </td>



                                {{-- MONTO --}}

                                <td class="text-end">


                                    @if ($transaction->type === 'income')
                                        <span class="amount-income">
                                            +
                                            ${{ number_format($transaction->amount, 2, ',', '.') }}
                                        </span>
                                    @elseif ($transaction->type === 'reserve')
                                        <span class="history-reserve-amount">
                                            -
                                            ${{ number_format($transaction->amount, 2, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="amount-expense">
                                            -
                                            ${{ number_format($transaction->amount, 2, ',', '.') }}
                                        </span>
                                    @endif


                                </td>


                            </tr>


                        @empty


                            <tr>

                                <td colspan="8" class="history-empty">

                                    <i class="bi bi-search"></i>

                                    <strong>
                                        No encontramos movimientos
                                    </strong>

                                    <span>
                                        Probá modificando o limpiando los filtros.
                                    </span>

                                </td>

                            </tr>
                        @endforelse


                    </tbody>


                </table>


            </div>


        </div>



        {{-- =========================================================
            PAGINACIÓN
        ========================================================== --}}

        @if ($transactions->hasPages())
            <div class="history-pagination">

                {{ $transactions->links() }}

            </div>
        @endif


    </div>

@endsection
@if (request('results'))

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {

                const results =
                    document.getElementById(
                        'history-results'
                    );

                if (!results) {
                    return;
                }

                setTimeout(() => {

                    results.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });

                }, 150);

            }
        );
    </script>

@endif