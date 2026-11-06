@extends('layouts.app')

@section('content')

<div class="page-container clients-page">

    {{-- =====================================================
         HEADER
    ===================================================== --}}

    <div class="page-header">

        <div>
            <h1>
                <i class="bi bi-people me-2"></i>
                Clientes
            </h1>

            <p>
                Administrá el padrón de clientes de tu empresa.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">

            <a href="{{ route('dashboard') }}"
               class="secondary-button">

                <i class="bi bi-arrow-left"></i>
                Volver

            </a>

            <a href="{{ route('clients.create') }}"
               class="primary-action-button">

                <i class="bi bi-person-plus"></i>
                Nuevo cliente

            </a>

        </div>

    </div>


    {{-- =====================================================
         RESUMEN
    ===================================================== --}}

    <div class="row g-3 mb-4">

        <div class="col-12 col-md-6">

            <div class="movements h-100">

                <div class="d-flex align-items-center gap-3">

                    <div class="mini-logo">
                        <i class="bi bi-people"></i>
                    </div>

                    <div>
                        <div class="text-muted small">
                            Total de clientes
                        </div>

                        <h3 class="mb-0">
                            {{ number_format($totalClients, 0, ',', '.') }}
                        </h3>
                    </div>

                </div>

            </div>

        </div>

        <div class="col-12 col-md-6">

            <div class="movements h-100">

                <div class="d-flex align-items-center gap-3">

                    <div class="mini-logo">
                        <i class="bi bi-person-check"></i>
                    </div>

                    <div>
                        <div class="text-muted small">
                            Clientes activos
                        </div>

                        <h3 class="mb-0">
                            {{ number_format($activeClients, 0, ',', '.') }}
                        </h3>
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================
         FILTROS
    ===================================================== --}}

    <div class="movements mb-4">

        <form method="GET"
              action="{{ route('clients.index') }}">

            <div class="row g-3 align-items-end">

                <div class="col-12 col-lg-7">

                    <div class="form-group mb-0">

                        <label for="client-search">
                            Buscar cliente
                        </label>

                        <input
                            type="search"
                            id="client-search"
                            name="search"
                            class="dark-input"
                            value="{{ $search }}"
                            placeholder="Nombre, apellido, razón social, CUIT, DNI, teléfono...">

                    </div>

                </div>

                <div class="col-12 col-md-5 col-lg-3">

                    <div class="form-group mb-0">

                        <label for="client-status">
                            Estado
                        </label>

                        <select
                            id="client-status"
                            name="status"
                            class="dark-input">

                            <option value="active"
                                @selected($status === 'active')>
                                Activos
                            </option>

                            <option value="inactive"
                                @selected($status === 'inactive')>
                                Inactivos
                            </option>

                            <option value="all"
                                @selected($status === 'all')>
                                Todos
                            </option>

                        </select>

                    </div>

                </div>

                <div class="col-12 col-md-7 col-lg-2">

                    <div class="d-flex gap-2">

                        <button type="submit"
                                class="primary-action-button flex-fill">

                            <i class="bi bi-search"></i>
                            Buscar

                        </button>

                        @if ($search !== '' || $status !== 'active')

                            <a href="{{ route('clients.index') }}"
                               class="secondary-button"
                               title="Limpiar filtros"
                               aria-label="Limpiar filtros">

                                <i class="bi bi-x-lg"></i>

                            </a>

                        @endif

                    </div>

                </div>

            </div>

        </form>

    </div>


    {{-- =====================================================
         LISTADO
    ===================================================== --}}

    <div class="movements">

        <div class="d-flex flex-wrap
                    align-items-center
                    justify-content-between
                    gap-3 mb-3">

            <div>

                <h3 class="mb-1">
                    Padrón de clientes
                </h3>

                <span class="text-muted small">

                    {{ number_format($clients->total(), 0, ',', '.') }}

                    {{ $clients->total() === 1
                        ? 'cliente encontrado'
                        : 'clientes encontrados' }}

                </span>

            </div>

        </div>


        <div class="table-responsive">

            <table class="modern-table">

                <thead>

                    <tr>
                        <th>Cliente</th>
                        <th>CUIT / CUIL</th>
                        <th>DNI</th>
                        <th>Contacto</th>
                        <th>Localidad</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse ($clients as $client)

                        <tr>

                            {{-- CLIENTE --}}

                            <td>

                                <div class="d-flex
                                            align-items-center
                                            gap-2">

                                    <div class="mini-logo">

                                        @if ($client->person_type === 'company')

                                            <i class="bi bi-building"></i>

                                        @else

                                            <i class="bi bi-person"></i>

                                        @endif

                                    </div>

                                    <div>

                                        <a href="{{ route('clients.show', $client) }}"
                                           class="fw-semibold"
                                           style="color:var(--company-text)">

                                            {{ $client->display_name }}

                                        </a>

                                        @if ($client->email)

                                            <div class="text-muted small">
                                                {{ $client->email }}
                                            </div>

                                        @endif

                                    </div>

                                </div>

                            </td>


                            {{-- CUIT --}}

                            <td>

                                @if ($client->tax_id)

                                    {{ substr($client->tax_id, 0, 2) }}-{{ substr($client->tax_id, 2, 8) }}-{{ substr($client->tax_id, 10, 1) }}

                                @else

                                    <span class="text-muted">—</span>

                                @endif

                            </td>


                            {{-- DOCUMENTO --}}

                            <td>

                                @if ($client->document_number)

                                    {{ $client->document_number }}

                                @else

                                    <span class="text-muted">—</span>

                                @endif

                            </td>


                            {{-- CONTACTO --}}

                            <td>

                                @if ($client->phone)

                                    <i class="bi bi-telephone me-1"></i>
                                    {{ $client->phone }}

                                @else

                                    <span class="text-muted">—</span>

                                @endif

                            </td>


                            {{-- LOCALIDAD --}}

                            <td>

                                {{ $client->city ?: '—' }}

                            </td>


                            {{-- TIPO --}}

                            <td>

                                <span class="tag"
                                      style="background:var(--company-hover);
                                             color:var(--company-text);
                                             border:1px solid var(--company-border)">

                                    @if ($client->person_type === 'company')

                                        <i class="bi bi-building"></i>
                                        Jurídica

                                    @else

                                        <i class="bi bi-person"></i>
                                        Física

                                    @endif

                                </span>

                            </td>


                            {{-- ESTADO --}}

                            <td>

                                @if ($client->is_active)

                                    <span class="tag income-tag">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Activo
                                    </span>

                                @else

                                    <span class="tag expense-tag">
                                        <i class="bi bi-dash-circle"></i>
                                        Inactivo
                                    </span>

                                @endif

                            </td>


                            {{-- ACCIONES --}}

                            <td>

                                <div class="table-actions">

                                    <a href="{{ route('clients.show', $client) }}"
                                       class="icon-button"
                                       title="Ver cliente"
                                       aria-label="Ver cliente">

                                        <i class="bi bi-eye"></i>

                                    </a>

                                    <a href="{{ route('clients.edit', $client) }}"
                                       class="icon-button"
                                       title="Editar cliente"
                                       aria-label="Editar cliente">

                                        <i class="bi bi-pencil-square"></i>

                                    </a>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8"
                                class="text-center py-5">

                                <div class="mb-3">

                                    <i class="bi bi-people"
                                       style="font-size:36px;
                                              color:var(--company-muted)">
                                    </i>

                                </div>

                                <h5>
                                    @if ($search !== '' || $status !== 'active')
                                        No encontramos clientes
                                    @else
                                        Todavía no hay clientes activos
                                    @endif
                                </h5>

                                <p class="text-muted">

                                    @if ($search !== '' || $status !== 'active')
                                        Probá modificando los filtros de búsqueda.
                                    @else
                                        Comenzá registrando tu primer cliente.
                                    @endif

                                </p>

                                @if ($search === '' && $status === 'active')

                                    <a href="{{ route('clients.create') }}"
                                       class="primary-action-button">

                                        <i class="bi bi-person-plus"></i>
                                        Agregar cliente

                                    </a>

                                @else

                                    <a href="{{ route('clients.index') }}"
                                       class="secondary-button">

                                        <i class="bi bi-arrow-clockwise"></i>
                                        Limpiar filtros

                                    </a>

                                @endif

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =====================================================
             PAGINACIÓN
        ===================================================== --}}

        @if ($clients->hasPages())

            <div class="mt-4">

                {{ $clients->links() }}

            </div>

        @endif

    </div>

</div>

@endsection
