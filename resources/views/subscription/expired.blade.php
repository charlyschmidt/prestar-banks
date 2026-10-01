<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="AERIA Finance - Activar suscripción">

    <title>
        Período de prueba finalizado | AERIA Finance
    </title>

    @vite(['resources/css/welcome.css', 'resources/css/subscription.css', 'resources/js/welcome.js'])

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">

    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">

    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-32MQLSS6RJ"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-32MQLSS6RJ');
    </script>

</head>


<body>

    <main class="subscription-page">


        {{-- FONDO AERIA --}}

        <div class="welcome-nebula" id="welcome-nebula" aria-hidden="true">

            <div class="welcome-nebula-cloud welcome-nebula-cloud-1"></div>

            <div class="welcome-nebula-cloud welcome-nebula-cloud-2"></div>

            <div class="welcome-nebula-cloud welcome-nebula-cloud-3"></div>

        </div>


        <canvas id="welcome-particles" class="welcome-particles" aria-hidden="true"></canvas>



        {{-- CONTENIDO --}}

        <div class="subscription-container">


            <div class="subscription-brand">

                AERIA <span>Finance</span>

            </div>



            <section class="subscription-card">


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


                    <form method="POST" action="{{ route('subscription.subscribe') }}">

                        @csrf

                        <input type="hidden" name="plan" value="monthly">

                        <button type="submit" class="subscription-button">
                            Activar plan mensual
                        </button>

                    </form>



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


                    <form method="POST" action="{{ route('subscription.subscribe') }}">

                        @csrf

                        <input type="hidden" name="plan" value="annual">

                        <button type="submit" class="subscription-button">
                            Activar plan anual
                        </button>

                    </form>

                </div>



                <form method="POST" action="{{ route('logout') }}" class="subscription-logout-form">

                    @csrf

                    <button type="submit" class="subscription-logout">
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
