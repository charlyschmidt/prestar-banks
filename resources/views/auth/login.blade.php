<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Ingresar | Prestar</title>

    @vite([
        'resources/js/app.js'
    ])

</head>

<body class="login-page">

    <div class="login-wrapper">

        <div class="login-card">

            <div class="login-logo">
                <img src="{{ asset('images/logo.png') }}">
            </div>


            <h1 class="login-title">
                Prestar
            </h1>


            <div class="login-subtitle">
                Ingresá a tu panel financiero
            </div>


            @if ($errors->any())

                <div class="login-error">

                    {{ $errors->first() }}

                </div>

            @endif


            <form
                method="POST"
                action="{{ route('login') }}"
            >

                @csrf


                <div class="mb-3">

                    <label
                        for="username"
                        class="login-label"
                    >
                        Usuario
                    </label>

                    <input
                        id="username"
                        type="text"
                        name="username"
                        value="{{ old('username') }}"
                        class="login-input"
                        required
                        autofocus
                        autocomplete="username"
                    >

                </div>


                <div class="mb-3">

                    <label
                        for="password"
                        class="login-label"
                    >
                        Contraseña
                    </label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="login-input"
                        required
                        autocomplete="current-password"
                    >

                </div>


                <div class="login-remember mb-4">

                    <input
                        id="remember"
                        type="checkbox"
                        name="remember"
                    >

                    <label for="remember">
                        Recordarme
                    </label>

                </div>


                <button
                    type="submit"
                    class="login-button"
                >
                    Ingresar
                </button>

            </form>

        </div>

    </div>

</body>

</html>