@extends('layouts.app')


@section('content')
    <div class="page-container">


        <div class="page-header">

            <div>

                <h1>
                    Editar movimiento
                </h1>

                <p>
                    Modificá los datos del ingreso o egreso
                </p>

            </div>

        </div>





        <div class="form-card transaction-form-card">


            <form method="POST" action="{{ route('transactions.update', $transaction->id) }}">

                @csrf

                @method('PUT')



                <div class="form-grid">





                    <div class="form-group">


                        <label>
                            Cuenta
                        </label>


                        <select name="account_id" id="account-select" class="dark-input">


                            @foreach ($accounts as $account)
                                @php
                                    $dailyBalance = $account->dailyBalances->first();

                                    $balance = $dailyBalance ? $dailyBalance->current_balance : 0;
                                @endphp


                                <option value="{{ $account->id }}" data-balance="{{ $balance }}"
                                    {{ $transaction->account_id == $account->id ? 'selected' : '' }}>

                                    {{ $account->name }}

                                </option>
                            @endforeach


                        </select>



                        <div class="account-balance-card">

                            <span>
                                Saldo disponible
                            </span>


                            <strong id="account-balance">

                                $0

                            </strong>


                        </div>


                    </div>






                    <div class="form-group">


                        <label>
                            Tipo
                        </label>


                        <select name="type" class="dark-input">


                            <option value="income" {{ $transaction->type == 'income' ? 'selected' : '' }}>
                                Ingreso
                            </option>


                            <option value="expense" {{ $transaction->type == 'expense' ? 'selected' : '' }}>
                                Egreso
                            </option>

                        </select>


                    </div>







                    <div class="form-group">


                        <label>
                            Monto
                        </label>


                        <input type="number" step="0.01" name="amount" class="dark-input"
                            value="{{ $transaction->amount }}" required>


                    </div>







                    <div class="form-group">


                        <label>
                            Fecha
                        </label>


                        <input type="datetime-local" name="date" class="dark-input"
                            value="{{ \Carbon\Carbon::parse($transaction->date)->format('Y-m-d\TH:i') }}">


                    </div>








                    <div class="form-group full-width">


                        <label>
                            Descripción
                        </label>


                        <input name="description" class="dark-input" value="{{ $transaction->description }}">


                    </div>





                </div>







                <div class="form-actions">


                    <a href="{{ route('transactions.index') }}" class="secondary-button">

                        Cancelar

                    </a>





                    <button type="submit" class="primary-action-button">

                        <i class="bi bi-check-lg"></i>

                        Guardar cambios

                    </button>


                </div>





            </form>


        </div>





    </div>





    <script>
        const accountSelect = document.getElementById('account-select');

        const balanceElement = document.getElementById('account-balance');



        function updateAccountBalance() {


            const selected = accountSelect.options[
                accountSelect.selectedIndex
            ];



            const balance = Number(
                selected.dataset.balance
            );



            balanceElement.innerHTML =
                '$ ' + balance.toLocaleString('es-AR');



            balanceElement.classList.remove(
                'positive',
                'negative'
            );



            if (balance >= 0) {

                balanceElement.classList.add('positive');

            } else {

                balanceElement.classList.add('negative');

            }


        }



        accountSelect.addEventListener(
            'change',
            updateAccountBalance
        );



        updateAccountBalance();
    </script>
@endsection
