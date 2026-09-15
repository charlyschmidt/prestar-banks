@extends('layouts.app')


@section('content')

    <div class="page-container">


        <div class="page-header">

            <div>

                <h1>
                    Editar movimiento
                </h1>

                <p>
                    Modificá los datos del movimiento
                </p>

            </div>

        </div>



        <div class="form-card transaction-form-card">


            <form
                method="POST"
                action="{{ route('transactions.update', $transaction->id) }}"
            >

                @csrf

                @method('PUT')


                <div class="form-grid">



                    {{-- CUENTA --}}

                    <div class="form-group">

                        <label>
                            Cuenta
                        </label>


                        <select
                            name="account_id"
                            id="account-select"
                            class="dark-input"
                            required
                        >

                            @foreach ($accounts as $account)

                                @php

                                    $dailyBalance =
                                        $account->dailyBalances->first();

                                    $balance =
                                        $dailyBalance
                                            ? $dailyBalance->current_balance
                                            : 0;

                                @endphp


                                <option
                                    value="{{ $account->id }}"
                                    data-balance="{{ $balance }}"
                                    {{ old('account_id', $transaction->account_id) == $account->id ? 'selected' : '' }}
                                >

                                    {{ $account->name }}

                                </option>

                            @endforeach

                        </select>



                        <div class="account-balance-card">

                            <span>
                                Saldo disponible
                            </span>


                            <strong id="account-balance">

                                $0,00

                            </strong>

                        </div>

                    </div>



                    {{-- TIPO --}}

                    <div class="form-group">

                        <label>
                            Tipo
                        </label>


                        <select
                            name="type"
                            id="transaction-type"
                            class="dark-input"
                            required
                        >

                            <option
                                value="income"
                                {{ old('type', $transaction->type) === 'income' ? 'selected' : '' }}
                            >
                                Ingreso
                            </option>


                            <option
                                value="expense"
                                {{ old('type', $transaction->type) === 'expense' ? 'selected' : '' }}
                            >
                                Egreso
                            </option>


                            <option
                                value="reserve"
                                {{ old('type', $transaction->type) === 'reserve' ? 'selected' : '' }}
                            >
                                Reserva
                            </option>

                        </select>

                    </div>



                    {{-- BANCO DESTINO --}}

                    <div
                        class="form-group"
                        id="destination-bank-group"
                    >

                        <label>
                            Banco destino
                        </label>


                        <select
                            name="destination_bank"
                            id="destination-bank"
                            class="dark-input"
                        >

                            <option value="">
                                Seleccionar banco
                            </option>


                            @foreach ($banks as $bank)

                                <option
                                    value="{{ $bank }}"
                                    {{ old('destination_bank', $transaction->destination_bank) === $bank ? 'selected' : '' }}
                                >
                                    {{ $bank }}
                                </option>

                            @endforeach

                        </select>

                    </div>



                    {{-- MONTO --}}

                    <div class="form-group">

                        <label>
                            Monto
                        </label>


                        <input
                            type="text"
                            name="amount"
                            class="dark-input money-input"
                            value="{{ old('amount', $transaction->amount) }}"
                            inputmode="decimal"
                            autocomplete="off"
                            required
                        >

                    </div>



                    {{-- FECHA --}}

                    <div class="form-group">

                        <label>
                            Fecha
                        </label>


                        <input
                            type="datetime-local"
                            name="date"
                            class="dark-input"
                            value="{{ old(
                                'date',
                                \Carbon\Carbon::parse($transaction->date)->format('Y-m-d\TH:i')
                            ) }}"
                            required
                        >

                    </div>



                    {{-- DESCRIPCIÓN --}}

                    <div class="form-group full-width">

                        <label>
                            Descripción
                        </label>


                        <input
                            name="description"
                            class="dark-input"
                            value="{{ old('description', $transaction->description) }}"
                        >

                    </div>


                </div>



                <div class="form-actions">


                    <a
                        href="{{ route('transactions.index') }}"
                        class="secondary-button"
                    >

                        Cancelar

                    </a>


                    <button
                        type="submit"
                        class="primary-action-button"
                    >

                        <i class="bi bi-check-lg"></i>

                        Guardar cambios

                    </button>


                </div>


            </form>


        </div>


    </div>



    <script>

        /*
        |--------------------------------------------------------------------------
        | SALDO DISPONIBLE
        |--------------------------------------------------------------------------
        */

        const accountSelect =
            document.getElementById('account-select');

        const balanceElement =
            document.getElementById('account-balance');


        function updateAccountBalance() {

            const selected =
                accountSelect.options[
                    accountSelect.selectedIndex
                ];


            const balance =
                Number(
                    selected.dataset.balance
                );


            balanceElement.innerHTML =
                '$ ' +
                balance.toLocaleString(
                    'es-AR',
                    {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }
                );


            balanceElement.classList.remove(
                'positive',
                'negative'
            );


            if (balance >= 0) {

                balanceElement.classList.add(
                    'positive'
                );

            } else {

                balanceElement.classList.add(
                    'negative'
                );

            }

        }


        accountSelect.addEventListener(
            'change',
            updateAccountBalance
        );


        updateAccountBalance();



        /*
        |--------------------------------------------------------------------------
        | BANCO DESTINO
        |--------------------------------------------------------------------------
        |
        | Solo corresponde cuando el movimiento
        | es un Egreso.
        |
        | Ingreso  -> oculto
        | Reserva  -> oculto
        | Egreso   -> visible y obligatorio
        |
        */

        const transactionType =
            document.getElementById(
                'transaction-type'
            );

        const destinationBankGroup =
            document.getElementById(
                'destination-bank-group'
            );

        const destinationBank =
            document.getElementById(
                'destination-bank'
            );


        function updateDestinationBank() {

            if (
                transactionType.value === 'expense'
            ) {

                destinationBankGroup.style.display =
                    '';

                destinationBank.required =
                    true;

            } else {

                destinationBankGroup.style.display =
                    'none';

                destinationBank.required =
                    false;

            }

        }


        transactionType.addEventListener(
            'change',
            updateDestinationBank
        );


        /*
         * Ejecutamos al cargar la página.
         *
         * Esto es importante al EDITAR:
         *
         * expense -> muestra inmediatamente
         *            el banco existente.
         *
         * reserve -> queda Reserva seleccionada
         *            y banco oculto.
         *
         * income  -> banco oculto.
         */
        updateDestinationBank();

    </script>

@endsection