<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Error del servidor | AERIA Finance">

    <title>500 | AERIA Finance</title>

    @vite(['resources/js/app.js'])
</head>

<body>

    <main class="error-page">

        <div class="error-content">

            <a href="{{ url('/') }}" class="error-brand">
                AERIA <span>Finance</span>
            </a>


            <div class="error-code">
                500
            </div>


            <h1>
                Algo salió mal
            </h1>

            <p>
                Ocurrió un error inesperado. Intentá nuevamente en unos momentos.
            </p>


            <a href="{{ url('/') }}" class="error-button">
                Volver al inicio
            </a>

        </div>

    </main>

</body>

</html>
