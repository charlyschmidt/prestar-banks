
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <meta name="description"
        content="AERIA Finance - Activar suscripción">

    <title>
        Período de prueba finalizado | AERIA Finance
    </title>

    @vite([
        'resources/css/welcome.css',
        'resources/css/subscription.css',
        'resources/js/welcome.js'
    ])

    <link rel="apple-touch-icon" sizes="180x180"
        href="{{ asset('apple-touch-icon.png') }}">

    <link rel="icon" type="image/png" sizes="32x32"
        href="{{ asset('favicon-32x32.png') }}">

    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('favicon-16x16.png') }}">

    <link rel="manifest"
        href="{{ asset('site.webmanifest') }}">

    <script async
        src="https://www.googletagmanager.com/gtag/js?id=G-32MQLSS6RJ">
    </script>

    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());
        gtag('config', 'G-32MQLSS6RJ');
    </script>

</head>

<body>

    <main class="subscription-page">

        {{-- FONDO AERIA --}}

        <div class="welcome-nebula"
            id="welcome-nebula"
            aria-hidden="true">

            <div class="welcome-nebula-cloud welcome-nebula-cloud-1"></div>
            <div class="welcome-nebula-cloud welcome-nebula-cloud-2"></div>
            <div class="welcome-nebula-cloud welcome-nebula-cloud-3"></div>

        </div>

        <canvas
            id="welcome-particles"
            class="welcome-particles"
            aria-hidden="true">
        </canvas>


        {{-- COTIZACIÓN INFORMADA POR LARAVEL --}}

        @php
            $exchangeRate = isset($exchangeRate)
                && is_numeric($exchangeRate)
                && (float) $exchangeRate > 0
                    ? (float) $exchangeRate
                    : null;

            $monthlyArs = $exchangeRate !== null
                ? round(99 * $exchangeRate, 2)
                : null;

            $annualArs = $exchangeRate !== null
                ? round(990 * $exchangeRate, 2)
                : null;
        @endphp


        <div class="subscription-container">

            <div class="subscription-brand">
                AERIA <span>Finance</span>
            </div>


            <section class="subscription-card">

                {{-- ESTADO --}}

                <div class="subscription-status">
                    PERÍODO DE PRUEBA FINALIZADO
                </div>

                <h1>
                    Tu prueba gratuita terminó
                </h1>

                <p class="subscription-description">
                    Finalizaron tus 7 días gratuitos de AERIA Finance.

                    Tus cuentas, movimientos y configuraciones
                    siguen guardados y disponibles al activar
                    tu suscripción.
                </p>


                {{-- DATOS PROTEGIDOS --}}

                <div class="subscription-data-safe">

                    <span class="subscription-check">
                        ✓
                    </span>

                    <div>

                        <strong>
                            No perdiste ningún dato
                        </strong>

                        <p>
                            Vas a continuar exactamente
                            desde donde estabas.
                        </p>

                    </div>

                </div>


                {{-- MENSAJES --}}

                @if (session('error'))

                    <div class="subscription-alert subscription-alert-error">
                        {{ session('error') }}
                    </div>

                @endif

                @if ($errors->any())

                    <div class="subscription-alert subscription-alert-error">

                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach

                    </div>

                @endif

                @if (session('success'))

                    <div class="subscription-alert subscription-alert-success">
                        {{ session('success') }}
                    </div>

                @endif


                {{-- UN SOLO FORMULARIO PARA AMBOS PLANES --}}

                <form
                    method="POST"
                    action="{{ route('subscription.subscribe') }}"
                    class="subscription-checkout-form">

                    @csrf


                    {{-- EMAIL MERCADO PAGO --}}

                    <div class="subscription-email-field">

                        <label for="payer_email">
                            Correo electrónico de Mercado Pago
                        </label>

                        <input
                            type="email"
                            id="payer_email"
                            name="payer_email"
                            value="{{ old('payer_email', auth()->user()->email ?? '') }}"
                            placeholder="correo@ejemplo.com"
                            maxlength="255"
                            autocomplete="email"
                            required
                        >

                        <p class="subscription-field-help">
                            Utilizá el correo de la cuenta de
                            Mercado Pago con la que vas a autorizar
                            la suscripción.
                        </p>

                    </div>


                    {{-- FACTURACIÓN Y TIPO DE CAMBIO --}}

                    <div class="subscription-billing-info">

                        <div class="subscription-billing-icon">
                            <span aria-hidden="true">ⓘ</span>
                        </div>

                        <div class="subscription-billing-content">

                            <strong>
                                Facturación en pesos argentinos
                            </strong>

                            <p>
                                Los planes están expresados en dólares
                                estadounidenses (USD), pero
                                <strong>
                                    el cobro se realiza en pesos argentinos (ARS).
                                </strong>
                            </p>

                            @if ($exchangeRate !== null)

                                <div class="subscription-exchange-rate">

                                    <span>
                                        Dólar oficial vendedor de referencia
                                    </span>

                                    <strong>
                                        USD 1 =
                                        ARS {{ number_format($exchangeRate, 2, ',', '.') }}
                                    </strong>

                                    <small>
                                        Cotización consultada el
                                        {{ now('America/Argentina/Buenos_Aires')->format('d/m/Y') }}
                                    </small>

                                </div>

                            @else

                                <p class="subscription-rate-unavailable">
                                    La cotización no está disponible
                                    temporalmente. El importe en pesos
                                    se informará al iniciar la contratación.
                                </p>

                            @endif

                            <p>
                                El importe en pesos se determina
                                aplicando la cotización oficial vendedora
                                utilizada por AERIA al momento de contratar.
                                En cada renovación se recalcula según
                                la cotización vigente.
                            </p>

                        </div>

                    </div>


                    {{-- SELECCIÓN DE PLAN --}}

                    <div class="subscription-plans">

                        {{-- PLAN MENSUAL --}}

                        <div class="subscription-plan">

                            <div class="subscription-plan-info">

                                <span>
                                    AERIA Finance
                                </span>

                                <strong>
                                    Plan mensual
                                </strong>

                                @if ($monthlyArs !== null)

                                    <small class="subscription-plan-ars">
                                        Aproximadamente ARS
                                        {{ number_format($monthlyArs, 2, ',', '.') }}
                                    </small>

                                @endif

                            </div>

                            <div class="subscription-price">

                                <span>
                                    USD
                                </span>

                                <strong>
                                    99
                                </strong>

                                <small>
                                    / mes
                                </small>

                            </div>

                        </div>

                        <button
                            type="submit"
                            name="plan"
                            value="monthly"
                            class="subscription-button">

                            Activar plan mensual

                        </button>


                        {{-- PLAN ANUAL --}}

                        <div class="subscription-plan">

                            <div class="subscription-plan-info">

                                <span>
                                    AERIA Finance
                                </span>

                                <strong>
                                    Plan anual
                                </strong>

                                <small>
                                    Ahorrás 2 meses
                                </small>

                                @if ($annualArs !== null)

                                    <small class="subscription-plan-ars">
                                        Aproximadamente ARS
                                        {{ number_format($annualArs, 2, ',', '.') }}
                                    </small>

                                @endif

                            </div>

                            <div class="subscription-price">

                                <span>
                                    USD
                                </span>

                                <strong>
                                    990
                                </strong>

                                <small>
                                    / año
                                </small>

                            </div>

                        </div>

                        <button
                            type="submit"
                            name="plan"
                            value="annual"
                            class="subscription-button">

                            Activar plan anual

                        </button>

                    </div>


                    <p class="subscription-estimate-notice">
                        Los importes en pesos son orientativos.
                        El importe definitivo se determina al iniciar
                        la contratación y se informa antes de autorizar
                        la suscripción en Mercado Pago.
                    </p>

                </form>


                {{-- CERRAR SESIÓN --}}

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="subscription-logout-form">

                    @csrf

                    <button
                        type="submit"
                        class="subscription-logout">

                        Cerrar sesión

                    </button>

                </form>

            </section>


            <div class="subscription-footer">
                AERIA Finance
                <span>·</span>
                Gestión financiera empresarial
            </div>

        </div>

    </main>

</body>

</html>
