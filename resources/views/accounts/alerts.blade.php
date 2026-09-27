@extends('layouts.app')


@section('content')
    <div class="page-container">


        {{-- =====================================================
        HEADER
        ====================================================== --}}

        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

            <div>

                <h1>
                    Configuración de Alertas e Impuestos
                </h1>

                <p>
                    Configurá los avisos y débitos de {{ $account->name }}
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
CONFIGURACIÓN
====================================================== --}}

        <form method="POST" action="{{ route('accounts.alerts.update', $account->id) }}"
            class="account-alert-form account-alert-form-full">

            @csrf
            @method('PUT')


            <div class="account-settings-grid">


                {{-- =================================================
        ALERTAS
        ================================================== --}}

                <div class="account-alert-card account-alert-card-full">


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


                        <div class="account-alert-currencies">


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


                    </div>


                </div>



                {{-- =================================================
        IMPUESTOS
        ================================================== --}}

                <div class="account-alert-card account-alert-card-full">


                    <div class="account-alert-card-header">


                        <div class="account-alert-card-icon">

                            <i class="bi bi-percent"></i>

                        </div>


                        <div>

                            <h2>
                                Impuestos
                            </h2>

                            <p>
                                Configurá el porcentaje aplicado a las
                                transferencias salientes.
                            </p>

                        </div>


                    </div>



                    <div class="account-alert-card-body">


                        <div class="account-alert-currencies">


                            <div class="account-alert-currency">


                                <div class="account-alert-currency-header">


                                    <div>

                                        <strong>
                                            Transferencias salientes
                                        </strong>

                                        <span>
                                            Impuesto al débito
                                        </span>

                                    </div>


                                    @if ((float) $account->transfer_tax_rate > 0)
                                        <span class="account-alert-status active">

                                            <i class="bi bi-percent"></i>

                                            Activo

                                        </span>
                                    @else
                                        <span class="account-alert-status">

                                            <i class="bi bi-dash-circle"></i>

                                            Desactivado

                                        </span>
                                    @endif


                                </div>



                                <div class="account-alert-field">


                                    <label for="transfer_tax_rate">

                                        Porcentaje aplicado

                                    </label>


                                    <div class="account-alert-input">


                                        <span>
                                            %
                                        </span>


                                        <input type="text" inputmode="decimal" id="transfer_tax_rate"
                                            name="transfer_tax_rate"
                                            value="{{ old(
                                                'transfer_tax_rate',
                                                rtrim(rtrim(number_format((float) $account->transfer_tax_rate, 4, '.', ''), '0'), '.'),
                                            ) }}"
                                            placeholder="0.6" class="dark-input">


                                    </div>


                                    <small>
                                        Ejemplo: con 0.6%, una transferencia de
                                        $100.000 tendrá $600 adicionales de impuesto.
                                        Ingresá 0 para desactivarlo.
                                    </small>


                                </div>


                            </div>


                        </div>


                    </div>


                </div>


            </div>



            {{-- =================================================
            ACCIONES
            ================================================== --}}

            <div class="account-alert-card-actions">

                <button type="submit" class="primary-action-button">
                    <i class="bi bi-check-lg"></i>

                    Guardar configuración
                </button>

            </div>


        </form>


    </div>
@endsection
