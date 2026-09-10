@extends('layouts.app')


@section('content')

<div class="dashboard">


    <div class="dashboard-header">

        <h1>
            Dashboard
        </h1>

        <p>
            No hay una jornada financiera abierta para hoy.
        </p>

    </div>



    <div class="start-day-card">


        <div class="start-day-icon">

            <i class="bi bi-calendar-check"></i>

        </div>



        <div class="start-day-content">

            <h2>
                Iniciar jornada
            </h2>


            <p>
                Antes de comenzar a registrar movimientos,
                cargá los saldos iniciales de tus cuentas.
            </p>



            <a href="{{ route('financial-days.create') }}"
               class="start-day-button">

                <i class="bi bi-play-circle"></i>

                Iniciar jornada

            </a>


        </div>


    </div>


</div>


@endsection