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

                    <select
                        name="account_id"
                        id="account-select"
                        class="dark-input"
                    >

                        @foreach ($accounts as $account)

                            @php
                                $balance = optional(
                                    $account->dailyBalances->first()
                                )->current_balance ?? 0;
                            @endphp

                            <option
                                value="{{ $account->id }}"
                                data-balance="{{ $balance }}"
                                {{ old('account_id') == $account->id ? 'selected' : '' }}
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
                    >

                        <option
                            value="income"
                            {{ old('type') === 'income' ? 'selected' : '' }}
                        >
                            Ingreso
                        </option>

                        <option
                            value="expense"
                            {{ old('type') === 'expense' ? 'selected' : '' }}
                        >
                            Egreso
                        </option>

                    </select>

                </div>



                {{-- BANCO DESTINO --}}

                <div
                    class="form-group"
                    id="destination-bank-group"
                    style="display: none;"
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
                            Seleccionar banco destino
                        </option>

                        @foreach ($banks as $bank)

                            <option
                                value="{{ $bank }}"
                                {{ old('destination_bank') === $bank ? 'selected' : '' }}
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
                        placeholder="0,00"
                        inputmode="decimal"
                        autocomplete="off"
                        value="{{ old('amount') }}"
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
                        value="{{ old('date', date('Y-m-d\TH:i')) }}"
                        class="dark-input"
                    >

                </div>



                {{-- DESCRIPCIÓN --}}

                <div class="form-group">

                    <label>
                        Descripción
                    </label>

                    <input
                        name="description"
                        class="dark-input"
                        placeholder="Ej: Compra supermercado"
                        value="{{ old('description') }}"
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

                    Guardar movimiento

                </button>

            </div>


        </form>


    </div>


</div>


<script>

    /*
    |--------------------------------------------------------------------------
    | Saldo de la cuenta
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


        const balance = Number(
            selected.dataset.balance
        );


        balanceElement.innerHTML =
            '$ ' + balance.toLocaleString(
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


    function updateDestinationBank() {

        if (
            transactionType.value === 'expense'
        ) {

            destinationBankGroup.style.display =
                '';

            destinationBank.required = true;

        } else {

            destinationBankGroup.style.display =
                'none';

            destinationBank.required = false;

            destinationBank.value = '';

        }

    }


    transactionType.addEventListener(
        'change',
        updateDestinationBank
    );


    updateDestinationBank();

</script>

@endsection