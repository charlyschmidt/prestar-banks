<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Registrar empresa en AERIA Finance">

    <title>Registrar empresa | AERIA Finance</title>

    @vite(['resources/js/app.js'])

</head>

<body>

    <main class="register-page">

        <div class="register-container">

            <div class="register-brand-row">

                <a href="{{ route('home') }}" class="register-back" aria-label="Volver al inicio">
                    <i class="bi bi-arrow-left"></i>
                </a>

                <a href="{{ route('home') }}" class="register-brand">
                    AERIA <span>Finance</span>
                </a>

            </div>


            <div class="register-header">

                <h1>
                    Registrá tu empresa
                </h1>

                <p>
                    Creá tu espacio financiero y comenzá a gestionar
                    tus cuentas y movimientos en tiempo real.
                </p>

            </div>


            @if ($errors->any())

                <div class="register-errors">

                    @foreach ($errors->all() as $error)
                        <div>
                            {{ $error }}
                        </div>
                    @endforeach

                </div>

            @endif


            <form method="POST" action="{{ route('register') }}" class="register-form">

                @csrf


                <div class="form-group">

                    <label for="company_name">
                        Nombre de la empresa
                    </label>

                    <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}"
                        placeholder="Ej: Prestar" autocomplete="organization" required autofocus>

                </div>


                <div class="form-group">

                    <label for="name">
                        Tu nombre
                    </label>

                    <input id="name" type="text" name="name" value="{{ old('name') }}"
                        placeholder="Nombre y apellido" autocomplete="name" required>

                </div>


                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                        placeholder="nombre@empresa.com" autocomplete="email" required>

                </div>


                <div class="form-group">

                    <label for="password">
                        Contraseña
                    </label>

                    <input id="password" type="password" name="password" placeholder="Mínimo 8 caracteres"
                        autocomplete="new-password" required>

                </div>


                <div class="form-group">

                    <label for="password_confirmation">
                        Confirmar contraseña
                    </label>

                    <input id="password_confirmation" type="password" name="password_confirmation"
                        placeholder="Repetí tu contraseña" autocomplete="new-password" required>

                </div>


                <button type="submit" class="register-button">
                    Registrar empresa
                </button>

            </form>


            <div class="register-footer">

                <span>
                    ¿Ya tenés una cuenta?
                </span>

                <a href="{{ route('login') }}">
                    Iniciar sesión
                </a>

            </div>

        </div>

    </main>

</body>

</html>
