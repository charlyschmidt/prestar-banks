@extends('layouts.app')

@section('content')

@php
    $money = static fn($number) =>
        $number === null
            ? '—'
            : '$ ' . number_format((float) $number, 2, ',', '.');

    $percent = static fn($number) =>
        $number === null
            ? '—'
            : rtrim(
                rtrim(
                    number_format((float) $number, 4, ',', ''),
                    '0'
                ),
                ','
            ) . ' %';

    $days = static fn($number) =>
        $number === null
            ? '—'
            : (int) $number . ' días';

    $status = $liquidation->status;

    $statusLabels = [
        'draft' => 'Borrador',
        'held' => 'Retenida',
        'confirmed' => 'Confirmada',
        'deposited' => 'Depositada',
        'accredited' => 'Acreditada',
        'rejected' => 'Rechazada',
    ];

    $statusLabel = $statusLabels[$status]
        ?? ucfirst($status);

    $canConfirm = $status === 'draft'
        && $liquidation->transaction_id === null;

    $canDelete = in_array(
        $status,
        ['draft', 'held'],
        true
    ) && $liquidation->transaction_id === null;

    $accountBalances = $accountBalances ?? collect();

    $hasCashAccounts = $accountBalances->contains(
        fn($balance) =>
            $balance->account?->type === 'cash'
    );

    $hasTransferAccounts = $accountBalances->contains(
        fn($balance) => in_array(
            $balance->account?->type,
            ['bank', 'wallet'],
            true
        )
    );

    $banks = $banks
        ?? \App\Support\ArgentineBanks::all();

    $cheques = $liquidation->cheques;

    $chequeCount = $cheques->count();

    $chequeStatusLabels = [
        'draft' => 'Pendiente de recepción',
        'in_portfolio' => 'En cartera',
        'deposited' => 'Depositado',
        'accredited' => 'Acreditado',
        'rejected' => 'Rechazado',
    ];

    /*
     * Totales calculados a partir de los registros
     * individuales únicamente cuando existe
     * información financiera completa.
     */

    $financialFields = [
        'calendar_days',
        'clearing_days',
        'term_days',
        'annual_rate',
        'interest_amount',
        'cheque_tax_amount',
        'gross_income_amount',
        'vat_amount',
        'total_discount',
        'net_amount',
    ];

    $hasCompleteDetail = $chequeCount > 0
        && $cheques->every(
            fn($cheque) =>
                collect($financialFields)->every(
                    fn($field) => $cheque->{$field} !== null
                )
        );

    $totalNominalCents = $cheques->reduce(
        fn($total, $cheque) =>
            $total + (int) round(
                (float) $cheque->nominal_amount * 100
            ),
        0
    );
@endphp

<div class="page-container cheques-page">

    {{-- ENCABEZADO --}}

    <div class="page-header">

        <div>
            <h1>Liquidación de cheques</h1>

            <p>
                {{ $liquidation->client_name }}
                ·
                {{ $liquidation->operation_date->format('d/m/Y') }}
                ·
                {{ $chequeCount }}
                {{ $chequeCount === 1 ? 'cheque' : 'cheques' }}
            </p>
        </div>

        <a
            href="{{ route('cheques.index') }}"
            class="cheques-link"
        >
            Volver al listado
        </a>

    </div>

    {{-- ERRORES --}}

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

    {{-- RESULTADO PRINCIPAL --}}

    <section class="cheques-panel">

        <div class="cheques-result-header">

            <div class="cheques-section-title mb-0">
                Resultado de liquidación
            </div>

            <span class="cheques-status {{ $status === 'held' ? 'cheques-status-held' : '' }}">
                {{ $statusLabel }}
            </span>

        </div>

        {{-- NETO --}}

        <div class="cheques-net cheques-net-featured">

            <div>
                <span>
                    Neto total a entregar al cliente
                </span>

                <strong class="cheques-net-amount">
                    {{ $money($liquidation->net_amount) }}
                </strong>
            </div>

        </div>

        {{-- ESTADO FINANCIERO --}}

        @if ($liquidation->transaction_id)

            <p class="cheques-note mt-3 mb-0">
                <i class="bi bi-check-circle"></i>

                Operación confirmada el

                <strong>
                    {{ $liquidation->confirmed_at?->format('d/m/Y H:i') ?? '—' }}
                </strong>.

                El dinero fue entregado y los cheques
                ingresaron en cartera.
            </p>

        @elseif ($status === 'draft')

            <p class="cheques-note mt-3 mb-0">
                <i class="bi bi-info-circle"></i>

                Esta liquidación todavía no generó
                movimientos de dinero.

                Seleccioná una cuenta y confirmá
                la entrega al cliente.
            </p>

        @elseif ($status === 'held')

            <p class="cheques-note mt-3 mb-0">
                <i class="bi bi-pause-circle"></i>

                La liquidación está retenida.

                Liberá la retención para poder confirmarla.
            </p>

        @endif

        {{-- TABLA INDIVIDUAL DE CHEQUES --}}

        <div
            class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-4 mb-3"
        >
            <div class="cheques-section-title mb-0">
                Detalle individual de cheques
            </div>

            <span class="cheques-status">
                {{ $chequeCount }}
                {{ $chequeCount === 1 ? 'cheque' : 'cheques' }}
            </span>
        </div>

        @if (!$hasCompleteDetail && $chequeCount > 0)

            <p class="cheques-note mb-3">
                <i class="bi bi-info-circle"></i>

                Algunos cheques fueron registrados antes
                de incorporar el desglose financiero individual.
                Los importes no disponibles se muestran
                sin estimaciones.
            </p>

        @endif

        <div class="table-responsive">

            <table class="table cheques-detail-table align-middle">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Formato</th>
                        <th>Banco</th>
                        <th>Número</th>
                        <th>Emisor</th>
                        <th>Vencimiento</th>
                        <th class="text-end">Calendario</th>
                        <th class="text-end">Clearing</th>
                        <th class="text-end">Plazo</th>
                        <th class="text-end">Tasa</th>
                        <th class="text-end">Nominal</th>
                        <th class="text-end">Intereses</th>
                        <th class="text-end">Imp. cheque</th>
                        <th class="text-end">IIBB</th>
                        <th class="text-end">IVA</th>
                        <th class="text-end">Descuento</th>
                        <th class="text-end">Neto</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($cheques as $index => $cheque)

                        <tr>

                            <td>
                                <strong>{{ $index + 1 }}</strong>
                            </td>

                            <td>
                                {{ $cheque->format === 'echeq'
                                    ? 'Echeq'
                                    : 'Físico' }}
                            </td>

                            <td>
                                {{ $cheque->bank ?: '—' }}
                            </td>

                            <td>
                                {{ $cheque->number ?: '—' }}
                            </td>

                            <td>
                                {{ $cheque->issuer ?: '—' }}
                            </td>

                            <td>
                                <strong>
                                    {{ $cheque->due_date?->format('d/m/Y') ?? '—' }}
                                </strong>
                            </td>

                            <td class="text-end">
                                {{ $days($cheque->calendar_days) }}
                            </td>

                            <td class="text-end">
                                {{ $days($cheque->clearing_days) }}
                            </td>

                            <td class="text-end">
                                <strong>
                                    {{ $days($cheque->term_days) }}
                                </strong>
                            </td>

                            <td class="text-end">
                                {{ $percent($cheque->annual_rate) }}
                            </td>

                            <td class="text-end">
                                <strong>
                                    {{ $money($cheque->nominal_amount) }}
                                </strong>
                            </td>

                            <td class="text-end">
                                {{ $money($cheque->interest_amount) }}
                            </td>

                            <td class="text-end">
                                {{ $money($cheque->cheque_tax_amount) }}
                            </td>

                            <td class="text-end">
                                {{ $money($cheque->gross_income_amount) }}
                            </td>

                            <td class="text-end">
                                {{ $money($cheque->vat_amount) }}
                            </td>

                            <td class="text-end cheques-amount-expense">
                                {{ $money($cheque->total_discount) }}
                            </td>

                            <td class="text-end">
                                <strong>
                                    {{ $money($cheque->net_amount) }}
                                </strong>
                            </td>

                            <td>
                                <span class="cheques-status">
                                    {{ $chequeStatusLabels[$cheque->status]
                                        ?? ucfirst($cheque->status) }}
                                </span>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="18" class="text-center">
                                No hay cheques registrados.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

                @if ($chequeCount > 0)

                    <tfoot>
                        <tr>

                            <th colspan="10" class="text-end">
                                Totales de liquidación
                            </th>

                            <th class="text-end">
                                {{ $money($liquidation->nominal_amount) }}
                            </th>

                            <th class="text-end">
                                {{ $money($liquidation->interest_amount) }}
                            </th>

                            <th class="text-end">
                                {{ $money($liquidation->cheque_tax_amount) }}
                            </th>

                            <th class="text-end">
                                {{ $money($liquidation->gross_income_amount) }}
                            </th>

                            <th class="text-end">
                                {{ $money($liquidation->vat_amount) }}
                            </th>

                            <th class="text-end">
                                {{ $money($liquidation->total_discount) }}
                            </th>

                            <th class="text-end">
                                {{ $money($liquidation->net_amount) }}
                            </th>

                            <th></th>

                        </tr>
                    </tfoot>

                @endif

            </table>

        </div>

        {{-- DETALLE CONSOLIDADO --}}

        <div class="cheques-section-title mt-4">
            Totales de la liquidación
        </div>

        <p class="cheques-note mb-3">
            Importes consolidados de todos los cheques.
            Cada plazo y tasa individual se muestra
            en la tabla superior.
        </p>

        <div class="cheques-summary-grid">

            <div>
                <span>Nominal total</span>

                <strong>
                    {{ $money($liquidation->nominal_amount) }}
                </strong>
            </div>

            <div>
                <span>Intereses totales</span>

                <strong class="cheques-amount-income">
                    {{ $money($liquidation->interest_amount) }}
                </strong>
            </div>

            <div>
                <span>Impuesto al cheque</span>

                <strong class="cheques-amount-expense">
                    {{ $money($liquidation->cheque_tax_amount) }}
                </strong>
            </div>

            <div>
                <span>Ingresos Brutos</span>

                <strong class="cheques-amount-expense">
                    {{ $money($liquidation->gross_income_amount) }}
                </strong>
            </div>

            <div>
                <span>IVA</span>

                <strong class="cheques-amount-expense">
                    {{ $money($liquidation->vat_amount) }}
                </strong>
            </div>

            <div class="cheques-summary-total">
                <span>Descuento total</span>

                <strong class="cheques-amount-expense">
                    {{ $money($liquidation->total_discount) }}
                </strong>
            </div>

        </div>

    </section>

    {{-- CONFIRMAR OPERACIÓN --}}

    @if ($canConfirm)

        <section class="cheques-panel mt-4">

            <div class="cheques-section-title">
                Confirmar operación
            </div>

            <p class="cheques-note mb-3">
                Seleccioná cómo se entregará el dinero.
                Al confirmar, AERIA registrará un único
                egreso y todos los cheques ingresarán en cartera.
            </p>

            <form
                method="POST"
                action="{{ route('cheques.confirm', $liquidation) }}"
                id="cheques-confirm-form"
            >

                @csrf

                <div class="cheques-grid">

                    {{-- FORMA DE ENTREGA --}}

                    <div class="form-group">

                        <label for="cheques-payment-method">
                            Forma de entrega
                        </label>

                        <select
                            name="payment_method"
                            id="cheques-payment-method"
                            class="form-select"
                            required
                        >
                            <option value="">
                                Seleccionar forma de entrega
                            </option>

                            <option
                                value="cash"
                                @disabled(!$hasCashAccounts)
                                @selected(old('payment_method') === 'cash')
                            >
                                Efectivo
                                {{ !$hasCashAccounts
                                    ? '(sin cuentas disponibles)'
                                    : '' }}
                            </option>

                            <option
                                value="transfer"
                                @disabled(!$hasTransferAccounts)
                                @selected(old('payment_method') === 'transfer')
                            >
                                Transferencia bancaria
                                {{ !$hasTransferAccounts
                                    ? '(sin cuentas disponibles)'
                                    : '' }}
                            </option>
                        </select>

                        @if (!$hasCashAccounts)
                            <p class="cheques-account-warning">
                                <i class="bi bi-info-circle"></i>
                                No hay cuentas de efectivo en ARS.

                                <a
                                    href="{{ route('accounts.index') }}"
                                    class="cheques-link"
                                >
                                    Crear una cuenta Caja
                                </a>
                            </p>
                        @endif

                        @if (!$hasTransferAccounts)
                            <p class="cheques-account-warning">
                                <i class="bi bi-info-circle"></i>

                                No hay cuentas bancarias ni billeteras en ARS.

                                <a
                                    href="{{ route('accounts.index') }}"
                                    class="cheques-link"
                                >
                                    Administrar cuentas
                                </a>
                            </p>
                        @endif

                    </div>

                    {{-- CUENTA DE ORIGEN --}}

                    <div class="form-group">

                        <label for="cheques-account-balance">
                            Cuenta de origen (ARS)
                        </label>

                        <select
                            name="account_balance_id"
                            id="cheques-account-balance"
                            class="form-select"
                            required
                        >
                            <option value="">
                                Seleccionar cuenta
                            </option>

                            @foreach ($accountBalances as $balance)

                                <option
                                    value="{{ $balance->id }}"
                                    data-account-type="{{ $balance->account?->type }}"
                                    @selected(old('account_balance_id') == $balance->id)
                                >
                                    {{ $balance->account?->name ?? 'Cuenta' }}
                                    · ARS
                                </option>

                            @endforeach
                        </select>

                    </div>

                    {{-- BANCO DESTINO --}}

                    <div
                        class="form-group"
                        id="cheques-destination-field"
                        style="display: none;"
                    >

                        <label
                            for="cheques-destination-search"
                            class="cheques-destination-label"
                        >
                            Banco de destino

                            <span class="cheques-optional">
                                (cuando corresponda)
                            </span>
                        </label>

                        <div class="bank-search-select searchable-select">

                            <input
                                type="text"
                                id="cheques-destination-search"
                                class="dark-input searchable-select-input"
                                placeholder="Escribí para buscar un banco..."
                                autocomplete="off"
                                role="combobox"
                                aria-autocomplete="list"
                                aria-expanded="false"
                                aria-controls="cheques-destination-options"
                            >

                            <input
                                type="hidden"
                                name="destination_bank"
                                id="cheques-destination-bank"
                                class="searchable-select-value"
                                value="{{ old('destination_bank') }}"
                            >

                            <div
                                id="cheques-destination-options"
                                class="bank-search-options searchable-select-options"
                                role="listbox"
                                hidden
                            >
                                @foreach ($banks as $bank)

                                    <button
                                        type="button"
                                        class="bank-search-option searchable-select-option"
                                        data-value="{{ $bank }}"
                                        role="option"
                                    >
                                        {{ $bank }}
                                    </button>

                                @endforeach
                            </div>

                        </div>

                    </div>

                </div>

                <p class="cheques-note mt-3 mb-0">
                    Para efectivo, seleccioná una cuenta de caja.
                    Para transferencias, seleccioná una cuenta
                    bancaria o billetera y elegí el banco destino.
                </p>

                {{-- IMPORTE --}}

                <div class="cheques-net mt-4">
                    <div>
                        <span>
                            Importe total a entregar
                        </span>

                        <strong class="cheques-net-amount">
                            {{ $money($liquidation->net_amount) }}
                        </strong>
                    </div>
                </div>

            </form>

            {{-- ACCIONES --}}

            <div class="cheques-actions mt-4 d-flex align-items-center flex-wrap gap-3">

                <button
                    type="button"
                    id="cheques-confirm-button"
                    data-amount="{{ $money($liquidation->net_amount) }}"
                    class="primary-action-button"
                >
                    <i class="bi bi-check-circle"></i>
                    Confirmar operación y entregar dinero
                </button>

                <form
                    method="POST"
                    action="{{ route('cheques.hold', $liquidation) }}"
                    id="cheques-hold-form"
                    class="m-0"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        type="button"
                        id="cheques-hold-button"
                        class="secondary-button"
                    >
                        <i class="bi bi-pause-circle"></i>
                        Retener liquidación
                    </button>
                </form>

                @if ($canDelete)

                    <form
                        method="POST"
                        action="{{ route('cheques.destroy', $liquidation) }}"
                        id="cheques-delete-form"
                        class="m-0"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="button"
                            id="cheques-delete-button"
                            class="cheques-danger-button"
                        >
                            <i class="bi bi-trash"></i>
                            Eliminar operación
                        </button>
                    </form>

                @endif

            </div>

        </section>

    @elseif ($status === 'held')

        {{-- OPERACIÓN RETENIDA --}}

        <section class="cheques-panel mt-4">

            <div class="cheques-section-title">
                Acciones de operación
            </div>

            <div class="cheques-actions d-flex align-items-center flex-wrap gap-3">

                <form
                    method="POST"
                    action="{{ route('cheques.release', $liquidation) }}"
                    class="m-0"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="secondary-button"
                    >
                        <i class="bi bi-play-circle"></i>
                        Liberar retención
                    </button>
                </form>

                @if ($canDelete)

                    <form
                        method="POST"
                        action="{{ route('cheques.destroy', $liquidation) }}"
                        id="cheques-delete-form"
                        class="m-0"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="button"
                            id="cheques-delete-button"
                            class="cheques-danger-button"
                        >
                            <i class="bi bi-trash"></i>
                            Eliminar operación
                        </button>
                    </form>

                @endif

            </div>

        </section>

    @endif

    {{-- HISTORIAL --}}

    <section class="cheques-panel mt-4">

        <div class="cheques-section-title">
            Historial de operación
        </div>

        @forelse ($liquidation->events as $event)

            <div class="cheques-history-line">

                {{ $event->created_at?->format('d/m/Y H:i') }}

                ·

                {{ match ($event->action) {
                    'created' => 'Operación registrada',
                    'held' => 'Operación retenida',
                    'released' => 'Retención liberada',
                    'confirmed' => 'Operación confirmada y dinero entregado',
                    'deposited' => 'Cheque depositado',
                    'accredited' => 'Cheque acreditado',
                    'rejected' => 'Cheque rechazado',
                    default => $event->action,
                } }}

            </div>

        @empty

            <p class="cheques-note mb-0">
                No hay eventos registrados.
            </p>

        @endforelse

    </section>

</div>

{{-- CSS EXTERNO --}}

<link
    rel="stylesheet"
    href="{{ asset('css/cheques/create.css') }}?v=1"
>

@endsection