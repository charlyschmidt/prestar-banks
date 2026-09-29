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



        @if ($company->isOnTrial())

            @php
                $trialDaysRemaining = max(
                    1,
                    (int) ceil(
                        now()->diffInSeconds(
                            $company->trial_ends_at,
                            false
                        ) / 86400
                    )
                );
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

                        $trialProgressWidth = min(
                            100,
                            ($trialDaysRemaining / 7) * 100
                        );
                    @endphp


                    <div class="subscription-trial-progress-track">

                        <div
                            class="subscription-trial-progress-bar subscription-trial-progress-{{ $trialProgressStatus }}"
                            style="width: {{ $trialProgressWidth }}%"
                        ></div>

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

                        <input
                            type="email"
                            id="subscription-payer-email"
                            value="{{ old('payer_email', auth()->user()->email) }}"
                            placeholder="ejemplo@empresa.com"
                            autocomplete="email"
                            required
                        >

                        <small>
                            Puede ser distinto al email con el que ingresás a AERIA Finance.
                        </small>

                    </div>

                </div>

            </section>



            {{-- ==========================
                 PRUEBA MERCADO PAGO
            ========================== --}}

            @if (
                auth()->check()
                && strtolower(auth()->user()->email) === 'centralpadelar@gmail.com'
            )

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


                    <form
                        method="POST"
                        action="{{ route('subscription.test') }}"
                        class="subscription-payer-form"
                    >

                        @csrf

                        <input
                            type="hidden"
                            name="payer_email"
                            class="subscription-payer-email-hidden"
                        >

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            <i class="bi bi-credit-card"></i>
                            Probar suscripción $50
                        </button>

                    </form>

                </div>

            @endif



            {{-- ==========================
                 PLANES
            ========================== --}}

            <section class="subscription-dashboard-section">


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


                        <form
                            method="POST"
                            action="{{ route('subscription.subscribe') }}"
                            class="subscription-plan-action subscription-payer-form"
                        >

                            @csrf

                            <input
                                type="hidden"
                                name="plan"
                                value="monthly"
                            >

                            <input
                                type="hidden"
                                name="payer_email"
                                class="subscription-payer-email-hidden"
                            >

                            <button
                                type="submit"
                                class="primary-button"
                            >
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


                        <form
                            method="POST"
                            action="{{ route('subscription.subscribe') }}"
                            class="subscription-plan-action subscription-payer-form"
                        >

                            @csrf

                            <input
                                type="hidden"
                                name="plan"
                                value="annual"
                            >

                            <input
                                type="hidden"
                                name="payer_email"
                                class="subscription-payer-email-hidden"
                            >

                            <button
                                type="submit"
                                class="primary-button"
                            >
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

            <div class="subscription-dashboard-status">

                <div>

                    <span class="subscription-dashboard-label">
                        Suscripción activa
                    </span>

                    <h2>
                        {{ $subscription?->plan === 'annual' ? 'Plan anual' : 'Plan mensual' }}
                    </h2>

                    <p>
                        Tu suscripción a AERIA Finance
                        se encuentra activa.
                    </p>

                </div>

            </div>


            {{-- Acá construiremos después:
                - período actual
                - próxima renovación
                - importe abonado
                - cotización aplicada
                - historial de pagos
                - cancelación
            --}}


        @endif


    </div>

@endsection