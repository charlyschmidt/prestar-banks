<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>AERIA Finance · Administración</title>

    @vite(['resources/js/app.js'])

</head>


<body>


<header class="admin-header">

    <div class="admin-brand">

        AERIA Finance

        <span>
            Platform Admin
        </span>

    </div>


    <div class="admin-user">

        <span class="admin-user-name">
            {{ auth()->user()->name }}
        </span>

        <form
            method="POST"
            action="{{ route('logout') }}"
            class="admin-logout-form"
        >
            @csrf

            <button
                type="submit"
                class="admin-logout-button"
                title="Cerrar sesión"
                aria-label="Cerrar sesión"
            >
                <i class="bi bi-box-arrow-right"></i>

                <span>
                    Salir
                </span>

            </button>

        </form>

    </div>

</header>



<main class="admin-container">


    {{-- ==========================
         HEADER
    ========================== --}}

    <div class="page-header">

        <h1>
            Dashboard
        </h1>

        <p>
            Actividad general de AERIA Finance
        </p>

    </div>



    {{-- ==========================
         MÉTRICAS PRINCIPALES
    ========================== --}}

    <div class="platform-stats">


        <div class="platform-stat-card platform-stat-primary">

            <div class="platform-stat-header">

                <span>
                    Saldo actual
                </span>

                <i class="bi bi-wallet2"></i>

            </div>

            <strong>
                ${{ number_format(
                    $stats['current_balance'],
                    2,
                    ',',
                    '.'
                ) }}
            </strong>

            <small>
                Saldo consolidado de todas las empresas
            </small>

        </div>



        <div class="platform-stat-card">

            <div class="platform-stat-header">

                <span>
                    Volumen movido
                </span>

                <i class="bi bi-arrow-left-right"></i>

            </div>

            <strong>
                ${{ number_format(
                    $stats['volume'],
                    2,
                    ',',
                    '.'
                ) }}
            </strong>

            <small>
                Ingresos + egresos históricos
            </small>

        </div>



        <div class="platform-stat-card">

            <div class="platform-stat-header">

                <span>
                    Movimientos
                </span>

                <i class="bi bi-activity"></i>

            </div>

            <strong>
                {{ number_format(
                    $stats['movements'],
                    0,
                    ',',
                    '.'
                ) }}
            </strong>

            <small>
                Operaciones registradas
            </small>

        </div>



        <div class="platform-stat-card">

            <div class="platform-stat-header">

                <span>
                    Usuarios
                </span>

                <i class="bi bi-people"></i>

            </div>

            <strong>
                {{ number_format(
                    $stats['users'],
                    0,
                    ',',
                    '.'
                ) }}
            </strong>

            <small>
                Usuarios registrados en empresas
            </small>

        </div>


    </div>



    {{-- ==========================
         FLUJO FINANCIERO
    ========================== --}}

    <div class="platform-flow">


        <div class="platform-flow-item platform-flow-income">

            <div>

                <span>
                    Ingresos totales
                </span>

                <strong>
                    ${{ number_format(
                        $stats['income'],
                        2,
                        ',',
                        '.'
                    ) }}
                </strong>

            </div>

            <i class="bi bi-arrow-up-right"></i>

        </div>



        <div class="platform-flow-item platform-flow-expense">

            <div>

                <span>
                    Egresos totales
                </span>

                <strong>
                    ${{ number_format(
                        $stats['expense'],
                        2,
                        ',',
                        '.'
                    ) }}
                </strong>

            </div>

            <i class="bi bi-arrow-down-right"></i>

        </div>


    </div>



    {{-- ==========================
         EMPRESAS
    ========================== --}}

    <section class="companies-card">


        <div class="companies-header">

            <div>

                <h2>
                    Empresas
                </h2>

                <p>
                    Empresas registradas en la plataforma
                </p>

            </div>


            <div class="companies-total">

                {{ $stats['companies'] }}

                <span>
                    total
                </span>

            </div>

        </div>



        {{-- ==========================
             ESTADOS
        ========================== --}}

        <div class="company-status-summary">


            <div>

                <span class="company-status-dot status-dot-active"></span>

                <strong>
                    {{ $stats['active'] }}
                </strong>

                <small>
                    Activas
                </small>

            </div>



            <div>

                <span class="company-status-dot status-dot-pending"></span>

                <strong>
                    {{ $stats['pending'] }}
                </strong>

                <small>
                    Pendientes
                </small>

            </div>



            <div>

                <span class="company-status-dot status-dot-suspended"></span>

                <strong>
                    {{ $stats['suspended'] }}
                </strong>

                <small>
                    Suspendidas
                </small>

            </div>


        </div>



        {{-- ==========================
             LISTADO
        ========================== --}}

        @forelse ($companies as $company)


            <div class="company-row">


                <div class="company-main">


                    <div>

                        <div class="company-name">

                            <a
                                href="{{ route(
                                    'aeria-admin.companies.show',
                                    $company
                                ) }}"
                                class="company-name-link"
                            >

                                {{ $company->name }}

                                <i class="bi bi-arrow-up-right"></i>

                            </a>

                        </div>


                        <div class="company-meta">

                            Registrada
                            {{ $company->created_at->format('d/m/Y H:i') }}

                        </div>

                    </div>


                    <span class="status status-{{ $company->status }}">
                        {{ $company->status }}
                    </span>


                </div>



                <div class="company-detail">

                    <span>
                        Usuarios
                    </span>

                    <strong>
                        {{ number_format(
                            $company->users_count,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>

                </div>



                <div class="company-detail">

                    <span>
                        Email
                    </span>

                    <strong>
                        {{ $company->email ?: '—' }}
                    </strong>

                </div>



                <div class="company-detail">

                    <span>
                        CUIT
                    </span>

                    <strong>
                        {{ $company->tax_id ?: '—' }}
                    </strong>

                </div>



                <div class="company-actions">


                    @if ($company->status === 'pending')


                        <form
                            method="POST"
                            action="{{ route(
                                'aeria-admin.companies.approve',
                                $company
                            ) }}"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="admin-button admin-button-approve"
                            >
                                Aprobar
                            </button>

                        </form>


                        <form
                            method="POST"
                            action="{{ route(
                                'aeria-admin.companies.suspend',
                                $company
                            ) }}"
                            onsubmit="return confirm('¿Suspender esta empresa?');"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="admin-button admin-button-suspend"
                            >
                                Suspender
                            </button>

                        </form>


                    @elseif ($company->status === 'active')


                        <form
                            method="POST"
                            action="{{ route(
                                'aeria-admin.companies.suspend',
                                $company
                            ) }}"
                            onsubmit="return confirm('¿Suspender esta empresa? Sus usuarios no podrán acceder.');"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="admin-button admin-button-suspend"
                            >
                                Suspender
                            </button>

                        </form>


                    @elseif ($company->status === 'suspended')


                        <form
                            method="POST"
                            action="{{ route(
                                'aeria-admin.companies.approve',
                                $company
                            ) }}"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="admin-button admin-button-approve"
                            >
                                Reactivar
                            </button>

                        </form>


                    @endif


                </div>


            </div>


        @empty


            <div class="empty-state">

                Todavía no hay empresas registradas.

            </div>


        @endforelse


    </section>


</main>


</body>

</html>