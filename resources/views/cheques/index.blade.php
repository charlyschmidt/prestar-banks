
@extends('layouts.app')

@section('content')
<div class="page-container cheques-page">

    <div class="page-header">
        <div>
            <h1>Cheques</h1>
            <p>Gestión de liquidaciones de cheques de terceros.</p>
        </div>

        <a href="{{ route('cheques.create') }}"
           class="primary-action-button text-decoration-none">
            <i class="bi bi-plus-lg"></i>
            Nueva liquidación
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

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
                        <th>Nominal</th>
                        <th>Neto</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($liquidations as $item)
                        <tr>
                            <td>#{{ $item->id }}</td>

                            <td>
                                {{ $item->client_name }}
                            </td>

                            <td>
                                {{ $item->operation_date->format('d/m/Y') }}
                            </td>

                            <td>
                                $ {{ number_format((float) $item->nominal_amount, 2, ',', '.') }}
                            </td>

                            <td class="cheques-amount">
                                $ {{ number_format((float) $item->net_amount, 2, ',', '.') }}
                            </td>

                            <td>
                                <span class="cheques-status {{ $item->status === 'held' ? 'cheques-status-held' : '' }}">
                                    {{ $item->status === 'held' ? 'Retenida' : 'Borrador' }}
                                </span>
                            </td>

                            <td>
                                <div class="cheques-table-actions">
                                    <a href="{{ route('cheques.show', $item) }}"
                                       class="secondary-button cheques-small-button">
                                        <i class="bi bi-eye"></i>
                                        Ver detalle
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                Todavía no hay operaciones registradas.
                                Creá una liquidación para comenzar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </section>

    <div class="mt-3">
        {{ $liquidations->links() }}
    </div>

</div>
@endsection
