<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Página no encontrada | AERIA Finance">

    <title>404 | AERIA Finance</title>

    @vite(['resources/js/app.js'])

</head>

<body>

    <main class="error-page">

        <div class="error-content">

            <a href="{{ url('/') }}" class="error-brand">
                AERIA <span>Finance</span>
            </a>


            <div class="error-code">
                404
            </div>


            <h1>
                Página no encontrada
            </h1>

            <p>
                La página que estás buscando no existe o fue movida.
            </p>


            <a href="{{ url('/') }}" class="error-button">
                Volver al inicio
            </a>

        </div>

    </main>

</body>

</html>
