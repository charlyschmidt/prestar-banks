@extends('layouts.app')

@section('content')
    @php
        $v = static fn(string $key, $fallback = '') => old($key, $form[$key] ?? $fallback);

        $money = static fn($number) => '$ ' . number_format((float) $number, 2, ',', '.');
    @endphp

    <div class="page-container cheques-page">

        <div class="page-header">
            <div>
                <h1>Nueva liquidación</h1>
                <p>Cheque de tercero · Cálculo ACT/365 · ARS</p>
            </div>

            <a href="{{ route('cheques.index') }}" class="cheques-link">
                Volver al listado
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Revisá los datos:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- RESULTADO ARRIBA DEL FORMULARIO --}}

        @if ($preview)
            <section class="cheques-panel cheques-result-panel" id="resultado-liquidacion">

                <div class="cheques-result-header">
                    <div>
                        <div class="cheques-section-title mb-1">
                            Resultado de liquidación
                        </div>

                        <p class="cheques-note mb-0">
                            Cálculo actualizado con los parámetros ingresados.
                            Guardá la operación para registrarla.
                        </p>
                    </div>

                    <span class="cheques-status">
                        Calculada
                    </span>
                </div>

                <div class="cheques-net cheques-net-featured">
                    <div>
                        <span>Neto a entregar al cliente</span>
                        <strong class="cheques-net-amount">
                            {{ $money($preview['net_amount']) }}
                        </strong>
                    </div>
                </div>

                <div class="cheques-summary-grid mt-3">

                    <div>
                        <span>Plazo calendario</span>
                        <strong>
                            {{ $preview['calendar_days'] }} días
                        </strong>
                    </div>

                    <div>
                        <span>Clearing bancario</span>
                        <strong>
                            {{ $preview['clearing_days'] }} días
                        </strong>
                    </div>

                    <div>
                        <span>Plazo computado</span>
                        <strong>
                            {{ $preview['term_days'] }} días
                        </strong>
                    </div>

                    {{-- Intereses: verde --}}
                    <div>
                        <span>Intereses</span>
                        <strong class="cheques-amount-income">
                            {{ $money($preview['interest_amount']) }}
                        </strong>
                    </div>

                    <div>
                        <span>Ingresos Brutos</span>
                        <strong class="cheques-amount-expense">
                            {{ $money($preview['gross_income_amount']) }}
                        </strong>
                    </div>
                    <div>
                        <span>IVA</span>
                        <strong class="cheques-amount-expense">
                            {{ $money($preview['vat_amount']) }}
                        </strong>
                    </div>

                    {{-- Descuento total: rojo --}}
                    <div class="cheques-summary-total">
                        <span>Descuento total</span>
                        <strong class="cheques-amount-expense">
                            {{ $money($preview['total_discount']) }}
                        </strong>
                    </div>

                </div>

                <div class="cheques-actions mt-4">

                    <button type="submit" form="cheques-form" formaction="{{ route('cheques.store') }}"
                        class="primary-action-button">
                        <i class="bi bi-check-lg"></i>
                        Guardar operación
                    </button>

                    <a href="#datos-liquidacion" class="secondary-button">
                        <i class="bi bi-pencil"></i>
                        Modificar datos
                    </a>

                </div>

            </section>
        @endif

        {{-- FORMULARIO DE LIQUIDACIÓN --}}

        <form id="cheques-form" method="POST" action="{{ route('cheques.preview') }}" class="cheques-panel">

            @csrf

            <div id="datos-liquidacion" class="cheques-section-title">
                Datos de operación
            </div>

            <div class="cheques-grid">

                <label>
                    Cliente
                    <input type="text" class="form-control" name="client_name" maxlength="150" required
                        value="{{ $v('client_name') }}" placeholder="Nombre del cliente">
                </label>

                <label>
                    Fecha de operación
                    <input type="date" class="form-control" name="operation_date" required
                        value="{{ $v('operation_date') }}">
                </label>

            </div>

            <div class="cheques-section-title mt-4">
                Datos del cheque
            </div>

            <div class="cheques-grid">

                <label>
                    Importe nominal (ARS)
                    <input type="number" step="0.01" min="0.01" class="form-control money-input" name="nominal_amount" required
                        value="{{ $v('nominal_amount') }}" placeholder="100000.00">
                </label>

                <label>
                    Fecha de vencimiento
                    <input type="date" class="form-control" name="due_date" required value="{{ $v('due_date') }}">
                </label>

                <label>
                    Formato
                    <select name="format" class="form-select">
                        <option value="paper" @selected($v('format', 'paper') === 'paper')>
                            Cheque físico
                        </option>

                        <option value="echeq" @selected($v('format') === 'echeq')>
                            Echeq
                        </option>
                    </select>
                </label>

                <label>
                    Banco
                    <span class="cheques-optional">(opcional)</span>

                    <input type="text" class="form-control" name="bank" maxlength="120" value="{{ $v('bank') }}">
                </label>

                <label>
                    Emisor
                    <span class="cheques-optional">(opcional)</span>

                    <input type="text" class="form-control" name="issuer" maxlength="150" value="{{ $v('issuer') }}">
                </label>

                <label>
                    Número de cheque
                    <span class="cheques-optional">(opcional)</span>

                    <input type="text" class="form-control" name="number" maxlength="80" value="{{ $v('number') }}">
                </label>

            </div>

            <div class="cheques-section-title mt-4">
                Parámetros de liquidación
            </div>

            <div class="cheques-grid">

                <label>
                    Tasa anual (%)
                    <input type="number" step="0.0001" min="0" max="300" class="form-control money-input"
                        name="annual_rate" required value="{{ $v('annual_rate') }}">
                </label>

                <label>
                    Días adicionales de clearing
                    <input type="number" step="1" min="0" max="30" class="form-control"
                        name="clearing_days" required value="{{ $v('clearing_days') }}">
                </label>

                <label>
                    Impuesto al cheque (%)
                    <input type="number" step="0.0001" min="0" max="100" class="form-control money-input"
                        name="cheque_tax_rate" required value="{{ $v('cheque_tax_rate') }}">
                </label>

                <label>
                    Ingresos Brutos (%)
                    <input type="number" step="0.0001" min="0" max="100" class="form-control money-input"
                        name="gross_income_rate" required value="{{ $v('gross_income_rate') }}">
                </label>

                <label>
                    IVA (%)
                    <input type="number" step="0.0001" min="0" max="100" class="form-control money-input"
                        name="vat_rate" required value="{{ $v('vat_rate') }}">
                </label>

            </div>

            <p class="cheques-note mt-3 mb-0">
                El cálculo utiliza interés simple ACT/365.
                El IVA se calcula sobre intereses más ambos impuestos
                según la referencia inicial. Su tratamiento tributario
                debe validarse antes de utilizarse operativamente.
            </p>

            <div class="cheques-actions mt-4">

                <button type="submit" class="secondary-button">
                    <i class="bi bi-calculator"></i>
                    Calcular liquidación
                </button>

                <button type="submit" formaction="{{ route('cheques.store') }}" class="primary-action-button">
                    <i class="bi bi-check-lg"></i>
                    Guardar operación
                </button>

            </div>

        </form>

    </div>

    @if ($preview)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const result = document.getElementById('resultado-liquidacion');

                if (result) {
                    const reducedMotion = window.matchMedia(
                        '(prefers-reduced-motion: reduce)'
                    ).matches;

                    result.scrollIntoView({
                        behavior: reducedMotion ? 'instant' : 'smooth',
                        block: 'start'
                    });
                }
            });
        </script>
    @endif

@endsection
