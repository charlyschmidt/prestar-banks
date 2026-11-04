@extends('layouts.app')

@section('content')

    @php
        /*
        |--------------------------------------------------------------------------
        | AERIA FINANCE — ESTADOS DE LIQUIDACIÓN
        |--------------------------------------------------------------------------
        */

        $statusLabels = [
            'draft' => 'Borrador',
            'held' => 'Retenida',
            'confirmed' => 'Confirmada',
            'deposited' => 'Depositada',
            'accredited' => 'Acreditada',
            'rejected' => 'Rechazada',
        ];

        $statusClasses = [
            'draft' => 'cheques-status-draft',
            'held' => 'cheques-status-held',
            'confirmed' => 'cheques-status-confirmed',
            'deposited' => 'cheques-status-deposited',
            'accredited' => 'cheques-status-accredited',
            'rejected' => 'cheques-status-rejected',
        ];

        $money = static fn($amount) =>
            '$ ' . number_format(
                (float) $amount,
                2,
                ',',
                '.'
            );
    @endphp

    <div class="page-container cheques-page cheque-liquidations-page">

        {{-- ENCABEZADO --}}

        <div class="page-header">

            <div>
                <h1>Cheques</h1>

                <p>
                    Gestión de liquidaciones y cartera
                    de cheques de terceros.
                </p>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">

                {{-- CARTERA DE CHEQUES --}}

                <a
                    href="{{ route('cheques.portfolio.index') }}"
                    class="secondary-button text-decoration-none"
                >
                    <i class="bi bi-wallet2"></i>
                    Cartera de cheques
                </a>

                {{-- NUEVA LIQUIDACIÓN --}}

                <a
                    href="{{ route('cheques.create') }}"
                    class="primary-action-button text-decoration-none"
                >
                    <i class="bi bi-plus-lg"></i>
                    Nueva liquidación
                </a>

            </div>

        </div>

        {{-- MENSAJES --}}

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{--
        |--------------------------------------------------------------------------
        | CONTENIDO SINCRONIZABLE POR REVERB
        |--------------------------------------------------------------------------
        |
        | El frontend reemplazará solamente este bloque
        | utilizando el HTML actualizado de Laravel.
        |
        | Se mantienen:
        |
        | - Tabla de liquidaciones.
        | - Estados.
        | - Acciones.
        | - Paginación.
        |
        | No se modifica el encabezado ni otros componentes.
        |
        --}}

        <div data-cheque-liquidations-content>

            {{-- OPERACIONES --}}

            <section class="cheques-panel">

                <div
                    class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3"
                >

                    <div class="cheques-section-title mb-0">
                        Operaciones registradas
                    </div>

                    <span class="cheques-status">
                        {{ $liquidations->total() }}
                        {{ $liquidations->total() === 1
                            ? 'operación'
                            : 'operaciones' }}
                    </span>

                </div>

                <div class="table-responsive">

                    <table class="table cheques-table align-middle mb-0">

                        <thead>
                            <tr>
                                <th>Nº</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Cheques</th>
                                <th>Nominal</th>
                                <th>Neto</th>
                                <th>Estado</th>
                                <th class="text-end">
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($liquidations as $item)

                                @php
                                    $status = $item->status;

                                    $statusLabel =
                                        $statusLabels[$status]
                                        ?? ucfirst($status);

                                    $statusClass =
                                        $statusClasses[$status]
                                        ?? '';
                                @endphp

                                <tr
                                    data-liquidation-id="{{ $item->id }}"
                                    data-liquidation-status="{{ $status }}"
                                >

                                    {{-- ID --}}

                                    <td>
                                        <strong>
                                            #{{ $item->id }}
                                        </strong>
                                    </td>

                                    {{-- CLIENTE --}}

                                    <td>
                                        {{ $item->client_name }}
                                    </td>

                                    {{-- FECHA --}}

                                    <td>
                                        {{ $item->operation_date->format('d/m/Y') }}
                                    </td>

                                    {{-- CANTIDAD DE CHEQUES --}}

                                    <td>
                                        <span class="cheques-count">
                                            <i class="bi bi-file-earmark-text"></i>

                                            {{ $item->cheques->count() }}
                                        </span>
                                    </td>

                                    {{-- NOMINAL --}}

                                    <td>
                                        {{ $money($item->nominal_amount) }}
                                    </td>

                                    {{-- NETO --}}

                                    <td class="cheques-amount">
                                        <strong>
                                            {{ $money($item->net_amount) }}
                                        </strong>
                                    </td>

                                    {{-- ESTADO --}}

                                    <td>

                                        <span
                                            class="cheques-status {{ $statusClass }}"
                                        >
                                            {{ $statusLabel }}
                                        </span>

                                    </td>

                                    {{-- ACCIONES --}}

                                    <td>

                                        <div class="cheques-table-actions">

                                            <a
                                                href="{{ route('cheques.show', $item) }}"
                                                class="secondary-button cheques-small-button"
                                            >
                                                <i class="bi bi-eye"></i>
                                                Ver detalle
                                            </a>

                                            <a
                                                href="{{ route('cheques.export', $item) }}"
                                                class="secondary-button cheques-small-button"
                                                title="Descargar liquidación en Excel"
                                            >
                                                <i class="bi bi-file-earmark-excel text-success"></i>
                                                Excel
                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td
                                        colspan="8"
                                        class="text-center py-5"
                                    >
                                        <i class="bi bi-inbox fs-3 d-block mb-3"></i>

                                        <strong>
                                            Todavía no hay operaciones registradas.
                                        </strong>

                                        <p class="cheques-note mt-2 mb-0">
                                            Creá una liquidación para comenzar.
                                        </p>
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </section>

            {{-- PAGINACIÓN --}}

            @if ($liquidations->hasPages())

                <div class="mt-3">
                    {{ $liquidations->links() }}
                </div>

            @endif

        </div>

    </div>

@endsection
