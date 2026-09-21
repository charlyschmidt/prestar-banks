@extends('layouts.app')


@section('content')

    @php

        $summary = $result['summary'];

        $periodFrom = \Carbon\Carbon::parse(
            $result['period']['from']
        )->format('d/m/Y');

        $periodTo = \Carbon\Carbon::parse(
            $result['period']['to']
        )->format('d/m/Y');

        $differences =
            $summary['missing_in_aeria']
            + $summary['only_in_aeria'];

    @endphp


    <div class="page-container account-movements-page movement-control-results-page">


        {{-- ==========================
             HEADER
        ========================== --}}

        <div class="account-movements-header">


            <div class="account-movements-heading">


                <a
                    href="{{ route('accounts.movement-control', $account->id) }}"
                    class="secondary-button back-button"
                >

                    <i class="bi bi-arrow-left"></i>

                    Nuevo control

                </a>


                <div class="account-movements-account">


                    @if ($account->logo)

                        <img
                            src="{{ asset('storage/' . $account->logo) }}"
                            alt="{{ $account->name }}"
                            class="account-movements-logo"
                        >

                    @endif


                    <div class="account-movements-title">

                        <h1>
                            {{ $account->name }}
                        </h1>

                        <p>
                            Resultado del control · {{ $accountBalance->currency }}
                        </p>

                    </div>


                </div>


            </div>


        </div>



        {{-- ==========================
             RESULTADO
        ========================== --}}

        <div class="movement-control-results">


            {{-- ==========================
                 HEADER RESULTADO
            ========================== --}}

            <div class="movement-control-results-header">

                <div>

                    <h2>
                        Resultado de la comparación
                    </h2>

                    <p>
                        Movimientos bancarios comparados con los registros de AERIA.
                    </p>

                </div>


                <div class="movement-control-results-period">

                    <i class="bi bi-calendar3"></i>

                    {{ $periodFrom }}

                    @if ($periodFrom !== $periodTo)
                        — {{ $periodTo }}
                    @endif

                </div>

            </div>



            {{-- ==========================
                 RESUMEN
            ========================== --}}

            <div class="movement-control-result-summary">


                <div class="movement-control-result-card">

                    <span class="movement-control-result-label">
                        Extracto bancario
                    </span>

                    <strong>
                        {{ $summary['bank_total'] }}
                    </strong>

                    <span>
                        movimientos
                    </span>

                </div>


                <div class="movement-control-result-card">

                    <span class="movement-control-result-label">
                        AERIA
                    </span>

                    <strong>
                        {{ $summary['aeria_total'] }}
                    </strong>

                    <span>
                        movimientos
                    </span>

                </div>


                <div class="movement-control-result-card movement-control-result-card-success">

                    <span class="movement-control-result-label">
                        Coincidencias
                    </span>

                    <strong>
                        {{ $summary['matched'] }}
                    </strong>

                    <span>
                        movimientos
                    </span>

                </div>


                <div class="movement-control-result-card {{ $differences > 0 ? 'movement-control-result-card-warning' : 'movement-control-result-card-success' }}">

                    <span class="movement-control-result-label">
                        Diferencias
                    </span>

                    <strong>
                        {{ $differences }}
                    </strong>

                    <span>
                        movimientos
                    </span>

                </div>


            </div>



            {{-- ==========================
                 ESTADO GENERAL
            ========================== --}}

            @if ($differences === 0)

                <div class="movement-control-result-ok">

                    <i class="bi bi-check-circle"></i>

                    <div>

                        <strong>
                            No se encontraron diferencias
                        </strong>

                        <span>
                            Todos los movimientos del período coinciden entre el extracto bancario y AERIA.
                        </span>

                    </div>

                </div>

            @else

                <div class="movement-control-result-alert">

                    <i class="bi bi-exclamation-triangle"></i>

                    <div>

                        <strong>
                            Se encontraron {{ $differences }}
                            {{ $differences === 1 ? 'diferencia' : 'diferencias' }}
                        </strong>

                        <span>
                            Revisá los movimientos detallados a continuación.
                        </span>

                    </div>

                </div>

            @endif



            {{-- ==========================
                 FALTAN EN AERIA
            ========================== --}}

            <section class="movement-control-result-section">


                <div class="movement-control-result-section-header">

                    <div>

                        <h3>
                            Faltan en AERIA
                        </h3>

                        <p>
                            Estos movimientos aparecen en el banco pero no fueron encontrados en AERIA.
                        </p>

                    </div>


                    <span class="movement-control-result-count">
                        {{ $summary['missing_in_aeria'] }}
                    </span>

                </div>


                @if (count($result['missing_in_aeria']) > 0)

                    <div class="movement-control-result-table">

                        <div class="table-responsive">

                            <table class="table">

                                <thead>

                                    <tr>

                                        <th>
                                            Fecha
                                        </th>

                                        <th>
                                            Descripción
                                        </th>

                                        <th>
                                            Tipo
                                        </th>

                                        <th>
                                            Moneda
                                        </th>

                                        <th class="text-end">
                                            Importe
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @foreach ($result['missing_in_aeria'] as $movement)

                                        <tr>

                                            <td>

                                                {{ \Carbon\Carbon::parse($movement['date'])->format('d/m/Y') }}

                                            </td>


                                            <td>

                                                {{ $movement['description'] ?: 'Sin descripción' }}

                                            </td>


                                            <td>

                                                @if ($movement['type'] === 'income')

                                                    <span class="movement-control-result-type income">

                                                        <i class="bi bi-arrow-down-left"></i>

                                                        Ingreso

                                                    </span>

                                                @else

                                                    <span class="movement-control-result-type expense">

                                                        <i class="bi bi-arrow-up-right"></i>

                                                        Egreso

                                                    </span>

                                                @endif

                                            </td>


                                            <td>

                                                {{ $accountBalance->currency }}

                                            </td>


                                            <td class="text-end">

                                                <span class="movement-control-result-amount {{ $movement['type'] }}">

                                                    {{ $movement['type'] === 'income' ? '+' : '-' }}

                                                    {{ number_format(
                                                        $movement['amount'],
                                                        2,
                                                        ',',
                                                        '.'
                                                    ) }}

                                                </span>

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                @else

                    <div class="movement-control-result-empty">

                        <i class="bi bi-check-circle"></i>

                        <span>
                            No faltan movimientos en AERIA.
                        </span>

                    </div>

                @endif


            </section>



            {{-- ==========================
                 SOLO EN AERIA
            ========================== --}}

            <section class="movement-control-result-section">


                <div class="movement-control-result-section-header">

                    <div>

                        <h3>
                            Solo en AERIA
                        </h3>

                        <p>
                            Estos movimientos están registrados en AERIA pero no aparecen en el extracto bancario.
                        </p>

                    </div>


                    <span class="movement-control-result-count">
                        {{ $summary['only_in_aeria'] }}
                    </span>

                </div>


                @if (count($result['only_in_aeria']) > 0)

                    <div class="movement-control-result-table">

                        <div class="table-responsive">

                            <table class="table">

                                <thead>

                                    <tr>

                                        <th>
                                            Fecha
                                        </th>

                                        <th>
                                            Descripción
                                        </th>

                                        <th>
                                            Tipo
                                        </th>

                                        <th>
                                            Moneda
                                        </th>

                                        <th class="text-end">
                                            Importe
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @foreach ($result['only_in_aeria'] as $movement)

                                        <tr>

                                            <td>

                                                {{ \Carbon\Carbon::parse($movement['date'])->format('d/m/Y') }}

                                            </td>


                                            <td>

                                                {{ $movement['description'] ?: 'Sin descripción' }}

                                            </td>


                                            <td>

                                                @if ($movement['type'] === 'income')

                                                    <span class="movement-control-result-type income">

                                                        <i class="bi bi-arrow-down-left"></i>

                                                        Ingreso

                                                    </span>

                                                @else

                                                    <span class="movement-control-result-type expense">

                                                        <i class="bi bi-arrow-up-right"></i>

                                                        Egreso

                                                    </span>

                                                @endif

                                            </td>


                                            <td>

                                                {{ $accountBalance->currency }}

                                            </td>


                                            <td class="text-end">

                                                <span class="movement-control-result-amount {{ $movement['type'] }}">

                                                    {{ $movement['type'] === 'income' ? '+' : '-' }}

                                                    {{ number_format(
                                                        $movement['amount'],
                                                        2,
                                                        ',',
                                                        '.'
                                                    ) }}

                                                </span>

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                @else

                    <div class="movement-control-result-empty">

                        <i class="bi bi-check-circle"></i>

                        <span>
                            No hay movimientos adicionales en AERIA.
                        </span>

                    </div>

                @endif


            </section>



            {{-- ==========================
                 COINCIDENCIAS
            ========================== --}}

            @if ($summary['matched'] > 0)

                <div class="movement-control-result-matched">

                    <i class="bi bi-check2-circle"></i>

                    <div>

                        <strong>
                            {{ $summary['matched'] }}
                            {{ $summary['matched'] === 1 ? 'movimiento coincide' : 'movimientos coinciden' }}
                        </strong>

                        <span>
                            Las coincidencias se verificaron por fecha, tipo e importe.
                        </span>

                    </div>

                </div>

            @endif



            {{-- ==========================
                 ACCIONES
            ========================== --}}

            <div class="movement-control-result-actions">

                <a
                    href="{{ route('accounts.movements', $account->id) }}"
                    class="secondary-button"
                >

                    <i class="bi bi-arrow-left"></i>

                    Volver a movimientos

                </a>


                <a
                    href="{{ route('accounts.movement-control', $account->id) }}"
                    class="primary-action-button"
                >

                    <i class="bi bi-arrow-repeat"></i>

                    Nuevo control

                </a>

            </div>


        </div>


    </div>

@endsection