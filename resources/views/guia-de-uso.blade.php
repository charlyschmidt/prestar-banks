<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Guía de uso | AERIA Finance</title>

    <meta name="description"
        content="Guía de uso de AERIA Finance. Aprendé a configurar cuentas, registrar movimientos, controlar extractos, crear recordatorios y administrar tu tesorería.">

    <meta name="robots" content="index, follow">

    <link rel="canonical" href="{{ url('/guia-de-uso') }}">

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">

    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">

    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    @vite(['resources/css/guia-de-uso.css', 'resources/css/footer.css', 'resources/js/welcome.js', 'resources/js/guia-de-uso.js'])

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
    <div class="guide-nebula" aria-hidden="true">
        <div class="guide-nebula-cloud guide-nebula-cloud-1"></div>
        <div class="guide-nebula-cloud guide-nebula-cloud-2"></div>
        <div class="guide-nebula-cloud guide-nebula-cloud-3"></div>
    </div>
    <canvas id="welcome-particles" class="guide-particles" aria-hidden="true">
    </canvas>

    {{-- HEADER --}}

    <header class="guide-header">

        <div class="guide-header-inner">

            <a href="{{ url('/') }}" class="guide-logo">
                AERIA <span>Finance</span>
            </a>

            <div class="guide-header-actions">

                <a href="{{ route('seo.software-tesoreria') }}" class="guide-product-link">
                    Conocer AERIA
                </a>

                <a href="{{ route('login') }}" class="guide-login">
                    Iniciar sesión
                </a>

            </div>

        </div>

    </header>



    {{-- NAVEGACIÓN STICKY --}}

    <nav class="guide-nav">

        <div class="guide-nav-inner">

            <a href="#dashboard" class="active">
                Dashboard
            </a>

            <a href="#cuentas">
                Cuentas
            </a>

            <a href="#movimientos">
                Movimientos
            </a>

            <a href="#control">
                Control de movimientos
            </a>

            <a href="#recordatorios">
                Recordatorios
            </a>

            <a href="#historial">
                Historial
            </a>

            <a href="#usuarios">
                Usuarios
            </a>

            <a href="#configuracion">
                Configuración
            </a>

        </div>

    </nav>



    <main class="guide">


        {{-- INTRO --}}

        <section class="guide-intro">

            <span class="guide-eyebrow">
                GUÍA DE USO
            </span>

            <h1>
                Empezá a usar<br>
                AERIA Finance.
            </h1>

            <p>
                Una guía rápida para conocer las principales herramientas
                de AERIA y comenzar a controlar la tesorería de tu empresa.
            </p>

        </section>



        {{-- DASHBOARD --}}

        <section class="guide-section" id="dashboard">

            <div class="guide-section-number">
                01
            </div>

            <div class="guide-section-heading">

                <span>PRIMERA VISTA</span>

                <h2>
                    Tu tesorería de un vistazo.
                </h2>

                <p>
                    El Dashboard concentra la información principal de la
                    jornada actual. Desde acá podés conocer rápidamente el
                    estado financiero de tu empresa.
                </p>

            </div>


            <div class="guide-points">

                <article>

                    <strong>
                        Balance general
                    </strong>

                    <p>
                        Visualizá el saldo total disponible entre
                        todas tus cuentas.
                    </p>

                </article>


                <article>

                    <strong>
                        Ingresos y egresos
                    </strong>

                    <p>
                        Consultá cuánto ingresó y egresó durante
                        la jornada actual.
                    </p>

                </article>


                <article>

                    <strong>
                        Estado de las cuentas
                    </strong>

                    <p>
                        Cada tarjeta muestra saldo actual, movimientos
                        y fondos reservados de la cuenta.
                    </p>

                </article>

            </div>


            <div class="guide-screenshot">

                <img src="{{ asset('images/guia/01-dashboard.jpg') }}" alt="Dashboard principal de AERIA Finance"
                    class="guide-image" loading="lazy">

            </div>

        </section>



        {{-- CUENTAS --}}

        <section class="guide-section" id="cuentas">

            <div class="guide-section-number">
                02
            </div>

            <div class="guide-section-heading">

                <span>CUENTAS</span>

                <h2>
                    Configurá dónde está tu dinero.
                </h2>

                <p>
                    Agregá bancos, billeteras o efectivo y definí las
                    monedas con las que opera cada cuenta.
                </p>

            </div>


            <div class="guide-screenshot guide-placeholder-large">
                <img src="{{ asset('images/guia/02-cuentas.jpg') }}" alt="Listado de cuentas de AERIA Finance"
                    class="guide-image" loading="lazy">
            </div>


            <div class="guide-subsection">

                <div>

                    <span class="guide-step">
                        CREAR UNA CUENTA
                    </span>

                    <h3>
                        Agregá una nueva cuenta.
                    </h3>

                    <p>
                        Indicá el nombre, el tipo de cuenta, agregá
                        su logo y seleccioná las monedas que vas
                        a utilizar.
                    </p>

                </div>

                <div class="guide-screenshot">
                    <img src="{{ asset('images/guia/03-nueva-cuenta.jpg') }}"
                        alt="Formulario para crear una nueva cuenta en AERIA Finance" class="guide-image"
                        loading="lazy">
                </div>

            </div>


            <div class="guide-subsection">

                <div>

                    <span class="guide-step">
                        ALERTAS E IMPUESTOS
                    </span>

                    <h3>
                        Configuración por cuenta.
                    </h3>

                    <p>
                        Podés definir alertas de saldo bajo y configurar
                        el porcentaje aplicado a transferencias salientes
                        para cada cuenta.
                    </p>

                </div>

                <div class="guide-screenshot">
                    <img src="{{ asset('images/guia/15-alertas-impuestos.jpg') }}"
                        alt="Configuración de alertas e impuestos de una cuenta en AERIA Finance" class="guide-image"
                        loading="lazy">
                </div>

            </div>

        </section>



        {{-- MOVIMIENTOS --}}

        <section class="guide-section" id="movimientos">

            <div class="guide-section-number">
                03
            </div>

            <div class="guide-section-heading">

                <span>MOVIMIENTOS</span>

                <h2>
                    Registrá cada operación.
                </h2>

                <p>
                    Los movimientos actualizan automáticamente la
                    información de tus cuentas y quedan registrados
                    dentro de la jornada.
                </p>

            </div>


            <div class="guide-subsection">

                <div>

                    <span class="guide-step">
                        NUEVO MOVIMIENTO
                    </span>

                    <h3>
                        Cuenta, moneda, tipo y monto.
                    </h3>

                    <p>
                        Seleccioná la cuenta y la moneda, verificá el
                        saldo disponible e ingresá los datos de la operación.
                    </p>

                    <p>
                        Desde el tipo de movimiento también podés registrar
                        reservas de fondos para compromisos futuros.
                    </p>

                </div>

                <div class="guide-screenshot">
                    <img src="{{ asset('images/guia/04-nuevo-movimiento.jpg') }}"
                        alt="Registro de un nuevo movimiento en AERIA Finance" class="guide-image" loading="lazy">
                </div>

            </div>


            <div class="guide-screenshot guide-placeholder-large">
                <img src="{{ asset('images/guia/05-movimientos.jpg') }}" alt="Listado de movimientos de AERIA Finance"
                    class="guide-image" loading="lazy">
            </div>


            <div class="guide-subsection">

                <div>

                    <span class="guide-step">
                        DETALLE POR CUENTA
                    </span>

                    <h3>
                        Analizá cada cuenta por separado.
                    </h3>

                    <p>
                        Desde una cuenta podés consultar su saldo inicial,
                        ingresos, egresos, saldo actual y los movimientos
                        realizados durante la jornada.
                    </p>

                </div>

                <div class="guide-screenshot">
                    <img src="{{ asset('images/guia/06-detalle-cuenta.jpg') }}"
                        alt="Detalle de movimientos y saldo de una cuenta en AERIA Finance" class="guide-image"
                        loading="lazy">
                </div>

            </div>

        </section>



        {{-- CONTROL DE MOVIMIENTOS --}}

        <section class="guide-section" id="control">

            <div class="guide-section-number">
                04
            </div>

            <div class="guide-section-heading">

                <span>CONTROL DE MOVIMIENTOS</span>

                <h2>
                    Compará AERIA con tu extracto.
                </h2>

                <p>
                    Utilizá un extracto bancario para comparar los
                    movimientos registrados y detectar diferencias.
                </p>

            </div>


            <div class="guide-process">

                <article>

                    <span>01</span>

                    <strong>
                        Subí el extracto
                    </strong>

                    <p>
                        Seleccioná la moneda y cargá un archivo
                        CSV, XLSX o XLS.
                    </p>

                </article>


                <article>

                    <span>02</span>

                    <strong>
                        Mapeá las columnas
                    </strong>

                    <p>
                        Indicá qué columnas corresponden a fecha,
                        descripción, importe y saldo.
                    </p>

                </article>


                <article>

                    <span>03</span>

                    <strong>
                        Revisá el resultado
                    </strong>

                    <p>
                        AERIA identifica coincidencias y diferencias
                        entre ambas fuentes.
                    </p>

                </article>

            </div>


            <div class="guide-screenshot guide-placeholder-large">
                <img src="{{ asset('images/guia/07-control-extracto.jpg') }}"
                    alt="Carga de extracto para controlar movimientos en AERIA Finance" class="guide-image"
                    loading="lazy">
            </div>

            <div class="guide-screenshot guide-placeholder-large">
                <img src="{{ asset('images/guia/08-mapeo-columnas.jpg') }}"
                    alt="Mapeo de columnas de un extracto en AERIA Finance" class="guide-image" loading="lazy">
            </div>

            <div class="guide-screenshot guide-placeholder-large">
                <img src="{{ asset('images/guia/09-resultado-control.jpg') }}"
                    alt="Resultado del control de movimientos en AERIA Finance" class="guide-image" loading="lazy">
            </div>

        </section>



        {{-- RECORDATORIOS --}}

        <section class="guide-section" id="recordatorios">

            <div class="guide-section-number">
                05
            </div>

            <div class="guide-section-heading">

                <span>RECORDATORIOS</span>

                <h2>
                    No dejes pasar una tarea importante.
                </h2>

                <p>
                    Programá pagos, transferencias y otras tareas
                    financieras para una fecha y hora determinada.
                </p>

            </div>


            <div class="guide-screenshot guide-placeholder-large">
                <img src="{{ asset('images/guia/10-recordatorios.jpg') }}"
                    alt="Listado de recordatorios en AERIA Finance" class="guide-image" loading="lazy">
            </div>


            <div class="guide-subsection">

                <div>

                    <span class="guide-step">
                        NUEVO RECORDATORIO
                    </span>

                    <h3>
                        Indicá qué y cuándo.
                    </h3>

                    <p>
                        Escribí qué necesitás recordar y seleccioná
                        la fecha y hora. AERIA te avisará cuando llegue
                        el momento programado.
                    </p>

                </div>

                <div class="guide-screenshot">
                    <img src="{{ asset('images/guia/11-nuevo-recordatorio.jpg') }}"
                        alt="Creación de un recordatorio en AERIA Finance" class="guide-image" loading="lazy">
                </div>

            </div>

        </section>



        {{-- HISTORIAL --}}

        <section class="guide-section" id="historial">

            <div class="guide-section-number">
                06
            </div>

            <div class="guide-section-heading">

                <span>HISTORIAL</span>

                <h2>
                    Encontrá cualquier movimiento.
                </h2>

                <p>
                    Consultá operaciones anteriores utilizando filtros
                    por fecha, cuenta, moneda, usuario, tipo, estado
                    o rango de montos.
                </p>

            </div>


            <div class="guide-screenshot guide-placeholder-large">
                <img src="{{ asset('images/guia/12-historial.jpg') }}"
                    alt="Historial y filtros de movimientos de AERIA Finance" class="guide-image" loading="lazy">
            </div>

        </section>



        {{-- USUARIOS --}}

        <section class="guide-section" id="usuarios">

            <div class="guide-section-number">
                07
            </div>

            <div class="guide-section-heading">

                <span>USUARIOS</span>

                <h2>
                    Definí quién puede operar.
                </h2>

                <p>
                    Administrá los usuarios que tienen acceso a la
                    empresa y asigná el rol correspondiente a cada uno.
                </p>

            </div>


            <div class="guide-screenshot guide-placeholder-large">
                <img src="{{ asset('images/guia/13-usuarios.jpg') }}"
                    alt="Administración de usuarios y roles en AERIA Finance" class="guide-image" loading="lazy">
            </div>

        </section>



        {{-- CONFIGURACIÓN --}}

        <section class="guide-section" id="configuracion">

            <div class="guide-section-number">
                08
            </div>

            <div class="guide-section-heading">

                <span>CONFIGURACIÓN</span>

                <h2>
                    Personalizá tu empresa.
                </h2>

                <p>
                    Modificá el nombre, color principal y logo utilizado
                    dentro de AERIA.
                </p>

            </div>


            <div class="guide-screenshot guide-placeholder-large">
                <img src="{{ asset('images/guia/14-configuracion.jpg') }}"
                    alt="Configuración general de una empresa en AERIA Finance" class="guide-image" loading="lazy">
            </div>

        </section>



        {{-- FINAL --}}

        <section class="guide-final">

            <span>
                LISTO PARA EMPEZAR
            </span>

            <h2>
                Tu tesorería,<br>
                bajo control.
            </h2>

            <p>
                Ingresá a AERIA y comenzá a gestionar
                tu operación financiera diaria.
            </p>

            <a href="{{ route('login') }}">
                Ingresar a AERIA
                <span>→</span>
            </a>

        </section>


    </main>


    @include('partials.public-footer')


</body>

</html>
