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
                <h1>Liquidación #{{ $liquidation->id }}</h1>
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

        {{-- MENSAJES --}}

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

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
                    {{ $liquidation->confirmed_at?->format('d/m/Y H:i') ?? '—' }}.

                    Movimiento financiero
                    #{{ $liquidation->transaction_id }}.
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

                    <button type="submit" form="cheques-confirm-form" class="primary-action-button"
                        onclick="return confirm('¿Confirmar la entrega de {{ $money($liquidation->net_amount) }} al cliente? Se registrará un egreso real.');">
                        <i class="bi bi-check-circle"></i>
                        Confirmar operación y entregar dinero
                    </button>

                    <form method="POST" action="{{ route('cheques.hold', $liquidation) }}" class="m-0">
                        @csrf
                        @method('PATCH')

                        <button type="submit" class="secondary-button">
                            <i class="bi bi-pause-circle"></i>
                            Retener liquidación
                        </button>

                    </form>

                    @if ($canDelete)
                        <form method="POST" action="{{ route('cheques.destroy', $liquidation) }}" class="m-0"
                            onsubmit="return confirm('¿Eliminar definitivamente esta operación? Esta acción no se puede deshacer.');">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="cheques-danger-button">
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
                        <form method="POST" action="{{ route('cheques.destroy', $liquidation) }}" class="m-0"
                            onsubmit="return confirm('¿Eliminar definitivamente esta operación? Esta acción no se puede deshacer.');">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="cheques-danger-button">
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

    {{-- COMPORTAMIENTO DEL FORMULARIO Y BUSCADOR --}}

    @if ($canConfirm)
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const form = document.getElementById('cheques-confirm-form');

                const paymentMethod = document.getElementById(
                    'cheques-payment-method'
                );

                const accountSelect = document.getElementById(
                    'cheques-account-balance'
                );

                const destinationField = document.getElementById(
                    'cheques-destination-field'
                );

                const bankSearch = document.getElementById(
                    'cheques-destination-search'
                );

                const destinationBank = document.getElementById(
                    'cheques-destination-bank'
                );

                const optionsContainer = document.getElementById(
                    'cheques-destination-options'
                );

                if (
                    !form ||
                    !paymentMethod ||
                    !accountSelect ||
                    !destinationField ||
                    !bankSearch ||
                    !destinationBank ||
                    !optionsContainer
                ) {
                    return;
                }

                const accountOptions = Array.from(
                    accountSelect.querySelectorAll(
                        'option[data-account-type]'
                    )
                );

                const bankOptions = Array.from(
                    optionsContainer.querySelectorAll(
                        '.searchable-select-option'
                    )
                );

                function normalize(value) {
                    return String(value || '')
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .toLowerCase()
                        .trim();
                }

                function closeBankOptions() {
                    optionsContainer.hidden = true;
                    bankSearch.setAttribute('aria-expanded', 'false');
                }

                function filterBankOptions() {

                    const query = normalize(bankSearch.value);

                    let visible = 0;

                    bankOptions.forEach(function(option) {

                        const matches = normalize(
                            option.dataset.value
                        ).includes(query);

                        option.hidden = !matches;
                        option.style.display = matches ? '' : 'none';

                        if (matches) {
                            visible++;
                        }
                    });

                    optionsContainer.hidden = visible === 0;

                    bankSearch.setAttribute(
                        'aria-expanded',
                        visible > 0 ? 'true' : 'false'
                    );
                }

                function clearBank() {
                    bankSearch.value = '';
                    destinationBank.value = '';
                    bankSearch.setCustomValidity('');
                    closeBankOptions();
                }

                function updatePaymentMethod() {

                    const method = paymentMethod.value;
                    const isTransfer = method === 'transfer';

                    destinationField.style.display =
                        isTransfer ? '' : 'none';

                    bankSearch.disabled = !isTransfer;
                    destinationBank.disabled = !isTransfer;

                    if (!isTransfer) {
                        clearBank();
                    }

                    accountOptions.forEach(function(option) {

                        const accountType = option.dataset.accountType;

                        const compatible =
                            (method === 'cash' && accountType === 'cash') ||
                            (
                                method === 'transfer' && ['bank', 'wallet'].includes(accountType)
                            );

                        option.disabled = !compatible;
                        option.hidden = !compatible;
                        option.style.display = compatible ? '' : 'none';
                    });

                    const selected = accountSelect.selectedOptions[0];

                    if (selected && selected.disabled) {
                        accountSelect.value = '';
                    }
                }

                /*
                 * Seleccionar un banco del listado.
                 */
                bankOptions.forEach(function(option) {

                    option.addEventListener('click', function() {

                        const value = option.dataset.value || '';

                        destinationBank.value = value;
                        bankSearch.value = value;
                        bankSearch.setCustomValidity('');

                        closeBankOptions();
                    });
                });

                /*
                 * Buscar bancos por nombre.
                 */
                bankSearch.addEventListener('input', function() {

                    // Si cambia el texto, se invalida la selección anterior.
                    destinationBank.value = '';
                    bankSearch.setCustomValidity('');

                    filterBankOptions();
                });

                bankSearch.addEventListener('focus', function() {
                    if (paymentMethod.value === 'transfer') {
                        filterBankOptions();
                    }
                });

                bankSearch.addEventListener('keydown', function(event) {

                    if (event.key === 'Escape') {
                        closeBankOptions();
                        return;
                    }

                    if (event.key === 'Enter' && !optionsContainer.hidden) {
                        const firstVisible = bankOptions.find(
                            option => !option.hidden
                        );

                        if (firstVisible) {
                            event.preventDefault();
                            firstVisible.click();
                        }
                    }
                });

                document.addEventListener('click', function(event) {

                    if (!destinationField.contains(event.target)) {
                        closeBankOptions();
                    }
                });

                /*
                 * Validar selección al confirmar.
                 *
                 * El input hidden contiene el valor real
                 * que se envía al controlador.
                 */
                form.addEventListener('submit', function(event) {

                    if (paymentMethod.value !== 'transfer') {
                        return;
                    }

                    if (!destinationBank.value.trim()) {

                        event.preventDefault();

                        bankSearch.setCustomValidity(
                            'Seleccioná un banco de la lista.'
                        );

                        bankSearch.reportValidity();
                        bankSearch.focus();

                        filterBankOptions();

                        return;
                    }

                    bankSearch.setCustomValidity('');
                });

                /*
                 * Restaurar un banco anterior si la
                 * validación del servidor devolvió errores.
                 */
                const previousBank = destinationBank.value.trim();

                if (previousBank) {

                    const exists = bankOptions.some(
                        option => option.dataset.value === previousBank
                    );

                    if (exists) {
                        bankSearch.value = previousBank;
                    } else {
                        destinationBank.value = '';
                    }
                }

                paymentMethod.addEventListener(
                    'change',
                    updatePaymentMethod
                );

                updatePaymentMethod();

            });
        </script>
    @endif

@endsection
