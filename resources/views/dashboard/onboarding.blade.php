@extends('layouts.app')


@section('content')

<div class="page-container onboarding-page">


    {{-- ============================================================
        HERO
    ============================================================ --}}

    <section class="onboarding-hero">

        <div class="onboarding-hero-content">

            <div class="onboarding-eyebrow">
                <i class="bi bi-stars"></i>
                Primeros pasos
            </div>

            <h1>
                Bienvenido a
                <span>AERIA Finance</span>
            </h1>

            <p>
                Gracias por confiar en AERIA para controlar
                las finanzas de tu empresa.
                Configuremos lo esencial para que puedas comenzar.
            </p>

        </div>


        <div class="onboarding-progress">

            <div class="onboarding-progress-info">

                <span>Configuración inicial</span>

                <strong>
                    {{ $accountsCount > 0 ? '1 de 2 pasos' : '0 de 2 pasos' }}
                </strong>

            </div>

            <div class="onboarding-progress-bar">

                <span
                    style="width: {{ $accountsCount > 0 ? '50%' : '0%' }}"
                ></span>

            </div>

        </div>

    </section>



    {{-- ============================================================
        PASOS
    ============================================================ --}}

    <section class="onboarding-steps">


        {{-- PASO 1 --}}

        <article class="onboarding-step {{ $accountsCount > 0 ? 'is-completed' : 'is-active' }}">

            <div class="onboarding-step-top">

                <div class="onboarding-step-number">

                    @if ($accountsCount > 0)

                        <i class="bi bi-check-lg"></i>

                    @else

                        01

                    @endif

                </div>


                <div class="onboarding-step-status">

                    @if ($accountsCount > 0)

                        <i class="bi bi-check-circle-fill"></i>
                        Completado

                    @else

                        Paso 1

                    @endif

                </div>

            </div>


            <div class="onboarding-step-icon">

                <i class="bi bi-bank"></i>

            </div>


            <div class="onboarding-step-content">

                <h2>
                    {{ $accountsCount > 0
                        ? 'Cuentas configuradas'
                        : 'Configurá tus cuentas'
                    }}
                </h2>


                @if ($accountsCount > 0)

                    <p>
                        Ya tenés
                        <strong>
                            {{ $accountsCount }}
                            {{ $accountsCount === 1 ? 'cuenta configurada' : 'cuentas configuradas' }}.
                        </strong>
                        Podés administrarlas o continuar con tu primera jornada.
                    </p>

                @else

                    <p>
                        Agregá los bancos, billeteras y cajas
                        que utilizás habitualmente para controlar
                        tus saldos y movimientos desde AERIA.
                    </p>

                @endif

            </div>


            <div class="onboarding-step-action">

                @if ($accountsCount > 0)

                    <a
                        href="{{ route('accounts.index') }}"
                        class="onboarding-button onboarding-button-secondary"
                    >
                        <i class="bi bi-gear"></i>

                        Administrar cuentas
                    </a>

                @else

                    <a
                        href="{{ route('accounts.create') }}"
                        class="onboarding-button onboarding-button-primary"
                    >
                        Crear primera cuenta

                        <i class="bi bi-arrow-right"></i>
                    </a>

                @endif

            </div>

        </article>



        {{-- PASO 2 --}}

        <article class="onboarding-step {{ $accountsCount > 0 ? 'is-active' : 'is-locked' }}">

            <div class="onboarding-step-top">

                <div class="onboarding-step-number">
                    02
                </div>


                <div class="onboarding-step-status">

                    @if ($accountsCount > 0)

                        Siguiente paso

                    @else

                        <i class="bi bi-lock"></i>
                        Pendiente

                    @endif

                </div>

            </div>


            <div class="onboarding-step-icon">

                <i class="bi bi-calendar-check"></i>

            </div>


            <div class="onboarding-step-content">

                <h2>
                    Iniciá tu primera jornada
                </h2>

                <p>
                    Cargá el saldo inicial de cada cuenta
                    y comenzá a registrar ingresos,
                    egresos y transferencias.
                </p>

            </div>


            <div class="onboarding-step-action">

                @if ($accountsCount > 0)

                    <a
                        href="{{ route('financial-days.create') }}"
                        class="onboarding-button onboarding-button-primary"
                    >
                        Iniciar primera jornada

                        <i class="bi bi-arrow-right"></i>
                    </a>

                @else

                    <div class="onboarding-locked-message">

                        <i class="bi bi-info-circle"></i>

                        Primero necesitás configurar
                        al menos una cuenta.

                    </div>

                @endif

            </div>

        </article>

    </section>



    {{-- ============================================================
        FOOTER
    ============================================================ --}}

    <div class="onboarding-footer">

        <i class="bi bi-shield-check"></i>

        <span>
            Una vez iniciada tu primera jornada,
            AERIA estará listo para acompañar
            el control financiero diario de tu empresa.
        </span>

    </div>


</div>

@endsection