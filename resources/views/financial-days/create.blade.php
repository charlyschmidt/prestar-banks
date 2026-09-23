@extends('layouts.app')


@section('content')

    <div class="page-container opening-page">


        {{-- =========================================================
        HEADER
    ========================================================== --}}

        <div class="page-header opening-page-header">

            <div>

                <h1>
                    Apertura de jornada
                </h1>

                <p>
                    Ingresá el saldo disponible de cada cuenta al comenzar el día.
                </p>

            </div>

        </div>



        {{-- =========================================================
        ERRORES
    ========================================================== --}}

        @if ($errors->any())
            <div class="alert alert-danger opening-errors">

                @foreach ($errors->all() as $error)
                    <div>
                        {{ $error }}
                    </div>
                @endforeach

            </div>
        @endif


        {{-- =========================================================
    IMPORTAR SALDOS
========================================================== --}}

        <div class="opening-import" id="opening-balance-import"
            data-analyze-url="{{ route('financial-days.import-balances.analyze') }}" data-csrf="{{ csrf_token() }}">

            <div class="opening-import-info">

                <div class="opening-import-icon">
                    <i class="bi bi-file-earmark-spreadsheet"></i>
                </div>

                <div>
                    <strong>
                        Importar saldos
                    </strong>

                    <span>
                        Cargá un archivo Excel o CSV para completar automáticamente los saldos iniciales.
                    </span>
                </div>

            </div>


            <div class="opening-import-action">

                <input type="file" id="opening-balance-file" accept=".xlsx,.xls,.csv" hidden>

                <button type="button" class="secondary-button" id="opening-balance-file-button">
                    <i class="bi bi-upload"></i>

                    Seleccionar archivo
                </button>

            </div>

        </div>

        {{-- =========================================================
        FORMULARIO
    ========================================================== --}}

        <form method="POST" action="{{ route('financial-days.store') }}" class="opening-form">

            @csrf



            <div class="opening-grid">


                @forelse ($accounts as $account)
                    <div class="opening-account-card">


                        {{-- =================================================
                        BANCO
                    ================================================== --}}

                        <div class="opening-account-header">


                            <div class="opening-account-identity">


                                @if ($account->logo)
                                    <img class="opening-account-logo" src="{{ Storage::url($account->logo) }}"
                                        alt="{{ $account->name }}">
                                @else
                                    <div class="opening-account-logo opening-account-logo-empty">

                                        <i class="bi bi-bank"></i>

                                    </div>
                                @endif



                                <div class="opening-account-name">

                                    <h3>
                                        {{ $account->name }}
                                    </h3>

                                    <span>

                                        {{ $account->balances->count() }}

                                        {{ $account->balances->count() === 1 ? 'moneda configurada' : 'monedas configuradas' }}

                                    </span>

                                </div>


                            </div>


                        </div>



                        {{-- =================================================
                        MONEDAS
                    ================================================== --}}

                        <div class="opening-balances">


                            @forelse ($account->balances as $balance)
                                <div class="opening-balance-row">


                                    {{-- MONEDA --}}

                                    <div class="opening-balance-currency">

                                        <strong>
                                            {{ $balance->currency }}
                                        </strong>

                                        <span>
                                            Saldo inicial
                                        </span>

                                    </div>



                                    {{-- INPUT --}}

                                    <div class="opening-balance-field">


                                        <span class="opening-input-currency">
                                            {{ $balance->currency }}
                                        </span>


                                        <input type="text" class="opening-money-input money-input"
                                            name="balances[{{ $balance->id }}]" placeholder="0,00"
                                            value="{{ old('balances.' . $balance->id) }}" inputmode="decimal"
                                            autocomplete="off" data-account-name="{{ $account->name }}"
                                            data-currency="{{ $balance->currency }}"
                                            data-account-balance-id="{{ $balance->id }}"
                                            aria-label="Saldo inicial {{ $balance->currency }} de {{ $account->name }}"
                                            required>


                                    </div>


                                </div>


                            @empty


                                <div class="opening-no-currencies">

                                    <i class="bi bi-exclamation-circle"></i>

                                    <span>
                                        Esta cuenta no tiene monedas configuradas.
                                    </span>

                                </div>
                            @endforelse


                        </div>


                    </div>


                @empty


                    <div class="opening-no-accounts">

                        <div class="opening-no-accounts-icon">

                            <i class="bi bi-bank"></i>

                        </div>

                        <strong>
                            No hay cuentas disponibles
                        </strong>

                        <span>
                            Primero tenés que crear una cuenta para poder abrir la jornada.
                        </span>

                    </div>
                @endforelse


            </div>



            {{-- =========================================================
            ACTIONS
        ========================================================== --}}

            @if ($accounts->isNotEmpty() && $accounts->sum(fn($account) => $account->balances->count()) > 0)
                <div class="opening-actions">


                    <div class="opening-actions-info">

                        <i class="bi bi-info-circle"></i>

                        <span>
                            Verificá los saldos antes de iniciar la jornada.
                        </span>

                    </div>


                    <button type="submit" class="primary-action-button opening-submit">

                        <i class="bi bi-play-circle"></i>

                        Abrir jornada

                    </button>


                </div>
            @endif


        </form>


    </div>

@endsection
