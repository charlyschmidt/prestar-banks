<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Recuperar contraseña de AERIA Finance">

    <title>Recuperar contraseña | AERIA Finance</title>

    @vite(['resources/js/app.js'])

</head>

<body class="login-page">

    <main class="login-wrapper">

        <div class="login-container">


            <div class="login-brand-row">

                <a
                    href="{{ route('login') }}"
                    class="login-back"
                    aria-label="Volver al inicio de sesión"
                >
                    <i class="bi bi-arrow-left"></i>
                </a>

                <a href="{{ route('home') }}" class="login-brand">
                    AERIA <span>Finance</span>
                </a>

            </div>


            <div class="login-header">

                <h1>
                    Recuperar contraseña
                </h1>

                <p>
                    Ingresá tu email y te enviaremos un enlace para crear una nueva contraseña.
                </p>

            </div>


            @if ($errors->any())

                <div class="login-error">
                    {{ $errors->first() }}
                </div>

            @endif


            @if (session('status'))

                <div class="login-status">
                    {{ session('status') }}
                </div>

            @endif


            <form
                method="POST"
                action="{{ route('password.email') }}"
                class="login-form"
            >

                @csrf


                <div class="login-group">

                    <label
                        for="email"
                        class="login-label"
                    >
                        Email
                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="login-input"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="nombre@empresa.com"
                    >

                </div>


                <button
                    type="submit"
                    class="login-button"
                >
                    Enviar enlace de recuperación
                </button>

            </form>


            <div class="login-footer">

                <span>
                    ¿Recordaste tu contraseña?
                </span>

                <a href="{{ route('login') }}">
                    Iniciar sesión
                </a>

            </div>


        </div>

    </main>

</body>

</html>