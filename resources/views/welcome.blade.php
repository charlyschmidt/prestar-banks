<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="AERIA Finance - Dashboard financiero en tiempo real">

    <title>AERIA Finance - Control financiero diario, multiempresa, simple y en tiempo real.</title>

    @vite(['resources/css/welcome.css', 'resources/css/footer.css', 'resources/js/welcome.js'])

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">

    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">

    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

</head>

<body>

    <main class="welcome">

        {{-- Fondo ambiental --}}

        <div class="welcome-nebula" id="welcome-nebula" aria-hidden="true">

            <div class="welcome-nebula-cloud welcome-nebula-cloud-1"></div>

            <div class="welcome-nebula-cloud welcome-nebula-cloud-2"></div>

            <div class="welcome-nebula-cloud welcome-nebula-cloud-3"></div>

        </div>


        <canvas id="welcome-particles" class="welcome-particles" aria-hidden="true">
        </canvas>


        <div class="welcome-page">


            {{-- HERO --}}
            <section class="welcome-content">

                <div class="welcome-badge">
                    Gestión financiera en tiempo real
                </div>

                <h1 class="welcome-logo">
                    AERIA <span>Finance</span>
                </h1>

                <p class="welcome-subtitle">
                    Control financiero diario, multiempresa,
                    simple y en tiempo real.
                </p>

                <div class="welcome-actions">

                    <a href="{{ route('register') }}" class="welcome-button welcome-button-primary">
                        Registrar empresa
                    </a>

                    <a href="{{ route('login') }}" class="welcome-button welcome-button-secondary">
                        Iniciar sesión
                    </a>

                </div>

                <div class="welcome-trial">
                    <span class="welcome-trial-dot"></span>

                    Probalo gratis durante 7 días.
                    <strong>Sin compromiso.</strong>
                </div>

                <a href="#plan" class="scroll-indicator" aria-label="Ver plan">
                    <span class="scroll-arrow"></span>
                </a>

            </section>




            {{-- PLAN --}}
            <section class="welcome-plan" id="plan">

                <div class="plan-card">


                    {{-- PRECIOS --}}
                    <div class="plan-price">

                        <span class="plan-label">
                            AERIA Finance
                        </span>

                        <div class="plan-pricing-options">


                            {{-- MENSUAL --}}
                            <div class="plan-pricing-option">

                                <div class="plan-pricing-option-header">
                                    <span class="plan-pricing-name">
                                        Mensual
                                    </span>
                                </div>

                                <div class="plan-price-value">

                                    <span class="plan-currency">
                                        USD
                                    </span>

                                    <strong>
                                        99
                                    </strong>

                                    <span class="plan-period">
                                        / mes
                                    </span>

                                </div>

                                <p class="plan-pricing-detail">
                                    Facturación mensual.
                                </p>

                            </div>


                            {{-- ANUAL --}}
                            <div class="plan-pricing-option plan-pricing-option-featured">

                                <div class="plan-pricing-option-header">

                                    <span class="plan-pricing-name">
                                        Anual
                                    </span>

                                    <span class="plan-pricing-badge">
                                        2 meses de ahorro
                                    </span>

                                </div>

                                <div class="plan-price-value">

                                    <span class="plan-currency">
                                        USD
                                    </span>

                                    <strong>
                                        990
                                    </strong>

                                    <span class="plan-period">
                                        / año
                                    </span>

                                </div>

                                <p class="plan-pricing-detail">
                                    Equivale a USD 82,50 por mes.
                                    Ahorrás USD 198 al año.
                                </p>

                            </div>

                        </div>


                        <p class="plan-description">
                            Una plataforma para centralizar,
                            controlar y organizar las finanzas
                            diarias de tu empresa desde un solo lugar.
                        </p>


                        <div class="plan-unlimited">
                            Todas las funcionalidades incluidas
                        </div>


                        <a href="{{ route('register') }}" class="plan-button">
                            Probar gratis 7 días
                        </a>


                        <p class="plan-trial">
                            7 días de prueba gratuita.
                            Elegí tu plan al contratar.
                        </p>

                    </div>


                    {{-- FUNCIONALIDADES --}}
                    <div class="plan-features">

                        <div class="plan-features-header">

                            <span>
                                TODO INCLUIDO
                            </span>

                            <h2>
                                Todo el control.<br>
                                Un solo lugar.
                            </h2>

                        </div>


                        <div class="features-grid">


                            <div class="feature-item">

                                <span class="feature-check">✓</span>

                                <div>
                                    <strong>Cuentas y saldos en tiempo real</strong>

                                    <p>
                                        Bancos, billeteras y efectivo centralizados
                                        con el saldo actualizado de cada cuenta.
                                    </p>
                                </div>

                            </div>


                            <div class="feature-item">

                                <span class="feature-check">✓</span>

                                <div>
                                    <strong>Ingresos y egresos</strong>

                                    <p>
                                        Registrá movimientos y visualizá
                                        su impacto inmediatamente.
                                    </p>
                                </div>

                            </div>


                            <div class="feature-item">

                                <span class="feature-check">✓</span>

                                <div>
                                    <strong>Importación de extractos</strong>

                                    <p>
                                        Importá archivos CSV o XLSX
                                        y adaptá sus columnas a cada cuenta.
                                    </p>
                                </div>

                            </div>


                            <div class="feature-item">

                                <span class="feature-check">✓</span>

                                <div>
                                    <strong>Control de movimientos</strong>

                                    <p>
                                        Compará extractos con los movimientos
                                        registrados y detectá diferencias.
                                    </p>
                                </div>

                            </div>


                            <div class="feature-item">

                                <span class="feature-check">✓</span>

                                <div>
                                    <strong>Importación de saldos</strong>

                                    <p>
                                        Cargá saldos de múltiples cuentas
                                        de forma rápida desde archivos.
                                    </p>
                                </div>

                            </div>


                            <div class="feature-item">

                                <span class="feature-check">✓</span>

                                <div>
                                    <strong>Recordatorios</strong>

                                    <p>
                                        Programá pagos, transferencias
                                        y tareas financieras importantes.
                                    </p>
                                </div>

                            </div>


                            <div class="feature-item">

                                <span class="feature-check">✓</span>

                                <div>
                                    <strong>Alertas</strong>

                                    <p>
                                        Recibí avisos sobre eventos
                                        importantes para el control diario.
                                    </p>
                                </div>

                            </div>


                            <div class="feature-item">

                                <span class="feature-check">✓</span>

                                <div>
                                    <strong>Gestión multiempresa</strong>

                                    <p>
                                        Administrá distintas empresas
                                        desde una misma plataforma.
                                    </p>
                                </div>

                            </div>


                            <div class="feature-item">

                                <span class="feature-check">✓</span>

                                <div>
                                    <strong>Múltiples monedas</strong>

                                    <p>
                                        Gestioná saldos independientes
                                        por moneda dentro de cada cuenta.
                                    </p>
                                </div>

                            </div>


                            <div class="feature-item">

                                <span class="feature-check">✓</span>

                                <div>
                                    <strong>Historial completo</strong>

                                    <p>
                                        Consultá movimientos y operaciones
                                        anteriores de cada cuenta.
                                    </p>
                                </div>

                            </div>


                        </div>

                    </div>

                </div>

            </section>

        </div>

        @include('partials.public-footer')

    </main>

</body>

</html>
