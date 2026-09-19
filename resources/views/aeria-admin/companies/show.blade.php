<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>{{ $company->name }} · AERIA Finance</title>

    @vite(['resources/js/app.js'])

</head>


<body>


<header class="admin-header">

    <div class="admin-brand">

        <strong>
            AERIA Finance
        </strong>

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
         VOLVER
    ========================== --}}

    <div class="admin-back">

        <a href="{{ route('aeria-admin.index') }}">

            <i class="bi bi-arrow-left"></i>

            Empresas

        </a>

    </div>



    {{-- ==========================
         EMPRESA
    ========================== --}}

    <div class="company-detail-header">


        <div>

            <div class="company-detail-title">

                <h1>
                    {{ $company->name }}
                </h1>

                <span class="status status-{{ $company->status }}">
                    {{ $company->status }}
                </span>

            </div>


            <div class="company-detail-info">

                @if ($company->tax_id)

                    <span>
                        CUIT {{ $company->tax_id }}
                    </span>

                @endif


                @if ($company->email)

                    <span>
                        {{ $company->email }}
                    </span>

                @endif


                <span>
                    Registrada {{ $company->created_at->format('d/m/Y') }}
                </span>

            </div>

        </div>


    </div>



    {{-- ==========================
         RESUMEN
    ========================== --}}

    <div class="company-summary-grid">


        <div class="company-summary-card">

            <span>
                Saldo actual
            </span>

            <strong>
                ${{ number_format(
                    $summary['current_balance'],
                    2,
                    ',',
                    '.'
                ) }}
            </strong>

        </div>



        <div class="company-summary-card">

            <span>
                Usuarios
            </span>

            <strong>
                {{ number_format(
                    $summary['users'],
                    0,
                    ',',
                    '.'
                ) }}
            </strong>

        </div>



        <div class="company-summary-card">

            <span>
                Movimientos
            </span>

            <strong>
                {{ number_format(
                    $summary['movements'],
                    0,
                    ',',
                    '.'
                ) }}
            </strong>

        </div>



        <div class="company-summary-card">

            <span>
                Jornadas
            </span>

            <strong>
                {{ number_format(
                    $summary['financial_days'],
                    0,
                    ',',
                    '.'
                ) }}
            </strong>

        </div>



        <div class="company-summary-card company-summary-income">

            <span>
                Ingresos totales
            </span>

            <strong>
                ${{ number_format(
                    $summary['income'],
                    2,
                    ',',
                    '.'
                ) }}
            </strong>

        </div>



        <div class="company-summary-card company-summary-expense">

            <span>
                Egresos totales
            </span>

            <strong>
                ${{ number_format(
                    $summary['expense'],
                    2,
                    ',',
                    '.'
                ) }}
            </strong>

        </div>


    </div>



    {{-- ==========================
         ACTIVIDAD
    ========================== --}}

    <section class="company-activity-card">


        <div class="company-activity-header">

            <div>

                <h2>
                    Actividad diaria
                </h2>

                <p>
                    Resumen financiero por jornada
                </p>

            </div>

        </div>



        @if ($days->isEmpty())


            <div class="empty-state">

                Esta empresa todavía no tiene jornadas registradas.

            </div>


        @else


            {{-- ==========================
                 MOBILE
            ========================== --}}

            <div class="activity-mobile">


                @foreach ($days as $day)


                    <article class="activity-day">


                        <div class="activity-day-header">

                            <div>

                                <strong>
                                    {{ \Carbon\Carbon::parse($day['date'])->format('d/m/Y') }}
                                </strong>

                                <span>
                                    {{ $day['movements'] }}
                                    {{ $day['movements'] === 1 ? 'movimiento' : 'movimientos' }}
                                </span>

                            </div>


                            <span class="day-status day-status-{{ $day['status'] }}">
                                {{ $day['status'] }}
                            </span>

                        </div>



                        <div class="activity-day-balances">


                            <div>

                                <span>
                                    Saldo inicial
                                </span>

                                <strong>
                                    ${{ number_format(
                                        $day['initial_balance'],
                                        2,
                                        ',',
                                        '.'
                                    ) }}
                                </strong>

                            </div>


                            <i class="bi bi-arrow-right"></i>


                            <div>

                                <span>
                                    Saldo final
                                </span>

                                <strong>
                                    ${{ number_format(
                                        $day['final_balance'],
                                        2,
                                        ',',
                                        '.'
                                    ) }}
                                </strong>

                            </div>


                        </div>



                        <div class="activity-day-movements">


                            <div class="activity-income">

                                <span>
                                    Ingresos
                                </span>

                                <strong>
                                    +${{ number_format(
                                        $day['income'],
                                        2,
                                        ',',
                                        '.'
                                    ) }}
                                </strong>

                            </div>


                            <div class="activity-expense">

                                <span>
                                    Egresos
                                </span>

                                <strong>
                                    -${{ number_format(
                                        $day['expense'],
                                        2,
                                        ',',
                                        '.'
                                    ) }}
                                </strong>

                            </div>


                        </div>


                    </article>


                @endforeach


            </div>



            {{-- ==========================
                 DESKTOP
            ========================== --}}

            <div class="activity-desktop">


                <div class="activity-table">


                    <div class="activity-table-row activity-table-head">

                        <div>
                            Fecha
                        </div>

                        <div>
                            Saldo inicial
                        </div>

                        <div>
                            Ingresos
                        </div>

                        <div>
                            Egresos
                        </div>

                        <div>
                            Saldo final
                        </div>

                        <div>
                            Movimientos
                        </div>

                    </div>



                    @foreach ($days as $day)


                        <div class="activity-table-row">

                            <div class="activity-date">

                                <strong>
                                    {{ \Carbon\Carbon::parse($day['date'])->format('d/m/Y') }}
                                </strong>

                                <span>
                                    {{ $day['status'] }}
                                </span>

                            </div>


                            <div>
                                ${{ number_format(
                                    $day['initial_balance'],
                                    2,
                                    ',',
                                    '.'
                                ) }}
                            </div>


                            <div class="activity-income">
                                +${{ number_format(
                                    $day['income'],
                                    2,
                                    ',',
                                    '.'
                                ) }}
                            </div>


                            <div class="activity-expense">
                                -${{ number_format(
                                    $day['expense'],
                                    2,
                                    ',',
                                    '.'
                                ) }}
                            </div>


                            <div>
                                ${{ number_format(
                                    $day['final_balance'],
                                    2,
                                    ',',
                                    '.'
                                ) }}
                            </div>


                            <div>
                                {{ number_format(
                                    $day['movements'],
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </div>


                        </div>


                    @endforeach



                    {{-- TOTAL GENERAL --}}

                    <div class="activity-table-row activity-table-total">

                        <div>
                            Total general
                        </div>


                        <div>
                            —
                        </div>


                        <div class="activity-income">
                            +${{ number_format(
                                $summary['income'],
                                2,
                                ',',
                                '.'
                            ) }}
                        </div>


                        <div class="activity-expense">
                            -${{ number_format(
                                $summary['expense'],
                                2,
                                ',',
                                '.'
                            ) }}
                        </div>


                        <div>
                            ${{ number_format(
                                $summary['current_balance'],
                                2,
                                ',',
                                '.'
                            ) }}
                        </div>


                        <div>
                            {{ number_format(
                                $summary['movements'],
                                0,
                                ',',
                                '.'
                            ) }}
                        </div>

                    </div>


                </div>


            </div>


        @endif


    </section>


</main>


</body>

</html>