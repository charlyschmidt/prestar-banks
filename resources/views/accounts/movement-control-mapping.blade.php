@extends('layouts.app')


@section('content')

    @php

        /*
        |--------------------------------------------------------------------------
        | Intentar detectar automáticamente las columnas
        |--------------------------------------------------------------------------
        */

        $detectedDate = null;
        $detectedDescription = null;
        $detectedAmount = null;
        $detectedBalance = null;


        foreach ($statement['headers'] as $index => $header) {

            $normalized = mb_strtolower(trim($header));


            if (
                $detectedDate === null &&
                str_contains($normalized, 'fecha')
            ) {
                $detectedDate = $index;
            }


            if (
                $detectedDescription === null &&
                (
                    str_contains($normalized, 'concepto') ||
                    str_contains($normalized, 'descripcion') ||
                    str_contains($normalized, 'descripción') ||
                    str_contains($normalized, 'detalle')
                )
            ) {
                $detectedDescription = $index;
            }


            if (
                $detectedAmount === null &&
                (
                    str_contains($normalized, 'importe') ||
                    str_contains($normalized, 'monto')
                )
            ) {
                $detectedAmount = $index;
            }


            if (
                $detectedBalance === null &&
                str_contains($normalized, 'saldo')
            ) {
                $detectedBalance = $index;
            }

        }

    @endphp



    <div class="page-container account-movements-page">


        {{-- ==========================
             HEADER
        ========================== --}}

        <div class="account-movements-header">


            <div class="account-movements-heading">


                <a href="{{ route('accounts.movement-control', $account->id) }}"
                   class="secondary-button back-button">

                    <i class="bi bi-arrow-left"></i>

                    Volver

                </a>


                <div class="account-movements-account">


                    @if ($account->logo)

                        <img
                            src="{{ asset('storage/' . $account->logo) }}"
                            alt="{{ $account->name }}"
                            class="account-movements-logo"
                        >

                    @endif


                    <div class="account-movements-title">

                        <h1>
                            {{ $account->name }}
                        </h1>

                        <p>
                            Control de movimientos · {{ $accountBalance->currency }}
                        </p>

                    </div>


                </div>


            </div>


        </div>



        {{-- ==========================
             CARD PRINCIPAL
        ========================== --}}

        <div class="movement-control-card">


            {{-- ==========================
                 HEADER CARD
            ========================== --}}

            <div class="movement-control-header">

                <div>

                    <h2>
                        Mapear columnas
                    </h2>

                    <p>
                        Indicá qué información contiene cada columna del extracto.
                    </p>

                </div>


                <div class="movement-control-day">

                    <i class="bi bi-file-earmark-spreadsheet"></i>

                    {{ $statement['filename'] }}

                </div>

            </div>



            <form
                method="POST"
                action="{{ route('accounts.movement-control.compare', $account->id) }}"
                class="movement-control-form"
                id="movement-control-mapping-form"
            >

                @csrf


                {{-- ==========================
                     DATOS INTERNOS
                ========================== --}}

                <input
                    type="hidden"
                    name="account_balance_id"
                    value="{{ $accountBalance->id }}"
                >

                <input
                    type="hidden"
                    name="temporary_path"
                    value="{{ $temporaryPath }}"
                >



                {{-- ==========================
                     INFORMACIÓN DEL ARCHIVO
                ========================== --}}

                <div class="movement-control-section">

                    <div class="movement-control-section-title">

                        <span class="movement-control-step">
                            1
                        </span>

                        <div>

                            <strong>
                                Extracto detectado
                            </strong>

                            <span>
                                {{ $statement['rows_count'] }}
                                {{ $statement['rows_count'] === 1 ? 'movimiento detectado' : 'movimientos detectados' }}
                            </span>

                        </div>

                    </div>


                    <div class="movement-control-notice">

                        <i class="bi bi-check-circle"></i>

                        <span>
                            El archivo se leyó correctamente.
                            Ahora asigná las columnas que AERIA debe utilizar para comparar los movimientos.
                        </span>

                    </div>

                </div>



                {{-- ==========================
                     MAPEO
                ========================== --}}

                <div class="movement-control-section">

                    <div class="movement-control-section-title">

                        <span class="movement-control-step">
                            2
                        </span>

                        <div>

                            <strong>
                                Mapeo de columnas
                            </strong>

                            <span>
                                Fecha e importe son obligatorios.
                            </span>

                        </div>

                    </div>



                    <div class="movement-control-mapping-grid">


                        {{-- FECHA --}}

                        <div class="movement-control-mapping-field">

                            <label for="date_column">
                                Fecha
                            </label>

                            <select
                                name="date_column"
                                id="date_column"
                                required
                            >

                                <option value="">
                                    Seleccionar columna
                                </option>

                                @foreach ($statement['headers'] as $index => $header)

                                    <option
                                        value="{{ $index }}"
                                        @selected($detectedDate === $index)
                                    >
                                        {{ $header }}
                                    </option>

                                @endforeach

                            </select>

                            <span>
                                Fecha en la que figura el movimiento bancario.
                            </span>

                        </div>



                        {{-- DESCRIPCIÓN --}}

                        <div class="movement-control-mapping-field">

                            <label for="description_column">
                                Descripción
                            </label>

                            <select
                                name="description_column"
                                id="description_column"
                            >

                                <option value="">
                                    No utilizar
                                </option>

                                @foreach ($statement['headers'] as $index => $header)

                                    <option
                                        value="{{ $index }}"
                                        @selected($detectedDescription === $index)
                                    >
                                        {{ $header }}
                                    </option>

                                @endforeach

                            </select>

                            <span>
                                Concepto o detalle del movimiento. Es opcional.
                            </span>

                        </div>



                        {{-- IMPORTE --}}

                        <div class="movement-control-mapping-field">

                            <label for="amount_column">
                                Importe
                            </label>

                            <select
                                name="amount_column"
                                id="amount_column"
                                required
                            >

                                <option value="">
                                    Seleccionar columna
                                </option>

                                @foreach ($statement['headers'] as $index => $header)

                                    <option
                                        value="{{ $index }}"
                                        @selected($detectedAmount === $index)
                                    >
                                        {{ $header }}
                                    </option>

                                @endforeach

                            </select>

                            <span>
                                Positivo para ingresos y negativo para egresos.
                            </span>

                        </div>



                        {{-- SALDO --}}

                        <div class="movement-control-mapping-field">

                            <label for="balance_column">
                                Saldo
                            </label>

                            <select
                                name="balance_column"
                                id="balance_column"
                            >

                                <option value="">
                                    No utilizar
                                </option>

                                @foreach ($statement['headers'] as $index => $header)

                                    <option
                                        value="{{ $index }}"
                                        @selected($detectedBalance === $index)
                                    >
                                        {{ $header }}
                                    </option>

                                @endforeach

                            </select>

                            <span>
                                Saldo informado por el banco. Es opcional.
                            </span>

                        </div>


                    </div>

                </div>



                {{-- ==========================
                     VISTA PREVIA
                ========================== --}}

                <div class="movement-control-section">

                    <div class="movement-control-section-title">

                        <span class="movement-control-step">
                            3
                        </span>

                        <div>

                            <strong>
                                Vista previa
                            </strong>

                            <span>
                                Primeros movimientos encontrados en el archivo.
                            </span>

                        </div>

                    </div>



                    <div class="movement-control-preview">

                        <div class="table-responsive">

                            <table class="table">

                                <thead>

                                    <tr>

                                        @foreach ($statement['headers'] as $header)

                                            <th>
                                                {{ $header }}
                                            </th>

                                        @endforeach

                                    </tr>

                                </thead>


                                <tbody>

                                    @foreach ($statement['preview'] as $row)

                                        <tr>

                                            @foreach ($statement['headers'] as $index => $header)

                                                <td>
                                                    {{ $row[$index] ?? '—' }}
                                                </td>

                                            @endforeach

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>



                {{-- ==========================
                     ACCIONES
                ========================== --}}

                <div class="movement-control-actions">


                    <div class="movement-control-notice">

                        <i class="bi bi-shield-check"></i>

                        <span>
                            AERIA solamente comparará los movimientos.
                            No se crearán ni modificarán registros.
                        </span>

                    </div>


                    <button
                        type="submit"
                        class="primary-action-button movement-control-submit"
                        id="movement-control-compare"
                    >

                        Comparar movimientos

                        <i class="bi bi-arrow-right"></i>

                    </button>


                </div>


            </form>


        </div>


    </div>

@endsection