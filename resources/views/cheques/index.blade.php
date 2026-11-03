@extends('layouts.app')

@section('content')
    @php
        /*
    |--------------------------------------------------------------------------
    | ESTADOS DE LIQUIDACIÓN
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

        $money = static fn($amount) => '$ ' . number_format((float) $amount, 2, ',', '.');
    @endphp

    <div class="page-container cheques-page">

        {{-- ENCABEZADO --}}

        <div class="page-header">

            <div>
                <h1>Cheques</h1>

                <p>
                    Gestión de liquidaciones de cheques de terceros.
                </p>
            </div>

            <a href="{{ route('cheques.create') }}" class="primary-action-button text-decoration-none">
                <i class="bi bi-plus-lg"></i>
                Nueva liquidación
            </a>

        </div>

        {{-- MENSAJES --}}

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- OPERACIONES --}}

        <section class="cheques-panel">

            <div class="cheques-section-title">
                Operaciones registradas
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
                            <th class="text-end">Acciones</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($liquidations as $item)
                            @php
                                $status = $item->status;

                                $statusLabel = $statusLabels[$status] ?? ucfirst($status);

                                $statusClass = $statusClasses[$status] ?? '';
                            @endphp

                            <tr>

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

                                    <span class="cheques-status {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>

                                </td>

                                {{-- ACCIONES --}}

                                <td>

                                    <div class="cheques-table-actions">

                                        <a href="{{ route('cheques.show', $item) }}"
                                            class="secondary-button cheques-small-button">
                                            <i class="bi bi-eye"></i>
                                            Ver detalle
                                        </a>

                                        <a href="{{ route('cheques.export', $item) }}"
                                            class="secondary-button cheques-small-button"
                                            title="Descargar liquidación en Excel">
                                            <i class="bi bi-file-earmark-excel text-success"></i>
                                            Excel
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="text-center py-5">
                                    Todavía no hay operaciones registradas.

                                    Creá una liquidación para comenzar.
                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </section>

        {{-- PAGINACIÓN --}}

        <div class="mt-3">
            {{ $liquidations->links() }}
        </div>

    </div>
@endsection
