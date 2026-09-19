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

                                <option
                                    value="{{ $account->id }}"
                                    {{ old(
                                        'account_id',
                                        $transaction->account_id
                                    ) == $account->id ? 'selected' : '' }}
                                >

                                    {{ $account->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>



                    {{-- MONEDA --}}

                    <div class="form-group">

                        <label>
                            Moneda
                        </label>

                        <select
                            name="account_balance_id"
                            id="currency-select"
                            class="dark-input"
                            required
                        >

                            @foreach ($accounts as $account)

                                @foreach ($account->balances as $accountBalance)

                                    @php

                                        $dailyBalance =
                                            $accountBalance->dailyBalance;

                                        $currentBalance =
                                            $dailyBalance?->current_balance ?? 0;

                                        $selectedBalanceId =
                                            old(
                                                'account_balance_id',
                                                $transaction->account_balance_id
                                            );

                                    @endphp


                                    <option
                                        value="{{ $accountBalance->id }}"
                                        data-account="{{ $account->id }}"
                                        data-currency="{{ $accountBalance->currency }}"
                                        data-balance="{{ $currentBalance }}"
                                        {{ $selectedBalanceId == $accountBalance->id ? 'selected' : '' }}
                                    >

                                        {{ $accountBalance->currency }}

                                    </option>

                                @endforeach

                            @endforeach

                        </select>



                        <div class="account-balance-card">

                            <span>
                                Saldo disponible
                            </span>

                            <strong id="account-balance">
                                0,00
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
                                {{ old(
                                    'type',
                                    $transaction->type
                                ) === 'income' ? 'selected' : '' }}
                            >
                                Ingreso
                            </option>


                            <option
                                value="expense"
                                {{ old(
                                    'type',
                                    $transaction->type
                                ) === 'expense' ? 'selected' : '' }}
                            >
                                Egreso
                            </option>


                            <option
                                value="reserve"
                                {{ old(
                                    'type',
                                    $transaction->type
                                ) === 'reserve' ? 'selected' : '' }}
                            >
                                Reserva
                            </option>

                        </select>

                    </div>



                    {{-- BANCO DESTINO --}}

                    <div
                        class="form-group"
                        id="destination-bank-group"
                        style="display:none;"
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
                                    {{ old(
                                        'destination_bank',
                                        $transaction->destination_bank
                                    ) === $bank ? 'selected' : '' }}
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
                            value="{{ old(
                                'amount',
                                $transaction->amount
                            ) }}"
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
                                \Carbon\Carbon::parse(
                                    $transaction->date
                                )->format('Y-m-d\TH:i')
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
                            value="{{ old(
                                'description',
                                $transaction->description
                            ) }}"
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
        | Cuenta + moneda
        |--------------------------------------------------------------------------
        */

        const accountSelect =
            document.getElementById(
                'account-select'
            );

        const currencySelect =
            document.getElementById(
                'currency-select'
            );

        const balanceElement =
            document.getElementById(
                'account-balance'
            );


        /*
        |--------------------------------------------------------------------------
        | Guardar todas las monedas disponibles
        |--------------------------------------------------------------------------
        */

        const currencyOptions =
            Array.from(
                currencySelect.options
            ).map(option => ({

                value:
                    option.value,

                account:
                    option.dataset.account,

                currency:
                    option.dataset.currency,

                balance:
                    option.dataset.balance,

                selected:
                    option.selected

            }));



        /*
        |--------------------------------------------------------------------------
        | Formatear importe
        |--------------------------------------------------------------------------
        */

        function formatCurrency(
            amount,
            currency
        ) {

            const value =
                Number(amount || 0);


            try {

                return new Intl.NumberFormat(
                    'es-AR',
                    {
                        style: 'currency',
                        currency: currency,
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }
                ).format(value);

            } catch (error) {

                return currency + ' ' +
                    value.toLocaleString(
                        'es-AR',
                        {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }
                    );

            }

        }



        /*
        |--------------------------------------------------------------------------
        | Mostrar saldo
        |--------------------------------------------------------------------------
        */

        function updateBalance()
        {

            const selected =
                currencySelect.options[
                    currencySelect.selectedIndex
                ];


            if (!selected) {

                balanceElement.textContent =
                    '—';

                return;

            }


            const balance =
                Number(
                    selected.dataset.balance || 0
                );


            const currency =
                selected.dataset.currency;


            balanceElement.textContent =
                formatCurrency(
                    balance,
                    currency
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



        /*
        |--------------------------------------------------------------------------
        | Filtrar monedas por cuenta
        |--------------------------------------------------------------------------
        */

        function updateCurrencies()
        {

            const accountId =
                accountSelect.value;


            const previousValue =
                currencySelect.value;


            currencySelect.innerHTML =
                '';


            const availableOptions =
                currencyOptions.filter(
                    option =>
                        option.account === accountId
                );


            availableOptions.forEach(
                optionData => {

                    const option =
                        document.createElement(
                            'option'
                        );


                    option.value =
                        optionData.value;


                    option.textContent =
                        optionData.currency;


                    option.dataset.account =
                        optionData.account;


                    option.dataset.currency =
                        optionData.currency;


                    option.dataset.balance =
                        optionData.balance;


                    /*
                     * Conservamos la moneda seleccionada
                     * si pertenece a esta cuenta.
                     */

                    if (
                        optionData.value ===
                        previousValue
                    ) {

                        option.selected =
                            true;

                    }


                    currencySelect.appendChild(
                        option
                    );

                }
            );


            /*
             * Si cambiamos a otra cuenta y la moneda
             * anterior no existe allí, queda seleccionada
             * la primera moneda disponible.
             */

            if (
                currencySelect.options.length > 0 &&
                currencySelect.selectedIndex < 0
            ) {

                currencySelect.selectedIndex =
                    0;

            }


            updateBalance();

        }



        accountSelect.addEventListener(
            'change',
            updateCurrencies
        );


        currencySelect.addEventListener(
            'change',
            updateBalance
        );


        /*
        |--------------------------------------------------------------------------
        | Inicializar
        |--------------------------------------------------------------------------
        |
        | Al editar conserva:
        |
        | - cuenta original
        | - moneda original
        | - saldo de esa moneda
        |
        */

        updateCurrencies();



        /*
        |--------------------------------------------------------------------------
        | Banco destino
        |--------------------------------------------------------------------------
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


        function updateDestinationBank()
        {

            if (
                transactionType.value ===
                'expense'
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

                destinationBank.value =
                    '';

            }

        }


        transactionType.addEventListener(
            'change',
            updateDestinationBank
        );


        updateDestinationBank();

    </script>

@endsection