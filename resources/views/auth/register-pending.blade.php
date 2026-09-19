<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Solicitud de registro recibida en AERIA Finance"
    >

    <title>Solicitud recibida | AERIA Finance</title>

    @vite(['resources/css/register-pending.css'])

</head>

<body>

    <main class="pending-page">

        <div class="pending-container">

            <a
                href="{{ route('home') }}"
                class="pending-brand"
            >
                AERIA <span>Finance</span>
            </a>


            <div class="pending-content">

                <div class="pending-icon">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M20 6L9 17L4 12"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>


                <h1>
                    Solicitud recibida
                </h1>


                <p>
                    Tu empresa fue registrada correctamente.
                    Estamos revisando tu solicitud antes de habilitar
                    el acceso a AERIA Finance.
                </p>


                <div class="pending-status">

                    <span class="status-dot"></span>

                    <span>
                        Pendiente de aprobación
                    </span>

                </div>


                <p class="pending-info">
                    Una vez aprobada la cuenta, vas a poder iniciar
                    sesión y comenzar a gestionar las finanzas de tu empresa.
                </p>


                <a
                    href="{{ route('home') }}"
                    class="pending-button"
                >
                    Volver a AERIA Finance
                </a>

            </div>

        </div>

    </main>

</body>

</html>