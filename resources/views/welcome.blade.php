<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="AERIA Finance - Dashboard financiero en tiempo real">

    <title>AERIA Finance</title>

    @vite(['resources/css/welcome.css', 'resources/js/welcome.js'])

</head>

<body>

    <main class="welcome">

        {{-- Fondo ambiental --}}
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

                <a href="#plan" class="scroll-indicator" aria-label="Ver plan">
    <span class="scroll-arrow"></span>
</a>

            </section>




            {{-- PLAN --}}
            <section class="welcome-plan" id="plan">

                <div class="plan-card">


                    {{-- PRECIO --}}
                    <div class="plan-price">

                        <span class="plan-label">
                            AERIA Finance
                        </span>

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

                        <p class="plan-description">
                            Una plataforma para centralizar
                            y controlar las finanzas de tu empresa
                            desde un solo lugar.
                        </p>

                        <div class="plan-unlimited">
                            Uso ilimitado
                        </div>

                        <a href="{{ route('register') }}" class="plan-button">
                            Comenzar ahora
                        </a>

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
                                    <strong>Múltiples cuentas</strong>
                                    <p>
                                        Bancos, billeteras y efectivo
                                        centralizados.
                                    </p>
                                </div>
                            </div>


                            <div class="feature-item">
                                <span class="feature-check">✓</span>

                                <div>
                                    <strong>Ingresos y egresos</strong>
                                    <p>
                                        Registrá todos tus movimientos
                                        financieros.
                                    </p>
                                </div>
                            </div>


                            <div class="feature-item">
                                <span class="feature-check">✓</span>

                                <div>
                                    <strong>Saldos en tiempo real</strong>
                                    <p>
                                        Visualizá el estado de cada cuenta
                                        al instante.
                                    </p>
                                </div>
                            </div>


                            <div class="feature-item">
                                <span class="feature-check">✓</span>

                                <div>
                                    <strong>Historial completo</strong>
                                    <p>
                                        Consultá movimientos y operaciones
                                        de cada cuenta.
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
                                    <strong>Actualización instantánea</strong>
                                    <p>
                                        Los movimientos se reflejan
                                        automáticamente en el dashboard.
                                    </p>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </section>


            <footer class="welcome-footer">
                AERIA Finance
                <span>·</span>
                Gestión financiera empresarial
            </footer>

        </div>

    </main>

</body>

</html>
