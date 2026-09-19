<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Acceso no autorizado | AERIA Finance">

    <title>403 | AERIA Finance</title>

    @vite(['resources/js/app.js'])

</head>

<body>

    <main class="error-page">

        <div class="error-content">

            <a href="{{ url('/') }}" class="error-brand">
                AERIA <span>Finance</span>
            </a>


            <div class="error-code">
                403
            </div>


            <h1>
                Acceso no autorizado
            </h1>

            <p>
                No tenés permisos para acceder a esta sección.
            </p>


            <a href="{{ url('/') }}" class="error-button">
                Volver al inicio
            </a>

        </div>

    </main>

</body>

</html>
