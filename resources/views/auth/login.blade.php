<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Ingresar a AERIA Finance">

    <title>Ingresar | AERIA Finance</title>

    @vite(['resources/js/app.js'])

</head>

<body class="login-page">

    <main class="login-wrapper">

        <div class="login-container">

            <div class="login-brand-row">

                <a href="{{ route('home') }}" class="login-back" aria-label="Volver al inicio">
                    <i class="bi bi-arrow-left"></i>
                </a>

                <a href="{{ route('home') }}" class="login-brand">
                    AERIA <span>Finance</span>
                </a>

            </div>


            <div class="login-header">

                <h1>
                    Iniciar sesión
                </h1>

                <p>
                    Ingresá a tu dashboard financiero.
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


            <form method="POST" action="{{ route('login') }}" class="login-form">

                @csrf


                <div class="login-group">

                    <label for="email" class="login-label">
                        Email
                    </label>

                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="login-input"
                        required autofocus autocomplete="email" placeholder="nombre@empresa.com">

                </div>


                <div class="login-group">

                    <label for="password" class="login-label">
                        Contraseña
                    </label>

                    <input id="password" type="password" name="password" class="login-input" required
                        autocomplete="current-password" placeholder="Tu contraseña">

                </div>


                <div class="login-options">

                    <label for="remember" class="login-remember">

                        <input id="remember" type="checkbox" name="remember">

                        <span>
                            Recordarme
                        </span>

                    </label>


                    <a href="{{ route('password.request') }}" class="login-forgot">
                        ¿Olvidaste tu contraseña?
                    </a>

                </div>


                <button type="submit" class="login-button">
                    Ingresar
                </button>

            </form>


            <div class="login-footer">

                <span>
                    ¿Todavía no tenés una cuenta?
                </span>

                <a href="{{ route('register') }}">
                    Registrar empresa
                </a>

            </div>

        </div>

    </main>

</body>

</html>
