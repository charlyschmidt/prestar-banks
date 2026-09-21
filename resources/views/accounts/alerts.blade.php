@extends('layouts.app')


@section('content')


    <div class="page-container">


        {{-- =====================================================
        HEADER
    ====================================================== --}}

        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

            <div>

                <h1>
                    Alertas
                </h1>

                <p>
                    Configurá avisos para {{ $account->name }}
                </p>

            </div>


            <a href="{{ route('accounts.index') }}" class="secondary-button">
                <i class="bi bi-arrow-left"></i>

                Volver a cuentas
            </a>

        </div>


        {{-- =====================================================
        INFORMACIÓN DE LA CUENTA
    ====================================================== --}}

        <div class="account-alerts-account">


            <div class="account-alerts-account-info">


                @if ($account->logo)
                    <img src="{{ Storage::url($account->logo) }}" class="account-alerts-logo" alt="{{ $account->name }}">
                @else
                    <div class="account-alerts-logo account-alerts-logo-empty">

                        <i class="bi bi-bank"></i>

                    </div>
                @endif


                <div>

                    <strong>
                        {{ $account->name }}
                    </strong>

                    <span>

                        @switch($account->type)
                            @case('bank')
                                Banco
                            @break

                            @case('wallet')
                                Billetera virtual
                            @break

                            @case('cash')
                                Efectivo
                            @break

                            @default
                                {{ ucfirst($account->type) }}
                        @endswitch

                    </span>

                </div>


            </div>


        </div>



        {{-- =====================================================
        GRID DE ALERTAS
    ====================================================== --}}

        <div class="account-alerts-grid">



            {{-- =================================================
            SALDO BAJO
        ================================================== --}}

            <form method="POST" action="{{ route('accounts.alerts.update', $account->id) }}" class="account-alert-form">

                @csrf
                @method('PUT')
                <div class="account-alert-card">


                    <div class="account-alert-card-header">


                        <div class="account-alert-card-icon">

                            <i class="bi bi-wallet2"></i>

                        </div>


                        <div>

                            <h2>
                                Saldo bajo
                            </h2>

                            <p>
                                Recibí un aviso cuando el saldo disponible
                                alcance el límite configurado.
                            </p>

                        </div>


                    </div>



                    <div class="account-alert-card-body">


                        @forelse ($account->balances as $balance)
                            <div class="account-alert-currency">


                                <div class="account-alert-currency-header">

                                    <div>

                                        <strong>
                                            {{ $balance->currency }}
                                        </strong>

                                        <span>
                                            Límite de saldo
                                        </span>

                                    </div>


                                    @if ($balance->low_balance_threshold !== null)
                                        <span class="account-alert-status active">

                                            <i class="bi bi-bell-fill"></i>

                                            Activa

                                        </span>
                                    @else
                                        <span class="account-alert-status">

                                            <i class="bi bi-bell-slash"></i>

                                            Desactivada

                                        </span>
                                    @endif

                                </div>



                                <div class="account-alert-field">

                                    <label for="low_balance_threshold_{{ $balance->id }}">
                                        Avisarme cuando el saldo sea igual o menor a
                                    </label>


                                    <div class="account-alert-input">

                                        <span>
                                            {{ $balance->currency }}
                                        </span>

                                        <input type="text" inputmode="decimal"
                                            id="low_balance_threshold_{{ $balance->id }}"
                                            name="thresholds[{{ $balance->id }}]"
                                            value="{{ old('thresholds.' . $balance->id, $balance->low_balance_threshold) }}"
                                            placeholder="Sin límite" class="dark-input money-input">

                                    </div>


                                    <small>
                                        Dejalo vacío para desactivar esta alerta.
                                    </small>

                                </div>


                            </div>


                        @empty


                            <div class="account-alert-empty">

                                <i class="bi bi-currency-exchange"></i>

                                <p>
                                    Esta cuenta no tiene monedas configuradas.
                                </p>

                            </div>
                        @endforelse


                    </div>

                    <div class="account-alert-card-actions">

                        <button type="submit" class="primary-action-button">
                            <i class="bi bi-check-lg"></i>

                            Guardar alertas
                        </button>

                    </div>


                </div>
            </form>



            {{-- =================================================
            TRANSFERENCIAS PROGRAMADAS
        ================================================== --}}

            <div class="account-alert-card account-alert-card-coming">


                <div class="account-alert-card-header">


                    <div class="account-alert-card-icon">

                        <i class="bi bi-calendar2-check"></i>

                    </div>


                    <div>

                        <h2>
                            Transferencias programadas
                        </h2>

                        <p>
                            Programá recordatorios para transferencias
                            que necesites realizar más adelante.
                        </p>

                    </div>


                </div>



                <div class="account-alert-coming-content">

                    <div class="account-alert-coming-icon">

                        <i class="bi bi-clock"></i>

                    </div>


                    <div>

                        <strong>
                            Próximamente
                        </strong>

                        <span>
                            Vas a poder programar fecha, importe, moneda
                            y concepto para recibir un recordatorio.
                        </span>

                    </div>

                </div>


            </div>


        </div>


    </div>


@endsection
