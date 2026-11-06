@extends('layouts.app')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | VALORES INICIALES
    |--------------------------------------------------------------------------
    */

    $form = $form ?? [];
    $preview = $preview ?? null;
    $editing = isset($liquidation) && $liquidation !== null;
    $saveRoute = $editing
        ? route('cheques.update', $liquidation)
        : route('cheques.store');

    $v = static fn(string $key, $fallback = '') =>
        old($key, $form[$key] ?? $fallback);

    $money = static fn($number) =>
        '$ ' . number_format(
            (float) $number,
            2,
            ',',
            '.'
        );

    /*
    |--------------------------------------------------------------------------
    | CHEQUES INICIALES
    |--------------------------------------------------------------------------
    |
    | Compatible con:
    |
    | - Formulario anterior de un cheque.
    | - Nuevo formulario de múltiples cheques.
    | - Recuperación de datos tras errores.
    |
    */

    $initialCheques = old(
        'cheques',
        $form['cheques'] ?? null
    );

    if (
        !is_array($initialCheques) ||
        count($initialCheques) === 0
    ) {
        $initialCheques = [
            [
                'nominal_amount' => $v('nominal_amount'),
                'due_date' => $v('due_date'),
                'format' => $v('format', 'paper'),
                'bank' => $v('bank'),
                'issuer' => $v('issuer'),
                'number' => $v('number'),
            ],
        ];
    }

    $initialCheques = array_values($initialCheques);

    /*
    |--------------------------------------------------------------------------
    | PARÁMETROS GENERALES
    |--------------------------------------------------------------------------
    */

    $parameters = [
        'annual_rate' => [
            'label' => 'Tasa anual (%)',
            'default' => 62,
        ],

        'clearing_days' => [
            'label' => 'Días adicionales de clearing',
            'default' => 2,
        ],

        'cheque_tax_rate' => [
            'label' => 'Impuesto al cheque (%)',
            'default' => 1.2,
        ],

        'gross_income_rate' => [
            'label' => 'Ingresos Brutos (%)',
            'default' => 0.6,
        ],

        'vat_rate' => [
            'label' => 'IVA (%)',
            'default' => 21,
        ],
    ];
@endphp

<div class="page-container cheques-page">

    {{-- ENCABEZADO --}}

    <div class="page-header">

        <div>
            <h1>{{ $editing ? 'Editar liquidación #' . $liquidation->id : 'Nueva liquidación' }}</h1>

            <p>
                Cheques de terceros ·
                Cálculo ACT/365 ·
                ARS
            </p>
        </div>

        <a
            href="{{ $editing ? route('cheques.show', $liquidation) : route('cheques.index') }}"
            class="cheques-link"
        >
            <i class="bi bi-arrow-left"></i>
            {{ $editing ? 'Volver a la liquidación' : 'Volver al listado' }}
        </a>

    </div>

    {{-- ERRORES DE VALIDACIÓN --}}

    @if ($errors->any())

        <div class="alert alert-danger">

            <strong>
                Revisá los datos:
            </strong>

            <ul class="mb-0">

                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>

        </div>

    @endif

    {{-- RESULTADO DE LIQUIDACIÓN --}}

    @if ($preview)

        <section
            class="cheques-panel cheques-result-panel mb-4"
            id="resultado-liquidacion"
        >

            <div class="cheques-result-header">

                <div>

                    <div class="cheques-section-title mb-1">
                        Resultado de liquidación
                    </div>

                    <p class="cheques-note mb-0">

                        {{ $preview['cheque_count'] ?? 1 }}
                        cheque(s) calculados.

                        El resultado fue validado por el servidor.

                    </p>

                </div>

                <span class="cheques-status">
                    Calculada
                </span>

            </div>

            {{-- NETO CONSOLIDADO --}}

            <div class="cheques-net cheques-net-featured">

                <div>

                    <span>
                        Neto total a entregar al cliente
                    </span>

                    <strong class="cheques-net-amount">
                        {{ $money($preview['net_amount']) }}
                    </strong>

                </div>

            </div>

            {{-- RESUMEN FINANCIERO --}}

            <div class="cheques-summary-grid mt-3">

                <div>

                    <span>
                        Nominal total
                    </span>

                    <strong>
                        {{ $money($preview['nominal_amount']) }}
                    </strong>

                </div>

                <div>

                    <span>
                        Intereses
                    </span>

                    <strong class="cheques-amount-income">
                        {{ $money($preview['interest_amount']) }}
                    </strong>

                </div>

                <div>

                    <span>
                        Impuesto al cheque
                    </span>

                    <strong class="cheques-amount-expense">
                        {{ $money($preview['cheque_tax_amount']) }}
                    </strong>

                </div>

                <div>

                    <span>
                        Ingresos Brutos
                    </span>

                    <strong class="cheques-amount-expense">
                        {{ $money($preview['gross_income_amount']) }}
                    </strong>

                </div>

                <div>

                    <span>
                        IVA
                    </span>

                    <strong class="cheques-amount-expense">
                        {{ $money($preview['vat_amount']) }}
                    </strong>

                </div>

                <div class="cheques-summary-total">

                    <span>
                        Descuento total
                    </span>

                    <strong class="cheques-amount-expense">
                        {{ $money($preview['total_discount']) }}
                    </strong>

                </div>

            </div>

            {{-- DETALLE POR CHEQUE --}}

            @if (!empty($preview['items']))

                <div class="cheques-section-title mt-4 mb-3">
                    Detalle por cheque
                </div>

                <div class="table-responsive">

                    <table class="table cheques-detail-table">

                        <thead>

                            <tr>
                                <th>Cheque</th>
                                <th>Vencimiento</th>
                                <th class="text-end">Días</th>
                                <th class="text-end">Tasa</th>
                                <th class="text-end">Nominal</th>
                                <th class="text-end">Descuento</th>
                                <th class="text-end">Neto</th>
                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($preview['items'] as $index => $item)

                                <tr>

                                    <td>

                                        <strong>
                                            #{{ $index + 1 }}
                                        </strong>

                                        @if (!empty($item['number']))

                                            <div class="cheques-note">
                                                {{ $item['number'] }}
                                            </div>

                                        @endif

                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($item['due_date'])->format('d/m/Y') }}
                                    </td>

                                    <td class="text-end">
                                        {{ $item['term_days'] }}
                                    </td>

                                    <td class="text-end">

                                        {{ number_format(
                                            (float) $item['annual_rate'],
                                            2,
                                            ',',
                                            '.'
                                        ) }}%

                                    </td>

                                    <td class="text-end">
                                        {{ $money($item['nominal_amount']) }}
                                    </td>

                                    <td class="text-end cheques-amount-expense">
                                        {{ $money($item['total_discount']) }}
                                    </td>

                                    <td class="text-end">

                                        <strong>
                                            {{ $money($item['net_amount']) }}
                                        </strong>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @endif

            {{-- ACCIONES DEL RESULTADO --}}

            <div class="cheques-actions mt-4">

                <button
                    type="submit"
                    form="cheques-form"
                    formaction="{{ $saveRoute }}"
                    @if ($editing) name="_method" value="PUT" @endif
                    class="primary-action-button"
                >

                    <i class="bi bi-check-lg"></i>
                    {{ $editing ? 'Guardar cambios' : 'Guardar operación' }}

                </button>

                <a
                    href="#datos-liquidacion"
                    class="secondary-button"
                >

                    <i class="bi bi-pencil"></i>
                    Modificar datos

                </a>

            </div>

        </section>

    @endif

    {{-- FORMULARIO PRINCIPAL --}}

    <form
        id="cheques-form"
        method="POST"
        action="{{ route('cheques.preview') }}"
        class="cheques-panel"
    >

        @csrf

        {{-- DATOS GENERALES --}}

        <div
            id="datos-liquidacion"
            class="cheques-section-title"
        >
            Datos de operación
        </div>

        <div class="cheques-grid">

            {{-- CLIENTE --}}

            <label>

                Cliente

                <input
                    type="text"
                    class="form-control"
                    name="client_name"
                    maxlength="150"
                    required
                    value="{{ $v('client_name') }}"
                    placeholder="Nombre del cliente"
                >

            </label>

            {{-- FECHA --}}

            <label>

                Fecha de operación

                <input
                    type="date"
                    class="form-control"
                    id="cheques-operation-date"
                    name="operation_date"
                    required
                    value="{{ $v('operation_date') }}"
                >

            </label>

        </div>

        {{-- PARÁMETROS GENERALES --}}

        <div class="cheques-section-title mt-4">
            Parámetros generales
        </div>

        <p class="cheques-note mb-3">

            Estos parámetros se aplican a todos los cheques,
            salvo que configures condiciones particulares
            para alguno de ellos.

        </p>

        <div class="cheques-grid">

            @foreach ($parameters as $key => $parameter)

                <label>

                    {{ $parameter['label'] }}

                    @if ($key === 'clearing_days')

                        <input
                            type="number"
                            class="form-control"
                            name="{{ $key }}"
                            id="general-{{ $key }}"
                            min="0"
                            max="30"
                            step="1"
                            required
                            value="{{ $v($key, $parameter['default']) }}"
                        >

                    @else

                        <input
                            type="text"
                            class="form-control percent-input"
                            name="{{ $key }}"
                            id="general-{{ $key }}"
                            inputmode="decimal"
                            autocomplete="off"
                            required
                            value="{{ $v($key, $parameter['default']) }}"
                        >

                    @endif

                </label>

            @endforeach

        </div>

        {{-- ENCABEZADO DE CHEQUES --}}

        <div
            class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-4 mb-3"
        >

            <div>

                <div class="cheques-section-title mb-1">
                    Cheques de la operación
                </div>

                <p class="cheques-note mb-0">
                    Agregá todos los cheques que entrega el cliente.
                </p>

            </div>

            <span
                class="cheques-status"
                id="cheques-counter"
            >
                {{ count($initialCheques) }} cheque(s)
            </span>

        </div>

        {{-- CONTENEDOR DE CHEQUES --}}

        <div id="cheques-container">

            @foreach ($initialCheques as $index => $cheque)

                @php

                    $cv = static fn(string $key, $fallback = '') =>
                        $cheque[$key] ?? $fallback;

                    $custom = collect([
                        'annual_rate',
                        'clearing_days',
                        'cheque_tax_rate',
                        'gross_income_rate',
                        'vat_rate',
                    ])->contains(
                        fn($key) => array_key_exists(
                            $key,
                            $cheque
                        )
                    );

                @endphp

                <div
                    class="cheque-item"
                    data-cheque-index="{{ $index }}"
                >

                    {{-- ENCABEZADO DEL CHEQUE --}}

                    <div class="cheque-item-header">

                        <strong class="cheque-item-title">
                            Cheque #{{ $index + 1 }}
                        </strong>

                        <button
                            type="button"
                            class="cheque-remove-button"
                            title="Eliminar cheque"
                        >

                            <i class="bi bi-trash"></i>
                            Quitar

                        </button>

                    </div>

                    {{-- Conservar ID al editar; los cheques nuevos no lo tienen. --}}
                    @if ($editing && $cv('id'))
                        <input
                            type="hidden"
                            name="cheques[{{ $index }}][id]"
                            value="{{ $cv('id') }}"
                        >
                    @endif

                    {{-- CAMPOS DEL CHEQUE --}}

                    <div class="cheques-grid">

                        {{-- IMPORTE --}}

                        <label>

                            Importe nominal (ARS)

                            <input
                                type="text"
                                class="form-control money-input cheque-nominal"
                                name="cheques[{{ $index }}][nominal_amount]"
                                inputmode="decimal"
                                autocomplete="off"
                                required
                                value="{{ $cv('nominal_amount') }}"
                                placeholder="100.000,00"
                            >

                        </label>

                        {{-- VENCIMIENTO --}}

                        <label>

                            Fecha de vencimiento

                            <input
                                type="date"
                                class="form-control cheque-due-date"
                                name="cheques[{{ $index }}][due_date]"
                                required
                                value="{{ $cv('due_date') }}"
                            >

                        </label>

                        {{-- FORMATO --}}

                        <label>

                            Formato

                            <select
                                name="cheques[{{ $index }}][format]"
                                class="form-select"
                            >

                                <option
                                    value="paper"
                                    @selected($cv('format', 'paper') === 'paper')
                                >
                                    Cheque físico
                                </option>

                                <option
                                    value="echeq"
                                    @selected($cv('format') === 'echeq')
                                >
                                    Echeq
                                </option>

                            </select>

                        </label>

                        {{-- BANCO --}}

                        <label>

                            Banco
                            <span class="cheques-optional">
                                (opcional)
                            </span>

                            <input
                                type="text"
                                class="form-control"
                                name="cheques[{{ $index }}][bank]"
                                maxlength="120"
                                value="{{ $cv('bank') }}"
                            >

                        </label>

                        {{-- EMISOR --}}

                        <label>

                            Emisor
                            <span class="cheques-optional">
                                (opcional)
                            </span>

                            <input
                                type="text"
                                class="form-control"
                                name="cheques[{{ $index }}][issuer]"
                                maxlength="150"
                                value="{{ $cv('issuer') }}"
                            >

                        </label>

                        {{-- NÚMERO --}}

                        <label>

                            Número de cheque
                            <span class="cheques-optional">
                                (opcional)
                            </span>

                            <input
                                type="text"
                                class="form-control"
                                name="cheques[{{ $index }}][number]"
                                maxlength="80"
                                value="{{ $cv('number') }}"
                            >

                        </label>

                    </div>

                    {{-- CONDICIONES PARTICULARES --}}

                    <div class="mt-3">

                        <label class="cheque-custom-label">

                            <input
                                type="checkbox"
                                class="cheque-custom-toggle"
                                @checked($custom)
                            >

                            Condiciones particulares para este cheque

                        </label>

                    </div>

                    <div
                        class="cheque-custom-fields mt-3"
                        style="{{ $custom ? '' : 'display:none;' }}"
                    >

                        <div class="cheques-grid">

                            @foreach ($parameters as $key => $parameter)

                                <label>

                                    {{ $parameter['label'] }}

                                    <input
                                        type="{{ $key === 'clearing_days' ? 'number' : 'text' }}"
                                        class="form-control cheque-custom-input {{ $key === 'clearing_days' ? '' : 'percent-input' }}"
                                        data-field="{{ $key }}"
                                        name="cheques[{{ $index }}][{{ $key }}]"
                                        value="{{ $cv($key, $v($key, $parameter['default'])) }}"

                                        @if ($key === 'clearing_days')

                                            min="0"
                                            max="30"
                                            step="1"

                                        @else

                                            inputmode="decimal"
                                            autocomplete="off"

                                        @endif

                                        @disabled(!$custom)
                                    >

                                </label>

                            @endforeach

                        </div>

                    </div>

                    {{-- RESUMEN INDIVIDUAL --}}

                    <div class="cheque-item-footer">

                        <div>

                            <span>
                                Plazo computado
                            </span>

                            <strong class="cheque-term">
                                —
                            </strong>

                        </div>

                        <div>

                            <span>
                                Nominal
                            </span>

                            <strong class="cheque-nominal-display">
                                $ 0,00
                            </strong>

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

        {{-- AGREGAR OTRO CHEQUE --}}

        <button
            type="button"
            class="cheque-add-button mt-3"
            id="add-cheque-button"
        >

            <i class="bi bi-plus-circle"></i>
            Agregar otro cheque

        </button>

        {{-- RESUMEN DINÁMICO --}}

        <div class="cheques-live-summary mt-4">

            <div class="cheques-section-title mb-3">
                Resumen de la operación
            </div>

            <div class="cheques-summary-grid">

                <div>

                    <span>
                        Cantidad de cheques
                    </span>

                    <strong id="summary-count">
                        {{ count($initialCheques) }}
                    </strong>

                </div>

                <div>

                    <span>
                        Nominal total
                    </span>

                    <strong id="summary-nominal">
                        $ 0,00
                    </strong>

                </div>

            </div>

            <p class="cheques-note mt-3 mb-0">

                Los intereses, impuestos, descuentos y el
                neto definitivo se obtienen al calcular
                la liquidación.

            </p>

        </div>

        {{-- ACLARACIÓN --}}

        <p class="cheques-note mt-4 mb-0">

            El cálculo utiliza interés simple ACT/365.

            Cada cheque se calcula individualmente y los
            importes se consolidan en una única liquidación.

            El tratamiento tributario del IVA sobre intereses
            e impuestos debe validarse antes de su uso operativo.

        </p>

        {{-- ACCIONES --}}

        <div class="cheques-actions mt-4">

            @unless ($editing)
                <button
                    type="submit"
                    class="secondary-button"
                >
                    <i class="bi bi-calculator"></i>
                    Calcular liquidación
                </button>
            @endunless

            <button
                type="submit"
                formaction="{{ $saveRoute }}"
                @if ($editing) name="_method" value="PUT" @endif
                class="primary-action-button"
            >

                <i class="bi bi-check-lg"></i>
                {{ $editing ? 'Guardar cambios' : 'Guardar operación' }}

            </button>

        </div>

    </form>

</div>

{{-- PLANTILLA PARA CHEQUES DINÁMICOS --}}

<template id="cheque-template">

    <div class="cheque-item" data-cheque-index="__INDEX__">

        <div class="cheque-item-header">

            <strong class="cheque-item-title">
                Cheque
            </strong>

            <button
                type="button"
                class="cheque-remove-button"
                title="Eliminar cheque"
            >
                <i class="bi bi-trash"></i>
                Quitar
            </button>

        </div>

        <div class="cheques-grid">

            <label>
                Importe nominal (ARS)

                <input
                    type="text"
                    class="form-control money-input cheque-nominal"
                    name="cheques[__INDEX__][nominal_amount]"
                    inputmode="decimal"
                    autocomplete="off"
                    required
                    placeholder="100.000,00"
                >
            </label>

            <label>
                Fecha de vencimiento

                <input
                    type="date"
                    class="form-control cheque-due-date"
                    name="cheques[__INDEX__][due_date]"
                    required
                >
            </label>

            <label>
                Formato

                <select
                    name="cheques[__INDEX__][format]"
                    class="form-select"
                >
                    <option value="paper">
                        Cheque físico
                    </option>

                    <option value="echeq">
                        Echeq
                    </option>
                </select>
            </label>

            <label>
                Banco
                <span class="cheques-optional">
                    (opcional)
                </span>

                <input
                    type="text"
                    class="form-control"
                    name="cheques[__INDEX__][bank]"
                    maxlength="120"
                >
            </label>

            <label>
                Emisor
                <span class="cheques-optional">
                    (opcional)
                </span>

                <input
                    type="text"
                    class="form-control"
                    name="cheques[__INDEX__][issuer]"
                    maxlength="150"
                >
            </label>

            <label>
                Número de cheque
                <span class="cheques-optional">
                    (opcional)
                </span>

                <input
                    type="text"
                    class="form-control"
                    name="cheques[__INDEX__][number]"
                    maxlength="80"
                >
            </label>

        </div>

        {{-- CONDICIONES PARTICULARES --}}

        <div class="mt-3">

            <label class="cheque-custom-label">

                <input
                    type="checkbox"
                    class="cheque-custom-toggle"
                >

                Condiciones particulares para este cheque

            </label>

        </div>

        <div
            class="cheque-custom-fields mt-3"
            style="display:none;"
        >

            <div class="cheques-grid">

                @foreach ($parameters as $key => $parameter)

                    <label>

                        {{ $parameter['label'] }}

                        <input
                            type="{{ $key === 'clearing_days' ? 'number' : 'text' }}"
                            class="form-control cheque-custom-input {{ $key === 'clearing_days' ? '' : 'percent-input' }}"
                            data-field="{{ $key }}"
                            name="cheques[__INDEX__][{{ $key }}]"

                            @if ($key === 'clearing_days')

                                min="0"
                                max="30"
                                step="1"

                            @else

                                inputmode="decimal"
                                autocomplete="off"

                            @endif

                            disabled
                        >

                    </label>

                @endforeach

            </div>

        </div>

        {{-- RESUMEN INDIVIDUAL --}}

        <div class="cheque-item-footer">

            <div>

                <span>
                    Plazo computado
                </span>

                <strong class="cheque-term">
                    —
                </strong>

            </div>

            <div>

                <span>
                    Nominal
                </span>

                <strong class="cheque-nominal-display">
                    $ 0,00
                </strong>

            </div>

        </div>

    </div>

</template>

@endsection