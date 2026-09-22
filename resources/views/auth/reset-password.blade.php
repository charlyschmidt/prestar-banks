<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta
        name="description"
        content="Crear nueva contraseña en AERIA Finance"
    >

    <title>
        Nueva contraseña | AERIA Finance
    </title>

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

                <a
                    href="{{ route('home') }}"
                    class="login-brand"
                >
                    AERIA <span>Finance</span>
                </a>

            </div>


            <div class="login-header">

                <h1>
                    Nueva contraseña
                </h1>

                <p>
                    Creá una nueva contraseña para acceder a tu cuenta.
                </p>

            </div>


            @if ($errors->any())

                <div class="login-error">
                    {{ $errors->first() }}
                </div>

            @endif


            <form
                method="POST"
                action="{{ route('password.store') }}"
                class="login-form"
            >

                @csrf


                <input
                    type="hidden"
                    name="token"
                    value="{{ $request->route('token') }}"
                >


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
                        value="{{ old('email', $request->email) }}"
                        class="login-input"
                        required
                        autofocus
                        autocomplete="username"
                    >

                </div>


                <div class="login-group">

                    <label
                        for="password"
                        class="login-label"
                    >
                        Nueva contraseña
                    </label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="login-input"
                        required
                        autocomplete="new-password"
                        placeholder="Nueva contraseña"
                    >

                </div>


                <div class="login-group">

                    <label
                        for="password_confirmation"
                        class="login-label"
                    >
                        Confirmar contraseña
                    </label>

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="login-input"
                        required
                        autocomplete="new-password"
                        placeholder="Repetí la nueva contraseña"
                    >

                </div>


                <button
                    type="submit"
                    class="login-button"
                >
                    Guardar nueva contraseña
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