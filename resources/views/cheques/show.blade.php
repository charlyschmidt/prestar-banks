@extends('layouts.app')

@section('content')

    @php
        $money = static fn($number) => $number === null ? '—' : '$ ' . number_format((float) $number, 2, ',', '.');

        $percent = static fn($number) => $number === null
            ? '—'
            : rtrim(rtrim(number_format((float) $number, 4, ',', ''), '0'), ',') . ' %';

        $days = static fn($number) => $number === null ? '—' : (int) $number . ' días';

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

        $canEdit = $canConfirm;

        $canDelete = in_array($status, ['draft', 'held'], true) && $liquidation->transaction_id === null;

        $accountBalances = $accountBalances ?? collect();

        $hasCashAccounts = $accountBalances->contains(fn($balance) => $balance->account?->type === 'cash');

        $hasTransferAccounts = $accountBalances->contains(
            fn($balance) => in_array($balance->account?->type, ['bank', 'wallet'], true),
        );

        $banks = $banks ?? \App\Support\ArgentineBanks::all();

        $cheques = $liquidation->cheques;

        $chequeCount = $cheques->count();

        $chequeStatusLabels = [
            'draft' => 'Pendiente de recepción',
            'in_portfolio' => 'En cartera',
            'deposited' => 'Depositado',
            'accredited' => 'Acreditado',
            'rejected' => 'Rechazado',
            'delivered' => 'Entregado a tercero',
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

        $hasCompleteDetail =
            $chequeCount > 0 &&
            $cheques->every(fn($cheque) => collect($financialFields)->every(fn($field) => $cheque->{$field} !== null));

        $totalNominalCents = $cheques->reduce(
            fn($total, $cheque) => $total + (int) round((float) $cheque->nominal_amount * 100),
            0,
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

            <a href="{{ route('cheques.index') }}" class="cheques-link">
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

                <div class="d-flex align-items-center gap-2">                  

                    @if ($status === 'draft' && $canEdit)
                        <a href="{{ route('cheques.edit', $liquidation) }}" class="secondary-button cheques-edit-badge">
                            <i class="bi bi-pencil-square"></i>
                            Editar borrador
                        </a>
                    @endif

                    <span class="cheques-status {{ $status === 'held' ? 'cheques-status-held' : '' }}">
                        {{ $statusLabel }}
                    </span>

                </div>

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

            @if ($status === 'confirmed')
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

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-4 mb-3">
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
                                    {{ $cheque->format === 'echeq' ? 'Echeq' : 'Físico' }}
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
                                        {{ $chequeStatusLabels[$cheque->status] ?? ucfirst($cheque->status) }}
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


        {{-- MEDIOS DE PAGO REGISTRADOS — OPERACIONES CONFIRMADAS --}}

        @if ($status === 'confirmed')
            @php
                /*
                 * Los importes se consultan de la liquidación confirmada.
                 * No recalculamos cotizaciones ni reconstruimos pagos históricos.
                 */
                $liquidation->loadMissing([
                    'deliveries.account',
                    'deliveries.deliveredCheque',
                    'deliveries.transaction',
                ]);

                $confirmedDeliveries = $liquidation->deliveries->sortBy('id')->values();

                $deliveredCents = $confirmedDeliveries->sum(
                    fn($delivery) => (int) round((float) $delivery->amount_ars * 100),
                );

                $confirmedNetCents = (int) round((float) $liquidation->net_amount * 100);
                $differenceCents = $confirmedNetCents - $deliveredCents;

                $deliveryTypeLabels = [
                    'cash' => 'Efectivo',
                    'transfer' => 'Transferencia bancaria',
                    'third_party_cheque' => 'Cheque de terceros',
                ];

                $currencyMoney = static fn($amount, $currency) => $amount === null
                    ? '—'
                    : ($currency ?: 'ARS') . ' ' . number_format((float) $amount, 2, ',', '.');
            @endphp

            <section class="cheques-panel mt-4">

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div class="cheques-section-title mb-0">
                        Detalle de fondos y valores entregados
                    </div>

                    <span class="cheques-status">
                        {{ $confirmedDeliveries->count() }}
                        {{ $confirmedDeliveries->count() === 1 ? 'medio de pago' : 'medios de pago' }}
                    </span>
                </div>

                <p class="cheques-note mb-3">
                    Distribución registrada al confirmar la operación.
                    Los importes y cotizaciones son los guardados en ese momento.
                    Los impuestos de transferencia se muestran aparte porque no forman parte
                    del neto recibido por el cliente.
                </p>

                @if ($confirmedDeliveries->isEmpty())
                    <div class="cheques-note mb-0">
                        <i class="bi bi-info-circle"></i>
                        Esta liquidación no tiene un desglose histórico de medios de pago.
                        No se pueden reconstruir los medios de entrega con certeza.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table cheques-deliveries-table align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Medio de pago</th>
                                    <th>Cuenta de origen</th>
                                    <th>Banco destino / referencia</th>
                                    <th>Moneda</th>
                                    <th class="text-end">Importe original</th>
                                    <th class="text-end">Cotización ARS</th>
                                    <th class="text-end">Entregado en ARS</th>
                                    <th class="text-end">Imp. transferencia</th>
                                    <th class="text-end">Débito total</th>

                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($confirmedDeliveries as $index => $delivery)
                                    @php
                                        $deliveryTransaction = $delivery->transaction;
                                        $deliveredCheque = $delivery->deliveredCheque;

                                        $chequeReference = $deliveredCheque
                                            ? trim(
                                                ($deliveredCheque->bank ?: 'Banco sin identificar') .
                                                    ' · N.º ' .
                                                    ($deliveredCheque->number ?: 'S/N'),
                                            )
                                            : null;

                                        $deliveryReference = match ($delivery->type) {
                                            'transfer' => $delivery->notes ?: '—',
                                            'third_party_cheque' => $chequeReference ?: 'Cheque sin referencia',
                                            default => '—',
                                        };

                                        /*
                                         * No estimamos impuestos después de la confirmación:
                                         * solo mostramos lo registrado en Transaction.
                                         */
                                        $recordedTax = $deliveryTransaction?->transfer_tax_amount;
                                        $recordedDebit = $deliveryTransaction
                                            ? (float) $deliveryTransaction->amount + (float) ($recordedTax ?? 0)
                                            : null;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $index + 1 }}</strong></td>
                                        <td>
                                            <strong>{{ $deliveryTypeLabels[$delivery->type] ?? $delivery->type }}</strong>
                                            @if ($deliveredCheque)
                                                <div class="cheques-note mt-1 mb-0">
                                                    Emisor: {{ $deliveredCheque->issuer ?: '—' }}
                                                    · Vence: {{ $deliveredCheque->due_date?->format('d/m/Y') ?? '—' }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $delivery->type === 'third_party_cheque' ? 'Cartera de cheques' : ($delivery->account?->name ?: '—') }}
                                        </td>
                                        <td>{{ $deliveryReference }}</td>
                                        <td>{{ $delivery->currency ?: '—' }}</td>
                                        <td class="text-end">
                                            {{ $currencyMoney($delivery->amount, $delivery->currency) }}
                                        </td>
                                        <td class="text-end">
                                            {{ $delivery->currency === 'ARS'
                                                ? '—'
                                                : ($delivery->exchange_rate === null
                                                    ? '—'
                                                    : number_format((float) $delivery->exchange_rate, 6, ',', '.')) }}
                                        </td>
                                        <td class="text-end cheques-amount-income">
                                            <strong>{{ $money($delivery->amount_ars) }}</strong>
                                        </td>
                                        <td class="text-end">
                                            {{ $recordedTax === null ? '—' : $currencyMoney($recordedTax, $delivery->currency) }}
                                        </td>
                                        <td class="text-end">
                                            {{ $recordedDebit === null ? '—' : $currencyMoney($recordedDebit, $delivery->currency) }}
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="7" class="text-end">Total entregado al cliente</th>
                                    <th class="text-end cheques-amount-income">
                                        {{ $money($deliveredCents / 100) }}
                                    </th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="cheques-summary-grid mt-4">
                        <div>
                            <span>Neto de liquidación</span>
                            <strong>{{ $money($liquidation->net_amount) }}</strong>
                        </div>
                        <div>
                            <span>Total entregado al cliente (ARS)</span>
                            <strong class="cheques-amount-income">{{ $money($deliveredCents / 100) }}</strong>
                        </div>
                        <div class="cheques-summary-total">
                            <span>Diferencia contra el neto (ARS)</span>
                            <strong
                                class="{{ $differenceCents === 0 ? 'cheques-amount-income' : 'cheques-amount-expense' }}">
                                {{ $money($differenceCents / 100) }}
                            </strong>
                        </div>
                    </div>

                    @if ($differenceCents !== 0)
                        <div class="alert-error mt-3" role="alert">
                            <i class="bi bi-exclamation-triangle"></i>
                            El desglose registrado no coincide con el neto de la liquidación.
                            Revisá el historial de esta operación.
                        </div>
                    @endif
                @endif
            </section>
        @endif

        {{-- CONFIRMAR OPERACIÓN --}}

        @if ($canConfirm)
            @php
                /*
    |--------------------------------------------------------------------------
    | AERIA Finance — Datos para Vue 3
    |--------------------------------------------------------------------------
    */

                /*
    |--------------------------------------------------------------------------
    | Cuentas y monedas
    |--------------------------------------------------------------------------
    |
    | Cada opción representa un AccountBalance.
    | Conservamos account_id para recuperar las
    | entregas previamente guardadas.
    |
    */

                $vueAccounts = $accountBalances
                    ->map(
                        fn($balance) => [
                            'id' => $balance->id,
                            'account_id' => $balance->account_id,
                            'name' => $balance->account?->name ?? 'Cuenta',
                            'type' => $balance->account?->type,
                            'currency' => $balance->currency,
                            'available_balance' => $balance->available_balance,
                            'transfer_tax_rate' => $balance->transfer_tax_rate,
                        ],
                    )
                    ->values()
                    ->all();

                /*
    |--------------------------------------------------------------------------
    | Cheques disponibles en cartera
    |--------------------------------------------------------------------------
    */

                $vueCheques = ($portfolioCheques ?? collect())
                    ->map(
                        fn($cheque) => [
                            'id' => $cheque->id,
                            'bank' => $cheque->bank,
                            'number' => $cheque->number,
                            'issuer' => $cheque->issuer,
                            'nominal_amount' => $cheque->nominal_amount,
                            'due_date' => $cheque->due_date?->format('Y-m-d'),
                        ],
                    )
                    ->values()
                    ->all();

                /*
    |--------------------------------------------------------------------------
    | Entregas guardadas
    |--------------------------------------------------------------------------
    |
    | Se envían los datos originales.
    | Vue reconstruye las filas editables.
    |
    */

                $vueExistingDeliveries = $liquidation->deliveries
                    ->map(
                        fn($delivery) => [
                            'id' => $delivery->id,
                            'type' => $delivery->type,
                            'currency' => $delivery->currency,
                            'amount' => $delivery->amount,
                            'exchange_rate' => $delivery->exchange_rate,
                            'amount_ars' => $delivery->amount_ars,
                            'account_id' => $delivery->account_id,
                            'delivered_cheque_id' => $delivery->delivered_cheque_id,
                            'destination_bank' => $delivery->isTransfer() ? $delivery->notes : null,
                        ],
                    )
                    ->values()
                    ->all();

                /*
    |--------------------------------------------------------------------------
    | Bancos
    |--------------------------------------------------------------------------
    */

                $vueBanks = collect($banks)->values()->all();

                /*
    |--------------------------------------------------------------------------
    | Estado de la distribución guardada
    |--------------------------------------------------------------------------
    |
    | El botón Confirmar solo podrá habilitarse
    | cuando el backend tenga una distribución
    | completa y coincidente con el neto.
    |
    */

                $netCents = (int) round((float) $liquidation->net_amount * 100);

                $savedCents = $liquidation->deliveries->sum(
                    fn($delivery) => (int) round((float) $delivery->amount_ars * 100),
                );

                $hasSavedDistribution = $liquidation->deliveries->isNotEmpty();

                /*
|--------------------------------------------------------------------------
| Validar distribución guardada
|--------------------------------------------------------------------------
|
| Esta comprobación solo determina si podemos
| ofrecer la confirmación en la interfaz.
|
| El método confirm() volverá a validar:
| - Saldos reales.
| - Importes y cotizaciones.
| - Cheques disponibles.
| - Estado de la liquidación.
|
*/

                $netCents = (int) round((float) $liquidation->net_amount * 100);

                $savedDeliveries = $liquidation->deliveries;

                $savedCents = $savedDeliveries->sum(fn($delivery) => (int) round((float) $delivery->amount_ars * 100));

                $hasSavedDistribution = $savedDeliveries->isNotEmpty();

                $savedDistributionComplete =
                    $liquidation->status === 'draft' &&
                    $liquidation->transaction_id === null &&
                    $hasSavedDistribution &&
                    $netCents > 0 &&
                    $savedCents === $netCents &&
                    $savedDeliveries->every(fn($delivery) => $delivery->transaction_id === null);
            @endphp


            <section class="cheques-panel mt-4">

                {{--
            Vue administra exclusivamente los medios de pago.
            Los formularios de acciones continúan en Blade.
        --}}


                <div id="cheque-deliveries-app" data-net-amount="{{ $liquidation->net_amount }}"
                    data-accounts="{{ json_encode($vueAccounts, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}"
                    data-cheques="{{ json_encode($vueCheques, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}"
                    data-banks="{{ json_encode($vueBanks, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}"
                    data-existing-deliveries="{{ json_encode($vueExistingDeliveries, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}"
                    data-save-url="{{ route('cheques.deliveries.store', $liquidation) }}"
                    data-csrf-token="{{ csrf_token() }}">
                    <p class="cheques-note mb-0">
                        Cargando medios de pago...
                    </p>
                </div>


                {{--
            Formulario reservado para la confirmación.

            El guardado y validación de entregas todavía
            debe conectarse con el controlador Laravel.
        --}}

                <form method="POST" action="{{ route('cheques.confirm', $liquidation) }}" id="cheques-confirm-form">
                    @csrf
                </form>

                {{-- ACCIONES --}}

                <div class="cheques-actions mt-4 d-flex align-items-center flex-wrap gap-3">

                    {{-- Editar únicamente un borrador sin movimientos --}}
                    @if ($canEdit)
                        <a href="{{ route('cheques.edit', $liquidation) }}" class="secondary-button">
                            <i class="bi bi-pencil-square"></i>
                            Editar borrador
                        </a>
                    @endif

                    <button type="button" id="cheques-confirm-button"
                        data-amount="{{ $money($liquidation->net_amount) }}"
                        data-saved-complete="{{ $savedDistributionComplete ? '1' : '0' }}" class="primary-action-button"
                        disabled>
                        <i class="bi bi-check-circle"></i>

                        Confirmar operación
                    </button>

                    <p id="cheques-confirm-status" class="cheques-note mb-0" role="status" aria-live="polite">
                        @if ($savedDistributionComplete)
                            La distribución está guardada y completa.
                            Podés confirmar cuando no haya modificaciones pendientes.
                        @elseif ($hasSavedDistribution)
                            La distribución guardada todavía no coincide
                            con el neto de la liquidación.
                        @else
                            Primero guardá los medios de pago.
                        @endif
                    </p>


                    {{-- RETENER --}}

                    <form method="POST" action="{{ route('cheques.hold', $liquidation) }}" id="cheques-hold-form"
                        class="m-0">
                        @csrf
                        @method('PATCH')

                        <button type="button" id="cheques-hold-button" class="secondary-button">
                            <i class="bi bi-pause-circle"></i>
                            Retener liquidación
                        </button>

                    </form>

                    {{-- ELIMINAR --}}

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

                <div class="cheques-actions d-flex align-items-center flex-wrap gap-3">

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
                        'updated' => 'Borrador actualizado',
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
