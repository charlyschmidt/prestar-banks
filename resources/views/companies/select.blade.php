<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Seleccionar empresa | AERIA Finance">

    <title>Seleccionar empresa | AERIA Finance</title>

    @vite(['resources/js/app.js'])

</head>

<body>

    <main class="company-selection-page">

        <div class="company-selection-wrapper">


            <a href="{{ route('home') }}" class="company-selection-brand">
                AERIA <span>Finance</span>
            </a>


            <header class="company-selection-header">

                <h1>
                    Seleccionar empresa
                </h1>

                <p>
                    Elegí la empresa con la que querés trabajar.
                </p>

            </header>


            <div class="company-selection-grid">

                @foreach ($companies as $company)
                    <form method="POST" action="{{ route('company.select.store') }}">

                        @csrf


                        <input type="hidden" name="company_id" value="{{ $company->id }}">


                        <button type="submit" class="company-selection-card">

                            <div class="company-selection-avatar">

                                @if ($company->logo)
                                    <img src="{{ asset('storage/' . $company->logo) }}" alt="{{ $company->name }}"
                                        class="company-selection-logo">
                                @else
                                    {{ strtoupper(substr($company->name, 0, 1)) }}
                                @endif

                            </div>


                            <div class="company-selection-info">

                                <strong>
                                    {{ $company->name }}
                                </strong>

                                <span>
                                    Ingresar al dashboard
                                </span>

                            </div>


                            <i class="bi bi-chevron-right"></i>

                        </button>

                    </form>
                @endforeach

            </div>


            <footer class="company-selection-footer">

                <div class="company-selection-user">

                    <span>
                        Sesión iniciada como
                    </span>

                    <strong>
                        {{ auth()->user()->email }}
                    </strong>

                </div>


                <form method="POST" action="{{ route('logout') }}">

                    @csrf

                    <button type="submit" class="company-selection-logout">

                        <i class="bi bi-box-arrow-right"></i>

                        <span>
                            Salir
                        </span>

                    </button>

                </form>

            </footer>


        </div>

    </main>

</body>

</html>
