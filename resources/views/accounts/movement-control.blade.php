@extends('layouts.app')


@section('content')
    <div class="page-container account-movements-page">


        {{-- =========================================================
        HEADER
        ========================================================== --}}

        <div class="account-movements-header">

            <div class="account-movements-heading">

                <a href="{{ route('accounts.movements', $account->id) }}" class="secondary-button back-button">

                    <i class="bi bi-arrow-left"></i>

                    Volver

                </a>


                <div class="account-movements-account">

                    @if ($account->logo)
                        <img src="{{ asset('storage/' . $account->logo) }}" alt="{{ $account->name }}"
                            class="account-movements-logo">
                    @else
                        <div class="account-movements-logo account-movements-logo-empty">

                            <i class="bi bi-bank"></i>

                        </div>
                    @endif


                    <div class="account-movements-title">

                        <h1>
                            {{ $account->name }}
                        </h1>

                        <p>
                            Control de movimientos
                        </p>

                    </div>

                </div>

            </div>

        </div>



        {{-- =========================================================
        CONTROL
        ========================================================== --}}

        <div class="movement-control-card">


            <div class="movement-control-header">

                <div>

                    <h2>
                        Comparar extracto bancario
                    </h2>

                    <p>
                        Subí el extracto de la cuenta para compararlo con
                        los movimientos registrados en AERIA.
                    </p>

                </div>

                <div class="movement-control-day">

                    <i class="bi bi-calendar3"></i>

                    <span>
                        Jornada {{ $day->date->format('d/m/Y') }}
                    </span>

                </div>

            </div>



            <form id="movement-control-form" method="POST"
                action="{{ route('accounts.movement-control.process', $account->id) }}" enctype="multipart/form-data"
                class="movement-control-form">

                @csrf


                {{-- =================================================
                MONEDA
                ================================================== --}}

                <div class="movement-control-section">

                    <div class="movement-control-section-title">

                        <span class="movement-control-step">
                            1
                        </span>

                        <div>

                            <h3>
                                Seleccioná la moneda
                            </h3>

                            <p>
                                El extracto se comparará únicamente contra
                                los movimientos de esta moneda.
                            </p>

                        </div>

                    </div>


                    <div class="movement-control-currencies">

                        @forelse ($balances as $balance)
                            @php

                                $currency = $balance->accountBalance?->currency ?? '---';

                            @endphp


                            <label class="movement-control-currency">

                                <input type="radio" name="account_balance_id" value="{{ $balance->account_balance_id }}"
                                    @checked($balances->count() === 1) required>


                                <span class="movement-control-currency-content">

                                    <span class="movement-control-currency-code">

                                        {{ $currency }}

                                    </span>


                                    <span class="movement-control-currency-check">

                                        <i class="bi bi-check-lg"></i>

                                    </span>

                                </span>

                            </label>

                        @empty

                            <div class="account-balance-empty">

                                <i class="bi bi-wallet2"></i>

                                <span>
                                    Esta cuenta no tiene monedas disponibles
                                    para la jornada actual.
                                </span>

                            </div>
                        @endforelse

                    </div>

                </div>



                {{-- =================================================
                ARCHIVO
                ================================================== --}}

                <div class="movement-control-section">

                    <div class="movement-control-section-title">

                        <span class="movement-control-step">
                            2
                        </span>

                        <div>

                            <h3>
                                Seleccioná el extracto
                            </h3>

                            <p>
                                Podés utilizar archivos CSV o Excel.
                                Las columnas se configurarán en el siguiente paso.
                            </p>

                        </div>

                    </div>


                    <label class="movement-control-file">

                        <input type="file" name="statement" id="statement" accept=".csv,.xlsx,.xls" required>


                        <span class="movement-control-file-icon">

                            <i class="bi bi-file-earmark-spreadsheet"></i>

                        </span>


                        <span class="movement-control-file-info">

                            <strong>
                                Seleccionar archivo
                            </strong>

                            <small>
                                CSV, XLSX o XLS
                            </small>

                        </span>


                        <span class="movement-control-file-action">

                            <i class="bi bi-upload"></i>

                        </span>

                    </label>

                </div>



                {{-- =================================================
                ACTION
                ================================================== --}}

                <div class="movement-control-actions">

                    <div class="movement-control-notice">

                        <i class="bi bi-shield-check"></i>

                        <span>
                            El archivo se utilizará únicamente para comparar.
                            No se crearán ni modificarán movimientos.
                        </span>

                    </div>


                    <button type="submit" class="primary-action-button movement-control-submit"
                        id="movement-control-submit" @disabled($balances->isEmpty())>
                        Continuar

                        <i class="bi bi-arrow-right"></i>
                    </button>

                </div>

            </form>

        </div>

    </div>
@endsection
