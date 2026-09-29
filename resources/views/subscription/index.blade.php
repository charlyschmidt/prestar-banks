@extends('layouts.app')


@section('content')

    <div class="page-container">


        {{-- ==========================
             HEADER
        ========================== --}}

        <div class="page-header">

            <div>

                <h1>
                    Suscripción
                </h1>

                <p>
                    Administrá el plan de AERIA Finance de tu empresa
                </p>

            </div>

        </div>



        @if ($company->subscription_lifetime)
            {{-- LIFETIME --}}

            <div class="subscription-dashboard-status subscription-active-status">

                <div class="subscription-active-main">

                    <div class="subscription-active-icon">
                        <i class="bi bi-infinity"></i>
                    </div>

                    <div>
                        <span class="subscription-dashboard-label">
                            Suscripción activa
                        </span>

                        <h2>
                            Acceso de por vida
                        </h2>

                        <p>
                            Esta empresa cuenta con acceso permanente a AERIA Finance.
                        </p>
                    </div>

                </div>

                <div class="subscription-active-badge">
                    <i class="bi bi-check-circle-fill"></i>
                    Lifetime
                </div>

            </div>


            {{-- ==========================
         DETALLES
    ========================== --}}

            <section class="subscription-dashboard-section">

                <div class="subscription-dashboard-heading">

                    <h2>
                        Detalles de la suscripción
                    </h2>

                    <p>
                        Información del acceso actual de tu empresa.
                    </p>

                </div>


                <div class="subscription-active-grid">

                    <div class="subscription-detail-card">

                        <div class="subscription-detail-block">

                            <span class="subscription-detail-label">
                                Plan
                            </span>

                            <strong class="subscription-detail-value">
                                Lifetime
                            </strong>

                            <small>
                                Acceso completo a AERIA Finance
                            </small>

                        </div>


                        <div class="subscription-detail-divider"></div>


                        <div class="subscription-detail-block">

                            <span class="subscription-detail-label">
                                Vigencia
                            </span>

                            <strong class="subscription-detail-value">
                                Sin vencimiento
                            </strong>

                            <small>
                                Acceso permanente
                            </small>

                        </div>

                    </div>


                    <div class="subscription-detail-card">

                        <div class="subscription-detail-block">

                            <span class="subscription-detail-label">
                                Renovación
                            </span>

                            <strong class="subscription-detail-value">
                                No requerida
                            </strong>

                            <small>
                                No existen renovaciones periódicas
                            </small>

                        </div>


                        <div class="subscription-detail-divider"></div>


                        <div class="subscription-detail-block">

                            <span class="subscription-detail-label">
                                Estado
                            </span>

                            <strong class="subscription-detail-value">
                                Activa
                            </strong>

                            <small>
                                Sin fecha de finalización
                            </small>

                        </div>

                    </div>

                </div>

            </section>


            {{-- ==========================
         ACCESO PERMANENTE
    ========================== --}}

            <section class="subscription-dashboard-section mt-3">

                <div class="subscription-renewal-card">

                    <div class="subscription-renewal-info">

                        <div class="subscription-renewal-icon">
                            <i class="bi bi-infinity"></i>
                        </div>

                        <div>

                            <strong>
                                No requiere renovación
                            </strong>

                            <p>
                                Esta empresa cuenta con acceso permanente a AERIA Finance.
                            </p>

                        </div>

                    </div>

                </div>

            </section>
        @elseif ($company->isOnTrial() && !$company->hasActiveSubscription())
            @php
                $trialDaysRemaining = max(1, (int) ceil(now()->diffInSeconds($company->trial_ends_at, false) / 86400));
            @endphp



            {{-- ==========================
                 TRIAL
            ========================== --}}

            <div class="subscription-dashboard-status subscription-trial-status">

                <div class="subscription-trial-main">

                    <div class="subscription-trial-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>


                    <div class="subscription-trial-info">

                        <span class="subscription-dashboard-label">
                            Período de prueba
                        </span>

                        <div class="subscription-trial-days">

                            <strong>
                                {{ $trialDaysRemaining }}
                            </strong>

                            <div>

                                <span>
                                    {{ $trialDaysRemaining === 1 ? 'día restante' : 'días restantes' }}
                                </span>

                                <small>
                                    de tu prueba gratuita
                                </small>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="subscription-trial-expiration">

                    <span>
                        Finaliza el
                    </span>

                    <strong>
                        {{ $company->trial_ends_at->format('d/m/Y') }}
                    </strong>

                </div>


                <div class="subscription-trial-progress">

                    <div class="subscription-trial-progress-header">

                        <span>
                            Período de prueba
                        </span>

                        <span>
                            {{ $trialDaysRemaining }} / 7 días
                        </span>

                    </div>


                    @php
                        $trialProgressStatus = match (true) {
                            $trialDaysRemaining <= 1 => 'danger',
                            $trialDaysRemaining <= 3 => 'warning',
                            $trialDaysRemaining <= 5 => 'attention',
                            default => 'success',
                        };

                        $trialProgressWidth = min(100, ($trialDaysRemaining / 7) * 100);
                    @endphp


                    <div class="subscription-trial-progress-track">

                        <div class="subscription-trial-progress-bar subscription-trial-progress-{{ $trialProgressStatus }}"
                            style="width: {{ $trialProgressWidth }}%"></div>

                    </div>

                </div>


                <p class="subscription-trial-message">
                    Elegí un plan antes del vencimiento para continuar
                    utilizando AERIA Finance sin interrupciones.
                </p>

            </div>



            {{-- ==========================
                 EMAIL DE PAGO
            ========================== --}}

            <section class="subscription-dashboard-section">

                <div class="subscription-dashboard-heading">

                    <h2>
                        Datos de facturación
                    </h2>

                    <p>
                        Indicá el email de la cuenta que utilizarás
                        para pagar mediante Mercado Pago.
                    </p>

                </div>


                <div class="form-card">

                    <div class="form-group">

                        <label for="subscription-payer-email">
                            Email de Mercado Pago
                        </label>

                        <input type="email" id="subscription-payer-email"
                            value="{{ old('payer_email', auth()->user()->email) }}" placeholder="ejemplo@empresa.com"
                            autocomplete="email" required>

                        <small>
                            Puede ser distinto al email con el que ingresás a AERIA Finance.
                        </small>

                    </div>

                </div>

            </section>



            {{-- ==========================
                 PRUEBA MERCADO PAGO
            ========================== --}}

            @if (auth()->check() && strtolower(auth()->user()->email) === 'centralpadelar@gmail.com')
                <div class="subscription-test-box">

                    <div>

                        <strong>
                            Prueba de Mercado Pago
                        </strong>

                        <p>
                            Genera una suscripción real de prueba por $50 ARS
                            con inicio inmediato.
                        </p>

                    </div>


                    <form method="POST" action="{{ route('subscription.test') }}" class="subscription-payer-form">

                        @csrf

                        <input type="hidden" name="payer_email" class="subscription-payer-email-hidden">

                        <button type="submit" class="primary-button">
                            <i class="bi bi-credit-card"></i>
                            Probar suscripción $50
                        </button>

                    </form>

                </div>
            @endif



            {{-- ==========================
                 PLANES
            ========================== --}}

            <section class="subscription-dashboard-section mt-4">


                <div class="subscription-dashboard-heading">

                    <h2>
                        Elegí tu plan
                    </h2>

                    <p>
                        Ambos planes incluyen todas las funcionalidades
                        de AERIA Finance.
                    </p>

                </div>



                <div class="subscription-dashboard-plans">


                    {{-- ==========================
                         PLAN MENSUAL
                    ========================== --}}

                    <article class="subscription-dashboard-plan">


                        <div class="subscription-plan-content">


                            <div class="subscription-plan-header">

                                <span class="subscription-dashboard-plan-name">
                                    Plan mensual
                                </span>

                            </div>


                            <div class="subscription-dashboard-price">

                                <span class="subscription-price-currency">
                                    USD
                                </span>

                                <strong>
                                    99
                                </strong>

                                <span class="subscription-price-period">
                                    / mes
                                </span>

                            </div>


                            <p class="subscription-plan-description">
                                Facturación mensual con renovación automática.
                                El cobro se realiza en pesos argentinos mediante
                                Mercado Pago.
                            </p>


                            <div class="subscription-plan-features">

                                <div>

                                    <i class="bi bi-check2"></i>

                                    <span>
                                        Todas las funcionalidades
                                    </span>

                                </div>


                                <div>

                                    <i class="bi bi-arrow-repeat"></i>

                                    <span>
                                        Renovación mensual automática
                                    </span>

                                </div>


                                <div>

                                    <i class="bi bi-currency-exchange"></i>

                                    <span>
                                        USD 99 convertidos a pesos según
                                        la cotización oficial vendedora
                                        aplicada al momento de facturar
                                    </span>

                                </div>


                                <div>

                                    <i class="bi bi-credit-card"></i>

                                    <span>
                                        Pago procesado por Mercado Pago
                                    </span>

                                </div>

                            </div>

                        </div>


                        <form method="POST" action="{{ route('subscription.subscribe') }}"
                            class="subscription-plan-action subscription-payer-form">

                            @csrf

                            <input type="hidden" name="plan" value="monthly">

                            <input type="hidden" name="payer_email" class="subscription-payer-email-hidden">

                            <button type="submit" class="primary-button">
                                Elegir plan mensual
                            </button>

                        </form>


                    </article>



                    {{-- ==========================
                         PLAN ANUAL
                    ========================== --}}

                    <article class="subscription-dashboard-plan subscription-dashboard-plan-featured">


                        <div class="subscription-plan-content">


                            <div class="subscription-plan-header">

                                <span class="subscription-dashboard-plan-name">
                                    Plan anual
                                </span>

                                <span class="subscription-dashboard-saving">
                                    Ahorrás 2 meses
                                </span>

                            </div>


                            <div class="subscription-dashboard-price">

                                <span class="subscription-price-currency">
                                    USD
                                </span>

                                <strong>
                                    990
                                </strong>

                                <span class="subscription-price-period">
                                    / año
                                </span>

                            </div>


                            <span class="subscription-dashboard-equivalent">
                                Equivale a USD 82,50 por mes
                            </span>


                            <p class="subscription-plan-description">
                                Un único cobro anual con renovación automática.
                                El importe se procesa en pesos argentinos
                                mediante Mercado Pago.
                            </p>


                            <div class="subscription-plan-features">

                                <div>

                                    <i class="bi bi-check2"></i>

                                    <span>
                                        Todas las funcionalidades
                                    </span>

                                </div>


                                <div>

                                    <i class="bi bi-arrow-repeat"></i>

                                    <span>
                                        Renovación anual automática
                                    </span>

                                </div>


                                <div>

                                    <i class="bi bi-currency-exchange"></i>

                                    <span>
                                        USD 990 convertidos a pesos según
                                        la cotización oficial vendedora
                                        aplicada al momento de facturar
                                    </span>

                                </div>


                                <div>

                                    <i class="bi bi-credit-card"></i>

                                    <span>
                                        Pago procesado por Mercado Pago
                                    </span>

                                </div>

                            </div>

                        </div>


                        <form method="POST" action="{{ route('subscription.subscribe') }}"
                            class="subscription-plan-action subscription-payer-form">

                            @csrf

                            <input type="hidden" name="plan" value="annual">

                            <input type="hidden" name="payer_email" class="subscription-payer-email-hidden">

                            <button type="submit" class="primary-button">
                                Elegir plan anual
                            </button>

                        </form>


                    </article>

                </div>



                {{-- ==========================
                     ACLARACIÓN
                ========================== --}}

                <div class="subscription-dashboard-note">

                    <i class="bi bi-info-circle"></i>

                    <p>
                        Los precios de AERIA Finance están expresados en dólares
                        estadounidenses. Los cobros se procesan en pesos argentinos
                        mediante Mercado Pago, utilizando la cotización oficial
                        vendedora aplicada al momento de cada facturación.
                    </p>

                </div>


            </section>
        @elseif ($company->hasActiveSubscription())
            {{-- ==========================
         SUSCRIPCIÓN ACTIVA
    ========================== --}}

            <div class="subscription-dashboard-status subscription-active-status">

                <div class="subscription-active-main">

                    <div class="subscription-active-icon">
                        <i class="bi bi-check-lg"></i>
                    </div>

                    <div>
                        <span class="subscription-dashboard-label">
                            Suscripción activa
                        </span>

                        <h2>
                            {{ $subscription?->plan === 'annual' ? 'Plan anual' : 'Plan mensual' }}
                        </h2>

                        <p>
                            Tu suscripción a AERIA Finance se encuentra activa
                            y se renovará automáticamente.
                        </p>
                    </div>

                </div>

                <div class="subscription-active-badge">
                    <i class="bi bi-arrow-repeat"></i>
                    Renovación automática
                </div>

            </div>


            {{-- ==========================
         RESUMEN
    ========================== --}}

            <section class="subscription-dashboard-section">

                <div class="subscription-dashboard-heading">
                    <h2>Detalles de la suscripción</h2>

                    <p>
                        Información del período actual y de tu último pago.
                    </p>
                </div>


                <div class="subscription-active-grid">

                    {{-- PLAN Y PERÍODO --}}
                    <div class="subscription-detail-card">

                        <div class="subscription-detail-block">

                            <span class="subscription-detail-label">
                                Plan actual
                            </span>

                            <strong class="subscription-detail-value">
                                {{ $subscription?->plan === 'annual' ? 'Plan anual' : 'Plan mensual' }}
                            </strong>

                            <small>
                                USD {{ number_format((float) ($subscription?->price_usd ?? 0), 2, ',', '.') }}
                                /
                                {{ $subscription?->plan === 'annual' ? 'año' : 'mes' }}
                            </small>

                        </div>


                        <div class="subscription-detail-divider"></div>


                        <div class="subscription-detail-block">

                            <span class="subscription-detail-label">
                                Período actual
                            </span>

                            @if ($lastPayment?->period_start && $lastPayment?->period_end)
                                <strong class="subscription-detail-value">
                                    {{ $lastPayment->period_start->format('d/m/Y') }}
                                    —
                                    {{ $lastPayment->period_end->format('d/m/Y') }}
                                </strong>
                            @else
                                <strong class="subscription-detail-value">
                                    —
                                </strong>
                            @endif

                            <small>
                                Período cubierto por el último pago
                            </small>

                        </div>

                    </div>


                    {{-- RENOVACIÓN Y ÚLTIMO PAGO --}}
                    <div class="subscription-detail-card">

                        <div class="subscription-detail-block">

                            <span class="subscription-detail-label">
                                Próxima renovación
                            </span>

                            <strong class="subscription-detail-value">

                                @if ($subscription?->next_billing_at)
                                    {{ $subscription->next_billing_at->format('d/m/Y') }}
                                @else
                                    —
                                @endif

                            </strong>

                            <small>
                                El importe se actualizará según la cotización
                                correspondiente.
                            </small>

                        </div>


                        <div class="subscription-detail-divider"></div>


                        <div class="subscription-detail-block">

                            <span class="subscription-detail-label">
                                Último pago
                            </span>

                            @if ($lastPayment)
                                <strong class="subscription-detail-value">
                                    $
                                    {{ number_format((float) $lastPayment->amount_ars, 2, ',', '.') }}
                                    ARS
                                </strong>

                                <small>
                                    USD
                                    {{ number_format((float) $lastPayment->price_usd, 2, ',', '.') }}

                                    · Cotización $
                                    {{ number_format((float) $lastPayment->exchange_rate, 2, ',', '.') }}
                                    / USD
                                </small>
                            @else
                                <strong class="subscription-detail-value">
                                    —
                                </strong>

                                <small>
                                    Todavía no hay pagos registrados.
                                </small>
                            @endif

                        </div>

                    </div>

                </div>

            </section>


            {{-- ==========================
         HISTORIAL DE PAGOS
    ========================== --}}

            <section class="subscription-dashboard-section mt-3">

                <div class="subscription-dashboard-heading">

                    <h2>
                        Historial de pagos
                    </h2>

                    <p>
                        Detalle de los cobros realizados por tu suscripción.
                    </p>

                </div>


                <div class="table-card subscription-payments-table-wrapper">

                    <div class="table-responsive">

                        <table class="subscription-payments-table">

                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Período</th>
                                    <th>Plan</th>
                                    <th>Cotización</th>
                                    <th>Importe</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($subscription->payments as $payment)
                                    <tr>

                                        <td>
                                            @if ($payment->paid_at)
                                                {{ $payment->paid_at->format('d/m/Y') }}
                                            @elseif ($payment->due_at)
                                                {{ $payment->due_at->format('d/m/Y') }}
                                            @else
                                                —
                                            @endif
                                        </td>


                                        <td>

                                            @if ($payment->period_start && $payment->period_end)
                                                {{ $payment->period_start->format('d/m/Y') }}
                                                <span class="subscription-period-arrow">
                                                    →
                                                </span>
                                                {{ $payment->period_end->format('d/m/Y') }}
                                            @else
                                                —
                                            @endif

                                        </td>


                                        <td>
                                            USD
                                            {{ number_format((float) $payment->price_usd, 2, ',', '.') }}
                                        </td>


                                        <td>
                                            $
                                            {{ number_format((float) $payment->exchange_rate, 2, ',', '.') }}
                                        </td>


                                        <td class="subscription-payment-amount">
                                            $
                                            {{ number_format((float) $payment->amount_ars, 2, ',', '.') }}
                                        </td>


                                        <td>

                                            @if ($payment->status === 'paid')
                                                <span class="subscription-payment-status is-paid">
                                                    <i class="bi bi-check-circle"></i>
                                                    Aprobado
                                                </span>
                                            @elseif ($payment->status === 'pending')
                                                <span class="subscription-payment-status is-pending">
                                                    <i class="bi bi-clock"></i>
                                                    Pendiente
                                                </span>
                                            @else
                                                <span class="subscription-payment-status is-failed">
                                                    <i class="bi bi-x-circle"></i>
                                                    Rechazado
                                                </span>
                                            @endif

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="6" class="subscription-empty-payments">
                                            Todavía no hay pagos registrados.
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </section>


            {{-- ==========================
                RENOVACIÓN / CANCELACIÓN
            ========================== --}}

            <section class="subscription-dashboard-section">

                <div class="subscription-renewal-card">

                    <div class="subscription-renewal-info">

                        <div class="subscription-renewal-icon">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>

                        <div>

                            <strong>
                                Renovación automática activa
                            </strong>

                            <p>
                                @if ($subscription?->next_billing_at)
                                    Tu plan se renovará automáticamente el
                                    {{ $subscription->next_billing_at->format('d/m/Y') }}.
                                @else
                                    Tu plan tiene habilitada la renovación automática.
                                @endif
                            </p>

                        </div>

                    </div>


                    <form method="POST" action="{{ route('subscription.cancel') }}" id="cancel-subscription-form">
                        @csrf

                        <button type="button" class="subscription-cancel-button" id="cancel-subscription-button">
                            <i class="bi bi-x-circle"></i>
                            Cancelar suscripción
                        </button>
                    </form>

                </div>

            </section>
        @endif


    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const cancelButton = document.getElementById(
                'cancel-subscription-button'
            );

            const cancelForm = document.getElementById(
                'cancel-subscription-form'
            );

            if (!cancelButton || !cancelForm) {
                return;
            }

            cancelButton.addEventListener('click', function() {

                Swal.fire({
                    title: '¿Cancelar suscripción?',
                    text: 'Se detendrán las próximas renovaciones de AERIA Finance.',
                    icon: 'warning',

                    showCancelButton: true,

                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'Volver',

                    reverseButtons: true,

                    customClass: {
                        popup: 'aeria-swal',
                        confirmButton: 'aeria-swal-danger',
                        cancelButton: 'aeria-swal-secondary'
                    },

                    buttonsStyling: false

                }).then((result) => {

                    if (result.isConfirmed) {
                        cancelForm.submit();
                    }

                });

            });

        });
    </script>
@endsection
