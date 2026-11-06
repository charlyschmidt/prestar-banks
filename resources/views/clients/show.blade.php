@extends('layouts.app')

@section('content')
    @php
        $isCompany = $client->person_type === 'company';

        $formattedTaxId =
            $client->tax_id && strlen($client->tax_id) === 11
                ? substr($client->tax_id, 0, 2) .
                    '-' .
                    substr($client->tax_id, 2, 8) .
                    '-' .
                    substr($client->tax_id, 10, 1)
                : ($client->tax_id ?:
                '—');

        $fullAddress = implode(
            ', ',
            array_filter(
                [$client->address, $client->city, $client->province, $client->postal_code],
                fn($value) => filled($value),
            ),
        );
    @endphp

    <div class="page-container clients-page">

        {{-- =====================================================
             HEADER
        ===================================================== --}}

        <div class="page-header">

            <div>

                <h1>
                    <i class="bi {{ $isCompany ? 'bi-building' : 'bi-person-vcard' }} me-2"></i>
                    {{ $client->display_name }}
                </h1>

                <p>
                    Ficha del cliente ·
                    {{ $isCompany ? 'Persona jurídica' : 'Persona física' }}
                </p>

            </div>

            <div class="d-flex align-items-center flex-wrap gap-2">

                <a href="{{ route('clients.index') }}" class="secondary-button">

                    <i class="bi bi-arrow-left"></i>
                    Volver al listado

                </a>

                <a href="{{ route('clients.edit', $client) }}" class="primary-action-button">

                    <i class="bi bi-pencil-square"></i>
                    Editar cliente

                </a>

            </div>

        </div>


        {{-- =====================================================
             ESTADO Y DATOS PRINCIPALES
        ===================================================== --}}

        <div class="movements mb-4">

            <div
                class="d-flex align-items-center
                        justify-content-between
                        flex-wrap gap-3">

                <div class="d-flex align-items-center gap-3">

                    <div class="mini-logo">

                        <i class="bi {{ $isCompany ? 'bi-building' : 'bi-person' }}"></i>

                    </div>

                    <div>

                        <div class="fw-semibold">
                            {{ $client->display_name }}
                        </div>

                        <div class="text-muted small">
                            Cliente #{{ $client->id }}
                        </div>

                    </div>

                </div>

                <div class="d-flex align-items-center flex-wrap gap-2">

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

                    <form method="POST" action="{{ route('clients.toggle-status', $client) }}">

                        @csrf
                        @method('PATCH')

                        <button type="submit" class="secondary-button">

                            @if ($client->is_active)
                                <i class="bi bi-person-dash"></i>
                                Desactivar
                            @else
                                <i class="bi bi-person-check"></i>
                                Activar
                            @endif

                        </button>

                    </form>

                </div>

            </div>

        </div>


        {{-- =====================================================
             IDENTIFICACIÓN
        ===================================================== --}}

        <div class="movements mb-4">

            <h4 class="mb-3">

                <i class="bi bi-person-vcard me-2"></i>
                Identificación

            </h4>

            <div class="row g-3">

                <div class="col-12 col-md-6">

                    <div class="account-balance-card h-100">

                        <span>Tipo de persona</span>

                        <strong class="fs-6">
                            {{ $isCompany ? 'Persona jurídica' : 'Persona física' }}
                        </strong>

                    </div>

                </div>

                @if ($isCompany)
                    <div class="col-12 col-md-6">

                        <div class="account-balance-card h-100">

                            <span>Razón social</span>

                            <strong class="fs-6">
                                {{ $client->business_name ?: '—' }}
                            </strong>

                        </div>

                    </div>
                @else
                    <div class="col-12 col-md-6">

                        <div class="account-balance-card h-100">

                            <span>Nombre</span>

                            <strong class="fs-6">
                                {{ $client->first_name ?: '—' }}
                            </strong>

                        </div>

                    </div>

                    <div class="col-12 col-md-6">

                        <div class="account-balance-card h-100">

                            <span>Apellido</span>

                            <strong class="fs-6">
                                {{ $client->last_name ?: '—' }}
                            </strong>

                        </div>

                    </div>
                @endif

                <div class="col-12 col-md-6">

                    <div class="account-balance-card h-100">

                        <span>CUIT / CUIL</span>

                        <strong class="fs-6">
                            {{ $formattedTaxId }}
                        </strong>

                    </div>

                </div>

                <div class="col-12 col-md-6">

                    <div class="account-balance-card h-100">

                        <span>DNI / Documento</span>

                        <strong class="fs-6">
                            {{ $client->document_number ?: '—' }}
                        </strong>

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
             CONTACTO
        ===================================================== --}}

        <div class="movements mb-4">

            <h4 class="mb-3">

                <i class="bi bi-telephone me-2"></i>
                Datos de contacto

            </h4>

            <div class="row g-3">

                <div class="col-12 col-md-6">

                    <div class="account-balance-card h-100">

                        <span>Correo electrónico</span>

                        <strong class="fs-6">

                            @if ($client->email)
                                <a href="mailto:{{ $client->email }}" style="color:var(--company-text)">

                                    {{ $client->email }}

                                </a>
                            @else
                                —
                            @endif

                        </strong>

                    </div>

                </div>

                <div class="col-12 col-md-6">

                    <div class="account-balance-card h-100">

                        <span>Teléfono</span>

                        <strong class="fs-6">

                            @if ($client->phone)
                                <a href="tel:{{ preg_replace('/[^\d+]/', '', $client->phone) }}"
                                    style="color:var(--company-text)">

                                    {{ $client->phone }}

                                </a>
                            @else
                                —
                            @endif

                        </strong>

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
             DOMICILIO
        ===================================================== --}}

        <div class="movements mb-4">

            <h4 class="mb-3">

                <i class="bi bi-geo-alt me-2"></i>
                Domicilio

            </h4>

            <div class="row g-3">

                <div class="col-12 col-md-6">

                    <div class="account-balance-card h-100">

                        <span>Dirección</span>

                        <strong class="fs-6">
                            {{ $client->address ?: '—' }}
                        </strong>

                    </div>

                </div>

                <div class="col-12 col-md-6">

                    <div class="account-balance-card h-100">

                        <span>Localidad</span>

                        <strong class="fs-6">
                            {{ $client->city ?: '—' }}
                        </strong>

                    </div>

                </div>

                <div class="col-12 col-md-6">

                    <div class="account-balance-card h-100">

                        <span>Provincia</span>

                        <strong class="fs-6">
                            {{ $client->province ?: '—' }}
                        </strong>

                    </div>

                </div>

                <div class="col-12 col-md-6">

                    <div class="account-balance-card h-100">

                        <span>Código postal</span>

                        <strong class="fs-6">
                            {{ $client->postal_code ?: '—' }}
                        </strong>

                    </div>

                </div>

            </div>

            @if ($fullAddress !== '')
                <div class="mt-3 text-muted small">

                    <i class="bi bi-pin-map me-1"></i>

                    {{ $fullAddress }}

                </div>
            @endif

        </div>


        {{-- =====================================================
             OBSERVACIONES
        ===================================================== --}}

        <div class="movements mb-4">

            <h4 class="mb-3">

                <i class="bi bi-card-text me-2"></i>
                Observaciones

            </h4>

            @if ($client->notes)
                <div class="account-balance-card">

                    <div
                        style="white-space:pre-wrap;
                                overflow-wrap:anywhere;
                                font-size:13px;
                                color:var(--company-text)">
                        {{ $client->notes }}</div>

                </div>
            @else
                <p class="text-muted mb-0">
                    No hay observaciones registradas para este cliente.
                </p>
            @endif

        </div>



        {{-- =====================================================
     HISTORIAL DE MOVIMIENTOS
===================================================== --}}

        <div class="movements mb-4">

            <div
                class="d-flex align-items-center
                justify-content-between
                flex-wrap gap-3 mb-3">

                <div>

                    <h4 class="mb-1">

                        <i class="bi bi-clock-history me-2"></i>
                        Historial de movimientos

                    </h4>

                    <p class="text-muted small mb-0">
                        Operaciones financieras asociadas al cliente,
                        de todas las jornadas.
                    </p>

                </div>

                <span class="tag"
                    style="background:var(--company-hover);
                     color:var(--company-text);
                     border:1px solid var(--company-border)">

                    <i class="bi bi-arrow-left-right me-1"></i>

                    {{ $movements->total() }}

                    {{ $movements->total() === 1 ? 'movimiento' : 'movimientos' }}

                </span>

            </div>

            <div class="table-responsive">

                <table class="modern-table">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Fecha</th>

                            <th>Cuenta</th>

                            <th>Concepto</th>

                            <th>Moneda</th>

                            <th>Tipo</th>

                            <th class="text-end">
                                Importe
                            </th>

                            <th>Estado</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($movements as $movement)
                            @php

                                $currency = $movement->accountBalance?->currency ?? '---';

                                $isIncome = in_array($movement->type, ['income', 'transfer_in'], true);

                                $isReserve = $movement->type === 'reserve';

                                $typeLabel = match ($movement->type) {
                                    'income' => 'Ingreso',
                                    'transfer_in' => 'Transferencia recibida',
                                    'reserve' => 'Reserva',
                                    'transfer_out' => 'Transferencia saliente',
                                    default => 'Egreso',
                                };

                            @endphp

                            <tr data-transaction-id="{{ $movement->id }}">

                                {{-- NÚMERO --}}

                                <td>
                                    {{ $movements->firstItem() + $loop->index }}
                                </td>

                                {{-- FECHA --}}

                                <td>

                                    <div class="movement-date">

                                        <strong>
                                            {{ \Carbon\Carbon::parse($movement->date)->format('d/m/Y') }}
                                        </strong>

                                        <small>
                                            {{ \Carbon\Carbon::parse($movement->date)->format('H:i') }}
                                        </small>

                                    </div>

                                </td>

                                {{-- CUENTA --}}

                                <td>

                                    <div class="movement-bank">

                                        @if ($movement->account)
                                            <span>
                                                {{ $movement->account->name }}
                                            </span>
                                        @else
                                            <span class="text-muted">
                                                Cuenta no disponible
                                            </span>
                                        @endif

                                    </div>

                                </td>

                                {{-- CONCEPTO --}}

                                <td>

                                    <strong class="movement-description">
                                        {{ $movement->description ?: 'Sin descripción' }}
                                    </strong>

                                    @if ($movement->type === 'expense' && $movement->destination_bank)
                                        <div class="text-muted small mt-1">

                                            <i class="bi bi-bank me-1"></i>

                                            {{ $movement->destination_bank }}

                                        </div>
                                    @endif

                                </td>

                                {{-- MONEDA --}}

                                <td>

                                    <span class="movement-currency">
                                        {{ $currency }}
                                    </span>

                                </td>

                                {{-- TIPO --}}

                                <td>

                                    @if ($isIncome)
                                        <span class="tag income-tag">

                                            <i class="bi bi-arrow-up"></i>

                                            {{ $typeLabel }}

                                        </span>
                                    @elseif ($isReserve)
                                        <span class="tag reserve-tag">

                                            <i class="bi bi-lock"></i>

                                            {{ $typeLabel }}

                                        </span>
                                    @else
                                        <span class="tag expense-tag">

                                            <i class="bi bi-arrow-down"></i>

                                            {{ $typeLabel }}

                                        </span>
                                    @endif

                                </td>

                                {{-- IMPORTE --}}

                                <td class="amount text-end">

                                    @if ($isIncome)
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

                                    @if ((float) ($movement->transfer_tax_amount ?? 0) > 0)
                                        <div class="text-muted tax-mov">

                                            imp. débito -

                                            {{ number_format($movement->transfer_tax_amount, 2, ',', '.') }}

                                        </div>
                                    @endif

                                </td>

                                {{-- ESTADO --}}

                                <td>

                                    @if ($movement->executed_at)
                                        <span class="tag income-tag">

                                            <i class="bi bi-check-circle-fill"></i>

                                            Ejecutada

                                        </span>
                                    @elseif ($movement->type === 'expense')
                                        <span class="tag reserve-tag">

                                            <i class="bi bi-clock"></i>

                                            Pendiente

                                        </span>
                                    @else
                                        <span class="text-muted">
                                            Registrado
                                        </span>
                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="text-center">

                                    <div class="py-4">

                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>

                                        <strong>
                                            Sin movimientos asociados
                                        </strong>

                                        <p class="text-muted small mt-2 mb-0">

                                            Cuando registres movimientos
                                            vinculados a este cliente,
                                            aparecerán en este historial.

                                        </p>

                                    </div>

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- PAGINACIÓN --}}

            @if ($movements->hasPages())
                <div class="mt-4">

                    {{ $movements->links() }}

                </div>
            @endif

        </div>



        {{-- =====================================================
             DATOS DEL REGISTRO
        ===================================================== --}}

        <div class="movements">

            <h4 class="mb-3">

                <i class="bi bi-info-circle me-2"></i>
                Información del registro

            </h4>

            <div class="row g-3">

                <div class="col-12 col-md-6">

                    <div class="account-balance-card h-100">

                        <span>Fecha de alta</span>

                        <strong class="fs-6">
                            {{ $client->created_at?->format('d/m/Y H:i') ?? '—' }}
                        </strong>

                    </div>

                </div>

                <div class="col-12 col-md-6">

                    <div class="account-balance-card h-100">

                        <span>Última actualización</span>

                        <strong class="fs-6">
                            {{ $client->updated_at?->format('d/m/Y H:i') ?? '—' }}
                        </strong>

                    </div>

                </div>

            </div>

        </div>

    </div>
@endsection
