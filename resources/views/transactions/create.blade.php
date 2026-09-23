@extends('layouts.app')


@section('content')
    <div class="page-container">


        <div class="page-header">

            <div>

                <h1>
                    Nuevo movimiento
                </h1>

                <p>
                    Registrá un ingreso o egreso
                </p>

            </div>

        </div>



        <div class="form-card transaction-form-card">


            <form method="POST" action="{{ route('transactions.store') }}">

                @csrf


                <div class="form-grid">


                    {{-- CUENTA --}}

                    <div class="form-group">

                        <label>
                            Cuenta
                        </label>

                        <select name="account_id" id="account-select" class="dark-input" required>

                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}"
                                    {{ old('account_id') == $account->id ? 'selected' : '' }}>

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

                        <select name="account_balance_id" id="currency-select" class="dark-input" required>

                            @foreach ($accounts as $account)
                                @foreach ($account->balances as $accountBalance)
                                    @php

                                        $dailyBalance = $accountBalance->dailyBalance;

                                        $currentBalance = $dailyBalance?->current_balance ?? 0;

                                    @endphp


                                    <option value="{{ $accountBalance->id }}" data-account="{{ $account->id }}"
                                        data-currency="{{ $accountBalance->currency }}" data-balance="{{ $currentBalance }}"
                                        {{ old('account_balance_id') == $accountBalance->id ? 'selected' : '' }}>

                                        {{ $accountBalance->currency }}

                                    </option>
                                @endforeach
                            @endforeach

                        </select>



                        {{-- SALDO DISPONIBLE --}}

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

                        <select name="type" id="transaction-type" class="dark-input" required>

                            <option value="income" {{ old('type') === 'income' ? 'selected' : '' }}>
                                Ingreso
                            </option>

                            <option value="expense" {{ old('type') === 'expense' ? 'selected' : '' }}>
                                Egreso
                            </option>

                            <option value="reserve" {{ old('type') === 'reserve' ? 'selected' : '' }}>
                                Reserva
                            </option>

                        </select>

                    </div>



                    {{-- BANCO DESTINO --}}



                    <div class="form-group" id="destination-bank-group" style="display:none;">

                        <label for="destination-bank-search">
                            Banco destino
                        </label>

                        <div class="bank-search-select">

                            <input type="text" id="destination-bank-search" class="dark-input"
                                placeholder="Escribí para buscar un banco..." autocomplete="off">

                            <input type="hidden" name="destination_bank" id="destination-bank"
                                value="{{ old('destination_bank') }}">

                            <div id="destination-bank-options" class="bank-search-options" hidden>

                                @foreach ($banks as $bank)
                                    <button type="button" class="bank-search-option" data-value="{{ $bank }}">
                                        {{ $bank }}
                                    </button>
                                @endforeach

                            </div>

                        </div>

                    </div>



                    {{-- MONTO --}}

                    <div class="form-group">

                        <label>
                            Monto
                        </label>

                        <input type="text" name="amount" class="dark-input money-input" placeholder="0,00"
                            inputmode="decimal" autocomplete="off" value="{{ old('amount') }}" required>

                    </div>



                    {{-- FECHA --}}

                    <div class="form-group">

                        <label>
                            Fecha
                        </label>

                        <input type="datetime-local" name="date" value="{{ old('date', date('Y-m-d\TH:i')) }}"
                            class="dark-input" required>

                    </div>



                    {{-- DESCRIPCIÓN --}}

                    <div class="form-group">

                        <label>
                            Descripción
                        </label>

                        <input name="description" class="dark-input" placeholder="Ej: Compra supermercado"
                            value="{{ old('description') }}">

                    </div>


                </div>



                <div class="form-actions">

                    <a href="{{ route('transactions.index') }}" class="secondary-button">
                        Cancelar
                    </a>


                    <button type="submit" class="primary-action-button">

                        <i class="bi bi-check-lg"></i>

                        Guardar movimiento

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
        | Opciones originales de moneda
        |--------------------------------------------------------------------------
        |
        | Guardamos todas las monedas para poder
        | reconstruir el select cuando cambia
        | la cuenta.
        |
        */

        const currencyOptions =
            Array.from(
                currencySelect.options
            ).map(option => ({

                value: option.value,

                account: option.dataset.account,

                currency: option.dataset.currency,

                balance: option.dataset.balance,

                selected: option.selected

            }));



        /*
        |--------------------------------------------------------------------------
        | Formatear moneda
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
                    'es-AR', {
                        style: 'currency',
                        currency: currency,
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }
                ).format(value);

            } catch (error) {

                return currency + ' ' +
                    value.toLocaleString(
                        'es-AR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }
                    );

            }

        }



        /*
        |--------------------------------------------------------------------------
        | Actualizar saldo visible
        |--------------------------------------------------------------------------
        */

        function updateBalance() {

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
        | Filtrar monedas según cuenta
        |--------------------------------------------------------------------------
        */

        function updateCurrencies() {

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


                    if (
                        optionData.value ===
                        previousValue
                    ) {

                        option.selected = true;

                    }


                    currencySelect.appendChild(
                        option
                    );

                }
            );


            /*
             * Si la moneda seleccionada anteriormente
             * no pertenece a esta cuenta,
             * seleccionamos la primera disponible.
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


        updateCurrencies();



        /*
    |--------------------------------------------------------------------------
    | Banco destino
    |--------------------------------------------------------------------------
    */

        const transactionType =
            document.getElementById('transaction-type');

        const destinationBankGroup =
            document.getElementById('destination-bank-group');

        const destinationBank =
            document.getElementById('destination-bank');

        const destinationBankSearch =
            document.getElementById('destination-bank-search');

        const destinationBankOptions =
            document.getElementById('destination-bank-options');

        const bankOptions =
            Array.from(
                document.querySelectorAll('.bank-search-option')
            );


        /*
        |--------------------------------------------------------------------------
        | Normalizar texto para búsqueda
        |--------------------------------------------------------------------------
        */

        function normalizeBankText(text) {
            return text
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim();
        }


        /*
        |--------------------------------------------------------------------------
        | Filtrar bancos
        |--------------------------------------------------------------------------
        */

        function filterBanks() {
            const search =
                normalizeBankText(
                    destinationBankSearch.value
                );

            let visibleCount = 0;

            bankOptions.forEach(option => {

                const bankName =
                    normalizeBankText(
                        option.dataset.value
                    );

                const visible =
                    bankName.includes(search);

                option.hidden = !visible;

                if (visible) {
                    visibleCount++;
                }

            });

            destinationBankOptions.hidden =
                visibleCount === 0;
        }


        /*
        |--------------------------------------------------------------------------
        | Abrir listado
        |--------------------------------------------------------------------------
        */

        destinationBankSearch.addEventListener(
            'focus',
            function() {

                filterBanks();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Buscar mientras escribe
        |--------------------------------------------------------------------------
        */

        destinationBankSearch.addEventListener(
            'input',
            function() {

                destinationBank.value = '';

                filterBanks();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Seleccionar banco
        |--------------------------------------------------------------------------
        */

        bankOptions.forEach(option => {

            option.addEventListener(
                'click',
                function() {

                    const bank =
                        this.dataset.value;

                    destinationBank.value =
                        bank;

                    destinationBankSearch.value =
                        bank;

                    destinationBankOptions.hidden =
                        true;

                }
            );

        });


        /*
        |--------------------------------------------------------------------------
        | Cerrar al hacer click afuera
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'click',
            function(event) {

                if (
                    !destinationBankGroup.contains(
                        event.target
                    )
                ) {

                    destinationBankOptions.hidden =
                        true;

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Mostrar banco destino solamente en egresos
        |--------------------------------------------------------------------------
        */

        function updateDestinationBank() {
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

                destinationBankSearch.value =
                    '';

                destinationBankOptions.hidden =
                    true;

            }
        }


        transactionType.addEventListener(
            'change',
            updateDestinationBank
        );


        /*
        |--------------------------------------------------------------------------
        | Restaurar valor anterior
        |--------------------------------------------------------------------------
        */

        if (destinationBank.value) {

            destinationBankSearch.value =
                destinationBank.value;

        }


        updateDestinationBank();
    </script>
@endsection
