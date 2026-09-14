@extends('layouts.app')


@section('content')

<div class="page-container">


    <div class="page-header no-day-header">

        <div>

            <h1>
                Dashboard
            </h1>

            <p>
                No hay una jornada financiera abierta para hoy.
            </p>

        </div>

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

                Cargar saldos iniciales

            </a>

        </div>


    </div>


</div>

@endsection