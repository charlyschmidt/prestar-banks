<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Software de tesorería para empresas | AERIA Finance</title>

    <meta name="description"
        content="Software de tesorería para centralizar cuentas, saldos, ingresos, egresos y movimientos. Controlá las finanzas diarias de tu empresa en tiempo real.">

    <meta name="robots" content="index, follow">

    <link rel="canonical" href="{{ route('seo.software-tesoreria') }}">


    {{-- Open Graph --}}

    <meta property="og:type" content="website">

    <meta property="og:locale" content="es_AR">

    <meta property="og:site_name" content="AERIA Finance">

    <meta property="og:title" content="Software de tesorería para empresas | AERIA Finance">

    <meta property="og:description"
        content="Centralizá cuentas, saldos y movimientos y controlá diariamente la tesorería de tu empresa desde un solo lugar.">

    <meta property="og:url" content="{{ route('seo.software-tesoreria') }}">


    {{-- Twitter / X --}}

    <meta name="twitter:card" content="summary_large_image">

    <meta name="twitter:title" content="Software de tesorería para empresas | AERIA Finance">

    <meta name="twitter:description"
        content="Centralizá cuentas, saldos y movimientos y controlá diariamente la tesorería de tu empresa desde un solo lugar.">


    {{-- Schema.org --}}

    {{-- Structured Data / Schema.org --}}
    @php
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => 'AERIA Finance',
            'url' => url('/'),
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web',
            'description' =>
                'Software de control financiero y tesorería para empresas. Centralizá cuentas, saldos, ingresos, egresos, transferencias y movimientos en tiempo real.',
            'offers' => [
                [
                    '@type' => 'Offer',
                    'name' => 'Plan mensual',
                    'price' => '99',
                    'priceCurrency' => 'USD',
                    'category' => 'subscription',
                ],
                [
                    '@type' => 'Offer',
                    'name' => 'Plan anual',
                    'price' => '990',
                    'priceCurrency' => 'USD',
                    'category' => 'subscription',
                ],
            ],
        ];
    @endphp

    <script type="application/ld+json">
    {!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>


    {{-- Favicons --}}

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">

    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">

    <link rel="manifest" href="{{ asset('site.webmanifest') }}">


    @vite(['resources/css/software-tesoreria.css', 'resources/css/footer.css', 'resources/js/welcome.js'])
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-32MQLSS6RJ"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-32MQLSS6RJ');
    </script>
</head>

<body>

    <main class="product-page">

        {{-- Fondo AERIA --}}
        <div class="welcome-nebula" id="welcome-nebula" aria-hidden="true">
            <div class="welcome-nebula-cloud welcome-nebula-cloud-1"></div>
            <div class="welcome-nebula-cloud welcome-nebula-cloud-2"></div>
            <div class="welcome-nebula-cloud welcome-nebula-cloud-3"></div>
        </div>

        <canvas id="welcome-particles" class="welcome-particles" aria-hidden="true">
        </canvas>


        {{-- =====================================================
             HEADER
        ====================================================== --}}

        <header class="product-header">

            <div class="product-container product-header-inner">

                <a href="{{ route('home') }}" class="product-logo">
                    AERIA <span>Finance</span>
                </a>

                <nav class="product-nav">

                    <a href="#producto">
                        Producto
                    </a>

                    <a href="#funciones">
                        Funciones
                    </a>

                    <a href="{{ route('home') }}#plan">
                        Precio
                    </a>

                </nav>

                <div class="product-header-actions">

                    <a href="{{ route('login') }}" class="product-login">
                        Ingresar
                    </a>

                    <a href="{{ route('register') }}" class="product-header-cta">
                        Probar gratis
                    </a>

                </div>

            </div>

        </header>



        {{-- =====================================================
             HERO
        ====================================================== --}}

        <section class="product-hero" id="producto">

            <div class="product-container">

                <div class="product-hero-copy">

                    <span class="product-eyebrow">
                        SOFTWARE DE TESORERÍA
                    </span>

                    <h1>
                        Toda tu tesorería.
                        <span>Una sola vista.</span>
                    </h1>

                    <p>
                        Centralizá cuentas, saldos y movimientos
                        y conocé la posición financiera de tu empresa
                        durante toda la jornada.
                    </p>

                    <div class="product-hero-actions">

                        <a href="{{ route('register') }}" class="product-button product-button-primary">

                            Probar gratis 7 días

                        </a>

                        <a href="#como-funciona" class="product-button product-button-secondary">

                            Ver cómo funciona

                        </a>

                    </div>

                    <div class="product-hero-note">
                        <span></span>
                        7 días de prueba gratuita · Sin compromiso
                    </div>

                </div>



                {{-- Captura principal --}}

                <div class="product-dashboard">

                    <div class="product-dashboard-bar">

                        <div class="product-dashboard-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>

                        <div class="product-dashboard-url">
                            app.aeriafinance.com.ar
                        </div>

                    </div>

                    <div class="product-dashboard-image">

                        <img src="{{ asset('images/aeria/dashboard-tesoreria.jpg') }}"
                            alt="Dashboard de tesorería de AERIA Finance mostrando cuentas, saldos y movimientos"
                            width="1680" height="896" loading="eager" fetchpriority="high">

                    </div>

                </div>

            </div>

        </section>



        {{-- =====================================================
             POSICIÓN FINANCIERA
        ====================================================== --}}

        <section class="product-section product-position">

            <div class="product-container">

                <div class="product-section-grid">

                    <div class="product-section-copy">

                        <span class="product-eyebrow">
                            POSICIÓN FINANCIERA
                        </span>

                        <h2>
                            Sabé cuánto tenés.
                            <span>Y dónde lo tenés.</span>
                        </h2>

                        <p>
                            AERIA reúne bancos, billeteras y efectivo
                            en un único dashboard para que puedas consultar
                            el saldo de cada cuenta y el balance general
                            de tu empresa.
                        </p>

                        <p>
                            Cada movimiento modifica los saldos
                            inmediatamente, manteniendo una visión actualizada
                            de la tesorería durante toda la jornada.
                        </p>

                    </div>


                    <div class="product-data-card">

                        <div class="product-data-label">
                            EN UNA SOLA VISTA
                        </div>

                        <div class="product-data-row">

                            <span>
                                Balance general
                            </span>

                            <strong>
                                Todas tus cuentas
                            </strong>

                        </div>

                        <div class="product-data-row">

                            <span>
                                Saldos
                            </span>

                            <strong>
                                Actualizados
                            </strong>

                        </div>

                        <div class="product-data-row">

                            <span>
                                Movimientos
                            </span>

                            <strong>
                                En tiempo real
                            </strong>

                        </div>

                        <div class="product-data-row">

                            <span>
                                Monedas
                            </span>

                            <strong>
                                Independientes
                            </strong>

                        </div>

                    </div>

                </div>

            </div>

        </section>



        {{-- =====================================================
             MOVIMIENTOS
        ====================================================== --}}

        <section class="product-section product-section-dark">

            <div class="product-container">

                <div class="product-section-heading">

                    <span class="product-eyebrow">
                        MOVIMIENTOS EN TIEMPO REAL
                    </span>

                    <h2>
                        Cada movimiento cuenta.
                    </h2>

                    <p>
                        Registrá ingresos, egresos y transferencias, y reservá fondos
                        para compromisos futuros. AERIA actualiza automáticamente
                        tus saldos y mantiene el historial de cada operación.
                    </p>

                </div>


                <div class="product-movement-flow">

                    <article>

                        <span class="product-flow-icon">
                            ↓
                        </span>

                        <div>

                            <h3>
                                Ingresos
                            </h3>

                            <p>
                                Registrá entradas de dinero y actualizá
                                automáticamente el saldo disponible.
                            </p>

                        </div>

                    </article>


                    <article>

                        <span class="product-flow-icon">
                            ↑
                        </span>

                        <div>

                            <h3>
                                Egresos
                            </h3>

                            <p>
                                Controlá pagos y salidas de dinero
                                con validación sobre el saldo disponible.
                            </p>

                        </div>

                    </article>


                    <article>

                        <span class="product-flow-icon">
                            ⇄
                        </span>

                        <div>

                            <h3>
                                Transferencias
                            </h3>

                            <p>
                                Mové dinero entre tus cuentas manteniendo
                                actualizado tanto el origen como el destino.
                            </p>

                        </div>

                    </article>

                    <article>

                        <span class="product-flow-icon">
                            ◷
                        </span>

                        <div>

                            <h3>
                                Reservas
                            </h3>

                            <p>
                                Reservá fondos para pagos futuros sin alterar el saldo real
                                y conocé cuánto dinero tenés realmente disponible.
                            </p>

                        </div>

                    </article>

                </div>

            </div>

        </section>



        {{-- =====================================================
             CONTROL DE MOVIMIENTOS
        ====================================================== --}}

        <section class="product-section">

            <div class="product-container">

                <div class="product-control-grid">

                    <div class="product-section-copy">

                        <span class="product-eyebrow">
                            CONTROL DE MOVIMIENTOS
                        </span>

                        <h2>
                            Lo que registraste.
                            <span>Contra lo que realmente pasó.</span>
                        </h2>

                        <p>
                            Importá extractos CSV o XLSX y compará
                            los movimientos de tus cuentas con los
                            registrados en AERIA.
                        </p>

                        <p>
                            Podés adaptar las columnas de cada archivo
                            a la estructura de tu cuenta y detectar
                            diferencias sin depender de un formato
                            específico de banco.
                        </p>

                    </div>


                    <div class="product-control-flow">

                        <div class="product-control-step">

                            <strong>01</strong>

                            <div>
                                <h3>Importá</h3>
                                <p>CSV o XLSX</p>
                            </div>

                        </div>


                        <div class="product-control-line"></div>


                        <div class="product-control-step">

                            <strong>02</strong>

                            <div>
                                <h3>Mapeá</h3>
                                <p>Las columnas</p>
                            </div>

                        </div>


                        <div class="product-control-line"></div>


                        <div class="product-control-step">

                            <strong>03</strong>

                            <div>
                                <h3>Compará</h3>
                                <p>Los movimientos</p>
                            </div>

                        </div>


                        <div class="product-control-line"></div>


                        <div class="product-control-step">

                            <strong>04</strong>

                            <div>
                                <h3>Detectá</h3>
                                <p>Diferencias</p>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>



        {{-- =====================================================
             CÓMO FUNCIONA
        ====================================================== --}}

        <section class="product-section product-day" id="como-funciona">

            <div class="product-container">

                <div class="product-section-heading">

                    <span class="product-eyebrow">
                        UN DÍA CON AERIA
                    </span>

                    <h2>
                        La tesorería sigue el ritmo de tu empresa.
                    </h2>

                    <p>
                        AERIA organiza el control financiero alrededor
                        de la operación diaria.
                    </p>

                </div>


                <div class="product-timeline">


                    <article>

                        <span class="product-timeline-number">
                            01
                        </span>

                        <div class="product-timeline-dot"></div>

                        <h3>
                            Abrís la jornada
                        </h3>

                        <p>
                            Partís de los saldos reales
                            de cada cuenta.
                        </p>

                    </article>


                    <article>

                        <span class="product-timeline-number">
                            02
                        </span>

                        <div class="product-timeline-dot"></div>

                        <h3>
                            Operás
                        </h3>

                        <p>
                            Registrás ingresos,
                            egresos y transferencias.
                        </p>

                    </article>


                    <article>

                        <span class="product-timeline-number">
                            03
                        </span>

                        <div class="product-timeline-dot"></div>

                        <h3>
                            Controlás
                        </h3>

                        <p>
                            Revisás saldos, movimientos
                            y diferencias.
                        </p>

                    </article>


                    <article>

                        <span class="product-timeline-number">
                            04
                        </span>

                        <div class="product-timeline-dot"></div>

                        <h3>
                            Cerrás el día
                        </h3>

                        <p>
                            La información queda
                            organizada e historizada.
                        </p>

                    </article>


                </div>

            </div>

        </section>



        {{-- =====================================================
             FUNCIONES COMPLEMENTARIAS
        ====================================================== --}}

        <section class="product-section" id="funciones">

            <div class="product-container">

                <div class="product-section-heading">

                    <span class="product-eyebrow">
                        MÁS CONTROL
                    </span>

                    <h2>
                        Una tesorería que crece con tu operación.
                    </h2>

                </div>


                <div class="product-secondary-features">


                    <article>

                        <span>
                            01
                        </span>

                        <h3>
                            Multiempresa
                        </h3>

                        <p>
                            Administrá distintas empresas desde
                            una misma cuenta manteniendo su
                            información financiera separada.
                        </p>

                    </article>


                    <article>

                        <span>
                            02
                        </span>

                        <h3>
                            Múltiples monedas
                        </h3>

                        <p>
                            Gestioná saldos independientes
                            por moneda dentro de cada cuenta.
                        </p>

                    </article>


                    <article>

                        <span>
                            03
                        </span>

                        <h3>
                            Recordatorios
                        </h3>

                        <p>
                            Programá pagos, transferencias
                            y tareas financieras importantes.
                        </p>

                    </article>

                    <article>

                        <span>
                            04
                        </span>

                        <h3>
                            Alertas
                        </h3>

                        <p>
                            Recibí avisos ante movimientos importantes,
                            saldos bajos y situaciones que requieren atención.
                        </p>

                    </article>


                </div>

            </div>

        </section>



        {{-- =====================================================
             CTA FINAL
        ====================================================== --}}

        <section class="product-final">

            <div class="product-container">

                <div class="product-final-card">

                    <div>

                        <span class="product-eyebrow">
                            AERIA FINANCE
                        </span>

                        <h2>
                            Tu tesorería.
                            <span>Todos los días.</span>
                        </h2>

                        <p>
                            Empezá a centralizar el control financiero
                            de tu empresa.
                        </p>

                    </div>


                    <div class="product-final-price">

                        <span>
                            Desde
                        </span>

                        <div>
                            <strong>USD 99</strong>
                            <small>/ mes</small>
                        </div>

                        <a href="{{ route('register') }}" class="product-button product-button-primary">

                            Probar gratis 7 días

                        </a>

                    </div>

                </div>

            </div>

        </section>



        @include('partials.public-footer')

    </main>

</body>

</html>
