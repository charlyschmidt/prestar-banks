@extends('layouts.app')

@section('content')
    @php
        $money = static fn($number) => '$ ' . number_format((float) $number, 2, ',', '.');

        $percent = static fn($number) => rtrim(rtrim(number_format((float) $number, 4, ',', ''), '0'), ',');

        $status = $liquidation->status;

        $statusLabels = [
            'draft' => 'Borrador',
            'held' => 'Retenida',
            'confirmed' => 'Confirmada',
            'deposited' => 'Depositada',
            'accredited' => 'Acreditada',
            'rejected' => 'Rechazada',
        ];

        $statusLabel = $statusLabels[$status] ?? ucfirst($status);

        $canConfirm = $status === 'draft' && $liquidation->transaction_id === null;

        $canDelete = in_array($status, ['draft', 'held'], true) && $liquidation->transaction_id === null;

        $accountBalances = $accountBalances ?? collect();

        $hasCashAccounts = $accountBalances->contains(fn($balance) => $balance->account?->type === 'cash');

        $hasTransferAccounts = $accountBalances->contains(
            fn($balance) => in_array($balance->account?->type, ['bank', 'wallet'], true),
        );

        // Reutilizamos el catálogo existente de AERIA.
        $banks = $banks ?? \App\Support\ArgentineBanks::all();
    @endphp

    <div class="page-container cheques-page">

        {{-- ENCABEZADO --}}

        <div class="page-header">
            <div>
                <h1>Liquidación de cheque</h1>
                <p>
                    {{ $liquidation->client_name }}
                    ·
                    {{ $liquidation->operation_date->format('d/m/Y') }}
                </p>
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

        {{-- RESULTADO DE LIQUIDACIÓN --}}

        <section class="cheques-panel">

            <div class="cheques-result-header">
                <div class="cheques-section-title mb-0">
                    Resultado de liquidación
                </div>

                <span class="cheques-status {{ $status === 'held' ? 'cheques-status-held' : '' }}">
                    {{ $statusLabel }}
                </span>
            </div>

            <div class="cheques-net cheques-net-featured">
                <div>
                    <span>Neto a entregar al cliente</span>

                    <strong class="cheques-net-amount">
                        {{ $money($liquidation->net_amount) }}
                    </strong>
                </div>
            </div>

            @if ($liquidation->transaction_id)
                <p class="cheques-note mt-3 mb-0">
                    <i class="bi bi-check-circle"></i>

                    Operación confirmada el
                    <strong>
                        {{ $liquidation->confirmed_at?->format('d/m/Y H:i') ?? '—' }}
                    </strong>.

                    El dinero fue entregado y el cheque ingresó en cartera.
                </p>
            @elseif ($status === 'draft')
                <p class="cheques-note mt-3 mb-0">
                    <i class="bi bi-info-circle"></i>

                    Esta liquidación todavía no generó movimientos
                    de dinero. Seleccioná una cuenta y confirmá
                    la entrega al cliente.
                </p>
            @elseif ($status === 'held')
                <p class="cheques-note mt-3 mb-0">
                    <i class="bi bi-pause-circle"></i>

                    La liquidación está retenida.
                    Liberá la retención para poder confirmarla.
                </p>
            @endif

            {{-- DATOS DEL CHEQUE --}}

            <div class="cheques-section-title mt-4">
                Datos del cheque
            </div>

            @foreach ($liquidation->cheques as $cheque)
                <div class="cheques-summary-grid">

                    <div>
                        <span>Formato</span>
                        <strong>
                            {{ $cheque->format === 'echeq' ? 'Echeq' : 'Físico' }}
                        </strong>
                    </div>

                    <div>
                        <span>Banco</span>
                        <strong>{{ $cheque->bank ?: '—' }}</strong>
                    </div>

                    <div>
                        <span>Número</span>
                        <strong>{{ $cheque->number ?: '—' }}</strong>
                    </div>

                    <div>
                        <span>Emisor</span>
                        <strong>{{ $cheque->issuer ?: '—' }}</strong>
                    </div>

                    <div>
                        <span>Vencimiento</span>
                        <strong>
                            {{ $cheque->due_date->format('d/m/Y') }}
                        </strong>
                    </div>

                    <div>
                        <span>Importe nominal</span>
                        <strong>
                            {{ $money($cheque->nominal_amount) }}
                        </strong>
                    </div>

                    <div>
                        <span>Estado del cheque</span>

                        <strong>
                            {{ match ($cheque->status) {
                                'draft' => 'Pendiente de recepción',
                                'in_portfolio' => 'En cartera',
                                'deposited' => 'Depositado',
                                'accredited' => 'Acreditado',
                                'rejected' => 'Rechazado',
                                default => ucfirst($cheque->status),
                            } }}
                        </strong>
                    </div>

                </div>
            @endforeach

            {{-- DETALLE FINANCIERO --}}

            <div class="cheques-section-title mt-4">
                Detalle de liquidación
            </div>

            <div class="cheques-summary-grid">

                <div>
                    <span>Plazo calendario</span>
                    <strong>
                        {{ $liquidation->calendar_days }} días
                    </strong>
                </div>

                <div>
                    <span>Clearing bancario</span>
                    <strong>
                        {{ $liquidation->clearing_days }} días
                    </strong>
                </div>

                <div>
                    <span>Plazo computado</span>
                    <strong>
                        {{ $liquidation->term_days }} días
                    </strong>
                </div>

                <div>
                    <span>Tasa anual</span>
                    <strong>
                        {{ $percent($liquidation->annual_rate) }} %
                    </strong>
                </div>

                <div>
                    <span>Intereses</span>
                    <strong class="cheques-amount-income">
                        {{ $money($liquidation->interest_amount) }}
                    </strong>
                </div>

                <div>
                    <span>
                        Impuesto al cheque
                        ({{ $percent($liquidation->cheque_tax_rate) }} %)
                    </span>

                    <strong class="cheques-amount-expense">
                        {{ $money($liquidation->cheque_tax_amount) }}
                    </strong>
                </div>

                <div>
                    <span>
                        Ingresos Brutos
                        ({{ $percent($liquidation->gross_income_rate) }} %)
                    </span>

                    <strong class="cheques-amount-expense">
                        {{ $money($liquidation->gross_income_amount) }}
                    </strong>
                </div>

                <div>
                    <span>
                        IVA ({{ $percent($liquidation->vat_rate) }} %)
                    </span>

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
                    Al confirmar, AERIA registrará el egreso
                    y el cheque ingresará en cartera.
                </p>

                <form method="POST" action="{{ route('cheques.confirm', $liquidation) }}" id="cheques-confirm-form">
                    @csrf

                    <div class="cheques-grid">

                        {{-- FORMA DE ENTREGA --}}

                        <div class="form-group">

                            <label for="cheques-payment-method">
                                Forma de entrega
                            </label>

                            <select name="payment_method" id="cheques-payment-method" class="form-select" required>
                                <option value="">Seleccionar forma de entrega</option>

                                <option value="cash" @disabled(!$hasCashAccounts) @selected(old('payment_method') === 'cash')>
                                    Efectivo
                                    {{ !$hasCashAccounts ? '(sin cuentas disponibles)' : '' }}
                                </option>

                                <option value="transfer" @disabled(!$hasTransferAccounts) @selected(old('payment_method') === 'transfer')>
                                    Transferencia bancaria
                                    {{ !$hasTransferAccounts ? '(sin cuentas disponibles)' : '' }}
                                </option>
                            </select>

                            @if (!$hasCashAccounts)
                                <p class="cheques-account-warning">
                                    <i class="bi bi-info-circle"></i>
                                    No hay cuentas de efectivo en ARS.
                                    <a href="{{ route('accounts.index') }}" class="cheques-link">
                                        Crear una cuenta Caja
                                    </a>
                                </p>
                            @endif

                            @if (!$hasTransferAccounts)
                                <p class="cheques-account-warning">
                                    <i class="bi bi-info-circle"></i>
                                    No hay cuentas bancarias ni billeteras en ARS.
                                    <a href="{{ route('accounts.index') }}" class="cheques-link">
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

                            <select name="account_balance_id" id="cheques-account-balance" class="form-select" required>
                                <option value="">
                                    Seleccionar cuenta
                                </option>

                                @foreach ($accountBalances as $balance)
                                    <option value="{{ $balance->id }}" data-account-type="{{ $balance->account?->type }}"
                                        @selected(old('account_balance_id') == $balance->id)>
                                        {{ $balance->account?->name ?? 'Cuenta' }}
                                        · ARS
                                    </option>
                                @endforeach
                            </select>

                        </div>

                        {{-- BUSCADOR DE BANCO DESTINO --}}

                        <div class="form-group" id="cheques-destination-field" style="display: none;">

                            <label for="cheques-destination-search" class="cheques-destination-label">
                                Banco de destino <span class="cheques-optional">(cuando corresponda)</span>
                            </label>

                            <div class="bank-search-select searchable-select">

                                <input type="text" id="cheques-destination-search"
                                    class="dark-input searchable-select-input" placeholder="Escribí para buscar un banco..."
                                    autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false"
                                    aria-controls="cheques-destination-options">

                                <input type="hidden" name="destination_bank" id="cheques-destination-bank"
                                    class="searchable-select-value" value="{{ old('destination_bank') }}">

                                <div id="cheques-destination-options" class="bank-search-options searchable-select-options"
                                    role="listbox" hidden>

                                    @foreach ($banks as $bank)
                                        <button type="button" class="bank-search-option searchable-select-option"
                                            data-value="{{ $bank }}" role="option">
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
                            <span>Importe a entregar</span>

                            <strong class="cheques-net-amount">
                                {{ $money($liquidation->net_amount) }}
                            </strong>
                        </div>

                    </div>

                </form>

                {{-- LAS TRES ACCIONES EN LA MISMA FILA --}}


                <div class="cheques-actions mt-4" style="display: flex; align-items: center; flex-wrap: wrap; gap: 12px;">

                    {{-- CONFIRMAR OPERACIÓN --}}
                    <button type="button" id="cheques-confirm-button" data-amount="{{ $money($liquidation->net_amount) }}"
                        class="primary-action-button">
                        <i class="bi bi-check-circle"></i>
                        Confirmar operación y entregar dinero
                    </button>

                    {{-- RETENER LIQUIDACIÓN --}}
                    <form method="POST" action="{{ route('cheques.hold', $liquidation) }}" id="cheques-hold-form"
                        class="m-0">

                        @csrf
                        @method('PATCH')

                        <button type="button" id="cheques-hold-button" class="secondary-button">
                            <i class="bi bi-pause-circle"></i>
                            Retener liquidación
                        </button>
                    </form>

                    {{-- ELIMINAR OPERACIÓN --}}
                    @if ($canDelete)
                        <form method="POST" action="{{ route('cheques.destroy', $liquidation) }}"
                            id="cheques-delete-form" class="m-0">

                            @csrf
                            @method('DELETE')

                            <button type="button" id="cheques-delete-button" class="cheques-danger-button">
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

                <div class="cheques-actions" style="display: flex; align-items: center; flex-wrap: wrap; gap: 12px;">

                    <form method="POST" action="{{ route('cheques.release', $liquidation) }}" class="m-0">
                        @csrf
                        @method('PATCH')

                        <button type="submit" class="secondary-button">
                            <i class="bi bi-play-circle"></i>
                            Liberar retención
                        </button>

                    </form>

                    @if ($canDelete)
                        <form method="POST" action="{{ route('cheques.destroy', $liquidation) }}"
                            id="cheques-delete-form" class="m-0">
                            @csrf
                            @method('DELETE')

                            <button type="button" id="cheques-delete-button" class="cheques-danger-button">
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

@endsection
